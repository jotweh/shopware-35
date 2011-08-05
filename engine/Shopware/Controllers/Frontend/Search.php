<?php
/**
 * Shopware search controller
 * 
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Heiner Lohaus
 * @author Stefan Hamann
 * @package Shopware
 * @subpackage Controllers
 */
class Shopware_Controllers_Frontend_Search extends Enlight_Controller_Action
{	
	/**
	 * Index action method
	 *
	 * @return void
	 */
	public function indexAction()
	{
		if (!Shopware()->License()->checkLicense('sFUZZY') || !empty($this->Request()->sSearchMode)) {
			return $this->forward('search');			
		} else {
			return $this->forward('searchFuzzy');
		}
	}
	
	/**
	 * Search action method
	 */
	public function searchAction()
	{
		$search = urldecode($this->Request()->sSearch);
		
		if($location = $this->searchFuzzyCheck($search)) {
			return $this->redirect($location);
		}
		
		if ($this->Request()->sSearchMode=='supplier') {
			$variables = Shopware()->Modules()->Articles()->sGetArticlesByName('a.name ASC', '', 'supplier', $search);
			$search = urldecode($this->Request()->sSearchText);			
		} elseif ($this->Request()->sSearchMode=='newest') {
			$variables = Shopware()->Modules()->Articles()->sGetArticlesByName('a.datum DESC', '', 'newest', $search);
			$search = urldecode($this->Request()->sSearchText);		
		} else {
			$variables = Shopware()->Modules()->Articles()->sGetArticlesByName('a.topseller DESC', '', '', $search);
		}
		
		foreach ($variables['sPerPage'] as $perPageKey => &$perPage) {
			$perPage['link'] = str_replace('sPage=' . $this->Request()->sPage, 'sPage=1', $perPage['link']);
		}
		if (!empty($variables['sArticles'])){
			$searchResults = $variables['sArticles'];
		} else {
			$searchResults = $variables;
		}
		
		foreach ($searchResults as $searchResult){
			if (is_array($searchResult)) {
				$searchResult = $searchResult['id'];
			}
			$article = Shopware()->Modules()->Articles()->sGetPromotionById('fix', 0, (int) $searchResult);
			if (!empty($article['articleID'])){
				$articles[] = $article;
			}
		}
	
		$this->View()->sSearchResults = $articles;
		$this->View()->sSearchResultsNum = empty($variables['sNumberArticles']) ? count($articles) : $variables['sNumberArticles'];
		$this->View()->sSearchTerm = $search;
		$this->View()->sPages = $variables['sPages'];
		$this->View()->sPerPage = $variables['sPerPage'];
		$this->View()->sNumberPages = $variables['sNumberPages'];
	
		$this->View()->sPage = $this->Request()->sPage;
		$this->View()->loadTemplate('frontend/search/index.tpl');
	}
	
	/**
	 * Search fuzzy action method
	 *
	 * @return void
	 */
	public function searchFuzzyAction()
	{
		$config = $this->searchFuzzyInit();
		
		$location = $this->searchFuzzyCheck($config['sSearch']);
		if(!empty($location)) {
			return $this->redirect($location);
		}
		
		$links =  $this->searchFuzzyPrepareLinks($config);
		
		if(!empty(Shopware()->Config()->sFUZZYSEARCHSELECTPERPAGE)) {
			$sPerPage = preg_split('/[^0-9]/', (string) Shopware()->Config()->sFUZZYSEARCHSELECTPERPAGE, -1, PREG_SPLIT_NO_EMPTY);
		} else {
			$sPerPage = array(8, 16, 24, 48);
		}
			
		if(!empty(Shopware()->Config()->sFUZZYSEARCHPRICEFILTER)) {
			$sPriceFilter = preg_split('/[^0-9]/', (string) Shopware()->Config()->sFUZZYSEARCHPRICEFILTER, -1, PREG_SPLIT_NO_EMPTY);
		} else {
			$sPriceFilter = array(5, 10, 20, 50, 100, 300, 600, 1000, 1500, 2500, 3500, 5000);
		}
						
		$tmp = array();
		$last = 0;
		foreach ($sPriceFilter as $key => $price) {
			$tmp[$key+1] = array('start'=>$last, 'end'=>$price);
			$last = $price;
		}
		$sPriceFilter = $tmp;
		unset($tmp, $last, $key, $price);
		
		if (strlen($config['sSearch']) >= (int) Shopware()->Config()->sMINSEARCHLENGHT) {	
			Shopware()->Modules()->Search()->sInit();
			Shopware()->Modules()->Search()->sPriceFilter = $sPriceFilter;
			$sSearchResults = Shopware()->Modules()->Search()->sStartSearch($config);
			
			$sql = '
				INSERT INTO s_statistics_search (datum, searchterm, results)
				VALUES (NOW(), ?, ?)
			';
			Shopware()->Db()->query($sql,array(
				implode(' ', $sSearchResults['sSearchTerms']),
				empty($sSearchResults['sArticlesCountAll']) ? 0 : $sSearchResults['sArticlesCountAll']
			));		
			
			$sPages = array();
			for($i=0, $page=0; $i<$sSearchResults['sArticlesCount']; $i+=$config['sPerPage'], $page++) {
				if($config['sPage']-3<$page && $config['sPage']+3>$page) {
					$sPages['pages'][] = $page;
				}
			}
			$sPages['count'] = $page;
			if($config['sPage']>0)
				$sPages['before'] = $sPages['bevor'] = $config['sPage']-1;
			if($config['sPage']<$sPages['count']-1)
				$sPages['next'] = $config['sPage']+1;
			$articles = array();
			foreach ($sSearchResults['sArticles'] as $articleID){
				$article = Shopware()->Modules()->Articles()->sGetPromotionById ('fix', 0, (int) $articleID);
				if (!empty($article['articleID'])) {
					$articles[] = $article;
				}
			}
			$sSearchResults['sArticles'] = $articles;
			
			$this->View()->sRequests = $config;
			$this->View()->sSearchResults = $sSearchResults;
			$this->View()->sPerPage = $sPerPage;
			$this->View()->sLinks = $links;
			$this->View()->sPages = $sPages;
			$this->View()->sPriceFilter = $sPriceFilter;
			$this->View()->sCategoriesTree = $this->getCategoryTree($sSearchResults['sLastCategory'], $config['sMainCategoryID']);
		}
		
		$this->View()->loadTemplate('frontend/search/fuzzy.tpl');
	}
	
	/**
	 * Returns a category tree
	 *
	 * @param int $id
	 * @param int $mainId
	 * @return array
	 */
	protected function getCategoryTree ($id, $mainId)
	{
		$sql = '
			SELECT 
				`id` ,
				`description`,
				`parent`
			FROM `s_categories`
			WHERE `id`=?
		';
		$cat = Shopware()->Db()->fetchRow($sql, array($id));
		if(empty($cat['id']) || $id==$cat['parent'] || $id==$mainId) {
			return array();
		} else {
			$cats = $this->getCategoryTree($cat['parent'], $mainId);
			$cats[$id] = $cat;
			return $cats;
		}
	}
	
	/**
	 * Prepare fuzzy search links
	 *
	 * @param array $config
	 * @return array
	 */
	protected function searchFuzzyPrepareLinks($config)
	{
		$links = array();
		
		$links['sLink'] = Shopware()->Config()->BaseFile . '?sViewport=searchFuzzy';
		$links['sLink'] .= '&sSearch=' . urlencode($config['sSearch']);
		$links['sSearch'] = $this->Front()->Router()->assemble(array('sViewport' => 'search'));
		
		$links['sPage'] = $links['sLink'];
		$links['sPerPage'] = $links['sLink'];
		$links['sSort'] = $links['sLink'];
		
		$links['sFilter']['category'] = $links['sLink'];
		$links['sFilter']['supplier'] = $links['sLink'];
		$links['sFilter']['price'] = $links['sLink'];
		$links['sFilter']['propertygroup'] = $links['sLink'];
		
		$filterTypes = array('supplier', 'category', 'price', 'propertygroup');
		
		foreach ($filterTypes as $filterType) {
			if (empty($config['sFilter'][$filterType])){
				continue;
			}
			$links['sPage'] .= "&sFilter_$filterType=".$config['sFilter'][$filterType];
			$links['sPerPage'] .= "&sFilter_$filterType=".$config['sFilter'][$filterType];
			$links['sSort'] .= "&sFilter_$filterType=".$config['sFilter'][$filterType];
			
			foreach ($filterTypes as $filterType2) {
				if ($filterType != $filterType2){
					$links['sFilter'][$filterType2] .= "&sFilter_$filterType=" . urlencode($config['sFilter'][$filterType]);
				}
			}
		}
		
		foreach (array('sOrder', 'sSort', 'sPerPage') as $property){
			if(!empty($config[$property])) {
				if($property!='sPage') {
					$links['sPage'] .= "&$property=".$config[$property];
				}
				if($property!='sPerPage') {
					$links['sPerPage'] .= "&$property=".$config[$property];
				}
				$links['sFilter']['__'] .= "&$property=".$config[$property];
			}
		}
		
		foreach ($filterTypes as $filterType){
			$links['sFilter'][$filterType] .= $links['sFilter']['__'];
		}
		
		$links['sSupplier'] = $links['sSort'];
		
		return $links;
	}
	
	/**
	 * Search product by productnumber 
	 *
	 * @param string $search
	 * @return string
	 */
	protected function searchFuzzyCheck($search)
	{
		$minSearch = empty(Shopware()->Config()->sMINSEARCHLENGHT) ? 2 : (int) Shopware()->Config()->sMINSEARCHLENGHT;
		if(!empty($search) && strlen($search) >= $minSearch) {
			$sql = '
				(
					SELECT DISTINCT articleID
					FROM s_articles_groups_value
					WHERE ordernumber = ?
					GROUP BY articleID
					LIMIT 2
				) UNION ALL (
					SELECT DISTINCT articleID
					FROM s_articles_details
					WHERE ordernumber = ?
					GROUP BY articleID
					LIMIT 2
				)
			';
			$articles = Shopware()->Db()->fetchCol($sql, array($search, $search));
			
			if(empty($articles)) {
				$sql = "
					(
						SELECT DISTINCT articleID
						FROM s_articles_groups_value
						WHERE ordernumber = ?
						GROUP BY articleID
						LIMIT 2
					) UNION ALL (
						SELECT DISTINCT articleID
						FROM s_articles_details
						WHERE ordernumber = ?
						OR ? LIKE CONCAT(ordernumber, '%')
						GROUP BY articleID
						LIMIT 2
					)
				";
				$articles =  Shopware()->Db()->fetchCol($sql, array($search, $search, $search));
			}
		}
		if(!empty($articles) && count($articles)==1) {
			$sql = 'SELECT articleID FROM s_articles_categories WHERE categoryID=? AND articleID=?';
			$articles = Shopware()->Db()->fetchCol($sql, array(Shopware()->Shop()->get('parentID'), $articles[0]));
		}
		if(!empty($articles) && count($articles)==1) {
			return $this->Front()->Router()->assemble(array('sViewport'=>'detail', 'sArticle'=>$articles[0]));
		}
	}
	
	/**
	 * Init fuzzy search variables
	 *
	 * @return array
	 */
	protected function searchFuzzyInit()
	{
		$config['sMainCategoryID'] = Shopware()->Shop()->get('parentID');
		$config['sFilter']['supplier'] = (int) $this->Request()->sFilter_supplier;
		$config['sFilter']['category'] = (int) $this->Request()->sFilter_category;
		$config['sFilter']['price'] =  (int) $this->Request()->sFilter_price;
		$config['sFilter']['propertygroup'] = $this->Request()->sFilter_propertygroup;
		
		$config['sSort'] = (int) $this->Request()->sSort;
		
		if(!empty($this->Request()->sPage)) {
			$config['sPage'] = (int) $this->Request()->sPage;
		} else {
			$config['sPage'] = 0;
		}
		
		if(!empty($this->Request()->sPerPage)) {
			$config['sPerPage'] = (int) $this->Request()->sPerPage;
		} elseif(!empty(Shopware()->Config()->sFUZZYSEARCHRESULTSPERPAGE)) {
			$config['sPerPage'] = (int) Shopware()->Config()->sFUZZYSEARCHRESULTSPERPAGE;
		} else {
			$config['sPerPage'] = 8;
		}
						
		$config['sOrder'] = intval($this->Request()->sOrder);
		$config['sSearch'] = urldecode(trim(strip_tags(htmlspecialchars_decode(stripslashes($this->Request()->sSearch)))));
		
		if(function_exists('mb_detect_encoding') && function_exists('mb_check_encoding')
			&& mb_detect_encoding($config['sSearch'])=='UTF-8' && mb_check_encoding($config['sSearch'], 'UTF-8')) {
			$config['sSearch'] = utf8_decode($config['sSearch']);
		}
		
		$config['sSearchOrginal'] = $config['sSearch'];
		$config['sSearchOrginal'] = htmlspecialchars($config['sSearchOrginal']);
		
		return $config;
	}
}