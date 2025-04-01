<?php

namespace Concrete\Package\BlocksCloner\Controller\Dialog\Export;

use Concrete\Core\Entity\File\File;
use Concrete\Core\Entity\File\Version as FileVersion;
use Concrete\Core\Error\UserMessageException;
use Concrete\Core\File\Service\VolatileDirectory;
use Concrete\Core\Permission\Checker;
use Concrete\Package\BlocksCloner\Controller\Dialog\Export;
use Doctrine\ORM\EntityManagerInterface;
use ZipArchive;

defined('C5_EXECUTE') or die('Access Denied.');

class Files extends Export
{
    public function view()
    {
        parent::view();
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
}
