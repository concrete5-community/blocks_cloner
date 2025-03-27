<?php

namespace Concrete\Package\BlocksCloner\Controller\Dialog;

use Concrete\Core\Area\Area;
use Concrete\Core\Block\Block;
use Concrete\Core\Entity\File\File;
use Concrete\Core\Entity\File\Version as FileVersion;
use Concrete\Core\Entity\Package;
use Concrete\Core\Error\UserMessageException;
use Concrete\Core\File\Service\VolatileDirectory;
use Concrete\Core\Permission\Checker;
use Concrete\Core\Url\Resolver\Manager\ResolverManagerInterface;
use Concrete\Package\BlocksCloner\Controller\AbstractController;
use Concrete\Package\BlocksCloner\CIF;
use Concrete\Package\BlocksCloner\XmlParser;
use Doctrine\ORM\EntityManagerInterface;
use ZipArchive;

defined('C5_EXECUTE') or die('Access Denied.');

class Export extends AbstractController
{
    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Controller\Controller::$viewPath
     */
    protected $viewPath = '/dialogs/export';

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
        $bID = $this->request->query->getInt('bID');
        if (!$bID) {
            throw new UserMessageException(t('Access Denied'));
        }
        $areaHandle = (string) $this->request->query->get('aHandle');
        if ($areaHandle === '') {
            throw new UserMessageException(t('Access Denied'));
        }
        $area = Area::get($this->getPage(), $areaHandle);
        if (!$area || $area->isError()) {
            throw new UserMessageException(t('Access Denied'));
        }
        $block = Block::getByID($bID);
        if (!$block || $block->isError()) {
            throw new UserMessageException(t('Access Denied'));
        }
        $block->setBlockCollectionObject($this->getPage());
        $block->setBlockAreaObject($area);
        $sx = simplexml_load_string('<root />');
        $block->export($sx);
        $this->app->make(CIF::class)->fixExportedBlocks($sx);
        $children = $sx->children();
        $blockElement = $children[0];
        $doc = new \DOMDocument('1.0');
        $doc->preserveWhiteSpace = false;
        $doc->formatOutput = true;
        $doc->loadXML($blockElement->asXML());
        $xml = $doc->saveXML();
        $xml = preg_replace('{^<\?xml[^>]*>\s}i', '', $xml);
        $this->set('xml', $xml);
        $parser = $this->app->make(XmlParser::class);
        $packages = $this->getInstalledPackages();
        $blockTypesAndPackages = [];
        foreach ($parser->findBlockTypes($xml) as $blockType) {
            $packageID = $blockType->getPackageID();
            $blockTypesAndPackages[] = [
                'blockType' => $blockType,
                'package' => $packageID ? $packages[$packageID] : null,
            ];
        }
        $this->set('blockTypesAndPackages', $blockTypesAndPackages);
        $this->set('references', $parser->findItems($sx));
        $this->set('resolverManager', $this->app->make(ResolverManagerInterface::class));
    }

    public function downloadFiles()
    {
        $fileVersions = $this->getFileVersionsToBeDownloaded(preg_split('/\D+/', (string) $this->request->query->get('fIDs'), -1, PREG_SPLIT_NO_EMPTY));
        if (count($fileVersions) === 1) {
            $this->sendFile($fileVersions[0]);
        } else {
            $this->sendZip($fileVersions);
        }
    }

    /**
     * @param array<string|int|mixed> $fileIDs
     *
     * @throws \Concrete\Core\Error\UserMessageException
     *
     * @return \Concrete\Core\Entity\File\Version[]
     */
    private function getFileVersionsToBeDownloaded(array $fileIDs)
    {
        $fileIDs = array_unique(array_map('intval', $fileIDs));
        if ($fileIDs === []) {
            throw new UserMessageException(t('Access Denied'));
        }
        $result = [];
        $em = $this->app->make(EntityManagerInterface::class);
        foreach ($fileIDs as $fileID) {
            $file = $em->find(File::class, $fileID);
            $fileVersion = $file === null ? null : $file->getApprovedVersion();
            if ($fileVersion === null) {
                throw new UserMessageException(t('Access Denied'));
            }
            $checker = new Checker($file);
            if (!$checker->canRead()) {
                throw new UserMessageException(t('Access Denied'));
            }
            $result[] = $fileVersion;
        }
        return $result;
    }

    /**
     * @return never
     */
    private function sendFile(FileVersion $fileVersion)
    {
        $volatile = $this->app->make(VolatileDirectory::class);
        $localFile = $volatile->getPath() . '/' . "{$fileVersion->getPrefix()}_{$fileVersion->getFilename()}";
        if (file_put_contents($localFile, $fileVersion->getFileContents()) === false) {
            throw new UserMessageException(t('Failed to the contents of the file'));
        }
        $this->app->make('helper/file')->forceDownload($localFile);
    }

    /**
     * @param \Concrete\Core\Entity\File\Version[] $fileVersions
     *
     * @return never
     */
    private function sendZip(array &$fileVersions)
    {
        $volatile = $this->app->make(VolatileDirectory::class);
        $zipFile = $volatile->getPath() . '/files.zip';
        $zipArchive = new ZipArchive();
        if (!$zipArchive->open($zipFile, ZipArchive::CREATE)) {
            throw new UserMessageException(t('Could not open with ZipArchive::CREATE'));
        }
        @ini_set('memory_limit', '-1');
        @set_time_limit(0);
        foreach ($fileVersions as $fileVersion) {
            $zipArchive->addFromString("{$fileVersion->getPrefix()}_{$fileVersion->getFilename()}", $fileVersion->getFileContents());
        }
        $zipArchive->close();
        unset($fileVersions);
        $this->app->make('helper/file')->forceDownload($zipFile);
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
}
