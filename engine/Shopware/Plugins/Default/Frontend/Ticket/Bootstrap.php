<?php
class Shopware_Plugins_Frontend_Ticket_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
	public function install()
	{		
		$event = $this->createEvent(
	 		'Enlight_Controller_Action_PostDispatch',
	 		'onPostDispatch'
	 	);
		$this->subscribeEvent($event);
		
		return true;
	}
	
	public static function onPostDispatch(Enlight_Event_EventArgs $args)
	{			
		$view = $args->getSubject()->View();
		$request = $args->getSubject()->Request();
		$response = $args->getSubject()->Response();
		
		if(!$request->isDispatched()||$response->isException()||$request->getModuleName()!='frontend') {
			return;
		}
		
		if(in_array($request->getControllerName(), array('forms', 'account', 'note'))) {
			$view->sTicketLicensed = Shopware()->License()->checkLicense('sTICKET');
		}
	}
}