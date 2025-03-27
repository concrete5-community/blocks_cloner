<?php

namespace Concrete\Package\BlocksCloner\Plugin;

defined('C5_EXECUTE') or die('Access Denied.');

use Concrete\Package\BlocksCloner\Plugin;

interface ConvertExport extends Plugin
{
    /**
     * @return \Concrete\Package\BlocksCloner\Converter\Export[]
     */
    public function getExportConverters();
}
