<?php
/**
 * Suggest search controller
 *
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Stefan Hamann
 * @author Heiner Lohaus
 * @package Shopware
 * @subpackage Controllers
 */
class Shopware_Controllers_Frontend_AjaxSearch extends Enlight_Controller_Action
{
	/**
	 * Index action - get searchterm from request (sSearch) and start search
	 * @return
	 */
	public function indexAction()
	{		
		Enlight()->Plugins()->Controller()->Json()->setPadding();
		
		$this->View()->loadTemplate('frontend/search/ajax.tpl');
		
		$search = $this->Request()->getParam('sSearch');
		$search = trim(stripslashes(html_entity_decode(utf8_decode($search))));
		if (!$search||strlen($search)<Shopware()->Config()->MinSearchLenght) {
			return;
		}
				
		$search_request = array(
			'sSuggestSearch'=>true,
			'sSearch'=>$search,
			'sPerPage'=>empty(Shopware()->Config()->MaxLiveSearchResults) ? 6 : (int) Shopware()->Config()->MaxLiveSearchResults
		);
		$search_results = $this->doSearch($search_request);
		
		$this->view->sSearchRequest = $search_request;
		$this->view->sSearchResults = $search_results;
	}


	/**
	 * Deprecated used in old template-base
	 * @return
	 */
	public function jsonSearchAction()
	{
		$this->View()->setTemplate();
		
		$search = $this->Request()->getParam('sSearch');
		if(!$search) return;
		
		$search_request = array(
			'sSuggestSearch'=>true,
			'sSearch'=>trim(stripslashes(html_entity_decode(utf8_decode($this->Request()->getParam('sSearch'))))),
			'sPerPage'=>empty(Shopware()->Config()->MaxLiveSearchResults) ? 6 : (int) Shopware()->Config()->MaxLiveSearchResults
		);
		$search_results = $this->doSearch($search_request);
				
		if(!empty($search_results['sResults']))
		foreach ($search_results['sResults'] as &$result)
		{
			$result['name'] = trim(htmlentities(strip_tags(html_entity_decode($result['name']))));
			if(!empty($result['description']))
			{
				$result['description'] = trim(strip_tags(html_entity_decode($result['description'])));
				$result['description'] = preg_replace('/\s+/', ' ', $result['description']);
				$result['description'] = htmlentities($result['description']);
			}
		}
		echo json_encode(array('sResults'=>$search_results['sResults']));
	}


	/**
	 * Generate open-search conform data
	 * @return
	 */
	public function openSearchAction()
	{
		$this->View()->setTemplate();
		
		$search = $this->Request()->getParam('sSearch');
		if(!$search) return;
		
		$search_request = array(
			'sSuggestSearch'=>true,
			'sSearch'=>trim(stripslashes(html_entity_decode(utf8_decode($search)))),
			'sPerPage'=>empty(Shopware()->Config()->MaxLiveSearchResults) ? 6 : (int) Shopware()->Config()->MaxLiveSearchResults
		);
		$search_results = $this->doSearch($search_request);
		
		$sSearchResultsNames = array();
		if(!empty($search_results['sResults']))
		foreach ($search_results['sResults'] as $result)
		{
			if(!empty($result['name']))
			{
				$sSearchResultsNames[] = utf8_encode($result['name']);
				$sSearchResultsLinks[] = $result['link'];
			}
		}
		echo json_encode(array($sRequests['sSearch'], $sSearchResultsNames, $sSearchResultsDesc, $sSearchResultsLinks));
	}

	/**
	 * Start suggest search and return results
	 * @param  $sRequests
 	 * 			$sRequests = array(
	 *				'sSuggestSearch'=>true,
	 *				'sSearch'=>$search,
	 *				'sPerPage
	 * 			)
	 * @return
	 */
	public function doSearch($sRequests)
	{
		
		Shopware()->Modules()->Search()->sInit();
		
		$search_results = Shopware()->Modules()->Search()->sStartSearch($sRequests);
		
		$base_path = $this->Request()->getScheme().'://'.$this->Request()->getHttpHost().$this->Request()->getBasePath();
		
		if(!empty($search_results['sResults']))
		foreach ($search_results['sResults'] as &$result)
		{
			if(empty($result['type'])) $result['type'] =  'article';
			if(!empty($result['image']))
			{
				switch ($result['type']) {
					case 'supplier':
						$result['image'] = $base_path.Shopware()->Config()->sSUPPLIERIMAGES.'/'.$result['image'];
						break;
					case 'article':
						$result['image'] = $base_path.Shopware()->Config()->sARTICLEIMAGES.'/'.$result['image'].'_1.'.$result['extension'];
						break;
					case 'category':		
					default:
						break;
				}
			}
			switch ($result['type']) {
				case 'supplier':
					$result['link'] = $this->Front()->Router()->assemble(array('controller'=>'search', 'action'=>'fuzzy', 'sSearch'=>$result['name'], 'sFilter_supplier'=>$result['id']));
					break;
				case 'article':
					$result['link'] = $this->Front()->Router()->assemble(array('controller'=>'detail', 'sArticle'=>$result['articleID'], 'title'=>$result['name']));
					break;
				case 'category':
					$result['link'] = $this->Front()->Router()->assemble(array('controller'=>'listing', 'sCategory'=>$result['id'], 'title'=>$result['name']));
					break;	
				default:
					break;
			}
		}
		
		return $search_results;
	}
}