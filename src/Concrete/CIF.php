<?php

namespace Concrete\Package\BlocksCloner;

use Concrete\Core\Editor\LinkAbstractor;
use DOMElement;
use DOMXPath;
use SimpleXMLElement;

class CIF
{
    public function fixExportedBlocks(SimpleXMLElement $exported)
    {
        foreach ($this->listBlockElements($exported) as $blockElement) {
            $this->fixExportedBlock($blockElement);
        }
    }

    /**
     * @param string $blockTypeHandle
     *
     * @return array|null
     */
    protected function getContentFieldsForBlockType($blockTypeHandle)
    {
        switch ($blockTypeHandle) {
            case 'whale_cta':
                return [
                    'btWhaleCta' => ['paragraph'],
                ];
        }

        return null;
    }

    /**
     * @param \SimpleXMLElement $element
     *
     * @return \SimpleXMLElement[]|\Generator
     */
    private function listBlockElements(SimpleXMLElement $element)
    {
        $name = $element->getName();
        if ($name === 'block') {
            yield $element;
        }
        foreach ($element->children() as $child) {
            if ($name === 'data' && $child->getName() === 'record') {
                continue;
            }
            foreach ($this->listBlockElements($child) as $blockElement) {
                yield $blockElement;
            }
        }
    }

    private function fixExportedBlock(SimpleXMLElement $blockElement)
    {
        $blockTypeHandle = isset($blockElement['type']) ? (string) $blockElement['type'] : '';
        $contentFields = $this->getContentFieldsForBlockType($blockTypeHandle);
        if ($contentFields === null) {
            return;
        }
        $domElement = dom_import_simplexml($blockElement);
        if (!$domElement instanceof DOMElement) {
            return;
        }
        $xpath = new DOMXPath($domElement->ownerDocument);
        foreach ($contentFields as $tableName => $fieldNames) {
            foreach ($fieldNames as $fieldName) {
                foreach ($xpath->query("./data[@table=\"{$tableName}\"]/record/{$fieldName}", $domElement) as $fieldElement) {
                    /** @var \DOMElement $fieldElement */
                    $textContent = (string) $fieldElement->textContent;
                    if (trim($textContent) === '') {
                        continue;
                    }
                    $newTextContent = LinkAbstractor::export($textContent);
                    if ($newTextContent === $textContent) {
                        continue;
                    }
                    $cdata = $fieldElement->ownerDocument->createCDataSection($newTextContent);
                    $fieldElement->nodeValue = '';
                    $fieldElement->appendChild($cdata);
                }
            }
        }
    }
}
