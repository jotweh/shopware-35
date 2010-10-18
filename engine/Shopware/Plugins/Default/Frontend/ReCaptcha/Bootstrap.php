<?php
class Shopware_Plugins_Frontend_ReCaptcha_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
	protected $recaptcha;
	protected $errorCode = null;
	
	public function install()
	{		
		return false;
		$event = $this->createEvent(
	 		'Enlight_Controller_Action_PostDispatch_Frontend_Detail',
	 		array($this, 'onPostDispatch')
	 	);
		$this->subscribeEvent($event);
		
		$event = $this->createEvent(
	 		'Enlight_Controller_Action_PreDispatch_Frontend_Detail',
	 		array($this, 'onPreDispatch')
	 	);
		$this->subscribeEvent($event);
	}
	
	function getRecaptcha()
	{
		if(!isset($this->recaptcha))
		{
			$pubKey = '6Lf3sbwSAAAAALG6MB4s2eVv68JXt6cDTVkIVQWh';
			$privKey = '6Lf3sbwSAAAAAFjiBWfO8Ra3bWFi_GWNhTChxs0E';
			
	      	$this->recaptcha = new Zend_Service_ReCaptcha($pubKey, $privKey);
		}
		return $this->recaptcha;
	}
	
	function onPostDispatch(Enlight_Event_EventArgs $args)
	{				
		if(!$args->getSubject()->Request()->isDispatched()) return;
					
		$view = $args->getSubject()->View();
		$request = $args->getSubject()->Request();
		
		$view->addTemplateDir(dirname(__FILE__).'/templates/'); // Template-Verzeichniss hinzufügen
		$view->extendsTemplate('frontend/widgets/recaptcha/detail.tpl'); // aktuelles Template extenden
		
		$lang = Shopware()->Locale()->getLanguage(); // Sprache auslesen
		
		$this->getRecaptcha()->setParams(array('ssl' => $request->isSecure(), 'xhtml' => true, 'error'=>$this->errorCode));
		$this->getRecaptcha()->setOptions(array('theme' => 'white', 'lang' => $lang));
		
		$view->assign('sReCaptcha', $this->getRecaptcha()->getHTML(), true);
	}
	
	function onPreDispatch(Enlight_Event_EventArgs $args)
	{
		$request = $args->getSubject()->Request();
		
		if(!$request->getPost('recaptcha_response_field'))
		{
			Shopware()->Session()->sCaptcha = 'ReCaptcha';
			Shopware()->System()->_POST['sCaptcha'] = '';
			return;
		}
		
		$result = $this->getRecaptcha()->verify(
			$request->getPost('recaptcha_challenge_field'),
			$request->getPost('recaptcha_response_field')
		);
		
		$this->errorCode = $result->getErrorCode();
		
		if (!$result->isValid()) {
			Shopware()->Session()->sCaptcha = 'ReCaptcha';
			Shopware()->System()->_POST['sCaptcha'] = $request->getPost('recaptcha_response_field');
		} else {
			Shopware()->Session()->sCaptcha = 'ReCaptcha';
			Shopware()->System()->_POST['sCaptcha'] = 'ReCaptcha';
		}
	}
}