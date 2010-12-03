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
		
		$form = $this->Form();
		$form->setElement('checkbox', 'show', array('label'=>'Link im Kundenkonto zeigen', 'value'=>1, 'scope'=>Shopware_Components_Form::SCOPE_SHOP));
		$form->save();
		
		return true;
	}
	
	public static function onPostDispatch(Enlight_Event_EventArgs $args)
	{			
		$view = $args->getSubject()->View();
		$request = $args->getSubject()->Request();
		$response = $args->getSubject()->Response();
		$config = Shopware()->Plugins()->Frontend()->Ticket()->Config();
		
		if(!$request->isDispatched()||$response->isException()||$request->getModuleName()!='frontend') {
			return;
		}
		
		if(empty($config->show) && $config->show!==null) {
			return;
		}
		
		if(in_array($request->getControllerName(), array('forms', 'account', 'note'))) {
			$view->sTicketLicensed = Shopware()->License()->checkLicense('sTICKET');
		}
	}
}