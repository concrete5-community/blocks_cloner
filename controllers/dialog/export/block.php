<?php

namespace Concrete\Package\BlocksCloner\Controller\Dialog\Export;

use Concrete\Core\Area\Area as ConcreteArea;
use Concrete\Core\Block\Block as ConcreteBlock;
use Concrete\Core\Error\UserMessageException;
use Concrete\Core\Url\Resolver\Manager\ResolverManagerInterface;
use Concrete\Package\BlocksCloner\Controller\Dialog\Export;
use Concrete\Package\BlocksCloner\Edit\Context;
use Concrete\Package\BlocksCloner\ExportFixer;
use Concrete\Package\BlocksCloner\XmlParser;

defined('C5_EXECUTE') or die('Access Denied.');

class Block extends Export
{
    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Controller\Controller::$viewPath
     */
    protected $viewPath = '/dialogs/export/block';

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Package\BlocksCloner\Controller\AbstractController::view()
     */
    public function view()
    {
        parent::view();
        $bID = $this->request->query->getInt('bID');
        if (!$bID) {
            throw new UserMessageException(t('Access Denied'));
        }
        $areaHandle = (string) $this->request->query->get('aHandle');
        if ($areaHandle === '') {
            throw new UserMessageException(t('Access Denied'));
        }
        $area = ConcreteArea::get($this->getPage(), $areaHandle);
        if (!$area || $area->isError()) {
            throw new UserMessageException(t('Access Denied'));
        }
        $context = Context::forReading($this->getPage(), $area);
        $block = ConcreteBlock::getByID($bID, $context->page, $context->area);
        if (!$block || $block->isError()) {
            throw new UserMessageException(t('Access Denied'));
        }
        $temporaryDocument = simplexml_load_string('<root />');
        $block->export($temporaryDocument);
        $temporaryDocumentChildren = $temporaryDocument->children();
        $blockElement = null;
        if (count($temporaryDocumentChildren) === 1) {
            $blockElement = $temporaryDocumentChildren[0];
            if ($blockElement->getName() !== 'block') {
                $blockElement = null;
            }
        }
        if ($blockElement === null) {
            throw new UserMessageException(t('Unable to detect the structure of the exported area'));
        }
        $this->app->make(ExportFixer::class)->fix($blockElement);
        $this->set('xml', $this->formatXml($blockElement));
        $parser = $this->app->make(XmlParser::class);
        $this->set('references', $this->serializeReferences($parser->extractReferences($blockElement)));
        $this->set('resolverManager', $this->app->make(ResolverManagerInterface::class));
    }
}
