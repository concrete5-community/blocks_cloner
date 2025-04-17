<?php

namespace Concrete\Package\BlocksCloner\UI;

use Concrete\Core\Controller\Controller as CoreController;
use Concrete\Core\Entity\Block\BlockType\BlockType;
use Concrete\Core\Entity\File\Version as FileVersion;
use Concrete\Core\Entity\Package;
use Concrete\Core\Entity\Page\Container;
use Concrete\Core\Entity\Page\Feed as PageFeed;
use Concrete\Core\Error\UserMessageException;
use Concrete\Core\Page\Page;
use Concrete\Core\Page\Stack\Stack;
use Concrete\Core\Page\Type\Type as PageType;
use Concrete\Core\Url\Resolver\Manager\ResolverManagerInterface;
use Concrete\Package\BlocksCloner\XmlParser;
use Doctrine\ORM\EntityManagerInterface;
use SimpleXMLElement;
use stdClass;

defined('C5_EXECUTE') or die('Access Denied.');

abstract class Controller extends CoreController
{
    /**
     * @var int
     */
    protected $cID;

    /**
     * @var \Concrete\Core\Page\Page|null
     */
    private $page;

    /**
     * @var \Concrete\Core\Entity\Package[]|null
     */
    private $installedPackages;

    /**
     * @var \Concrete\Core\Url\Resolver\Manager\ResolverManagerInterface|null
     */
    private $resolverManager;

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Controller\Controller::__construct()
     */
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
     * @return \Concrete\Core\Page\Page
     */
    protected function getPage()
    {
        if ($this->page === null) {
            $this->page = Page::getByID($this->cID);
        }

        return $this->page;
    }

    /**
     * @return \Concrete\Core\Entity\Package[] array keys are the package IDs
     */
    protected function getInstalledPackages()
    {
        if ($this->installedPackages === null) {
            $installedPackages = [];
            $em = $this->app->make(EntityManagerInterface::class);
            $repo = $em->getRepository(Package::class);
            foreach ($repo->findAll() as $package) {
                $installedPackages[$package->getPackageID()] = $package;
            }
            $this->installedPackages = $installedPackages;
        }

        return $this->installedPackages;
    }

    /**
     * @return \Concrete\Core\Url\Resolver\Manager\ResolverManagerInterface
     */
    private function getResolverManager()
    {
        if ($this->resolverManager === null) {
            $this->resolverManager = $this->app->make(ResolverManagerInterface::class);
        }

        return $this->resolverManager;
    }

    /**
     * @param string[] $expectedElementNames
     *
     * @return array|null returns NULL if some unexpected element name has been found
     */
    protected function extractChildElements(SimpleXMLElement $parent, array $expectedElementNames)
    {
        $result = array_fill_keys($expectedElementNames, []);
        foreach ($parent->children() as $child) {
            $name = $child->getName();
            if (!isset($result[$name])) {
                return null;
            }
            $result[$name][] = $child;
        }

        return $result;
    }

    /**
     * @return array
     */
    protected function serializeReferences(array $references)
    {
        $result = new stdClass();
        foreach ($references as $referenceType => $items) {
            $result->{$referenceType} = new stdClass();
            foreach ($items as $itemKey => $item) {
                $result->{$referenceType}->{$itemKey} = $this->serializeReference($referenceType, $item);
            }
        }

        return $result;
    }

    /**
     * @param string $referenceType
     *
     * @return array
     */
    private function serializeReference($referenceType, $reference)
    {
        switch ($referenceType) {
            case XmlParser::KEY_BLOCKTYPES:
                return is_string($reference) ? ['error' => $reference] : $this->serializeBlockType($reference);
            case XmlParser::KEY_FILES:
                return is_string($reference) ? ['error' => $reference] : $this->serializeFileVersionReference($reference);
            case XmlParser::KEY_PAGES:
                return is_string($reference) ? ['error' => $reference] : $this->serializePageReference($reference);
            case XmlParser::KEY_PAGETYPES:
                return is_string($reference) ? ['error' => $reference] : $this->serializePageTypeReference($reference);
            case XmlParser::KEY_PAGEFEEDS:
                return is_string($reference) ? ['error' => $reference] : $this->serializePageFeedReference($reference);
            case XmlParser::KEY_STACKS:
                return is_string($reference) ? ['error' => $reference] : $this->serializeStackReference($reference);
            case XmlParser::KEY_CONTAINERS:
                return is_string($reference) ? ['error' => $reference] : $this->serializeContainerReference($reference);
            default:
                throw new UserMessageException(t('Unrecognized reference type: %s', $referenceType));
        }
    }

    /**
     * @return array
     */
    private function serializeBlockType(BlockType $blockType)
    {
        $package = null;
        $packageID = $blockType->getPackageID();
        if ($packageID) {
            $packages = $this->getInstalledPackages();
            if (isset($packages[$packageID])) {
                $package = $packages[$packageID];
            }
        }

        return [
            'handle' => $blockType->getBlockTypeHandle(),
            'name' => t($blockType->getBlockTypeName()),
            'package' => $package === null ? null : [
                'name' => t($package->getPackageName()),
                'handle' => $package->getPackageHandle(),
            ],
        ];
    }

    /**
     * @return array
     */
    private function serializeFileVersionReference(FileVersion $fileVersion)
    {
        return [
            'id' => (int) $fileVersion->getFileID(),
            'name' => $fileVersion->getFileName(),
            'prefix' => $fileVersion->getPrefix(),
        ];
    }

    /**
     * @return array
     */
    private function serializePageReference(Page $page)
    {
        return [
            'id' => (int) $page->getCollectionID(),
            'name' => $page->getCollectionName(),
            'visitLink' => (string) $this->getResolverManager()->resolve([$page]),
        ];
    }

    /**
     * @return array
     */
    private function serializePageTypeReference(PageType $pageType)
    {
        return [
            'id' => (int) $pageType->getPageTypeID(),
            'name' => $pageType->getPageTypeName(),
        ];
    }

    /**
     * @return array
     */
    private function serializePageFeedReference(PageFeed $pageFeed)
    {
        return [
            'id' => (int) $pageFeed->getID(),
            'title' => $pageFeed->getFeedDisplayTitle('text'),
        ];
    }

    /**
     * @return array
     */
    private function serializeStackReference(Stack $stack)
    {
        return [
            'id' => (int) $stack->getCollectionID(),
            'name' => $stack->getStackName() ?: $stack->getCollectionName(),
        ];
    }

    private function serializeContainerReference(Container $container)
    {
        return [
            'id' => (int) $container->getContainerID(),
            'name' => $container->getContainerDisplayName(),
        ];
    }
}
