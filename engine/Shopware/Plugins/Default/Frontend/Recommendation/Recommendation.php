<?php
class Shopware_Controllers_Frontend_Recommendation extends Enlight_Controller_Action
{
	public function viewedAction(){
		if (empty($this->Request()->article)){
			throw new Enlight_Exception("Missing article-id");
		}
		$config = Shopware()->Plugins()->Frontend()->Recommendation()->Config();
		
		$page = empty($this->Request()->pages) ? 1 : (int) $this->Request()->pages;
		$maxArticles = empty($config->max_seen_articles) ? 40 : (int) $config->max_seen_articles;
		$perPage = empty($config->page_seen_articles) ? 4 : (int) $config->page_seen_articles;
		
		// Ignore articles that are already in shopping cart 
		Shopware()->Modules()->Crossselling()->sBlacklist = Shopware()->Modules()->Basket()->sGetBasketIds();
		Shopware()->Modules()->Crossselling()->sBlacklist[] = $this->Request()->article;
		
		// Get the articles which were viewed in the context of the current article
		$articles = Shopware()->Modules()->Crossselling()->sGetSimilaryShownArticles($this->Request()->article,$maxArticles);
		// Split result into chunks
		$articles = array_chunk($articles,$perPage);
		// Count pages
		$pages = count($articles);
		// Define current scope
		$articles = $articles[$page-1];
		
		foreach ($articles as $article){
			$tmpContainer =  Shopware()->Modules()->Articles()->sGetPromotionById("fix",0,$article['id']);
			if (!empty($tmpContainer["articleName"])){
				$result[] = $tmpContainer;
			}
		}
		$this->View()->articles = $result;
		$this->View()->pages = $pages;
		$this->View()->loadTemplate("frontend/plugins/recommendation/slide_articles.tpl");
	}
	
	public function boughtAction()
	{ 
		if (empty($this->Request()->article)){
			throw new Enlight_Exception("Missing article-id");
		}
		$config = Shopware()->Plugins()->Frontend()->Recommendation()->Config();

		$page = empty($this->Request()->pages) ? 1 : (int) $this->Request()->pages;
		$maxArticles = empty($config->max_bought_articles) ? 40 : (int) $config->max_bought_articles;
		$perPage = empty($config->page_bought_articles) ? 4 : (int) $config->page_bought_articles;
		
		// Ignore articles that are already in shopping cart 
		
		Shopware()->Modules()->Crossselling()->sBlacklist = Shopware()->Modules()->Basket()->sGetBasketIds();
		Shopware()->Modules()->Crossselling()->sBlacklist[] = $this->Request()->article;
		// Get the articles which were viewed in the context of the current article
		$articles = Shopware()->Modules()->Crossselling()->sGetAlsoBoughtArticles($this->Request()->article,$maxArticles);
		// Split result into chunks
		$articles = array_chunk($articles,$perPage);
		// Count pages
		$pages = count($articles);
		// Define current scope
		$articles = $articles[$page-1];
		
		foreach ($articles as $article){
			$tmpContainer =  Shopware()->Modules()->Articles()->sGetPromotionById("fix",0,$article['id']);
			if (!empty($tmpContainer["articleName"])){
				$result[] = $tmpContainer;
			}
		}
		$this->View()->articles = $result;
		$this->View()->pages = $pages;
		$this->View()->loadTemplate("frontend/plugins/recommendation/slide_articles.tpl");
	}
	
	public function similaryViewedAction()
	{
		if (empty($this->Request()->category)){
			throw new Enlight_Exception("Missing category-id");
		}
		$config = Shopware()->Plugins()->Frontend()->Recommendation()->Config();

		$page = empty($this->Request()->pages) ? 1 : (int) $this->Request()->pages;
		$maxArticles = empty($config->max_simlar_articles) ? 20 : (int) $config->max_simlar_articles;
		$perPage = empty($config->page_similar_articles) ? 3 : (int) $config->page_similar_articles;
		
		$getLastViewed = Shopware()->Modules()->Articles()->sGetLastArticles();
		foreach ($getLastViewed as $v){
			$lastViewed[] = $v["articleID"];
		}
		if (!count($lastViewed)){
			$this->View()->setTemplate();
			return;
		}
		$sql = "
			SELECT e1.articleID as id, COUNT(e1.articleID) AS hits
			FROM s_emarketing_lastarticles AS e1,
			s_emarketing_lastarticles AS e2,
			s_articles_categories ac,
			s_articles a
			WHERE ac.categoryID=?
			AND ac.articleID=e1.articleID
			AND e2.articleID IN (".implode(",",$lastViewed).")
			AND e1.sessionID=e2.sessionID
			AND a.id=e1.articleID
			AND (
				SELECT articleID 
				FROM s_articles_avoid_customergroups 
				WHERE articleID = a.id AND customergroupID = ".Shopware()->System()->sUSERGROUPDATA["id"]."
			) IS NULL
			AND a.active=1
			AND a.mode=0
			AND e1.articleID NOT IN (".implode(",",$lastViewed).")
			GROUP BY e1.articleID
			ORDER BY hits DESC
			LIMIT $maxArticles
		";
		$articles = Shopware()->Db()->fetchAll($sql, array($this->Request()->category));
		$articles = array_chunk($articles,$perPage);
		$pages = count($articles);
		$articles = $articles[$page-1];
		
		foreach ($articles as $article){
			$tmpContainer =  Shopware()->Modules()->Articles()->sGetPromotionById("fix", 0, (int) $article['id']);
			if (!empty($tmpContainer["articleName"])){
				$result[] = $tmpContainer;
			}
		}
		$this->View()->articles = $result;
		$this->View()->pages = $pages;
		$this->View()->loadTemplate("frontend/plugins/recommendation/slide_articles.tpl");
	}
	
	public function newAction()
	{
		if (empty($this->Request()->category)){
			throw new Enlight_Exception("Missing category-id");
		}
		$config = Shopware()->Plugins()->Frontend()->Recommendation()->Config();

		$page = empty($this->Request()->pages) ? 1 : (int) $this->Request()->pages;
		$maxArticles = empty($config->max_new_articles) ? 20 : (int) $config->max_new_articles;
		$perPage = empty($config->page_new_articles) ? 3 : (int) $config->page_new_articles;
		
		$sql = "
			SELECT s_articles.id AS id
			FROM s_articles, s_articles_categories 
			WHERE active=1 AND mode = 0
			AND (
				SELECT articleID 
				FROM s_articles_avoid_customergroups 
				WHERE articleID = s_articles.id AND customergroupID = ".Shopware()->System()->sUSERGROUPDATA["id"]."
			) IS NULL
			AND s_articles.id=s_articles_categories.articleID
			AND s_articles_categories.categoryID=?
			ORDER BY datum DESC LIMIT $maxArticles
		";
		$articles = Shopware()->Db()->fetchAll($sql,array($this->Request()->category));
		$articles = array_chunk($articles,$perPage);
		// Count pages
		$pages = count($articles);
		// Define current scope
		$articles = $articles[$page-1];
		
		foreach ($articles as $article) {
			$tmpContainer =  Shopware()->Modules()->Articles()->sGetPromotionById("fix", 0, (int) $article['id']);
			if (!empty($tmpContainer["articleName"])){
				$result[] = $tmpContainer;
			}
		}
		$this->View()->articles = $result;
		$this->View()->pages = $pages;
		$this->View()->loadTemplate("frontend/plugins/recommendation/slide_articles.tpl");
	}
		
	public function suppliersAction()
	{
		if (empty($this->Request()->category)){
			throw new Enlight_Exception("Missing category-id");
		}
		$getSuppliers = Shopware()->Modules()->Articles()->sGetAffectedSuppliers($this->Request()->category);
	}
}