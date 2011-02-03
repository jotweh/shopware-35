<?php
class Shopware_Controllers_Frontend_Index extends Enlight_Controller_Action
{	
	public function preDispatch()
	{
		if($this->Request()->getActionName()!='index') {
			$this->forward('index');
		}
	}
	
	public function indexAction()
	{
		$this->View()->loadTemplate('frontend/home/index.tpl');
		
		$category = Shopware()->Shop()->get('parentID');
					
		$this->View()->sCharts = Shopware()->Modules()->Articles()->sGetArticleCharts();
		$this->View()->sCategoryContent = Shopware()->Modules()->Categories()->sGetCategoryContent($category);
		
		$this->View()->sOffers = Shopware()->Modules()->Articles()->sGetPromotions($category);
		$this->View()->sLiveShopping = $this->getLiveShopping();
		$this->View()->sBanner = Shopware()->Modules()->Marketing()->sBanner($category);
		
		$this->View()->sBlog = $this->getBlog();
		
		if($this->Request()->getPathInfo()!='/') {
			 $this->Response()->setHttpResponseCode(404);
		}
	}
	
	public function getLiveShopping()
	{
		$liveShopping = Shopware()->Modules()->Articles()->sGetLiveShopping('random', 0, null, true, 'AND lv.frontpage_display=1', '', 0);
		if(!empty($liveShopping["liveshoppingData"][0]['articleID'])) {
			return $liveShopping;
		}
		return null;
	}
	
	public function getBlog()
	{
		$blog = null;
		if (!empty(Shopware()->Config()->BlogCategory)) {
			$category = isset(Shopware()->System()->_GET['sCategory']) ? Shopware()->System()->_GET['sCategory'] : null;
			$blog = Shopware()->Modules()->Articles()->sGetArticlesByCategory(Shopware()->Config()->BlogCategory, true, Shopware()->Config()->BlogLimit);
			Shopware()->System()->_GET['sCategory'] = $category;
		}
		return $blog;
	}
}