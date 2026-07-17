<?php

namespace Concrete\Package\BlocksCloner\Converter;

class Description implements \JsonSerializable
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
     * @param string $handle
     * @param string $name
     */
    public function __construct($handle, $name)
    {
        $this->handle = (string) $handle;
        $this->name = (string) $name;
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
