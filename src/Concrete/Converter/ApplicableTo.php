<?php

namespace Concrete\Package\BlocksCloner\Converter;

use JsonSerializable;

defined('C5_EXECUTE') or die('Access Denied.');

abstract class ApplicableTo implements JsonSerializable
{
    /**
     * @var string
     */
    private $sourceVersionConstraint;

    /**
     * @var string
     */
    private $destinationVersionConstraint;

    /**
     * @param string $sourceVersionConstraint
     * @param string $destinationVersionConstraint
     */
    public function __construct($sourceVersionConstraint, $destinationVersionConstraint)
    {
        $this->sourceVersionConstraint = (string) $sourceVersionConstraint;
        $this->destinationVersionConstraint = (string) $destinationVersionConstraint;
    }

    /**
     * @return string
     */
    public function getSourceVersionConstraint()
    {
        return $this->sourceVersionConstraint;
    }

    /**
     * @return string
     */
    public function getDestinationVersionConstraint()
    {
        return $this->destinationVersionConstraint;
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
            'sourceVersionConstraint' => $this->getSourceVersionConstraint(),
            'destinationVersionConstraint' => $this->getDestinationVersionConstraint(),
        ];
    }
}
