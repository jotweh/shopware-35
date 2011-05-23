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
		$this->uninstall();
		
		$this->createEvents();
		$this->createPayments();
		$this->createTable();
		$this->createMenu();
		$this->createForm();
				
		return true;
	}
	
	/**
	 * Create and subscribe events
	 */
	protected function createEvents()
	{
		$event = $this->createEvent(
			'Enlight_Controller_Dispatcher_ControllerPath_Frontend_PaymentEos',
			'onGetControllerPathFrontend'
		);
		$this->subscribeEvent($event);
		
		$event = $this->createEvent(
			'Enlight_Controller_Dispatcher_ControllerPath_Backend_PaymentEos',
			'onGetControllerPathBackend'
		);
		$this->subscribeEvent($event);
	}
	
	/**
	 * Create and save payments
	 */
	protected function createPayments()
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
	}
	
	/**
	 * Create payment table
	 */
	protected function createTable()
	{
		$sql = '
			CREATE TABLE IF NOT EXISTS `s_plugin_payment_eos` (
			  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			  `userID` int(11) unsigned NOT NULL,
			  `werbecode` varchar(255) COLLATE latin1_german1_ci DEFAULT NULL,
			  `transactionID` int(11) unsigned NOT NULL,
			  `secret` varchar(255) COLLATE latin1_german1_ci NOT NULL,
			  `reference` varchar(255) COLLATE latin1_german1_ci NOT NULL,
			  `bank_account` varchar(255) COLLATE latin1_german1_ci DEFAULT NULL,
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
			  `payment_key` varchar(255) COLLATE latin1_german1_ci NOT NULL,
			  PRIMARY KEY (`id`),
			  UNIQUE KEY `transactionID` (`transactionID`)
			) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci;
		';
		Shopware()->Db()->exec($sql);
	}
	
	/**
	 * Create payment menu item
	 */
	protected function createMenu()
	{
		$parent = $this->Menu()->findOneBy('label', 'Zahlungen');
		$item = $this->createMenuItem(array(
			'label' => 'EOS Payment',
			'onclick' => 'openAction(\'payment_eos\');',
			'class' => 'ico2 date2',
			'active' => 1,
			'parent' => $parent,
			'style' => 'background-position: 5px 5px;'
		));
		$this->Menu()->addItem($item);
		$this->Menu()->save();
	}
	
	/**
	 * Create payment config form 
	 */
	protected function createForm()
	{
		$form = $this->Form();
		$form->setElement('text', 'merchantId', array(
			'label' => 'Händler ID',
			'value' => Shopware()->Config()->clickPayMerchantId,
			'required' => true
		));
		$form->setElement('text', 'merchantCode', array(
			'label' => 'Händler Code',
			'value' => Shopware()->Config()->clickPayMerchantCode,
			'required' => true
		));
		$form->setElement('text', 'giropayProvider', array(
			'label' => 'Giropay-Provider',
			'value' => '',
			'required' => false
		));
		$form->setElement('checkbox', 'elvDirectBook', array(
			'label' => 'Sofortbuchung ELV',
			'value' => Shopware()->Config()->clickPayElvDirectBook,
			'required' => true
		));
		$form->setElement('checkbox', 'creditDirectBook', array(
			'label' => 'Sofortbuchung Kreditkarte',
			'value' => Shopware()->Config()->clickPayDirectBook,
			'required' => true
		));
		$form->setElement('checkbox', 'paymentStatusMail', array(
			'label' => 'eMail bei Zahlstatus-Änderung verschicken',
			'value' => false,
			'required' => true
		));
		$form->save();
	}
	
	/**
	 * Uninstall plugin method
	 *
	 * @return bool
	 */
	public function uninstall()
	{
		$rows = Shopware()->Payments()->fetchAll(
			array('name LIKE ?' => 'eos_%')
		);
		foreach ($rows as $row) {
			$row->delete();
		}
		
		$sql = '
			DROP TABLE IF EXISTS `s_plugin_payment_eos`;
		';
		Shopware()->Db()->exec($sql);
		return parent::uninstall();
	}
	
	/**
	 * Event listener method
	 *
	 * @param Enlight_Event_EventArgs $args
	 */
	public static function onGetControllerPathFrontend(Enlight_Event_EventArgs $args)
    {
    	Shopware()->Template()->addTemplateDir(dirname(__FILE__).'/Views/');
		return dirname(__FILE__).'/Controllers/Frontend/PaymentEos.php';
    }
    
    /**
	 * Event listener method
	 *
	 * @param Enlight_Event_EventArgs $args
	 */
	public static function onGetControllerPathBackend(Enlight_Event_EventArgs $args)
    {
    	Shopware()->Template()->addTemplateDir(dirname(__FILE__).'/Views/');
		return dirname(__FILE__).'/Controllers/Backend/PaymentEos.php';
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
		
		$view->addTemplateDir(dirname(__FILE__).'/Views/');
		//$view->extendsTemplate('frontend/payment_eos/index.tpl');
	}
}