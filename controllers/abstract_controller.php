<?php

namespace Concrete\Package\BlocksCloner\Controller;

use Concrete\Core\Controller\Controller as CoreController;
use Concrete\Core\Entity\Block\BlockType\BlockType;
use Concrete\Core\Page\Page;
use Doctrine\ORM\EntityManagerInterface;

defined('C5_EXECUTE') or die('Access Denied.');

abstract class AbstractController extends CoreController
{
    /**
     * @var int
     */
    protected $cID;

    /**
     * @var \Concrete\Core\Page\Page|null
     */
    private $page;

    public function __construct()
    {
        parent::__construct();
        $this->cID = $this->request->query->getInt('cID');
    }

    public function view()
    {
        $this->set('cID', $this->cID);
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

    /**
     * @return \Concrete\Core\Page\Page
     */
    protected function getPage()
    {
        if ($this->page === null) {
            $this->page = Page::getByID($this->cID);
        }

        return $this->page;
    }
}
