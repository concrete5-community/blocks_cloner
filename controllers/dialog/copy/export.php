<?php

namespace Concrete\Package\BlocksCloner\Controller\Dialog\Copy;

use Concrete\Core\Block\Block;
use Concrete\Core\Entity\File\File;
use Concrete\Core\Error\UserMessageException;
use Concrete\Core\File\Service\VolatileDirectory;
use Concrete\Core\Permission\Checker;
use Concrete\Core\Url\Resolver\Manager\ResolverManagerInterface;
use Concrete\Package\BlocksCloner\Controller\AbstractController;
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
    protected $viewPath = '/dialogs/copy/export';

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
        $block = Block::getByID($bID);
        if (!$block || $block->isError()) {
            throw new UserMessageException(t('Access Denied'));
        }
        $sx = simplexml_load_string('<root />');
        $block->export($sx);
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
        $this->set('pages', $parser->findPages($sx));
        $this->set('fileVersions', $parser->findFileVersions($sx));
        $this->set('resolverManager', $this->app->make(ResolverManagerInterface::class));
    }

    public function downloadFiles()
    {
        $fileVersions = $this->getFileVersionsToBeDownloaded(preg_split('/\D+/', (string) $this->request->query->get('fIDs'), -1, PREG_SPLIT_NO_EMPTY));
        $this->sendZip($fileVersions);
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
}
