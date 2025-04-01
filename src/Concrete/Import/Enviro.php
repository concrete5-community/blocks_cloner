<?php

namespace Concrete\Package\BlocksCloner\Import;

use Concrete\Core\Http\Request;
use Concrete\Core\Error\UserMessageException;
use Concrete\Core\Validation\CSRF\Token;
use Concrete\Core\Area\Area;
use Concrete\Package\BlocksCloner\ImportChecker;
use Concrete\Core\Page\Page;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @readonly
 */
class Enviro
{
    use LoadXmlTrait;

    /**
     * @var \SimpleXMLElement
     */
    public $sx;

    /**
     * @var \Concrete\Core\Area\Area
     */
    public $area;

    /**
     * @var int
     */
    public $beforeBlockID;

    /**
     * @param string $importType
     *
     * @throws \Concrete\Core\Error\UserMessageException
     */
    public function __construct(Page $page, $importType, Request $request, Token $token, ImportChecker $importChecker)
    {
        $xml = $request->request->get('xml');
        $this->sx = $this->loadXml($xml);
        $areaHandle = (string) $request->request->get('areaHandle');
        if ($areaHandle === '') {
            throw new UserMessageException(t('Access Denied'));
        }
        if (!$token->validate("blocks_cloner:import:{$importType}:{$page->getCollectionID()}:{$areaHandle}:" . sha1($xml))) {
            throw new UserMessageException($token->getErrorMessage());
        }
        $area = Area::get($page, $areaHandle);
        if (!$area || $area->isError()) {
            throw new UserMessageException(t('Unable to find the requested area'));
        }
        $this->area = $area;
        $this->beforeBlockID = $request->request->getInt('beforeBlockID');
        switch ($importType === 'block') {
            case 'block':
                $importChecker->checkArea($area, 1);
                break;
            case 'area':
                $importChecker->checkArea($area, count($this->sx->xpath('/area/blocks/block')));
                break;
        }
    }
}
