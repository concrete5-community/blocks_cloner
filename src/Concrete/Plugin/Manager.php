<?php

namespace Concrete\Package\BlocksCloner\Plugin;

use Concrete\Core\Application\Application;
use Concrete\Core\File\Service\File;
use Concrete\Package\BlocksCloner\Plugin;

defined('C5_EXECUTE') or die('Access Denied.');

class Manager
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
     * @return \Concrete\Package\BlocksCloner\Plugin[]
     */
    public function getPlugins()
    {
        return $this->plugins;
    }

    /**
     * @return \Concrete\Package\BlocksCloner\Plugin\ConvertExport[]
     */
    public function getConvertExportPlugins()
    {
        return array_values(
            array_filter(
                $this->getPlugins(),
                static function (Plugin $plugin) {
                    return $plugin instanceof ConvertExport;
                }
            )
        );
    }

    /**
     * @return \Concrete\Package\BlocksCloner\Plugin\ConvertImport[]
     */
    public function getConvertImportPlugins()
    {
        return array_values(
            array_filter(
                $this->getPlugins(),
                static function (Plugin $plugin) {
                    return $plugin instanceof ConvertImport;
                }
            )
        );
    }

    /**
     * @return void
     */
    public function registerDefaultPlugins()
    {
        $dirPrefix = rtrim(str_replace(DIRECTORY_SEPARATOR, '/', dirname(__DIR__)), '/') . '/Plugins/';
        $namespaceChunks = preg_split('{\\\}', __NAMESPACE__);
        array_pop($namespaceChunks);
        $namespaceChunks[] = 'Plugins';
        $namespacePrefix = implode('\\', $namespaceChunks) . '\\';
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
            $classInfo = new \ReflectionClass($className);
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
