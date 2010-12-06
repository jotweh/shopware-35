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
		
		$form = $this->Form();
		$form->setElement('checkbox', 'show', array('label'=>'Vergleich zeigen', 'value'=>1, 'scope'=>Shopware_Components_Form::SCOPE_SHOP));
		$form->save();
		
		return true;
	}
	
	static function onPostDispatch(Enlight_Event_EventArgs $args)
	{	
		$request = $args->getSubject()->Request();
		$response = $args->getSubject()->Response();
		$view = $args->getSubject()->View();
		$config = Shopware()->Plugins()->Frontend()->Compare()->Config();
		
		if(empty($config->show) && $config->show!==null) {
			return;
		}
					
		if(!$request->isDispatched()||$response->isException()||$request->getModuleName()!='frontend'){
			return;
		}
								
		$view->extendsTemplate('frontend/plugins/compare/index.tpl');
		
		$view->assign('sComparisons', Shopware()->Modules()->Articles()->sGetComparisons(), true);
	}
}