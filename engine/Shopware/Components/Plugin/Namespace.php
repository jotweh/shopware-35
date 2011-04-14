<?php
/**
 * Shopware Plugin Namespace
 * 
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Heiner Lohaus
 */
class Shopware_Components_Plugin_Namespace extends Enlight_Plugin_Namespace
{
	protected $cache;
	protected $infos;
	protected $configs;
	
	protected $shop;
	protected $locale;
		
	/**
	 * Returns plugin info
	 *
	 * @param unknown_type $plugin
	 * @param unknown_type $name
	 * @return unknown
	 */
	public function getInfo($plugin, $name)
	{
		if($this->infos===null) {
			$this->infos = new Shopware_Components_Config_DbTable(array(
	    		'cache' => $this->cache,
	    		'name' => 's_core_plugins',
	    		'nameColum' => 'name',
	    		'valueColum' => '*',
	    		'sectionColum' => 'namespace',
	    		'section' => $this->name,
	    		'cacheTags' => array('Shopware_Plugin')
	    	));
		}
		if(isset($this->infos->{$plugin}->$name)) {
			return $this->infos->{$plugin}->$name;
		} else {
			return null;
		}
	}
	
	/**
	 * Returns plugin source
	 *
	 * @param string $plugin
	 * @return string
	 */
	public function getSource($plugin)
	{
		foreach ($this->path as $path=>$prefix) {
			$file = $path.$plugin.Enlight::DS().'Bootstrap.php';
			if(file_exists($file)){
				return basename(dirname(dirname(dirname($file))));
			}
		}
	}
	
	/**
	 * Returns plugin config
	 *
	 * @param string $plugin
	 * @return Shopware_Models_Plugin_Config
	 */
	public function getConfig($plugin)
    {
    	$pluginId = $this->getPluginId($plugin);
    	
    	if(Shopware()->Bootstrap()->issetResource('Shop')) {
    		$shopId = Shopware()->Shop()->getId();
			$localeId = Shopware()->Locale()->getId();
    	} else {
    		$shopId = 1;
			$localeId = 1;
    	}
    	
		if(!isset($this->configs[$pluginId][$shopId][$localeId])) {
			$this->configs[$pluginId][$shopId][$localeId] = new Shopware_Models_Plugin_Config(array(
				'section' => array($pluginId, $localeId, $shopId),
				'extends' => array(array($pluginId, 1, $shopId), array($pluginId, 1, 1)),
				'cache' => $this->cache
			));
		}
		return $this->configs[$pluginId][$shopId][$localeId];
    }
	
    /**
     * Set shop instance
     *
     * @param Shopware_Models_Shop $shop
     * @return Shopware_Components_Plugin_Namespace
     */
	public function setShop(Shopware_Models_Shop $shop)
    {
    	$this->shop = $shop;
    	$this->locale = $shop->Locale();
        return $this;
    }
    
    /**
     * Set cache instance
     *
     * @param Zend_Cache_Core $cache
     * @return Shopware_Components_Plugin_Namespace
     */
    public function setCache(Zend_Cache_Core $cache)
    {
    	$this->cache = $cache;
        return $this;
    }
    
    /**
     * Returns plugin id
     *
     * @param string $plugin
     * @return int
     */
    public function getPluginId($plugin)
    {
    	return $this->getInfo($plugin, 'id');
    }
}