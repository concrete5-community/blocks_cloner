<?php

namespace Concrete\Package\BlocksCloner\Controller;

use Concrete\Core\Controller\Controller;
use Concrete\Core\Entity\Block\BlockType\BlockType;
use Concrete\Core\Http\ResponseFactoryInterface;
use Concrete\Core\Page\Page;
use Concrete\Core\Permission\Checker;
use Concrete\Package\BlocksCloner\GlobalOptions;
use Concrete\Package\BlocksCloner\Plugin;
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
        $stackEditPageID = null;
        $stackEditPage = Page::getByPath(\STACKS_LISTING_PAGE_PATH);
        if ($stackEditPage && !$stackEditPage->isError()) {
            $checker = new Checker($stackEditPage);
            if ($checker->canViewPage()) {
                $stackEditPageID = (int) $stackEditPage->getCollectionID();
            }
        }
        $globalOptions = $this->app->make(GlobalOptions::class);

        return 'window.ccmBlocksClonerDynamicData = window.ccmBlocksClonerDynamicData || ' . json_encode([
            'i18n' => [
                'exportAreaAsXml' => t('Export area as XML'),
                'exportAreaNameAsXmlName' => t('Export %s area as XML'),
                'exportBlockAsXml' => t('Export block as XML'),
                'exportBlockTypeNameAsXml' => t('Export %s block as XML'),
                'exportStackAsXml' => t('Export stack as XML'),
                'importFromXml' => t('Import content from XML'),
                'importFromXmlIntoAreaName' => t('Import content from XML into %s'),
            ],
            'stackEditPageID' => $stackEditPageID,
            'blockTypeNames' => $this->getBlockTypeNames(),
            'exportEnabled' => $globalOptions->isExportEnabled(),
            'importEnabled' => $globalOptions->isImportEnabled(),
            'pageStructureEnabled' => $globalOptions->isPageStructureEnabled(),
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
     * @return string
     */
    private function buildConverterRegistration()
    {
        $converters = [];
        $plugins = $this->app->make(Plugin\Manager::class)->getConvertImportPlugins();
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
