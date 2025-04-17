<?php

namespace Concrete\Package\BlocksCloner\UI\Controller;

use Concrete\Core\View\DialogView;
use Concrete\Package\BlocksCloner\UI\Controller;

defined('C5_EXECUTE') or die('Access Denied.');

abstract class Dialog extends Controller
{
    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Package\BlocksCloner\UI\Controller::__construct()
     */
    public function __construct()
    {
        parent::__construct();
        $view = new DialogView($this->viewPath);
        $view->setPackageHandle('blocks_cloner');
        $view->setController($this);
        $this->setViewObject($view);
    }
}
