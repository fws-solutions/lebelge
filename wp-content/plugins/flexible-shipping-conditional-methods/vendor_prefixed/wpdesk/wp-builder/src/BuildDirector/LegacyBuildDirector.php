<?php

namespace FSConditionalMethodsVendor\WPDesk\PluginBuilder\BuildDirector;

use FSConditionalMethodsVendor\WPDesk\PluginBuilder\Builder\AbstractBuilder;
use FSConditionalMethodsVendor\WPDesk\PluginBuilder\Plugin\AbstractPlugin;
use FSConditionalMethodsVendor\WPDesk\PluginBuilder\Storage\StorageFactory;
class LegacyBuildDirector
{
    /** @var AbstractBuilder */
    private $builder;
    public function __construct(\FSConditionalMethodsVendor\WPDesk\PluginBuilder\Builder\AbstractBuilder $builder)
    {
        $this->builder = $builder;
    }
    /**
     * Builds plugin
     */
    public function build_plugin()
    {
        $this->builder->build_plugin();
        $this->builder->init_plugin();
        $storage = new \FSConditionalMethodsVendor\WPDesk\PluginBuilder\Storage\StorageFactory();
        $this->builder->store_plugin($storage->create_storage());
    }
    /**
     * Returns built plugin
     *
     * @return AbstractPlugin
     */
    public function get_plugin()
    {
        return $this->builder->get_plugin();
    }
}
