<?php

namespace Concrete\Package\BlocksCloner\Converter;

use SimpleXMLElement;

defined('C5_EXECUTE') or die('Access Denied.');

trait SimpleXmlTrait
{
    /**
     * @param string $name
     *
     * @return \SimpleXMLElement[]
     */
    protected function listChildElements(SimpleXMLElement $el, $name)
    {
        $result = [];
        foreach ($el->children() as $child) {
            if ($child->getName() === $name) {
                $result[] = $child;
            }
        }

        return $result;
    }

    /**
     * @param string $name
     *
     * @return \SimpleXMLElement
     */
    protected function getOrCreateFirstChildElement(SimpleXMLElement $el, $name)
    {
        $all = $this->listChildElements($el, $name);

        return $all === [] ? $el->addChild($name) : $all[0];
    }

    /**
     * @param string $name
     * @param string $value
     *
     * @return void
     */
    protected function setSimpleXMLElementAttribute(SimpleXMLElement $el, $name, $value)
    {
        $el[(string) $name] = (string) $value;
    }

    /**
     * @param string $value
     *
     * @return void
     */
    protected function setSimpleXMLElementValue(SimpleXMLElement $el, $value)
    {
        $el[0] = $value;
    }
}
