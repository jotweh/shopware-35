<?php
/**
 * Shopware LastArticles Plugin
 * 
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Heiner Lohaus
 * @package Shopware
 * @subpackage Plugins
 */
class Shopware_Plugins_Frontend_LastArticles_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
	/**
	 * Install plugin method
	 *
	 * @return bool
	 */
	public function install()
	{
		$event = $this->createEvent(
			'Enlight_Controller_Action_PostDispatch',
			'onPostDispatch'
		);
		$this->subscribeEvent($event);
		
		$form = $this->Form();
		
		$form->setElement('checkbox', 'show', array(
			'label'=>'Artikelverlauf anzeigen',
			'value'=>1,
			'scope'=>Shopware_Components_Form::SCOPE_SHOP
		));
		$form->setElement('text', 'controller', array(
			'label'=>'Controller-Auswahl',
			'value'=>'index, listing, detail, custom, newsletter, sitemap, campaign',
			'scope'=>Shopware_Components_Form::SCOPE_SHOP
		));

		$form->save();
		
		return true;
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
		
		if(!$request->isDispatched()
		  || $response->isException()
		  || $request->getModuleName() != 'frontend') {
			return;	
		}
		
		$config = Shopware()->Plugins()->Frontend()->LastArticles()->Config();
		
		if(empty($config->show)) {
			return;
		}
		if(!empty($config->controller)
		  && strpos($config->controller, $request->getControllerName()) === false) {
			return;
		}
		
		$args->getSubject()->View()->assign(
			'sLastArticles',
			Shopware()->Modules()->Articles()->sGetLastArticles(),
			true
		);
	}
}