<?php

namespace Concrete\Package\BlocksCloner\Controller\Dialog;

use Concrete\Package\BlocksCloner\Conversion\Environment;
use Concrete\Package\BlocksCloner\Plugin;
use Concrete\Package\BlocksCloner\UI\Controller\Dialog;
use Concrete\Package\BlocksCloner\Xml;
use SimpleXMLElement;

defined('C5_EXECUTE') or die('Access Denied.');

abstract class Export extends Dialog
{
    /**
     * @return void
     */
    protected function convert(SimpleXMLElement $exported)
    {
        $currentEnvironment = $this->app->make(Environment\Service::class)->getCurrentEnvironment();
        array_map(
            static function (Plugin\ConvertExport $plugin) use ($exported, $currentEnvironment) {
                $plugin->applyExportConvertersByEnvironment($exported, $currentEnvironment);
            },
            $this->app->make(Plugin\Manager::class)->getConvertExportPlugins()
        );
    }

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
            $xmlNormalized = $this->app->make(Environment\Service::class)->addCurrentEnvironmentToXml($xmlNormalized);
        }

        return $xmlNormalized;
    }
}
