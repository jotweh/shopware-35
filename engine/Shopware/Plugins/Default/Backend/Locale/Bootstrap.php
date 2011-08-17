<?php
/**
 * Shopware Backend Locale Plugin
 * 
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @package Shopware
 * @subpackage Plugins
 * @author H.Lohaus
 */
class Shopware_Plugins_Backend_Locale_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
	/**
	 * Install plugin method
	 *
	 * @return bool
	 */
	public function install()
	{		
		$event = $this->createEvent(
	 		'Enlight_Controller_Action_PostDispatch_Backend_Auth',
	 		'onPostDispatchAuth'
	 	);
	 	$this->subscribeEvent($event);
		$event = $this->createEvent(
	 		'Enlight_Controller_Action_PreDispatch',
	 		'onPreDispatchBackend'
	 	);
		$this->subscribeEvent($event);
		
		$form = $this->Form();
		$form->setElement('text', 'locales', array('label'=>'Auswählbare Sprachen','value'=>'de_DE,'));
		$form->save();
		
		return true;
	}
	
	/**
	 * Event listener method
	 *
	 * @param Enlight_Event_EventArgs $args
	 */
	public static function onPostDispatchAuth(Enlight_Event_EventArgs $args)
	{
		$request = $args->getSubject()->Request();
		$identity = Shopware()->Auth()->getIdentity();
		
		if($identity !== null 
		  && $request->getPost('locale')) {
			$identity->locale = new Shopware_Models_Locale($request->getPost('locale'));
		}
	}
	
	/**
	 * Event listener method
	 *
	 * @param Enlight_Event_EventArgs $args
	 */
	public static function onPreDispatchBackend(Enlight_Event_EventArgs $args)
	{
		$request = $args->getSubject()->Request();
		
		if($request->getModuleName() != 'backend' 
		  || $request->getControllerName() == 'error') {
			return;
		}
		
		$view = $args->getSubject()->View();
		$locales = self::getLocales();
		$identity = Shopware()->Auth()->getIdentity();
		
		if(isset($identity->locale)) {
		  	$locale = $identity->locale;
		} elseif(Shopware()->Bootstrap()->issetResource('Locale')) {
			$locale = Shopware()->Locale();
		} else {
			$locale = new Shopware_Models_Locale();
		}
		
		if(!empty($locales) 
		  && (!isset($locales[$locale->getId()])
		  || !$locale instanceof Shopware_Models_Locale)) {
			$locale = reset($locales);
		}
		
		Shopware()->Bootstrap()->registerResource('Locale', $locale);
		
		$view->LocaleOptions = $locales;
		$view->Locale = $locale;
		
		$view->Engine()->setCompileId($locale->toString());
	}
	
	/**
	 * Returns an array of valid locales
	 *
	 * @return array
	 */
	public static function getLocales()
	{
		$locales = Shopware()->Plugins()->Backend()->Locale()->Config()->locales;
		$locales = explode(',', trim($locales));
		
		$result = array();
		foreach ($locales as $locale) {
			$locale = new Shopware_Models_Locale($locale);
			if($locale->getId()) {
				$result[$locale->getId()] = $locale;
			}
		}
		return $result;
	}
}