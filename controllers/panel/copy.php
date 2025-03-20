<?php

namespace Concrete\Package\BlocksCloner\Controller\Panel;

use Concrete\Package\BlocksCloner\Controller\AbstractController;

defined('C5_EXECUTE') or die('Access Denied.');

class Copy extends AbstractController
{
    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Controller\Controller::$viewPath
     */
    protected $viewPath = '/panels/copy';

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Package\BlocksCloner\Controller\AbstractController::view()
     */
    public function view()
    {
        parent::view();
        $this->set('blockTypeNames', $this->getBlockTypeNames());
    }
}
