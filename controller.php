<?php

namespace Concrete\Package\BlocksCloner;

use Concrete\Core\Asset\AssetList;
use Concrete\Core\Database\EntityManager\Provider\ProviderAggregateInterface;
use Concrete\Core\Database\EntityManager\Provider\StandardPackageProvider;
use Concrete\Core\Http\Request;
use Concrete\Core\Http\ResponseAssetGroup;
use Concrete\Core\Package\Package;
use Concrete\Core\Page\Event as PageEvent;
use Concrete\Core\Page\Page;
use Concrete\Core\Permission\Checker;
use Concrete\Core\User\User;
use Concrete\Package\BlocksCloner\Controller\ViewLocalization;

defined('C5_EXECUTE') or die('Access Denied.');

class Controller extends Package implements ProviderAggregateInterface
{
    protected $pkgHandle = 'blocks_cloner';

    protected $pkgVersion = '0.9.2';

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Package\Package::$appVersionRequired
     */
    protected $appVersionRequired = '8.5.4';

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Package\Package::getPackageName()
     */
    public function getPackageName()
    {
        return t('Blocks Cloner');
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Package\Package::getPackageDescription()
     */
    public function getPackageDescription()
    {
        return t('Copy blocks between Concrete websites.');
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Database\EntityManager\Provider\ProviderAggregateInterface::getEntityManagerProvider()
     */
    public function getEntityManagerProvider()
    {
        return new StandardPackageProvider($this->app, $this, []);
    }

    public function on_start()
    {
        $user = $this->app->make(User::class);
        if (!$user->isRegistered()) {
            return;
        }
        $this->app->make('router')->get('/ccm/blocks-cloner/view-localization', ViewLocalization::class . '::view');
        $this->app->make('director')->addListener(
            'on_page_view',
            function ($event) {
                if (!$event instanceof PageEvent) {
                    return;
                }
                $this->inject($event->getPageObject(), $event->getUserObject());
            }
        );
        $request = $this->app->make(Request::class);
        if (strpos($request->getPath(), '/ccm/blocks_cloner/') === 0) {
            $this->registerRoutes($request->query->getInt('cID'));
        }
    }

    /**
     * @param \Concrete\Core\Page\Page|mixed $page
     * @param \Concrete\Core\User\User|mixed $user
     *
     * @return void
     */
    private function inject($page, $user)
    {
        if (!$page instanceof Page || $page->isError() || !$page->isEditMode()) {
            return;
        }
        $checker = new Checker($page);
        if (!$checker->canEditPageContents()) {
            return;
        }
        $menu = $this->app->make('helper/concrete/ui/menu');
        $menu->addPageHeaderMenuItem(
            'export',
            $this->pkgHandle,
            [
                'icon' => 'download',
                'label' => t('Export Block as XML'),
                'position' => 'left',
                'href' => false,
                'linkAttributes' => [
                    'data-launch-panel' => 'blocks_cloner-export',
                    'title' => t('Export Block as XML'),
                ],
            ]
        );
        $menu->addPageHeaderMenuItem(
            'import',
            $this->pkgHandle,
            [
                'icon' => 'upload',
                'label' => t('Import Block from XML'),
                'position' => 'left',
                'href' => false,
                'linkAttributes' => [
                    'data-launch-panel' => 'blocks_cloner-import',
                    'title' => t('Import Block from XML'),
                ],
            ]
        );
        $assetList = AssetList::getInstance();
        $assetList->register('javascript-localized', 'blocks_cloner-view', "/ccm/blocks-cloner/view-localization", ['minify' => false, 'combine' => false, 'version' => $this->pkgVersion], 'blocks_cloner');
        $assetList->register('javascript', 'blocks_cloner-view', 'js/view.js', ['minify' => false, 'combine' => false, 'version' => $this->pkgVersion], 'blocks_cloner');
        $assetList->registerGroup('blocks_cloner-view', [
            ['javascript-localized', 'blocks_cloner-view'],
            ['javascript', 'blocks_cloner-view'],
        ]);
        $responseAssets = ResponseAssetGroup::get();
        if (version_compare(APP_VERSION, '9') < 0) {
            $responseAssets->addHeaderAsset('<style>.ccm-ui [v-cloak] { display: none; }');
            $responseAssets->requireAsset('javascript', 'vue');
        }
        $responseAssets->requireAsset('blocks_cloner-view');
    }

    /**
     * @param int $cID
     *
     * @return void
     */
    private function registerRoutes($cID)
    {
        if (!$cID || $cID < 1) {
            return;
        }
        $page = Page::getByID($cID);
        if (!$page || $page->isError()) {
            return;
        }
        $checker = new Checker($page);
        if (!$checker->canEditPageContents()) {
            return;
        }
        $router = $this->app->make('router');
        $router
            ->buildGroup()
            ->setPrefix('/ccm/blocks_cloner')
            ->setNamespace('Concrete\Package\BlocksCloner\Controller')
            ->routes('export.php', $this->pkgHandle)
            ->routes('import.php', $this->pkgHandle)
        ;
    }
}
