<?php
class Shopware_Plugins_Frontend_TagCloud_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
	public function install()
	{
		$event = $this->createEvent(
			'Enlight_Controller_Action_PostDispatch',
			'onPostDispatch'
		);
		$this->subscribeEvent($event);
		
		$form = $this->Form();
		
		$form->setElement('checkbox', 'show', array('label'=>'Tag-Cloud anzeigen', 'value'=>1, 'scope'=>Shopware_Components_Form::SCOPE_SHOP));
		$form->setElement('text', 'controller', array('label'=>'Controller-Auswahl', 'value'=>'index, listing', 'scope'=>Shopware_Components_Form::SCOPE_SHOP));
		
		$form->save();
		
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
		
		$config = Shopware()->Plugins()->Frontend()->TagCloud()->Config();
				
		if(empty($config->show)) {
			return;
		}
		
		if(strpos($config->controller, $request->getControllerName())!==false) {
			if(!$view->isCached()) {
				$view->sCloud = Shopware()->Modules()->Marketing()->sBuildTagCloud();
			}
		}
	}
}