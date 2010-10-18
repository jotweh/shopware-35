<?php
class Shopware_Controllers_Frontend_Sitemap extends Enlight_Controller_Action
{
	public function preDispatch()
	{
		return;
		$this->View()->setCaching(true);
		$this->View()->addCacheID(array(
			'frontend',
			'sitemap'
		));
	}
	
	public function indexAction()
	{
		if(!$this->view->isCached()) {
			foreach (Shopware()->Modules()->sCategories()->sGetMainCategories() as $category){
				$id = $category["id"];
				$result[] = array("link"=>$category["link"],"name"=>$category["description"],"sub"=>Shopware()->Modules()->sCategories()->sGetWholeCategoryTree($id));
			}
			
			$this->View()->sCategoryTree = $result;
		}
	}
}