<?php
/**
 * Modules plugin
 * 
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Heiner Lohaus
 * @package Shopware
 * @subpackage Plugins
 */
class Shopware_Plugins_Backend_Modules_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{			
	/**
	 * Plugin init method
	 */
	public function init()
	{	
		$event = new Enlight_Event_EventHandler(
	 		'Enlight_Controller_Router_Route',
	 		array($this, 'onRoute')
	 	);
		Shopware()->Events()->registerListener($event);
		$event = new Enlight_Event_EventHandler(
			'Enlight_Controller_Dispatcher_ControllerPath_Backend_Modules',
			array($this, 'onGetControllerPath')
		);
		Shopware()->Events()->registerListener($event);
		return true;
	}
	
	/**
	 * Plugin event method
	 *
	 * @param Enlight_Event_EventArgs $args
	 */
	public function onRoute(Enlight_Event_EventArgs $args)
	{
		$request = $args->getRequest();
		$url = $args->getRequest()->getPathInfo();
		$url = ltrim($url, '/');
		
		if(strpos($url, 'engine/backend/modules/') !== 0) {
			return;
		}
		return array('module' => 'backend', 'sViewport' => 'Modules');
	}
	
	/**
	 * Plugin event method
	 *
	 * @param Enlight_Event_EventArgs $args
	 */
	public static function onGetControllerPath(Enlight_Event_EventArgs $args)
    {
		return dirname(__FILE__).'/Modules.php';
    }
    
    /**
     * @var bool
     */
    protected $include;
    
	/**
	 * Set include config
	 *
	 * @param bool $include
	 * @return Shopware_Plugins_Backend_Modules_Bootstrap
	 */
	public function setInclude($include = true)
	{
		$this->include = (bool) $include;
		return $this;
	}
	
	/**
	 * Returns should include
	 *
	 * @return bool
	 */
	public function shouldInclude()
	{
		return $this->include;
	}
}