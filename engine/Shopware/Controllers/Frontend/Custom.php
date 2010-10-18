<?php
class Shopware_Controllers_Frontend_Custom extends Enlight_Controller_Action
{	
	public function preDispatch()
	{
		return;
		$this->View()->setCaching(true);
		$this->View()->setCacheID(array($this->Request()->sCustom));
	}
	
	public function indexAction()
	{
		if($this->request->isXmlHttpRequest()) {
			$this->view->loadTemplate('frontend/custom/ajax.tpl');
		}
		if(!$this->view->isCached() || $this->request->isXmlHttpRequest())
		{
			$static_pages = Shopware()->Modules()->Cms()->sGetStaticPage($this->request->getParam('sCustom'));
			
			if (!empty($static_pages['html']))
			{
				$this->view->sContent = $static_pages['html'];
			}
			
			for ($i=1;$i<=3;$i++)
			{
				if (empty($static_pages['tpl'.$i.'variable'])||!empty($static_pages['tpl'.$i.'path'])) continue;
				if(!$this->action->View()->templateExists($static_pages['tpl'.$i.'path'])) continue;
				
				$this->action->View()->assign($static_pages['tpl'.$i.'variable'], $this->action->View()->fetch($static_pages['tpl'.$i.'path']));
			}
			
			$this->view->sCustomPage = $static_pages;
			$this->view->sBreadcrumb = array(0=>array('name'=>$static_pages['description']));
		}
		
		
	}
}