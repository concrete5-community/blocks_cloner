<?php

namespace Concrete\Package\BlocksCloner\Plugin;

defined('C5_EXECUTE') or die('Access Denied.');

use Concrete\Package\BlocksCloner\Conversion\Environment;
use Concrete\Package\BlocksCloner\Plugin;

interface ConvertExport extends Plugin
{
    /**
     * @return \Concrete\Package\BlocksCloner\Converter\Description[]
     */
    public function getExportConverters();

    /**
     * @string $handle
     *
     * @return bool false if there's no converter with the provided handle, true otherwise
     */
    public function applyExportConverterByHandle(\SimpleXMLElement $xDocument, $handle);

    /**
     * @return void
     */
    public function applyExportConvertersByEnvironment(\SimpleXMLElement $xDocument, Environment $environment);
}
