<?php

namespace Concrete\Package\BlocksCloner\Controller;

use Concrete\Core\Config\Repository\Repository;
use Concrete\Core\Controller\Controller;
use Concrete\Core\Entity\Block\BlockType\BlockType;
use Concrete\Core\Http\ResponseFactoryInterface;
use Concrete\Core\Package\PackageService;
use Doctrine\ORM\EntityManagerInterface;

defined('C5_EXECUTE') or die('Access Denied.');

final class ViewLocalization extends Controller
{
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function view()
    {
        $config = $this->app->make(Repository::class);
        $content = 'window.ccmBlocksClonerI18N = window.ccmBlocksClonerI18N || ' . json_encode([
            'exportAsXml' => t('Export as XML'),
            'exportBlockTypeNameAsXml' => t('Export %s block as XML'),
            'importBlockFromXml' => t('Import Block from XML'),
            'importBlockFromXmlIntoAreaName' => t('Import Block from XML into %s'),
            '_blockTypeNames' => $this->getBlockTypeNames(),
            '_environment' => [
                'core' => $config->get('concrete.version'),
                'packages' => $this->getPackagesAndVersions(),
            ],
        ]) . ';';
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
}

