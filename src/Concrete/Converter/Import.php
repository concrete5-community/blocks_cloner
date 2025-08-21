<?php

namespace Concrete\Package\BlocksCloner\Converter;

use Closure;
use JsonSerializable;

defined('C5_EXECUTE') or die('Access Denied.');

class Import implements JsonSerializable
{
    /**
     * @var string
     */
    private $handle;

    /**
     * @var string
     */
    private $name;

    /**
     * @var \Closure
     */
    private $canBeAppliedTo;

    /**
     * @var \Concrete\Package\BlocksCloner\Converter\Import\BlockType[]|array
     */
    private $blockTypes = [];

    /**
     * @param string $handle
     * @param string $name
     */
    public function __construct($handle, $name, Closure $canBeAppliedTo)
    {
        $this->handle = (string) $handle;
        $this->name = (string) $name;
        $this->canBeAppliedTo = $canBeAppliedTo;
    }

    /**
     * @return string
     */
    public function getHandle()
    {
        return $this->handle;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return bool
     */
    public function canBeAppliedTo(Environment $sourceEnvironment, Environment $destinationEnvironment)
    {
        $closure = $this->canBeAppliedTo;

        return $closure($sourceEnvironment, $destinationEnvironment);
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
        return [
            'handle' => $this->getHandle(),
            'name' => $this->getName(),
        ];
    }
}
