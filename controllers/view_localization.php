<?php

namespace Concrete\Package\BlocksCloner\Controller;

use Concrete\Core\Controller\Controller;
use Concrete\Core\Entity\Block\BlockType\BlockType;
use Concrete\Core\Http\ResponseFactoryInterface;
use Doctrine\ORM\EntityManagerInterface;

defined('C5_EXECUTE') or die('Access Denied.');

class ViewLocalization extends Controller
{
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function view()
    {
        $content = 'window.ccmBlocksClonerI18N = window.ccmBlocksClonerI18N || ' . json_encode([
            'exportAsXml' => t('Export as XML'),
            'exportBlockTypeNameAsXml' => t('Export %s block as XML'),
            'importBlockFromXml' => t('Import Block from XML'),
            'importBlockFromXmlIntoAreaName' => t('Import Block from XML into %s'),
            'blockTypeNames' => $this->getBlockTypeNames(),
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
    protected function getBlockTypeNames()
    {
        $result = [];
        $em = $this->app->make(EntityManagerInterface::class);
        $repo = $em->getRepository(BlockType::class);
        foreach ($repo->findAll() as $blockType) {
            $result[$blockType->getBlockTypeHandle()] = t($blockType->getBlockTypeName());
        }

        return $result;
    }
}
