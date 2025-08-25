<?php

namespace Concrete\Package\BlocksCloner\Converter;

use Concrete\Package\BlocksCloner\Converter;

defined('C5_EXECUTE') or die('Access Denied.');

class Blocks
{
    use SimpleXmlTrait;

    /**
     * @var \Concrete\Package\BlocksCloner\Converter
     */
    private $converter;

    /**
     * @var \SimpleXMLElement[]
     */
    private $xBlocks;

    /**
     * @param \SimpleXMLElement[] $xBlocks
     */
    public function __construct(Converter $converter, array $xBlocks)
    {
        $this->converter = $converter;
        $this->xBlocks = $xBlocks;
    }

    /**
     * @return \Concrete\Package\BlocksCloner\Converter
     */
    public function done()
    {
        return $this->converter;
    }

    /**
     * @param string $tableName
     *
     * @return \Concrete\Package\BlocksCloner\Converter\Blocks\Table
     */
    public function table($tableName)
    {
        $tableName = (string) $tableName;
        $xDatas = [];
        foreach ($this->xBlocks as $xBlock) {
            foreach ($this->listChildElements($xBlock, 'data') as $xData) {
                if ((string) $xData['table'] === $tableName) {
                    $xDatas[] = $xData;
                }
            }
        }

        return new Blocks\Table($this, $xDatas);
    }

    /**
     * @param string $newTypeHandle
     *
     * @return $this
     */
    public function renameBlockTypeHandle($newTypeHandle)
    {
        $newTypeHandle = (string) $newTypeHandle;
        foreach ($this->xBlocks as $xBlock) {
            $this->setSimpleXMLElementAttribute($xBlock, 'type', $newTypeHandle);
        }

        return $this;
    }

    /**
     * @param string $originalCustomTemplate
     * @param string|null $newCustomTemplate NULL to keep it, empty string to remove it, non-empty string to change it
     * @param string|string[] $newCustomClasses
     *
     * @return $this
     */
    public function changeCustomTemplate($originalCustomTemplate, $newCustomTemplate = null, $newCustomClasses = '')
    {
        $originalCustomTemplate = preg_replace('/\.php$/', '', (string) $originalCustomTemplate);
        if ($newCustomTemplate !== null) {
            $newCustomTemplate = (string) $newCustomTemplate;
            if ($newCustomTemplate !== '') {
                $newCustomTemplate = preg_replace('/\.php$/', '', (string) $newCustomTemplate) . '.php';
            }
        }
        if (is_array($newCustomClasses)) {
            $tmp = [];
            foreach ($newCustomClasses as $cls) {
                $tmp = array_merge($tmp, preg_split('/\s+/', (string) $cls, -1, PREG_SPLIT_NO_EMPTY));
            }
        } else {
            $tmp = preg_split('/\s+/', (string) $newCustomClasses, -1, PREG_SPLIT_NO_EMPTY);
        }
        $newCustomClasses = array_unique($tmp, SORT_STRING);

        foreach ($this->xBlocks as $xBlock) {
            if (preg_replace('/\.php$/', '', (string) $xBlock['custom-template']) !== $originalCustomTemplate) {
                continue;
            }
            if ($newCustomTemplate === '') {
                unset($xBlock['custom-template']);
            } elseif ($newCustomTemplate !== null) {
                $xBlock['custom-template'] = $newCustomTemplate;
            }
            if ($newCustomClasses !== []) {
                $xStyle = $this->getOrCreateFirstChildElement($xBlock, 'style');
                $xCustomClass = $this->getOrCreateFirstChildElement($xStyle, 'customClass');
                $oldCustomClasses = preg_split('/\s+/', (string) $xCustomClass, -1, PREG_SPLIT_NO_EMPTY);
                $customClassesToBeAdded = array_diff($newCustomClasses, $oldCustomClasses);
                if ($customClassesToBeAdded !== []) {
                    $this->setSimpleXMLElementValue($xCustomClass, implode(' ', array_merge($oldCustomClasses, $customClassesToBeAdded)));
                }
            }
        }

        return $this;
    }
}
