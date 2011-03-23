<?php
/**
 * Listing controller
 * 
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Stefan Hamann
 * @author Heiner Lohaus
 * @package Shopware
 * @subpackage Controllers
 */
class Shopware_Controllers_Frontend_Listing extends Enlight_Controller_Action
{
	protected $system;
	protected $config;
	
	/**
	 * Init controller method
	 */
	public function init()
	{
		$this->system = Shopware()->System();
		$this->config = Shopware()->Config();
	}
	
	/**
	 * Index action method
	 */
	public function indexAction()
	{
		$this->system->_GET['sCategory'] = (int) $this->request->getQuery('sCategory');
		$categoryID = (int) $this->request->getQuery('sCategory');
		
		$categoryContent = Shopware()->Modules()->Categories()->sGetCategoryContent($categoryID);
		$categoryArticles = Shopware()->Modules()->Articles()->sGetArticlesByCategory($categoryID);
		if ((!$this->request->getQuery('sSupplier') || $this->request->getQuery('sSupplier')==-1) 
			&& (!$this->request->getQuery('sPage'))
			&& (!$this->request->getQuery('sFilterProperties') || $this->request->getQuery('sFilterProperties')==-1)
			&& (!$this->request->getQuery('sFilterGroup') || $this->request->getQuery('sFilterGroup')==-1))
		{
			$offers = Shopware()->Modules()->Articles()->sGetPromotions($categoryID);
			
		}
		if(!empty($categoryContent['external'])) {
			$location = $categoryContent['external'];
		} elseif ($this->config['sCATEGORYDETAILLINK']&&!empty($categoryArticles['sArticles'])&&count($categoryArticles['sArticles'])==1) {
			$categoryArticle = reset($categoryArticles['sArticles']);
			$location = array('sViewport'=>'detail', 'sArticle'=>$categoryArticle['articleID'], 'title'=>$categoryArticle['articleName']);
		} elseif(!$categoryContent) {
			$location = array('controller' => 'index');
		}
		if(isset($location)) {
			return $this->redirect($location, array('code'=>301));
		}
		
		$defaultViews = array('article_listing_1col.tpl', 'article_listing_2col.tpl', 'article_listing_3col.tpl', 'article_listing_4col.tpl');
		
		if (!empty($this->request->sTemplate)){
			Shopware()->Session()->selectedView = $this->request->sTemplate;
			
		}
			//die(Shopware()->Session()->selectedView."#");
		// Configurable listing view
		if (in_array($categoryContent['template'], $defaultViews) && !empty(Shopware()->Session()->selectedView) && empty($offers) && $categoryContent["noviewselect"] != true){
			// Allow only, if no custom listing was choosen
			if (Shopware()->Session()->selectedView=="table"){
				if (!empty($categoryContent["template"]) && in_array($categoryContent["template"],array('article_listing_2col.tpl', 'article_listing_3col.tpl', 'article_listing_4col.tpl'))){
					
				}else {
					$categoryContent['template'] = 'article_listing_3col.tpl';
				}
			}else {
				$categoryContent['template'] = 'article_listing_1col.tpl';
			}
		}else {
			// Otherway disable view selector
			if (!in_array($categoryContent['template'], $defaultViews) || !empty($offers)){
				$categoryContent["noviewselect"] = true;
			}
		}
	
		
		if($this->request->getParam('sRss')||$this->request->getParam('sAtom'))
		{
			Shopware()->Config()->DontAttachSession = true;
			$type = $this->request->getParam('sRss') ? 'rss' : 'atom';
			$listing = !empty($categoryContent['blog']) ? 'blog' : 'listing';
			
			$this->response->setHeader('Content-Type', 'text/xml; charset=ISO-8859-1');
			$this->view->loadTemplate('frontend/'.$listing.'/'.$type.'.tpl');
		}
		elseif (!empty($categoryContent['template'])
			&& !in_array($categoryContent['template'], $defaultViews))
		{			
			$this->view->loadTemplate('frontend/listing/'.$categoryContent['template']);
		}
		elseif (!empty($categoryContent['blog']))
		{
			$this->view->loadTemplate('frontend/blog/index.tpl');
			$this->view->_GET = $this->request->getQuery();
		}
		
		$this->view->sCategoryContent = $categoryContent;
		$breadcrumb = $this->getBreadcrumb($categoryID);
		$this->view->sBreadcrumb = $breadcrumb;
		$this->view->sCategoryInfo = reset($breadcrumb);
		
		$liveShopping = $this->getLiveShopping($categoryID);
		
		if ($liveShopping["liveshoppingData"][0]['articleID']!=0){
			$this->View()->sLiveShopping = $liveShopping;
		}
		
		$this->view->sBanner = Shopware()->Modules()->Marketing()->sBanner($categoryID);
		
		if (!empty($offers)){
			$this->view->sOffers = $offers;
		}else {
			$this->view->assign($categoryArticles);
		}
		
		$categoryDepth = Shopware()->Modules()->Categories()->sGetCategoryDepth($categoryID);
		if ($categoryDepth<=1 && empty($categoryContent['blog'])) {
			$this->view->sCharts = Shopware()->Modules()->Articles()->sGetArticleCharts($categoryID);
		}
		$this->view->sSuppliers =  Shopware()->Modules()->Articles()->sGetAffectedSuppliers($categoryID);
		$this->view->activeFilterGroup = $this->request->getQuery('sFilterGroup');
	}
	
	/**
	 * Returns live shoppinp article
	 *
	 * @param unknown_type $categoryID
	 * @return unknown
	 */
	public function getLiveShopping($categoryID)
	{
		return Shopware()->Modules()->Articles()->sGetLiveShopping('random', $categoryID, null, true, 'AND lv.categories_display=1', '', 0);
	}
	
	/**
	 * Returns listing breadcrumb
	 *
	 * @param unknown_type $categoryID
	 * @return unknown
	 */
	public function getBreadcrumb($categoryID)
	{
		return array_reverse(Shopware()->Modules()->Categories()->sGetCategoriesByParent($categoryID));
	}
}