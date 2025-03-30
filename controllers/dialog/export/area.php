<?php

namespace Concrete\Package\BlocksCloner\Controller\Dialog\Export;

use Concrete\Core\Area\Area as ConcreteArea;
use Concrete\Core\Error\UserMessageException;
use Concrete\Package\BlocksCloner\Controller\Dialog\Export;
use Concrete\Package\BlocksCloner\Edit\Context;
use Concrete\Package\BlocksCloner\ExportFixer;
use Concrete\Package\BlocksCloner\XmlParser;
use SimpleXMLElement;

defined('C5_EXECUTE') or die('Access Denied.');

class Area extends Export
{
    protected $viewPath = '/dialogs/export/area';

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Package\BlocksCloner\Controller\AbstractController::view()
     */
    public function view()
    {
        parent::view();
        $aID = $this->request->query->getInt('aID');
        if (!$aID) {
            throw new UserMessageException(t('Access Denied'));
        }
        $areaHandle = (string) $this->request->query->get('aHandle');
        if ($areaHandle === '') {
            throw new UserMessageException(t('Access Denied'));
        }
        $area = ConcreteArea::get($this->getPage(), $areaHandle);
        if (!$area || $area->isError() || $aID !== (int) $area->getAreaID()) {
            throw new UserMessageException(t('Access Denied'));
        }
        $context = Context::forReading($this->getPage(), $area);
        $temporaryDocument = simplexml_load_string('<root />');
        $context->area->export($temporaryDocument, $context->page);
        if ($context->area !== $context->pageSpecificArea) {
            $styleDocument = simplexml_load_string('<root />');
            $area->export($styleDocument, $this->getPage());
            if (isset($styleDocument->area) && isset($styleDocument->area->style)) {
                if (isset($temporaryDocument->area) && !isset($temporaryDocument->area->style)) {
                    $domArea = dom_import_simplexml($temporaryDocument->area);
                    $domStyle = dom_import_simplexml($styleDocument->area->style);
                    $domStyle2 = $domArea->ownerDocument->importNode($domStyle, true);
                    $domArea->insertBefore($domStyle2, $domArea->firstChildElement);
                }
            }
        }
        $this->app->make(ExportFixer::class)->fix($temporaryDocument);
        $components = $this->extractComponents($temporaryDocument);
        if ($components === null) {
            throw new UserMessageException(t('Unable to detect the structure of the exported area'));
        }
        $xStyle = $components['style'];
        $xBlocks = $components['blocks'];
        $parser = $this->app->make(XmlParser::class);
        $variants = [];
        if ($xStyle !== null && $xBlocks !== []) {
            $variants[] = [
                'name' => t('Area Design and Blocks'),
                'data' => $this->buildVariant($xStyle, $xBlocks, $parser),
            ];
        }
        if ($xStyle !== null) {
            $variants[] = [
                'name' => t('Area Design'),
                'data' => $this->buildVariant($xStyle, [], $parser),
            ];
        }
        if ($xBlocks !== []) {
            $variants[] = [
                'name' => t('Blocks'),
                'data' => $this->buildVariant(null, $xBlocks, $parser),
            ];
        }
        $this->set('variants', $variants);
    }

    /**
     * @return \SimpleXMLElement[]|array|null
     */
    private function extractComponents(SimpleXMLElement $temporaryDocument)
    {
        // Structure for normal areas
        $structure = $this->extractChildElements($temporaryDocument, ['area']);
        if ($structure !== null && count($structure['area']) === 1) {
            $structure = $this->extractChildElements($temporaryDocument->area, ['style', 'blocks']);
            if ($structure !== null) {
                if (count($structure['style']) > 1 || count($structure['blocks']) > 1) {
                    return null;
                }
                $xStyle = $structure['style'] === [] ? null : $structure['style'][0];
                $xBlocks = $structure['blocks'] === [] ? null : $structure['blocks'][0];
                if ($xBlocks === null) {
                    return [
                        'style' => $xStyle,
                        'blocks' => [],
                    ];
                }
                $structure = $this->extractChildElements($xBlocks, ['block']);
                if ($structure === null) {
                    return null;
                }
                return [
                    'style' => $xStyle,
                    'blocks' => $structure['block'],
                ];
            }
        }
        // Structure for areas in containers
        $structure = $this->extractChildElements($temporaryDocument, ['style', 'block']);
        if ($structure !== null) {
            if (count($structure['style']) > 1) {
                return null;
            }
            return [
                'style' => $structure['style'] === [] ? null : $structure['style'][0],
                'blocks' => $structure['block'],
            ];
        }

        return null;
    }

    /**
     * @param \SimpleXMLElement|null $xStyle
     * @param \SimpleXMLElement[] $xBlocks
     *
     * @return array
     */
    private function buildVariant($xStyle, array $xBlocks, XmlParser $parser)
    {
        $xml = '<area>';
        if ($xStyle !== null) {
            $xml .= $xStyle->asXML();
        }
        if ($xBlocks !== []) {
            $xml .= '<blocks>';
            foreach ($xBlocks as $xBlock) {
                $xml .= $xBlock->asXML();
            }
            $xml .= '</blocks>';
        }
        $xml .= '</area>';
        $sx = simplexml_load_string($xml);
        return [
            'xml' => $this->formatXml($sx),
            'references' => $this->serializeReferences($parser->extractReferences($sx)),
        ];
    }
}

