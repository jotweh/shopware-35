<?php
class Shopware_Plugins_Frontend_Compare_Bootstrap extends Shopware_Components_Plugin_Bootstrap
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
	
	static function onPostDispatch(Enlight_Event_EventArgs $args)
	{	
		$request = $args->getSubject()->Request();
		$response = $args->getSubject()->Response();
		$view = $args->getSubject()->View();
					
		if(!$request->isDispatched()||$response->isException()||$request->getModuleName()!='frontend'){
			return;
		}
								
		$view->extendsTemplate('frontend/plugins/compare/index.tpl');
		
		$view->assign('sComparisons', Shopware()->Modules()->Articles()->sGetComparisons(), true);
	}
}