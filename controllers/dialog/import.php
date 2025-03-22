<?php

namespace Concrete\Package\BlocksCloner\Controller\Dialog;

use Concrete\Core\Area\Area;
use Concrete\Core\Block\Block;
use Concrete\Core\Database\Connection\Connection;
use Concrete\Core\Entity\Block\BlockType\BlockType;
use Concrete\Core\Entity\File\Version;
use Concrete\Core\Entity\Package;
use Concrete\Core\Error\UserMessageException;
use Concrete\Core\Http\ResponseFactoryInterface;
use Concrete\Core\Page\Page;
use Concrete\Core\Permission\Checker;
use Concrete\Core\Validation\CSRF\Token;
use Concrete\Package\BlocksCloner\Controller\AbstractController;
use Concrete\Package\BlocksCloner\ImportChecker;
use Concrete\Package\BlocksCloner\XmlParser;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use SimpleXMLElement;
use Throwable;

defined('C5_EXECUTE') or die('Access Denied.');

class Import extends AbstractController
{
    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Controller\Controller::$viewPath
     */
    protected $viewPath = '/dialogs/import';

    /**
     * @var \Concrete\Core\Entity\Block\BlockType\BlockType[]|null
     */
    private $installedBlockTypes;

    /**
     * @var \Concrete\Core\Entity\Package[]|null
     */
    private $installedPackages;

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Package\BlocksCloner\Controller\AbstractController::view()
     */
    public function view()
    {
        parent::view();
        $aID = $this->request->query->getInt('aID');
        if (!$aID) {
            throw new UserMessageException(t('Access Denied'));
        }
        $aHandle = (string) $this->request->query->get('aHandle');
        if ($aHandle === '') {
            throw new UserMessageException(t('Access Denied'));
        }
        $area = Area::get($this->getPage(), $aHandle);
        if (!$area || $area->isError() || $area->getAreaID() != $aID) {
            throw new UserMessageException(t('Access Denied'));
        }
        $checker = $this->app->make(ImportChecker::class);
        $checker->checkArea($area);
        $this->set('area', $area);
        $this->set('token', $this->app->make(Token::class));
    }

    /**
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function analyze()
    {
        try {
            $aID = $this->request->request->getInt('aID');
            if (!$aID) {
                throw new UserMessageException(t('Access Denied'));
            }
            $aHandle = (string) $this->request->request->get('aHandle');
            if ($aHandle === '') {
                throw new UserMessageException(t('Access Denied'));
            }
            $area = Area::get($this->getPage(), $aHandle);
            if (!$area || $area->isError() || $area->getAreaID() != $aID) {
                throw new UserMessageException(t('Access Denied'));
            }
            $checker = $this->app->make(ImportChecker::class);
            $checker->checkArea($area);
            $xml = $this->request->request->get('xml');
            $sx = $this->loadXml($xml);
            $result = [
                'importToken' => $this->app->make(Token::class)->generate('blocks_cloner:import:' . $this->cID . ':'. sha1($xml)),
            ];
            $result['blockTypes'] = [];
            $installedPackages = $this->getInstalledPackages();
            $checker = new Checker($this->getPage());
            foreach ($this->extractBlockTypes($sx) as $blockType) {
                if (!$checker->canAddBlockType($blockType)) {
                    throw new UserMessageException(t("You can't add blocks of type %s to this page.", t($blockType->getBlockTypeName())));
                }
                $packageID = $blockType->getPackageHandle();
                $package = $packageID ? $installedPackages[$packageID] : null;
                $result['blockTypes'][] = [
                    'id' => (int) $blockType->getBlockTypeID(),
                    'handle' => $blockType->getBlockTypeHandle(),
                    'displayName' => t($blockType->getBlockTypeName()),
                    'package' => $package ? [
                        'handle' => $package->getPackageHandle(),
                        'displayName' => t($package->getPackageName()),
                    ] : null,
                ];
            }
            $parser = $this->app->make(XmlParser::class);
            $result['files'] = [];
            foreach ($parser->findFileVersions($sx) as $key => $item) {
                $serialized = [
                    'key' => $key,
                ];
                if ($item instanceof Version) {
                    $serialized += [
                        'fID' => $item->getFileID(),
                        'prefix' => $item->getPrefix(),
                        'name' => $item->getFileName(),
                    ];
                } else {
                    $serialized['error'] = $item;
                }
                $result['files'][] = $serialized;
            }
            $result['pages'] = [];
            foreach ($parser->findPages($sx) as $key => $item) {
                $serialized = [
                    'path' => $key,
                ];
                if ($item instanceof Page) {
                    $serialized += [
                        'cID' => (int) $item->getCollectionID(),
                        'name' => (string) $item->getCollectionName(),
                        'link' => (string) $item->getCollectionLink(),
                    ];
                } else {
                    $serialized['error'] = $item;
                }
                $result['pages'][] = $serialized;
            }

            return $this->app->make(ResponseFactoryInterface::class)->json($result);
        } catch (Exception $x) {
            return $this->buildErrorResponse($x);
        } catch (Exception $x) {
            return $this->buildErrorResponse($x);
        }
    }

    /**
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function import()
    {
        try {
            $xml = $this->request->request->get('xml');
            if (!$xml || !is_string($xml)) {
                throw new UserMessageException(t('Access Denied'));
            }
            $token = $this->app->make(Token::class);
            if (!$token->validate('blocks_cloner:import:' . $this->cID . ':'. sha1($xml))) {
                throw new UserMessageException($token->getErrorMessage());
            }
            $areaHandle = (string) $this->request->request->get('areaHandle');
            if ($areaHandle === '') {
                throw new UserMessageException(t('Access Denied'));
            }
            $sx = $this->loadXml($xml);
            $area = Area::get($this->getPage(), $areaHandle);
            if (!$area || $area->isError()) {
                throw new UserMessageException(t('Unable to find the requested area'));
            }
            $blockTypeHandle = (string) $sx['type'];
            $blockTypes = $this->getInstalledBlockTypes();
            if (!isset($blockTypes[$blockTypeHandle])) {
                throw new UserMessageException(t('The XML references to a block type with handle %s which is not currently installed.', $blockTypeHandle));
            }
            $blockType = $blockTypes[$blockTypeHandle];
            $initialBlockIDsInArea = $this->getBlockIDsInArea($area);
            $beforeBlockID = $this->request->request->getInt('beforeBlockID');
            if ($beforeBlockID && !in_array($beforeBlockID, $initialBlockIDsInArea, true)) {
                throw new UserMessageException(t('Unable to find the requested block'));
            }
            $blockController = $blockType->getController();
            if (!$blockController) {
                $blockType->loadController();
                $blockController = $blockType->getController();
            }
            /** @var \Concrete\Core\Block\BlockController $blockController */
            $cn = $this->app->make(Connection::class);
            $rollBack = true;
            $cn->beginTransaction();
            try {
                $newBlock = $blockController->import($this->getPage(), $area, $sx);
                $this->app->make('cache/request')->flush();
                $newBlockIDsInArea = $this->getBlockIDsInArea($area);
                if (!$newBlock) {
                    $deltaBlockIDs = array_diff($newBlockIDsInArea, $initialBlockIDsInArea);
                    if (count($deltaBlockIDs) !== 1) {
                        throw new UserMessageException(t('Failed to retrieve the ID of the new block'));
                    }
                    $newBlock = Block::getByID(array_shift($deltaBlockIDs));
                    if (!$newBlock || $newBlock->isError()) {
                        throw new UserMessageException(t('Failed to retrieve the new block'));
                    }
                }
                if ($beforeBlockID !== null) {
                    $newBlockIDsInArea = $this->sortBlockIDs((int) $newBlock->getBlockID(), $beforeBlockID, $newBlockIDsInArea);
                    $this->getPage()->processArrangement($area->getAreaID(), $newBlock->getBlockID(), $newBlockIDsInArea);
                }
                // @todo move block to correct before specified blocks
                // @todo return code so that the block is rendered in the page
                $response = $this->app->make(ResponseFactoryInterface::class)->json([]);
                throw new UserMessageException('@wip');
                $cn->commit();
                $rollBack = false;
            } finally {
                if ($rollBack) {
                    $cn->rollBack();
                }
            }
        } catch (Exception $x) {
            return $this->buildErrorResponse($x);
        } catch (Exception $x) {
            return $this->buildErrorResponse($x);
        }

        return $response;
    }

    /**
     * @param \Exception|\Throwable $error
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    private function buildErrorResponse($error)
    {
        $message = $error instanceof UserMessageException ? $error->getMessage() : (string) $error;

        return $this->app->make(ResponseFactoryInterface::class)->json(['error' => $message]);
    }

    /**
     * @param string|mixed $xml
     *
     * @throws \Concrete\Core\Error\UserMessageException
     *
     * @return \SimpleXMLElement
     */
    private function loadXml($xml)
    {
        if (!is_string($xml) || $xml === '') {
            throw new UserMessageException(t('Please specify the XML to be imported'));
        }
        $restore = libxml_use_internal_errors(true);
        try {
            $sx = simplexml_load_string($xml);
            $errors = libxml_get_errors();
        } finally {
            libxml_use_internal_errors($restore);
        }
        if (!empty($errors)) {
            $lines = [];
            foreach ($errors as $error) {
                $line = '';
                switch ($error->level) {
                    case LIBXML_ERR_WARNING:
                        $line .= '[' . t('Warning') . '] ';
                        break;
                    case LIBXML_ERR_ERROR:
                        $line .= '[' . t('Error') . '] ';
                        break;
                    case LIBXML_ERR_FATAL:
                        $line .= '[' . t('Fatal error') . '] ';
                        break;
                }
                $line .= $error->message;
                if ($error->line) {
                    $line .= ' (' . t('at line %s', $error->line) . ')';
                }
                $lines[] = $line;
            }
            throw new UserMessageException(implode("\n", $lines));
        }
        if (!$sx) {
            throw new UserMessageException(t('Failed to parse the XML'));
        }

        return $sx;
    }

    /**
     * @throws \Concrete\Core\Error\UserMessageException
     *
     * @return \Concrete\Core\Entity\Block\BlockType\BlockType[]
     */
    private function extractBlockTypes(SimpleXMLElement $sx)
    {
        if ($sx->getName() !== 'block') {
            throw new UserMessageException(t('The XML does not represent a block in ConcreteCMS CIF Format'));
        }
        $result = [];
        $errors = [];
        $blockTypes = $this->getInstalledBlockTypes();
        foreach ($this->listBlockNodes($sx) as $xBlock) {
            $type = isset($xBlock['type']) ? (string) $xBlock['type'] : '';
            if ($type === '') {
                $errors[] = t('A %1$s element is missing the %2$s attribute.', '<block>', 'type');
                continue;
            }
            if (!isset($blockTypes[$type])) {
                $errors[] = t('The XML references to a block type with handle %s which is not currently installed.', $type);
                continue;
            }
            $blockType = $blockTypes[$type];
            if (!in_array($blockType, $result, true)) {
                $result[] = $blockType;
            }
        }
        if ($errors !== []) {
            throw new UserMessageException(implode("\n", $errors));
        }

        return $result;
    }

    /**
     * @return \SimpleXMLElement[]|\Generator
     */
    private function listBlockNodes(SimpleXMLElement $sx)
    {
        $isDataTable = false;
        switch ($sx->getName()) {
            case 'block':
                yield $sx;
                break;
            case 'data':
                $isDataTable = isset($sx['table']);
                break;
        }
        foreach ($sx->children() as $child) {
            if ($isDataTable && $child->getName() === 'record') {
                continue;
            }
            foreach ($this->listBlockNodes($child) as $blockNode) {
                yield $blockNode;
            }
        }
    }

    /**
     * @return \Concrete\Core\Entity\Block\BlockType\BlockType[]
     */
    private function getInstalledBlockTypes()
    {
        if ($this->installedBlockTypes === null) {
            $installedBlockTypes = [];
            $em = $this->app->make(EntityManagerInterface::class);
            $repo = $em->getRepository(BlockType::class);
            foreach ($repo->findAll() as $blockType) {
                $installedBlockTypes[$blockType->getBlockTypeHandle()] = $blockType;
            }
            $this->installedBlockTypes = $installedBlockTypes;
        }

        return $this->installedBlockTypes;
    }

    /**
     * @return \Concrete\Core\Entity\Block\BlockType\BlockType[]
     */
    private function getInstalledPackages()
    {
        if ($this->installedPackages === null) {
            $installedPackages = [];
            $em = $this->app->make(EntityManagerInterface::class);
            $repo = $em->getRepository(Package::class);
            foreach ($repo->findAll() as $package) {
                $installedPackages[$package->getPackageID()] = $package;
            }
            $this->installedPackages = $installedPackages;
        }

        return $this->installedPackages;
    }

    /**
     * @return int[]
     */
    private function getBlockIDsInArea(Area $area)
    {
        return array_map(
            static function (array $arr) { return (int) $arr['bID']; },
            $this->getPage()->getBlockIDs($area->getAreaHandle())
        );
    }

    /**
     * @param int $blockID
     * @param int $beforeBlockID
     * @param int[] $allBlockIDs
     *
     * @return int[]
     */
    private function sortBlockIDs($blockID, $beforeBlockID, array $allBlockIDs)
    {
        $result = array_values(array_diff($allBlockIDs, [$blockID]));
        if (count($result) === count($allBlockIDs)) {
            throw new Exception(('Failed to retrieve the ID of the new block'));
        }
        $beforeBlockIDIndex = array_search($beforeBlockID, $result, true);
        if ($beforeBlockIDIndex === false) {
            throw new Exception(('Unable to find the requested block'));
        }
        array_splice($result, $beforeBlockIDIndex, 0, [$blockID]);
        
        return $result;
    }
}
