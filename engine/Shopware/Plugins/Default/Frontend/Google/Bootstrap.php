<?php
class Shopware_Plugins_Frontend_Google_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
	public function install()
	{
		$event = $this->createEvent(
			'Enlight_Controller_Action_PostDispatch',
			'onPostDispatch'
		);
		$this->subscribeEvent($event);
		
		$form = $this->Form();
		
		$form->setElement('text', 'tracking_code', array(
			'label'=>'Google Analytics-ID',
			'value'=>Shopware()->Config()->GoogleCode,
			'scope'=>Shopware_Components_Form::SCOPE_SHOP
		));
		$form->setElement('text', 'conversion_code', array(
			'label'=>'Google Conversion-ID',
			'value'=>Shopware()->Config()->GoogleConversion,
			'scope'=>Shopware_Components_Form::SCOPE_SHOP
		));
		
		$form->save();
				
		return true;
	}
	
	public static function onPostDispatch(Enlight_Event_EventArgs $args)
	{	
		$request = $args->getSubject()->Request();
		$response = $args->getSubject()->Response();
				
		if(!$request->isDispatched()||$response->isException()||$request->getModuleName()!='frontend'){
			return;
		}
		
		$config = Shopware()->Plugins()->Frontend()->Google()->Config();

		if(empty($config->tracking_code)&&empty($config->conversion_code)) {
			return;
		}

		$view = $args->getSubject()->View();
		
		$view->addTemplateDir(dirname(__FILE__).'/templates/');
		$view->extendsTemplate('frontend/widgets/google/index.tpl');
		
		if(!empty($config->conversion_code)) {
			$view->GoogleConversionID = $config->conversion_code;
			$view->GoogleConversionLanguage = Shopware()->Locale()->getLanguage();
		}
		if(!empty($config->tracking_code)) {
			$view->GoogleTrackingID = $config->tracking_code;
		}
	}
}