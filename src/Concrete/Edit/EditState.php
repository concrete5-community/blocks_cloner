<?php

namespace Concrete\Package\BlocksCloner\Edit;

use Concrete\Core\Area\Area;
use Concrete\Core\Page\Page;
use Concrete\Core\Page\Stack\Stack;
use Concrete\Core\Error\UserMessageException;

/**
 * @readonly
 */
class EditState
{
    /**
     * @var \Concrete\Core\Page\Page
     */
    public $page;

    /**
     * @var \Concrete\Core\Area\Area
     */
    public $area;

    public function __construct(Page $page, Area $area)
    {
        if ($area->isGlobalArea()) {
            $stack = Stack::getByName($area->getAreaHandle());
            if (!$stack || $stack->isError()) {
                throw new UserMessageException(t('Unable to find the requested area'));
            }
            $actualPage = $stack;
            $this->area = Area::getOrCreate($stack, STACKS_AREA_NAME);
        } else {
            $stack = null;
            $actualPage = $page;
            $this->area = $area;
        }
        $actualPageToEdit = $actualPage->getVersionToModify();
        if ($actualPageToEdit !== $actualPage && $stack !== null) {
            $page->getVersionToModify()->relateVersionEdits($actualPageToEdit);
        }
        $this->page = $actualPageToEdit;
    }
}
