<?php

namespace Concrete\Package\BlocksCloner;

use Concrete\Core\Area\Area;
use Concrete\Core\Error\UserMessageException;
use Concrete\Core\Permission\Checker;

defined('C5_EXECUTE') or die('Access Denied.');

final class ImportChecker
{
    /**
     * @param int|null $numberOfBlocksToBeAdded
     *
     * @throws \Concrete\Core\Error\UserMessageException
     *
     * @return void
     */
    public function checkArea(Area $area, $numberOfBlocksToBeAdded = null)
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
}
