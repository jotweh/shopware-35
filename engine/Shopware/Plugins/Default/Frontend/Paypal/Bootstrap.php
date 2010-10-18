<?php
class Shopware_Plugins_Frontend_Paypal_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
	public function install()
	{
		$event = $this->createEvent(
			'Enlight_Controller_Action_PostDispatch',
			'onPostDispatch'
		);
		$this->subscribeEvent($event);
		
		$event = $this->createEvent(
			'Enlight_Controller_Dispatcher_ControllerPath_Frontend_Paypal',
			'onGetControllerPath'
		);
		$this->subscribeEvent($event);
		
		$event = $this->createEvent(
			'Enlight_Controller_Front_PreDispatch',
			'onPreDispatch',
			10
		);
		$this->subscribeEvent($event);
				
		return true;
	}
	
	public static function onGetControllerPath(Enlight_Event_EventArgs $args)
    {
		return dirname(__FILE__).'/Paypal.php';
    }
    
	public static function onPostDispatch(Enlight_Event_EventArgs $args)
	{
		$request = $args->getSubject()->Request();
		$response = $args->getSubject()->Response();
		$view = $args->getSubject()->View();
		
		if(!$request->isDispatched()||$response->isException()||$request->getModuleName()!='frontend') {
			return;
		}
					
		$view->addTemplateDir(dirname(__FILE__).'/templates/');	
		$view->extendsTemplate('frontend/plugins/paypal/index.tpl');
	}
	
	public static function onPreDispatch(Enlight_Event_EventArgs $args)
	{
		$request = $args->getSubject()->Request();
		
		if($request->getModuleName()&&$request->getModuleName()!='frontend') {
			return;
		}
		
		if(Shopware()->Config()->TemplateOld) {
			return;
		}
		
		$viewport = $request->getControllerName()!='viewport' ? $request->getControllerName() : $request->getParam('sViewport');
		
		switch ($viewport)
		{
			case 'sViewport,paypalexpressAPIError':
			case 'paypalexpressAPIError':
				$request->setControllerName('paypal')->setActionName('errorApi')->setDispatched(false);
				break;
			case 'sViewport,paypalexpressGA':
			case 'paypalexpressGA':
				$request->setControllerName('paypal')->setActionName('guest')->setDispatched(false);
				break;
			case 'sViewport,paypalexpressGAReg':
			case 'paypalexpressGAReg':
				$request->setControllerName('paypal')->setActionName('register')->setDispatched(false);
				break;
			default:
				break;
		}
	}
}