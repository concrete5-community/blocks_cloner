<?php

namespace Concrete\Package\BlocksCloner\Import;

use Concrete\Core\Http\Request;
use Concrete\Core\Error\UserMessageException;
use Concrete\Core\Validation\CSRF\Token;
use Concrete\Core\Area\Area;
use Concrete\Core\Page\Page;
use Concrete\Core\Permission\Checker;

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
    public function __construct(Page $page, $importType, Request $request, Token $token)
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
        xdebug_break();
        $this->area = $area;
        $this->beforeBlockID = $request->request->getInt('beforeBlockID');
        switch ($this->sx->getName()) {
            case 'block':
                $this->checkAddBlocks($area, 1);
                break;
            case 'area':
                if (isset($this->sx->style)) {
                    $this->checkEditAreaDesign($area);
                }
                $numBlocks = count($this->sx->xpath('/area/blocks/block'));
                if ($numBlocks > 0) {
                    $this->checkAddBlocks($area, $numBlocks);
                }
                break;
        }
    }

    /**
     * @param int $numberOfBlocksToBeAdded
     *
     * @throws \Concrete\Core\Error\UserMessageException
     *
     * @return void
     */
    private function checkAddBlocks(Area $area, $numberOfBlocksToBeAdded)
    {
        $checker = new Checker($area);
        if (!$checker->canAddBlocks()) {
            throw new UserMessageException(t("You don't have permission to add blocks to this area"));
        }
        $numberOfBlocksToBeAdded = (int) $numberOfBlocksToBeAdded;
        if ($numberOfBlocksToBeAdded > 0) {
            $maxBlocks = (int) $area->getMaximumBlocks();
            if ($maxBlocks === 0) {
                throw new UserMessageException(t('No block can be added to this area'));
            }
            if ($maxBlocks > 0) {
                $currentBlocks = $area->getTotalBlocksInAreaEditMode();
                if ($currentBlocks + $numberOfBlocksToBeAdded >= $maxBlocks) {
                    throw new UserMessageException(
                        t2(
                            $maxBlocks,
                            'This area accepts up to %s block (and this limit is already reached)',
                            'This area accepts up to %s blocks (and this limit is already reached)'
                        )
                    );
                }
            }
        }
    }

    /**
     * @throws \Concrete\Core\Error\UserMessageException
     *
     * @return void
     */
    private function checkEditAreaDesign(Area $area)
    {
        $checker = new Checker($area);
        if (!$checker->canEditAreaDesign()) {
            throw new UserMessageException(t("You don't have the permission to edit area designs"));
        }
    }
}
