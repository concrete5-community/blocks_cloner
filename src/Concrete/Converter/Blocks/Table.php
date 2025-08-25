<?php

namespace Concrete\Package\BlocksCloner\Converter\Blocks;

use Concrete\Core\Editor\LinkAbstractor;
use Concrete\Core\File\Set\Set as FileSet;
use Concrete\Package\BlocksCloner\Conversion\FontAwesome;
use Concrete\Package\BlocksCloner\Converter\Blocks;
use Concrete\Package\BlocksCloner\Converter\SimpleXmlTrait;

defined('C5_EXECUTE') or die('Access Denied.');

class Table
{
    use SimpleXmlTrait;

    /**
     * @var \Concrete\Package\BlocksCloner\Converter\Blocks
     */
    private $blocks;

    /**
     * @var \SimpleXMLElement[]
     */
    private $xDatas;

    /**
     * @var \Concrete\Package\BlocksCloner\Conversion\FontAwesome|null
     */
    private static $fontawesome = null;

    /**
     * @param \SimpleXMLElement[] $xDatas
     */
    public function __construct(Blocks $blocks, array $xDatas)
    {
        $this->blocks = $blocks;
        $this->xDatas = $xDatas;
    }

    /**
     * @return \Concrete\Package\BlocksCloner\Converter\Blocks
     */
    public function done()
    {
        return $this->blocks;
    }

    /**
     * @param string $newTableName
     *
     * @return $this
     */
    public function renameTable($newTableName)
    {
        $newTableName = (string) $newTableName;
        foreach ($this->xDatas as $xData) {
            $this->setSimpleXMLElementAttribute($xData, 'table', $newTableName);
        }

        return $this;
    }

    /**
     * @param string $fieldName
     *
     * @return $this
     */
    public function deleteField($fieldName)
    {
        $fieldName = (string) $fieldName;
        foreach ($this->xDatas as $xData) {
            foreach ($this->listChildElements($xData, 'record') as $xRecord) {
                while (isset($xRecord->{$fieldName})) {
                    unset($xRecord->{$fieldName}[0]);
                }
            }
        }

        return $this;
    }

    /**
     * @param string $fieldName
     * @param string $value
     *
     * @return $this
     */
    public function addField($fieldName, $value)
    {
        $fieldName = (string) $fieldName;
        $value = (string) $value;
        foreach ($this->xDatas as $xData) {
            foreach ($this->listChildElements($xData, 'record') as $xRecord) {
                if (isset($xRecord->{$fieldName})) {
                    continue;
                }
                $xField = $xRecord->addChild($fieldName);
                $this->setSimpleXMLElementValue($xField, $value);
            }
        }

        return $this;
    }

    public function convertFontAwesome4to5Field($fieldName)
    {
        $fontawesome = null;
        foreach ($this->listFields((string) $fieldName) as $xField) {
            $originalValue = trim((string) $xField);
            if ($originalValue === '') {
                continue;
            }
            if ($fontawesome === null) {
                $fontawesome = $this->getFontAwesome();
            }
            $newValue = $fontawesome->convertFontAwesomeIcon4To5($originalValue);
            if ($newValue !== '') {
                $this->setSimpleXMLElementValue($xField, $newValue);
            }
        }

        return $this;
    }

    /**
     * @param string $fieldName
     *
     * @return $this
     */
    public function fixExportedRichTextField($fieldName)
    {
        foreach ($this->listFields((string) $fieldName) as $xField) {
            $originalTextContent = (string) $xField;
            if ($originalTextContent === '') {
                continue;
            }
            $newTextContent = LinkAbstractor::export($originalTextContent);
            if ($newTextContent === $originalTextContent) {
                continue;
            }
            $this->setSimpleXMLElementValue($xField, $newTextContent);
        }

        return $this;
    }

    /**
     * @param string $fieldName
     *
     * @return $this
     */
    public function fixExportedFileSetIDField($fieldName)
    {
        foreach ($this->listFields((string) $fieldName) as $xField) {
            $originalTextContent = trim((string) $xField);
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
            $this->setSimpleXMLElementValue($xField, $fileSet->getFileSetName());
        }

        return $this;
    }

    /**
     * @param string $fieldName
     *
     * @return \SimpleXMLElement[]|\Generator
     */
    private function listFields($fieldName)
    {
        foreach ($this->xDatas as $xData) {
            foreach ($this->listChildElements($xData, 'record') as $xRecord) {
                foreach ($this->listChildElements($xRecord, $fieldName) as $xField) {
                    yield $xField;
                }
            }
        }
    }

    /**
     * @return \Concrete\Package\BlocksCloner\Conversion\FontAwesome
     */
    private function getFontAwesome()
    {
        if (self::$fontawesome === null) {
            self::$fontawesome = new FontAwesome();
        }

        return self::$fontawesome;
    }
}
