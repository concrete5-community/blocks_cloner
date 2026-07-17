<?php

namespace Concrete\Package\BlocksCloner;

use Concrete\Core\Config\Repository\Repository;

defined('C5_EXECUTE') or die('Access Denied.');

class GlobalOptions
{
    /**
     * @var \Concrete\Core\Config\Repository\Repository
     */
    private $config;

    public function __construct(Repository $config)
    {
        $this->config = $config;
    }

    /**
     * @return bool
     */
    public function isExportEnabled()
    {
        return $this->config->get('blocks_cloner::options.exportEnabled') ? true : false;
    }

    /**
     * @param bool $value
     *
     * @return $this
     */
    public function setExportEnabled($value)
    {
        $this->config->set('blocks_cloner::options.exportEnabled', $value ? true : false);
        $this->config->save('blocks_cloner::options.exportEnabled', $value ? true : false);

        return $this;
    }
    
    /**
     * @return bool
     */
    public function isImportEnabled()
    {
        return $this->config->get('blocks_cloner::options.importEnabled') ? true : false;
    }

    /**
     * @param bool $value
     *
     * @return $this
     */
    public function setImportEnabled($value)
    {
        $this->config->set('blocks_cloner::options.importEnabled', $value ? true : false);
        $this->config->save('blocks_cloner::options.importEnabled', $value ? true : false);
        
        return $this;
    }
    
    /**
     * @return bool
     */
    public function isPageStructureEnabled()
    {
        return $this->config->get('blocks_cloner::options.pageStructureEnabled') ? true : false;
    }

    /**
     * @param bool $value
     *
     * @return $this
     */
    public function setPageStructureEnabled($value)
    {
        $this->config->set('blocks_cloner::options.pageStructureEnabled', $value ? true : false);
        $this->config->save('blocks_cloner::options.pageStructureEnabled', $value ? true : false);
        
        return $this;
    }

    /**
     * @return bool
     */
    public function isEverythingDisabled()
    {
        return $this->isExportEnabled() === false && $this->isImportEnabled() === false && $this->isPageStructureEnabled() === false;
    }
}
