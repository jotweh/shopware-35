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
class Shopware_Plugins_Frontend_PaymentEos_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
	/**
	 * Install plugin method
	 *
	 * @return bool
	 */
	public function install()
	{
		$paymentRow = Shopware()->Payments()->createRow(array(
			'name' => 'eos_credit',
			'description' => 'EOS - Kreditkarte',
			'action' => 'payment_eos',
			'active' => 1,
			'pluginID' => $this->getId()
		))->save();
		
		$paymentRow = Shopware()->Payments()->createRow(array(
			'name' => 'eos_elv',
			'description' => 'EOS - Lastschrift',
			'action' => 'payment_eos',
			'active' => 1,
			'pluginID' => $this->getId()
		))->save();
		
		$paymentRow = Shopware()->Payments()->createRow(array(
			'name' => 'eos_giropay',
			'description' => 'EOS - giropay',
			'action' => 'payment_eos',
			'active' => 1,
			'pluginID' => $this->getId()
		))->save();
		
		$paymentRow = Shopware()->Payments()->createRow(array(
			'name' => 'eos_ideal',
			'description' => 'EOS - iDEAL',
			'action' => 'payment_eos',
			'active' => 1,
			'pluginID' => $this->getId()
		))->save();
		
		$event = $this->createEvent(
			'Enlight_Controller_Dispatcher_ControllerPath_Frontend_PaymentEos',
			'onGetControllerPath'
		);
		$this->subscribeEvent($event);
		
		$sql = '
			-- DROP TABLE IF EXISTS `s_plugin_payment_eos`;
			CREATE TABLE IF NOT EXISTS `s_plugin_payment_eos` (
			  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			  `userID` int(11) unsigned NOT NULL,
			  `werbecode` varchar(255) COLLATE latin1_german1_ci DEFAULT NULL,
			  `transactionID` int(11) unsigned NOT NULL,
			  `secret` varchar(255) COLLATE latin1_german1_ci NOT NULL,
			  `reference` varchar(255) COLLATE latin1_german1_ci NOT NULL,
			  `account_number` varchar(255) COLLATE latin1_german1_ci DEFAULT NULL,
			  `account_expiry` date DEFAULT NULL,
			  `fail_message` varchar(255) COLLATE latin1_german1_ci DEFAULT NULL,
			  `status` varchar(255) COLLATE latin1_german1_ci DEFAULT NULL,
			  `clear_status` int(11) NOT NULL,
			  `book_date` date DEFAULT NULL,
			  `book_amount` decimal(10,2) DEFAULT NULL,
			  `added` datetime NOT NULL,
			  `changed` datetime NOT NULL,
			  `amount` decimal(10,2) NOT NULL,
			  `currency` varchar(3) COLLATE latin1_german1_ci NOT NULL,
			  PRIMARY KEY (`id`),
			  UNIQUE KEY `transactionID` (`transactionID`)
			) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci;
		';
		Shopware()->Db()->exec($sql);
				
		return true;
	}
	
	/**
	 * Event listener method
	 *
	 * @param Enlight_Event_EventArgs $args
	 */
	public static function onGetControllerPath(Enlight_Event_EventArgs $args)
    {
		return dirname(__FILE__).'/Controllers/PaymentEos.php';
    }
    
    /**
	 * Event listener method
	 *
	 * @param Enlight_Event_EventArgs $args
	 */
	public static function onPostDispatch(Enlight_Event_EventArgs $args)
	{
		$request = $args->getSubject()->Request();
		$response = $args->getSubject()->Response();
		$view = $args->getSubject()->View();
		
		if(!$request->isDispatched() 
		  || $response->isException()
		  || $request->getModuleName()!='frontend') {
			return;
		}
					
		$view->extendsTemplate('frontend/plugins/paypal/index.tpl');
	}
}