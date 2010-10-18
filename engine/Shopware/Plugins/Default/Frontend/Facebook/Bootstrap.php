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
	 	
		$form->setElement('text', 'app_id', array('label'=>'Facebook App-ID','value'=>''));
	
		$form->save();
	 	return true;
	}
	public static function onPostDispatchDetail(Enlight_Event_EventArgs $args)
	{
		$request = $args->getSubject()->Request();
		$response = $args->getSubject()->Response();
		$view = $args->getSubject()->View();
		$config = Shopware()->Plugins()->Frontend()->Facebook()->Config();
		$view->app_id = $config->app_id;
		
		if (!empty($_SERVER["HTTP_USER_AGENT"]) && preg_match("/MSIE 6/",$_SERVER['HTTP_USER_AGENT'])){
			$view->hideFacebook = true;
		}else {
			$view->hideFacebook = false;
		}
		$article = $view->sArticle;
		$view->unique_id = md5($article["linkDetails"]);
		$view->extendsTemplate('frontend/plugins/facebook/blocks_detail.tpl');
	}
}