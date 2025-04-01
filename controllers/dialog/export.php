<?php

namespace Concrete\Package\BlocksCloner\Controller\Dialog;

use Concrete\Package\BlocksCloner\Controller\AbstractController;
use DOMDocument;
use SimpleXMLElement;

defined('C5_EXECUTE') or die('Access Denied.');

abstract class Export extends AbstractController
{
    /**
     * @return string
     */
    protected function formatXml(SimpleXMLElement $sx)
    {
        $doc = new DOMDocument('1.0');
        $doc->preserveWhiteSpace = false;
        $doc->formatOutput = true;
        $doc->loadXML($sx->asXML());
        $xml = $doc->saveXML();
        $xml = preg_replace('{^<\?xml[^>]*>\s}i', '', $xml);

        return $xml;
    }
}
