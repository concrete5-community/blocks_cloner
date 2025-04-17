<?php

namespace Concrete\Package\BlocksCloner\Controller\Dialog;

use Concrete\Package\BlocksCloner\UI\Controller\Dialog;
use DOMDocument;
use SimpleXMLElement;

defined('C5_EXECUTE') or die('Access Denied.');

abstract class Export extends Dialog
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

        return preg_replace('{^<\?xml[^>]*>\s}i', '', $xml);
    }
}
