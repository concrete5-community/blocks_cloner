<?php

namespace Concrete\Package\BlocksCloner;

use Concrete\Core\Error\UserMessageException;

defined('C5_EXECUTE') or die('Access Denied.');

final class Xml
{
    /**
     * @param string|\SimpleXMLElement|\DOMDocument $xml
     *
     * @return \DOMDocument
     */
    public function getDOMDocument($xml)
    {
        return $this->ensureDOMDocument($xml);
    }

    /**
     * @param string|\SimpleXMLElement|\DOMDocument $xml
     *
     * @return \SimpleXMLElement
     */
    public function getSimpleXMLElement($xml)
    {
        return $this->ensureSimpleXMLElement($xml);
    }

    /**
     * @param string|\SimpleXMLElement|\DOMDocument $xml
     *
     * @return string
     */
    public function normalize($xml)
    {
        $xDoc = $this->ensureDOMDocument($xml, true);
        foreach ($this->listElementsWithoutChildren($xDoc) as $xElement) {
            $this->normalizeXmlElementValue($xElement);
        }

        return $this->renderDoc($xDoc);
    }

    /**
     * @param string|\SimpleXMLElement|\DOMDocument $xml
     * @param bool $reloadIfObject
     *
     * @return \DOMDocument
     */
    private function ensureDOMDocument($xml, $reloadIfObject = false)
    {
        if ($xml instanceof \DOMNode) {
            $xml = $xml->ownerDocument ?: $xml;
        }
        if ($xml instanceof \DOMDocument) {
            if (!$reloadIfObject) {
                return $xml;
            }
            $xml = $xml->saveXML();
        }
        if ($xml instanceof \SimpleXMLElement) {
            if (!$reloadIfObject) {
                $domElement = dom_import_simplexml($xml);

                return $domElement->ownerDocument ?: $domElement;
            }
            $xml = $xml->asXML();
        }
        if (!is_string($xml)) {
            throw new \RuntimeException(t('Invalid type of parameter %1$s of function %2$s', '$xml', __METHOD__));
        }
        if (($xml = trim($xml)) === '') {
            throw new UserMessageException(t('The XML is empty'));
        }
        $xDoc = new \DOMDocument('1.0', 'UTF-8');
        $xDoc->preserveWhiteSpace = false;
        $xDoc->formatOutput = true;
        $restoreInternalErrors = libxml_use_internal_errors(true);
        try {
            libxml_clear_errors();
            $ok = $xDoc->loadXML($xml, LIBXML_NONET);
            $errors = libxml_get_errors();
            if ($ok && empty($errors)) {
                return $xDoc;
            }
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($restoreInternalErrors);
        }
        $message = t('Failed to load the XML.');
        if (!empty($errors)) {
            foreach ($errors as $error) {
                $message .= "\n" . $this->describeXmlError($error);
            }
        }

        throw new UserMessageException($message);
    }

    /**
     * @param string|\SimpleXMLElement|\DOMDocument $xml
     *
     * @return \SimpleXMLElement
     */
    private function ensureSimpleXMLElement($xml)
    {
        if ($xml instanceof \SimpleXMLElement) {
            return $xml;
        }
        if ($xml instanceof \DOMDocument) {
            $xml = $xml->saveXML();
        }
        if (!is_string($xml)) {
            throw new \RuntimeException(t('Invalid type of parameter %1$s of function %2$s', '$xml', __METHOD__));
        }
        if (($xml = trim($xml)) === '') {
            throw new UserMessageException(t('The XML is empty'));
        }
        $restoreInternalErrors = libxml_use_internal_errors(true);
        try {
            libxml_clear_errors();
            $sx = simplexml_load_string($xml);
            $errors = libxml_get_errors();
            if ($sx instanceof \SimpleXMLElement && empty($errors)) {
                return $sx;
            }
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($restoreInternalErrors);
        }
        $message = t('Failed to load the XML.');
        if (!empty($errors)) {
            foreach ($errors as $error) {
                $message .= "\n" . $this->describeXmlError($error);
            }
        }

        throw new UserMessageException($message);
    }

    /**
     * @return string
     */
    private function describeXmlError(\LibXMLError $error)
    {
        $line = '';
        switch ($error->level) {
            case LIBXML_ERR_WARNING:
                $line .= '[' . t('Warning') . '] ';
                break;
            case LIBXML_ERR_ERROR:
                $line .= '[' . t('Error') . '] ';
                break;
            case LIBXML_ERR_FATAL:
                $line .= '[' . t('Fatal error') . '] ';
                break;
        }
        $line .= trim($error->message);
        if ($error->line) {
            $line .= ' (' . t('at line %s', $error->line) . ')';
        }

        return $line;
    }

    /**
     * @return \DOMElement[]|\Generator
     */
    private function listElementsWithoutChildren(\DOMDocument $xDoc)
    {
        $xPath = new \DOMXPath($xDoc);
        foreach ($xPath->query('//*') as $xElement) {
            foreach ($xElement->childNodes as $xChild) {
                if ($xChild->nodeType === XML_ELEMENT_NODE) {
                    continue 2;
                }
            }
            yield $xElement;
        }
    }

    /**
     * @return void
     */
    private function normalizeXmlElementValue(\DOMElement $xElement)
    {
        $text = '';
        $hasCData = false;
        foreach (iterator_to_array($xElement->childNodes) as $xChild) {
            switch ($xChild->nodeType) {
                case XML_CDATA_SECTION_NODE:
                    $hasCData = true;
                    $text .= $xChild->data;
                    break;
                case XML_TEXT_NODE:
                    $text .= $xChild->nodeValue;
                    break;
            }
        }
        $useCData = strpbrk($text, '&<>') !== false;
        if ($hasCData === $useCData) {
            return;
        }
        foreach (iterator_to_array($xElement->childNodes) as $xChild) {
            if ($xChild->nodeType === XML_TEXT_NODE || $xChild->nodeType === XML_CDATA_SECTION_NODE) {
                $xElement->removeChild($xChild);
            }
        }
        if ($text !== '') {
            if ($useCData) {
                $xElement->appendChild($xElement->ownerDocument->createCDATASection($text));
            } else {
                $xElement->appendChild($xElement->ownerDocument->createTextNode($text));
            }
        }
    }

    /**
     * @return string
     */
    private function renderDoc(\DOMDocument $xDoc)
    {
        $xml = $xDoc->saveXML();

        return rtrim(preg_replace('/^\s*(<\?xml[^>]*>\s*[\r\n]*\s*)?/', '', $xml));
    }
}
