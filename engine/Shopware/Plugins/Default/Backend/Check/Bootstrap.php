<?php
/**
 * Shopware Backend Check Plugin
 * 
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @package Shopware
 * @subpackage Plugins
 * @author Heiner Lohaus
 */
class Shopware_Plugins_Backend_Check_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
	/**
	 * Install plugin method
	 *
	 * @return bool
	 */
	public function install()
	{
		$event = $this->createEvent(
			'Enlight_Controller_Dispatcher_ControllerPath_Backend_Check',
			'onGetControllerPathBackend'
		);
		$this->subscribeEvent($event);
		
		$sql = 'DELETE FROM `s_core_menu` WHERE `name`=?';
		Shopware()->Db()->query($sql, array($this->getName()));
		
		$parent = $this->Menu()->findOneBy('label', 'Einstellungen');
		$item = $this->createMenuItem(array(
			'label' => $this->getName(),
			'onclick' => 'openAction(\'check\');',
			'class' => 'ico2 information_frame',
			'active' => 1,
			'parent' => $parent,
			'position' => -3,
			'style' => 'background-position: 5px 5px;'
		));
		$this->Menu()->addItem($item);
		$this->Menu()->save();
		
		return true;
	}
	
	/**
	 * Event listener method
	 *
	 * @param Enlight_Event_EventArgs $args
	 * @return string
	 */
	public static function onGetControllerPathBackend(Enlight_Event_EventArgs $args)
    {
    	Shopware()->Loader()->registerNamespace(
			'Shopware_Components_Check',
			dirname(__FILE__) . '/Components/Check/'
		);
		Shopware()->Template()->addTemplateDir(
			dirname(__FILE__) . '/Views/'
		);
    	
		return dirname(__FILE__) . '/Controllers/Backend/Check.php';
    }
    
    /**
	 * Returns plugin name
	 *
	 * @return string
	 */
    public function getName()
    {
    	return 'Systeminfo';
    }
}