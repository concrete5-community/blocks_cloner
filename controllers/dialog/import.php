<?php

namespace Concrete\Package\BlocksCloner\Controller\Dialog;

use Concrete\Core\Area\Area;
use Concrete\Core\Error\UserMessageException;
use Concrete\Package\BlocksCloner\Controller\AbstractController;
use Concrete\Core\Permission\Checker;

defined('C5_EXECUTE') or die('Access Denied.');

class Import extends AbstractController
{
    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Controller\Controller::$viewPath
     */
    protected $viewPath = '/dialogs/import';

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
        $aHandle = (string) $this->request->query->get('aHandle');
        if ($aHandle === '') {
            throw new UserMessageException(t('Access Denied'));
        }
        $area = Area::get($this->getPage(), $aHandle);
        if (!$area || $area->isError() || $area->getAreaID() != $aID) {
            throw new UserMessageException(t('Access Denied'));
        }
        $checker = new Checker($area);
        if (!$checker->canAddBlocks()) {
            throw new UserMessageException(t("You don't have permission to add blocks to this area"));
        }
        $maxBlocks = (int) $area->getMaximumBlocks();
        if ($maxBlocks === 0) {
            throw new UserMessageException(t("No block can be added to this area"));
        }
        if ($maxBlocks > 0) {
            $currentBlocks = $area->getTotalBlocksInAreaEditMode();
            if ($currentBlocks >= $maxBlocks) {
                throw new UserMessageException(
                    t2(
                        $maxBlocks,
                        "This area accepts up to %s block (and this limit is already reached)",
                        "This area accepts up to %s blocks (and this limit is already reached)"
                    )
                );
            }
        }
        $this->set('area', $area);
    }
}
