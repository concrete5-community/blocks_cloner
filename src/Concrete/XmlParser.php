<?php

namespace Concrete\Package\BlocksCloner;

use Concrete\Core\Backup\ContentImporter\ValueInspector\InspectionRoutine\FileRoutine;
use Concrete\Core\Backup\ContentImporter\ValueInspector\InspectionRoutine\ImageRoutine;
use Concrete\Core\Backup\ContentImporter\ValueInspector\InspectionRoutine\PageRoutine;
use Concrete\Core\Backup\ContentImporter\ValueInspector\InspectionRoutine\PictureRoutine;
use Concrete\Core\Entity\Block\BlockType\BlockType;
use Concrete\Core\Entity\File\File;
use Concrete\Core\Entity\File\Version;
use Concrete\Core\Error\UserMessageException;
use Concrete\Core\Page\Page;
use Concrete\Core\Permission\Checker;
use Doctrine\ORM\EntityManagerInterface;
use DOMDocument;
use DOMElement;
use SimpleXMLElement;

defined('C5_EXECUTE') or die('Access Denied.');

final class XmlParser
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var \Concrete\Core\Backup\ContentImporter\ValueInspector\InspectionRoutine\PageRoutine
     */
    private $pageInspector;

    /**
     * @var \Concrete\Core\Backup\ContentImporter\ValueInspector\InspectionRoutine\FileRoutine
     */
    private $fileInspector;

    /**
     * @var \Concrete\Core\Backup\ContentImporter\ValueInspector\InspectionRoutine\ImageRoutine
     */
    private $imageInspector;

    /**
     * @var \Concrete\Core\Backup\ContentImporter\ValueInspector\InspectionRoutine\PictureRoutine
     */
    private $pictureInspector;

    /**
     * @var \Concrete\Core\Entity\Block\BlockType\BlockType[]|null
     */
    private $installedBlockTypes;

    public function __construct(
        EntityManagerInterface $entityManager,
        PageRoutine $pageInspector,
        FileRoutine $fileInspector,
        ImageRoutine $imageInspector,
        PictureRoutine $pictureInspector
    )
    {
        $this->entityManager = $entityManager;
        $this->pageInspector = $pageInspector;
        $this->fileInspector = $fileInspector;
        $this->imageInspector = $imageInspector;
        $this->pictureInspector = $pictureInspector;
    }

    /**
     * @param \SimpleXMLElement|\DOMDocument|\DOMElement|string $xml
     *
     * @throws \Concrete\Core\Error\UserMessageException
     *
     * @return \Concrete\Core\Entity\Block\BlockType\BlockType[]
     */
    public function findBlockTypes($xml)
    {
        return $this->listBlockTypes($xml, false);
    }

    /**
     * @param \SimpleXMLElement|\DOMDocument|\DOMElement|string $xml
     *
     * @throws \Concrete\Core\Error\UserMessageException
     *
     * @return \Concrete\Core\Entity\Block\BlockType\BlockType
     */
    public function getRootBlockType($xml)
    {
        $blockTypes = $this->listBlockTypes($xml, true);

        return $blockTypes[0];
    }

    /**
     * @param \SimpleXMLElement|\DOMDocument|\DOMElement|string $xml
     *
     * @throws \Concrete\Core\Error\UserMessageException
     *
     * @return array Array keys are the page paths, array values are Page instances (or a string in case of errors)
     */
    public function findPages($xml)
    {
        $xml = $this->ensureSimpleXMLElement($xml);
        $result = [];
        foreach ($this->extractStrings($xml) as $str) {
            $this->extractPages($str, $result);
        }

        return $result;
    }

    /**
     * @param \SimpleXMLElement|\DOMDocument|\DOMElement|string $xml
     *
     * @throws \Concrete\Core\Error\UserMessageException
     *
     * @return array Array keys are the file identifiers, array values are File Version instances (or a string in case of errors)
     */
    public function findFileVersions($xml)
    {
        $xml = $this->ensureSimpleXMLElement($xml);
        $result = [];
        foreach ($this->extractStrings($xml) as $str) {
            $this->extractFileVersions($str, $result);
        }
        $this->removeDuplicatedFileVersions($result);
        $this->filterAccessibleFileVersions($result);

        return $result;
    }

    /**
     * @param \SimpleXMLElement|\DOMDocument|\DOMElement|string $xml
     *
     * @throws \Concrete\Core\Error\UserMessageException
     *
     * @return \SimpleXMLElement
     */
    private function ensureSimpleXMLElement($xml)
    {
        if ($xml instanceof SimpleXMLElement) {
            return $xml;
        }
        if ($xml instanceof DOMDocument) {
            $xml = $xml->documentElement;
        }
        if ($xml instanceof DOMElement) {
            $simpleXml = simplexml_import_dom($xml);
            if (!$simpleXml) {
                throw new UserMessageException(t('Failed to parse XML'));
            }
        }
        if (is_string($xml)) {
            $simpleXml = simplexml_load_string($xml);
            if (!$simpleXml) {
                throw new UserMessageException(t('Failed to parse XML'));
            }
            return $simpleXml;
        }

        throw new UserMessageException(t('Failed to parse XML'));
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

    private function extractStrings(SimpleXMLElement $el)
    {
        foreach ($el->attributes() as $value) {
            if (is_string($value)) {
                $value = trim($value);
                if ($value !== '') {
                    yield $value;
                }
            }
        }
        $value= trim((string) $el);
        if ($value !== '') {
            yield $value;
        }
        foreach ($el->children() as $child) {
            foreach ($this->extractStrings($child) as $value) {
                yield $value;
            }
        }
    }

    /**
     * @param string $str
     *
     * @return void
     */
    private function extractPages($str, array &$result)
    {
        // Process {ccm:export:page:/path/to/page}
        $items = $this->pageInspector->match($str);
        /**
         * @var \Concrete\Core\Backup\ContentImporter\ValueInspector\Item\PageItem[] $items
         */
        foreach ($items as $item) {
            $key = '/' . trim($item->getReference(), '/');
            if (!array_key_exists($key, $result)) {
                $page = $item->getContentObject();
                $result[$key] = $page instanceof Page && !$page->isError() ? $page : t('Page not found');
            }
        }
    }

    /**
     * @param string $str
     *
     * @return void
     */
    private function extractFileVersions($str, array &$result)
    {
        $items = array_merge(
            // Process {ccm:export:file:123456789012:filename.png}
            $this->fileInspector->match($str),
            // Process {ccm:export:image:123456789012:filename.png}
            $this->imageInspector->match($str),
            // Process <concrete-picture file="filename.png" />
            $this->pictureInspector->match($str)
        );
        /**
         * @var \Concrete\Core\Backup\ContentImporter\ValueInspector\Item\FileItem[]|\Concrete\Core\Backup\ContentImporter\ValueInspector\Item\ImageItem[]|\Concrete\Core\Backup\ContentImporter\ValueInspector\Item\PictureItem[] $items
         */
        foreach ($items as $item) {
            $key = $item->getReference();
            if (!array_key_exists($key, $result)) {
                $file = $item->getContentObject();
                $fileVersion = $file instanceof File ? $file->getApprovedVersion() : null;
                $result[$key] = $fileVersion instanceof Version ? $fileVersion : t('File not found');
            }
        }
    }

    private function removeDuplicatedFileVersions(array &$list)
    {
        $keys = array_keys($list);
        $remainingItems = $list;
        usort($keys, static function ($a, $b) { return mb_strlen((string) $a) - mb_strlen((string) $b); });
        while (true) {
            $key = array_shift($keys);
            if ($keys === []) {
                break;
            }
            $item = $remainingItems[$key];
            unset($remainingItems[$key]);
            if ($item instanceof Version && in_array($item, $remainingItems, true)) {
                unset($list[$key]);
            }
        }
    }

    private function filterAccessibleFileVersions(array &$list)
    {
        foreach (array_keys($list) as $key) {
            $fileVersion = $list[$key];
            if (!$fileVersion instanceof Version) {
                continue;
            }
            $checker = new Checker($fileVersion->getFile());
            if (!$checker->canRead()) {
                $list[$key] = t('Access Denied');
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
            $repo = $this->entityManager->getRepository(BlockType::class);
            foreach ($repo->findAll() as $blockType) {
                $installedBlockTypes[$blockType->getBlockTypeHandle()] = $blockType;
            }
            $this->installedBlockTypes = $installedBlockTypes;
        }

        return $this->installedBlockTypes;
    }

    /**
     * @param \SimpleXMLElement|\DOMDocument|\DOMElement|string $xml
     * @param bool $onlyFirst
     *
     * @throws \Concrete\Core\Error\UserMessageException
     *
     * @return \Concrete\Core\Entity\Block\BlockType\BlockType[]
     */
    private function listBlockTypes($xml, $onlyFirst)
    {
        $xml = $this->ensureSimpleXMLElement($xml);
        if ($xml->getName() !== 'block') {
            throw new UserMessageException(t('The XML does not represent a block in ConcreteCMS CIF Format'));
        }
        $result = [];
        $errors = [];
        $blockTypes = $this->getInstalledBlockTypes();
        foreach ($this->listBlockNodes($xml) as $xBlock) {
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
            if ($onlyFirst) {
                break;
            }
        }
        if ($errors !== []) {
            throw new UserMessageException(implode("\n", $errors));
        }

        return $result;
    }
}
