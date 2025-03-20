<?php

namespace Concrete\Package\BlocksCloner\Controller\Panel;

use Concrete\Package\BlocksCloner\Controller\AbstractController;

defined('C5_EXECUTE') or die('Access Denied.');

class Paste extends AbstractController
{
    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Controller\Controller::$viewPath
     */
    protected $viewPath = '/panels/paste';
    
    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Package\BlocksCloner\Controller\AbstractController::view()
     */
    public function view()
    {
        parent::view();
        if (version_compare(APP_VERSION, '9') < 0) {
            $this->requireAsset('javascript', 'vue');
        }
        $this->requireAsset('javascript', 'blocks_cloner-view');
    }
}
