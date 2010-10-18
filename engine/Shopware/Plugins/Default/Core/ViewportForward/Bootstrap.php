<?php
class Shopware_Plugins_Core_ViewportForward_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
	public function install()
	{		
		$event = $this->createEvent(
	 		'Enlight_Controller_Front_PreDispatch',
	 		'onPreDispatch',
	 		10
	 	);
		$this->subscribeEvent($event);
		return true;
	}
		
	public static function onPreDispatch(Enlight_Event_EventArgs $args)
	{
		$request = $args->getSubject()->Request();
		
		if($request->getModuleName()&&$request->getModuleName()!='frontend'){
			return;
		}
		
		$viewport = $request->getControllerName()=='viewport' ? $request->getParam('sViewport') : $request->getControllerName();
		
		if(!Shopware()->Config()->TemplateOld)
		{
			switch ($viewport)
			{
				case 'sViewport,sale':
					$url = $args->getRequest()->getPathInfo();
					$url = trim($url, '/');
					
					foreach(explode('/', $url) as $part) {
						$part = explode(',', $part);
						if(!empty($part[0])&&!empty($part[1])) {
							$request->setParam($part[0], $part[1]);
						}
					}
					
					if($request->getParam('sAction')&&$request->getParam('sAction')=='doSale') {
						$request->setControllerName('checkout')->setActionName('finish')->setDispatched(false);
					} else {
						$request->setControllerName('checkout')->setActionName('confirm')->setDispatched(false);
					}
					break;
				case 'cat':
					$request->setControllerName('listing')->setDispatched(false);
					break;
				case 'password':
				case 'login':
				case 'logout':
					$request->setActionName($request->getParam('sViewport'));
				case 'admin':
					$request->setControllerName('account')->setDispatched(false);
					break;
				case 'registerFC':
				case 'register1':
				case 'register2':
				case 'register2shipping':
				case 'register3':
					$request->setControllerName('register')->setDispatched(false);
					break;
				case 'sale':
					if($request->getParam('sAction')&&$request->getParam('sAction')=='doSale') {
						$request->setControllerName('checkout')->setActionName('finish')->setDispatched(false);
					} else {
						$request->setControllerName('checkout')->setActionName('confirm')->setDispatched(false);
					}
					break;
				case 'sViewport,basket':
				case 'basket':
					$request->setControllerName('checkout')->setActionName('cart')->setDispatched(false);
					break;
				case 'searchFuzzy':
					$request->setControllerName('search')->setActionName('index')->setDispatched(false);
					break;
				case 'newsletterListing':
					$request->setControllerName('newsletter')->setActionName('listing')->setDispatched(false);
					break;
				case 'support':
					$request->setControllerName('forms')->setActionName('index')->setDispatched(false);
					break;
				case 'ticketdirect':
					$request->setControllerName('ticket')->setActionName('direct')->setDispatched(false);
					break;
				default:
					break;
			}
		}
	}
	
	public function getCapabilities()
    {
        return array(
    		'install' => true,
    		'update' => true
    	);
    }
}