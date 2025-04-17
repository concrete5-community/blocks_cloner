<?php

namespace Concrete\Package\BlocksCloner\Controller\Panel;

use Concrete\Package\BlocksCloner\UI\Controller;

defined('C5_EXECUTE') or die('Access Denied.');

class Export extends Controller
{
    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Controller\Controller::$viewPath
     */
    protected $viewPath = '/panels/export';
}
