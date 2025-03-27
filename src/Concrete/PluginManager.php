<?php

namespace Concrete\Package\BlocksCloner;

use Concrete\Core\Application\Application;
use Concrete\Core\File\Service\File;
use ReflectionClass;

defined('C5_EXECUTE') or die('Access Denied.');

class PluginManager
{
    /**
     * @var \Concrete\Core\File\Service\File
     */
    private $fileService;

    /**
     * @var \Concrete\Core\Application\Application
     */
    private $pluginInstantiator;

    /**
     * @var \Concrete\Package\BlocksCloner\Plugin[]
     */
    private $plugins = [];

    public function __construct(File $fileService, Application $pluginInstantiator)
    {
        $this->fileService = $fileService;
        $this->pluginInstantiator = $pluginInstantiator;
    }

    /**
     * @return $this
     */
    public function registerPlugin(Plugin $plugin)
    {
        $this->plugins[] = $plugin;

        return $this;
    }

    /**
     * @param string $withInterface
    /**
     * @return \Concrete\Package\BlocksCloner\Plugin[]
     */
    public function getPlugins($withInterface = '')
    {
        return array_values(
            array_filter(
                $this->plugins,
                static function (Plugin $plugin) use ($withInterface) {
                    return !$withInterface || is_a($plugin, $withInterface);
                }
            )
        );
    }

    /**
     * @return void
     */
    public function registerDefaultPlugins()
    {
        $dirPrefix = rtrim(str_replace(DIRECTORY_SEPARATOR, '/', __DIR__), '/'). '/Plugins/';
        $namespacePrefix = __NAMESPACE__ . '\\Plugins\\';
        foreach ($this->fileService->getDirectoryContents($dirPrefix, [], true) as $item) {
            $item = str_replace(DIRECTORY_SEPARATOR, '/', $item);
            if (stripos($item, $dirPrefix) !== 0 || substr($item, -4) !== '.php') {
                continue;
            }
            $baseClassName = substr($item, strlen($dirPrefix), -4);
            $className = $namespacePrefix . $baseClassName;
            if (!class_exists($className)) {
                continue;
            }
            $classInfo = new ReflectionClass($className);
            if ($classInfo->isAbstract()) {
                continue;
            }
            if (!$classInfo->implementsInterface(Plugin::class)) {
                continue;
            }
            $instance = $this->pluginInstantiator->make($className);
            $this->registerPlugin($instance);
        }
    }
}
