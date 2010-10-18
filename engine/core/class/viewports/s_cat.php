<?php
class sViewportCat{
	var $sSYSTEM;
	
	function sRender()
	{
		$this->sSYSTEM->_GET['sCategory'] = intval($this->sSYSTEM->_GET['sCategory']);
		$promoteContent = $this->sSYSTEM->sMODULES['sCategories']->sGetCategoryContent($this->sSYSTEM->_GET['sCategory']);
		$categoryArticles = $this->sSYSTEM->sMODULES['sArticles']->sGetArticlesByCategory();

		if(!empty($promoteContent["external"]))
		{
			$location = $promoteContent["external"];
		}
		elseif (!empty($this->sSYSTEM->sCONFIG["sCATEGORYDETAILLINK"])&&!empty($categoryArticles['sArticles'])&&count($categoryArticles['sArticles'])==1)
		{
			$getArticle = reset($categoryArticles['sArticles']);
			$location = $this->sSYSTEM->sCONFIG['sBASEFILE'].'?sViewport=detail&sArticle='.$getArticle['articleID'];
			$location = $this->sSYSTEM->sMODULES['sCore']->sRewriteLink($location ,$getArticle['articleName']);
		}
		elseif(!$promoteContent)
		{
			$location = $this->sSYSTEM->sCONFIG['sBASEFILE'];
			$location =  $this->sSYSTEM->sMODULES['sCore']->sRewriteLink($location);
		}
		if(isset($location))
		{
			header('Location: '.$location, true, 301);
			exit();
		}
		
		$categoryDepth = $this->sSYSTEM->sMODULES['sCategories']->sGetCategoryDepth($this->sSYSTEM->_GET['sCategory']);
		$categoryBreadcrumb = array_reverse(($this->sSYSTEM->sMODULES['sCategories']->sGetCategoriesByParent($this->sSYSTEM->_GET['sCategory'])));
		
		if ((empty($this->sSYSTEM->_GET["sSupplier"]) || $this->sSYSTEM->_GET["sSupplier"]==-1) 
			&& (empty($this->sSYSTEM->_GET["sFilterProperties"]) || $this->sSYSTEM->_GET["sFilterProperties"]==-1)
			&& (empty($this->sSYSTEM->_GET["sFilterGroup"]) || $this->sSYSTEM->_GET["sFilterGroup"]==-1))
		{
			$promoteArticles = $this->sSYSTEM->sMODULES['sArticles']->sGetPromotions($this->sSYSTEM->_GET['sCategory']);
		}
		
		$promoteBanner = $this->sSYSTEM->sMODULES['sMarketing']->sBanner($this->sSYSTEM->_GET['sCategory']);
		
		$sLiveShopping = $this->sSYSTEM->sMODULES['sArticles']->sGetLiveShopping('random', $this->sSYSTEM->_GET['sCategory'], null, true, 'AND lv.categories_display=1', '', 0);

		if (empty($promoteContent["blog"])){
			if (count($promoteArticles)){
				$categoryListing = false;
				if ($promoteContent["template"]){
					$templates = array("sContainer"=>"/articles/{$promoteContent["template"]}");
				}else {
					$templates = array("sContainer"=>"/articles/article_home.tpl");
				}
				
			}else {
				$categoryListing = true;
				if ($promoteContent["template"]){
					$templates = array("sContainer"=>"/articles/".$promoteContent["template"]);
				}else {
					$templates = array("sContainer"=>"/articles/".$this->sSYSTEM->sCONFIG['sCATEGORY_DEFAULT_TPL']);
				}
			}
		}else {
			$templates = array("sContainer"=>"/blog/listing.tpl");
		}
		$variables = array(
			"sArticles"=>$categoryArticles['sArticles'],
			"sFilterDate"=>$categoryArticles['sFilterDate'],
			"sLiveShopping"=>$sLiveShopping,
			"sPropertiesOptionsOnly"=>$categoryArticles['sPropertiesOptionsOnly'],
			"sPropertiesGrouped"=>$categoryArticles['sPropertiesGrouped'],
			"sPages"=>$categoryArticles['sPages'],
			"sSupplierInfo"=>$categoryArticles['sSupplierInfo'],
			"sCategoryListing"=>$sCategoryListing,
			"sPerPage"=>$categoryArticles['sPerPage'],
			"sBanner"=>$promoteBanner,
			"sOffers"=>$promoteArticles,
			"sBreadcrumb"=>$categoryBreadcrumb,
			"sCategoryInfo"=>$categoryBreadcrumb[count($categoryBreadcrumb)-1],
			"sCategoryContent"=>$promoteContent,
			"sNumberPages"=>$categoryArticles['sNumberPages'],
			"sNumberArticles"=>$categoryArticles['sNumberArticles']
		);
		
		eval($this->sSYSTEM->sCallHookPoint("s_cat.php_sRender_AfterVariables"));
		// In first instance we show article charts, then we offer the possibility to filter by supplier
		if ($categoryDepth<=1){
			$articleCharts = $this->sSYSTEM->sMODULES['sArticles']->sGetArticleCharts();
			$variables["sCharts"] = $articleCharts;
			$templates["sContainerRight"] = "/category/category_right_charts.tpl";
		}else {
			$affectedSuppliers = $this->sSYSTEM->sMODULES['sArticles']->sGetAffectedSuppliers();
			$variables["sSuppliers"] = $affectedSuppliers;
			$templates["sContainerRight"] = "/category/category_right_supplier.tpl";
		}
		if (!empty($promoteContent["blog"])){
			$affectedSuppliers = $this->sSYSTEM->sMODULES['sArticles']->sGetAffectedSuppliers();
			$variables["sSuppliers"] = $affectedSuppliers;
			$templates["sContainerRight"] = "/blog/right.tpl";
		}
		
		// Load campaigns for this category
		$campaignGroups = $this->sSYSTEM->sCONFIG['sCAMPAIGNSPOSITIONS'];
		$campaignGroups = explode(";",$campaignGroups);
		foreach ($campaignGroups as $campaignGroup){
			$groupData = explode(":",$campaignGroup);
			$variables["sCampaigns"][$groupData[1]] = $this->sSYSTEM->sMODULES['sMarketing']->sCampaignsGetList($this->sSYSTEM->_GET['sCategory'],$groupData[1]);
		}
		
		if (!empty($variables["sSuppliers"])){
		    foreach ($variables["sSuppliers"] as &$link){
		     $link["link"] = preg_replace("#[&\?]?sPage=[0-9]+#","",$link["link"]);
		    }
		}
		if (!empty($variables["sPerPage"])){
		    foreach ($variables["sPerPage"] as &$link){
		     $link["link"] = preg_replace("#[&\?]?sPage=[0-9]+#","",$link["link"]);
		    }
		}
		
	    eval($this->sSYSTEM->sCallHookPoint("s_cat.php_sRender_BeforeEnd"));
		return array("templates"=>$templates,"variables"=>$variables);
	}
}
?>