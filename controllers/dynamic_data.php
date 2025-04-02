<?php

namespace Concrete\Package\BlocksCloner\Controller;

use Concrete\Core\Config\Repository\Repository;
use Concrete\Core\Controller\Controller;
use Concrete\Core\Entity\Block\BlockType\BlockType;
use Concrete\Core\Http\ResponseFactoryInterface;
use Concrete\Core\Package\PackageService;
use Concrete\Core\Page\Page;
use Concrete\Core\Permission\Checker;
use Concrete\Package\BlocksCloner\Plugin\ConvertImport;
use Concrete\Package\BlocksCloner\PluginManager;
use Doctrine\ORM\EntityManagerInterface;

defined('C5_EXECUTE') or die('Access Denied.');

final class DynamicData extends Controller
{
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function view()
    {
        $content = <<<EOT
;(function() {

{$this->buildCCMBlocksClonerDynamicData()}

{$this->buildConverterRegistration()}

})();
EOT
        ;
        $responseFactory = $this->app->make(ResponseFactoryInterface::class);

        return $responseFactory->create(
            $content,
            200,
            [
                'Content-Type' => 'application/javascript; charset=' . APP_CHARSET,
                'Content-Length' => strlen($content),
            ]
        );
    }

    /**
     * @return string
     */
    private function buildCCMBlocksClonerDynamicData()
    {
        $config = $this->app->make(Repository::class);
        $stackEditPageID = null;
        $stackEditPage = Page::getByPath('/dashboard/blocks/stacks');
        if ($stackEditPage && !$stackEditPage->isError()) {
            $checker = new Checker($stackEditPage);
            if ($checker->canViewPage()) {
                $stackEditPageID = (int) $stackEditPage->getCollectionID();
            }
        }

        return 'window.ccmBlocksClonerDynamicData = window.ccmBlocksClonerDynamicData || ' . json_encode([
            'i18n' => [
                'exportAreaAsXml' => t('Export Area as XML'),
                'exportAreaNameAsXmlName' => t('Export %s area as XML'),
                'exportBlockAsXml' => t('Export Block as XML'),
                'exportBlockTypeNameAsXml' => t('Export %s block as XML'),
                'exportStackAsXml' => t('Export Stack as XML'),
                'importFromXml' => t('Import from XML'),
                'importFromXmlIntoAreaName' => t('Import from XML into %s'),
            ],
            'stackEditPageID' => $stackEditPageID,
            'blockTypeNames' => $this->getBlockTypeNames(),
            'environment' => [
                'core' => $config->get('concrete.version'),
                'packages' => $this->getPackagesAndVersions(),
            ],
        ]) . ';';
    }

    /**
     * @return array
     */
    private function getBlockTypeNames()
    {
        $result = [];
        $em = $this->app->make(EntityManagerInterface::class);
        $repo = $em->getRepository(BlockType::class);
        foreach ($repo->findAll() as $blockType) {
            $result[$blockType->getBlockTypeHandle()] = t($blockType->getBlockTypeName());
        }

        return $result;
    }

    /**
     * @return array
     */
    private function getPackagesAndVersions()
    {
        $packageService = $this->app->make(PackageService::class);
        $result = [];
        foreach ($packageService->getInstalledList() as $package) {
            $result[$package->getPackageHandle()] = $package->getPackageVersion();
        }
        ksort($result);

        return $result;
    }

    /**
     * @return string
     */
    private function buildConverterRegistration()
    {
        $converters = [];
        $plugins = $this->app->make(PluginManager::class)->getPlugins(ConvertImport::class);
        /** @var \Concrete\Package\BlocksCloner\Plugin\ConvertImport[] $plugins */
        foreach ($plugins as $plugin) {
            $converters = array_merge($converters, $plugin->getImportConverters());
        }
        if ($converters === []) {
            return;
        }
        $jsonConverters = json_encode($converters);

        return <<<EOT
(function() {

function setup()
{
    const fn = window.ccmBlocksCloner?.conversion?.registerConverters;
    if (!fn) {
        return false;
    }
    fn({$jsonConverters});
}

if (!setup()) {
    document.addEventListener('DOMContentLoaded', setup);
}

})();
EOT;
    }
}
