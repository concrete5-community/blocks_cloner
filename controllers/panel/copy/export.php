<?php

namespace Concrete\Package\BlocksCloner\Controller\Panel\Copy;

use Concrete\Package\BlocksCloner\Controller\Panel\Controller;
use Concrete\Core\Error\UserMessageException;
use Concrete\Core\Page\Page;
use Concrete\Core\Block\Block;

class Export extends Controller
{
    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Controller\Controller::$viewPath
     */
    protected $viewPath = '/panels/copy/export';

    public function view()
    {
        $cID = $this->request->query->getInt('cID');
        $bID = $this->request->query->getInt('bId');
        if (!$cID || !$bID) {
            throw new UserMessageException(t('Access Denied'));
        }
        $page = Page::getByID($cID);
        if (!$page || $page->isError() || !$page->isEditMode()) {
            throw new UserMessageException(t('Access Denied'));
        }
        $block = Block::getByID($bID);
        if (!$block || $block->isError()) {
            throw new UserMessageException(t('Access Denied'));
        }
        $sx = simplexml_load_string('<root />');
        $block->export($sx);
        $children = $sx->children();
        $blockElement = $children[0];
        $doc = new \DOMDocument('1.0');
        $doc->preserveWhiteSpace = false;
        $doc->formatOutput = true;
        $doc->loadXML($blockElement->asXML());
        $xml = $doc->saveXML();
        $xml = preg_replace('{^<\?xml[^>]*>\s}i', '', $xml);
        $this->set('xml', $xml);
    }
}
