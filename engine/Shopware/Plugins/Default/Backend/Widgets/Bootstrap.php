<?php
/**
 * Shopware default widgets
 *
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Stefan Hamann
 * @package Shopware
 * @subpackage Plugins
 */
class Shopware_Plugins_Backend_Widgets_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
	/**
	 * Install shopware default widgets
	 * @return bool
	 */
	public function install()
	{
		$form = $this->Form();

		$form->setElement('text', 'columns', array('label'=>'Anzahl Spalten', 'value'=>4, 'scope'=>Shopware_Components_Form::SCOPE_SHOP));

		$form->save();

		
		// Controller to load data from
		$event = $this->createEvent(
		'Enlight_Controller_Dispatcher_ControllerPath_Backend_WidgetDataStore',
		'onGetControllerPathBackend'
		);
		$this->subscribeEvent($event);

		$event = $this->createEvent(
			'Enlight_Controller_Action_PostDispatch_Backend_Widgets',
			'onPostDispatch'
		);
		$this->subscribeEvent($event);

		// Conversion Rate
		$this->createWidget(
			'ShopwareConversion',	// Unique name
			'Shows increase / decrease of conversion rate',	// Description
			array(
				"timeBack" =>array("type"=>"text","value"=>"30","name"=>"timeBack","label"=>"Range in days for comparision","isRequired"=>true),
				"refresh" =>array("type"=>"text","value"=>"600","name"=>"refresh","label"=>"Refresh Interval in seconds","isRequired"=>false),
			),
			'backend/plugins/widgets/widgetConversion.tpl',
			dirname(__FILE__)."/Views/"
		);

		// Amount
		$this->createWidget(
			'ShopwareAmountChart',	// Unique name
			'Display amount grouped by days',	// Description
			array(
				"subshopID" =>array("type"=>"text","value"=>"","name"=>"subshopID","label"=>"Restrict to a certain subshop (id)","isRequired"=>false),
				"refresh" =>array("type"=>"text","value"=>"600","name"=>"refresh","label"=>"Refresh Interval in seconds","isRequired"=>false),
				"timeBack" =>array("type"=>"text","value"=>"14","name"=>"timeBack","label"=>"Show last n days","isRequired"=>false),
			),
			'backend/plugins/widgets/widgetAmountChart.tpl',
			dirname(__FILE__)."/Views/"
		);

		// Last orders
		$this->createWidget(
			'ShopwareLastOrders',				// Unique name
			'Show a grid with last orders',		// Description
			array(
				"subshopID" =>array("type"=>"text","value"=>"","name"=>"subshopID","label"=>"Restrict to a certain subshop (id)","isRequired"=>false),
				"restrictPayment" =>array("type"=>"text","value"=>"","name"=>"restrictPayment","label"=>"Restrict to a certain paymentmean","isRequired"=>false),
				"refresh" =>array("type"=>"text","value"=>"600","name"=>"refresh","label"=>"Refresh Interval in seconds","isRequired"=>false),
			),
			'backend/plugins/widgets/widgetLastOrders.tpl',
			dirname(__FILE__)."/Views/"
		);

		// Last edited articles
		$this->createWidget(
			'ShopwareLastEdits',				// Unique name
			'Show a grid with last edited articles',		// Description
			array(
				"refresh" =>array("type"=>"text","value"=>"600","name"=>"refresh","label"=>"Refresh Interval in seconds","isRequired"=>false),
			),
			'backend/plugins/widgets/widgetLastEdit.tpl',
			dirname(__FILE__)."/Views/"
		);
		
		// Visitors / Page-Impressions
		$this->createWidget(
			'ShopwareViewStats',				// Unique name
			'Show visitors / pi',		// Description
			array(
				"refresh" =>array("type"=>"text","value"=>"600","name"=>"refresh","label"=>"Refresh Interval in seconds","isRequired"=>false),
				"timeBack" =>array("type"=>"text","value"=>"14","name"=>"timeBack","label"=>"Timerange to compare visisitors in days","isRequired"=>false),
			),
			'backend/plugins/widgets/widgetViewStats.tpl',
			dirname(__FILE__)."/Views/"
		);

		// Notepad
		$this->createWidget(
			'ShopwareNotepad',				// Unique name
			'Simple nodepad',		// Description
			array(
	
			),
			'backend/plugins/widgets/widgetNotepad.tpl',
			dirname(__FILE__)."/Views/"
		);

		// Referer List
		$this->createWidget(
			'ShopwareReferer',				// Unique name
			'Top referers today',		// Description
			array(

			),
			'backend/plugins/widgets/widgetReferers.tpl',
			dirname(__FILE__)."/Views/"
		);

		Shopware()->Db()->query("CREATE TABLE IF NOT EXISTS `s_plugin_widgets_notes` (
		`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
		`userID` INT NOT NULL ,
		`notes` TEXT NOT NULL
		) ENGINE = MYISAM ;
		");
		

		return true;
	}

	/**
	 * Uninstall default widgets and drop table s_plugin_widgets_notes
	 * @return bool
	 */
	public function uninstall(){
		Shopware()->Db()->query("
		DROP TABLE IF EXISTS `s_plugin_widgets_notes`
		");

		return parent::uninstall();

	}

	/**
	 * Adding controller to get widget data (json)
	 * @static
	 * @param Enlight_Event_EventArgs $args
	 * @return string
	 */
	public static function onGetControllerPathBackend(Enlight_Event_EventArgs $args){
		$file = dirname(__FILE__)."/Controllers/Backend/WidgetDataStore.php";
		if (!is_file($file)){
			die("File $file not found");
		}
		return $file;
	}

	/**
	 * Adding widget resources dynamically to widget-/panel-controller
	 * @static
	 * @param Enlight_Event_EventArgs $args
	 * @return void
	 */
	public static function onPostDispatch(Enlight_Event_EventArgs $args)
	{
		$view = $args->getSubject()->View();
		$view->addTemplateDir(dirname(__FILE__)."/Views/");
		$view->extendsTemplate(dirname(__FILE__).'/Views/backend/plugins/widgets/extends.tpl');
	}

	/**
	 * Get-Version of plugin
	 * @return string
	 */
	public function getVersion(){
		return "1.0.0";
	}

	/**
	 * Get Plugin Meta-data
	 * @return array
	 */
	public function getInfo(){
		return array(
    		'version' => $this->getVersion(),
			'autor' => 'shopware AG',
			'copyright' => 'Copyright © 2011, shopware AG',
			'label' => 'Shopware Standard Widgets',
			'source' => $this->getSource(),
			'description' => '',
			'license' => '',
			'support' => 'http://www.shopware.de/wiki/',
			'link' => 'http://www.shopware.de/'
    	);
	}
}