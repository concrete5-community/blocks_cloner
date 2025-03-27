<?php

namespace Concrete\Package\BlocksCloner\Converter;

defined('C5_EXECUTE') or die('Access Denied.');

use JsonSerializable;

defined('C5_EXECUTE') or die('Access Denied.');

class Import implements JsonSerializable
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var \Concrete\Package\BlocksCloner\Converter\ApplicableTo
     */
    private $applicableTo;

    /**
     * @var \Concrete\Package\BlocksCloner\Converter\Import\BlockType[]|array
     */
    private $blockTypes = [];

    /**
     * @param string $name
     */
    public function __construct($name, ApplicableTo $applicableTo)
    {
        $this->name = (string) $name;
        $this->applicableTo = $applicableTo;
    }

    /**
     * @return \Concrete\Package\BlocksCloner\Converter\ApplicableTo
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return \Concrete\Package\BlocksCloner\Converter\ApplicableTo
     */
    public function getApplicableTo()
    {
        return $this->applicableTo;
    }

    /**
     * @param string $blockTypeHandle
     *
     * @return $this
     */
    public function addBlockType($blockTypeHandle, Import\BlockType $conversion)
    {
        $blockTypeHandle = (string) $blockTypeHandle;
        if (isset($this->blockTypes[$blockTypeHandle])) {
            throw new \RuntimeException(t('Duplicated block type handle: %s', $blockTypeHandle));
        }
        $this->blockTypes[$blockTypeHandle] = $conversion;

        return $this;
    }

    /**
     * @return \Concrete\Package\BlocksCloner\Converter\Import\BlockType[]|array
     */
    public function getBlockTypes()
    {
        return $this->blockTypes;
    }

    /**
     * {@inheritdoc}
     *
     * @see \JsonSerializable::jsonSerialize()
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        $result = [
            'name' => $this->getName(),
            'applicableTo' => $this->getApplicableTo(),
        ];
        if (($blockTypes = $this->getBlockTypes()) !== null) {
            $result['blockTypes'] = $blockTypes;
        }

        return $result;
    }
}
