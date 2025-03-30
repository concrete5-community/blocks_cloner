<?php

namespace Concrete\Package\BlocksCloner\Edit;

use Concrete\Core\Area\Area;
use Concrete\Core\Page\Page;
use Concrete\Core\Page\Stack\Stack;
use Concrete\Core\Error\UserMessageException;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @readonly
 */
final class Context
{
    /**
     * @var \Concrete\Core\Page\Page
     */
    public $page;

    /**
     * @var \Concrete\Core\Area\Area
     */
    public $area;

    /**
     * @param bool $forWriting
     */
    private function __construct(Page $page, Area $area, $forWriting)
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
        if ($forWriting) {
            $this->page = $actualPage->getVersionToModify();
            if ($stack !== null) {
                $page->getVersionToModify()->relateVersionEdits($this->page);
            }
        } else {
            $this->page = $actualPage;
        }
    }

    /**
     * @return self
     */
    public static function forReading(Page $page, Area $area)
    {
        return new self($page, $area, false);
    }

    /**
     * @return self
     */
    public static function forWriting(Page $page, Area $area)
    {
        return new self($page, $area, true);
    }
}
