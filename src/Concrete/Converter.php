<?php

namespace Concrete\Package\BlocksCloner;

defined('C5_EXECUTE') or die('Access Denied.');

class Converter
{
    /**
     * @var \SimpleXMLElement
     */
    private $xDocument;

    public function __construct(\SimpleXMLElement $xDocument)
    {
        $this->xDocument = $xDocument;
    }

    /**
     * @param string $blockTypeHandle
     *
     * @return \Concrete\Package\BlocksCloner\Converter\Blocks
     */
    public function blocks($blockTypeHandle)
    {
        $xBlockElements = $this->extractBlockElements(
            static function (\SimpleXMLElement $xBlock) use ($blockTypeHandle) {
                return (string) $xBlock['type'] === $blockTypeHandle;
            }
        );

        return new Converter\Blocks($this, $xBlockElements);
    }

    /**
     * @return \SimpleXMLElement[]
     */
    private function extractBlockElements(\Closure $filter)
    {
        $result = [];
        $walk = null;
        $walk = static function (\SimpleXMLElement $xElement, $xParentElement = null) use (&$walk, $filter, &$result) {
            switch ($xElement->getName()) {
                case 'attributekey':
                    return;
                case 'block':
                    if ($filter($xElement)) {
                        $result[] = $xElement;
                    }
                    break;
                case 'data':
                    if ($xParentElement !== null && $xParentElement->getName() === 'block') {
                        return;
                    }
                    break;
            }
            foreach ($xElement->children() as $xChildElement) {
                $walk($xChildElement, $xElement);
            }
        };
        $walk($this->xDocument);

        return $result;
    }
}
