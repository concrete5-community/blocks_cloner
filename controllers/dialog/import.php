<?php

namespace Concrete\Package\BlocksCloner\Controller\Dialog;

use Concrete\Core\Area\Area;
use Concrete\Core\Area\CustomStyle as AreaCustomStyle;
use Concrete\Core\Block\Block;
use Concrete\Core\Block\CustomStyle as BlockCustomStyle;
use Concrete\Core\Database\Connection\Connection;
use Concrete\Core\Entity\Block\BlockType\BlockType;
use Concrete\Core\Error\UserMessageException;
use Concrete\Core\File\Filesystem;
use Concrete\Core\File\Import\FileImporter;
use Concrete\Core\File\Import\ImportException;
use Concrete\Core\File\Import\ImportOptions;
use Concrete\Core\File\Service\VolatileDirectory;
use Concrete\Core\File\Service\Zip;
use Concrete\Core\Http\ResponseFactoryInterface;
use Concrete\Core\Permission\Checker;
use Concrete\Core\StyleCustomizer\Inline\StyleSet;
use Concrete\Core\User\User;
use Concrete\Core\Validation\CSRF\Token;
use Concrete\Package\BlocksCloner\Edit\Context;
use Concrete\Package\BlocksCloner\Import\Enviro;
use Concrete\Package\BlocksCloner\Import\LoadXmlTrait;
use Concrete\Package\BlocksCloner\UI\Controller\Dialog;
use Concrete\Package\BlocksCloner\XmlParser;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use SimpleXMLElement;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Throwable;

defined('C5_EXECUTE') or die('Access Denied.');

class Import extends Dialog
{
    use LoadXmlTrait;

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Controller\Controller::$viewPath
     */
    protected $viewPath = '/dialogs/import';

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Package\BlocksCloner\UI\Controller::view()
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
            $areaHandle = (string) $this->request->request->get('aHandle');
            if ($areaHandle === '') {
                throw new UserMessageException(t('Access Denied'));
            }
            $area = Area::get($this->getPage(), $areaHandle);
            if (!$area || $area->isError() || $area->getAreaID() != $aID) {
                throw new UserMessageException(t('Access Denied'));
            }
            $xml = $this->request->request->get('xml');
            $sx = $this->loadXml($xml);
            $importType = $this->extractImportType($sx);
            $result = [
                'importType' => $importType,
                'importToken' => $this->app->make(Token::class)->generate("blocks_cloner:import:{$importType}:{$this->cID}:{$areaHandle}:" . sha1($xml)),
            ];
            $parser = $this->app->make(XmlParser::class);
            $references = $parser->extractReferences($sx);
            if (isset($references[XmlParser::KEY_BLOCKTYPES])) {
                $checker = new Checker($this->getPage());
                $references[XmlParser::KEY_BLOCKTYPES] = array_map(
                    static function ($blockType) use ($checker) {
                        if ($blockType instanceof BlockType && !$checker->canAddBlockType($blockType)) {
                            return t("You can't add blocks of type %s to this page.", t($blockType->getBlockTypeName()));
                        }

                        return $blockType;
                    },
                    $references[XmlParser::KEY_BLOCKTYPES]
                );
                foreach ($references[XmlParser::KEY_BLOCKTYPES] as $blockType) {
                    if (!$blockType instanceof BlockType) {
                        $result['importToken'] = '';
                    }
                }
            }
            $result['references'] = $this->serializeReferences($references);

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
            $uploadToFolderID = $this->request->request->getInt('uploadToFolder');
            if ($uploadToFolderID < 1) {
                throw new UserMessageException(t('Access Denied'));
            }
            $fileFolder = $this->resolveUploadFolder($uploadToFolderID, false);
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
                !$this->canStoreZipFilesInFileManager() || filter_var($this->request->request->get('decompressZip'), FILTER_VALIDATE_BOOLEAN),
                $volatile
            );
            $this->checkUploadedFiles($fileInfos);
            $importer = $this->app->make(FileImporter::class);
            $importOptions = $this->app->make(ImportOptions::class)
                ->setCanChangeLocalFile(true)
                ->setImportToFolder($fileFolder)
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
    public function importBlock()
    {
        try {
            $enviro = $this->app->make(Enviro::class, ['page' => $this->getPage(), 'importType' => 'block']);
            $cn = $this->app->make(Connection::class);
            $rollBack = true;
            $cn->beginTransaction();
            try {
                $context = Context::forWriting($this->getPage(), $enviro->area);
                $newBlock = $this->importXBlock($enviro->sx, $context, $enviro);
                $response = $this->app->make(ResponseFactoryInterface::class)->json(['newBlockIDs' => [(int) $newBlock->getBlockID()]]);
                $cn->commit();
                $rollBack = false;
            } finally {
                if ($rollBack) {
                    $cn->rollBack();
                }
            }

            return $response;
        } catch (Exception $x) {
            return $this->buildErrorResponse($x);
        } catch (Throwable $x) {
            return $this->buildErrorResponse($x);
        }
    }

    /**
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function importArea()
    {
        try {
            $enviro = $this->app->make(Enviro::class, ['page' => $this->getPage(), 'importType' => 'area']);
            $cn = $this->app->make(Connection::class);
            $rollBack = true;
            $cn->beginTransaction();
            try {
                $areaStyleData = [];
                $context = Context::forWriting($this->getPage(), $enviro->area);
                if (isset($enviro->sx->style)) {
                    $oldCustomStyle = $this->getPage()->getAreaCustomStyle($context->area);
                    $oldCustomStyleSet = $oldCustomStyle ? $oldCustomStyle->getStyleSet() : null;
                    if ($oldCustomStyleSet) {
                        $areaStyleData['oldAreaStyleInlineStylesetID'] = (int) $oldCustomStyleSet->getID();
                    }
                    $newCustomStyleSet = StyleSet::import($enviro->sx->style);
                    $this->getPage()->setCustomStyleSet($context->pageSpecificArea, $newCustomStyleSet);
                    $newCustomStyle = new AreaCustomStyle($newCustomStyleSet, $context->pageSpecificArea, $this->getPage()->getCollectionThemeObject());
                    $newCss = (string) $newCustomStyle->getCSS();
                    if ($newCss !== '') {
                        $areaStyleData += [
                            'newAreaStyleInlineStylesetID' => (int) $newCustomStyleSet->getID(),
                            'newAreaHtmlStyleElement' => $newCustomStyle->getStyleWrapper($newCss),
                            'newAreaContainerClass' => $newCustomStyle->getContainerClass(),
                        ];
                    }
                }
                $xBlockList = [];
                if (isset($enviro->sx->blocks) && isset($enviro->sx->blocks->block)) {
                    foreach ($enviro->sx->blocks->block as $xBlock) {
                        $xBlockList[] = $xBlock;
                    }
                }
                $newBlockIDs = [];
                foreach (array_reverse($xBlockList) as $xBlock) {
                    $newBlock = $this->importXBlock($xBlock, $context, $enviro);
                    $newBlockID = (int) $newBlock->getBlockID();
                    $newBlockIDs[] = $newBlockID;
                    $enviro->beforeBlockID = $newBlockID;
                }
                $response = $this->app->make(ResponseFactoryInterface::class)->json(
                    $areaStyleData + [
                        'newBlockIDs' => $newBlockIDs,
                    ]
                );
                $cn->commit();
                $rollBack = false;
            } finally {
                if ($rollBack) {
                    $cn->rollBack();
                }
            }

            return $response;
        } catch (Exception $x) {
            return $this->buildErrorResponse($x);
        } catch (Throwable $x) {
            return $this->buildErrorResponse($x);
        }
    }

    /**
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getDesigns()
    {
        try {
            $blockIDsByAreaHandles = json_decode(
                (string) $this->request->request->get('blockIDsByAreaHandles'),
                true,
                0 | (defined('JSON_THROW_ON_ERROR') ? JSON_THROW_ON_ERROR : 0)
            );
            if (!is_array($blockIDsByAreaHandles)) {
                throw new UserMessageException(t('Access Denied'));
            }
            $areas = json_decode(
                (string) $this->request->request->get('areas'),
                true,
                0 | (defined('JSON_THROW_ON_ERROR') ? JSON_THROW_ON_ERROR : 0)
            );
            if (!is_array($areas)) {
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
                    if ($area->isGlobalArea()) {
                        // See https://github.com/concretecms/concretecms/issues/3135
                        $block->setBlockAreaObject($area);
                    }
                    $customStyle = $block->getCustomStyle();
                    $customStyleSet = $customStyle ? $customStyle->getStyleSet() : null;
                    if (!$customStyleSet) {
                        continue;
                    }
                    $style = new BlockCustomStyle($customStyleSet, $block, $this->getPage()->getCollectionThemeObject());
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
            foreach ($areas as $rawArea) {
                $areaHandle = is_array($rawArea) && isset($rawArea['handle']) && is_string($rawArea['handle']) ? $rawArea['handle'] : '';
                if ($areaHandle === '') {
                    throw new UserMessageException(t('Access Denied'));
                }
                $areaID = is_array($rawArea) && isset($rawArea['id']) && is_int($rawArea['id']) ? $rawArea['id'] : 0;
                if ($areaID < 1) {
                    throw new UserMessageException(t('Access Denied'));
                }
                $area = Area::get($this->getPage(), $areaHandle);
                if (!$area || $area->isError() || $areaID !== (int) $area->getAreaID()) {
                    throw new UserMessageException(t('Unable to find the requested area'));
                }
                $context = Context::forReading($this->getPage(), $area);
                $customStyle = $this->getPage()->getAreaCustomStyle($context->area);
                $customStyleSet = $customStyle ? $customStyle->getStyleSet() : null;
                if (!$customStyleSet) {
                    continue;
                }
                $style = new AreaCustomStyle($customStyleSet, $area, $this->getPage()->getCollectionThemeObject());
                $css = (string) $style->getCSS();
                if ($css === '') {
                    continue;
                }
                $result[] = [
                    'areaID' => $areaID,
                    'issID' => (int) $customStyleSet->getID(),
                    'htmlStyleElement' => $style->getStyleWrapper($css),
                    'containerClass' => $style->getContainerClass(),
                ];
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
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function checkUploadFolder()
    {
        try {
            $requestedFolderID = $this->request->query->getInt('folderID');
            $fallbackToRoot = $this->request->query->getBoolean('fallbackToRoot');
            $folder = $this->resolveUploadFolder($requestedFolderID, $fallbackToRoot);
            $result = [
                'id' => (int) $folder->getTreeNodeID(),
                'name' => (string) $folder->getTreeNodeDisplayName('text'),
                'path' => $folder->getTreeNodeDisplayPath(),
            ];
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
        if (!$decompressZip || !preg_match('/\.zip$/i', $uploadedFile->getClientOriginalName())) {
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
                throw ImportException::fromErrorCode(ImportException::E_FILE_INVALID_EXTENSION);
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

    /**
     * @return string
     */
    private function extractImportType(SimpleXMLElement $doc)
    {
        switch ($doc->getName()) {
            case 'block':
                return 'block';
            case 'area':
                $structure = $this->extractChildElements($doc, ['style', 'blocks']);
                if ($structure === null || count($structure['style']) > 1 && count($structure['blocks']) > 1) {
                    break;
                }
                $xStyle = array_shift($structure['style']);
                $xBlocks = array_shift($structure['blocks']);
                if ($xBlocks !== null) {
                    $structure = $this->extractChildElements($xBlocks, ['block']);
                    if ($structure === null) {
                        break;
                    }
                    if ($structure['block'] === []) {
                        $xBlocks = null;
                    }
                }
                if ($xStyle === null && $xBlocks === null) {
                    throw new UserMessageException(t('The XML is empty'));
                }

                return 'area';
        }

        throw new UserMessageException(t('The XML is not valid'));
    }

    /**
     * @throws \Concrete\Core\Error\UserMessageException
     *
     * @return \Concrete\Core\Block\Block
     */
    private function importXBlock(SimpleXMLElement $xBlock, Context $context, Enviro $enviro)
    {
        $initialBlockIDsInArea = $this->getBlockIDsInArea($context);
        if ($enviro->beforeBlockID !== 0 && !in_array($enviro->beforeBlockID, $initialBlockIDsInArea, true)) {
            throw new UserMessageException(t('Unable to find the requested block'));
        }
        $blockTypeHandle = (string) $xBlock['type'];
        $blockType = $this->app->make(EntityManagerInterface::class)->getRepository(BlockType::class)->findOneBy(['btHandle' => $blockTypeHandle]);
        $blockController = $blockType->getController();
        if (!$blockController) {
            $blockType->loadController();
            $blockController = $blockType->getController();
        }
        /** @var \Concrete\Core\Block\BlockController $blockController */
        $initialBlockIDsInArea = $this->getBlockIDsInArea($context);
        $newBlock = $blockController->import($context->page, $context->area, $xBlock);
        $this->app->make('cache/request')->flush();
        $newBlockIDsInArea = $this->getBlockIDsInArea($context);
        if (!$newBlock) {
            $deltaBlockIDs = array_diff($newBlockIDsInArea, $initialBlockIDsInArea);
            if (count($deltaBlockIDs) !== 1) {
                throw new UserMessageException(t('Failed to retrieve the ID of the new block'));
            }
            $newBlock = $this->getImportedBlock($context, array_shift($deltaBlockIDs));
        }
        if ($enviro->beforeBlockID !== 0) {
            $newBlockIDsInArea = $this->sortBlockIDs((int) $newBlock->getBlockID(), $enviro->beforeBlockID, $newBlockIDsInArea);
            $context->page->processArrangement($context->area->getAreaID(), $newBlock->getBlockID(), $newBlockIDsInArea);
        }

        return $newBlock;
    }

    /**
     * @param int|null $id
     * @param bool $fallbackToRoot;
     *
     * @throws \Concrete\Core\Error\UserMessageException
     *
     * @return \Concrete\Core\Tree\Node\Type\FileFolder
     */
    private function resolveUploadFolder($id = null, $fallbackToRoot = false)
    {
        $service = $this->app->make(Filesystem::class);
        $folder = null;
        $id = empty($id) ? 0 : (int) $id;
        if ($id < 1) {
            $fallbackToRoot = true;
            $user = $this->app->make(User::class);
            if ($user->isRegistered()) {
                $userInfo = $user->getUserInfoObject();
                if ($userInfo && method_exists($userInfo, 'getUserHomeFolderId')) {
                    $id = (int) $user->getUserInfoObject()->getUserHomeFolderId();
                }
            }
        }
        if ($id > 0) {
            $folder = $service->getFolder($id);
            if (!$folder) {
                if (!$fallbackToRoot) {
                    throw new UserMessageException(t('Unable to find the folder specified'));
                }
            } else {
                $checker = new Checker($folder);
                if (!$checker->canAddFiles()) {
                    if (!$fallbackToRoot) {
                        throw new UserMessageException(t("You don't have the permission to upload files to the specified folder"));
                    }
                    $folder = null;
                }
            }
        }
        if (!$folder) {
            $folder = $service->getRootFolder();
            if (!$folder) {
                throw new UserMessageException(t("The root file folder doesn't exist"));
            }
            $checker = new Checker($folder);
            if (!$checker->canAddFiles()) {
                throw new UserMessageException(t("You don't have the permission to upload files"));
            }
        }

        return $folder;
    }

    /**
     * @return bool
     */
    private function canStoreZipFilesInFileManager()
    {
        return in_array('zip', $this->app->make('helper/concrete/file')->getAllowedFileExtensions(), true);
    }
}
