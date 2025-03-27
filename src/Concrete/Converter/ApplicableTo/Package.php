<?php

namespace Concrete\Package\BlocksCloner\Converter\ApplicableTo;

use Concrete\Package\BlocksCloner\Converter\ApplicableTo;

defined('C5_EXECUTE') or die('Access Denied.');

class Package extends ApplicableTo
{
    /**
     * @var string
     */
    private $packageHandle;

    /**
     * @param string $packageHandle
     * @param string $sourceVersionConstraint
     * @param string $destinationVersionConstraint
     */
    public function __construct($packageHandle, $sourceVersionConstraint, $destinationVersionConstraint)
    {
        parent::__construct($sourceVersionConstraint, $destinationVersionConstraint);
        $this->packageHandle = (string) $packageHandle;
    }

    /**
     * @return string
     */
    public function getPackageHandle()
    {
        return $this->packageHandle;
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
            'packageHandle' => $this->getPackageHandle(),
        ] + parent::jsonSerialize();
    }
}
