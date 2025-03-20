<?php

namespace Concrete\Package\BlocksCloner\Controller;

use Concrete\Core\Asset\AssetList;
use Concrete\Core\Controller\Controller as CoreController;
use Concrete\Core\Entity\Block\BlockType\BlockType;
use Doctrine\ORM\EntityManagerInterface;

defined('C5_EXECUTE') or die('Access Denied.');

abstract class AbstractController extends CoreController
{
    public function view()
    {
        $this->set('cID', $this->request->query->getInt('cID'));
        $al = AssetList::getInstance();
        if (!$al->getAsset('javascript', 'blocks_cloner-view')) {
            $al->register('javascript', 'blocks_cloner-view', 'js/view.js', ['minify' => false, 'combine' => false], 'blocks_cloner');
        }
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
