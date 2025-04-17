<?php

namespace Concrete\Package\BlocksCloner\Controller\Panel;

use Concrete\Package\BlocksCloner\UI\Controller\Panel;

defined('C5_EXECUTE') or die('Access Denied.');

class Export extends Panel
{
    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Controller\Controller::$viewPath
     */
    protected $viewPath = '/panels/export';
}
