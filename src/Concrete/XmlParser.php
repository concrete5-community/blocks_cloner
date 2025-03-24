<?php

namespace Concrete\Package\BlocksCloner;

use Concrete\Core\Application\Application;
use Concrete\Core\Backup\ContentImporter\ValueInspector\Item;
use Concrete\Core\Entity\Block\BlockType\BlockType;
use Concrete\Core\Entity\File\Version;
use Concrete\Core\Error\UserMessageException;
use Concrete\Core\Permission\Checker;
use Doctrine\ORM\EntityManagerInterface;
use DOMDocument;
use DOMElement;
use SimpleXMLElement;

defined('C5_EXECUTE') or die('Access Denied.');

final class XmlParser
{
    const KEY_FILES = 'files';

    const KEY_PAGES = 'pages';

    const KEY_PAGETYPES = 'pageTypes';

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var \Concrete\Core\Entity\Block\BlockType\BlockType[]|null
     */
    private $installedBlockTypes;

    /**
     * @var \Concrete\Core\Backup\ContentImporter\ValueInspector\ValueInspectorInterface
     */
    private $valueInspector;

    public function __construct(
        EntityManagerInterface $entityManager,
        Application $valueInspectorProvider
    )
    {
        $this->entityManager = $entityManager;
        $this->valueInspector = $valueInspectorProvider->make('import/value_inspector');
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
    /**
     * @param \SimpleXMLElement|\DOMDocument|\DOMElement|string $xml
     *
     * @throws \Concrete\Core\Error\UserMessageException
     *
     * @return array
     */
    public function findItems($xml)
    {
        $xml = $this->ensureSimpleXMLElement($xml);
        $result = [];
        foreach ($this->extractStrings($xml) as $content) {
            $inspectionResult = $this->valueInspector->inspect($content);
            foreach ($inspectionResult->getMatchedItems() as $item) {
                $this->parseFoundItem($item, $result);
            }
        }
        if (isset($result[self::KEY_FILES])) {
            $this->filterAccessibleFileVersions($result[self::KEY_FILES]);
        }

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

    private function parseFoundItem(Item\ItemInterface $item, array &$result)
    {
        if ($item instanceof Item\FileItem) {
            $this->parseFoundFile($item, $result);
        } elseif ($item instanceof Item\PageItem) {
            $this->parseFoundPage($item, $result);
        } elseif ($item instanceof Item\PageTypeItem) {
            $this->parseFoundPageType($item, $result);
        } else {
            throw new UserMessageException(t('Unable to handle items of type %s', $item->getDisplayName()));
        }
    }

    private function parseFoundFile(Item\FileItem $item, array &$result)
    {
        if (!isset($result[self::KEY_FILES])) {
            $result[self::KEY_FILES] = [];
        }
        $key = $item->getReference();
        if (array_key_exists($key, $result[self::KEY_FILES])) {
            return;
        }
        $file = $item->getContentObject();
        if ($file === null) {
            $result[self::KEY_FILES][$key] = t('File not found');
        } else {
            $fileVersion = $file->getApprovedVersion();
            if ($fileVersion === null) {
                $result[self::KEY_FILES][$key] = t('File without an approved version');
            } else {
                $existingKey = array_search($fileVersion, $result[self::KEY_FILES], true);
                if ($existingKey === false || strlen((string) $existingKey) < strlen($key)) {
                    $result[self::KEY_FILES][$key] = $fileVersion;
                    if ($existingKey !== false) {
                        unset($result[self::KEY_FILES][$existingKey]);
                    }
                }
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

    private function parseFoundPage(Item\PageItem $item, array &$result)
    {
        if (!isset($result[self::KEY_PAGES])) {
            $result[self::KEY_PAGES] = [];
        }
        $key = '/' . trim($item->getReference(), '/');
        if (array_key_exists($key, $result[self::KEY_PAGES])) {
            return;
        }
        $page = $item->getContentObject();
        $result[self::KEY_PAGES][$key] = $page ?: t('Page not found');
    }

    private function parseFoundPageType(Item\PageTypeItem $item, array &$result)
    {
        if (!isset($result[self::KEY_PAGETYPES])) {
            $result[self::KEY_PAGETYPES] = [];
        }
        $key = $item->getReference();
        if (array_key_exists($key, $result[self::KEY_PAGETYPES])) {
            return;
        }
        $pageType = $item->getContentObject();
        $result[self::KEY_PAGETYPES][$key] = $pageType ?: t('Page Type not found');
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
