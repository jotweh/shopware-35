<?php
class Shopware_Plugins_Frontend_Facebook_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
	public function install()
	{		
		$event = $this->createEvent(
	 		'Enlight_Controller_Action_PostDispatch_Frontend_Detail',
	 		'onPostDispatchDetail'
	 	);
		$this->subscribeEvent($event);
		$form = $this->Form();
	 	
		$form->setElement('checkbox', 'show', array('label'=>'Facebook zeigen', 'value'=>1, 'scope'=>Shopware_Components_Form::SCOPE_SHOP));
		$form->setElement('text', 'app_id', array('label'=>'Facebook App-ID','value'=>'', 'scope'=>Shopware_Components_Form::SCOPE_SHOP));
	
		$form->save();
	 	return true;
	}
	
	public static function onPostDispatchDetail(Enlight_Event_EventArgs $args)
	{
		$request = $args->getSubject()->Request();
		$response = $args->getSubject()->Response();
		$view = $args->getSubject()->View();
		$config = Shopware()->Plugins()->Frontend()->Facebook()->Config();
		
		if(empty($config->show) && $config->show!==null) {
			return;
		}
		
		$view->app_id = $config->app_id;
		
		if (!empty($_SERVER["HTTP_USER_AGENT"]) && preg_match("/MSIE 6/",$_SERVER['HTTP_USER_AGENT'])){
			$view->hideFacebook = true;
		} else {
			$view->hideFacebook = false;
		}
		
		$article = $view->sArticle;
		$view->unique_id = Shopware()->Shop()->getId().'_'.$article['articleID'];
		$view->extendsTemplate('frontend/plugins/facebook/blocks_detail.tpl');
	}
}