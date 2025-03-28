<?php

namespace Concrete\Package\BlocksCloner\Controller\Dialog;

use Concrete\Core\Area\Area;
use Concrete\Core\Block\Block;
use Concrete\Core\Block\CustomStyle;
use Concrete\Core\Database\Connection\Connection;
use Concrete\Core\Entity\File\Version;
use Concrete\Core\Entity\Package;
use Concrete\Core\Entity\Page\Feed as PageFeed;
use Concrete\Core\Error\UserMessageException;
use Concrete\Core\File\Filesystem;
use Concrete\Core\File\Import\FileImporter;
use Concrete\Core\File\Import\ImportException;
use Concrete\Core\File\Import\ImportOptions;
use Concrete\Core\File\Service\VolatileDirectory;
use Concrete\Core\File\Service\Zip;
use Concrete\Core\Http\ResponseFactoryInterface;
use Concrete\Core\Page\Page;
use Concrete\Core\Page\Type\Type as PageType;
use Concrete\Core\Permission\Checker;
use Concrete\Core\Url\Resolver\Manager\ResolverManagerInterface;
use Concrete\Core\Validation\CSRF\Token;
use Concrete\Package\BlocksCloner\Controller\AbstractController;
use Concrete\Package\BlocksCloner\Edit\Context;
use Concrete\Package\BlocksCloner\ImportChecker;
use Concrete\Package\BlocksCloner\XmlParser;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use SimpleXMLElement;
use Symfony\Component\HttpFoundation\File\UploadedFile;
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
        $areaHandle = (string) $this->request->query->get('aHandle');
        if ($areaHandle === '') {
            throw new UserMessageException(t('Access Denied'));
        }
        $area = Area::get($this->getPage(), $areaHandle);
        if (!$area || $area->isError() || $area->getAreaID() != $aID) {
            throw new UserMessageException(t('Access Denied'));
        }
        $importChecker = $this->app->make(ImportChecker::class);
        $importChecker->checkArea($area);
        $this->set('area', $area);
        $this->set('token', $this->app->make(Token::class));
        $resolverManager = $this->app->make(ResolverManagerInterface::class);
        $page = Page::getByPath('/dashboard/sitemap/full');
        if ($page && !$page->isError() && (new Checker($page))->canViewPage()) {
            $this->set('sitemapPageUrl', (string) $resolverManager->resolve([$page]));
        } else {
            $this->set('sitemapPageUrl', '');
        }
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
            $areaHandle = (string) $this->request->request->get('aHandle');
            if ($areaHandle === '') {
                throw new UserMessageException(t('Access Denied'));
            }
            $area = Area::get($this->getPage(), $areaHandle);
            if (!$area || $area->isError() || $area->getAreaID() != $aID) {
                throw new UserMessageException(t('Access Denied'));
            }
            $importChecker = $this->app->make(ImportChecker::class);
            $importChecker->checkArea($area);
            $xml = $this->request->request->get('xml');
            $sx = $this->loadXml($xml);
            $result = [
                'importToken' => $this->app->make(Token::class)->generate("blocks_cloner:import:{$this->cID}:{$areaHandle}:" . sha1($xml)),
            ];
            $parser = $this->app->make(XmlParser::class);
            $installedPackages = $this->getInstalledPackages();
            $result['blockTypes'] = [];
            $checker = new Checker($this->getPage());
            foreach ($parser->findBlockTypes($sx) as $blockType) {
                if (!$checker->canAddBlockType($blockType)) {
                    throw new UserMessageException(t("You can't add blocks of type %s to this page.", t($blockType->getBlockTypeName())));
                }
                $packageID = $blockType->getPackageID();
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
            foreach ($parser->findItems($sx) as $categoryKey => $items) {
                $result[$categoryKey] = [];
                foreach ($items as $itemKey => $item) {
                    $serialized = ['key' => $itemKey];
                    switch ($categoryKey) {
                        case XmlParser::KEY_FILES:
                            if ($item instanceof Version) {
                                $serialized += [
                                    'fID' => $item->getFileID(),
                                    'prefix' => $item->getPrefix(),
                                    'name' => $item->getFileName(),
                                ];
                            } else {
                                $serialized['error'] = $item;
                            }
                            break;
                        case XmlParser::KEY_PAGES:
                            if ($item instanceof Page) {
                                $serialized += [
                                    'cID' => (int) $item->getCollectionID(),
                                    'name' => (string) $item->getCollectionName(),
                                    'link' => (string) $item->getCollectionLink(),
                                ];
                            } else {
                                $serialized['error'] = $item;
                            }
                            break;
                        case XmlParser::KEY_PAGETYPES:
                            if ($item instanceof PageType) {
                                $serialized += [
                                    'name' => (string) t($item->getPageTypeName()),
                                    'id' => $item->getPageTypeID(),
                                ];
                            } else {
                                $serialized['error'] = $item;
                            }
                            break;
                        case XmlParser::KEY_PAGEFEEDS:
                            if ($item instanceof PageFeed) {
                                $serialized += [
                                    'title' => $item->getFeedDisplayTitle('text'),
                                ];
                            } else {
                                $serialized['error'] = $item;
                            }
                            break;
                        default:
                            $serialized['error'] = t('Unsuppored category: %s', $categoryKey);
                            break;
                    }
                    $result[$categoryKey][] = $serialized;
                }
            }

            return $this->app->make(ResponseFactoryInterface::class)->json($result);
        } catch (Exception $x) {
            return $this->buildErrorResponse($x);
        } catch (Throwable $x) {
            return $this->buildErrorResponse($x);
        }
    }
    /**
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function uploadFile()
    {
        try {
            $token = $this->app->make(Token::class);
            if (!$token->validate('blocks_cloner:import:uploadFile')) {
                throw new UserMessageException($token->getErrorMessage());
            }
            $file = $this->request->files->get('file');
            if (!$file) {
                throw new UserMessageException(t('No files uploaded'));
            }
            /** @var \Symfony\Component\HttpFoundation\File\UploadedFile $file */
            if (!$file->isValid()) {
                throw ImportException::fromErrorCode($file->getError());
            }
            $volatile = $this->app->make(VolatileDirectory::class);
            $fileInfos = $this->parseUploadedFile(
                $file,
                filter_var($this->request->request->get('decompressZip'), FILTER_VALIDATE_BOOLEAN),
                $volatile
            );
            $this->checkUploadedFiles($fileInfos);
            $importer = $this->app->make(FileImporter::class);
            $importOptions = $this->app->make(ImportOptions::class)
                ->setCanChangeLocalFile(true)
            ;
            $matches = null;
            foreach ($fileInfos as $fileInfo) {
                $name = $fileInfo['name'];
                $prefix = '';
                if (preg_match('/^([0-9]{12}]*)\_(.*)$/', $name, $matches)) {
                    $prefix = $matches[1];
                    $name = $matches[2];
                }
                $importOptions->setCustomPrefix($prefix);
                $importer->importLocalFile($fileInfo['file']->getPathname(), $name, $importOptions);
            }

            return $this->app->make(ResponseFactoryInterface::class)->json(true);
        } catch (Exception $x) {
            return $this->buildErrorResponse($x);
        } catch (Throwable $x) {
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
            $areaHandle = (string) $this->request->request->get('areaHandle');
            if ($areaHandle === '') {
                throw new UserMessageException(t('Access Denied'));
            }
            $token = $this->app->make(Token::class);
            if (!$token->validate("blocks_cloner:import:{$this->cID}:{$areaHandle}:" . sha1($xml))) {
                throw new UserMessageException($token->getErrorMessage());
            }
            $area = Area::get($this->getPage(), $areaHandle);
            if (!$area || $area->isError()) {
                throw new UserMessageException(t('Unable to find the requested area'));
            }
            $sx = $this->loadXml($xml);
            $context = Context::forWriting($this->getPage(), $area);
            $parser = $this->app->make(XmlParser::class);
            $blockType = $parser->getRootBlockType($xml);
            $initialBlockIDsInArea = $this->getBlockIDsInArea($context);
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
                $newBlock = $blockController->import($context->page, $context->area, $sx);
                $this->app->make('cache/request')->flush();
                $newBlockIDsInArea = $this->getBlockIDsInArea($context);
                if (!$newBlock) {
                    $deltaBlockIDs = array_diff($newBlockIDsInArea, $initialBlockIDsInArea);
                    if (count($deltaBlockIDs) !== 1) {
                        throw new UserMessageException(t('Failed to retrieve the ID of the new block'));
                    }
                    $newBlock = $this->getImportedBlock($context, array_shift($deltaBlockIDs));
                }
                if ($beforeBlockID) {
                    $newBlockIDsInArea = $this->sortBlockIDs((int) $newBlock->getBlockID(), $beforeBlockID, $newBlockIDsInArea);
                    $context->processArrangement($context->area->getAreaID(), $newBlock->getBlockID(), $newBlockIDsInArea);
                }
                $response = $this->app->make(ResponseFactoryInterface::class)->json(['bID' => (int) $newBlock->getBlockID()]);
                $cn->commit();
                $rollBack = false;
            } finally {
                if ($rollBack) {
                    $cn->rollBack();
                }
            }
        } catch (Exception $x) {
            return $this->buildErrorResponse($x);
        } catch (Throwable $x) {
            return $this->buildErrorResponse($x);
        }

        return $response;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getBlocksDesign()
    {
        try {
            $blockIDsByAreaHandles = json_decode(
                (string) $this->request->request->get('blockIDsByAreaHandles'),
                true,
                0 | (defined('JSON_THROW_ON_ERROR') ? JSON_THROW_ON_ERROR : 0)
            );
            if (empty($blockIDsByAreaHandles || !is_array($blockIDsByAreaHandles))) {
                throw new UserMessageException(t('Access Denied'));
            }
            $result = [];
            foreach ($blockIDsByAreaHandles as $areaHandle => $blockIDs) {
                $area = Area::get($this->getPage(), $areaHandle);
                if (!$area || $area->isError()) {
                    throw new UserMessageException(t('Unable to find the requested area'));
                }
                $context = Context::forReading($this->getPage(), $area);
                foreach ($blockIDs as $blockID) {
                    if (!is_int($blockID) || $blockID < 1) {
                        throw new UserMessageException(t('Access Denied'));
                    }
                    $block = $this->getImportedBlock($context, $blockID);
                    $customStyle = $block->getCustomStyle();
                    $customStyleSet = $customStyle ? $customStyle->getStyleSet() : null;
                    if (!$customStyleSet) {
                        continue;
                    }
                    $style = new CustomStyle($customStyleSet, $block, $this->getPage()->getCollectionThemeObject());
                    $css = (string) $style->getCSS();
                    if ($css === '') {
                        continue;
                    }
                    $result[] = [
                        'blockID' => $blockID,
                        'issID' => (int) $customStyleSet->getID(),
                        'htmlStyleElement' => $style->getStyleWrapper($css),
                    ];
                }
            }
            $response = $this->app->make(ResponseFactoryInterface::class)->json($result);
        } catch (Exception $x) {
            return $this->buildErrorResponse($x);
        } catch (Throwable $x) {
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
     * @return \Concrete\Core\Entity\Package[]
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
    private function getBlockIDsInArea(Context $state)
    {
        return array_map(
            static function (array $arr) { return (int) $arr['bID']; },
            $state->page->getBlockIDs($state->area->getAreaHandle())
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

    /**
     * @param bool $decompressZip
     *
     * @return array[] A list of ['name' => filename, 'file' => an instance of \SplFileInfo]
     */
    private function parseUploadedFile(UploadedFile $uploadedFile, $decompressZip, VolatileDirectory $volatile)
    {
        if (!preg_match('/\.zip$/i', $uploadedFile->getClientOriginalName()) || !$decompressZip) {
            return [
                ['name' => $uploadedFile->getClientOriginalName(), 'file' => $uploadedFile],
            ];
        }
        $zip = $this->app->make(Zip::class);
        $zip->unzip($uploadedFile->getPathname(), $volatile->getPath());

        $result = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($volatile->getPath(), \RecursiveDirectoryIterator::SKIP_DOTS | \RecursiveDirectoryIterator::CURRENT_AS_FILEINFO),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );
        foreach ($iterator as $file) {
            /** @var \SplFileInfo $file */
            $result[] = ['name' => $file->getFilename(), 'file' => $file];
        }
        if ($result === []) {
            throw new UserMessageException(t('The ZIP archive does not contain any file'));
        }

        return $result;
    }

    private function checkUploadedFiles(array $fileInfos)
    {
        $rootFolder = $this->app->make(Filesystem::class)->getRootFolder();
        if (!$rootFolder) {
            throw new UserMessageException(t('Failed to retrieve the Concrete root folder'));
        }
        $checker = new Checker($rootFolder);
        $fileService = $this->app->make('helper/file');
        foreach ($fileInfos as $fileInfo) {
            $fileExtension = $fileService->getExtension($fileInfo['name']);
            if (!$checker->canAddFileType($fileExtension)) {
                throw new ImportException(ImportException::E_FILE_INVALID_EXTENSION);
            }
        }
    }

    /**
     * @param int $blockID
     *
     * @return \Concrete\Core\Block\Block
     */
    private function getImportedBlock(Context $context, $blockID)
    {
        $block = Block::getByID($blockID, $context->page, $context->area);
        if (!$block || $block->isError()) {
            throw new Exception(('Unable to find the requested block'));
        }

        return $block;
    }
}
