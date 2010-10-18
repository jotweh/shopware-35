<?php
class sViewportSearchFuzzy{
	var $sSYSTEM;
	var $sMainCategoryID;
	
	function sRender()
	{
		
		$this->sMainCategoryID = $this->sSYSTEM->sLanguageData[$this->sSYSTEM->sLanguage]["parentID"] ? $this->sSYSTEM->sLanguageData[$this->sSYSTEM->sLanguage]["parentID"] : $this->sSYSTEM->sCONFIG["sCATEGORYPARENT"];
		
		/*
		$sRequests['sFilter']['supplier']
		$sRequests['sFilter']['category']
		$sRequests['sFilter']['price']
		$sRequests['sSort']
		$sRequests['sPage']
		$sRequests['sPerPage']
		$sRequests['sOrder']
		$sRequests['sSearch']
		*/
		
		$sRequests['sFilter']['supplier'] = intval($this->sSYSTEM->_GET['sFilter_supplier']);
		$sRequests['sFilter']['category'] = intval($this->sSYSTEM->_GET['sFilter_category']);
		$sRequests['sFilter']['price'] = intval($this->sSYSTEM->_GET['sFilter_price']);
		$sRequests['sFilter']['propertygroup'] = $this->sSYSTEM->_GET['sFilter_propertygroup'];
		
		$sRequests['sSort'] = intval($this->sSYSTEM->_GET['sSort']);
		if(!empty($this->sSYSTEM->_GET['sPage']))
			$sRequests['sPage'] = (int) $this->sSYSTEM->_GET['sPage'];
		else
			$sRequests['sPage'] = 0;
			
		if(!empty($this->sSYSTEM->_GET['sPerPage']))
			$sRequests['sPerPage'] = (int) $this->sSYSTEM->_GET['sPerPage'];
		elseif(!empty($this->sSYSTEM->sCONFIG['sFUZZYSEARCHRESULTSPERPAGE']))
			$sRequests['sPerPage'] = (int) $this->sSYSTEM->sCONFIG['sFUZZYSEARCHRESULTSPERPAGE'];
		else
			$sRequests['sPerPage'] = 8;
			
		if(!empty($this->sSYSTEM->sCONFIG['sFUZZYSEARCHSELECTPERPAGE']))
			$sPerPage = preg_split('/[^0-9]/', (string) $this->sSYSTEM->sCONFIG['sFUZZYSEARCHSELECTPERPAGE'], -1, PREG_SPLIT_NO_EMPTY);
		else
			$sPerPage = array(8, 16, 24, 48);
			
		if(!empty($this->sSYSTEM->sCONFIG['sFUZZYSEARCHPRICEFILTER']))
			$sPriceFilter = preg_split('/[^0-9]/', (string) $this->sSYSTEM->sCONFIG['sFUZZYSEARCHPRICEFILTER'], -1, PREG_SPLIT_NO_EMPTY);
		else
			$sPriceFilter = array(5, 10, 20, 50, 100, 300, 600, 1000, 1500, 2500, 3500, 5000);
		
		$tmp = array();
		$last = 0;
		foreach ($sPriceFilter as $key => $price)
		{
			$tmp[$key+1] = array('start'=>$last, 'end'=>$price);
			$last = $price;
		}
		$sPriceFilter = $tmp;
		unset($tmp, $last, $key, $price);
			
		$sRequests['sOrder'] = intval($this->sSYSTEM->_GET['sOrder']);
		$sRequests['sSearch'] = urldecode(trim(str_replace("+"," ",strip_tags(htmlspecialchars_decode(html_entity_decode(stripslashes($this->sSYSTEM->_GET['sSearch']),ENT_QUOTES))))));
		
		if(function_exists('mb_detect_encoding')&&function_exists('mb_check_encoding')
			&&mb_detect_encoding($sRequests['sSearch'])=='UTF-8'&&mb_check_encoding($sRequests['sSearch'], 'UTF-8'))
			$sRequests['sSearch'] = utf8_decode($sRequests['sSearch']);
			
		if(!empty($sRequests['sSearch'])&&strlen($sRequests['sSearch'])>=(empty($this->sSYSTEM->sCONFIG["sMINSEARCHLENGHT"]) ? 2 : (int) $this->sSYSTEM->sCONFIG["sMINSEARCHLENGHT"]))
		{
			$sql_search_like = $this->sSYSTEM->sDB_CONNECTION->qstr($sRequests['sSearch']."%");
			$sql_search = $this->sSYSTEM->sDB_CONNECTION->qstr($sRequests['sSearch']);
			$sql = "
				SELECT DISTINCT articleID 
				FROM
				(
					SELECT DISTINCT articleID
					FROM s_articles_groups_value
					WHERE ordernumber = $sql_search
					GROUP BY articleID
					LIMIT 2
					UNION 
					SELECT DISTINCT articleID
					FROM s_articles_details
					WHERE ordernumber = $sql_search
					GROUP BY articleID
					LIMIT 2
				) as a
				LIMIT 2
			";
			
			$articles = $this->sSYSTEM->sDB_CONNECTION->GetCol($sql);
			if(empty($articles))
			{
				$sql = "
					SELECT DISTINCT articleID 
					FROM
					(
						SELECT DISTINCT articleID
						FROM s_articles_groups_value
						WHERE ordernumber = $sql_search
						GROUP BY articleID
						LIMIT 2
						UNION 
						SELECT DISTINCT articleID
						FROM s_articles_details
						WHERE ordernumber = $sql_search
						OR $sql_search LIKE CONCAT(ordernumber,'%')
						GROUP BY articleID
						LIMIT 2
					) as a
					LIMIT 2
				";
				
				$articles = $this->sSYSTEM->sDB_CONNECTION->GetCol($sql);
			}
		}
		
		if(!empty($articles)&&count($articles)==1)
		{
			$sql = "SELECT articleID FROM s_articles_categories WHERE categoryID=$this->sMainCategoryID AND articleID={$articles[0]}";
			$articles = $this->sSYSTEM->sDB_CONNECTION->GetCol($sql);
		}
		if(!empty($articles)&&count($articles)==1)
		{
			$location = $this->sSYSTEM->rewriteLink(array(2=>$this->sSYSTEM->sCONFIG["sBASEFILE"]."?sViewport=detail&sArticle={$articles[0]}"),true);
			if (!headers_sent()) {
				header("Location: $location");
			}
			else 
			{
				$html  = "<?xml version=\"1.0\" encoding=\"iso-8859-1\" ?>\r\n";
				$html .= "<!DOCTYPE html\r\n";
				$html .= "	PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\"\r\n";
				$html .= "	\"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">\r\n";
				$html .= "<html xmlns=\"http://www.w3.org/1999/xhtml\" lang=\"de\" xml:lang=\"de\">\r\n";
				$html .= " <head>\r\n";
				$html .= "  <title>Sie werden weitergeleitet auf $location</title>\r\n";
				$html .= "  <meta http-equiv=\"Refresh\" content=\"5; URL=$location\">\r\n";
				$html .= " </head>\r\n";
				$html .= " <body>Klicken Sie <a href=\"$location\">Hier</a> falls Sie in 5 Sekunden nicht weiter geleitet werden.</body>\r\n";
				$html .= "</html>\r\n";
				echo $html;
			}
			exit();
		}
		
		/*
		Licence Check
		*/
		if (!$this->sSYSTEM->sCheckLicense("","",$this->sSYSTEM->sLicenseData["sFUZZY"])){
			include("s_search.php");
			$sViewportSearch = new sViewportSearch();
			$sViewportSearch->sSYSTEM =& $this->sSYSTEM;
			return $sViewportSearch->sRender();
		}
		
		$sRequests['sSearchOrginal'] = $sRequests['sSearch'];
		$sLinks['sLink'] = $this->sSYSTEM->sCONFIG["sBASEFILE"]."?sViewport=searchFuzzy&sSearch=".rawurlencode(str_replace(" ","+",$sRequests['sSearchOrginal']));
		$sRequests['sSearchOrginal'] = htmlspecialchars($sRequests['sSearchOrginal']);

		$sLinks['sSearch'] = $this->sSYSTEM->rewriteLink(array(2=>$this->sSYSTEM->sCONFIG["sBASEFILE"]."?sViewport=searchFuzzy"),true);
			
		$sLinks['sPage'] = $sLinks['sLink'];
		$sLinks['sPerPage'] = $sLinks['sLink'];
		$sLinks['sSort'] = $sLinks['sLink'];
		$sLinks['sFilter']['category'] = $sLinks['sLink'];
		$sLinks['sFilter']['supplier'] = $sLinks['sLink'];
		$sLinks['sFilter']['price'] = $sLinks['sLink'];
		$sLinks['sFilter']['propertygroup'] = $sLinks['sLink'];
		
		if(!empty($sRequests['sFilter']['supplier']))
		{
			$sLinks['sPage'] .= "&sFilter_supplier=".$sRequests['sFilter']['supplier'];
			$sLinks['sPerPage'] .= "&sFilter_supplier=".$sRequests['sFilter']['supplier'];
			$sLinks['sSort'] .= "&sFilter_supplier=".$sRequests['sFilter']['supplier'];
			$sLinks['sFilter']['category'] .= "&sFilter_supplier=".$sRequests['sFilter']['supplier'];
			$sLinks['sFilter']['price'] .= "&sFilter_supplier=".$sRequests['sFilter']['supplier'];
			$sLinks['sFilter']['propertygroup'] .= "&sFilter_supplier=".$sRequests['sFilter']['supplier'];
		}
		if(!empty($sRequests['sFilter']['category']))
		{
			$sLinks['sPage'] .= "&sFilter_category=".$sRequests['sFilter']['category'];
			$sLinks['sPerPage'] .= "&sFilter_category=".$sRequests['sFilter']['category'];
			$sLinks['sSort'] .= "&sFilter_category=".$sRequests['sFilter']['category'];
			$sLinks['sFilter']['supplier'] .= "&sFilter_category=".$sRequests['sFilter']['category'];
			$sLinks['sFilter']['price'] .= "&sFilter_category=".$sRequests['sFilter']['category'];
			$sLinks['sFilter']['propertygroup'] .= "&sFilter_category=".$sRequests['sFilter']['category'];
		}
		if(!empty($sRequests['sFilter']['price']))
		{
			$sLinks['sPage'] .= "&sFilter_price=".$sRequests['sFilter']['price'];
			$sLinks['sPerPage'] .= "&sFilter_price=".$sRequests['sFilter']['price'];
			$sLinks['sSort'] .= "&sFilter_price=".$sRequests['sFilter']['price'];
			$sLinks['sFilter']['category'] .= "&sFilter_price=".$sRequests['sFilter']['price'];
			$sLinks['sFilter']['supplier'] .= "&sFilter_price=".$sRequests['sFilter']['price'];
			$sLinks['sFilter']['propertygroup'] .= "&sFilter_price=".$sRequests['sFilter']['price'];
		}
		if(!empty($sRequests['sFilter']['propertygroup']))
		{
			$value = urlencode($sRequests['sFilter']['propertygroup']);
			$sLinks['sPage'] .= "&sFilter_propertygroup=".$value;
			$sLinks['sPerPage'] .= "&sFilter_propertygroup=".$value;
			$sLinks['sSort'] .= "&sFilter_propertygroup=".$value;
			$sLinks['sFilter']['category'] .= "&sFilter_propertygroup=".$value;
			$sLinks['sFilter']['supplier'] .= "&sFilter_propertygroup=".$value;
			$sLinks['sFilter']['price'] .= "&sFilter_propertygroup=".$value;
		}
		if(!empty($sRequests['sOrder']))
		{
			$sLinks['sPage'] .= "&sOrder=".$sRequests['sOrder'];
			$sLinks['sPerPage'] .= "&sOrder=".$sRequests['sOrder'];
			$sLinks['sFilter']['__'] .= "&sOrder=".$sRequests['sOrder'];
		}
		if(!empty($sRequests['sSort']))
		{
			$sLinks['sPage'] .= "&sSort=".$sRequests['sSort'];
			$sLinks['sPerPage'] .= "&sSort=".$sRequests['sSort'];	
			$sLinks['sFilter']['__'] .= "&sSort=".$sRequests['sSort'];
		}
		if(!empty($sRequests['sPerPage'])&&$sRequests['sPerPage']!=$sPerPage[0])
		{
			$sLinks['sPage'] .= "&sPerPage=".$sRequests['sPerPage'];
			$sLinks['sSort'] .= "&sPerPage=".$sRequests['sPerPage'];
			$sLinks['sFilter']['__'] .= "&sPerPage=".$sRequests['sPerPage'];
		}
		
		$sLinks['sFilter']['price'] .= $sLinks['sFilter']['__'];
		$sLinks['sFilter']['category'] .= $sLinks['sFilter']['__'];
		$sLinks['sFilter']['supplier'] .= $sLinks['sFilter']['__'];
		$sLinks['sFilter']['propertygroup'] .= $sLinks['sFilter']['__'];
		
		$sLinks['sSort'] = $this->sSYSTEM->rewriteLink(array(2=>$sLinks['sSort']),true);
		$sLinks['sSupplier'] = $sLinks['sSort'];
		
		if (strlen($sRequests['sSearch'])>=(int)$this->sSYSTEM->sCONFIG["sMINSEARCHLENGHT"])
		{	
			
			$this->sSYSTEM->sMODULES['sSearch']->sInit();
			$this->sSYSTEM->sMODULES['sSearch']->sPriceFilter = $sPriceFilter;
			$sSearchResults = $this->sSYSTEM->sMODULES['sSearch']->sStartSearch($sRequests);
			
			$sql = "
				INSERT INTO s_statistics_search (datum, searchterm, results)
				VALUES (NOW(), ?, ?)
			";
			$this->sSYSTEM->sDB_CONNECTION->Execute($sql,array(
				implode(' ', $sSearchResults['sSearchTerms']),
				empty($sSearchResults['sArticlesCountAll']) ? 0 : $sSearchResults['sArticlesCountAll']
			));		
			
			$sPages = array();
			for($i=0,$page=0;$i<$sSearchResults['sArticlesCount'];$i+=$sRequests['sPerPage'],$page++)
			{
				if($sRequests['sPage']-3<$page&&$sRequests['sPage']+3>$page)
					$sPages['pages'][] = $page;
			}
			$sPages['count'] = $page;
			if($sRequests['sPage']>0)
				$sPages['before'] = $sPages['bevor'] = $sRequests['sPage']-1;
			if($sRequests['sPage']<$sPages['count']-1)
				$sPages['next'] = $sRequests['sPage']+1;
			$articles = array();
			foreach ($sSearchResults["sArticles"] as $articleID){
				$article = $this->sSYSTEM->sMODULES['sArticles']->sGetPromotionById ('fix', 0, (int)$articleID);
				if (!empty($article["articleID"])) {
					$articles[] = $article;
				}
			}
			$sSearchResults["sArticles"] = $articles;

			$sCategoriesTree = $this->getCatogerieTree($sSearchResults['sLastCategory']);
			
			$variables = array(
				"sRequests"=>$sRequests,
				"sSearchResults"=>$sSearchResults,
				"sPerPage" => $sPerPage,
				"sLinks" => $sLinks,
				"sPages" => $sPages,
				"sPriceFilter" => $sPriceFilter,
				"sCategoriesTree" => $sCategoriesTree
			);
		}
		
		$variables["sBreadcrumb"] = array(
			0=>array("name"=>$this->sSYSTEM->sCONFIG['sViewports'][$this->sSYSTEM->_GET["sViewport"]]["name"]),
			1=>array("name"=>$sRequests['sSearchOrginal'])
		);
		
		$templates = array(
			"sContainer"=>"/search/search_middle.tpl",
			"sContainerRight"=>"/search/search_right_fuzzy.tpl"
		);
		
		
		$this->sSYSTEM->sSMARTY->register_function('partition', array('sViewportSearchFuzzy','partition'));
		$this->sSYSTEM->sSMARTY->register_function('slice', array('sViewportSearchFuzzy','slice'));
		return array("templates"=>$templates,"variables"=>$variables);
	}
	function getCatogerieTree ($id)
	{
		$sql = "
			SELECT 
				`id` ,
				`description`,
				`parent`
			FROM `s_categories`
			WHERE `id`='$id'
		";
		$cat = $this->sSYSTEM->sDB_CONNECTION->CacheGetRow($sql);
		if(empty($cat['id'])||$id==$cat['parent']||$id==$this->sMainCategoryID)
			return array();
		else 
		{
			$cats = $this->getCatogerieTree($cat['parent']);
			$cats[$id] = $cat;
			return $cats;
		}
	}
	function partition($params, &$smarty) {
		$list = $params['array'];
		$p = $params['parts'];
	    $listlen = count( $list );
	    $partlen = floor( $listlen / $p );
	    $partrem = $listlen % $p;
	    $partition = array();
	    $mark = 0;
	    for ($px = 0; $px < $p; $px++) {
	        $incr = ($px < $partrem) ? $partlen + 1 : $partlen;
	        $partition[$px] = array_slice( $list, $mark, $incr );
	        $mark += $incr;
	    }
	    $smarty->assign($params['assign'],$partition);
	    return;
	}
	function slice($params, &$smarty) {
		$array = array_slice($params['array'],$params['offset'],$params['lenght']);
		$smarty->assign($params['assign'],$array);
	    return;
	}
}
?>