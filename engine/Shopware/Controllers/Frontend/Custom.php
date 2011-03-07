<?php
/**
 * Custom controller
 * 
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Heiner Lohaus
 * @package Shopware
 * @subpackage Controllers
 */
class Shopware_Controllers_Frontend_Custom extends Enlight_Controller_Action
{	
	/**
	 * Pre dispatch method
	 */
	public function preDispatch()
	{
		//$this->View()->setCaching(true);
		//$this->View()->setCacheID(array($this->Request()->sCustom));
	}
	
	/**
	 * Index action method
	 */
	public function indexAction()
	{
		if($this->Request()->isXmlHttpRequest()) {
			$this->View()->loadTemplate('frontend/custom/ajax.tpl');
		}
		
		$staticPage = Shopware()->Modules()->Cms()->sGetStaticPage($this->Request()->sCustom);
		
		if (!empty($staticPage['link'])) {
			$link = Shopware()->Modules()->Core()->sRewriteLink($staticPage['link'], $staticPage['description']);
			return $this->redirect($link, array('code' => 301));
		}
		
		if (!empty($staticPage['html'])) {
			$this->View()->sContent = $staticPage['html'];
		}
				
		for ($i=1; $i<=3; $i++) {
			if (empty($staticPage['tpl'.$i.'variable'])||empty($staticPage['tpl'.$i.'path'])) {
				continue;
			}
			if(!$this->View()->templateExists($staticPage['tpl'.$i.'path'])) {
				continue;
			}
			$this->View()->assign($staticPage['tpl'.$i.'variable'], $this->View()->fetch($staticPage['tpl'.$i.'path']));
		}
		
		$this->View()->sCustomPage = $staticPage;
		$this->View()->sBreadcrumb = array(0=>array('name'=>$staticPage['description']));
	}
}