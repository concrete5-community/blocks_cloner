<?php

namespace Concrete\Package\BlocksCloner\Edit;

use Concrete\Core\Area\Area;
use Concrete\Core\Error\UserMessageException;
use Concrete\Core\Page\Page;
use Concrete\Core\Page\Stack\Stack;

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
     * @var \Concrete\Core\Area\Area
     */
    public $pageSpecificArea;

    /**
     * @param bool $forWriting
     */
    private function __construct(Page $page, Area $area, $forWriting)
    {
        $this->pageSpecificArea = $area;
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
            $this->area = $this->pageSpecificArea;
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
