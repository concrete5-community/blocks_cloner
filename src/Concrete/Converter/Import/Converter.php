<?php

namespace Concrete\Package\BlocksCloner\Converter\Import;

use Concrete\Package\BlocksCloner\Converter\Import;
use SimpleXMLElement;

defined('C5_EXECUTE') or die('Access Denied.');

class Converter
{
    /**
     * @var \Concrete\Package\BlocksCloner\Converter\Import\Converter\FontAwesome|null
     */
    private $fontAwesome = null;

    /**
     * @param \Concrete\Package\BlocksCloner\Converter\Import[] $converters
     *
     * @return void
     */
    public function apply(SimpleXMLElement $sx, array $converters)
    {
        $this->convertBlocks($sx, $converters);
    }

    /**
     * @param \Concrete\Package\BlocksCloner\Converter\Import[] $converters
     *
     * @return void
     */
    private function convertBlocks(SimpleXMLElement $sx, array $converters)
    {
        $blockConverters = [];
        foreach ($converters as $converter) {
            foreach ($converter->getBlockTypes() as $handle => $btc) {
                if (isset($blockConverters[$handle])) {
                    $blockConverters[$handle][] = $btc;
                } else {
                    $blockConverters[$handle] = [$btc];
                }
            }
        }
        if ($blockConverters === []) {
            return;
        }
        $blockElements = $this->extractBlockElements($sx);
        if ($blockElements === []) {
            return;
        }
        foreach ($blockElements as $blockElement) {
            $type = (string) $blockElement['type'];
            if (isset($blockConverters[$type])) {
                foreach ($blockConverters[$type] as $blockConverter) {
                    $this->convertBlock($blockElement, $blockConverter);
                }
            }
        }
    }

    /**
     * @return \SimpleXMLElement[]
     */
    private function extractBlockElements(SimpleXMLElement $sx)
    {
        $result = [];
        $walk = null;
        $walk = static function (SimpleXMLElement $el, $parentEl = null) use (&$walk, &$result) {
            switch ($el->getName()) {
                case 'block':
                    $result[] = $el;
                    break;
                case 'data':
                    if ($parentEl !== null && $parentEl->getName() === 'block') {
                        return;
                    }
                    break;
            }
            foreach ($el->children() as $child) {
                $walk($child, $el);
            }
        };
        $walk($sx);

        return $result;
    }

    /**
     * @param string $name
     *
     * @return \SimpleXMLElement[]
     */
    private function listChildElements(SimpleXMLElement $el, $name)
    {
        $result = [];
        foreach ($el->children() as $child) {
            if ($child->getName() === $name) {
                $result[] = $el;
            }
        }

        return $result;
    }

    /**
     * @param string $name
     *
     * @return \SimpleXMLElement|null
     */
    private function getFirstChildElement(SimpleXMLElement $el, $name)
    {
        $all = $this->listChildElements($el, $name);

        return $all === [] ? null : $all[0];
    }

    /**
     * @return void
     */
    private function convertBlock(SimpleXMLElement $xBlock, Import\BlockType $converter)
    {
        /** @var \Concrete\Package\BlocksCloner\Converter\Import\BlockType $converter */
        if (($newBlockTypeHandle = $converter->getNewBlockTypeHandle()) !== '') {
            $xBlock['type'] = $newBlockTypeHandle;
        }
        if (($templateRemappings = $converter->getTemplateRemappings()) !== []) {
            $this->applyBlockTemplateRemappings($xBlock, $templateRemappings);
        }
        if (($fieldList = $converter->getAddRecordFields()) !== []) {
            $this->addRecordFields($xBlock, $fieldList);
        }
        if (($fieldList = $converter->getEnsureIntegerFields()) !== []) {
            $this->ensureIntegerFields($xBlock, $fieldList);
        }
        if (($fieldList = $converter->getRemoveRecordFields()) !== []) {
            $this->removeRecordFields($xBlock, $fieldList);
        }
        if (($fieldList = $converter->getFontAwesome4to5Fields()) !== []) {
            $this->convertFontAwesome4to5($xBlock, $fieldList);
        }
        if (($map = $converter->getRenameDataTables()) !== []) {
            $this->renameDataTables($xBlock, $map);
        }
        if (($customConversion = $converter->getCustomConversion()) !== null) {
            $customConversion($xBlock);
        }
    }

    /**
     * @return void
     */
    private function applyBlockTemplateRemappings(SimpleXMLElement $xBlock, array $templateRemappings)
    {
        $currentTemplateHandle = preg_replace('\.php$/', '', (string) $xBlock['custom-template']);
        if (isset($templateRemappings[$currentTemplateHandle])) {
            $remapTo = $templateRemappings[$currentTemplateHandle];
        } elseif (isset($templateRemappings["{$currentTemplateHandle}.php"])) {
            $remapTo = $templateRemappings["{$currentTemplateHandle}.php"];
        } else {
            return;
        }
        if ($remapTo['newTemplate'] === '') {
            unset($xBlock['custom-template']);
        } elseif ($remapTo['newTemplate'] !== null) {
            $xBlock['custom-template'] = preg_replace('\.php$/', '', $remapTo['newTemplate']) . '.php';
        }
        if ($remapTo['newCustomClasses'] !== []) {
            $xStyle = $this->getFirstChildElement($xBlock, 'style') ?: $xBlock->addChild('style');
            $xCustomClass = $this->getFirstChildElement($xStyle, 'customClass') ?: $xStyle->addChild('customClass');
            $oldCustomClasses = preg_split('/\s+/', (string) $xCustomClass, -1, PREG_SPLIT_NO_EMPTY);
            $customClassesToBeAdded = array_diff($remapTo['newCustomClasses'], $oldCustomClasses);
            if ($customClassesToBeAdded !== []) {
                $this->setSimpleXMLElementValue($xCustomClass, implode(' ', array_merge($oldCustomClasses, $customClassesToBeAdded)));
            }
        }
    }

    /**
     * @return void
     */
    private function addRecordFields(SimpleXMLElement $xBlock, array $fieldList)
    {
        foreach ($this->listChildElements($xBlock, 'data') as $xData) {
            $tableName = (string) $xData['table'];
            $fields = isset($fieldList[$tableName]) ? $fieldList[$tableName] : [];
            if ($fields === []) {
                continue;
            }
            foreach ($this->listChildElements($xData, 'record') as $xRecord) {
                $existingFields = [];
                foreach ($xRecord->children() as $child) {
                    $existingFields[] = $child->getName();
                }
                foreach ($fields as $fieldName => $fieldValue) {
                    if (in_array($fieldName, $existingFields, true)) {
                        continue;
                    }
                    $xField = $xRecord->addChild($fieldName);
                    $this->setSimpleXMLElementValue($xField, $fieldValue);
                }
            }
        }
    }

    /**
     * @return void
     */
    private function ensureIntegerFields(SimpleXMLElement $xBlock, array $fieldList)
    {
        foreach ($this->listChildElements($xBlock, 'data') as $xData) {
            $tableName = (string) $xData['table'];
            $fields = isset($fieldList[$tableName]) ? $fieldList[$tableName] : [];
            if ($fields === []) {
                continue;
            }
            foreach ($this->listChildElements($xData, 'record') as $xRecord) {
                foreach ($xRecord->children() as $xField) {
                    if (!in_array($xField->getName(), $fields, true)) {
                        continue;
                    }
                    $string = (string) $xField;
                    $int = (int) $string;
                    if ($string !== (string) $int) {
                        $this->setSimpleXMLElementValue($xField, '0');
                    }
                }
            }
        }
    }

    /**
     * @return void
     */
    private function removeRecordFields(SimpleXMLElement $xBlock, array $fieldList)
    {
        foreach ($this->listChildElements($xBlock, 'data') as $xData) {
            $tableName = (string) $xData['table'];
            $fields = isset($fieldList[$tableName]) ? $fieldList[$tableName] : [];
            if ($fields === []) {
                continue;
            }
            foreach ($this->listChildElements($xData, 'record') as $xRecord) {
                foreach ($fields as $field) {
                    foreach ($xRecord->{$field} as $xField) {
                        unset($xField[0]);
                    }
                }
            }
        }
    }

    /**
     * @return void
     */
    private function convertFontAwesome4to5(SimpleXMLElement $xBlock, array $fieldList)
    {
        $fontAwesome = $this->getFontAwesome();
        foreach ($this->listChildElements($xBlock, 'data') as $xData) {
            $tableName = (string) $xData['table'];
            $fields = isset($fieldList[$tableName]) ? $fieldList[$tableName] : [];
            if ($fields === []) {
                continue;
            }
            foreach ($this->listChildElements($xData, 'record') as $xRecord) {
                foreach ($xRecord->children() as $xField) {
                    if (!in_array($xField->getName(), $fields, true)) {
                        continue;
                    }
                    $currentIconClass = trim((string) $xField);
                    if ($currentIconClass === '') {
                        continue;
                    }
                    $newIconClass = $fontAwesome->convertFontAwesomeIcon4To5($currentIconClass);
                    if ($newIconClass !== '') {
                        $this->setSimpleXMLElementValue($xField, $newIconClass);
                    }
                }
            }
        }
    }

    /**
     * @return void
     */
    private function renameDataTables(SimpleXMLElement $xBlock, array $map)
    {
        foreach ($this->listChildElements($xBlock, 'data') as $xData) {
            $currentDataTableName = (string) $xData['table'];
            if (!isset($map[$currentDataTableName])) {
                continue;
            }
            $xData['table'] = $map[$currentDataTableName];
        }
    }

    /**
     * @return \Concrete\Package\BlocksCloner\Converter\Import\Converter\FontAwesome
     */
    private function getFontAwesome()
    {
        if ($this->fontAwesome === null) {
            $this->fontAwesome = new Converter\FontAwesome();
        }

        return $this->fontAwesome;
    }

    /**
     * @param string $value
     *
     * @return void
     */
    private function setSimpleXMLElementValue(SimpleXMLElement $el, $value)
    {
        $el[0] = (string) $value;
    }
}
