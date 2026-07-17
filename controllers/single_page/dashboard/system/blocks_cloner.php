<?php

namespace Concrete\Package\BlocksCloner\Controller\SinglePage\Dashboard\System;

use Concrete\Core\Page\Controller\DashboardPageController;
use Concrete\Package\BlocksCloner\GlobalOptions;

defined('C5_EXECUTE') or die('Access Denied.');

class BlocksCloner extends DashboardPageController
{
    public function view()
    {
        $this->set('globalOptions', $this->app->make(GlobalOptions::class));
    }

    public function save()
    {
        if (!$this->token->validate('blocks_cloner-save')) {
            $this->error->add($this->token->getErrorMessage());
        }
        if ($this->error->has()) {
            $this->view();
            return;
        }
        $globalOptions = $this->app->make(GlobalOptions::class);
        $globalOptions
            ->setExportEnabled($this->request->request->getBoolean('exportEnabled'))
            ->setImportEnabled($this->request->request->getBoolean('importEnabled'))
            ->setPageStructureEnabled($this->request->request->getBoolean('pageStructureEnabled'))
        ;
        $this->flash('success', t('Settings Saved.'));

        return $this->buildRedirect('/dashboard/system/blocks_cloner');
    }
}
