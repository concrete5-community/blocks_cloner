<?php

namespace Concrete\Package\BlocksCloner;

use Concrete\Core\Database\EntityManager\Provider\ProviderAggregateInterface;
use Concrete\Core\Database\EntityManager\Provider\StandardPackageProvider;
use Concrete\Core\Package\Package;
use Concrete\Core\Page\Page;
use Concrete\Core\Permission\Checker;
use Concrete\Core\Routing\Route;
use Concrete\Core\User\User;
use Concrete\Core\Http\Request;

defined('C5_EXECUTE') or die('Access Denied.');

class Controller extends Package implements ProviderAggregateInterface
{
    const PANELFLAG_COPY = 1;
    const PANELFLAG_PASTE = 2;

    protected $pkgHandle = 'blocks_cloner';

    protected $pkgVersion = '0.9.0';

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Package\Package::$appVersionRequired
     */
    protected $appVersionRequired = '8.5.12';

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
        $this->app->make('director')->addListener(
            'on_page_view',
            function ($event) {
                if (!$event instanceof \Concrete\Core\Page\Event) {
                    return;
                }
                $this->setupMenu($event->getPageObject(), $event->getUserObject());
            }
        );
        $request = $this->app->make(Request::class);
        if (strpos($request->getPath(), '/ccm/blocks_cloner/panels/') === 0) {
            $cID = $request->query->getInt('cID');
            if ($cID > 0) {
                $page = Page::getByID($cID);
                if ($page && !$page->isError() && $page->isEditMode()) {
                    $panelFlags = self::PANELFLAG_COPY;
                    $checker = new Checker($page);
                    if ($checker->canEditPageContents()) {
                        $panelFlags |= self::PANELFLAG_PASTE;
                    }
                    $this->registerPanelRoutes($panelFlags);
                }
            }
        }
    }

    /**
     * @param \Concrete\Core\Page\Page|mixed $page
     * @param \Concrete\Core\User\User|mixed $user
     *
     * @return void
     */
    private function setupMenu($page, $user)
    {
        if (!$page instanceof Page || $page->isError() || !$page->isEditMode()) {
            return;
        }
        $menu = $this->app->make('helper/concrete/ui/menu');
        $menu->addPageHeaderMenuItem(
            'copy',
            $this->pkgHandle,
            [
                'icon' => 'clone',
                'label' => t('Copy Block'),
                'position' => 'left',
                'href' => false,
                'linkAttributes' => [
                    'data-launch-panel' => 'blocks_cloner-copy',
                ],
            ]
        );
        $checker = new Checker($page);
        if ($checker->canEditPageContents()) {
            $menu->addPageHeaderMenuItem(
                'paste',
                $this->pkgHandle,
                [
                    'icon' => 'clipboard',
                    'label' => t('Paste Blocks'),
                    'position' => 'left',
                    'href' => false,
                    'linkAttributes' => [
                        'data-launch-panel' => 'blocks_cloner-paste',
                    ],
                ]
            );
        }
    }

    /**
     * @param int $panelFlags
     *
     * @return void
     */
    private function registerPanelRoutes($panelFlags)
    {
        $router = $this->app->make('router');
        $group = $router
            ->buildGroup()
            ->setPrefix('/ccm/blocks_cloner/panels')
            ->setNamespace('Concrete\Package\BlocksCloner\Controller\Panel')
        ;
        
        if ($panelFlags & self::PANELFLAG_COPY) {
            $group->routes('panels/copy.php', $this->pkgHandle);
        }
        if ($panelFlags & self::PANELFLAG_PASTE) {
            $group->routes('panels/paste.php', $this->pkgHandle);
        }
    }
}
