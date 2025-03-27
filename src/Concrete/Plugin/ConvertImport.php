<?php

namespace Concrete\Package\BlocksCloner\Plugin;

defined('C5_EXECUTE') or die('Access Denied.');

use Concrete\Package\BlocksCloner\Plugin;

interface ConvertImport extends Plugin
{
    /**
     * @return \Concrete\Package\BlocksCloner\Converter\Import[]
     */
    public function getImportConverters();
}
