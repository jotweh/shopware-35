<?php
class Shopware_Plugins_Frontend_LastArticles_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
	public function install()
	{
		$event = $this->createEvent(
			'Enlight_Controller_Action_PostDispatch',
			'onPostDispatch'
		);
		$this->subscribeEvent($event);
		
		$form = $this->Form();
		
		$form->setElement('checkbox', 'show', array('label'=>'Artikelverlauf anzeigen', 'value'=>1, 'scope'=>Shopware_Components_Form::SCOPE_SHOP));

		$form->save();
		
		return true;
	}
	
	public static function onPostDispatch(Enlight_Event_EventArgs $args)
	{		
		$request = $args->getSubject()->Request();
		$response = $args->getSubject()->Response();
		
		if(!$request->isDispatched()||$response->isException()||$request->getModuleName()!='frontend') {
			return;	
		}
							
		$config = Shopware()->Plugins()->Frontend()->LastArticles()->Config();
				
		if(empty($config->show)) {
			return;
		}
		
		$view = $args->getSubject()->View();
		
		$view->assign('sLastArticles', Shopware()->Modules()->Articles()->sGetLastArticles(), true);
	}
}