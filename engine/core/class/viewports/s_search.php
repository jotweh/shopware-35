<?php
class sViewportSearch{
	var $sSYSTEM;
	
	
	function sRender()
	{
		
		$this->sSYSTEM->_GET['filterMainCategory'] = intval($this->sSYSTEM->_GET['filterMainCategory']);
		if ($this->sSYSTEM->_GET['sSearchMode']=="supplier"){
			$searchResults = $this->sSYSTEM->sMODULES['sArticles']->sGetArticlesBySupplier();
			$this->sSYSTEM->_GET['sSearch'] = urldecode($this->sSYSTEM->_GET['sSearchText']);
		}
		else if ($this->sSYSTEM->_GET['sSearchMode']=="newest"){
			$variables = $this->sSYSTEM->sMODULES['sArticles']->sGetArticlesByName("a.datum DESC",$this->sSYSTEM->_GET['filterMainCategory'],"newest");
			//Links für "Artikel pro Seite" fixen
			foreach ($variables["sPerPage"] as $perPageKey => &$perPage){
				$perPage["link"] = str_replace("sPage=".$this->sSYSTEM->_GET['sPage'],"sPage=1",$perPage["link"]);
			}
			
			$searchResults = $variables["sArticles"];
			$this->sSYSTEM->_GET['sSearch'] = str_replace("_"," ",$this->sSYSTEM->_GET['sSearchText']);
		}
		else if ($this->sSYSTEM->_GET['sSearchMode']=="topseller"){
			$variables = $this->sSYSTEM->sMODULES['sArticles']->sGetArticlesByName("sales DESC",$this->sSYSTEM->_GET['filterMainCategory'],"topseller");
			//Links für "Artikel pro Seite" fixen
			foreach ($variables["sPerPage"] as $perPageKey => &$perPage){
				$perPage["link"] = str_replace("sPage=".$this->sSYSTEM->_GET['sPage'],"sPage=1",$perPage["link"]);
			}
			
			$searchResults = $variables["sArticles"];
			$this->sSYSTEM->_GET['sSearch'] = str_replace("_"," ",$this->sSYSTEM->_GET['sSearchText']);
		}
		else {
			//Aufruf der Funktion in inherit/myArticles.php
			$variables = $this->sSYSTEM->sMODULES['sArticles']->sGetArticlesByName();
			//Links für "Artikel pro Seite" fixen
			foreach ($variables["sPerPage"] as $perPageKey => &$perPage){
				$perPage["link"] = str_replace("sPage=".$this->sSYSTEM->_GET['sPage'],"sPage=1",$perPage["link"]);
			}
			$searchResults = $variables["sArticles"];
		}
	
		
		foreach ($searchResults as $searchResult){
			if (is_array($searchResult)) $searchResult = $searchResult["id"];
			$article = $this->sSYSTEM->sMODULES['sArticles']->sGetPromotionById ('fix',0,$searchResult);
			
			if (!empty($article["articleID"])){
				$articles[] = $article;
			}
		}
		
		
		
		
		$variables["sSearchResults"] = $articles;
		$variables["sSearchResultsNum"]= empty($variables["sNumberArticles"]) ? count($articles) : $variables["sNumberArticles"];
		$variables["sCharts"] = $articleCharts;
		$variables["sBreadcrumb"] = array(0=>array("name"=>$this->sSYSTEM->sCONFIG['sViewports'][$this->sSYSTEM->_GET["sViewport"]]["name"]));
	
		$templates = array(
		"sContainer"=>"/search/search_middle_supplier.tpl",
		"sContainerRight"=>"/search/search_right.tpl"
		);

		return array("templates"=>$templates,"variables"=>$variables);
	}
}
?>