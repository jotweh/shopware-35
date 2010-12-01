<?php
/**
 * Shopware article management
 * @link http://www.shopware.de
 * @package core
 * @subpackage class
 * @copyright (C) Shopware AG 2002-2010
 * @version Shopware 3.5.0
 */
class sArticles
{
   
	/**
     * Pointer to sSystem object
     *
     * @var sSystem
     */
	var $sSYSTEM;
	/**
     * Array of already loaded promotions
     *
     * @var array
     */
	var $sCachePromotions = array();
	var $LiveShoppingCache = array();
	/**
	 * Delete articles from comparision chart
	 * @param int $article Unique article id - refers to s_articles.id
	 * @access public
	 */
	 public function sDeleteComparison ($article){
	 	$article = (int)$article;
	 	if ($article){
	 		$checkForArticle = $this->sSYSTEM->sDB_CONNECTION->Execute("
			DELETE FROM s_order_comparisons WHERE sessionID=? AND articleID=?
			",array($this->sSYSTEM->sSESSION_ID,$article));
	 	}
	 }
	 
	 /**
	 * Delete all articles from comparision chart
	 * @access public
	 */
	 public function sDeleteComparisons (){
	 	$sql = "
		DELETE FROM s_order_comparisons WHERE sessionID=?
		";
 		$checkForArticle = $this->sSYSTEM->sDB_CONNECTION->Execute($sql,array($this->sSYSTEM->sSESSION_ID));
	 }
	 
	 /**
	 * Insert articles in comparision chart
	 * @param int $article s_articles.id
	 * @access public
	 * @return bool true/false
	 */
	 public function sAddComparison ($article){
	 	$article = (int)$article;
	 	if ($article){
			// Check if this article is already noted
			$checkForArticle = $this->sSYSTEM->sDB_CONNECTION->GetRow("
			SELECT id FROM s_order_comparisons WHERE sessionID=? AND articleID=?
			",array($this->sSYSTEM->sSESSION_ID,$article));
			// Check if max. numbers of articles for one comparison-session is reached
			$checkNumberArticles = $this->sSYSTEM->sDB_CONNECTION->GetRow("
			SELECT COUNT(id) AS countArticles FROM s_order_comparisons WHERE sessionID=?
			",array($this->sSYSTEM->sSESSION_ID));
			
			if ($checkNumberArticles["countArticles"]>=$this->sSYSTEM->sCONFIG["sMAXCOMPARISONS"]){
				return "max_reached";
			}
			
			// 
			if (!$checkForArticle["id"]){
				$articleName = $this->sSYSTEM->sDB_CONNECTION->GetOne("
				SELECT s_articles.name AS articleName FROM s_articles WHERE 
				id = ?
				",array($article));
				
				if (!$articleName) return false;
				
				$sql = "
				INSERT INTO s_order_comparisons (sessionID, userID, articlename, articleID, datum)
				VALUES (?,?,?,?,now())
				";
				
				
				$queryNewPrice = $this->sSYSTEM->sDB_CONNECTION->Execute($sql,array(
					$this->sSYSTEM->sSESSION_ID,
					empty($this->sSYSTEM->_SESSION["sUserId"]) ? 0 : $this->sSYSTEM->_SESSION["sUserId"],
					$articleName,
					$article
				));
				
				if (!$queryNewPrice){
					$this->sSYSTEM->E_CORE_WARNING ("sArticles##sAddComparison##01","Error in SQL-query");
					return false;
				}
			}
			return true;
	 	}
	 }
	 
	 /**
	 * Get all articles from comparision chart
	 * @access public
	 * @return array Associative array with all articles or empty array
	 */
	 public function sGetComparisons (){
	 	
	 	if (!$this->sSYSTEM->sSESSION_ID) return array();
			
	 		// Get all comparisons for this user
			$checkForArticle = $this->sSYSTEM->sDB_CONNECTION->GetAll("
			SELECT * FROM s_order_comparisons WHERE sessionID=?
			",array($this->sSYSTEM->sSESSION_ID));
						
	 		if (count($checkForArticle)){
	 			foreach ($checkForArticle as $k => $article){
	 				$checkForArticle[$k]["articlename"] = stripslashes($article["articlename"]);
	 				$checkForArticle[$k] = $this->sGetTranslation($article,$article["articleID"],"article",$this->sSYSTEM->sLanguage);
	 				if (!empty($checkForArticle[$k]["articleName"])) $checkForArticle[$k]["articlename"] = $checkForArticle[$k]["articleName"];
	 			}
	 			
	 			
	 			return $checkForArticle;
	 		}else {
	 			return array();
	 		}
	 }
	 
	  /**
	 * Get all articles and a table of their properties as an array
	 * @access public
	 * @return array Associative array with all articles or empty array
	 */
	 public function sGetComparisonList (){
	 
	 	if (!$this->sSYSTEM->sSESSION_ID) return array();
			
	 		// Get all comparisons for this user
			$checkForArticle = $this->sSYSTEM->sDB_CONNECTION->GetAll("
			SELECT * FROM s_order_comparisons WHERE sessionID=?
			",array($this->sSYSTEM->sSESSION_ID));
						
	 		if (count($checkForArticle)){
	 			foreach ($checkForArticle as $article){
	 				if ($article["articleID"]){
	 					$data = $this->sGetPromotionById("fix",0,(int)$article["articleID"]);
	 					$articles[] = $data;
	 				}
	 			}
	 			foreach ($articles as $key => $article){
	 				// Building global property-list
	 				if(!empty($article["sProperties"]))
	 				foreach ($article["sProperties"] as $property){
	 					$properties[$property["id"]] = $property["name"];
	 					$articles[$key]["sPropertiesData"][$property["id"]] = $property["value"];
	 				}
	 			}
	 			
	 	
	 			return array("articles"=>$articles,"properties"=>$properties);
	 		}else {
	 			return array();
	 		}
	 }
	 
	 /**
	 * Get all properties from one article, filtered by one filter group
	 * @param int $article - s_articles.id
	 * @param int $filtergroupID id of the property group (s_filter_groups)
	 * @access public
	 * @return array
	 */
	 public function sGetArticleProperties ($article,$filtergroupID){
	 
		
		$article = intval($article);
		$filtergroupID = intval($filtergroupID);
		
		eval($this->sSYSTEM->sCallHookPoint("sArticles.php_sGetArticleProperties_Start"));
	 	$language = $this->sSYSTEM->sLanguageData[$this->sSYSTEM->sLanguage]["isocode"];
	 	
	 	// Read all assigned properties
		$sql = "
		SELECT fo.name, fo.id, value, st.objectdata AS nameTranslation
		FROM
		s_filter_relations AS fr,
		s_filter_options AS fo
		LEFT JOIN s_filter_values AS fv ON fv.groupID = $filtergroupID AND fv.optionID = fo.id AND fv.articleID = $article
		LEFT JOIN s_core_translations AS st ON st.objecttype='propertyoption' AND st.objectkey=fv.optionID AND st.objectlanguage='$language'
		WHERE 
			fr.groupID = $filtergroupID
		AND 
			fr.optionID = fo.id
		ORDER BY 
			fr.position ASC
		";
		eval($this->sSYSTEM->sCallHookPoint("sArticles.php_sGetArticleProperties_AfterSQL"));
		//die($sql);
		$getProperties = $this->sSYSTEM->sDB_CONNECTION->CacheGetAll($this->sSYSTEM->sCONFIG['sCACHEARTICLE'],$sql);
		
		if (!empty($language) && $language!="de"){
			// Get Value translations
			$sql = "
			SELECT objectdata FROM s_core_translations AS st2 WHERE st2.objecttype='properties' AND st2.objectkey=$article AND st2.objectlanguage='$language'
			";
			
			$getValueTranslations = $this->sSYSTEM->sDB_CONNECTION->CacheGetRow($this->sSYSTEM->sCONFIG['sCACHEARTICLE'],$sql);
			if (!empty($getValueTranslations["objectdata"])){
				$transValues = unserialize($getValueTranslations["objectdata"]);
				
			}
			
			foreach ($getProperties as $propertyKey => $propertyValue){
				if (!empty($propertyValue["nameTranslation"])){
					$vTrans = unserialize($propertyValue["nameTranslation"]);
					if (!empty($vTrans["optionName"])) $getProperties[$propertyKey]["name"] = $vTrans["optionName"];
				}
				if (!empty($transValues)){
					if (!empty($transValues[$propertyValue["id"]])){
						$getProperties[$propertyKey]["value"] = $transValues[$propertyValue["id"]];
					}
				}
			}
		}
		
		
		//$this->sGetTranslation($articles[$articleKey],$articles[$articleKey]["articleID"],"article",$this->sSYSTEM->sLanguage);
		eval($this->sSYSTEM->sCallHookPoint("sArticles.php_sGetArticleProperties_BeforeEnd"));
		return $getProperties;
		
	 }
	 /**
	 * Get the average rating from one article
	 * @param int $article - s_articles.id
	 * @access public
	 * @return array
	 */
	public function sGetArticlesAverangeVote ($article,$realtime=false){
		
		$sql = "
		SELECT AVG(points) AS averange, COUNT(articleID) as number FROM s_articles_vote WHERE articleID=?
		AND active=1
		GROUP BY articleID
		";
		eval($this->sSYSTEM->sCallHookPoint("sArticles.php_sGetArticlesAverangeVote_AfterSQL"));
		$getArticles = $this->sSYSTEM->sDB_CONNECTION->GetRow($sql,array($article),"article_$article");
		
		if (empty($getArticles["averange"])) $getArticles["averange"] = "0.00";
		if (empty($getArticles["number"])) $getArticles["number"] = "0";
		
		eval($this->sSYSTEM->sCallHookPoint("sArticles.php_sGetArticlesAverangeVote_BeforeEnd"));
		return array("averange"=>$getArticles["averange"],"count"=>$getArticles["number"]);
	}
	
	 /**
	 * Save a new article comment / voting
	 * Reads several values directly from _POST
	 * @param int $article - s_articles.id
	 * @access public
	 * @return null
	 */
	public function sSaveComment ($article){
		// Permit Injects
		
		$this->sSYSTEM->_POST["sVoteName"] = strip_tags($this->sSYSTEM->_POST["sVoteName"]);
		$this->sSYSTEM->_POST["sVoteSummary"] = strip_tags($this->sSYSTEM->_POST["sVoteSummary"]);
		$this->sSYSTEM->_POST["sVoteComment"] = strip_tags($this->sSYSTEM->_POST["sVoteComment"]);	
		$this->sSYSTEM->_POST["sVoteStars"] = doubleval($this->sSYSTEM->_POST["sVoteStars"]);
		
		if ($this->sSYSTEM->_POST["sVoteStars"] < 1 || $this->sSYSTEM->_POST["sVoteStars"] > 10) $this->sSYSTEM->_POST["sVoteStars"] = 0;
		eval($this->sSYSTEM->sCallHookPoint("sArticles.php_sSaveComment_AfterAssign"));
		$this->sSYSTEM->_POST["sVoteStars"] /= 2;
		
		$datum = date("Y-m-d H:i:s");
		
		if ($this->sSYSTEM->sCONFIG['sVOTEUNLOCK']){
			$active = 0;
		}else {
			$active = 1;
		}
		eval($this->sSYSTEM->sCallHookPoint("sArticles.php_sSaveComment_BeforeSQL"));
		 $sBADWORDS = array(
	        "sex",
	        "porn",
	        "viagra",
	        "url\=",
	        "src\=",
	        "link\=",
	    );
	    foreach ($sBADWORDS as $sBADWORD){
		    if (preg_match("/$sBADWORD/",strtolower($this->sSYSTEM->_POST["sVoteComment"]))){
		    	return false;
		    }
	    }

		$data = array(
		$article,
		$this->sSYSTEM->_POST["sVoteName"],
		$this->sSYSTEM->_POST["sVoteSummary"],
		$this->sSYSTEM->_POST["sVoteComment"],
		$this->sSYSTEM->_POST["sVoteStars"],
		$datum,
		$active
		);
		$sql = "
		INSERT INTO s_articles_vote (articleID, name, headline, comment, points, datum, active)
		VALUES (?,?,?,?,?,?,?)
		";
		
		eval($this->sSYSTEM->sCallHookPoint("sArticles.php_sSaveComment_AfterSQL"));
		$insertComment = $this->sSYSTEM->sDB_CONNECTION->Execute($sql,$data);
		
		if ($insertComment){
			unset($this->sSYSTEM->_POST);
		}else {
			$this->sSYSTEM->E_CORE_WARNING("sSaveComment #00","Could not save comment");
		}
	}
	
	 /**
	 * Read all article comments / votings
	 * @param int $article - s_articles.id
	 * @access public
	 * @return array
	 */
	public function sGetArticlesVotes ($article){
		$getArticles = $this->sSYSTEM->sDB_CONNECTION->GetAll("
		SELECT name, headline, comment, points, datum, active,DATE_FORMAT(datum,'%d.%m.%Y %H:%i') AS date FROM s_articles_vote WHERE articleID=?
		AND active=1
		ORDER BY datum DESC
		",array($article));
		foreach ($getArticles as $articleKey => $articleValue){
			$getArticles[$articleKey]["comment"] = str_replace("\\n","",$getArticles[$articleKey]["comment"]);
			$getArticles[$articleKey]["comment"] = str_replace("\\r","",$getArticles[$articleKey]["comment"]);
						
			$getArticles[$articleKey]["comment"] = stripslashes($getArticles[$articleKey]["comment"]);//nl2br($getArticles[$articleKey]["comment"]);
		}
		return $getArticles;
	}
	
	 /**
	 * Deprecated article chart function
	 * @param int $filterByCategory - s_categories.id 
	 * @access public
	 * @return array
	 */
	public function sGetArticlesTop ($filterByCategory = null)
	{
		if (empty($filterByCategory)){
			$filterByCategory = $this->sSYSTEM->sLanguageData[$this->sSYSTEM->sLanguage]["parentID"];
		} else {
			$filterByCategory = intval($filterByCategory);
		}
		$getArticles = $this->sSYSTEM->sDB_CONNECTION->CacheGetAll($this->sSYSTEM->sCONFIG['sCACHESEARCH'],"
			SELECT s_articles.id AS id
			FROM s_articles, s_articles_details, s_articles_categories 
			WHERE s_articles.active=1 
			AND s_articles.id=s_articles_details.articleID
			AND s_articles_details.kind=1
			AND s_articles.id=s_articles_categories.articleID
			AND s_articles_categories.categoryID=$filterByCategory
			ORDER BY sales DESC LIMIT 12 
		");
		return $getArticles;
	}
	
	 /**
	 * Deprecated: Read newest article, optional filtered by category
	 * @param int $filterByCategory - s_categories.id, category id
	 * @access public
	 * @return array
	 */
	public function sGetArticlesNewest ($filterByCategory = null)
	{
		if (empty($this->sSYSTEM->sCONFIG['sARTICLELIMIT'])) $this->sSYSTEM->sCONFIG['sARTICLELIMIT'] = 12;
		if (empty($filterByCategory)){
			$filterByCategory = $this->sSYSTEM->sLanguageData[$this->sSYSTEM->sLanguage]["parentID"];
		} else {
			$filterByCategory = intval($filterByCategory);
		}
		$sql = "
			SELECT s_articles.id AS id
			FROM s_articles, s_articles_categories 
			WHERE active=1 
			AND s_articles.id=s_articles_categories.articleID
			AND s_articles_categories.categoryID=?
			ORDER BY datum DESC LIMIT ".$this->sSYSTEM->sCONFIG['sARTICLELIMIT'];
		$getArticles = $this->sSYSTEM->sDB_CONNECTION->CacheGetAll($this->sSYSTEM->sCONFIG['sCACHESEARCH'],$sql,array($filterByCategory));
		return $getArticles;
	}
	
	 /**
	 * Get all special offers
	 * @access public
	 * @return array
	 */
	public function sGetArticlesSnips ($filterByCategory){	
		$getArticles = $this->sSYSTEM->sDB_CONNECTION->CacheGetAll($this->sSYSTEM->sCONFIG['sCACHESEARCH'],"
		SELECT DISTINCT s_articles.id AS id FROM s_articles, s_articles_prices 
		WHERE active=1 
		AND s_articles.id=s_articles_prices.articleID
		AND s_articles_prices.pseudoprice!=0
		AND s_articles_prices.pricegroup = '".$this->sSYSTEM->sUSERGROUP."'
		ORDER BY datum DESC 
		");
		return $getArticles;
	}
	
	 /**
	 * Get id from all articles, that belongs to a specific supplier
	 * @param int $supplierID Supplier id (s_articles.supplierID)
	 * @access public
	 * @return array
	 */
	public function sGetArticlesBySupplier ($supplierID = null){
		if (!empty($supplierID)) $this->sSYSTEM->_GET['sSearch'] = $supplierID;
		if (!$this->sSYSTEM->_GET['sSearch']) return;
		$sSearch = intval($this->sSYSTEM->_GET['sSearch']);
		
		$getArticles = $this->sSYSTEM->sDB_CONNECTION->CacheGetAll($this->sSYSTEM->sCONFIG['sCACHESEARCH'],"
		SELECT id FROM s_articles WHERE supplierID=? AND active=1 ORDER BY topseller DESC 
		",array($sSearch));
		
		return $getArticles;
		
		
	}
	
    /**
	 * Get articles by name
	 * @param string $orderBy Sort
	 * @param int $category Filter by category id
	 * @param string $mode 
	 * @param string $search searchterm
	 * @access public
	 * @return array
	 */
	public function sGetArticlesByName ($orderBy="a.topseller DESC",$category=0,$mode="",$search="")
	{		
		if (empty($search) && !empty($this->sSYSTEM->_GET['sSearch'])){
			$search = $this->sSYSTEM->_GET['sSearch'];
		}
		if (empty($search) && empty($mode)){
			return false;
		}
		
		if ($mode=="supplier"){
			$search = intval($search);
		}
		
		$sCategory = $this->sSYSTEM->sLanguageData[$this->sSYSTEM->sLanguage]["parentID"] ? $this->sSYSTEM->sLanguageData[$this->sSYSTEM->sLanguage]["parentID"] : $this->sSYSTEM->sCONFIG["sCATEGORYPARENT"];
		if (!empty($category)) $sCategory = $category;		
		$sSearch = trim(stripslashes(html_entity_decode($search)));
		
		
		if (strlen($sSearch)<(int)$this->sSYSTEM->sCONFIG["sMINSEARCHLENGHT"] && empty($mode)){
			return false;
		}
		
		$this->sCreateTranslationTable();	
			
		$sPage = !empty($this->sSYSTEM->_GET['sPage']) ? (int) $this->sSYSTEM->_GET['sPage'] : 1;

		if ($this->sSYSTEM->_GET['sPerPage']){
			$this->sSYSTEM->_SESSION['sPerPage'] = (int) $this->sSYSTEM->_GET['sPerPage'];
		}
		if ($this->sSYSTEM->_POST['sPerPage']){
			$this->sSYSTEM->_SESSION['sPerPage'] = (int) $this->sSYSTEM->_POST['sPerPage'];
		}
		
		if ($this->sSYSTEM->_SESSION['sPerPage']){
			$this->sSYSTEM->_GET['sPerPage'] = $this->sSYSTEM->_SESSION['sPerPage'];
		}
		
		if ($this->sSYSTEM->_GET['sPerPage']){
			$this->sSYSTEM->sCONFIG['sARTICLESPERPAGE'] = $this->sSYSTEM->_GET['sPerPage'];
		}

		$sLimitStart = ($sPage-1) * $this->sSYSTEM->sCONFIG['sARTICLESPERPAGE'];
		$sLimitEnd = $this->sSYSTEM->_GET['sPerPage'] ? $this->sSYSTEM->_GET['sPerPage'] : $this->sSYSTEM->sCONFIG['sARTICLESPERPAGE'];
		
		if (empty($this->sSYSTEM->sCONFIG['sARTICLELIMIT'])) $this->sSYSTEM->sCONFIG['sARTICLELIMIT'] = 125;
		if (!empty($mode)){
			$limitNew = $this->sSYSTEM->sCONFIG['sARTICLELIMIT'];
		}
		
		$ret = array();
		
		if ($mode!="supplier"){
			$sSearch = explode(" ",$sSearch);
			if (!is_array($sSearch)){
				$sSearch = array(0=>$sSearch);
			}
			
			foreach ($sSearch as $sqlSearch){
				$sql_search[] = $this->sSYSTEM->sDB_CONNECTION->qstr("%$sqlSearch%");
			}
			if(!empty($this->sSYSTEM->sLanguageData[$this->sSYSTEM->sLanguage]["id"]))
			{
				foreach ($sSearch as $search){
					$search = $this->sSYSTEM->sDB_CONNECTION->qstr("%$search%");
					$sql_add_where .= "
							OR (
								'{$this->sSYSTEM->sLanguageData[$this->sSYSTEM->sLanguage]["id"]}'=t.languageID 
								AND (t.name LIKE $search OR t.keywords LIKE $search)
							)
					";
				}
				$sql_add_join = "
					LEFT JOIN s_articles_translations AS t
					ON a.id=t.articleID
				";
			}
			
			$sqlFields = array("s.name","a.name","a.keywords","d.ordernumber");
			$sql_search_fields .= " OR (";
			foreach ($sql_search as $term){
				$sql_search_fields .= " (";
				foreach ($sqlFields as $field){
						$sql_search_fields .= "$field LIKE $term OR ";
				}
				$sql_search_fields .= " 1 != 1) AND ";
			}
			$sql_search_fields .= "1 = 1 ) ";
		}else {
			unset($sql_add_join);
			unset($sql_add_where);
			unset($sql_search_fields);
			$sql_search_fields = "OR a.supplierID = $search";
			$sql_search[0] = "";
		}
		$sql = "
			SELECT DISTINCT
				a.id as id
			FROM 
				s_articles a
			INNER JOIN 
				s_articles_details d
			INNER JOIN
				s_articles_supplier s
			INNER JOIN 
				s_articles_categories ac
			LEFT JOIN 
				s_articles_groups_value agv
			ON agv.ordernumber LIKE ?
			AND agv.articleID=a.id
			$sql_add_join
			WHERE 
				(
					agv.valueID
					$sql_search_fields
					$sql_add_where
						
				)
			AND ac.articleID=a.id
			AND ac.categoryID=$sCategory
			AND s.id=a.supplierID
			AND a.active=1 AND a.mode = 0
			AND a.id = d.articleID
			ORDER BY $orderBy
			LIMIT $sLimitStart,$sLimitEnd
		";
		
		
		$ret["sArticles"] = $this->sSYSTEM->sDB_CONNECTION->CacheGetCol($this->sSYSTEM->sCONFIG['sCACHESEARCH'],$sql,array($sql_search[0]));
		
		
		$sql = "
			SELECT 
				COUNT(DISTINCT a.id)
			FROM 
				s_articles a
			INNER JOIN 
				s_articles_details d
			INNER JOIN
				s_articles_supplier s
			INNER JOIN 
				s_articles_categories ac
			LEFT JOIN 
				s_articles_groups_value agv
			ON agv.ordernumber LIKE ?
			AND agv.articleID=a.id
			$sql_add_join
			WHERE 
				(
					agv.valueID
					$sql_search_fields
					$sql_add_where
						
				)
			AND ac.articleID=a.id
			AND ac.categoryID=$sCategory
			AND s.id=a.supplierID
			AND a.active=1 AND a.mode = 0
			AND a.id = d.articleID
		";
		
		$sCountArticles = $this->sSYSTEM->sDB_CONNECTION->CacheGetOne($this->sSYSTEM->sCONFIG['sCACHESEARCH'],$sql,array($sql_search[0]));
		
		if ($sCountArticles >= $limitNew && !empty($mode)){
			$sCountArticles = $limitNew;
			
		}
		
		$numberPages = ceil($sCountArticles / $sLimitEnd);
		
			
		
		// Max-Value for pages (in configuration, default: 12)
		if (!empty($this->sSYSTEM->sCONFIG['sMAXPAGES']) && $this->sSYSTEM->sCONFIG['sMAXPAGES']<$numberPages){
			$numberPages = $this->sSYSTEM->sCONFIG['sMAXPAGES'];
		}
		
		// Make Array with page-structure to render in template
		$pages = array();
		
		$this->sSYSTEM->_GET["sSearch"] = urlencode(urldecode($this->sSYSTEM->_GET["sSearch"]));
		if($numberPages>1)
		{
			for ($i=1;$i<=$numberPages;$i++){
				if ($i==$sPage){
					$pages["numbers"][$i]["markup"] = true;
				}else {
					$pages["numbers"][$i]["markup"] = false;
				}
				$pages["numbers"][$i]["value"] = $i;
				$pages["numbers"][$i]["link"] = $this->sSYSTEM->sCONFIG['sBASEFILE'].$this->sSYSTEM->sBuildLink(array("sPage"=>$i),false);
				$pages["numbers"][$i]["link"]  = str_replace("+"," ",$pages["numbers"][$i]["link"] );
				
			} 
			// Previous page
			if ($sPage!=1){
				$pages["previous"] = $this->sSYSTEM->sCONFIG['sBASEFILE'].$this->sSYSTEM->sBuildLink(array("sPage"=>$sPage-1),false);
			}else {
				$pages["previous"] = null;
			}
			// Next page
			if ($sPage!=$numberPages){
				$pages["next"] = $this->sSYSTEM->sCONFIG['sBASEFILE'].$this->sSYSTEM->sBuildLink(array("sPage"=>$sPage+1),false);
			}else {
				$pages["next"] = null;
			}
			// First page
			$pages["first"] = $this->sSYSTEM->sCONFIG['sBASEFILE'].$this->sSYSTEM->sBuildLink(array("sPage"=>1),false);
		}
		
		if (!empty($this->sSYSTEM->sCONFIG['sNUMBERARTICLESTOSHOW'])){
			$this->sSYSTEM->sExtractor[] = "sPerPage";
			// Load possible values from config
			$arrayArticlesToShow = explode("|",$this->sSYSTEM->sCONFIG['sNUMBERARTICLESTOSHOW']);
		
			// Iterate through values and building array for smarty
			foreach ($arrayArticlesToShow as $articlesToShowKey => $articlesToShowValue){
				// Delete previous data
				$arrayArticlesToShow[$articlesToShowKey] = array();
				// Setting value
				$arrayArticlesToShow[$articlesToShowKey]["value"] = $articlesToShowValue;
				// Setting markup for currencly choosen value
				if ($articlesToShowValue==$sLimitEnd){
					$arrayArticlesToShow[$articlesToShowKey]["markup"] = true;
				}else {
					$arrayArticlesToShow[$articlesToShowKey]["markup"] = false;
				}
				// Building link
				$arrayArticlesToShow[$articlesToShowKey]["link"] = $this->sSYSTEM->sCONFIG['sBASEFILE'].$this->sSYSTEM->sBuildLink(array("sPerPage"=>$articlesToShowValue),false)."";
				//echo $arrayArticlesToShow[$articlesToShowKey]["link"]."<br />";
			} // -- for every possible value
		} // -- Bulding array 
		$ret['sPages'] = $pages;			
		$ret['sPerPage'] = $arrayArticlesToShow;
		$ret['sNumberArticles'] = $sCountArticles;
		$ret['sNumberPages'] = $numberPages;
		eval($this->sSYSTEM->sCallHookPoint("sArticles.php_sGetArticlesByName_BeforeEnd"));
		
		return $ret;
	}
	
	 /**
	 * Get articles by char 
	 * @param string $char A-Z
	 * @access public
	 * @return array
	 */
	public function sGetArticlesByChar ($char=null){
		if (!empty($char)) $this->sSYSTEM->_GET['sSearchChar'] = $char;
		if (!$this->sSYSTEM->_GET['sSearchChar']) return;
		$sSearch = stripslashes(htmlspecialchars($this->sSYSTEM->_GET['sSearchChar']));
		
		
		$getArticles = $this->sSYSTEM->sDB_CONNECTION->CacheGetAll($this->sSYSTEM->sCONFIG['sCACHESEARCH'],"
		SELECT DISTINCT s_articles.id AS id FROM s_articles, s_articles_categories WHERE name LIKE '$sSearch%' AND active=1 
		AND s_articles.id = s_articles_categories.articleID
		ORDER BY name ASC
		");
		
		return $getArticles;
		
		
	}
	 /**
	 * Get all articles from a specific category
	 * @param int $id category id
	 * @param bool $blog read only blog articles 
	 * @param int $limit sql limit
	 * @access public
	 * @return array
	 */
	public function sGetArticlesByCategory ($id=0,$blog=false,$limit=0)
	{
		if (Enlight()->Events()->notifyUntil('Shopware_Modules_Articles_sGetArticlesByCategory_Start', array('subject'=>$this,'id'=>$id,'blog'=>$blog,'limit'=>$limit))){
			return false;
		}
		eval($this->sSYSTEM->sCallHookPoint("sArticles.php_sGetArticlesByCategory_Start"));
		// If no category, left
		if (!empty($id)){
			$backupCategory = isset($this->sSYSTEM->_GET["sCategory"]) ? $this->sSYSTEM->_GET["sCategory"] : 0;
			$this->sSYSTEM->_GET['sCategory'] = $id;
		
		}
		if (empty($this->sSYSTEM->_GET['sCategory'])) return;
		$this->sSYSTEM->_GET['sCategory'] = intval($this->sSYSTEM->_GET['sCategory']);
				
		// Check if blog category
		$blogCategory = $blog ? true :  $this->sSYSTEM->sDB_CONNECTION->GetOne("
		SELECT blog FROM s_categories WHERE id = ?
		",array($this->sSYSTEM->_GET["sCategory"]));
		
		
		
		// Page
		$sPage = !empty($this->sSYSTEM->_GET['sPage']) ? (int) $this->sSYSTEM->_GET['sPage'] : 1;
		
		// PerPage
		if (!empty($this->sSYSTEM->_GET['sPerPage'])){
			$this->sSYSTEM->_SESSION['sPerPage'] = (int) $this->sSYSTEM->_GET['sPerPage'];
		} elseif (!empty($this->sSYSTEM->_POST['sPerPage'])){
			$this->sSYSTEM->_SESSION['sPerPage'] = (int) $this->sSYSTEM->_POST['sPerPage'];
		}
		if (!empty($limit)) {
			$sPerPage =  (int) $limit;
		} elseif (!empty($this->sSYSTEM->_SESSION['sPerPage'])) {
			$sPerPage =  (int) $this->sSYSTEM->_SESSION['sPerPage'];
		} elseif(empty($this->sSYSTEM->_GET['sPerPage'])) {
			$sPerPage =  (int) $this->sSYSTEM->sCONFIG['sARTICLESPERPAGE'];
		}
		
		// Start for Limit
		$sLimitStart = ($sPage-1) * $sPerPage;
		$sLimitEnd = $sPerPage;
		
		// Order List by
		if (isset($this->sSYSTEM->_POST['sSort'])){
			$this->sSYSTEM->_SESSION['sSort'] = (int) $this->sSYSTEM->_POST['sSort'];
		}
		if (!empty($this->sSYSTEM->_SESSION['sSort'])){
			$this->sSYSTEM->_POST['sSort'] = $this->sSYSTEM->_SESSION['sSort'];
		}
		if (!isset($this->sSYSTEM->_POST['sSort'])) $this->sSYSTEM->_POST['sSort'] = "";
		
		switch ($this->sSYSTEM->_POST['sSort']){
			case 1:
				$orderBy  = "a.datum DESC, a.changetime DESC, a.id";
				break;
			case 2:
				$orderBy  = "aDetails.sales DESC, aDetails.impressions DESC, a.id";
				break;
			case 3:
				$orderBy  = "price ASC, a.id";
				break;	
			case 4:
				$orderBy  =  "price DESC, a.id";
				break;
			case 5:
				$orderBy  =  "articleName ASC, a.id";
				break;
			case 6:
				$orderBy  =  "articleName DESC, a.id";
				break;
			default:
				$orderBy  = $this->sSYSTEM->sCONFIG['sORDERBYDEFAULT'].', a.id';
		}
				
		if(strpos($orderBy,'price')!==false)
		{
			$select_price = "
				IFNULL((
					SELECT IFNULL(gp.price,gp2.price) as min_price
					FROM s_articles_groups_value v
					
					LEFT JOIN s_articles_groups_settings s
					ON s.articleID=v.articleID
					
					LEFT JOIN s_articles_groups_prices gp
					ON gp.valueID=v.valueID
					AND gp.groupkey='{$this->sSYSTEM->sUSERGROUP}'
					AND gp.price!=0
								
					LEFT JOIN s_articles_groups_prices gp2
					ON gp2.valueID=v.valueID
					AND gp2.groupkey='EK'
					AND gp2.price!=0
					
					WHERE v.active=1
					AND (s.instock IS NULL OR s.instock!=1 OR v.instock>0)
					AND IFNULL(gp.price,gp2.price) IS NOT NULL
					AND v.articleID=a.id
					
					ORDER BY min_price
					LIMIT 1
				),(
					SELECT IFNULL(p.price,p2.price) as min_price
					FROM s_articles_details d
					
					LEFT JOIN s_articles_prices p
					ON p.articleDetailsID=d.id
					AND p.pricegroup='{$this->sSYSTEM->sUSERGROUP}'
					AND p.to='beliebig'
					
					LEFT JOIN s_articles_prices p2
					ON p2.articledetailsID=d.id
					AND p2.pricegroup='EK'
					AND p2.to='beliebig'
					
					WHERE d.articleID=a.id
					
					ORDER BY min_price
					LIMIT 1
				))
			";
		}
		else
		{
			$select_price = 'IFNULL(p.price,p2.price)';
		}
		
		if (isset($this->sSYSTEM->_GET['sSupplier']) && $this->sSYSTEM->_GET['sSupplier']==-1||!empty($this->sSYSTEM->_GET['sFilterProperties'])){
			unset($this->sSYSTEM->_SESSION["sSupplier".$this->sSYSTEM->_GET['sCategory']]);
			unset($this->sSYSTEM->_GET['sSupplier']);
		}
		
		if (empty($this->sSYSTEM->_SESSION["sSupplier".$this->sSYSTEM->_GET['sCategory']])){
				foreach ($this->sSYSTEM->_SESSION as $sessKey => $sessValue){
					if (preg_match("/sSupplier/",$sessKey)){
						unset ($this->sSYSTEM->_SESSION[$sessKey]);
					}
				}
		}
		
		if ($this->sSYSTEM->_SESSION["sSupplier".$this->sSYSTEM->_GET['sCategory']] && !$this->sSYSTEM->_GET['sSupplier']){
			$this->sSYSTEM->_GET['sSupplier'] = $this->sSYSTEM->_SESSION["sSupplier".$this->sSYSTEM->_GET['sCategory']];
		}
		
		// Saving filter state, r303
		if (isset($this->sSYSTEM->_GET['sRemoveProperties'])||!empty($this->sSYSTEM->_GET['sSupplier'])){
			unset($this->sSYSTEM->_SESSION["sFilterProperties".$this->sSYSTEM->_GET['sCategory']]);
		}
		if (isset($this->sSYSTEM->_SESSION["sFilterProperties".$this->sSYSTEM->_GET['sCategory']]) && empty($this->sSYSTEM->_GET['sFilterProperties'])){
			$this->sSYSTEM->_GET['sFilterProperties'] = $this->sSYSTEM->_SESSION["sFilterProperties".$this->sSYSTEM->_GET['sCategory']];
		}
		if (!empty($this->sSYSTEM->_GET['sFilterProperties'])){
			$this->sSYSTEM->_SESSION["sFilterProperties".$this->sSYSTEM->_GET['sCategory']] = $this->sSYSTEM->_GET['sFilterProperties'];
		}
		
		
		if (!empty($this->sSYSTEM->_GET['sSupplier'])){
			$this->sSYSTEM->_SESSION["sSupplier".$this->sSYSTEM->_GET['sCategory']] = $this->sSYSTEM->_GET['sSupplier'];
			$supplierInfo = $this->sGetSupplierById ($this->sSYSTEM->_GET['sSupplier']);
			$supplierSQL = "AND supplierID=".intval($this->sSYSTEM->_GET['sSupplier']);
		}else {
			$supplierSQL = "";
			$supplierInfo = "";
		}

		
		/*
		Shopware 2.1 Layered navigation
		*/
		$addFilterSQL = "";
		$addFilterWhere = "";
		if (!empty($this->sSYSTEM->_GET['sFilterProperties']))
		{
			$activeFilters = preg_split('/\|/',$this->sSYSTEM->_GET["sFilterProperties"],-1, PREG_SPLIT_NO_EMPTY);
			$activeFilters = array_map(create_function('$e', 'return (int) $e;'),$activeFilters);
			$sql = 'SELECT id, optionID, groupID, value FROM s_filter_values WHERE id IN ('.implode(',',$activeFilters).')';
			
			$activeFiltersValues = $this->sSYSTEM->sDB_CONNECTION->CacheGetAll($this->sSYSTEM->sCONFIG['sCACHESUPPLIER'],$sql);
			
			if (!empty($activeFiltersValues))
			foreach ($activeFiltersValues as $filter)
			{
				$addFilterSQL .= "
					JOIN s_filter_values fv{$filter['id']}
					ON fv{$filter['id']}.articleID = a.id
					AND fv{$filter['id']}.optionID = {$filter['optionID']}
					AND fv{$filter['id']}.value = ".$this->sSYSTEM->sDB_CONNECTION->qstr($filter['value'])."
				";
			}
		}
		
		$this->sSYSTEM->sCONFIG['sMARKASNEW'] = (int) $this->sSYSTEM->sCONFIG['sMARKASNEW'];
		$this->sSYSTEM->sCONFIG['sMARKASTOPSELLER'] = (int) $this->sSYSTEM->sCONFIG['sMARKASTOPSELLER'];
		
		if (!isset($addAlias)) $addAlias = "";
		if (!isset($addFilterHaving)) $addFilterHaving = "";
		
		eval($this->sSYSTEM->sCallHookPoint("sArticles.php_sGetArticlesByCategory_BeforeSQL"));
		if (empty($blogCategory)){
		$sql = "
			SELECT SQL_CALC_FOUND_ROWS
				a.id as articleID,aDetails.id AS articleDetailsID, weight,aDetails.ordernumber,a.datum,releasedate
				additionaltext, shippingfree,shippingtime,instock, a.description AS description, description_long,
				aSupplier.name AS supplierName, aSupplier.img AS supplierImg, a.name AS articleName, topseller as highlight,
				($select_price*100/(100-IFNULL(cd.discount,0))) as price,laststock,
				sales, IF(p.pseudoprice,p.pseudoprice,p2.pseudoprice) as pseudoprice, aTax.tax,
				minpurchase,
				purchasesteps,
				maxpurchase,
				purchaseunit,
				referenceunit,
				unitID,
				pricegroupID,
				pricegroupActive,
				IFNULL(p.pricegroup,IFNULL(p2.pricegroup,'EK')) as pricegroup,
				attr1,attr2,attr3,attr4,attr5,attr6,attr7,attr8,attr9,attr10,
				attr11,attr12,attr13,attr14,attr15,attr16,attr17,attr18,attr19,attr20,
				cd.discount,
				(
					SELECT TRIM(GROUP_CONCAT(additionaltext SEPARATOR ', ')) as additionaltext
					FROM s_articles_details
					WHERE articleID=a.id
					AND additionaltext!=''
					GROUP BY articleID
					HAVING COUNT(*)>1
					ORDER BY kind, position, additionaltext
				) as variants,
				IFNULL((SELECT 1 FROM s_articles_groups WHERE articleID=a.id LIMIT 1),0) as sConfigurator,
				IFNULL((SELECT 1 FROM s_articles_esd WHERE articleID=a.id LIMIT 1),0) as esd,
				IFNULL((SELECT CONCAT(AVG(points),'|',COUNT(*)) as votes FROM s_articles_vote WHERE active=1 AND articleID=a.id),'0.00|00') as sVoteAverange,
				IF(DATEDIFF(NOW(), a.datum)<={$this->sSYSTEM->sCONFIG['sMARKASNEW']},1,0) as newArticle,
				IF(aDetails.sales>={$this->sSYSTEM->sCONFIG['sMARKASTOPSELLER']},1,0) as topseller,
				IF(a.releasedate>CURDATE(),1,0) as sUpcoming,
				IF(a.releasedate>CURDATE(),DATE_FORMAT(a.releasedate, '%d.%m.%Y'),'') as sReleasedate
			FROM s_articles AS a
			INNER JOIN s_articles_categories AS aCategories
			INNER JOIN s_articles_supplier AS aSupplier
			INNER JOIN s_core_tax AS aTax
			INNER JOIN s_articles_attributes AS aAttributes
				$addFilterSQL
			INNER JOIN s_articles_details AS aDetails

			LEFT JOIN s_articles_prices p
			ON p.articleDetailsID=aDetails.id
			AND p.pricegroup='{$this->sSYSTEM->sUSERGROUP}'
			AND p.to='beliebig'
			
			LEFT JOIN s_articles_prices p2
			ON p2.articledetailsID=aDetails.id
			AND p2.pricegroup='EK'
			AND p2.to='beliebig'
			
			LEFT JOIN s_core_customergroups cg
			ON cg.groupkey = '{$this->sSYSTEM->sUSERGROUP}'
			
			LEFT JOIN s_core_pricegroups_discounts cd
			ON a.pricegroupActive=1
			AND cd.groupID=a.pricegroupID
			AND cd.customergroupID=cg.id
			AND cd.discountstart=(SELECT MAX(discountstart) FROM s_core_pricegroups_discounts WHERE groupID=a.pricegroupID AND cd.customergroupID=cg.id)
			
			WHERE 
				aCategories.categoryID=".$this->sSYSTEM->_GET['sCategory']." AND aCategories.articleID=a.id 
				AND a.taxID=aTax.id
				AND a.mode = 0
				$addFilterWhere
				$supplierSQL
				AND aAttributes.articleID = a.id
				AND aAttributes.articledetailsID=aDetails.id
				AND (
					SELECT articleID 
					FROM s_articles_avoid_customergroups 
					WHERE articleID = a.id AND customergroupID = ".$this->sSYSTEM->sUSERGROUPDATA["id"]."
				) IS NULL
				AND aSupplier.id=a.supplierID AND aDetails.articleID=a.id AND aDetails.kind=1 AND a.active=1
				$addAlias
			$addFilterHaving
			ORDER BY $orderBy
			LIMIT $sLimitStart,$sLimitEnd
		";
		}else {
			if (!empty($this->sSYSTEM->_GET["dateFilter"])){
				$datum = explode("|",$this->sSYSTEM->_GET["dateFilter"]);
				$month = intval($datum[0]);
				$year = intval($datum[1]);
				if (!empty($month) && !empty($year)){
					$addSQL = "
					AND MONTH(changetime) = $month AND YEAR(changetime) = $year
					";
				}
			}
			if (!isset($addAlias)) $addAlias = "";
			if (!isset($addSQL)) $addSQL = "";
			if (!isset($addFilterHaving)) $addFilterHaving = "";
			
			$sql = "
			SELECT SQL_CALC_FOUND_ROWS
				a.id as articleID,aDetails.id AS articleDetailsID, weight,ordernumber,datum,releasedate,
				additionaltext, shippingfree,shippingtime,instock, a.description AS description, description_long,
				aSupplier.name AS supplierName, aSupplier.img AS supplierImg, a.name AS articleName, topseller,supplierID,
				minpurchase,
				purchasesteps,
				DATE_FORMAT(changetime,'%d.%M %Y %H:%i') AS datumFormated,
				changetime,
				maxpurchase,
				purchaseunit,
				referenceunit,
				unitID,
				attr1,attr2,attr3,attr4,attr5,attr6,attr7,attr8,attr9,attr10,
				attr11,attr12,attr13,attr14,attr15,attr16,attr17,attr18,attr19,attr20,
				(
					SELECT GROUP_CONCAT(value SEPARATOR ', ')
					FROM s_filter_values
					WHERE articleID=a.id
					GROUP BY articleID
					LIMIT 1
				) as tags,
				IFNULL((SELECT CONCAT(AVG(points),'|',COUNT(*)) as votes FROM s_articles_vote WHERE active=1 AND articleID=a.id),'0.00|00') as sVoteAverange,
				IF(DATEDIFF(NOW(), a.datum)<={$this->sSYSTEM->sCONFIG['sMARKASNEW']},1,0) as newArticle
			FROM s_articles AS a
			INNER JOIN s_articles_categories AS aCategories
			INNER JOIN s_articles_supplier AS aSupplier
			INNER JOIN s_articles_attributes AS aAttributes
				$addFilterSQL
			INNER JOIN s_articles_details AS aDetails
			WHERE 
				aCategories.categoryID=".$this->sSYSTEM->_GET['sCategory']." AND aCategories.articleID=a.id 
				AND a.mode = 1
				$addFilterWhere
				$supplierSQL
				AND aAttributes.articleID = a.id
				AND aAttributes.articledetailsID=aDetails.id
				AND aSupplier.id=a.supplierID AND aDetails.articleID=a.id AND aDetails.kind=1 AND a.active=1
				AND (
					SELECT articleID 
					FROM s_articles_avoid_customergroups 
					WHERE articleID = a.id AND customergroupID = ".$this->sSYSTEM->sUSERGROUPDATA["id"]."
				) IS NULL
				AND changetime <= NOW()
				$addAlias
				$addSQL
			$addFilterHaving
			ORDER BY a.changetime DESC
			LIMIT $sLimitStart,$sLimitEnd
		";
		}
		eval($this->sSYSTEM->sCallHookPoint("sArticles.php_sGetArticlesByCategory_AfterSQL"));
		
		$sql = Enlight()->Events()->filter('Shopware_Modules_Articles_sGetArticlesByCategory_FilterSql', $sql, array('subject'=>$this,'id'=>$this->sSYSTEM->_GET['sCategory']));
		
		$this->sSYSTEM->sDB_CONNECTION->LogSQL(false);
		$articles = $this->sSYSTEM->sDB_CONNECTION->CacheGetAll($this->sSYSTEM->sCONFIG['sCACHECATEGORY'],$sql,false,"category_".$this->sSYSTEM->_GET['sCategory']);
		
		
		$sql = 'SELECT FOUND_ROWS() as count_'.md5($sql);
		$numberArticles = $this->sSYSTEM->sDB_CONNECTION->CacheGetOne($this->sSYSTEM->sCONFIG['sCACHECATEGORY'],$sql,false,"category_".$this->sSYSTEM->_GET['sCategory']);
		
		$this->sSYSTEM->sDB_CONNECTION->LogSQL($this->sSYSTEM->sCONFIG['sADODB_LOG']);
		if (!isset($addAlias)) $addAlias = "";
		if (!isset($addFilterHaving)) $addFilterHaving = "";
		if (!empty($blogCategory)){
			// Now get all possible date filters
			$sql = "
				SELECT 
					COUNT(a.id) AS countArticles,
					DATE_FORMAT(changetime,'%m.%Y') AS datumFormated,
					changetime
				FROM s_articles AS a
				INNER JOIN s_articles_categories AS aCategories
				INNER JOIN s_articles_supplier AS aSupplier
				INNER JOIN s_articles_attributes AS aAttributes
					$addFilterSQL
				INNER JOIN s_articles_details AS aDetails
				WHERE 
					aCategories.categoryID=".$this->sSYSTEM->_GET['sCategory']." AND aCategories.articleID=a.id 
					AND a.mode = 1
					$addFilterWhere
					$supplierSQL
					AND aAttributes.articleID = a.id
					AND aAttributes.articledetailsID=aDetails.id
					AND a.changetime <= NOW()
					AND aSupplier.id=a.supplierID AND aDetails.articleID=a.id AND aDetails.kind=1 AND a.active=1
					$addAlias
				$addFilterHaving
				GROUP BY YEAR(changetime),MONTH(changetime)
				ORDER BY YEAR(changetime) DESC ,MONTH(changetime) DESC
			";
			$getDateFilter = $this->sSYSTEM->sDB_CONNECTION->GetAll($sql);
			if (!empty($getDateFilter) && count($getDateFilter)){
				$this->sSYSTEM->sExtractor[] = "dateFilter";
				foreach ($getDateFilter as $key => $filter){
					$getDateFilter[$key]["link"] = $this->sSYSTEM->sCONFIG['sBASEFILE'].$this->sSYSTEM->sBuildLink(array("dateFilter"=>implode("|",explode(".",$filter["datumFormated"]))),false);
				}
				$result["sFilterDate"] = $getDateFilter;
			}
		}
		/**
		 * LIVE-SHOPPING - START
		 */
		foreach ($articles as $articlekey => $article) {
						
			$sql = 'SELECT id FROM s_articles_live WHERE articleID=?';
			$liveShoppingIDs = $this->sSYSTEM->sDB_CONNECTION->GetRow($sql, array($article['articleID']));
			
			if(!empty($liveShoppingIDs)) {
				foreach ($liveShoppingIDs as $liveShoppingID) {
					$article = $this->sGetLiveShopping('fix', 0, $article);
					$article['liveshoppingData'] = $article['liveshoppingData'][0];
					if(isset($article['liveshoppingData']))	
					{
						//Relevant für eindimensionale Artikel
						$article['price'] = $article['liveshoppingData']['net_price'];
						unset($article['pseudoprice']);
						
						$articles[$articlekey] = $article;	
						break;					
					}
				}
			}
		}
		/**
		 * LIVE-SHOPPING - END
		 */
		
		// How many pages in this category?
		$numberPages = ceil($numberArticles / $sLimitEnd);

		// Max-Value for pages (in configuration, default: 12)
		if ($this->sSYSTEM->sCONFIG['sMAXPAGES']>0 && $this->sSYSTEM->sCONFIG['sMAXPAGES']<$numberPages){
			$numberPages = $this->sSYSTEM->sCONFIG['sMAXPAGES'];
		}
		
		// Make Array with page-structure to render in template
		$pages = array();
		
		if($numberPages>1)
		{
			for ($i=1;$i<=$numberPages;$i++){
				if ($i==$sPage){
					$pages["numbers"][$i]["markup"] = true;
				}else {
					$pages["numbers"][$i]["markup"] = false;
				}
				$pages["numbers"][$i]["value"] = $i;
				$pages["numbers"][$i]["link"] = $this->sSYSTEM->sCONFIG['sBASEFILE'].$this->sSYSTEM->sBuildLink(array("sPage"=>$i),false);
				
			} 
			// Previous page
			if ($sPage!=1){
				$pages["previous"] = $this->sSYSTEM->sCONFIG['sBASEFILE'].$this->sSYSTEM->sBuildLink(array("sPage"=>$sPage-1),false);
			}else {
				$pages["previous"] = null;
			}
			// Next page
			if ($sPage!=$numberPages){
				$pages["next"] = $this->sSYSTEM->sCONFIG['sBASEFILE'].$this->sSYSTEM->sBuildLink(array("sPage"=>$sPage+1),false);
			}else {
				$pages["next"] = null;
			}
		}
		
		
		// Building array for manage the quantity of articles per page
		if (isset($this->sSYSTEM->sCONFIG['sNUMBERARTICLESTOSHOW'])){
			$this->sSYSTEM->sExtractor[] = "sPerPage";
			// Load possible values from config
			$arrayArticlesToShow = explode("|",$this->sSYSTEM->sCONFIG['sNUMBERARTICLESTOSHOW']);
		
			// Iterate through values and building array for smarty
			foreach ($arrayArticlesToShow as $articlesToShowKey => $articlesToShowValue){
				// Delete previous data
				$arrayArticlesToShow[$articlesToShowKey] = array();
				// Setting value
				$arrayArticlesToShow[$articlesToShowKey]["value"] = $articlesToShowValue;
				// Setting markup for currencly choosen value
				if ($articlesToShowValue==$sLimitEnd){
					$arrayArticlesToShow[$articlesToShowKey]["markup"] = true;
				}else {
					$arrayArticlesToShow[$articlesToShowKey]["markup"] = false;
				}
				// Building link
				$arrayArticlesToShow[$articlesToShowKey]["link"] = $this->sSYSTEM->sCONFIG['sBASEFILE'].$this->sSYSTEM->sBuildLink(array("sPerPage"=>$articlesToShowValue),false)."";
				//echo $arrayArticlesToShow[$articlesToShowKey]["link"]."<br />";
			} // -- for every possible value
		} // -- Building array 
		
		eval($this->sSYSTEM->sCallHookPoint("sArticles.php_sGetArticlesByCategory_AfterCalculatingPages"));
	
		// Iterate through articles and complete data
		if (count($articles)){
			if(!empty($this->sSYSTEM->sLanguage)&&empty($this->sSYSTEM->sLanguageData[$this->sSYSTEM->sLanguage]["skipbackend"]))
			{
				// Load translations
				$ids = array();
				foreach ($articles as $k => $v){
					$ids[] = $v["articleID"];
				}
				
				$articles = $this->sGetTranslations($articles,$ids,"article",$this->sSYSTEM->sLanguage);
			}
			foreach ($articles as $articleKey => $articleValue)
			{
				$articles[$articleKey] = Enlight()->Events()->filter('Shopware_Modules_Articles_sGetArticlesByCategory_FilterLoopStart', $articles[$articleKey], array('subject'=>$this,'id'=>$this->sSYSTEM->_GET['sCategory']));
				
				if (!empty($blogCategory))
				{
					$articles[$articleKey]["sVoteComments"] = $this->sGetArticlesVotes($articles[$articleKey]["articleID"]);
					// Get-Category
					$sql = "
				    SELECT c2.id,c.id AS catID,c.description FROM s_articles_categories a, s_categories c
				    LEFT JOIN s_categories c2 ON c2.parent = c.id
					WHERE a.articleID=?
					AND a.categoryID = c.id
					AND c2.id IS NULL
					LIMIT 1
				    ";
					$catInfo = $this->sSYSTEM->sDB_CONNECTION->GetRow($sql,array($articles[$articleKey]["articleID"]));
					
					if (!empty($catInfo["catID"])){
						unset($this->sSYSTEM->_GET["sPerPage"]);
						$catInfo["linkCategory"] = $this->sSYSTEM->sCONFIG['sBASEFILE'].$this->sSYSTEM->sBuildLink(array("sViewport"=>"cat","sCategory"=>$catInfo["catID"]),false);
						
					}
					else {
						$catInfo = array();
					}
					$articles[$articleKey]["categoryInfo"] = $catInfo;
					$this->sSYSTEM->sExtractor[] = "sSupplier";
					$articles[$articleKey]["linkSupplier"] =  $this->sSYSTEM->sCONFIG['sBASEFILE'].$this->sSYSTEM->sBuildLink(array("sViewport"=>"cat","sCategory"=>$this->sSYSTEM->_GET["sCategory"],"sSupplier"=>$articleValue["supplierID"]),false);
				}
				
				$articles[$articleKey]["sVariantArticle"] = false;
				eval($this->sSYSTEM->sCallHookPoint("sArticles.php_sGetArticlesByCategory_LoopArticlesStart"));
				// Translate base 
				
				
				// Check if price is set for this customergroup
				
				eval($this->sSYSTEM->sCallHookPoint("sArticles.php_sGetArticlesByCategory_LoopArticlesStart1"));			
				if ($blog!=true){
					$cheapestPrice =  $this->sGetCheapestPrice($articles[$articleKey]["articleID"],$articles[$articleKey]["pricegroup"],$articles[$articleKey]["pricegroupID"],$articles[$articleKey]["pricegroupActive"],false,true);
					
					if (!is_array($cheapestPrice)){
						$cheapestPriceT[0] = $cheapestPrice;
						$cheapestPriceT[1] = "";
						$cheapestPrice = $cheapestPriceT;
					}
					
					if (!empty($cheapestPrice[0]) && $cheapestPrice[0]!="0"){
						if ($cheapestPrice[1]<=1){
							$articles[$articleKey]["priceStartingFrom"] = $cheapestPrice[0];
						}
						$articles[$articleKey]["priceDefault"] = $articles[$articleKey]["price"];
						$articles[$articleKey]["price"] = $cheapestPrice[0];
					}
								
					// Price-Handling
					$articles[$articleKey]["price"] = $this->sCalculatingPrice($articles[$articleKey]["price"],$articles[$articleKey]["tax"],$articles[$articleKey]);		
					
					if (!empty($articles[$articleKey]["pseudoprice"])){
						$articles[$articleKey]["pseudoprice"] = $this->sCalculatingPrice($articles[$articleKey]["pseudoprice"],$articles[$articleKey]["tax"],$articles[$articleKey]);
						$discPseudo =  str_replace(",",".",$articles[$articleKey]["pseudoprice"]);
						$discPrice = str_replace(",",".",$articles[$articleKey]["price"]);
						$discount = round(($discPrice / $discPseudo * 100) - 100,2)*-1;
						$articles[$articleKey]["pseudopricePercent"] = array("int"=>round($discount,0),"float"=>$discount);
					}		
					
					
				}
				// ---
				
				// Read unit if set
				if ($articles[$articleKey]["unitID"]){
					/*$articles[$articleKey]["sUnit"] = $this->sSYSTEM->sDB_CONNECTION->CacheGetRow($this->sSYSTEM->sCONFIG['sCACHEARTICLE'],"
					SELECT unit, description FROM s_core_units WHERE id={$articles[$articleKey]["unitID"]}
					");*/
					$articles[$articleKey]["sUnit"] = $this->sGetUnit($articles[$articleKey]["unitID"]);
				}
				
				$articles[$articleKey]['sVoteAverange'] = explode('|', $articles[$articleKey]['sVoteAverange']);
				$articles[$articleKey]['sVoteAverange'] = array(
					'averange' => round($articles[$articleKey]['sVoteAverange'][0], 2),
					'count' => round($articles[$articleKey]['sVoteAverange'][1]),
				);
				
				$articles[$articleKey]["articleName"] = $this->sOptimizeText($articles[$articleKey]["articleName"]);
				
				if (empty($blogCategory)){
					$articles[$articleKey]["description_long"] = strlen($articles[$articleKey]["description"])>5 ? $articles[$articleKey]["description"] : $this->sOptimizeText($articles[$articleKey]["description_long"]);
				}
				// Require Pictures
				$articles[$articleKey]["image"] = $this->sGetArticlePictures($articles[$articleKey]["articleID"],true,0);
		
				// Links to details, basket
				$articles[$articleKey]["linkBasket"] = "http://".$this->sSYSTEM->sCONFIG['sBASEPATH']."/".$this->sSYSTEM->sCONFIG['sBASEFILE']."?sViewport=basket&sAdd=".$articles[$articleKey]["ordernumber"];
				$articles[$articleKey]["linkDetails"] = $this->sSYSTEM->sCONFIG['sBASEFILE']."?sViewport=detail&sArticle=".$articles[$articleKey]["articleID"]."&sCategory=".$this->sSYSTEM->_GET['sCategory'];
				$articles[$articleKey]["priceNumeric"] = floatval(str_replace(",",".",$articles[$articleKey]["price"]));
				eval($this->sSYSTEM->sCallHookPoint("sArticles.php_sGetArticlesByCategory_LoopArticlesEnd"));
				if ($articles[$articleKey]["purchaseunit"]  > 0 && !empty($articles[$articleKey]["referenceunit"])){
					
				     $basePrice = $this->sCalculatingPriceNum(str_replace(",",".",$articles[$articleKey]["price"]),0,$articles[$articleKey],$articles[$articleKey],array("liveshoppingID"=>1),true);
				     $basePrice = $basePrice / $articles[$articleKey]["purchaseunit"] * $articles[$articleKey]["referenceunit"];
				     $basePrice = $this->sFormatPrice($basePrice);
				     $articles[$articleKey]["referenceprice"] = $basePrice;
			    }
				$articles[$articleKey] = Enlight()->Events()->filter('Shopware_Modules_Articles_sGetArticlesByCategory_FilterLoopEnd', $articles[$articleKey], array('subject'=>$this,'id'=>$this->sSYSTEM->_GET['sCategory']));
				
				
			} // For every article in this list

			// Build result array
			$result['sArticles'] = $articles;
			$result['sPages'] = $pages;			
			
			// Shopware 2.1 - layered navigation
			$result["sCategoryInfo"] = $this->sSYSTEM->sMODULES['sCategories']->sGetCategoryContent($this->sSYSTEM->_GET['sCategory']);
			
			if (empty($result["sCategoryInfo"]["hidefilter"]))
			{
				$articleProperties = $this->sGetCategoryProperties();
				$result['sPropertiesOptionsOnly'] = isset($articleProperties["filterOptions"]["optionsOnly"]) ? $articleProperties["filterOptions"]["optionsOnly"] : array();
				$result['sPropertiesGrouped'] = isset($articleProperties["filterOptions"]["grouped"]) ? $articleProperties["filterOptions"]["grouped"] : array() ;
			}
			
			$result['sPerPage'] = $arrayArticlesToShow;
			$result['sSupplierInfo'] = $supplierInfo;
			$result['sNumberArticles'] = $numberArticles;
			$result['sNumberPages'] = $numberPages;
			$result['sPage'] = $sPage;

			if (!empty($this->sSYSTEM->_POST['sTemplate'])){
				$this->sSYSTEM->_SESSION['sTemplate'] = basename($this->sSYSTEM->_POST['sTemplate']);
			}
			if (!empty($this->sSYSTEM->_SESSION['sTemplate'])){
				$result['sTemplate'] = $this->sSYSTEM->_SESSION['sTemplate'];
			}
			if (!empty($this->sSYSTEM->_SESSION['sSort'])){
				$result['sSort'] = $this->sSYSTEM->_SESSION['sSort'];
			}
			
			eval($this->sSYSTEM->sCallHookPoint("sArticles.php_sGetArticlesByCategory_BeforeEnd"));
			
			if (!empty($this->sSYSTEM->sCONFIG['sTEMPLATEOLD']) && (!empty($this->sSYSTEM->_GET["sRss"]) || !empty($this->sSYSTEM->_GET["sAtom"])))
			{
				ob_end_clean();
				header("Content-Type:text/xml; charset=ISO-8859-1");

				//header("Content-Type:text/xml; charset=ISO-8859-1");
				$this->sSYSTEM->sSMARTY->assign('sConfig',$this->sSYSTEM->sCONFIG);
				$category = $this->sSYSTEM->sMODULES['sCategories']->sGetCategoryContent($this->sSYSTEM->_GET['sCategory']);
				
				$category["rssFeed"] = $this->sSYSTEM->sMODULES['sCore']->sRewriteLink($category["rssFeed"],$category["description"]);
				$category["sSelf"] = $this->sSYSTEM->sMODULES['sCore']->sRewriteLink($category["sSelf"],$category["description"]);
				$this->sSYSTEM->sSMARTY->assign('sCategory',$category);
				foreach ($articles as $k => $v){
					$articles[$k]["linkDetails"] = $this->sSYSTEM->sMODULES['sCore']->sRewriteLink($v["linkDetails"],$v["articleName"]);
				}
				$this->sSYSTEM->sSMARTY->assign('sArticles',$articles);
				
				$this->sSYSTEM->sSMARTY->display($this->sSYSTEM->_GET["sRss"] ? "blog/details.rss.tpl" : "blog/details.atom.tpl");
				exit;
			}
			if (!empty($backupCategory)){
				$this->sSYSTEM->_GET['sCategory'] = $backupCategory;
			}
			
			$result = Enlight()->Events()->filter('Shopware_Modules_Articles_sGetArticlesByCategory_FilterResult', $result, array('subject'=>$this,'id'=>$this->sSYSTEM->_GET['sCategory']));
			
			return $result;
		
		} //  For the case, articles were found
	}
     /**
	 * Get supplier by id
	 * @param int $id - s_articles_supplier.id 
	 * @access public
	 * @return array
	 */
	public function sGetSupplierById ($id){
		$sql = "
		SELECT asupplier.id AS id, asupplier.name AS name, asupplier.img AS image 
		FROM s_articles_supplier AS asupplier WHERE id=?
		";
		
		$getSupplier = $this->sSYSTEM->sDB_CONNECTION->CacheGetRow($this->sSYSTEM->sCONFIG['sCACHESUPPLIER'],$sql,array($id));
		
		$this->sSYSTEM->sExtractor[] = "sSupplier";

		if ($getSupplier["image"]) $getSupplier["image"] = $this->sSYSTEM->sPathSupplierImg.$getSupplier["image"];
		
		$getSupplier["link"] = $this->sSYSTEM->sCONFIG['sBASEFILE'].$this->sSYSTEM->sBuildLink(array("sSupplier"=>-1),false)."";
		
		
		
		return $getSupplier;
	}
	
	 /**
	 * Get all available article properties from a specific category
	 * @param int $id - category id
	 * @access public
	 * @return array
	 */
	public function sGetCategoryProperties ($categoryID = null)
	{
		
		if(empty($categoryID))
			$categoryID = $this->sSYSTEM->_GET["sCategory"];
		$categoryID = (int) $categoryID;

		$addFilterSQL = "";
		$addFilterWhere = "";
		
		if (!empty($this->sSYSTEM->_GET["sFilterProperties"]))
		{
			$activeFilters = preg_split('/\|/',$this->sSYSTEM->_GET["sFilterProperties"],-1, PREG_SPLIT_NO_EMPTY);
			$activeFilters = array_map(create_function('$e', 'return (int) $e;'),$activeFilters);
			$sql = 'SELECT id, optionID, groupID, value FROM s_filter_values WHERE id IN ('.implode(',',$activeFilters).')';
			$activeFiltersValues = $this->sSYSTEM->sDB_CONNECTION->CacheGetAll($this->sSYSTEM->sCONFIG['sCACHESUPPLIER'],$sql);
			
			if (!empty($activeFiltersValues))
			{
				foreach ($activeFiltersValues as $filter)
				{
					$activeFiltersArray["activeOptions"][] = $filter["optionID"];
					$activeFiltersArray["activeValues"][] = $filter["value"];
					$activeFilterArrayLinkRemove[$filter["optionID"]] = $filter['id'];
					
					$addFilterSQL .= "
						INNER JOIN s_filter_values fv{$filter['id']}
						ON fv{$filter['id']}.articleID = a.id
						AND fv{$filter['id']}.optionID = {$filter['optionID']}
						AND fv{$filter['id']}.value = ".$this->sSYSTEM->sDB_CONNECTION->qstr($filter['value'])."
					";
				}
			}
		}
		
		$language = $this->sSYSTEM->sLanguageData[$this->sSYSTEM->sLanguage]["isocode"];
		
		$additionalSorting = 
		$sql = "
			SELECT fv.optionID AS id, COUNT(*) AS countOptionValues, fo.name AS optionName, f.name AS groupName, fv.value AS optionValue,fv.id AS uniqueID,
			st.objectdata AS optionNameTranslation, st2.objectdata AS groupNameTranslation,st3.objectdata AS articleTranslation
			FROM s_articles_categories ac
			INNER JOIN s_filter_values fv
			INNER JOIN s_filter_options fo
			INNER JOIN s_filter f
			INNER JOIN s_articles a
			$addFilterSQL
			LEFT JOIN s_core_translations AS st ON st.objecttype='propertyoption' AND st.objectkey=fo.id AND st.objectlanguage='$language'
			LEFT JOIN s_core_translations AS st2 ON st2.objecttype='propertygroup' AND st2.objectkey=fv.groupID AND st2.objectlanguage='$language'
			LEFT JOIN s_core_translations AS st3 ON st3.objecttype='properties' AND st3.objectkey=a.id AND st3.objectlanguage='$language'
			WHERE  ac.categoryID=$categoryID
			AND a.id = ac.articleID
			AND a.id = fv.articleID
			AND a.filtergroupID = f.id
			AND a.active =1
			AND (
					SELECT articleID 
					FROM s_articles_avoid_customergroups 
					WHERE articleID = a.id AND customergroupID = ".$this->sSYSTEM->sUSERGROUPDATA["id"]."
			) IS NULL
			AND a.changetime <= NOW()
			AND fv.optionID = fo.id
			AND fo.filterable = 1
			AND fv.groupID = f.id
			$addFilterWhere
			GROUP BY fv.optionID, fv.value
			ORDER BY fo.name ASC, IF(f.sortmode=1, TRIM(REPLACE(fv.value,',','.'))+0, 0), IF(f.sortmode=2, COUNT(*) , 0) DESC, fv.value
		";
		$getProperties = $this->sSYSTEM->sDB_CONNECTION->CacheGetAll($this->sSYSTEM->sCONFIG['sCACHESUPPLIER'],$sql);

		$this->sSYSTEM->sExtractor[] = "sFilterProperties";
		$this->sSYSTEM->sExtractor[] = "sFilterGroup";
		
		foreach ($getProperties as $property)
		{
			if (!empty($property["optionNameTranslation"])){
				$translation = unserialize($property["optionNameTranslation"]);
				$property["optionName"] = $translation["optionName"];
			}
			if (!empty($property["groupNameTranslation"])){
				$translation = unserialize($property["groupNameTranslation"]);
				$property["groupName"] = $translation["groupName"];
			}
			if (!empty($property["articleTranslation"])){
				$translation = unserialize($property["articleTranslation"]);
				if (!empty($translation[$property["id"]]))
					$property["optionValueTranslated"] = $translation[$property["id"]];
			}
			// Building unique filter link
			if (empty($activeFilters) || !is_array($activeFilters)){
				$activeFilters = array();
			}
			$link = $this->sSYSTEM->sCONFIG['sBASEFILE'].$this->sSYSTEM->sBuildLink(array("sFilterProperties"=>$property["uniqueID"]."|".implode("|",$activeFilters)),false)."";
			
			if (!empty($activeFiltersArray["activeOptions"]) && in_array($property["id"],$activeFiltersArray["activeOptions"]))
			{
				$optionGroupActive = true;
				$activeFiltersFixed = $activeFilters;
				$temp = $activeFilterArrayLinkRemove;
				unset($temp[$property["id"]]);
				if (empty($temp)) $temp[] = "-1";
				$linkRemoveProperty = $this->sSYSTEM->sCONFIG['sBASEFILE'].$this->sSYSTEM->sBuildLink(array("sFilterProperties"=>implode("|",$temp)),false)."";
			} else {
				$optionGroupActive = false;
			}
			
			if (!empty($activeFiltersArray["activeValues"]) && in_array($property["optionValue"],$activeFiltersArray["activeValues"]) && $optionGroupActive){
				$optionValueActive = true;
			}else {
				$optionValueActive = false;
			}
			// --
			$propertyArray["filterOptions"]["optionsOnly"][$property["optionName"]]["properties"] =  array("active"=>$optionGroupActive,"linkRemoveProperty"=>$linkRemoveProperty,"group"=>$property["groupName"]);
			$propertyArray["filterOptions"]["optionsOnly"][$property["optionName"]]["values"][$property["optionValue"]] = array(
					"name"=>$property["optionName"],
					"value"=>$property["optionValue"],
					"valueTranslation"=>$property["optionValueTranslated"],
					"count"=>$property["countOptionValues"],
					"group"=>$property["groupName"],
					"optionID"=>$property["id"],
					"link"=>$link,
					"filter"=>$property["uniqueID"]."|".implode("|",$activeFilters),
					"active"=>$optionValueActive
			);
			
			$propertyArray["filterOptions"]["grouped"][$property["groupName"]]["options"][$property["optionName"]][$property["optionValue"]] = array("name"=>$property["optionName"],"value"=>$property["optionValue"],"count"=>$property["countOptionValues"],"group"=>$property["groupName"],"optionID"=>$property["id"]);
			$propertyArray["filterOptions"]["grouped"][$property["groupName"]]["default"]["linkSelect"] = $this->sSYSTEM->sCONFIG['sBASEFILE'].$this->sSYSTEM->sBuildLink(array("sFilterGroup"=>$property["groupName"],false));
			$propertyArray["filterOptions"]["grouped"][$property["groupName"]]["default"]["linkUnselect"] = $this->sSYSTEM->sCONFIG['sBASEFILE'].$this->sSYSTEM->sBuildLink(array("sFilterGroup"=>"",false));
		}
		
		if (!isset($propertyArray)){
			$propertyArray["filterOptions"] = array();
		}
		eval($this->sSYSTEM->sCallHookPoint("sArticles.php_sGetCategoryProperties_BeforeEnd"));
		return $propertyArray;
	}
	
	 /**
	 * Get all available suppliers from a specific category
	 * @param int $id - category id
	 * @access public
	 * @return array
	 */
	public function sGetAffectedSuppliers ($id = null,$limit=30)
	{
		if(empty($id))
			$id = $this->sSYSTEM->_GET["sCategory"];
		$id = (int) $id;
		$sql = "
			SELECT s.id AS id, COUNT(*) AS countSuppliers, s.name AS name, s.img AS image
			FROM 
				s_articles a, 
				s_articles_supplier s,
				s_articles_categories ac
			WHERE a.supplierID = s.id
			AND a.active = 1
			AND (
					SELECT articleID 
					FROM s_articles_avoid_customergroups 
					WHERE articleID = a.id AND customergroupID = ".$this->sSYSTEM->sUSERGROUPDATA["id"]."
			) IS NULL
			AND a.changetime <= NOW()
			AND a.mode = 0
			AND ac.categoryID = ?
			AND ac.articleID = a.id
			GROUP BY s.id
			ORDER BY s.name ASC
			LIMIT 0 , $limit
		";
		$getSupplier = $this->sSYSTEM->sDB_CONNECTION->CacheGetAll($this->sSYSTEM->sCONFIG['sCACHESUPPLIER'],$sql,array($id));
		$this->sSYSTEM->sExtractor[] = "sSupplier";
		
		foreach ($getSupplier as $supplierKey => $supplierValue)
		{
			if ($supplierValue["image"]) $getSupplier[$supplierKey]["image"] = $this->sSYSTEM->sPathSupplierImg.$supplierValue["image"];
			
			$query = array('sViewport'=>'cat', 'sCategory'=>$id, 'sPage'=>1,'sSupplier'=>$supplierValue["id"]);
			$getSupplier[$supplierKey]["link"] = Shopware()->Router()->assemble($query);
		}
		
		eval($this->sSYSTEM->sCallHookPoint("sArticles.php_sGetAffectedSuppliers_BeforeEnd"));
		return $getSupplier;
	}

	/**
	 * Article price calucation
	 * @param double $price 
	 * @param double $tax 
	 * @param array $article article data as an array
	 * @access public
	 * @return double $price formated price
	 */
	public function sCalculatingPrice($price,$tax,$article=0){
		eval($this->sSYSTEM->sCallHookPoint("sArticles.php_sCalculatingPrice_Start"));
		$price = (float) $price;
		$tax = (float) $tax;
		// Calculate global discount
		if ($this->sSYSTEM->sUSERGROUPDATA["mode"] && $this->sSYSTEM->sUSERGROUPDATA["discount"] && empty($article['liveshoppingID']) && empty($article['liveshoppingData'])){
			$price = $price - ($price / 100 * $this->sSYSTEM->sUSERGROUPDATA["discount"]);
		}
		if ($this->sSYSTEM->sCurrency["factor"]){
			$price = $price * floatval($this->sSYSTEM->sCurrency["factor"]);
		}
		// Condition Output-Netto AND NOT overwrite by customer-group
		// OR Output-Netto NOT SET AND tax-settings provided by customer-group
		if ((!$this->sSYSTEM->sUSERGROUPDATA["tax"] && $this->sSYSTEM->sUSERGROUPDATA["id"])){
			$price = $this->sFormatPrice($price);
		}else {
			$price = $this->sFormatPrice(round($price*(100+$tax)/100,3));
		}
		eval($this->sSYSTEM->sCallHookPoint("sArticles.php_sCalculatingPrice_BeforeEnd"));
		return $price;

	}

	 /**
	 * Article price calucation unformated return
	 * @param double $price 
	 * @param double $tax 
	 * @param bool $considerTax 
	 * @param bool $donotround
	 * @param array $article article data as an array
	 * @param bool $ignoreCurrency
	 * @access public
	 * @return double $price  price unformated
	 */
	public function sCalculatingPriceNum($price,$tax,$considerTax=false, $donotround=false,$article=0,$ignoreCurrency=false){
		eval($this->sSYSTEM->sCallHookPoint("sArticles.php_sCalculatingPriceNum_Start"));
		// Calculating global discount
		if ($this->sSYSTEM->sUSERGROUPDATA["mode"] && $this->sSYSTEM->sUSERGROUPDATA["discount"] && empty($article['liveshoppingID']) && empty($article['liveshoppingData'])){
			//echo "Price before $price <br />";
			$price = $price - ($price / 100 * $this->sSYSTEM->sUSERGROUPDATA["discount"]);
			//echo "Price After $price <br />";
		}
		
		if (!empty($this->sSYSTEM->sCurrency["factor"]) && $ignoreCurrency == false){
			$price = floatval($price) * floatval($this->sSYSTEM->sCurrency["factor"]);
		}
		
		// Show brutto or netto ?
		// Condition Output-Netto AND NOT overwrite by customer-group
		// OR Output-Netto NOT SET AND tax-settings provided by customer-group
		if ($donotround){
			if ((!$this->sSYSTEM->sUSERGROUPDATA["tax"] && $this->sSYSTEM->sUSERGROUPDATA["id"])){
			
			}else {
				$price = $price*(100+$tax)/100;
			} 
		}else {
			if ((!$this->sSYSTEM->sUSERGROUPDATA["tax"] && $this->sSYSTEM->sUSERGROUPDATA["id"])){
				$price = round($price,3);
			}else {
				$price = round($price*(100+$tax)/100,3);
			}
		}
		eval($this->sSYSTEM->sCallHookPoint("sArticles.php_sCalculatingPriceNum_BeforeEnd"));
		return $price;

	}
	/**
	 * Get article topsellers for a specific category
	 * @param $category int category id
	 * @access public
	 * @return array 
	 */
	public function sGetArticleCharts ($category=null)
	{
		$sLimitChart = $this->sSYSTEM->sCONFIG['sCHARTRANGE'];
		$sIntervalCharts = $this->sSYSTEM->sCONFIG['sCHARTINTERVAL'] ? $this->sSYSTEM->sCONFIG['sCHARTINTERVAL'] : 10;

		if(!empty($category))
		{
			$category = (int) $category;
		}
		elseif(!empty($this->sSYSTEM->_GET['sCategory']))
		{
			$category = (int) $this->sSYSTEM->_GET['sCategory'];
		}
		else
		{
			$category = (int) $this->sSYSTEM->sLanguageData[$this->sSYSTEM->sLanguage]["parentID"];
		}

		if (!empty($category)){
			$categorySQL = "AND aCategories.categoryID=$category AND aCategories.articleID=a.id";
			$categorySelect = "s_articles_categories AS aCategories,";
		} else {
			$categorySelect = "";
			$categorySQL = "";
		}

		$sql = "
			SELECT a.id AS articleID, SUM(IFNULL(od.quantity, 0))+pseudosales AS quantity
			FROM $categorySelect s_articles a
			LEFT JOIN s_order_details od
			ON a.id = od.articleID
			AND od.modus = 0
			
			LEFT JOIN s_order o
			ON o.ordertime>=DATE_SUB(NOW(),INTERVAL $sIntervalCharts DAY)
			AND o.status >= 0
			AND o.id = od.orderID
			WHERE a.active = 1 AND a.mode = 0
			AND (
				SELECT articleID 
				FROM s_articles_avoid_customergroups 
				WHERE articleID = a.id AND customergroupID = ".$this->sSYSTEM->sUSERGROUPDATA["id"]."
			) IS NULL
			$categorySQL
			GROUP BY a.id
			ORDER BY quantity DESC
			LIMIT $sLimitChart			
		";
		eval($this->sSYSTEM->sCallHookPoint("sArticles.php_sGetArticleCharts_AfterSQL"));
		$queryChart = $this->sSYSTEM->sDB_CONNECTION->CacheGetAssoc(33200,$sql);

		$articles = array();
		if(!empty($queryChart))
		foreach ($queryChart as $articleID=>$quantity)
		{
			$article = $this->sSYSTEM->sMODULES['sArticles']->sGetPromotionById ('fix', 0, (int)$articleID);
			if (!empty($article["articleID"]))
			{
				$article['quantity'] = $quantity;
				$articles[] = $article;
			}
		}
		return $articles;
	}
	
	/**
	 * Check if an article has instant download
	 * @param int $id s_articles.id
	 * @param int $detailsID s_articles_details.id
	 * @param bool $realtime deprecated
	 * @access public
	 * @return bool
	 */
	function sCheckIfEsd($id,$detailsID,$realtime=false){
		// Check if this article is esd-only (check in variants, too -> later)
		
		if ($detailsID){
			$sqlGetEsd = "
			SELECT id, serials FROM s_articles_esd WHERE articleID=$id
			AND articledetailsID=$detailsID
			";
		}else {
			$sqlGetEsd = "
			SELECT id, serials FROM s_articles_esd WHERE articleID=$id
			";
		}
		
		$getEsd = $this->sSYSTEM->sDB_CONNECTION->CacheGetRow($realtime == true ? 0 : $this->sSYSTEM->sCONFIG['sCACHEARTICLE'],$sqlGetEsd);
		if (!empty($getEsd["id"])){
			return true;
		}else {
			return false;
		}
	}
	
    /**
	 * Read the id from all articles that are in the same category as the article specified by parameter (For article navigation in top of detailpage)
	 * @param int $article s_articles.id
	 * @access public
	 * @return array 
	 */
	public function sGetAllArticlesInCategory($article)
	{
	
		$article = intval($article);
		$this->sSYSTEM->_GET['sCategory'] = intval($this->sSYSTEM->_GET['sCategory']);
		// If no category, left
		eval($this->sSYSTEM->sCallHookPoint("sArticles.php_sGetAllArticlesInCategory_Start"));
		if (!$this->sSYSTEM->_GET['sCategory']) return;
		
		if (!empty($this->sSYSTEM->_POST['sSort'])){
			$this->sSYSTEM->_SESSION['sSort'] = $this->sSYSTEM->_POST['sSort'];
		}
		
		if (!empty($this->sSYSTEM->_SESSION['sSort'])){
			$this->sSYSTEM->_POST['sSort'] = $this->sSYSTEM->_SESSION['sSort'];
		}

		switch ($this->sSYSTEM->_POST['sSort']){
			case 1:
				$orderBy  = "a.datum DESC, a.changetime DESC, a.id";
				break;
			case 2:
				$orderBy  = "aDetails.sales DESC, aDetails.impressions DESC, a.id";
				break;
			case 3:
				$orderBy  = "price ASC, a.id";
				break;	
			case 4:
				$orderBy  =  "price DESC, a.id";
				break;
			case 5:
				$orderBy  =  "articleName ASC, a.id";
				break;
			case 6:
				$orderBy  =  "articleName DESC, a.id";
				break;
			default:
				$orderBy  = $this->sSYSTEM->sCONFIG['sORDERBYDEFAULT'].', a.id';
		}
		
		if(strpos($orderBy,'price')!==false)
		{
			$select_price = "
				IFNULL((
					SELECT IFNULL(gp.price,gp2.price) as min_price
					FROM s_articles_groups_value v
					
					LEFT JOIN s_articles_groups_settings s
					ON s.articleID=v.articleID
					
					LEFT JOIN s_articles_groups_prices gp
					ON gp.valueID=v.valueID
					AND gp.groupkey='{$this->sSYSTEM->sUSERGROUP}'
					AND gp.price!=0
								
					LEFT JOIN s_articles_groups_prices gp2
					ON gp2.valueID=v.valueID
					AND gp2.groupkey='EK'
					AND gp2.price!=0
					
					WHERE v.active=1
					AND (s.instock IS NULL OR s.instock!=1 OR v.instock>0)
					AND IFNULL(gp.price,gp2.price) IS NOT NULL
					AND v.articleID=a.id
					
					ORDER BY min_price
					LIMIT 1
				),(
					SELECT IFNULL(p.price,p2.price) as min_price
					FROM s_articles_details d
					
					LEFT JOIN s_articles_prices p
					ON p.articleDetailsID=d.id
					AND p.pricegroup='{$this->sSYSTEM->sUSERGROUP}'
					AND p.to='beliebig'
					
					LEFT JOIN s_articles_prices p2
					ON p2.articledetailsID=d.id
					AND p2.pricegroup='EK'
					AND p2.to='beliebig'
					
					WHERE d.articleID=a.id
					
					ORDER BY min_price
					LIMIT 1
				))
			";
		}
		else
		{
			$select_price = '0';
		}
	
		
		$sql = "
			SELECT a.id, name AS articleName,
				($select_price*100/100-IFNULL(cd.discount,0)) as price
			FROM 
				s_articles_categories ac
			INNER JOIN s_articles a
			
			INNER JOIN s_articles_details AS aDetails
			ON aDetails.articleID=a.id AND aDetails.kind=1
			INNER JOIN s_articles_attributes AS aAttributes
			ON aAttributes.articledetailsID = aDetails.id
			LEFT JOIN s_core_customergroups cg
			ON cg.groupkey = '{$this->sSYSTEM->sUSERGROUP}'
			
			LEFT JOIN s_core_pricegroups_discounts cd
			ON a.pricegroupActive=1
			AND cd.groupID=a.pricegroupID
			AND cd.customergroupID=cg.id
			AND cd.discountstart=(SELECT MAX(discountstart) FROM s_core_pricegroups_discounts WHERE groupID=a.pricegroupID AND cd.customergroupID=cg.id)
	
			WHERE ac.articleID=a.id
			AND ac.categoryID={$this->sSYSTEM->_GET['sCategory']}
			AND a.active=1
			AND (
				SELECT articleID 
				FROM s_articles_avoid_customergroups 
				WHERE articleID = a.id AND customergroupID = ".$this->sSYSTEM->sUSERGROUPDATA["id"]."
			) IS NULL
			GROUP BY a.id
			ORDER BY  $orderBy
		";
		
		eval($this->sSYSTEM->sCallHookPoint("sArticles.php_sGetAllArticlesInCategory_AfterSQL"));
		
		$getAllArticles = $this->sSYSTEM->sDB_CONNECTION->CacheGetAll($this->sSYSTEM->sCONFIG['sCACHECATEGORY'],$sql,false,"category_".$this->sSYSTEM->_GET["sCategory"]);
		
		// Get articles position and previous, next article
		
		if(!empty($getAllArticles))
		foreach ($getAllArticles as $allArticlesKey => $allArticlesValue)
		{
			eval($this->sSYSTEM->sCallHookPoint("sArticles.php_sGetAllArticlesInCategory_LoopStart"));
			$i++;
			if ($allArticlesValue["id"]==$article){
				if ($getAllArticles[$allArticlesKey-1]["id"]){
					// Previous article
					$sNavigation["sPrevious"]["id"] = $getAllArticles[$allArticlesKey-1]["id"];
					$sNavigation["sPrevious"]["link"] = $this->sSYSTEM->sCONFIG['sBASEFILE']."?sViewport=detail&sDetails=".$sNavigation["sPrevious"]["id"]."&sCategory=".$this->sSYSTEM->_GET["sCategory"];
					$sNavigation["sPrevious"]["name"] = $getAllArticles[$allArticlesKey-1]["articleName"];
				
				}
				if ($getAllArticles[$allArticlesKey+1]["id"]){
					// Next article
					$sNavigation["sNext"]["id"] = $getAllArticles[$allArticlesKey+1]["id"];
					$sNavigation["sNext"]["link"] = $this->sSYSTEM->sCONFIG['sBASEFILE']."?sViewport=detail&sDetails=".$sNavigation["sNext"]["id"]."&sCategory=".$this->sSYSTEM->_GET["sCategory"];
					$sNavigation["sNext"]["name"] = $getAllArticles[$allArticlesKey+1]["articleName"];
				}
				$sNavigation["sCurrent"]["position"] = $i;
				$sNavigation["sCurrent"]["count"] = count($getAllArticles);
				$sNavigation["sCurrent"]["sCategory"] = $this->sSYSTEM->_GET["sCategory"];
				$sNavigation["sCurrent"]["sCategoryLink"] = $this->sSYSTEM->sCONFIG['sBASEFILE']."?sViewport=cat&sCategory=".$this->sSYSTEM->_GET["sCategory"];
				$getCategoryName = $this->sSYSTEM->sMODULES["sCategories"]->sGetCategoryContent($this->sSYSTEM->_GET["sCategory"]);
				$sNavigation["sCurrent"]["sCategoryName"] = $getCategoryName["description"];
			}
			eval($this->sSYSTEM->sCallHookPoint("sArticles.php_sGetAllArticlesInCategory_LoopEnd"));
		}
		
		eval($this->sSYSTEM->sCallHookPoint("sArticles.php_sGetAllArticlesInCategory_BeforeEnd"));
		return $sNavigation;
		
	}
	
	/**
	 * Get all associated articles from a certain article (relates to multidimensional variants / article configurator)
	 * @param int $id s_articles.id
	 * @access public
	 * @return array 
	 */
	public function sGetArticleAccessories ($id){
		$fetchGroups = $this->sSYSTEM->sDB_CONNECTION->CacheGetAssoc($this->sSYSTEM->sCONFIG['sCACHEARTICLE'],"
			SELECT groupID as id, groupID, groupname, groupdescription, groupimage FROM s_articles_groups_accessories WHERE articleID=$id ORDER BY groupname ASC
		");

		if(empty($fetchGroups))
			return false;

		foreach ($fetchGroups as $key => $configGroup)
		{
			$fetchOptions = $this->sSYSTEM->sDB_CONNECTION->CacheGetAll($this->sSYSTEM->sCONFIG['sCACHEARTICLE'],"
				SELECT optionID, optionname, ordernumber FROM s_articles_groups_accessories_option
				WHERE articleID=$id AND groupID={$configGroup["groupID"]} ORDER BY optionID ASC
				");
			if (empty($fetchOptions)){
				unset($fetchGroups[$key]);
			} else {
				foreach ($fetchOptions as $fetchOptionKey => $fetchOptionValue){
					$article = $this->sGetPromotionById("fix",0,$fetchOptionValue["ordernumber"]);
					if (!$article["price"]){
						unset($fetchOptions[$fetchOptionKey]);
					}else {
						$fetchOptions[$fetchOptionKey]["price"] = $article["price"];
						$fetchOptions[$fetchOptionKey]["sArticle"] = $article;
					}

				}
				$fetchGroups[$key]["childs"] = $fetchOptions;
			}
		}
		
		/*/
		 *   get translation for groups
		/*/
		if ($this->sSYSTEM->sLanguageData[$this->sSYSTEM->sLanguage]["isocode"] != "de"){
			$sql = 'SELECT objectdata FROM s_core_translations WHERE objecttype=? AND objectkey=? AND objectlanguage=?';
			$data = array('accessorygroup', $id, $this->sSYSTEM->sLanguageData[$this->sSYSTEM->sLanguage]["isocode"]);
			$getGroupTranslations = $this->sSYSTEM->sDB_CONNECTION->CacheGetOne($this->sSYSTEM->sCONFIG['sCACHEARTICLE'],$sql,$data);
			if(!empty($getGroupTranslations))
				$getGroupTranslations = unserialize($getGroupTranslations);
			if (!empty($getGroupTranslations)&&is_array($getGroupTranslations))
			{
				foreach ($fetchGroups as $fetchGroupKey => $fetchGroupValue)
				{
					if ($getGroupTranslations[$fetchGroupValue["groupID"]]){
						if ($getGroupTranslations[$fetchGroupValue["groupID"]]["accessoryName"]){
							$fetchGroups[$fetchGroupKey]["groupname"] = $getGroupTranslations[$fetchGroupValue["groupID"]]["accessoryName"];
						}
						if ($getGroupTranslations[$fetchGroupValue["groupID"]]["accessoryDescription"]){
							$fetchGroups[$fetchGroupKey]["groupdescription"] = $getGroupTranslations[$fetchGroupValue["groupID"]]["accessoryDescription"];
						}
					}
				}
			}
		}
		/*/
		 *   get translation for options
		/*/
		if ($this->sSYSTEM->sLanguageData[$this->sSYSTEM->sLanguage]["isocode"] != "de"){
			$sql = "SELECT objectdata FROM s_core_translations WHERE objecttype='accessoryoption' AND objectkey=$id AND objectlanguage='".$this->sSYSTEM->sLanguageData[$this->sSYSTEM->sLanguage]["isocode"]."'";
			$getOptionTranslations =  $this->sSYSTEM->sDB_CONNECTION->CacheGetOne($this->sSYSTEM->sCONFIG['sCACHEARTICLE'],$sql);
			
			if(!empty($getOptionTranslations))
				$getOptionTranslations = unserialize($getOptionTranslations);
			if(!empty($getOptionTranslations))
			foreach ($fetchGroups as $key => &$configGroup)
			{
				foreach ($configGroup["childs"] as $fetchGroupValuesKey => $fetchGroupValuesValue){
					if ($getOptionTranslations[$fetchGroupValuesValue["optionID"]]){
						if ($getOptionTranslations[$fetchGroupValuesValue["optionID"]]["accessoryoption"]){
							$configGroup["childs"][$fetchGroupValuesKey]["optionname"] = $getOptionTranslations[$fetchGroupValuesValue["optionID"]]["accessoryoption"];
						}
					}
				}	
			}
		}
		eval($this->sSYSTEM->sCallHookPoint("sArticles.php_sGetArticleAccessories_BeforeEnd"));
		return $fetchGroups;
	}
	
    /**
	 * Clear and refill the article translation table, needed by fuzzy search
	 * @access public
	 * @return void 
	 */
    public function sCreateTranslationTable ()
    {
    	$current_time = $this->sSYSTEM->sDB_CONNECTION->GetOne('SELECT NOW() as `current_time`');
    	$cached_time = $this->sSYSTEM->sDB_CONNECTION->CacheGetOne((int) $this->sSYSTEM->sCONFIG['sCACHETRANSLATIONTABLE'], 'SELECT NOW() as cached_time');
    	if(strtotime($cached_time)<strtotime($current_time))
    	{
    		return true;
    	}
		
    	$sql = "
			SELECT
				IFNULL(ct.objectdata, ct2.objectdata) as data,
				IFNULL(ct.objectkey, ct2.objectkey) as articleID,
				cm.id as languageID
			FROM s_core_multilanguage cm 
			LEFT JOIN s_core_translations ct
			ON ct.objectlanguage=cm.isocode
			AND ct.objecttype = 'article'
			LEFT JOIN s_core_translations ct2
			ON ct2.objectlanguage=cm.fallback
			AND ct2.objecttype = 'article'
			WHERE ct.id IS NOT NULL
			OR ct2.id IS NOT NULL
		";
    	$result = Shopware()->Db()->query($sql);
    	if($result===false)
    		return false;

    	$values = array();
    	for ($i=1,$c=$result->rowCount();$row = $result->fetch();$i++)
		{
    		$data = unserialize($row['data']);
    		$articleID = (int) $row['articleID'];
    		$languageID = (int) $row['languageID'];
    		if(empty($data)||!is_array($data)||empty($articleID)||empty($languageID))
    		{
    			continue;
    		}
    		$values[] = implode(',', array(
	    		$articleID,
	    		$languageID,
	    		$this->sSYSTEM->sDB_CONNECTION->qstr(isset($data['txtArtikel']) ? (string) $data['txtArtikel'] : ''),
	    		$this->sSYSTEM->sDB_CONNECTION->qstr(isset($data['txtkeywords']) ? (string) $data['txtkeywords'] : ''),
	    		$this->sSYSTEM->sDB_CONNECTION->qstr(isset($data['txtshortdescription']) ? (string) $data['txtshortdescription'] : ''),
	    		$this->sSYSTEM->sDB_CONNECTION->qstr(isset($data['txtlangbeschreibung']) ? (string) $data['txtlangbeschreibung'] : ''),
    		));
    		if($i==$c||count($values)>5000)
			{
				$sql_values = '
					REPLACE INTO `s_articles_translations` (
						articleID, languageID, name, keywords, description, description_long
		 			)  VALUES
		 		';
				$sql_values .= ' ('.implode('), (',$values).')';
				$this->sSYSTEM->sDB_CONNECTION->Execute($sql_values);
				$values = array();
			}
    	}
    	
    	$sql = "
    		DELETE at FROM s_articles_translations at
			LEFT JOIN s_core_multilanguage cm
			ON  cm.id=at.languageID
			LEFT JOIN s_core_translations ct
			ON ct.objectkey=CONVERT(at.articleID USING latin1)
			AND ct.objectlanguage=cm.isocode
			AND ct.objecttype='article'
			LEFT JOIN s_core_translations ct2
			ON ct2.objectkey=CONVERT(at.articleID USING latin1)
			AND ct2.objectlanguage=cm.fallback
			AND ct2.objecttype='article'
			WHERE ct.id IS NULL AND ct2.id IS NULL
		";
    	$result = $this->sSYSTEM->sDB_CONNECTION->Execute($sql);
    	if($result===false)
    		return false;
    	
    	eval($this->sSYSTEM->sCallHookPoint("sArticles.php_sCreateTranslationTable_BeforeEnd"));
    	return true;
    }

	/**
	 * Get translations for multidimensional groups and options for a certain article
	 * @param int $id - s_articles.id
	 * @access public
	 * @return array 
	 */
	public function sGetArticleConfigTranslation ($id)
	{
		if ($this->sSYSTEM->sLanguageData[$this->sSYSTEM->sLanguage]["isocode"] == "de"){
			return array();
		}
		$sql = 'SELECT objectdata FROM s_core_translations WHERE objecttype=? AND objectkey=? AND objectlanguage=?';
		$data = array('configuratorgroup', $id, $this->sSYSTEM->sLanguageData[$this->sSYSTEM->sLanguage]["isocode"]);
		$getGroupTranslations = $this->sSYSTEM->sDB_CONNECTION->CacheGetOne($this->sSYSTEM->sCONFIG['sCACHEARTICLE'],$sql,$data);
		$getGroupTranslations = unserialize($getGroupTranslations);
		
		$sql = 'SELECT objectdata FROM s_core_translations WHERE objecttype=? AND objectkey=? AND objectlanguage=?';
		$data = array('configuratoroption', $id, $this->sSYSTEM->sLanguageData[$this->sSYSTEM->sLanguage]["isocode"]);
		$getOptionTranslations =  $this->sSYSTEM->sDB_CONNECTION->CacheGetOne($this->sSYSTEM->sCONFIG['sCACHEARTICLE'],$sql,$data);
		$getOptionTranslations = unserialize($getOptionTranslations);
		
		return array("options"=>$getOptionTranslations,"groups"=>$getGroupTranslations);
		
	}
	
	/**
	 * Get all multidimensional groups, options and properties for a certain article
	 * @param int $id - s_articles.id
	 * @param array $article - copy of the array object, will be filled with the configurator data and returned
	 * @access public
	 * @return array $article
	 */
	public function sGetArticleConfig ($id, $article)
	{
		return $this->sSYSTEM->sMODULES['sConfigurator']->sGetArticleConfig ($id, $article);
	}
		

	/**
	 * Checks if a certain article is multidimensional configurable
	 * @param int $id s_articles.id
	 * @param bool $realtime deprecated
	 * @access public
	 * @return bool 
	 */
	public function sCheckIfConfig ($id,$realtime = false){
		$fetchGroups = $this->sSYSTEM->sDB_CONNECTION->CacheGetAll($realtime == true ? 0 : $this->sSYSTEM->sCONFIG['sCACHEARTICLE'],"
		SELECT groupID, groupname, groupdescription, groupimage FROM s_articles_groups WHERE articleID=$id ORDER BY groupname DESC
		",false,"article_$id");
		
		if (count($fetchGroups)){
			return true;
		}else {
			return false;
		}
	}
	
	
	/**
	 * Read the unit types from a certain article
	 * @param int $id s_articles.id
	 * @access public
	 * @return array 
	 */
	public function sGetUnit($id){
		$unit = $this->sSYSTEM->sDB_CONNECTION->CacheGetRow($this->sSYSTEM->sCONFIG['sCACHEARTICLE'],"
		SELECT unit, description FROM s_core_units WHERE id=?
		",array($id));
		
		if (!empty($unit["unit"])){
			// Check for possible translation
			if ($this->sSYSTEM->sLanguageData[$this->sSYSTEM->sLanguage]["isocode"]!="de"){
				$sql = "
				SELECT objectdata FROM s_core_translations WHERE objecttype='config_units' AND objectlanguage='".$this->sSYSTEM->sLanguageData[$this->sSYSTEM->sLanguage]["isocode"]."'";
				
				$getTranslation = $this->sSYSTEM->sDB_CONNECTION->CacheGetRow($this->sSYSTEM->sCONFIG['sCACHECOUNTRIES'],$sql);
			
			if ($getTranslation["objectdata"]){
				$object = unserialize($getTranslation["objectdata"]);
				// Pass (possible) translation to country
				if ($object[$id]["description"]){
					$unit["description"] = $object[$id]["description"];
				}
				if ($object[$id]["unit"]){
					$unit["unit"] = $object[$id]["unit"];
				}
			}
			}
			
		}
		//print_r($unit);exit;
		return $unit;
	}
	
	
	/**
	 * Get discounts and discount table for a certain article
	 * @param string $customergroup id of customergroup key
	 * @param string $groupID customer group id
	 * @param float $listprice default price
	 * @param int $quantity 
	 * @param bool $doMatrix Return array with all block prices
	 * @param array $articleData current article
	 * @param bool $ignore deprecated
	 * @access public
	 * @return array 
	 */
	public function sGetPricegroupDiscount($customergroup,$groupID,$listprice,$quantity,$doMatrix=true,$articleData=array(),$ignore=false){
		
		if (!empty($this->sSYSTEM->sUSERGROUPDATA["groupkey"])){
			$customergroup = $this->sSYSTEM->sUSERGROUPDATA["groupkey"];
			
		}
		if (!$customergroup || !$groupID) return false;
		
		$sql = "
		SELECT s_core_pricegroups_discounts.discount AS discount,discountstart 
		FROM 
			s_core_pricegroups_discounts,
			s_core_customergroups AS scc
		WHERE  
			groupID=$groupID AND customergroupID = scc.id
		AND
			scc.groupkey = '$customergroup'
		GROUP BY discount
		ORDER BY discountstart ASC
		";
		eval($this->sSYSTEM->sCallHookPoint("sArticles.php_sGetPricegroupDiscount_Start"));
		$getGroups = $this->sSYSTEM->sDB_CONNECTION->CacheGetAll($this->sSYSTEM->sCONFIG['sCACHEARTICLE'],$sql);
		
		if (count($getGroups)){
			foreach ($getGroups as $group){
				$priceMatrix[$group["discountstart"]] = array("percent"=>$group["discount"]);
				if (!empty($group["discount"])) $discountsFounds = true;
			}

			if (empty($discountsFounds)){
				if (empty($doMatrix)){
					
					return $listprice;
				}else {
					return;
				}
			}
			
			if (!empty($doMatrix) && count($priceMatrix)==1){
				return;
			}
			
			if (empty($doMatrix)){
				// Getting price rule matching to quantity
				foreach ($priceMatrix as $start => $percent){
					if ($start<=$quantity){
						$matchingPercent = $percent["percent"];
					}
				}
				
				if ($matchingPercent){
					//echo "Percent discount via pricegroup $groupID - $matchingPercent Discount\n";
					eval($this->sSYSTEM->sCallHookPoint("sArticles.php_sGetPricegroupDiscount_BeforeEnd1"));
					$val = ($listprice / 100 * (100-$matchingPercent));
					
					return ($listprice / 100 * (100-$matchingPercent));
				}
			}else {
				$i = 0;
				// Building price-ranges
				foreach ($priceMatrix as $start => $percent){
					$to = $start-1;
					if ($laststart && $to) $priceMatrix[$laststart]["to"] = $to;
					$laststart = $start;
				}
				
				foreach ($priceMatrix as $start => $percent){
					
					$getBlockPricings[$i]["from"] = $start;
					$getBlockPricings[$i]["to"] = $percent["to"];
					if ($i==0 && $ignore){
					
						$getBlockPricings[$i]["price"] = $this->sCalculatingPrice(($listprice / 100 * (100)),$articleData["tax"],$articleData);
						$divPercent = $percent["percent"];
					}else {
						if ($ignore) $percent["percent"]-=$divPercent;
						$getBlockPricings[$i]["price"] = $this->sCalculatingPrice(($listprice / 100 * (100-$percent["percent"])),$articleData["tax"],$articleData);
					}
					$i++;
							
				}
				eval($this->sSYSTEM->sCallHookPoint("sArticles.php_sGetPricegroupDiscount_BeforeEnd2"));
				
				return $getBlockPricings;
			}
		}
		if (!empty($doMatrix)){
			eval($this->sSYSTEM->sCallHookPoint("sArticles.php_sGetPricegroupDiscount_BeforeEnd3"));
			return;
		}else {
			eval($this->sSYSTEM->sCallHookPoint("sArticles.php_sGetPricegroupDiscount_BeforeEnd4"));
			return $listprice;
		}
	}
	
	/**
	 * Get the cheapest price for a certain article
	 * @param int $article id
	 * @param int $group customer group id
	 * @param int $pricegroup pricegroup id
	 * @param bool $usepricegroups consider pricegroups
	 * @access public
	 * @return float cheapest price or null
	 */
	public function sGetCheapestPrice($article,$group,$pricegroup,$usepricegroups,$realtime=false,$returnArrayIfConfigurator=false,$checkLiveshopping=false){
		if ($group!=$this->sSYSTEM->sUSERGROUP){
			$fetchGroup = $group;
		}else {
			$fetchGroup = $this->sSYSTEM->sUSERGROUP;
		}
		eval($this->sSYSTEM->sCallHookPoint("sArticles.php_sGetCheapestPrice_Start"));
		
		if (empty($usepricegroups)){
		$sql = "
			SELECT price FROM s_articles_prices, s_articles_details WHERE 
			s_articles_details.id=s_articles_prices.articledetailsID AND
			pricegroup='$fetchGroup'
			AND s_articles_details.articleID=$article
			GROUP BY ROUND(price,2)
			ORDER BY price ASC
			LIMIT 2
		";
		}else {
			$sql = "
			SELECT price FROM s_articles_details 
			LEFT JOIN 
			s_articles_prices ON s_articles_details.id=s_articles_prices.articledetailsID AND
			pricegroup='$fetchGroup' AND s_articles_prices.from = '1'
			WHERE 
			s_articles_details.articleID=$article
			GROUP BY ROUND(price,2)
			ORDER BY price ASC
			LIMIT 2
			";			
		}
		
		eval($this->sSYSTEM->sCallHookPoint("sArticles.php_sGetCheapestPrice_AfterSQL"));
		$queryCheapestPrice = $this->sSYSTEM->sDB_CONNECTION->CacheGetAll($realtime==true ? 0 : $this->sSYSTEM->sCONFIG['sCACHEPRICES'],$sql,false,"article_$article");
		
		if (count($queryCheapestPrice)>1){
			$cheapestPrice = $queryCheapestPrice[0]["price"];
			if (empty($cheapestPrice)){
				// No Price for this customer-group fetch defaultprice
				$sql = "
				SELECT price FROM s_articles_details 
				LEFT JOIN 
				s_articles_prices ON s_articles_details.id=s_articles_prices.articledetailsID AND
				pricegroup='EK' AND s_articles_prices.from = '1'
				WHERE 
				s_articles_details.articleID=$article
				GROUP BY ROUND(price,2)
				ORDER BY price ASC
				LIMIT 2
				";
				eval($this->sSYSTEM->sCallHookPoint("sArticles.php_sGetCheapestPrice_AfterSQL2"));
				$queryCheapestPrice = $this->sSYSTEM->sDB_CONNECTION->CacheGetAll($realtime==true ? 0 : $this->sSYSTEM->sCONFIG['sCACHEPRICES'],$sql,false,"article_$article");
				if (count($queryCheapestPrice)>1){
					$cheapestPrice = $queryCheapestPrice[0]["price"];
				}else {
					$cheapestPrice = 0;
					$basePrice = $queryCheapestPrice[0]["price"];
				}
			}
			$foundPrice = true;
		}else {
			$cheapestPrice = 0;
			$basePrice = $queryCheapestPrice[0]["price"];
		}
	
		$instockCheck = $this->sSYSTEM->sDB_CONNECTION->CacheGetRow($realtime==true ? 0 : $this->sSYSTEM->sCONFIG['sCACHEPRICES'],"
		SELECT articleID, instock,type FROM s_articles_groups_settings
		WHERE articleID = $article
		",false,"article_$article");
		
		if (!empty($instockCheck["articleID"]) && !empty($instockCheck["instock"])){
			$instock = "AND sv.instock>0";
		}else {
			$instock = "";
		}
		
		
		if ($instockCheck["type"]!=3){
			// Check for additional prices in article - configurator
			// Updated / Fixed 28.10.2008 - STH
			$sql = "
			SELECT sp.price AS price, (
			SELECT COUNT(sp2.price) FROM s_articles_groups_prices AS sp2 WHERE sp2.articleID = $article AND sp2.groupkey='$fetchGroup' AND sp2.price != 0 GROUP BY sp2.articleID
			) AS `count`
			FROM s_articles_groups_prices AS sp
			INNER JOIN s_articles_groups_value sv ON sv.articleID = sp.articleID AND sv.valueID = sp.valueID
			WHERE 
			sp.articleID=$article
			AND sp.groupkey='$fetchGroup' AND sp.price!=0 AND sv.active = 1 $instock GROUP BY ROUND(sp.price,2) ORDER BY price ASC LIMIT 2
			";
			
			eval($this->sSYSTEM->sCallHookPoint("sArticles.php_sGetCheapestPrice_AfterSQL3"));
			
			$queryCheapestPrice = $this->sSYSTEM->sDB_CONNECTION->CacheGetAll($realtime==true ? 0 : $this->sSYSTEM->sCONFIG['sCACHEPRICES'],$sql,false,"article_$article");
			if (!empty($queryCheapestPrice[0])){
				if ($queryCheapestPrice[0]["price"]>0 && (($queryCheapestPrice[0]["price"]<$cheapestPrice || $cheapestPrice<=0) && count($queryCheapestPrice)>=1)){
					$foundPrice = true;
					$cheapestPrice = $queryCheapestPrice[0]["price"];
				}
			}
		}else {
			// Bei Aufpreis-Konfiguratoren immer AB-Preis anzeigen
			$foundPrice = true;
			$cheapestPrice = $basePrice;
		}
			
		
		
		// Updated / Fixed 28.10.2008 - STH
		if (!empty($usepricegroups)){
			
			if (!empty($cheapestPrice)){
				
				$basePrice = $cheapestPrice;
			}else {
				$foundPrice = true;
			}
			
			$returnPrice = $this->sGetPricegroupDiscount($this->sSYSTEM->sUSERGROUP,$pricegroup,$basePrice,99999,false);
			
			if (!empty($returnPrice) && $foundPrice){	
				
				eval($this->sSYSTEM->sCallHookPoint("sArticles.php_sGetCheapestPrice_BeforeEnd1"));	
				$cheapestPrice = $returnPrice; 
			}elseif (!empty($foundPrice) && $returnPrice==0.00){
				eval($this->sSYSTEM->sCallHookPoint("sArticles.php_sGetCheapestPrice_BeforeEnd3"));	
				$cheapestPrice = "0.00";
			}else {
				eval($this->sSYSTEM->sCallHookPoint("sArticles.php_sGetCheapestPrice_BeforeEnd2"));	
				$cheapestPrice = "0";
			}
		}
		
		/**
		 * LIVE-SHOPPING - START
		 */
		$liveConf = $this->sGetLiveShopping('fix', 0, array('articleID'=>$article, 'price'=>$cheapestPrice));
		if (isset($liveConf['liveshoppingData'][0])) $liveConf['liveshoppingData'] = $liveConf['liveshoppingData'][0];
		if(!empty($liveConf['liveshoppingData']['net_price']))
		{
			if($liveConf['liveshoppingData']['net_price'] < $cheapestPrice)
				$cheapestPrice = $liveConf['liveshoppingData']['net_price'];
		}
		
		/**
		 * LIVE-SHOPPING - END
		 */
		
		
		eval($this->sSYSTEM->sCallHookPoint("sArticles.php_sGetCheapestPrice_BeforeEnd3"));	
		if (isset($queryCheapestPrice[0]) && $queryCheapestPrice[0]["count"]>1 && empty($queryCheapestPrice[1]["price"]) && !empty($returnArrayIfConfigurator)){
			return (array($cheapestPrice,$queryCheapestPrice[0]["count"]));
		}
		
		return $cheapestPrice;
	}
	/**
	 * Get one article with all available data
	 * @param int $id article id
	 * @access public
	 * @return array 
	 */
	public function sGetArticleById ($id = 0){
		
		eval($this->sSYSTEM->sCallHookPoint("sArticles.php_sGetArticleById_Start"));	
		
		//error_reporting(E_ALL);
		if ($id) {
			$this->sSYSTEM->_GET['sArticle'] = $id;
		}
		$this->sSYSTEM->_GET["sArticle"] = intval($this->sSYSTEM->_GET["sArticle"]);
		
		$isBlog = $this->sSYSTEM->sDB_CONNECTION->GetOne("
		SELECT mode FROM s_articles WHERE id = ?
		",array($this->sSYSTEM->_GET["sArticle"] ));
		
		
		
		eval($this->sSYSTEM->sCallHookPoint("sArticles.php_sGetArticleById_Start2"));	
		
		/* 
		Fetch article-data
		Advanced in Shopware 2.1
		Load data in any case, if no price is available for the current customergroup, load default price
		*/ 
		if (empty($isBlog)){
		$sql = "SELECT 
			a.id as articleID, 
			aDetails.id as articleDetailsID,
			TRIM(ordernumber) as ordernumber,
			datum,
			additionaltext, 
			shippingtime, 
			shippingfree,
			instock,
			minpurchase,
			notification,
			purchasesteps,
			maxpurchase,
			purchaseunit,
			referenceunit,
			packunit,
			weight,
			laststock,
			unitID,
			template,
			pricegroupID,
			pricegroupActive,
			releasedate,
			a.mode,
			a.description AS description,
			keywords,
			description_long, 
			aSupplier.name AS supplierName, 
			aSupplier.img AS supplierImg, 
			aSupplier.id AS supplierID, 
			a.name AS articleName,
			IFNULL(p.price,p2.price) as price,
			sales, 
			IF(p.pseudoprice,p.pseudoprice,p2.pseudoprice) as pseudoprice,
			IFNULL(p.pricegroup,p2.pricegroup) as pricegroup,
			tax,
			attr1,attr2,attr3,attr4,attr5,attr6,attr7,attr8,attr9,attr10,
			attr11,attr12,attr13,attr14,attr15,attr16,attr17,attr18,attr19,attr20,
			filtergroupID,
			a.crossbundlelook
			
			FROM s_articles AS a,
			s_articles_supplier AS aSupplier, 
			s_articles_details AS aDetails
			
			LEFT JOIN s_articles_prices AS p
			ON p.articledetailsID=aDetails.id
			AND p.pricegroup='".$this->sSYSTEM->sUSERGROUP."'
			AND p.from='1'
			
			LEFT JOIN s_articles_prices AS p2
			ON p2.articledetailsID=aDetails.id
			AND p2.pricegroup='EK'
			AND p2.from='1',
			
			s_core_tax AS aTax,
			s_articles_attributes AS aAttributes
			WHERE 
			a.taxID=aTax.id
			AND aAttributes.articledetailsID=aDetails.id
			AND a.id=".$this->sSYSTEM->_GET['sArticle']."
			AND aSupplier.id=a.supplierID AND aDetails.articleID=a.id AND aDetails.kind=1 AND a.active=1
			AND (
				SELECT articleID 
				FROM s_articles_avoid_customergroups 
				WHERE articleID = a.id AND customergroupID = ".$this->sSYSTEM->sUSERGROUPDATA["id"]."
			) IS NULL
		"; 
		}else {
			$sql = "SELECT 
			a.id as articleID, 
			aDetails.id as articleDetailsID,
			ordernumber,
			datum,
			additionaltext, 
			shippingtime, 
			shippingfree,
			DATE_FORMAT(changetime,'%d.%M %Y %H:%i') AS datumFormated,
			changetime,
			instock,
			minpurchase,
			notification,
			purchasesteps,
			maxpurchase,
			purchaseunit,
			referenceunit,
			packunit,
			weight,
			laststock,
			unitID,
			template,
			pricegroupID,
			pricegroupActive,
			releasedate,
			a.mode,
			a.description AS description,
			keywords,
			description_long, 
			aSupplier.name AS supplierName, 
			aSupplier.img AS supplierImg, 
			aSupplier.id AS supplierID, 
			a.name AS articleName,
			attr1,attr2,attr3,attr4,attr5,attr6,attr7,attr8,attr9,attr10,
			attr11,attr12,attr13,attr14,attr15,attr16,attr17,attr18,attr19,attr20,
			filtergroupID,
			a.crossbundlelook
			
			FROM s_articles AS a,
			s_articles_supplier AS aSupplier, 
			s_articles_details AS aDetails,
			s_articles_attributes AS aAttributes
			WHERE 
			aAttributes.articledetailsID=aDetails.id
			AND a.id=".$this->sSYSTEM->_GET['sArticle']."
			AND aSupplier.id=a.supplierID AND aDetails.articleID=a.id AND aDetails.kind=1 AND a.active=1
			AND (
				SELECT articleID 
				FROM s_articles_avoid_customergroups 
				WHERE articleID = a.id AND customergroupID = ".$this->sSYSTEM->sUSERGROUPDATA["id"]."
			) IS NULL
		"; 
			
		}
		
		if (!empty(Shopware()->Session()->Admin)){
			$sql = str_replace("AND a.active=1","",$sql);
		}
		
		$sql = Enlight()->Events()->filter('Shopware_Modules_Articles_GetArticleById_FilterSQL', $sql, array('subject'=>$this,'id'=>$this->sSYSTEM->_GET['sArticle'],'isBlog'=>$isBlog,'customergroup'=>$this->sSYSTEM->sUSERGROUP));
		
		$this->sSYSTEM->_SESSION["sLastArticle"] = $this->sSYSTEM->_GET['sArticle']; // r302 save last visited article
		eval($this->sSYSTEM->sCallHookPoint("sArticles.php_sGetArticleById_AfterSQL"));
		$getArticle = $this->sSYSTEM->sDB_CONNECTION->CacheGetRow($this->sSYSTEM->sCONFIG['sCACHEARTICLE'],$sql,false,"article_".$this->sSYSTEM->_GET["sArticle"]);
		
		//Bei Konfiguratorartikeln wird der Wert laststock 
		//mit dem Wert der s_articles_groups_settings überschrieben 
		$laststockConfig = " 
			SELECT IF(ags.instock IS NULL, 0, ags.instock) AS laststock 
			FROM `s_articles_groups_value` AS agv 
			
			LEFT JOIN `s_articles_groups_settings` AS ags 
			ON(ags.articleID = agv.articleID) 
			
			WHERE agv.`articleID` = ? 
			LIMIT 1 
		"; 
		$getLaststockConfig = $this->sSYSTEM->sDB_CONNECTION->GetRow($laststockConfig,array($getArticle['articleID'])); 
		if(!empty($getLaststockConfig)){ 
			$getArticle['laststock'] = $getLaststockConfig['laststock']; 
		} 		
		
		// Check instock in realtime
		if (!empty($this->sSYSTEM->sCONFIG["sLIVEINSTOCK"])){
			$getArticle["instock"] = $this->sSYSTEM->sDB_CONNECTION->GetOne("
			SELECT instock FROM s_articles_details WHERE id=?
			",array($getArticle["articleDetailsID"]));
		}
		eval($this->sSYSTEM->sCallHookPoint("sArticles.php_sGetArticleById_AfterQuery"));
			
		// Translate main - data
		$getArticle = $this->sGetTranslation($getArticle,$this->sSYSTEM->_GET['sArticle'],"article",$this->sSYSTEM->sLanguage);
		
		/*
		Calculating matching price SW 2.1
		*/
		if ($getArticle["pricegroupActive"]){
			$getArticle["priceBeforePriceGroup"] = $getArticle["price"];
			$getArticle["price"] = $this->sGetPricegroupDiscount($this->sSYSTEM->sUSERGROUP,$getArticle["pricegroupID"],$getArticle["price"],1,false);
		}
			
		// If the article could found
		if (count($getArticle) && $getArticle["articleID"]){
				$getArticle = Enlight()->Events()->filter('Shopware_Modules_Articles_GetArticleById_FilterArticle', $getArticle, array('subject'=>$this,'id'=>$this->sSYSTEM->_GET['sArticle'],'isBlog'=>$isBlog,'customergroup'=>$this->sSYSTEM->sUSERGROUP));
		
				// Grap related links
				$getRelatedLinks = $this->sSYSTEM->sDB_CONNECTION->CacheGetAll($this->sSYSTEM->sCONFIG['sCACHEARTICLE'],"
				SELECT id, description, link, target FROM s_articles_information
				WHERE articleID={$getArticle["articleID"]}
				",false,"article_".$getArticle["articleID"]);
				// Add 'http://' to link (if not set)
				if (isset($getRelatedLinks[0])){
					foreach ($getRelatedLinks as $linkKey => $linkValue){
						// Get possible link - translation
						$getRelatedLinks[$linkKey] = $this->sGetTranslation($linkValue,$linkValue["id"],"link",$this->sSYSTEM->sLanguage);
		
						if (!preg_match("/http/",$getRelatedLinks[$linkKey]["link"])){
							$getRelatedLinks[$linkKey]["link"] = "http://".$getRelatedLinks[$linkKey]["link"];
						}
						//$getRelatedLinks[$linkKey]["target"] = "_blank";	// Open external links in new brower window
						$getRelatedLinks[$linkKey]["supplierSearch"] = false;
					}
				}
				
				// Building link 'More articles from this supplier'
				// =================================================.
				$link = $this->sSYSTEM->sCONFIG['sBASEFILE']."?sViewport=search&sSearch=".$getArticle['supplierID']."&sSearchMode=supplier&sSearchText=".urlencode($getArticle['supplierName']);

				$getRelatedLinks[count($getRelatedLinks)] = array("supplierSearch"=>true,
				"description"=>$getArticle["supplierName"],
				"link"=>$link,"target"=>"_parent");

				$getArticle["sLinks"] = $getRelatedLinks;
				eval($this->sSYSTEM->sCallHookPoint("sArticles.php_sGetArticleById_AfterLinks"));
				
				if (function_exists("htmlspecialchars_decode")){
					$getArticle["description_long"] = htmlspecialchars_decode($getArticle["description_long"]);
				}
				$getArticle["articleName"] = $this->sOptimizeText($getArticle["articleName"]);
				
				// If the user doesn´t come from category-system, get related category
				// SHOPWARE 2.1 //
				// =================================================.
				  $sArticleID = intval($this->sSYSTEM->_GET['sArticle']);
				  $sCategoryID = intval($this->sSYSTEM->_GET['sCategory']);
				  if(empty($sCategoryID)||$sCategoryID==$this->sSYSTEM->sLanguageData[$this->sSYSTEM->sLanguage]["parentID"])
				  {
				  
				   $sCategoryParent = $this->sSYSTEM->sLanguageData[$this->sSYSTEM->sLanguage]["parentID"];
				   while (!empty($sCategoryParent))
				   {
				    $sCategoryID = $sCategoryParent;
				    $sql = "
				    SELECT c.id FROM s_articles_categories a, s_categories c
					WHERE a.articleID=$sArticleID 
					AND c.parent=$sCategoryParent 
					AND a.categoryID = c.id
					ORDER BY a.id ASC LIMIT 1
				    ";
				    $sCategoryParent = $this->sSYSTEM->sDB_CONNECTION->CacheGetOne($this->sSYSTEM->sCONFIG['sCACHEARTICLE'],$sql,false,"article_".$getArticle["articleID"]);
				   }
				   
				  
				   $this->sSYSTEM->_GET['sCategory'] = $sCategoryID;
				  }

				
				// Get article accessories 
				// =================================================.
				$getRelatedArticles = $this->sSYSTEM->sDB_CONNECTION->CacheGetAll($this->sSYSTEM->sCONFIG['sCACHEARTICLE'],"
				SELECT relatedarticle FROM s_articles_relationships WHERE articleID={$getArticle["articleID"]}
				",false,"article_".$getArticle["articleID"]);
				if (count($getRelatedArticles)){
					foreach ($getRelatedArticles as $relatedArticleKey => $relatedArticleValue){
							$tmpContainer = $this->sGetPromotionById("fix",0,$relatedArticleValue['relatedarticle']);
							
							if (count($tmpContainer) && isset($tmpContainer["articleName"])){
								$getArticle["sRelatedArticles"][] = $tmpContainer;
							}
					}
				}else {
					$getArticle["sRelatedArticles"] = array();
				}
				
				
				// Get similar articles
				// =================================================.
				$getSimilarArticles = $this->sSYSTEM->sDB_CONNECTION->CacheGetAll($this->sSYSTEM->sCONFIG['sCACHEARTICLE'],"
				SELECT relatedarticle FROM s_articles_similar WHERE articleID={$getArticle["articleID"]}",array(),"article_".$getArticle["articleID"]);
				if (count($getSimilarArticles)){
					foreach ($getSimilarArticles as $relatedArticleKey => $relatedArticleValue){
							$tmpContainer = $this->sGetPromotionById("fix",0,$relatedArticleValue['relatedarticle']);
							if (count($tmpContainer) && isset($tmpContainer["articleName"])){
								$getArticle["sSimilarArticles"][] = $tmpContainer;
							}
					}
				}else {
					
					$similarLimit = $this->sSYSTEM->sCONFIG['sSIMILARLIMIT'] ? $this->sSYSTEM->sCONFIG['sSIMILARLIMIT'] : 3;
					$sqlGetCategory = "
					SELECT DISTINCT s_articles.id AS relatedarticle FROM s_articles_categories, s_articles, s_articles_details 
					WHERE s_articles_categories.categoryID=".$this->sSYSTEM->_GET["sCategory"]."
					AND s_articles.id=s_articles_categories.articleID AND s_articles.id=s_articles_details.articleID
					AND s_articles_details.kind=1 
					AND s_articles.id!={$getArticle["articleID"]}
					AND s_articles.active=1
					ORDER BY s_articles_details.sales DESC LIMIT $similarLimit
					";
					
					$getSimilarArticles = $this->sSYSTEM->sDB_CONNECTION->CacheGetAll($this->sSYSTEM->sCONFIG['sCACHEARTICLE'],$sqlGetCategory,false,"article_".$getArticle["articleID"]);
					
					foreach ($getSimilarArticles as $relatedArticleKey => $relatedArticleValue){
							$tmpContainer = $this->sGetPromotionById("fix",0,$relatedArticleValue['relatedarticle']);
							if (count($tmpContainer) && isset($tmpContainer["articleName"])){
								$getArticle["sSimilarArticles"][] = $tmpContainer;
							}
					}
					
					if (!count($getSimilarArticles)){
						$getArticle["sSimilarArticles"] = array();
					}
				}
				
				eval($this->sSYSTEM->sCallHookPoint("sArticles.php_sGetArticleById_AfterCrossSelling"));
				
				// Check if available as esd-article
				$getArticle["esd"] = $this->sCheckIfEsd($getArticle["articleID"],$getArticle["articleDetailsID"]);
				
				// Get blockpricing 
				// =================================================.
				if ($getArticle["pricegroupActive"]){
					// SW 2.1 Pricegroups
					/*
					If prices were calculated via an active pricegroup - build discount matrix dynamicly
					*/
					$getArticle["sBlockPrices"] = $this->sGetPricegroupDiscount($this->sSYSTEM->sUSERGROUP,$getArticle["pricegroupID"],$getArticle["priceBeforePriceGroup"],1,true,$getArticle);
					
				}else {
					/*
					Load defined discount-rules
					--
					Check if prices for this customergroup are available
					*/
					if ($getArticle["pricegroup"]!=$this->sSYSTEM->sUSERGROUP){
						$sql = "
						SELECT `from` AS valFrom,`to` AS valTo, price, pseudoprice FROM s_articles_prices WHERE articledetailsID={$getArticle["articleDetailsID"]}
						AND (pricegroup='EK')
						ORDER BY id ASC
						";
					}else {
						$sql = "
						SELECT `from` AS valFrom,`to` AS valTo, price, pseudoprice FROM s_articles_prices WHERE articledetailsID={$getArticle["articleDetailsID"]}
						AND (pricegroup='".$this->sSYSTEM->sUSERGROUP."')
						ORDER BY id ASC
						";
					}
						
					$getBlockPricings =$this->sSYSTEM->sDB_CONNECTION->CacheGetAll($this->sSYSTEM->sCONFIG['sCACHEARTICLE'],$sql,false,"article_".$getArticle["articleID"]);
					// If more then one row, there are block-prices				
					if (count($getBlockPricings)>1){
						foreach ($getBlockPricings as $blockPriceKey => $blockPriceValue){
								$getBlockPricings[$blockPriceKey]["from"] = $blockPriceValue["valFrom"];
								$getBlockPricings[$blockPriceKey]["to"] = $blockPriceValue["valTo"];
								$getBlockPricings[$blockPriceKey]["price"] = $this->sCalculatingPrice($blockPriceValue["price"],$getArticle["tax"],$getArticle);
								$getBlockPricings[$blockPriceKey]["pseudoprice"] =  $this->sCalculatingPrice($blockPriceValue["pseudoprice"],$getArticle["tax"],$getArticle);
							
						}
						$getArticle["sBlockPrices"] = $getBlockPricings;
					} // block pricing
					else {
						$getArticle["sBlockPrices"] = array();
					}
				}
				
				eval($this->sSYSTEM->sCallHookPoint("sArticles.php_sGetArticleById_AfterBlockPrices"));
				// Get article variants (one-dimensional)
				// =================================================.
				$sql = "
				SELECT s_articles_details.id, s_articles_details.id AS articleDetailsID, ordernumber, suppliernumber, weight,additionaltext, price, pseudoprice,pricegroup,
				instock,
				attr1, attr2, attr3, attr4, attr5, attr6, attr7, attr8, attr9, attr10, attr11, attr12,attr13,attr14,attr15,attr16,attr17,attr19,attr20
				FROM s_articles_details
				LEFT JOIN s_articles_prices ON s_articles_details.id=s_articles_prices.articledetailsID
				AND (s_articles_prices.pricegroup='".$this->sSYSTEM->sUSERGROUP."')
				, s_articles_attributes
				WHERE
				s_articles_details.articleID={$getArticle["articleID"]} AND
				s_articles_attributes.articledetailsID=s_articles_details.id
				AND s_articles_details.kind=2  
				GROUP BY articleDetailsID
				ORDER BY s_articles_details.kind ASC, s_articles_details.position ASC
				";
				eval($this->sSYSTEM->sCallHookPoint("sArticles.php_sGetArticleById_AfterVariantSQL"));
				$sql = Enlight()->Events()->filter('Shopware_Modules_Articles_GetArticleById_FilterSqlVariants', $sql, array('subject'=>$this,'id'=>$this->sSYSTEM->_GET['sArticle'],'isBlog'=>$isBlog,'customergroup'=>$this->sSYSTEM->sUSERGROUP));
		
				$getArticleVariants =$this->sSYSTEM->sDB_CONNECTION->CacheGetAssoc($this->sSYSTEM->sCONFIG['sCACHEARTICLE'],$sql,false,"article_".$getArticle["articleID"]);
				
				// If there are variants, format there prices
				if (count($getArticleVariants)){
					$getArticleVariants = Enlight()->Events()->filter('Shopware_Modules_Articles_GetArticleById_FilterVariants', $getArticleVariants, array('subject'=>$this,'id'=>$this->sSYSTEM->_GET['sArticle'],'isBlog'=>$isBlog,'customergroup'=>$this->sSYSTEM->sUSERGROUP));
		
					foreach ($getArticleVariants as $variantKey => $variantValue){
						// Check instock in realtime
						if (!empty($this->sSYSTEM->sCONFIG["sLIVEINSTOCK"])){
							$getArticleVariants[$variantKey]["instock"] = $this->sSYSTEM->sDB_CONNECTION->GetOne("
							SELECT instock FROM s_articles_details WHERE id = {$variantValue["articleDetailsID"]}
							");
						}
						eval($this->sSYSTEM->sCallHookPoint("sArticles.php_sGetArticleById_VariantLoopStart"));
						// r302
						if (empty($getArticleVariants[$variantKey]["shippingtime"])){
							// Fix the bug, that shippingtime is only available for the first article
							$getArticleVariants[$variantKey]["shippingtime"] = $getArticle["shippingtime"];
						}
						if (empty($getArticleVariants[$variantKey]["shippingfree"])){
							// Fix the bug, that shippingtime is only available for the first article
							$getArticleVariants[$variantKey]["shippingfree"] = $getArticle["shippingfree"];
						}
						/*
						SW 2.1 // Pricegroups
						*/
						if (empty($getArticleVariants[$variantKey]["price"]) && empty($this->sSYSTEM->sCONFIG["sALLOWZEROPRICES"]) && !empty($getArticleVariants[$variantKey]["articleDetailsID"])){
							// Load default-price
							$getDefaultPrice = $this->sSYSTEM->sDB_CONNECTION->CacheGetRow($this->sSYSTEM->sCONFIG['sCACHEARTICLE'],
							"SELECT * FROM s_articles_prices AS aPrices WHERE
							aPrices.articleDetailsID={$getArticleVariants[$variantKey]["articleDetailsID"]}
							AND aPrices.pricegroup='EK' AND aPrices.from='1'
							",false,"article_".$getArticle["articleID"]);
							$getArticleVariants[$variantKey]["price"] = $getDefaultPrice["price"];
							$getArticleVariants[$variantKey]["pseudoprice"] = $getDefaultPrice["pseudoprice"];
							$getArticleVariants[$variantKey]["pricegroup"] = "EK";
						}
						$getArticleVariants[$variantKey] = $this->sGetTranslation($getArticleVariants[$variantKey],$variantKey,"variant",$this->sSYSTEM->sLanguage);
						
						if ($getArticle["pricegroupActive"]){
							$getArticleVariants[$variantKey]["priceBeforePriceGroup"] = $getArticleVariants[$variantKey]["price"];
							
							$getArticleVariants[$variantKey]["price"] = $this->sGetPricegroupDiscount($this->sSYSTEM->sUSERGROUP,$getArticle["pricegroupID"],$getArticleVariants[$variantKey]["price"],1,false);
							$getArticleVariants[$variantKey]["price"] = $this->sCalculatingPrice($getArticleVariants[$variantKey]["price"],$getArticle["tax"],$getArticleVariants[$variantKey]);
						}else {
							$getArticleVariants[$variantKey]["price"] = $this->sCalculatingPrice($getArticleVariants[$variantKey]["price"],$getArticle["tax"],$getArticleVariants[$variantKey]);
						}
						
						$getArticleVariants[$variantKey]["esd"] = $this->sCheckIfEsd($getArticle["articleID"],$getArticleVariants[$variantKey]["articleDetailsID"]);
						
						if ($getArticleVariants[$variantKey]["pseudoprice"]){
							$getArticleVariants[$variantKey]["pseudoprice"] = $this->sCalculatingPrice($getArticleVariants[$variantKey]["pseudoprice"],$getArticle["tax"],$getArticleVariants[$variantKey]);
							$discPseudo =  str_replace(",",".",$getArticleVariants[$variantKey]["pseudoprice"]);
							$discPrice = str_replace(",",".",$getArticleVariants[$variantKey]["price"]);
							if ($discPseudo>$discPrice){
								$discount = round(($discPrice / $discPseudo * 100) - 100,2)*-1;
								$getArticleVariants[$variantKey]["pseudopricePercent"] = array("int"=>round($discount,0),"float"=>$discount);
							}
						
						}
						$getArticleVariants[$variantKey]["linkBasket"] = $this->sSYSTEM->sCONFIG['sBASEFILE']."?sViewport=basket&sAdd=".$variantValue['ordernumber'];
						
						/**
						 * LIVE-SHOPPING - START - VARIANTEN
						 */
						
						$tmpArticleVar = $getArticleVariants[$variantKey];
						$tmpArticleVar['articleID'] = $getArticle['articleID'];
						$pseudoprice = $tmpArticleVar['price'];
						$tmpArticleVar = $this->sGetLiveShopping('fix', 0, $tmpArticleVar, false, '', '', 0);	
						
						if(isset($tmpArticleVar['liveshoppingData']))
						{
							
							$tmpArticleVar['price'] = $this->sFormatPrice($tmpArticleVar['price']);
							
							foreach ($tmpArticleVar['liveshoppingData'] as $key => $liveshopping) {
								//Überprüfung, ob eine Artikelbeschränkung vorliegt
								if(!empty($liveshopping['sLiveStints'])) {
									if(!in_array($getArticleVariants[$variantKey]['ordernumber'], $liveshopping['sLiveStints'])){
										unset($tmpArticleVar['liveshoppingData'][$key]);
									}else{
										$getArticleVariants[$variantKey]['pseudoprice'] = $pseudoprice;
									}
								}else{
									$getArticleVariants[$variantKey]['pseudoprice'] = $pseudoprice;
								}
							}
						}
						
						if(!empty($tmpArticleVar['liveshoppingData']))
						{
							foreach ($tmpArticleVar['liveshoppingData'] as $live) {
								$getArticleVariants[$variantKey]['liveshoppingData'] = $live;
								$getArticleVariants[$variantKey]['price'] = $getArticleVariants[$variantKey]['liveshoppingData']['price'];
								break;
							}
						}
						
						if ($getArticle["pricegroupActive"]){
							// SW 2.1 Pricegroups
							/*
							If prices were calculated via an active pricegroup - build discount matrix dynamicly
							*/
							$getArticleVariants[$variantKey]["sBlockPrices"] = $this->sGetPricegroupDiscount($getArticle["pricegroup"],$getArticle["pricegroupID"],$getArticleVariants[$variantKey]["priceBeforePriceGroup"],1,true,$getArticle);
						}else {
							// Check if the variant has block-pricing available
							// Get blockpricing 
							// =================================================
							if ($getArticleVariants[$variantKey]["pricegroup"]!=$this->sSYSTEM->sUSERGROUP){
								$sql = "
								SELECT `from` AS valFrom,`to` AS valTo, price, pseudoprice FROM s_articles_prices WHERE articledetailsID={$variantValue["articleDetailsID"]}
								AND (pricegroup='EK')
								ORDER BY id ASC
								";
							}else {
								$sql = "
								SELECT `from` AS valFrom,`to` AS valTo, price, pseudoprice FROM s_articles_prices WHERE articledetailsID={$variantValue["articleDetailsID"]}
								AND (pricegroup='".$this->sSYSTEM->sUSERGROUP."')
								ORDER BY id ASC
								";
							}
							
							$getBlockPricings = $this->sSYSTEM->sDB_CONNECTION->CacheGetAll($this->sSYSTEM->sCONFIG['sCACHEARTICLE'],$sql,false,"article_".$getArticle["articleID"]);
							
							$getArticleVariants[$variantKey]["esd"] = $this->sCheckIfEsd($getArticle["articleID"],$variantValue["articleDetailsID"]);
							
							// If more then one row, there are block-prices				
							if (count($getBlockPricings)>1){
								foreach ($getBlockPricings as $blockPriceKey => $blockPriceValue){
									
									// Set a limit (9999) to quantity, after this the article can be inquiried
									if (intval($blockPriceValue["valTo"])<9999 && intval($blockPriceValue["valFrom"])<10000){
										$getBlockPricings[$blockPriceKey]["from"] = $blockPriceValue["valFrom"];
										$getBlockPricings[$blockPriceKey]["to"] = $blockPriceValue["valTo"];
										$getBlockPricings[$blockPriceKey]["price"] = $this->sCalculatingPrice($blockPriceValue["price"],$getArticle["tax"],$getArticle);
										$getBlockPricings[$blockPriceKey]["pseudoprice"] =  $this->sCalculatingPrice($blockPriceValue["pseudoprice"],$getArticle["tax"],$getArticle);
									}else {
										if (intval($staffel["bis"])==9999){
											// Statical link to contact-form
											$getBlockPricings[$blockPriceKey]["from"] = $blockPriceValue["valFrom"];
											$getBlockPricings[$blockPriceKey]["to"] = 0;
											$getBlockPricings[$blockPriceKey]["noOrderAllowed"] = true;
										}
									}
								}
								$getArticleVariants[$variantKey]["sBlockPrices"] = $getBlockPricings;
								
							} // block pricing
							else {
								$getArticleVariants[$variantKey]["sBlockPrices"] = array();
							}
						} // Check for price groups
						// --- block prices
						$getArticleVariants[$variantKey]["linkNote"] = $this->sSYSTEM->sCONFIG['sBASEFILE']."?sViewport=note&sAdd=".$getArticleVariants[$variantKey]["ordernumber"];
						eval($this->sSYSTEM->sCallHookPoint("sArticles.php_sGetArticleById_VariantLoopEnd"));
						
					} // For every variant
				$getArticle["sVariants"] = $getArticleVariants;
				} // variants
				else {
					$getArticle["sVariants"] = array();
				}
			
				// Default-values
				if (!$getArticle["minpurchase"]) $getArticle["minpurchase"] = 1;
				
				if (!$getArticle["maxpurchase"]){
					
					$getArticle["maxpurchase"] = $this->sSYSTEM->sCONFIG['sMAXPURCHASE'];
				}else {
					
				}
				if (!$getArticle["purchasesteps"]) $getArticle["purchasesteps"] = 1;
				
				// Calculating price for reference-unit
				if ($getArticle["purchaseunit"] > 0 && $getArticle["referenceunit"]){
					if (!empty($getArticle["sBlockPrices"][0])){
						$price = str_replace(",",".",$getArticle["sBlockPrices"][0]["price"]);
						$tax = 0;
					}else {
						$price = $getArticle["price"];
						$tax = $getArticle["tax"];
					}
					$basePrice = $this->sCalculatingPriceNum($price,$tax,$getArticle);
					$basePrice = $basePrice / $getArticle["purchaseunit"] * $getArticle["referenceunit"];
					$basePrice = $this->sFormatPrice($basePrice);
					$getArticle["referenceprice"] = $basePrice;
				}else {
					unset ($getArticle["purchaseunit"]);
				}
				// ---
				
				// Read unit if set
				if ($getArticle["unitID"]){
				
					$getArticle["sUnit"] = $this->sGetUnit($getArticle["unitID"]);
				}

				// Check release-date
				// =================================================.
				$tmpDate = $getArticle["releasedate"];
				$tmpDate = explode("-",$tmpDate);
				$newDate = $tmpDate[0].$tmpDate[1].$tmpDate[2];
				$curDate = date("Ymd");

				if ($newDate > $curDate){
					$getArticle["sUpcoming"] = true;
					$getArticle["sReleasedate"] = $tmpDate[2].".".$tmpDate[1].".".$tmpDate[0];
				}else {
					$getArticle["sUpcoming"] = false;
				}
				
				// Check if article is available yet
				list($Y,$M,$D) = explode("-",$getArticle["releasedate"]);
				
				if (mktime(0,0,0,$M,$D,$Y)>mktime(0,0,0,date("m"),date("d"),date("Y"))){
						$getArticle["sReleaseDate"] = $D.".".$M.".".$Y;
				}
				
				// Get cheapest price
				
				$getArticle["priceStartingFrom"] = $this->sGetCheapestPrice($getArticle["articleID"],$getArticle["pricegroup"],$getArticle["pricegroupID"],$getArticle["pricegroupActive"]);
				
				if ($getArticle["price"]) $getArticle["price"] = $this->sCalculatingPrice($getArticle["price"],$getArticle["tax"],$getArticle);
				
				// Load article-configurations
				
				$getArticle = $this->sGetArticleConfig($getArticle["articleID"],$getArticle);
				
				if ($getArticle["sConfigurator"]){
					unset($getArticle["sBlockPrices"]);
					// Refresh block-pricing if any pricegroup rules are active
					if (!$this->sSYSTEM->sUSERGROUPDATA["tax"] && $this->sSYSTEM->sUSERGROUPDATA["id"]){
						$getArticle["sBlockPrices"] = $this->sGetPricegroupDiscount($this->sSYSTEM->sUSERGROUP,$getArticle["pricegroupID"],str_replace(",",".",$getArticle["pricenumeric"]),1,true,$getArticle,false);
					}else {
						$getArticle["sBlockPrices"] = $this->sGetPricegroupDiscount($this->sSYSTEM->sUSERGROUP,$getArticle["pricegroupID"],str_replace(",",".",$getArticle["pricenumeric"]/(100+$getArticle["tax"])*100),1,true,$getArticle,false);
					}
					if ($getArticle["pricegroupActive"]){
						$getArticle["priceBeforePriceGroup"] = $getArticle["price"];
						$getArticle["price"] = $this->sFormatPrice($this->sGetPricegroupDiscount($this->sSYSTEM->sUSERGROUP,$getArticle["pricegroupID"],str_replace(",",".",$getArticle["price"]),1,false));
					}
				}
				
				// Reformat prices
				// =================================================.
				if ($getArticle["pseudoprice"]){
					$getArticle["pseudoprice"] = $this->sCalculatingPrice($getArticle["pseudoprice"],$getArticle["tax"],$getArticle);
					$discPseudo =  str_replace(",",".",$getArticle["pseudoprice"]);
					$discPrice = str_replace(",",".",$getArticle["price"]);
					if ($discPseudo>$discPrice){
						$discount = round(($discPrice / $discPseudo * 100) - 100,2)*-1;
						$getArticle["pseudopricePercent"] = array("int"=>round($discount,0),"float"=>$discount);
					}
				}
				if ($getArticle["priceStartingFrom"]) $getArticle["priceStartingFrom"] = $this->sCalculatingPrice($getArticle["priceStartingFrom"],$getArticle["tax"],$getArticle);
				
				// Update article impressions
				// =================================================.
				$updateImpressions = $this->sSYSTEM->sDB_CONNECTION->Execute("
				UPDATE s_articles_details SET impressions=impressions+1 WHERE articleID={$getArticle["articleID"]} AND kind=1
				");
				
				// Get Article images
				// =================================================.
				$getArticle["image"] = $this->sGetArticlePictures($getArticle["articleID"],true,4);		// Ändern
				$getArticle["images"] = $this->sGetArticlePictures($getArticle["articleID"],false,0);	// Ändern
				
				// Links
				// =================================================.
				$getArticle["linkBasket"] = $this->sSYSTEM->sCONFIG['sBASEFILE']."?sViewport=basket&sAdd=".$getArticle["ordernumber"];
				$getArticle["linkDetails"] = $this->sSYSTEM->sCONFIG['sBASEFILE']."?sViewport=detail&sArticle=".$getArticle["articleID"];
				$getArticle["linkDetailsRewrited"] = $this->sSYSTEM->sMODULES['sCore']->sRewriteLink($this->sSYSTEM->sCONFIG['sBASEFILE']."?sViewport=detail&sDetails=".$getArticle["articleID"],$getArticle["articleName"]);
				
				$getArticle["linkNote"] = $this->sSYSTEM->sCONFIG['sBASEFILE']."?sViewport=note&sAdd=".$getArticle["ordernumber"];
				$getArticle["linkTellAFriend"] = $this->sSYSTEM->sCONFIG['sBASEFILE']."?sViewport=tellafriend&sDetails=".$getArticle["articleID"];
				$getArticle["linkCheaper"] = $this->sSYSTEM->sCONFIG['sBASEFILE']."?sViewport=cheaper&sDetails=".$getArticle["articleID"];
				// PDF - Link
				
				
				$getArticle["linkPDF"] = $this->sSYSTEM->sCONFIG['sBASEFILE']."?sViewport=detail&sDetails=".$getArticle["articleID"]."&sLanguage=".$this->sSYSTEM->sLanguage."&sPDF=1";
			 
				// Downloads
				// =================================================.
				$sql = "
				SELECT id, description, filename, size FROM s_articles_downloads WHERE articleID = {$getArticle["articleID"]}
				";
				
				$getArticleDownloads =$this->sSYSTEM->sDB_CONNECTION->CacheGetAll($this->sSYSTEM->sCONFIG['sCACHEARTICLE'],$sql,false,"article_".$getArticle["articleID"]);
				foreach ($getArticleDownloads as $articleDownloadKey => $articleDownloadValue){
					// Get possible download - translation
					$getArticleDownloads[$articleDownloadKey] = $this->sGetTranslation($articleDownloadValue,$articleDownloadValue["id"],"download",$this->sSYSTEM->sLanguage);
					// Construct filename
					$getArticleDownloads[$articleDownloadKey]["filename"] = $this->sSYSTEM->sPathArticleFiles."/".$getArticleDownloads[$articleDownloadKey]["filename"];
				}
				
				$getArticle["sDownloads"] = $getArticleDownloads;
				// Load bundled products
				$getArticle["sAccessories"] = $this->sGetArticleAccessories($getArticle["articleID"]);
				// Professional - Vote AVG
				$getArticle["sVoteAverange"] = $this->sGetArticlesAverangeVote($getArticle["articleID"]);
				$getArticle["sVoteComments"] = $this->sGetArticlesVotes($getArticle["articleID"]);
				
				
				if (!empty($getArticle["images"])){
					foreach ($getArticle["images"] as &$image){ 
					    if ($image["relations"]=="&{}" || $image["relations"]=="||{}"){ 
					            //$getArticle["images"][$key]["relations"] = ""; 
								$image["relations"] = "";
					    } 
					} 
				}
					
				if (!empty($getArticle["image"])){
					if ($getArticle["image"]["res"]["relations"]=="&{}" || $getArticle["image"]["res"]["relations"]=="||{}"){
						$getArticle["image"]["res"]["relations"] = "";
					}
				}
			
				if (!empty($getArticle["filtergroupID"])) $getArticle["sProperties"] = $this->sGetArticleProperties($getArticle["articleID"],$getArticle["filtergroupID"]);
				
				// Refresh last articles
				$this->sSetLastArticle($getArticle["image"]["src"][$this->sSYSTEM->sCONFIG['sLASTARTICLESTHUMB']],$getArticle['articleName'],$getArticle['articleID']);
				$getArticle["sNavigation"] = $this->sGetAllArticlesInCategory($getArticle["articleID"]);
				
				//sDescriptionKeywords
				$string = (strip_tags(html_entity_decode($getArticle["description_long"])));
				$string = preg_replace("/[^a-zA-Z0-9äöüß\-]/", " ", $string);
				$words = preg_split('/ /', $string, -1, PREG_SPLIT_NO_EMPTY);
				$badwords = explode(",",$this->sSYSTEM->sCONFIG['sBADWORDS']);
				$words = array_diff($words, $badwords);
				$words = array_count_values($words);
				foreach (array_keys($words) as $word)
					if(strlen($word)<2)
						unset($words[$word]);
				arsort($words);
				$getArticle["sDescriptionKeywords"] = htmlentities(implode(", ",array_slice(array_keys($words),0,20)));
		}
		eval($this->sSYSTEM->sCallHookPoint("sArticles.php_sGetArticleById_BeforeReturn"));
				
		if (!$this->sSYSTEM->sCheckLicense("","",$this->sSYSTEM->sLicenseData["sBUNDLE"])){
			$getArticle['crossbundlelook'] = false;
		}
		
		
		/**
		 * LIVE-SHOPPING - START - HAUPTARTIKEL
		 */
		$tmpArticle = $getArticle;
		$tmpArticle = $this->sGetLiveShopping('fix', 0, $tmpArticle, false, '', '', 0);	
		
		if(isset($tmpArticle['liveshoppingData']))
		{
			
			foreach ($tmpArticle['liveshoppingData'] as $key => $liveshopping) {
				//Überprüfung, ob eine Artikelbeschränkung vorliegt
				if(!empty($liveshopping['sLiveStints'])) {
					if(!in_array($tmpArticle['ordernumber'], $liveshopping['sLiveStints'])){
						unset($tmpArticle['liveshoppingData'][$key]);
					}
				}
			}
		}
		
		if(!empty($tmpArticle['liveshoppingData']))
		{
			foreach ($tmpArticle['liveshoppingData'] as $live) {
				$tmpArticle['liveshoppingData'] = $live;
				$tmpArticle['price'] = $tmpArticle['liveshoppingData']['price'];
				$tmpArticle['price'] = $this->sFormatPrice($tmpArticle['price']);
				break;
			}
			$getArticle = $tmpArticle;
		}
		/**
		 * LIVE-SHOPPING - END
		 */
		$getArticle = Enlight()->Events()->filter('Shopware_Modules_Articles_GetArticleById_FilterResult', $getArticle, array('subject'=>$this,'id'=>$this->sSYSTEM->_GET['sArticle'],'isBlog'=>$isBlog,'customergroup'=>$this->sSYSTEM->sUSERGROUP));
		
		return $getArticle;


	}

	/**
	 * Sort multidimensional array without losing key information
	 * @param array $data data to sort
	 * @param string $sortby key to sort by
	 * @access public
	 * @return array 
	 */
	public function masort(&$data, $sortby)
	{
	   static $sort_funcs = array();

	   if (empty($sort_funcs[$sortby])) {
	       $code = "\$c=0;";
	       foreach (split(',', $sortby) as $key) {
	         $array = array_pop($data);
	         array_push($data, $array);
	         if(is_numeric($array[$key]))
	           $code .= "if ( \$c = ((\$a['$key'] == \$b['$key']) ? 0:((\$a['$key'] < \$b['$key']) ? -1 : 1 )) );";
	         else
	           $code .= "if ( (\$c = strcasecmp(\$a['$key'],\$b['$key'])) != 0 ) return \$c;\n";
	       }
	       $code .= 'return $c;';
	       $sort_func = $sort_funcs[$sortby] = create_function('$a, $b', $code);
	   } else {
	       $sort_func = $sort_funcs[$sortby];
	   }

	  $sort_func = $sort_funcs[$sortby];
	   uasort($data, $sort_func);
	}
	
	/**
	 * Formats article prices
	 * @access public
	 * @param float $price 
	 * @return float price
	 */
	public function sFormatPrice ($price){
		$price = str_replace(",",".",$price);
		$price = $this->sRound($price);
		$price = str_replace(".",",",$price);	// Replaces points with commas
		$commaPos = strpos($price,",");
		if ($commaPos){
		
			$part = substr($price,$commaPos+1,strlen($price)-$commaPos);
			switch (strlen($part)){
				case 1:
				$price .= "0";
				break;
				case 2:
				break;
			}
		}
		else {
			if (!$price){
				$price = "0";
			}else {
				$price .= ",00";
			}
		}
		eval($this->sSYSTEM->sCallHookPoint("sArticles.php_sFormatPrice_BeforeEnd"));
		return $price;
	}
	
	/**
	 * Round article price
	 * @param $moneyfloat price
	 * @access public
	 * @return float price
	 */
	public function sRound ($moneyfloat = null)
	{
		eval($this->sSYSTEM->sCallHookPoint("sArticles.php_sRound_Start"));
		$money_str = explode(".",$moneyfloat);
		if (empty($money_str[1])) $money_str[1] = 0;
			$money_str[1] = substr($money_str[1],0, 3); // convert to rounded (to the nearest thousandth) string
		
	   	$money_str = $money_str[0].".".$money_str[1];
	   	eval($this->sSYSTEM->sCallHookPoint("sArticles.php_sRound_BeforeEnd"));
		return round($money_str,2);
	}
	
	/**
	 * Get basic article data in various modes (firmly definied by id, random, top,new)
	 * @param string $mode Modus (fix, random, top, new)
	 * @param int $category filter by category
	 * @param int $value article id / ordernumber for firmly definied articles
	 * @access public
	 * @return array 
	 */
	public function sGetPromotionById ($mode,$category=0,$value=0)
	{
		if (Enlight()->Events()->notifyUntil('Shopware_Modules_Articles_GetPromotionById_Start', array('subject'=>$this,'mode'=>$mode,'category'=>$category,'value'=>$value))){
			return false;
		}
		eval($this->sSYSTEM->sCallHookPoint("sArticles.php_sGetPromotionById_Start"));
		$cacheSQL = "";
		$category = intval($category);
		if ($mode!="fix"){
			if (!empty($this->sCachePromotions)){
				$cacheSQL = "AND a.id!=".implode(" AND a.id!=",$this->sCachePromotions);
			}
		}
		if (empty($category) && $mode!="fix"){
			$category = $this->sSYSTEM->sLanguageData[$this->sSYSTEM->sLanguage]['parentID'];
		}
		if (!empty($category) && $mode!="fix"){
			$categorySQL = "AND ac.categoryID=$category AND ac.articleID=a.id";
			$categoryFrom = ", s_articles_categories ac";
		}else {
			$categorySQL = "";
			$categoryFrom = "";
		}
		eval($this->sSYSTEM->sCallHookPoint("sArticles.php_sGetPromotionById_BeforeSwitch"));
		switch ($mode){
			case "random":	// Random
				if (!is_array($this->sCachePromotions)) $this->sCachePromotions = array();
				
				$sql = "SELECT a.id as articleID FROM s_articles a $categoryFrom WHERE a.active=1 AND a.mode = 0 $categorySQL ORDER BY rand()";
				$sql = Enlight()->Events()->filter('Shopware_Modules_Articles_GetPromotionById_FilterSqlRandom', $sql, array('subject'=>$this,'mode'=>$mode,'category'=>$category,'value'=>$value));
			
				$articleIDs = $this->sSYSTEM->sDB_CONNECTION->GetCol($sql);
				foreach ($articleIDs as $articleID) {
					if(!in_array($articleID, $this->sCachePromotions)) {
						if (!empty($articleID)){
							$value = $articleID;
						}
					}
				}
				if(empty($value)) return false; 
				$valueSQL = "a.id=$value";
				break;
			case "fix":
				if(empty($value)) return false; 

				if(is_int($value)||is_double($value)) {
					$valueSQL = "d.articleID=$value";
					$articleID = $value;
				} elseif(strlen(intval($value))!=strlen($value)) {
					$value = $this->sSYSTEM->sDB_CONNECTION->qstr($value);
					$valueSQL = "d.ordernumber=$value";
				} else {
					$value = $this->sSYSTEM->sDB_CONNECTION->qstr($value);
					$valueSQL = "(d.articleID=$value OR d.ordernumber=$value)";
				}
				break;
			case "new":
				$sql = "SELECT a.datum as date, COUNT(a.id) as count FROM s_articles a $categoryFrom WHERE a.mode=0 AND a.active=1 $categorySQL $cacheSQL GROUP BY a.datum ORDER BY a.datum DESC LIMIT 1";
				$results = $this->sSYSTEM->sDB_CONNECTION->CacheGetRow($this->sSYSTEM->sCONFIG['sCACHEARTICLE'],$sql);
				$randLimit = rand(0,$results["count"]-1);
				$results["date"] = $this->sSYSTEM->sDB_CONNECTION->qstr($results["date"]);
				$sql = "SELECT a.id as articleID FROM s_articles a $categoryFrom WHERE a.mode=0 AND a.active=1 AND datum={$results["date"]} $categorySQL $cacheSQL LIMIT $randLimit,1";
				$sql = Enlight()->Events()->filter('Shopware_Modules_Articles_GetPromotionById_FilterSqlNew', $sql, array('subject'=>$this,'mode'=>$mode,'category'=>$category,'value'=>$value));
				
				$value = $this->sSYSTEM->sDB_CONNECTION->GetRow($sql);
				$value = $value["articleID"];
				$valueSQL = "a.id=$value";
				break;
			case "top":
				if(!empty($this->sSYSTEM->sCONFIG['sPROMOTIONTIME']))
					$promotionTime = (int) $this->sSYSTEM->sCONFIG['sPROMOTIONTIME'];
				else
					$promotionTime = 30;
				if(!empty($this->sCachePromotions))
					$cacheSQL = "AND od.articleID!=".implode(" AND od.articleID!=",$this->sCachePromotions);
				$sql = "
					SELECT od.articleID FROM s_order as o, s_order_details od, s_articles a $categoryFrom
					WHERE o.ordertime>DATE_SUB(NOW(),INTERVAL $promotionTime DAY) AND o.id=od.orderID AND od.modus=0 AND od.articleID=a.id AND a.active=1 $categorySQL $cacheSQL
					GROUP BY od.articleID ORDER BY COUNT(od.articleID) DESC";
				
				$sql = Enlight()->Events()->filter('Shopware_Modules_Articles_GetPromotionById_FilterSqlTop', $sql, array('subject'=>$this,'mode'=>$mode,'category'=>$category,'value'=>$value));
				
				$value = $this->sSYSTEM->sDB_CONNECTION->CacheGetOne($this->sSYSTEM->sCONFIG['sCACHEARTICLE'],$sql);
				if (empty($value)) return array();
				$valueSQL = "a.id=$value";
				
				break;
			case "gfx" || "image":
				
				$rs["mode"] = "gfx";
				$rs["img"] = $value["img"] ? $this->sSYSTEM->sPathBanner.$value["img"] : $this->sSYSTEM->sPathBanner.$value["image"];
				$rs["link"] = $value["link"];
				$rs["linkTarget"] = $value["link_target"] ? $value["link_target"] : $value["target"];
				$rs["description"] = $value["description"];
				
				$rs = Enlight()->Events()->filter('Shopware_Modules_Articles_GetPromotionById_FilterGfx', $rs, array('subject'=>$this,'mode'=>$mode,'category'=>$category,'value'=>$value));
				
				return $rs;
				break;
			case "premium": // Prämie
				break;
		}

		if($mode=="premium")
		{
			$value = $this->sSYSTEM->sDB_CONNECTION->qstr($value);
			$sql = "
				SELECT a.active AS active, a.id as articleID, ordernumber,datum,sales, topseller, a.description AS description,description_long, aSupplier.name AS supplierName, aSupplier.img AS supplierImg, a.name AS articleName
				FROM s_articles_categories AS aCategories,
				s_articles AS a,
				s_articles_supplier AS aSupplier,
				s_articles_details AS d
				WHERE aSupplier.id=a.supplierID
				AND d.articleID=a.id
				AND d.kind=1
				AND a.id=$value
				AND (
					SELECT articleID 
					FROM s_articles_avoid_customergroups 
					WHERE articleID = a.id AND customergroupID = ".$this->sSYSTEM->sUSERGROUPDATA["id"]."
				) IS NULL
			";
			$articleID = $value;
			$sql = Enlight()->Events()->filter('Shopware_Modules_Articles_GetPromotionById_FilterSqlPremium', $sql, array('subject'=>$this,'mode'=>$mode,'category'=>$category,'value'=>$value));
			
			eval($this->sSYSTEM->sCallHookPoint("sArticles.php_sGetPromotionById_Premium"));
		}
		else
		{
			$this->sSYSTEM->sCONFIG['sMARKASNEW'] = (int) $this->sSYSTEM->sCONFIG['sMARKASNEW'];
			$this->sSYSTEM->sCONFIG['sMARKASTOPSELLER'] = (int) $this->sSYSTEM->sCONFIG['sMARKASTOPSELLER'];
			
			$sql = "
				SELECT
					a.id as articleID, d.id AS articleDetailsID,
					TRIM(ordernumber) as ordernumber,datum,sales, topseller as highlight, a.description, description_long,
					s.name AS supplierName, s.img AS supplierImg,
					a.name AS articleName, IFNULL(p.price,p2.price) as price,
					IF(p.pseudoprice,p.pseudoprice,p2.pseudoprice) as pseudoprice, tax,
					attr1,attr2,attr3,attr4,attr5,attr6,attr7,attr8,attr9,attr10,
					attr11,attr12,attr13,attr14,attr15,attr16,attr17,attr18,attr19,attr20,
					instock, weight, shippingtime,
					IFNULL(p.pricegroup,IFNULL(p2.pricegroup,'EK')) as pricegroup,
					pricegroupID,pricegroupActive,filtergroupID,
					a.purchaseunit,referenceunit,
					unitID, laststock, additionaltext,
					IFNULL((SELECT 1 FROM s_articles_groups WHERE articleID=a.id LIMIT 1),0) as sConfigurator,
					IFNULL((SELECT 1 FROM s_articles_esd WHERE articleID=a.id LIMIT 1),0) as esd,
					IFNULL((SELECT CONCAT(AVG(points),'|',COUNT(*)) as votes FROM s_articles_vote WHERE active=1 AND articleID=a.id),'0.00|00') as sVoteAverange,
					IF(DATEDIFF(NOW(), a.datum)<={$this->sSYSTEM->sCONFIG['sMARKASNEW']},1,0) as newArticle,
					IF(d.sales>={$this->sSYSTEM->sCONFIG['sMARKASTOPSELLER']},1,0) as topseller,
					IF(a.releasedate>CURDATE(),1,0) as sUpcoming,
					IF(a.releasedate>CURDATE(),DATE_FORMAT(a.releasedate, '%d.%m.%Y'),'') as sReleasedate
				FROM s_articles a
				
				INNER JOIN s_articles_details d
				ON d.articleID=a.id
				AND d.kind=1
				
				JOIN s_articles_categories ac
				ON ac.articleID=a.id
				AND ac.categoryID={$this->sSYSTEM->sLanguageData[$this->sSYSTEM->sLanguage]['parentID']}
				
				JOIN s_articles_attributes at
				ON at.articleID=a.id
				
				JOIN s_core_tax t
				ON t.id=a.taxID
				
				LEFT JOIN s_articles_supplier s
				ON s.id=a.supplierID
				
				LEFT JOIN s_articles_prices p
				ON p.articleDetailsID=d.id
				AND p.pricegroup='{$this->sSYSTEM->sUSERGROUP}'
				AND p.`from`='1'

				LEFT JOIN s_articles_prices p2
				ON p2.articleDetailsID=d.id
				AND p2.pricegroup='EK'
				AND p2.`from`='1'
								
				WHERE $valueSQL
				AND a.active=1
				AND (
					SELECT articleID 
					FROM s_articles_avoid_customergroups 
					WHERE articleID = a.id AND customergroupID = ".$this->sSYSTEM->sUSERGROUPDATA["id"]."
				) IS NULL
				LIMIT 1
			";
			$sql = Enlight()->Events()->filter('Shopware_Modules_Articles_GetPromotionById_FilterSql', $sql, array('subject'=>$this,'mode'=>$mode,'category'=>$category,'value'=>$value));
			
			eval($this->sSYSTEM->sCallHookPoint("sArticles.php_sGetPromotionById_AfterSQL"));
		}
		if ($mode=="random")
		{
			$getPromotionResult = $this->sSYSTEM->sDB_CONNECTION->GetRow($sql);
		}
		else
		{
			if (!empty($articleID)){
				$getPromotionResult = $this->sSYSTEM->sDB_CONNECTION->CacheGetRow($this->sSYSTEM->sCONFIG['sCACHEARTICLE'],$sql,false,"article_".$articleID);
			} else {
				$getPromotionResult = $this->sSYSTEM->sDB_CONNECTION->CacheGetRow($this->sSYSTEM->sCONFIG['sCACHEARTICLE'],$sql);
			}
		}
		if($getPromotionResult==false)
		{
			return false;
		}
		elseif(empty($getPromotionResult))
		{
			return false;
		}
		
		// Check only for liveshopping if ls is configurated for this article
		if (!empty($getPromotionResult["articleID"])){
			$checkLS = $this->sSYSTEM->sDB_CONNECTION->GetRow("
			SELECT id FROM s_articles_live WHERE articleID = ?
			",array($getPromotionResult["articleID"]));
			if (!empty($checkLS["id"])){
				$getPromotionResult = $this->sGetLiveShopping('fix', 0, $getPromotionResult);
				if (isset($getPromotionResult['liveshoppingData'][0])) $getPromotionResult['liveshoppingData'] = $getPromotionResult['liveshoppingData'][0];
				if(!empty($getPromotionResult['liveshoppingData'])) $getPromotionResult['price'] = $getPromotionResult['liveshoppingData']['net_price'];
			}
		}
		
		$getPromotionResult = $this->sGetTranslation($getPromotionResult,$getPromotionResult["articleID"],"article",$this->sSYSTEM->sLanguage);
		eval($this->sSYSTEM->sCallHookPoint("sArticles.php_sGetPromotionById_QueryStart"));
		
		$checkVariantsSQL = 'SELECT COUNT(*) FROM `s_articles_details` WHERE `articleID` = ?';
		$checkVariants = $this->sSYSTEM->sDB_CONNECTION->CacheGetOne($this->sSYSTEM->sCONFIG['sCACHEARTICLE'], $checkVariantsSQL, $getPromotionResult['articleID']);
		$getPromotionResult['sVariantArticle'] = $checkVariants > 1 ? true : false;
		
		// Load article properties (Missing support for multilanguage)
		if ($getPromotionResult["filtergroupID"])
		{
			$getPromotionResult["sProperties"] = $this->sGetArticleProperties($getPromotionResult["articleID"],$getPromotionResult["filtergroupID"]);
		}
		// Add to cache, so this article will be displayed with clones ;)
		$this->sCachePromotions[] = $getPromotionResult["articleID"];

		// Get cheapest price
		$cheapestPrice = $this->sGetCheapestPrice($getPromotionResult["articleID"],$getPromotionResult["pricegroup"],$getPromotionResult["pricegroupID"],$getPromotionResult["pricegroupActive"],$mode=="random" ? true : false,true);
		if (!is_array($cheapestPrice)){
			$cheapestPriceT[0] = $cheapestPrice;
			$cheapestPriceT[1] = "";
			$cheapestPrice = $cheapestPriceT;
		}
		$getPromotionResult["priceStartingFrom"] = $cheapestPrice[0];
		
		if (!empty($getPromotionResult["priceStartingFrom"])){
			$getPromotionResult["price"] = $getPromotionResult["priceStartingFrom"];
			if ($cheapestPrice[1]<=1){
				$getPromotionResult["priceStartingFrom"] = $this->sCalculatingPrice($getPromotionResult["priceStartingFrom"],$getPromotionResult["tax"],$getPromotionResult);
			}else {
				unset($getPromotionResult["priceStartingFrom"]);
			}
		}
		// Formating prices
		$getPromotionResult["price"] = $this->sCalculatingPrice($getPromotionResult["price"],$getPromotionResult["tax"],$getPromotionResult);
		
		if ($getPromotionResult["purchaseunit"]  > 0 && !empty($getPromotionResult["referenceunit"]))
		{
			// $basePrice = $this->sCalculatingPriceNum(str_replace(",",".",$articles[$articleKey]["price"]),0,$articles[$articleKey],$articles[$articleKey],array("liveshoppingID"=>1),true);
				$basePrice = $this->sCalculatingPriceNum($getPromotionResult["price"],0,$getPromotionResult,$getPromotionResult,array("liveshoppingID"=>1),true);
				$basePrice = $basePrice / $getPromotionResult["purchaseunit"] * $getPromotionResult["referenceunit"];
				$basePrice = $this->sFormatPrice($basePrice);
				$getPromotionResult["referenceprice"] = $basePrice;
		}
		if (!empty($getPromotionResult["unitID"]))
		{
			$getPromotionResult["sUnit"] = $this->sGetUnit($getPromotionResult["unitID"]);
		}
		if ($getPromotionResult["pseudoprice"]){
			$getPromotionResult["pseudoprice"] = $this->sCalculatingPrice($getPromotionResult["pseudoprice"],$getPromotionResult["tax"],$getPromotionResult);
			$discPseudo =  str_replace(",",".",$getPromotionResult["pseudoprice"]);
			$discPrice = str_replace(",",".",$getPromotionResult["price"]);
			$discount = round(($discPrice / $discPseudo * 100) - 100,2)*-1;
			$getPromotionResult["pseudopricePercent"] = array("int"=>round($discount,0),"float"=>$discount);
		}
		
		if (!empty($getPromotionResult["articleID"]))
		{
			$basePrice = str_replace(",", ".", $getPromotionResult['price']);
		    $basePrice = floatval($basePrice);
		    if (!empty($getPromotionResult['purchaseunit'])){
		    $refPrice = $basePrice / $getPromotionResult['purchaseunit'] * $getPromotionResult['referenceunit'];
		    $getPromotionResult['referenceprice'] = number_format($refPrice, 2, ",", ".");
		    }
		}

		// Strip tags from descriptions
		$getPromotionResult["articleName"] = $this->sOptimizeText($getPromotionResult["articleName"]);
		$getPromotionResult["description_long"] = strlen($getPromotionResult["description"])>5 ? $getPromotionResult["description"] : $this->sOptimizeText($getPromotionResult["description_long"]);

		$getPromotionResult['sVoteAverange'] = explode('|', $getPromotionResult['sVoteAverange']);
		$getPromotionResult['sVoteAverange'] = array(
			'averange' => round($getPromotionResult['sVoteAverange'][0], 2),
			'count' => round($getPromotionResult['sVoteAverange'][1]),
		);
		$getPromotionResult["image"] = $this->sGetArticlePictures($getPromotionResult["articleID"],true,0,"",false,$mode=="random" ? true : false);

		$getPromotionResult["linkBasket"] = $this->sSYSTEM->sCONFIG['sBASEFILE']."?sViewport=basket&sAdd=".$getPromotionResult["ordernumber"];
		$getPromotionResult["linkDetails"] = $this->sSYSTEM->sCONFIG['sBASEFILE']."?sViewport=detail&sArticle=".$getPromotionResult["articleID"];
		if(!empty($category)&&$category!=$this->sSYSTEM->sLanguageData[$this->sSYSTEM->sLanguage]["parentID"])
			 $getPromotionResult["linkDetails"] .= "&sCategory=$category";
			 
		eval($this->sSYSTEM->sCallHookPoint("sArticles.php_sGetPromotionById_QueryEnd"));

		$getPromotionResult["mode"] = $mode;

		eval($this->sSYSTEM->sCallHookPoint("sArticles.php_sGetPromotionById_BeforeEnd"));
		
		$getPromotionResult = Enlight()->Events()->filter('Shopware_Modules_Articles_GetPromotionById_FilterResult', $getPromotionResult, array('subject'=>$this,'mode'=>$mode,'category'=>$category,'value'=>$value));
			
		return $getPromotionResult;
	}
	
	/**
	 * Optimize text, strip html tags etc.
	 * @param string $text
	 * @access public
	 * @return string $text 
	 */
	public function sOptimizeText($text)
	{
		eval($this->sSYSTEM->sCallHookPoint("sArticles.php_sOptimizeText_Start"));
		$text = html_entity_decode($text);
		$text = preg_replace('!<[^>]*?>!', ' ', $text);
		$text = str_replace(chr(0xa0), " ", $text);
		$text = preg_replace('/\s\s+/', ' ', $text);
		$text = htmlspecialchars($text);
		$text = trim($text);
		eval($this->sSYSTEM->sCallHookPoint("sArticles.php_sOptimizeText_BeforeEnd"));
		return $text;
	}
	
	
	/**
	 * Get all pictures from a certain article
	 * @access public
	 * @return array 
	 */
	public function sGetArticlePictures($sArticleID,$onlyCover=true,$pictureSize,$ordernumber="",$allImages=false,$realtime = false){
		$sArticleID = intval($sArticleID);
		if(empty($this->sSYSTEM->sPathArticleImg)){
			if (preg_match("/443/",$_SERVER['SERVER_PORT'])){
				$this->sSYSTEM->sPathArticleImg = "https://".$this->sSYSTEM->sCONFIG["sBASEPATH"].$this->sSYSTEM->sCONFIG["sARTICLEIMAGES"]."/";
			}else {
				$this->sSYSTEM->sPathArticleImg = "http://".$this->sSYSTEM->sCONFIG["sBASEPATH"].$this->sSYSTEM->sCONFIG["sARTICLEIMAGES"]."/";
			}
		}
		Enlight()->Events()->notify('Shopware_Modules_Articles_GetArticlePictures_Start',array('subject'=>$this,'id'=>$sArticleID));
		
		// Get different thumbnail sizes
		$imagesizes = $this->sSYSTEM->sCONFIG['sIMAGESIZES'];
	
		$imagesizes = explode(";",$imagesizes);
		foreach ($imagesizes as $imagesize){
			$imagesize = explode(":",$imagesize);
			$sizes[] = $imagesize[2];
		}
			    
		// Only main-picture
		if ($onlyCover){
			if (empty($ordernumber)){
				$query_image = $this->sSYSTEM->sDB_CONNECTION->CacheGetRow($realtime == true ? 0 : $this->sSYSTEM->sCONFIG['sCACHEARTICLE'],"SELECT * FROM s_articles_img
				WHERE articleID=$sArticleID AND main=1 UNION SELECT * FROM s_articles_img
				WHERE articleID=$sArticleID AND main=2 LIMIT 1
				",false,"article_$sArticleID");
			}else {
				$query_image = $this->sSYSTEM->sDB_CONNECTION->CacheGetRow($realtime == true ? 0 : $this->sSYSTEM->sCONFIG['sCACHEARTICLE'],"SELECT * FROM s_articles_img
				WHERE articleID=$sArticleID AND relations LIKE '%$ordernumber%' UNION SELECT * FROM s_articles_img
				WHERE articleID=$sArticleID AND main=1 LIMIT 1
				",false,"article_$sArticleID");
			}
	
			
			if (!empty($query_image["img"])){
				
				if (empty($query_image["extension"])) $query_image["extension"] = "jpg";
				$result["src"]["original"] = $this->sSYSTEM->sPathArticleImg.$query_image["img"].".".$query_image["extension"];
				$result["res"]["original"]["width"] = $query_image["width"];
				$result["res"]["original"]["height"] = $query_image["height"];
				$result["res"]["description"] = $query_image["description"];
				$result["res"]["relations"] = $query_image["relations"];
				$result["position"] = $query_image["position"];
				$result["extension"] = $query_image["extension"];
				$result["main"] = $query_image["main"];
				foreach ($sizes as $size){
					$result["src"][$size] = $this->sSYSTEM->sPathArticleImg.$query_image["img"]."_$size.".$query_image["extension"];
					
				}
				
			}else {
				foreach ($sizes as $size){
					//$result["src"][$size] = "";
				}
				$result["resolution"] = array();
			}
		}else {
			// Get all article pictures
			if (empty($allImages)){
				$query_image = $this->sSYSTEM->sDB_CONNECTION->CacheGetAll($realtime == true ? 0 : $this->sSYSTEM->sCONFIG['sCACHEARTICLE'],"SELECT * FROM s_articles_img
				WHERE articleID=$sArticleID AND main=2 ORDER BY position, id ASC
				",false,"article_$sArticleID");
			}else {
				$query_image = $this->sSYSTEM->sDB_CONNECTION->CacheGetAll($realtime == true ? 0 : $this->sSYSTEM->sCONFIG['sCACHEARTICLE'],"SELECT * FROM s_articles_img
				WHERE articleID=$sArticleID ORDER BY position, id ASC
				",false,"article_$sArticleID");
			}
			
			$result = array();
			foreach ($query_image as $imageKey => $image){
				if (empty($image["extension"])) $image["extension"] = "jpg";
				$result[$imageKey]["src"]["original"] = $this->sSYSTEM->sPathArticleImg.$image["img"].".".$image["extension"];
				$result[$imageKey]["res"]["original"]["width"] = $image["width"];
				$result[$imageKey]["res"]["original"]["height"] = $image["height"];
				$result[$imageKey]["description"] = $image["description"];
				$result[$imageKey]["relations"] = $image["relations"];
				$result[$imageKey]["extension"] = $image["extension"];
				$result[$imageKey]["position"] = $image["position"];
				$result[$imageKey]["main"] = $image["main"];
				// Access to every available size
				foreach ($sizes as $size){
					$result[$imageKey]["src"][$size] = $this->sSYSTEM->sPathArticleImg.$image["img"]."_$size.".$image["extension"];
				}
				
			}
		}
		
		$result = Enlight()->Events()->filter('Shopware_Modules_Articles_GetArticlePictures_FilterResult', $result, array('subject'=>$this,'id'=>$sArticleID));
		
		return $result;
	}
	
	/**
	 * Insert a article in the list of the recently visit articles
	 * @param string $image absolut image url
	 * @param string $name name of the article
	 * @param int $id id of the article
	 * @access public
	 */
	public function sSetLastArticle($image,$name, $id){
		if (empty($this->sSYSTEM->sSESSION_ID)) return;
		$id = intval($id);
		if ((rand()%10) == 0){
			// Remove entries older then 14 days
			$this->sSYSTEM->sDB_CONNECTION->Execute("DELETE FROM s_emarketing_lastarticles WHERE UNIX_TIMESTAMP(time)<=(UNIX_TIMESTAMP(now())-1209600)");	
		}
		$checkForArticle = $this->sSYSTEM->sDB_CONNECTION->GetRow("
		SELECT id FROM s_emarketing_lastarticles WHERE sessionID=? AND articleID=?
		",array($this->sSYSTEM->sSESSION_ID,$id));
		$name = $this->sSYSTEM->sDB_CONNECTION->qstr($name);
		if (!empty($name) && !empty($id) && empty($checkForArticle["id"])){
			$insertArticle = $this->sSYSTEM->sDB_CONNECTION->Execute("
			INSERT INTO s_emarketing_lastarticles (img, name, articleID, sessionID, time, userID)
			VALUES ('$image',$name,$id,'".$this->sSYSTEM->sSESSION_ID."',now(),'".intval($this->sSYSTEM->_SESSION["sUserId"])."')
			");
		}
	}
	
	/**
	 * Get article id by ordernumber
	 * @param string $ordernumber
	 * @access public
	 * @return int $id or false 
	 */
	public function sGetArticleIdByOrderNumber($ordernumber){
		$checkForArticle = $this->sSYSTEM->sDB_CONNECTION->GetRow("
		SELECT articleID AS id FROM s_articles_details WHERE ordernumber=? 
		",array($ordernumber));
		
		if ($checkForArticle["id"]){
			return $checkForArticle["id"];
		}else {
			// Check if is article-configurator-article
			$checkForArticle = $this->sSYSTEM->sDB_CONNECTION->GetRow("
			SELECT articleID AS id FROM s_articles_groups_value WHERE ordernumber=?
			",array($ordernumber));
			if ($checkForArticle["id"]){
				return $checkForArticle["id"];
			}else {
				return false;
			}
		}
	}
	
	/**
	 * Get name from a certain article by ordernumber
	 * @param string $ordernumber 
	 * @param bool $returnAll return only name or additional data, too
	 * @access public
	 * @return string or array 
	 */
	public function sGetArticleNameByOrderNumber($ordernumber,$returnAll = false){
		$checkForArticle = $this->sSYSTEM->sDB_CONNECTION->GetRow("
		SELECT s_articles.id,s_articles_details.id AS did, s_articles.name AS articleName, additionaltext FROM s_articles_details, s_articles WHERE 
		ordernumber=?
		AND s_articles.id=s_articles_details.articleID
		",array($ordernumber));

		if ($checkForArticle["articleName"]){
			$checkForArticle = $this->sGetTranslation($checkForArticle,$checkForArticle["id"],"article",$this->sSYSTEM->sLanguage);
			$checkForArticle = $this->sGetTranslation($checkForArticle,$checkForArticle["did"],"variant",$this->sSYSTEM->sLanguage);
			
			if ($returnAll){
				
				return $checkForArticle;
			}else {
				
				return $checkForArticle["articleName"];
			}
		}else {
			
			$checkForArticle = $this->sSYSTEM->sDB_CONNECTION->GetRow("
			SELECT s_articles.id,s_articles.name AS articleName FROM s_articles, s_articles_groups_value WHERE ordernumber=? 
			AND s_articles.id=s_articles_groups_value.articleiD
			",array($ordernumber));
			if ($checkForArticle["articleName"]){
				$checkForArticle = $this->sGetTranslation($checkForArticle,$checkForArticle["id"],"article",$this->sSYSTEM->sLanguage);
				return $checkForArticle["articleName"];
			}else {
				return false;
			}
		}
	}
	
	public function sGetArticleNameByArticleId($articleId, $returnAll = false)
	{
		$ordernumber = $this->sSYSTEM->sDB_CONNECTION->GetOne("
			SELECT ordernumber FROM s_articles_details WHERE kind=1 AND articleID=?
		", array($articleId));
		return $this->sGetArticleNameByOrderNumber($ordernumber, $returnAll);
	}
	
	/**
	 * Get article taxrate by id
	 * @param $id article id
	 * @access public
	 * @return float tax or false
	 */
	public function sGetArticleTaxById($id){
		$checkForArticle = $this->sSYSTEM->sDB_CONNECTION->GetRow("
		SELECT s_core_tax.tax AS tax FROM s_core_tax, s_articles WHERE s_articles.id=? AND
		s_articles.taxID = s_core_tax.id
		",array($id));
		
		if ($checkForArticle["tax"]){
			return $checkForArticle["tax"];
		}else {
			return false;
		}
	}
	
	
	/**
	 * Get recently viewed products
	 * @param int $sCurrentArticle current article
	 * @access public
	 * @return array 
	 */
	public function sGetLastArticles($sCurrentArticle=0){
		$numberOfArticles = $this->sSYSTEM->sCONFIG['sLASTARTICLESTOSHOW'];
		
		// If the user visits currencly an article, this article should not be listed 
		if ($sCurrentArticle){
			$queryArticles = $this->sSYSTEM->sDB_CONNECTION->GetAll("
			SELECT img, name, articleID FROM s_emarketing_lastarticles WHERE articleID!=$sCurrentArticle AND sessionID='".$this->sSYSTEM->sSESSION_ID."' GROUP BY articleID ORDER BY time DESC LIMIT $numberOfArticles
			");
		}else {
			// Update articles
			if (!empty($this->sSYSTEM->_SESSION["sUserId"])){
				$updateArticles =  $this->sSYSTEM->sDB_CONNECTION->Execute("
				UPDATE s_emarketing_lastarticles SET userID = '".$this->sSYSTEM->_SESSION["sUserId"]."'
				WHERE sessionID='".$this->sSYSTEM->sSESSION_ID."'
				");
			}
			$queryArticles = $this->sSYSTEM->sDB_CONNECTION->GetAll("
			SELECT img, name, articleID FROM s_emarketing_lastarticles WHERE sessionID='".$this->sSYSTEM->sSESSION_ID."' GROUP BY articleID ORDER BY time DESC LIMIT $numberOfArticles
			");
		}

		foreach ($queryArticles as $articleKey => $articleValue){
			$queryArticles[$articleKey]["linkDetails"] = $this->sSYSTEM->sCONFIG['sBASEFILE']."?sViewport=detail&sArticle=".$articleValue["articleID"];
			if (preg_match("/443/",$_SERVER['SERVER_PORT'])){
				$queryArticles[$articleKey]["img"] = str_replace("http://","https://",$queryArticles[$articleKey]["img"]);
			}
		}

		return $queryArticles;
	}
	
	/**
	 * Get list of all promotions from a certain category
	 * @param int $category category id
	 * @access public
	 * @return array 
	 */
	public function sGetPromotions($category){
		$category = intval($category);
		eval($this->sSYSTEM->sCallHookPoint("sArticles.php_sGetPromotions_Start"));
		$sToday = date("Y-m-d");
		$sql = "SELECT category,mode, TRIM(ordernumber) as ordernumber, link, description, link_target, img, liveshoppingID
		FROM s_emarketing_promotions
		WHERE category=$category AND ((TO_DAYS(valid_from) <= TO_DAYS('$sToday') AND
		TO_DAYS(valid_to) >= TO_DAYS('$sToday')) OR
		(valid_from='0000-00-00' AND valid_to='0000-00-00')) ORDER BY position ASC
		";
		$sql = Enlight()->Events()->filter('Shopware_Modules_Articles_GetPromotions_FilterSQL', $sql, array('subject'=>$this,'category'=>$category));
		eval($this->sSYSTEM->sCallHookPoint("sArticles.php_sGetPromotions_AfterSQL"));
		$getAffectedPromitions = $this->sSYSTEM->sDB_CONNECTION->GetAll($sql);
		
		// Clearing cache
		unset($this->sCachePromotions);
			if (count($getAffectedPromitions)){
				foreach ($getAffectedPromitions as $promotion){
					switch ($promotion["mode"]){
							case "random":
								$promotion = $this->sGetPromotionById ("random",$category);
								if (count($promotion)>1) $promote[] = $promotion; 
							break;
							case "fix":
								$promotion = $this->sGetPromotionById ("fix",$category,$promotion["ordernumber"]);
								if (count($promotion)>1) $promote[] = $promotion;
							break;
							case "new":
								$promotion = $this->sGetPromotionById ("new",$category);
								if (count($promotion)>1) $promote[] = $promotion; 
							break;
							case "top":
								$promotion = $this->sGetPromotionById ("top",$category);
								if (count($promotion)>1) $promote[] = $promotion; 
							break;
							case "gfx":
								$promotion = $this->sGetPromotionById ("gfx",$category,$promotion);
								if (count($promotion)>1) $promote[] = $promotion; 
							
							break;
							case "livefix":
								$promotion = $this->sGetLiveShopping('fix', 0, $promotion, true);
								$promotion['liveshoppingData'] = $promotion['liveshoppingData'][0];
								if (count($promotion)>1 && !empty($promotion['liveshoppingData'])) $promote[] = $promotion; 
							
							break;
							case "liverand":
								$promotion = $this->sGetLiveShopping('random', 0, $promotion, true);
								
								
								
								$promotion['liveshoppingData'] = $promotion['liveshoppingData'][0];
								if (count($promotion)>1 && !empty($promotion['liveshoppingData'])) $promote[] = $promotion; 
							
							break;
							case "liverandcat":
								$promotion = $this->sGetLiveShopping('random', $category, $promotion, true);
								
								$promotion['liveshoppingData'] = $promotion['liveshoppingData'][0];
								if (count($promotion)>1 && !empty($promotion['liveshoppingData'])) $promote[] = $promotion; 
							
							break;
					} // end switch
					
				} // end foreach
				eval($this->sSYSTEM->sCallHookPoint("sArticles.php_sGetPromotions_BeforeEnd"));
				$promote = Enlight()->Events()->filter('Shopware_Modules_Articles_GetPromotions_FilterResult', $promote, array('subject'=>$this,'category'=>$category));
		
				return $promote;
			} // end if
	} // end function
	
	/**
	 * Read translation for one or more articles
	 * @param $data
	 * @param $ids
	 * @param $object
	 * @param $language
	 * @access public
	 * @return array 
	 */
	public function sGetTranslations($data,$ids,$object,$language){
		if (intval($language)) $language = $this->sSYSTEM->sLanguageData[$language]["isocode"];
		if ($language=="de") return $data;
		if (!is_array($ids)) return $data;
		
		switch ($object){
			case "article":
				$map = array("txtshortdescription"=>"description","txtlangbeschreibung"=>"description_long","txtArtikel"=>"articleName","txtzusatztxt"=>"additionaltext","txtpackunit"=>"packunit");
				$objkey = "articleID";
				break;
			case "variant":
				$map = array("txtzusatztxt"=>"additionaltext");
				break;
			case "link":
				$map = array("linkname"=>"description");
				break;
			case "download":
				$map = array("downloadname"=>"description");
				break;
		}
		
		if (!empty($this->sSYSTEM->sSubShop["fallback"])){
			
			$fallback = $this->sSYSTEM->sSubShop["fallback"];
			$fallback = "UNION
			SELECT s.objectdata,s.objectkey,objectlanguage FROM s_core_translations s
			WHERE 
				s.objecttype = '$object'
			AND
				s.objectkey IN (".implode(",",$ids).")
			AND
				s.objectlanguage = '$fallback'";
		}
			
		$sql = "
		SELECT s.objectdata,s.objectkey,objectlanguage FROM s_core_translations s
		WHERE 
			s.objecttype = '$object'
		AND
			s.objectkey IN (".implode(",",$ids).")
		AND
			s.objectlanguage = '$language'
		$fallback
		";
		
		
		$queryTranslation = $this->sSYSTEM->sDB_CONNECTION->CacheGetAll($this->sSYSTEM->sCONFIG['sCACHETRANSLATIONS'] ? $this->sSYSTEM->sCONFIG['sCACHETRANSLATIONS'] : 3600,$sql);
		
		if (empty($queryTranslation)){
			return $data;
		}
		foreach ($queryTranslation as $value){
			if (empty($translationsTransformed[$value["objectkey"]] )){
				$translationsTransformed[$value["objectkey"]] = $value["objectdata"];	
			}
		}
		
		foreach ($translationsTransformed as $article => $translation){
			$articleKey = $this->recursive_array_search($article,$data,$objkey);
			// Found entry
			$object = unserialize($translation);
			foreach ($object as $translateKey => $value){
			if($map[$translateKey]){
				$key = isset($map[$translateKey]) ? $map[$translateKey] : "";
			}else {
				$key = $translateKey;
			}
			// echo "Setting {$articleKey} {$key} to $value<br />";
			if (!empty($value)) $data[$articleKey][$key] = $this->fixEncoding($value);
			}
			
		}
		return $data;
	}
	
	/**
	 * Recursive searches an array
	 * @param $needle
	 * @param $haystack
	 * @param $field
	 * @param $compareKey
	 * @access public
	 * @return array 
	 */
	public function recursive_array_search($needle,$haystack,$field,$compareKey=0) {
	    foreach($haystack as $key=>$value) {
	        $current_key=$key;
	        if($needle==$value OR (is_array($value) && $this->recursive_array_search($needle,$value,$field,1))) {
	            if ($compareKey==true && $current_key != $field) return false;
	        	return $current_key;
	            
	        }
	    }
	    return false;
	} 
	
	/**
	 * Get translation for an object (article / variant / link / download)
	 * @param $data
	 * @param $id
	 * @param $object
	 * @param $compareKey
	 * @access $language
	 * @return array 
	 */
	public function sGetTranslation($data,$id,$object,$language){
		
		if (intval($language)) $language = $this->sSYSTEM->sLanguageData[$language]["isocode"];
		if ($language=="de") return $data;
		
		switch ($object){
			case "article":
				$map = array("txtshortdescription"=>"description","txtlangbeschreibung"=>"description_long","txtArtikel"=>"articleName","txtzusatztxt"=>"additionaltext","txtpackunit"=>"packunit");
				break;
			case "variant":
				$map = array("txtzusatztxt"=>"additionaltext");
				break;
			case "link":
				$map = array("linkname"=>"description");
				break;
			case "download":
				$map = array("downloadname"=>"description");
				break;
		}
		
		$sql = "
		SELECT objectdata FROM s_core_translations
		WHERE 
			objecttype = '$object'
		AND
			objectkey = $id
		AND
			objectlanguage = '$language'
		";
		if (!empty($this->sSYSTEM->sSubShop["fallback"])){
			
			$fallback = $this->sSYSTEM->sSubShop["fallback"];
			$sqlFallback = "
			SELECT objectdata FROM s_core_translations
			WHERE 
				objecttype = '$object'
			AND
				objectkey = $id
			AND
				objectlanguage = '$fallback'";
			
			$queryFallback = $this->sSYSTEM->sDB_CONNECTION->CacheGetRow($this->sSYSTEM->sCONFIG['sCACHETRANSLATIONS'],$sqlFallback);
		
			if (!empty($queryFallback["objectdata"])){
				$objectFallback = unserialize($queryFallback["objectdata"]);
				
			}
		}
		$queryTranslation = $this->sSYSTEM->sDB_CONNECTION->CacheGetRow($this->sSYSTEM->sCONFIG['sCACHETRANSLATIONS'],$sql);
		
		if (!empty($queryTranslation["objectdata"]) || !empty($objectFallback)){
			
			$object = unserialize($queryTranslation["objectdata"]);
			if (!is_array($object)) $object = array();
			$key = "";
			if (!empty($objectFallback)){
				$object = array_merge($objectFallback,$object);
			}
			
			foreach ($object as $translateKey => $value){
				if($map[$translateKey]){
					$key = isset($map[$translateKey]) ? $map[$translateKey] : "";
				}else {
					$key = $translateKey;
				}
				
				if (!empty($value)) $data[$key] = $this->fixEncoding($value);
			}
		}
		return $data;
		// Get Translation
	}
	
	/**
	 * Fix UTF-8 / iso-8859-1 encoding issues
	 * @param string $in_str
	 * @return string 
	 */
	public function fixEncoding($in_str)
	{
	  $cur_encoding = mb_detect_encoding($in_str) ;
	  if($cur_encoding == "UTF-8" && mb_check_encoding($in_str,"UTF-8"))
	    return $in_str;
	  else
	  	if ($this->sSYSTEM->sSubShop["encoding"]){
	    	return utf8_encode($in_str);
	  	}else {
	  		return $in_str;
	  	}
	}
	
	/**
	 * Get array of images from a certain configurator combination
	 * @param array $sArticle Associative array with all article data
	 * @param string $sCombination Currencly active combination
	 * @return array
	 */
	public function sGetConfiguratorImage($sArticle,$sCombination=""){
		
		if  (!empty($sArticle["sConfigurator"]) || !empty($sCombination)){
		
			$foundImage = false; $configuratorImages = false; $mainKey = 0;
			
			if (empty($sCombination)){
				$sArticle["image"]["description"] = $sArticle["image"]["res"]["description"];
				$sArticle["image"]["relations"] = $sArticle["image"]["res"]["relations"];
				foreach ($sArticle["sConfigurator"] as $key => $group){
					foreach ($group["values"] as $key2 => $option){
						
						$groupVal = $group["groupnameOrig"] ? $group["groupnameOrig"] : $group["groupname"];
						$groupVal = str_replace("/","",$groupVal);
						$groupVal = str_replace(" ","",$groupVal);
						$optionVal = $option["optionnameOrig"] ? $option["optionnameOrig"] : $option["optionname"];
						$optionVal = str_replace("/","",$optionVal);
						$optionVal = str_replace(" ","",$optionVal);
						if (!empty($option["selected"])){
							$referenceImages[strtolower($groupVal.":".str_replace(" ","",$optionVal))] = true;
						}
					}
				}
				foreach (array_merge($sArticle["images"],array(count($sArticle["images"])=>$sArticle["image"])) as $value){
					if (preg_match("/(.*){(.*)}/",$value["relations"])){
						$configuratorImages = true;
						
						break;
					}
				}
			}else {
				$referenceImages = array_flip(explode("$$",$sCombination));
				$sArticle = array("images"=>$sArticle,"image"=>array());
				foreach ($sArticle["images"] as $k => $value){
					if (preg_match("/(.*){(.*)}/",$value["relations"])){
						$configuratorImages = true;
						
						
					}
					if ($value["main"]==1){
						$mainKey = $k;
					}
				}
				if (empty($configuratorImages)){
					
					return $sArticle["images"][$mainKey];
				}
				
			}
			
			
			if (!empty($configuratorImages)){
				
				$sArticle["images"]  = array_merge($sArticle["images"],array(count($sArticle["images"])=>$sArticle["image"]));
				
				unset($sArticle["image"]);
			
				$debug = false;
				
				foreach ($sArticle["images"] as $imageKey => $image){
					if (empty($image["src"]["original"])) continue;
					$string = $image["relations"];
					// Parsing string
					$stringParsed = array();
//					$string = str_replace(" ","",$string);
					preg_match("/(.*){(.*)}/",$string,$stringParsed);
					$relation = $stringParsed[1];
					$available = explode("/",$stringParsed[2]);
					
					if (!@count($available)) $available = array(0=>$stringParsed[2]);
					
					$imageFailedCheck = array();
					
					foreach ($available as $checkKey => $checkCombination){
						$getCombination = explode(":",$checkCombination);
						$group = $getCombination[0];
						$option = $getCombination[1];
					
						if (isset($referenceImages[strtolower($checkCombination)])){
							
							$imageFailedCheck[] = true;
						
						}
					}
					if (count($imageFailedCheck) && count($imageFailedCheck)>=1 && count($available)>=1 && $relation == "||"){	// ODER Verknüpfunbg
						if (!empty($debug)) echo $string." matching combination\n";
						$sArticle["images"][$imageKey]["relations"] = "";
						$positions[$image["position"]] = $imageKey;
					}
					elseif (count($imageFailedCheck) == count($available) && $relation=="&"){	// UND VERKNÜPFUNG
						$sArticle["images"][$imageKey]["relations"] = "";
						$positions[$image["position"]] = $imageKey;
					}
					else {
						if (!empty($debug)) echo $string." doesnt match combination\n";
						unset($sArticle["images"][$imageKey]);
					}
				}
				ksort($positions);
				$posKeys = array_keys($positions);
				
				$sArticle["image"] = $sArticle["images"][$positions[$posKeys[0]]];
				unset($sArticle["images"][$positions[$posKeys[0]]]);
				
				if (!empty($sCombination)){
						return $sArticle["image"];
				}
				if (!empty($debug)) {
					print_r($referenceImages);
					print_r($resultImages);
					exit;
				}
			}else {
				
			}
		}
		
		if (!empty($sArticle["images"])){
			foreach ($sArticle["images"] as $key => $image){ 
			    if ($image["relations"]=="&{}" || $image["relations"]=="||{}"){ 
			            $sArticle["images"][$key]["relations"] = ""; 
			    } 
			} 
		}
		return $sArticle;
	}
	
	/**
	 * Auslesen von LiveShopping Konfigurationen
	 *
	 * @param string $mode fix|random|new|all 
	 * @param int $categoryID KategorieID
	 * @param string $article Artikeldaten
	 * @param bool $loadDetails Artikeldetails laden
	 * @param string $whereAdd Zusätzliche Where Bedingungen (ACHTUNG: Wird in der Methode nicht maskiert!)
	 * @param string $orderBy SQL-Order (wird bei $mode=random|new ignoriert) (ACHTUNG: Wird in der Methode nicht maskiert!)
	 * @param int $limit Anzahl der zu ladenen Datensätze (0=Keine Begrenzung)
	 * @return $article Aktualisierter Array
	 * 
	 * $mode:
	 * - fix > Liest einen Artikel aus ($article muss hierzu 'ordernumber' bzw. 'articleID' beinhalten)
	 * - random > Liest einen oder mehrere zufällige Artikel aus
	 * - new > Nach den neuesten Liveshopping Konfigurationen sortiert
	 * - gibt alle Artikel aus
	 */
	public function sGetLiveShopping($mode,$categoryID=0,$article=null, $loadDetails=false, $whereAdd='' , $orderBy='', $limit=1)
	{
		return $this->sSYSTEM->sMODULES["sLiveshopping"]->sGetLiveShopping($mode,$categoryID,$article, $loadDetails, $whereAdd , $orderBy, $limit);
	}
	
	//BUNDLE-FUNCTIONS
	/**
	 * Gibt alle (aktiven) Bundleartikel eines Artikels zurück
	 *
	 * $articleID = s_articles.id
	 * $loadArticleData = true=Laden weitere Artikeldetails der Bundleartikel
	 *
	 **/
	public function sGetArticleBundlesByArticleID($articleID, $loadArticleData=true)
	{
		return $this->sSYSTEM->sMODULES["sBundle"]->sGetArticleBundlesByArticleID($articleID, $loadArticleData);
	}

	/**
	 * Liest die Details eines Bundleartikels aus
	 *
	 * $bundleID = s_articles_bundles.id
	 * $loadArticleData = true=Laden weitere Artikeldetails der Bundleartikel
	 *
	 **/
	public function sGetArticleBundleByID($bundleID, $loadArticleData=true)
	{
		
		return $this->sSYSTEM->sMODULES["sBundle"]->sGetArticleBundleByID($bundleID, $loadArticleData);
	}
	
	
	/**
	 * Get array of images from a certain configurator combination
	 * @param array $sArticle Associative array with all article data
	 * @param string $sCombination Currencly active combination
	 * @return array
	 */
	public function sGetBundleBasketDiscount($ordernumber, $bundleID)
	{
		return $this->sSYSTEM->sMODULES["sBundle"]->sGetBundleBasketDiscount($ordernumber, $bundleID);
	}


}
	
?>