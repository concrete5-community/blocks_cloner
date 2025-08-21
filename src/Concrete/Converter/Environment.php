<?php

namespace Concrete\Package\BlocksCloner\Converter;

use JsonSerializable;
use stdClass;

defined('C5_EXECUTE') or die('Access Denied.');

final class Environment implements JsonSerializable
{
    /**
     * @var \Concrete\Package\BlocksCloner\Converter\Environment|null
     */
    private static $current = null;

    /**
     * @var string
     */
    private $coreVersion;

    /**
     * @var array
     */
    private $packagesAndVersions = [];

    public function __construct($coreVersion, array $packagesAndVersions)
    {
        $this->coreVersion = (string) $coreVersion;
        ksort($packagesAndVersions);
        $this->packagesAndVersions = $packagesAndVersions;
    }

    /**
     * @return string
     */
    public function getCoreVersion()
    {
        return $this->coreVersion;
    }

    /**
     * @param string $handle
     *
     * @return bool
     */
    public function hasPackage($handle)
    {
        return is_string($handle) && $handle !== '' && isset($this->packagesAndVersions[$handle]);
    }

    /**
     * @param string $handle
     *
     * @return string
     */
    public function getPackageVersion($handle)
    {
        return $this->hasPackage($handle) ? $this->packagesAndVersions[$handle] : '';
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return [
            'core' => $this->coreVersion,
            'packages' => $this->packagesAndVersions === [] ? new stdClass() : $this->packagesAndVersions,
        ];
    }
}
