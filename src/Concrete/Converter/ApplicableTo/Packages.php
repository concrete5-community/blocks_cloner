<?php

namespace Concrete\Package\BlocksCloner\Converter\ApplicableTo;

use Concrete\Package\BlocksCloner\Converter\ApplicableTo;

defined('C5_EXECUTE') or die('Access Denied.');

class Packages extends ApplicableTo
{
    /**
     * @var string
     */
    private $sourcePackageHandle;

    /**
     * @var string
     */
    private $destinationPackageHandle;

    /**
     * @param string $sourcePackageHandle
     * @param string $sourceVersionConstraint
     * @param string $destinationPackageHandle
     * @param string $destinationVersionConstraint
     */
    public function __construct($sourcePackageHandle, $sourceVersionConstraint, $destinationPackageHandle, $destinationVersionConstraint)
    {
        parent::__construct($sourceVersionConstraint, $destinationVersionConstraint);
        $this->sourcePackageHandle = (string) $sourcePackageHandle;
        $this->destinationPackageHandle = (string) $destinationPackageHandle;
    }

    /**
     * @return string
     */
    public function getSourcePackageHandle()
    {
        return $this->sourcePackageHandle;
    }

    /**
     * @return string
     */
    public function getDestinationPackageHandle()
    {
        return $this->destinationPackageHandle;
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Package\BlocksCloner\Converter\ApplicableTo::jsonSerialize()
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return [
            'sourcePackageHandle' => $this->getSourcePackageHandle(),
            'destinationPackageHandle' => $this->getDestinationPackageHandle(),
        ] + parent::jsonSerialize();
    }
}
