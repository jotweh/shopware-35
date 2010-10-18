<?php
class Shopware_Controllers_Frontend_Content extends Enlight_Controller_Action
{
	public function preDispatch()
	{
		return;
		$this->View()->setCaching(true);
		$this->View()->addCacheID(array(
			'frontend',
			'content',
			(int) $this->Request()->sContent,
			(int) $this->Request()->sCid
		));
	}
	
	public function indexAction()
	{
		if (empty($this->Request()->sContent)){
			return $this->forward('index','index');
		}
		
		$groupID = $this->Request()->sContent;
		$detailID = $this->Request()->sCid;
		
		if (!empty($detailID)){
			$this->view->loadTemplate('frontend/content/detail.tpl');
		}
		
		if(!$this->view->isCached()) {
			if (!empty($detailID)){
				$sContent = Shopware()->Modules()->Cms()->sGetDynamicContentById($groupID, $detailID);
				$this->view->sContentItem = $sContent['sContent'];
				$this->view->sPages = $sContent['sPages'];
			} else {
				$sContent = Shopware()->Modules()->Cms()->sGetDynamicContentByGroup($groupID, $this->Request()->sPage);
				$this->view->sContent = $sContent['sContent'];
				$this->view->sPages = $sContent['sPages'];
			}
			$this->view->sContentName = Shopware()->Modules()->Cms()->sGetDynamicGroupName($groupID);
			$this->view->sBreadcrumb = array(0=>array('name'=>$this->view->sContentName));
		}
	}
}