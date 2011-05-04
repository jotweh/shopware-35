<?php
/**
 * Shopware default widgets
 *
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Heiner Lohaus
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
				"subshopID" =>array("type"=>"text","value"=>"","name"=>"subshopID","label"=>"Restrict to a certain subshop (id)","isRequired"=>false),
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
			),
			'backend/plugins/widgets/widgetAmountChart.tpl',
			dirname(__FILE__)."/Views/"
		);

		// Last edited articles
		$this->createWidget(
			'ShopwareLastEdits',				// Unique name
			'Show a grid with last edited articles',		// Description
			array(

			),
			'backend/plugins/widgets/widgetLastEdit.tpl',
			dirname(__FILE__)."/Views/"
		);
		
		// Visitors / Page-Impressions
		$this->createWidget(
			'ShopwareViewStats',				// Unique name
			'Show visitors / pi',		// Description
			array(

			),
			'backend/plugins/widgets/widgetViewStats.tpl',
			dirname(__FILE__)."/Views/"
		);

		

		return true;
	}

	/**
	 * Adding controller to get widget data (json)
	 * @static
	 * @param Enlight_Event_EventArgs $args
	 * @return string
	 */
	public static function onGetControllerPathFrontend(Enlight_Event_EventArgs $args){
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
		$request = $args->getSubject()->Request();
		$response = $args->getSubject()->Response();
		$view = $args->getSubject()->View();
		$view->addTemplateDir(dirname(__FILE__)."/Views/");
		$view->extendsTemplate(dirname(__FILE__).'/Views/backend/plugins/widgets/extends.tpl');
	}

	public function getVersion(){
		return "1.0.0";
	}
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