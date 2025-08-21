<?php

namespace Concrete\Package\BlocksCloner\Controller\Dialog;

use Concrete\Package\BlocksCloner\Converter\Environment\Service;
use Concrete\Package\BlocksCloner\UI\Controller\Dialog;
use Concrete\Package\BlocksCloner\Xml;

defined('C5_EXECUTE') or die('Access Denied.');

abstract class Export extends Dialog
{
    /**
     * @param string|\SimpleXMLElement|\DOMDocument $xml
     * @param bool $addCurrentEnvironment
     *
     * @return string
     */
    protected function formatXml($xml, $addCurrentEnvironment = false)
    {
        $xmlNormalized = $this->app->make(Xml::class)->normalize($xml);
        if ($addCurrentEnvironment) {
            $xmlNormalized = $this->app->make(Service::class)->addCurrentEnvironmentToXml($xmlNormalized);
        }

        return $xmlNormalized;
    }
}
