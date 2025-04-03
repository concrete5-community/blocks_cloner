<?php

namespace Concrete\Package\BlocksCloner;

use Concrete\Core\Editor\LinkAbstractor;
use Concrete\Core\File\Set\Set as FileSet;
use DOMElement;
use DOMXPath;
use SimpleXMLElement;

defined('C5_EXECUTE') or die('Access Denied.');

class ExportFixer
{
    /**
     * @var \Concrete\Package\BlocksCloner\PluginManager
     */
    private $pluginManager;

    public function __construct(PluginManager $pluginManager)
    {
        $this->pluginManager = $pluginManager;
    }

    public function fix(SimpleXMLElement $exported)
    {
        $converters = $this->getExportConverters();
        if ($converters === []) {
            return;
        }
        foreach ($this->listBlockElements($exported) as $blockElement) {
            $this->fixExportedBlock($blockElement, $converters);
        }
    }

    /**
     * @return \Concrete\Package\BlocksCloner\Converter\Export[]
     */
    protected function getExportConverters()
    {
        $result = [];
        foreach ($this->pluginManager->getPlugins(Plugin\ConvertExport::class) as $plugin) {
            /** @var \Concrete\Package\BlocksCloner\Plugin\ConvertExport $plugin */
            $result = array_merge($result, $plugin->getExportConverters());
        }

        return $result;
    }

    /**
     * @return \SimpleXMLElement[]|\Generator
     */
    private function listBlockElements(SimpleXMLElement $element)
    {
        $name = $element->getName();
        if ($name === 'block') {
            yield $element;
        }
        foreach ($element->children() as $child) {
            if ($name === 'data' && $child->getName() === 'record') {
                continue;
            }
            foreach ($this->listBlockElements($child) as $blockElement) {
                yield $blockElement;
            }
        }
    }

    /**
     * @param \Concrete\Package\BlocksCloner\Converter\Export[] $converters
     */
    private function fixExportedBlock(SimpleXMLElement $blockElement, array $converters)
    {
        $blockTypeHandle = isset($blockElement['type']) ? (string) $blockElement['type'] : '';
        $blockTypeConverters = [];
        foreach ($converters as $converter) {
            $blockTypeConverters = array_merge($blockTypeConverters, $converter->getBlockTypeConversions($blockTypeHandle));
        }
        if ($blockTypeConverters === []) {
            return;
        }
        $this->fixExportedBlockContent($blockElement, $blockTypeConverters);
        $this->fixExportedBlockFileSetID($blockElement, $blockTypeConverters);
    }

    /**
     * @param \Concrete\Package\BlocksCloner\Converter\Export\BlockType[] $converters
     */
    private function fixExportedBlockContent(SimpleXMLElement $blockElement, array $converters)
    {
        $domElement = dom_import_simplexml($blockElement);
        if (!$domElement instanceof DOMElement) {
            return;
        }
        $xpath = new DOMXPath($domElement->ownerDocument);
        foreach ($xpath->query('./data', $domElement) as $dataElement) {
            /** @var \DOMElement $dataElement */
            $tableName = (string) $dataElement->getAttribute('table');
            $fieldNames = [];
            foreach ($converters as $converter) {
                $fieldNames = array_merge($fieldNames, $converter->getContentFieldsForTable($tableName));
            }
            if ($fieldNames === []) {
                continue;
            }
            foreach ($xpath->query('./record/*', $dataElement) as $fieldElement) {
                /** @var \DOMElement $fieldElement */
                if (!in_array($fieldElement->nodeName, $fieldNames, true)) {
                    continue;
                }
                $originalTextContent = (string) $fieldElement->textContent;
                if (trim($originalTextContent) === '') {
                    continue;
                }
                $newTextContent = LinkAbstractor::export($originalTextContent);
                if ($newTextContent === $originalTextContent) {
                    continue;
                }
                $this->replaceElementValue($fieldElement, $newTextContent);
            }
        }
    }

    /**
     * @param \Concrete\Package\BlocksCloner\Converter\Export\BlockType[] $converters
     */
    private function fixExportedBlockFileSetID(SimpleXMLElement $blockElement, array $converters)
    {
        $domElement = dom_import_simplexml($blockElement);
        if (!$domElement instanceof DOMElement) {
            return;
        }
        $xpath = new DOMXPath($domElement->ownerDocument);
        foreach ($xpath->query('./data', $domElement) as $dataElement) {
            /** @var \DOMElement $dataElement */
            $tableName = (string) $dataElement->getAttribute('table');
            $fieldNames = [];
            foreach ($converters as $converter) {
                $fieldNames = array_merge($fieldNames, $converter->getFileSetIDFieldsForTable($tableName));
            }
            if ($fieldNames === []) {
                continue;
            }
            foreach ($xpath->query('./record/*', $dataElement) as $fieldElement) {
                /** @var \DOMElement $fieldElement */
                if (!in_array($fieldElement->nodeName, $fieldNames, true)) {
                    continue;
                }
                $originalTextContent = trim((string) $fieldElement->textContent);
                if (!preg_match('/^[1-9]\d{0,18}$/', $originalTextContent)) {
                    continue;
                }
                $fileSetID = (int) $originalTextContent;
                $fileSet = FileSet::getByID($fileSetID);
                if (!$fileSet) {
                    continue;
                }
                if ((int) $fileSet->getFileSetType() !== FileSet::TYPE_PUBLIC) {
                    continue;
                }
                $newTextContent = $fileSet->getFileSetName();
                $this->replaceElementValue($fieldElement, $newTextContent);
            }
        }
    }

    /**
     * @param string $newValue
     */
    private function replaceElementValue(DOMElement $element, $newValue)
    {
        $newValue = (string) $newValue;
        $element->nodeValue = '';
        if ($newValue === '') {
            return;
        }
        if (strpbrk($newValue, '&<>') !== false) { // '>' is not strictly required, only ']]>' is, but libxml2 escapes even '>' alone
            $cdata = $element->ownerDocument->createCDataSection($newValue);
            $element->appendChild($cdata);
        } else {
            $textNode = $element->ownerDocument->createTextNode($newValue);
            $element->appendChild($textNode);
        }
    }
}
