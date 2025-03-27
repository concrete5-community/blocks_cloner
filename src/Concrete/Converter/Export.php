<?php

namespace Concrete\Package\BlocksCloner\Converter;

defined('C5_EXECUTE') or die('Access Denied.');

class Export
{
    /**
     * @var \Concrete\Package\BlocksCloner\Converter\Export\BlockType[]
     */
    private $blockTypes = [];

    /**
     * @param string $blockTypeHandle
     *
     * @return $this
     */
    public function addBlockType($blockTypeHandle, Export\BlockType $conversion)
    {
        $blockTypeHandle = (string) $blockTypeHandle;
        if (isset($this->blockTypes[$blockTypeHandle])) {
            $this->blockTypes[$blockTypeHandle][] = $conversion;
        } else {
            $this->blockTypes[$blockTypeHandle] = [$conversion];
        }

        return $this;
    }

    /**
     * @param string $blockTypeHandle
     *
     * @return \Concrete\Package\BlocksCloner\Converter\Export\BlockType[]
     */
    public function getBlockTypeConversions($blockTypeHandle)
    {
        $blockTypeHandle = (string) $blockTypeHandle;

        return isset($this->blockTypes[$blockTypeHandle]) ? $this->blockTypes[$blockTypeHandle] : [];
    }
    
}
