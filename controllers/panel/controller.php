<?php

namespace Concrete\Package\BlocksCloner\Controller\Panel;

use Concrete\Core\Controller\Controller as CoreController;
use Concrete\Core\Asset\AssetList;

abstract class Controller extends CoreController
{
    public function view()
    {
        $this->set('cID', $this->request->query->getInt('cID'));
        if (version_compare(APP_VERSION, '9') < 0) {
            $this->requireAsset('javascript', 'vue');
        }
        $al = AssetList::getInstance();
        if (!$al->getAsset('javascript', 'blocks_cloner-view')) {
            $al->register('javascript', 'blocks_cloner-view', 'js/view.js', ['minify' => false, 'combine' => false], 'blocks_cloner');
        }
        $this->requireAsset('javascript', 'blocks_cloner-view');
    }
}
