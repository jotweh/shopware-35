<?php
/**
 * Shopware Payment Plugin
 * 
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Heiner Lohaus
 * @package Shopware
 * @subpackage Plugins
 */
class Shopware_Plugins_Frontend_Payment_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
	/**
	 * Install plugin method
	 *
	 * @return bool
	 */
	public function install()
	{
		$sql = '
			ALTER TABLE `s_core_paymentmeans` ADD `action` VARCHAR( 255 ) NULL ,
			ADD `pluginID` INT( 11 ) UNSIGNED NULL;
		';
		try {
			Shopware()->Db()->exec($sql);
		} catch (Exception $e) { }
		
		$event = $this->createEvent(
	 		'Enlight_Bootstrap_InitResource_Payments',
	 		'onInitResourcePayments'
	 	);
		$this->subscribeEvent($event);
		return true;
	}
	
	/**
	 * Event listener method
	 *
	 * @param Enlight_Event_EventArgs $args
	 */
	public static function onInitResourcePayments(Enlight_Event_EventArgs $args)
	{
		$resource = new Shopware_Models_PaymentManager();
        return $resource;
	}
}