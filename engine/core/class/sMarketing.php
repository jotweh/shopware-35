<?php
/**
 * Marketing functions like banners, campaigns, promotions
 * @link http://www.shopware.de
 * @package core
 * @subpackage class
 * @copyright (C) Shopware AG 2002-2010
 * @version Shopware 3.5.0
 */
class	sMarketing
{
	/**
	* Pointer to Shopware-Core-public functions
	*
	* @var    object
	* @access private
	*/
	var $sSYSTEM;
	
	/**
	  * Get banners to display in this category
	  * @param  int 	categoryID of the current category
	  * @return array 	Contains all information about the banner-object
	  * @access public
	*/
	public function sBanner($sCategory, $limit=1)
	{
		$limit = (int) $limit;
		$sql = "
			SELECT *
			FROM s_emarketing_banners
			WHERE categoryID=?
			AND (valid_from <= NOW() OR valid_from='0000-00-00 00:00:00')
			AND (valid_to >= NOW() OR valid_from='0000-00-00 00:00:00')
			ORDER BY RAND() LIMIT $limit 
		";
		$getBanners = $this->sSYSTEM->sDB_CONNECTION->GetAll($sql, array($sCategory));
		
		if(empty($getBanners[0])) {
			return false;
		}
		
		foreach ($getBanners as &$getAffectedBanners) {
			if(!empty($getAffectedBanners['liveshoppingID'])) {
				$tmpLive = array('liveshoppingID' => $getAffectedBanners['liveshoppingID']);
				$tmpLive = $this->sSYSTEM->sMODULES['sArticles']->sGetLiveShopping('fix', 0, $tmpLive, true);
				if(!empty($tmpLive)){
					$getAffectedBanners['liveshoppingData'] = $tmpLive['liveshoppingData'][0];
				}
			}
			
			if (!empty($getAffectedBanners["img"])){
				$getAffectedBanners["img"] = $this->sSYSTEM->sPathBanner.$getAffectedBanners["img"];
			}
						
			if (!preg_match("/http/",$getAffectedBanners["link"]) && !empty($getAffectedBanners["link"])) {
				$getAffectedBanners["link"] = "http://".$getAffectedBanners["link"];
			}
		}
		if ($limit == 1 ) {
			$getBanners = $getBanners[0];
		}
		return $getBanners;
	}
	
	public function sGetPremiums()
	{		
		
		$sql = "
		SELECT id, esdarticle FROM s_order_basket WHERE sessionID='".$this->sSYSTEM->sSESSION_ID."'
		AND modus=0
		ORDER BY esdarticle DESC
		";
		
		$checkForEsdOnly = $this->sSYSTEM->sDB_CONNECTION->GetAll($sql);
		
		foreach ($checkForEsdOnly as $esdCheck){
			if ($esdCheck["esdarticle"]){
				$esdOnly = true;
			}else {
				$esdOnly = false;
			}
		}
		if (!empty($esdOnly)) return array();
		
		$sBasketAmount =  $this->sSYSTEM->sMODULES['sBasket']->sGetAmount();
		if(empty($sBasketAmount["totalAmount"]))
			$sBasketAmount = 0;
		else 
			$sBasketAmount = $sBasketAmount["totalAmount"];
		$sql = "
			SELECT
				p.ordernumber as premium_ordernumber, startprice,subshopID, a.id as articleID,
				IF((p.startprice*".$this->sSYSTEM->sCurrency["factor"].")<=$sBasketAmount,1,0) as available
			FROM 
				s_addon_premiums p,
				s_articles a,
				s_articles_details d2
			WHERE p.articleID=d2.ordernumber
			AND d2.articleID=a.id
			AND (p.subshopID = ? OR p.subshopID = 0)
			ORDER BY p.startprice ASC
		";
		
		$premiums = $this->sSYSTEM->sDB_CONNECTION->GetAll($sql,array($this->sSYSTEM->sSubShop["id"]));
		
		foreach ($premiums as &$premium){
			$premium["startprice"] *= $this->sSYSTEM->sCurrency["factor"];
			
			if (empty($premium["available"])) $premium["sDifference"] = $this->sSYSTEM->sMODULES['sArticles']->sFormatPrice($premium["startprice"] - $sBasketAmount);
			$premium["sArticle"] = $this->sSYSTEM->sMODULES['sArticles']->sGetPromotionById ("fix",0,$premium["articleID"]);
			$premium["startprice"] = $this->sSYSTEM->sMODULES['sArticles']->sFormatPrice($premium["startprice"]);
			$sql = "SELECT ordernumber, additionaltext FROM s_articles_details WHERE articleID={$premium["articleID"]}";
			$premium["sVariants"] = $this->sSYSTEM->sDB_CONNECTION->GetAll($sql);
		}
		return $premiums;
	}
	
	public function sBuildTagCloud($categoryID = null)
	{
		if(empty($categoryID))
			$categoryID = empty($this->sSYSTEM->_GET["sCategory"]) ?  $this->sSYSTEM->sLanguageData[$this->sSYSTEM->sLanguage]["parentID"] : (int) $this->sSYSTEM->_GET["sCategory"];
		
		if(!empty($this->sSYSTEM->sCONFIG['sTAGCLOUDMAX']))
			$tagSize = (int) $this->sSYSTEM->sCONFIG['sTAGCLOUDMAX'];
		else 
			$tagSize = 50;
		if(!empty($this->sSYSTEM->sCONFIG['sTAGTIME']))
			$tagTime = (int) $this->sSYSTEM->sCONFIG['sTAGTIME'];
		else 
			$tagTime = 3;
		
		if(!empty($this->sSYSTEM->sCONFIG['sTAGRELEVANCEARTVIEWS']))
			$relevancViews = (int) $this->sSYSTEM->sCONFIG['sTAGRELEVANCEARTVIEWS'];
		else 
			$relevancViews = 3;
		if(!empty($this->sSYSTEM->sCONFIG['sTAGRELEVANCEARTSELLS']))
			$relevancSells = (int) $this->sSYSTEM->sCONFIG['sTAGRELEVANCEARTSELLS'];
		else 
			$relevancSells = 3;
		if(!empty($this->sSYSTEM->sCONFIG['sTAGRELEVANCEARTBASKET']))
			$relevancBaskt = (int) $this->sSYSTEM->sCONFIG['sTAGRELEVANCEARTBASKET'];
		else 
			$relevancBaskt = 3;
		
		$tagSplit = $this->sSYSTEM->sCONFIG['sTAGCLOUDSPLIT'];
		$tagWords = $this->sSYSTEM->sCONFIG['sTAGCLOUDMAX'];
		
		/*
		$tagCountCat = intval($this->sSYSTEM->sCONFIG['sTAGCOUNTCAT']);
		$tagCountArticles = intval($this->sSYSTEM->sCONFIG['sTAGCOUNTARTICLES']);
		$tagCountSerach = intval($this->sSYSTEM->sCONFIG['sTAGCOUNTSEARCH']);
		$tagCountSupplier = intval($this->sSYSTEM->sCONFIG['sTAGCOUNTSUPPLIER']);
		*/
		
		$categoryID = intval($categoryID);
		$sql = "
			SELECT a.id as articleID, a.name, COUNT(r.articleID) AS `relevance`
			FROM s_articles_categories c, s_articles a
		
			LEFT JOIN s_emarketing_lastarticles r
			ON a.id = r.articleID
			AND r.time >= DATE_SUB(NOW(),INTERVAL $tagTime DAY)
			
			WHERE c.categoryID = $categoryID
			AND c.articleID=a.id
			AND a.active = 1
			GROUP BY a.id
			ORDER BY COUNT(r.articleID) DESC
			LIMIT $tagSize
		";
		$articles = $this->sSYSTEM->sDB_CONNECTION->CacheGetAssoc($this->sSYSTEM->sCONFIG['sCACHEARTICLE'],$sql);
		if(empty($articles))
			return false;
		$pos = 1;
		$anz = count($articles);
		
		if(!empty($this->sSYSTEM->sLanguage)&&empty($this->sSYSTEM->sLanguageData[$this->sSYSTEM->sLanguage]["skipbackend"]))
		{
			
			$language = $this->sSYSTEM->sDB_CONNECTION->qstr($this->sSYSTEM->sLanguageData[$this->sSYSTEM->sLanguage]["isocode"]);
			
			$sql_add_from = "
				LEFT JOIN s_core_translations t
				ON t.objectkey = a.id
				AND	t.objecttype = 'article'
				AND	t.objectlanguage = $language
			";
			
			$sql_add_select = ", t.objectdata as translations";
			
			foreach ($articles as $key => $value){
				$ids[] = $key;
			}
			
			if (!empty($ids)){
				$sql = "
				SELECT objectdata, objectkey FROM s_core_translations
				WHERE objecttype = 'article' AND objectlanguage = $language
				AND objectkey IN (".implode(",",$ids).")
				";
				$getTranslations = $this->sSYSTEM->sDB_CONNECTION->CacheGetAll($this->sSYSTEM->sCONFIG['sCACHEARTICLE'],$sql);
				foreach ($getTranslations as $value){
					$articles[$value["objectkey"]]["translations"] = $value["objectdata"];
				}
			}
		}
		
		
		
		if(!empty($this->sSYSTEM->sCONFIG['sTAGCLOUDSPLIT']))
			$steps = (int) $this->sSYSTEM->sCONFIG['sTAGCLOUDSPLIT'];
		else 
			$steps = 3;
		if(!empty($this->sSYSTEM->sCONFIG['sTAGCLOUDCLASS']))
			$class = (string) $this->sSYSTEM->sCONFIG['sTAGCLOUDCLASS'];
		else 
			$class = "tag";
		$link = $this->sSYSTEM->sCONFIG['sBASEFILE']."?sViewport=detail&sArticle=";
		
		foreach ($articles as $articleID=>$article)
		{
			if(!empty($article["translations"])&&$article["translations"] = unserialize($article["translations"]))
			{
				$article["name"] = $article["translations"]["txtArtikel"];
			}
			$name = (strip_tags(html_entity_decode($article["name"])));

			$name = preg_replace("/[^a-zA-Z0-9äöüßÄÖÜ\-´`.]/", " ", $name);
			$name = preg_replace('/\s\s+/', ' ', $name);
			$name = preg_replace('/\(.*\)/', '', $name);
			$name = trim($name," -");
		
			$articles[$articleID]["name"] = $name;
			$articles[$articleID]["class"] = $class.round($pos/$anz*$steps);
			$articles[$articleID]["link"] = $link.$articleID;
			$pos++;
		}
		shuffle($articles);
		return $articles;
	}
	
	public function sGetSimilarArticles($articleID = null, $limit = null)
	{
		$limit = empty($limit) ? 6 : (int) $limit;
		$articleID = empty($articleID) ? (int) $this->sSYSTEM->_GET['sArticle'] : (int) $articleID;
		
		if(empty($categoryID)&&!empty($this->sSYSTEM->_GET['sCategory']))
		{
			$categoryID = (int) $this->sSYSTEM->_GET['sCategory'];
		}
		if(empty($categoryID)&&!empty($this->sSYSTEM->sLanguageData[$this->sSYSTEM->sLanguage]["parentID"]))
		{
			$categoryID = (int) $this->sSYSTEM->sLanguageData[$this->sSYSTEM->sLanguage]["parentID"];
		}
		
		$sql = "
			(
				SELECT d.articleID
				FROM s_articles_similar s, s_articles_details d
				WHERE d.ordernumber=s.relatedarticle
				AND	d.active = 1
				AND s.articleID=$articleID
				LIMIT $limit
			)
			UNION
			(
				SELECT a.id
				FROM
					s_articles_categories ac,
					s_articles_categories so,
					s_articles a,
					s_articles_details d 
				WHERE so.categoryID=so.categoryparentID
				AND so.articleID=$articleID
				AND ac.categoryID=so.categoryID
				AND ac.articleID!=so.articleID
				AND a.id=ac.articleID
				AND a.id=d.articleID
				AND d.kind=1 
				AND a.active=1
				ORDER BY d.sales DESC
				LIMIT $limit
			)
			LIMIT $limit
		";
		$similararticleIDs = $this->sSYSTEM->sDB_CONNECTION->CacheGetCol($this->sSYSTEM->sCONFIG['sCACHEARTICLE'],$sql);
		
		if(empty($similararticleIDs)&&!empty($categoryID))
		{
			$sql = "
				SELECT a.id
				FROM
					s_articles_categories ac,
					s_articles a,
					s_articles_details d 
				WHERE ac.categoryID=$categoryID
				AND a.id=ac.articleID
				AND a.id=d.articleID
				AND d.kind=1 
				AND a.active=1
				AND a.id!=$articleID
				ORDER BY d.sales DESC
				LIMIT $limit
			";
			$similararticleIDs = $this->sSYSTEM->sDB_CONNECTION->CacheGetCol($this->sSYSTEM->sCONFIG['sCACHEARTICLE'],$sql);
		}
		$similararticles = array();
		if(!empty($similararticleIDs))
		foreach ($similararticleIDs as $similararticleID)
		{
			$article = $this->sSYSTEM->sMODULES['sArticles']->sGetPromotionById("fix",0,(int)$similararticleID);
			if (!empty($article))
			{
				$similararticles[] = $article;
			}
		}
		return $similararticles;
	}
	
	public function sCampaignsGetList ($id,$group=false){
		
		
		
		$sToday = date("Y-m-d");
		
		if ($group){
			$sqlGroup = "AND positionGroup='$group'";	
		}else {
			$sqlGroup = "";
		}
		
		$id = intval($id);
		
		$licenceAdd = "";
		
		
		$getCampaigns = $this->sSYSTEM->sDB_CONNECTION->CacheGetAll(3600,"
		SELECT id, image, description, link, linktarget FROM s_emarketing_promotion_main
		WHERE parentID=$id AND active=1
		$sqlGroup
		AND ((TO_DAYS(start) <= TO_DAYS('$sToday') AND
		TO_DAYS(end) >= TO_DAYS('$sToday')) OR
		(start='0000-00-00' AND end='0000-00-00')) 
		ORDER BY position
		$licenceAdd
		");
		
		foreach ($getCampaigns as $campaignKey => $campaignValue){
			if ($getCampaigns[$campaignKey]["image"]){
				$getCampaigns[$campaignKey]["image"] = $this->sSYSTEM->sPathBanner.$getCampaigns[$campaignKey]["image"];
			}
		
			if (!preg_match("/http/",$getCampaigns[$campaignKey]["link"]) && $getCampaigns[$campaignKey]["link"]){
				$getCampaigns[$campaignKey]["link"] = "http://".$getCampaigns[$campaignKey]["link"];
				
			}elseif (!$getCampaigns[$campaignKey]["link"]) {
				// Building link to detail-page
				$getCampaigns[$campaignKey]["link"] = $this->sSYSTEM->sCONFIG['sBASEFILE']."?sViewport=campaign&sCampaign=".$campaignValue["id"];
			}
		}
		
		return $getCampaigns;
	}

	public function sCampaignsGetDetail($id){
		
		$id = intval($id);
		
		$sql = "
		SELECT id, image, description, link, linktarget FROM s_emarketing_promotion_main
		WHERE id=$id
		AND ((TO_DAYS(start) <= TO_DAYS(now()) AND
		TO_DAYS(end) >= TO_DAYS(now())) OR
		(start='0000-00-00' AND end='0000-00-00')) 
		ORDER BY position
		";
	
		$getCampaigns = $this->sSYSTEM->sDB_CONNECTION->CacheGetRow($sql);
		
		if (!$getCampaigns["id"]){
			return false;
		}else {
			// Fetch all positions
			$sql = "
			SELECT id, type, description FROM s_emarketing_promotion_containers
			WHERE promotionID=$id
			ORDER BY position
			";
			
			$getCampaignContainers = $this->sSYSTEM->sDB_CONNECTION->CacheGetAll($sql);
		
			foreach ($getCampaignContainers as $campaignKey => $campaignValue){
				switch ($campaignValue["type"]){
					case "ctBanner":
						// Query Banner
						$getBanner = $this->sSYSTEM->sDB_CONNECTION->CacheGetRow(3600,"
						SELECT image, link, linkTarget, description FROM s_emarketing_promotion_banner
						WHERE parentID={$campaignValue["id"]}
						");
						// Rewrite banner
						if ($getBanner["image"]){
							$getBanner["image"] = $this->sSYSTEM->sPathBanner.$getBanner["image"];
						}
					
						if (!preg_match("/http/",$getBanner["link"]) && $getBanner["link"]){
							$getBanner["link"] = "http://".$getBanner["link"];
						}
			
						$getCampaignContainers[$campaignKey]["description"] = $getBanner["description"];
						$getCampaignContainers[$campaignKey]["data"] = $getBanner;
						break;
					case "ctLinks":
						// Query links
						$getLinks = $this->sSYSTEM->sDB_CONNECTION->CacheGetAll(3600,"
						SELECT description, link, target FROM s_emarketing_promotion_links
						WHERE parentID={$campaignValue["id"]}
						ORDER BY position
						");
						$getCampaignContainers[$campaignKey]["data"] = $getLinks;
						break;
					case "ctArticles":
						$getArticles = $this->sSYSTEM->sDB_CONNECTION->CacheGetAll(3600,"
						SELECT * FROM s_emarketing_promotion_articles
						WHERE parentID={$campaignValue["id"]}
						ORDER BY position
						");
						unset($articleData);
						foreach ($getArticles as $article){
							
							
							if ($article["type"]){
								$category = $this->sSYSTEM->_GET["sCategory"] ? $this->sSYSTEM->_GET["sCategory"] : $this->sSYSTEM->sLanguageData[$this->sSYSTEM->sLanguage]["parentID"];
								if ($article["type"]=="image"){
									$tmpContainer = $this->sSYSTEM->sMODULES['sArticles']->sGetPromotionById($article["type"],$category,$article);
								}else {
									$articleID = (int) $this->sSYSTEM->sMODULES['sArticles']->sGetArticleIdByOrderNumber($article['articleordernumber']);
									$tmpContainer = $this->sSYSTEM->sMODULES['sArticles']->sGetPromotionById($article["type"],$category,$articleID);
								}

								if (count($tmpContainer) && isset($tmpContainer["articleName"])){
									$articleData[] = $tmpContainer;
								}elseif ($article["type"]=="image"){
									$articleData[] = $tmpContainer;
								}
							}
							
						}
						
						$getCampaignContainers[$campaignKey]["data"] = $articleData;
						break;
					case "ctText":
						$getText = $this->sSYSTEM->sDB_CONNECTION->CacheGetRow(3600,"
						SELECT headline, html FROM s_emarketing_promotion_html
						WHERE parentID={$campaignValue["id"]}
						");
						$getCampaignContainers[$campaignKey]["description"] = $getText["headline"];
						$getCampaignContainers[$campaignKey]["data"] = $getText;
						break;
			
				}
			}
			
			//print_r($getCampaignContainers);
			$getCampaigns["containers"] = $getCampaignContainers;
			return $getCampaigns;
		}
	}
	
	public function sMailCampaignsGetDetail($id){
		$sql = "
		SELECT * FROM s_campaigns_mailings
		WHERE id=$id
		";
		$getCampaigns = $this->sSYSTEM->sDB_CONNECTION->GetRow($sql);
		
		if (!$getCampaigns["id"]){
			return false;
		}else {
			// Fetch all positions
			$sql = "
			SELECT id, type, description, value FROM s_campaigns_containers
			WHERE promotionID=$id
			ORDER BY position
			";
			
			$getCampaignContainers = $this->sSYSTEM->sDB_CONNECTION->GetAll($sql);
		
			foreach ($getCampaignContainers as $campaignKey => $campaignValue){
				switch ($campaignValue["type"]){
					case "ctBanner":
						// Query Banner
						$getBanner = $this->sSYSTEM->sDB_CONNECTION->GetRow("
						SELECT image, link, linkTarget, description FROM s_campaigns_banner
						WHERE parentID={$campaignValue["id"]}
						");
						// Rewrite banner
						if ($getBanner["image"]){
							$getBanner["image"] = $this->sSYSTEM->sPathBanner.$getBanner["image"];
						}
					
						if (!preg_match("/http/",$getBanner["link"]) && $getBanner["link"]){
							$getBanner["link"] = "http://".$getBanner["link"];
						}
			
						$getCampaignContainers[$campaignKey]["description"] = $getBanner["description"];
						$getCampaignContainers[$campaignKey]["data"] = $getBanner;
						break;
					case "ctLinks":
						// Query links
						$getLinks = $this->sSYSTEM->sDB_CONNECTION->GetAll("
						SELECT description, link, target FROM s_campaigns_links
						WHERE parentID={$campaignValue["id"]}
						ORDER BY position
						");
						$getCampaignContainers[$campaignKey]["data"] = $getLinks;
						break;
					case "ctArticles":
						$sql = "
						SELECT articleordernumber, type FROM s_campaigns_articles
						WHERE parentID={$campaignValue["id"]}
						ORDER BY position
						";
					
						$getArticles = $this->sSYSTEM->sDB_CONNECTION->GetAll($sql);
							unset($articleData);
						foreach ($getArticles as $article){
							if ($article["type"]){
								$category = $this->sSYSTEM->_GET["sCategory"] ? $this->sSYSTEM->_GET["sCategory"] : 0;
								$tmpContainer = $this->sSYSTEM->sMODULES['sArticles']->sGetPromotionById($article["type"],$category,$article['articleordernumber']);
								
								if (count($tmpContainer) && isset($tmpContainer["articleName"])){
									$articleData[] = $tmpContainer;
								}
							}

						}
						
						$getCampaignContainers[$campaignKey]["data"] = $articleData;
						break;
					case "ctText":
					case "ctVoucher":
						$getText = $this->sSYSTEM->sDB_CONNECTION->GetRow("
							SELECT headline, html,image,alignment,link FROM s_campaigns_html
							WHERE parentID={$campaignValue["id"]}
						");
						if ($getText["image"]){
							$getText["image"] = $this->sSYSTEM->sPathBanner.$getText["image"];
						}
						if (!preg_match("/http/",$getText["link"]) && $getText["link"]){
							$getText["link"] = "http://".$getText["link"];
						}
						$getCampaignContainers[$campaignKey]["description"] = $getText["headline"];
						$getCampaignContainers[$campaignKey]["data"] = $getText;
						break;
				}
			}
			$getCampaigns["containers"] = $getCampaignContainers;
			return $getCampaigns;
		}
	}
	
	public function sCampaignsGetSuggestions($id,$userid=0){
		return $this->sSYSTEM->sMODULES['sNewsletter']->sCampaignsGetSuggestions($id,$userid);
	}
}
?>