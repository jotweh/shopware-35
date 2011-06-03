<?php
/**
 * Url rewrite functions
 * @link http://www.shopware.de
 * @package core
 * @subpackage class
 * @copyright (C) Shopware AG 2002-2010
 * @version Shopware 3.5.0
 */
class	sRewriteTable
{
	public $sSYSTEM;
	
	protected $template;
	protected $data;
	
	public function sCleanupPath ($path, $remove_ds=true)
	{
		$replace = array(
			' & ' => '-und-',
			'ä'=>'ae',
			'ö'=>'oe',
			'ü'=>'ue',
			'Ü'=>'Ue',
			'Ä'=>'Ae',
			'Ö'=>'Oe',
			'ß'=>'ss',
			//'/'=>'-',
			':'=>'-',
			','=>'-',
			"'"=>'-',
			'"'=>'-',
			' '=>'-',
			'+'=>'-',
			//'&'=>'-',
			'à'=>'a',
			'á'=>'a',
			'è'=>'e',
			'é'=>'e',
			'ù'=>'u',
			'ú'=>'u',
			'ë'=>'e',
			'ç'=>'c',
			'Ç'=>'C',
			'&#351;'=>'s',
			'&#350;'=>'S',
			'&#287;'=>'g',
			'&#286;'=>'G',
			'&#304;'=>'i',
		);
		$path = html_entity_decode($path);
		$path = str_replace(array_keys($replace), array_values($replace), $path);
		if($remove_ds) {
			$path = str_replace('/', '-', $path);
		}
		$path = preg_replace('/&[a-z0-9#]+;/i', '', $path);
		$path = preg_replace('#[^0-9a-z-_./]#i','',$path);
		$path = preg_replace('/-+/','-',$path);
		return trim($path,'-');
	}

	public function sCreateRewriteTable($last_update)
	{		
		@ini_set('memory_limt','265M');
		@set_time_limit(0);
		$this->sSYSTEM->sMODULES['sArticles']->sCreateTranslationTable();
		
		$this->template = $this->sSYSTEM->sSMARTY;
		$this->template->register_function('sCategoryPath', array($this, 'sSmartyCategoryPath'));
		$this->data = $this->template->createData($smarty);
		
		$this->data->assign('sConfig', $this->sSYSTEM->sCONFIG);
		$this->data->assign('sRouter', $this);
		
		$this->sCreateRewriteTableCleanup();
		$this->sCreateRewriteTableStatic();
		$this->sCreateRewriteTableCategories();
		$last_update = $this->sCreateRewriteTableArticles($last_update);
		$this->sCreateRewriteTableContent();
		
		return $last_update;
	}
	
	protected function sCreateRewriteTableCleanup()
	{
		$sql = "
			DELETE ru FROM s_core_rewrite_urls ru
			LEFT JOIN s_cms_static cs
			ON ru.org_path LIKE CONCAT('sViewport=custom&sCustom=', cs.id)
			LEFT JOIN s_cms_support ct
			ON ru.org_path LIKE CONCAT('sViewport=ticket&sFid=', ct.id)
			LEFT JOIN s_emarketing_promotion_main ep
			ON ru.org_path LIKE CONCAT('sViewport=campaign&sCampaign=', ep.id)
			LEFT JOIN s_cms_groups cg
			ON ru.org_path LIKE CONCAT('sViewport=content&sContent=', cg.id)
			WHERE (ru.org_path LIKE 'sViewport=custom&sCustom=%'
			OR ru.org_path LIKE 'sViewport=ticket&sFid=%'
			OR ru.org_path LIKE 'sViewport=campaign&sCampaign=%'
			OR ru.org_path LIKE 'sViewport=content&sContent=%')
			AND cs.id IS NULL
			AND ct.id IS NULL
			AND ep.id IS NULL
			AND cg.id IS NULL
		";
		$this->sSYSTEM->sDB_CONNECTION->Execute($sql);
		
		$sql = "
			DELETE ru FROM s_core_rewrite_urls ru
			LEFT JOIN s_categories c
			ON c.id = REPLACE(ru.org_path, 'sViewport=cat&sCategory=', '')
			AND c.external=''
			WHERE ru.org_path LIKE 'sViewport=cat&sCategory=%'
			AND c.id IS NULL
		";
		$this->sSYSTEM->sDB_CONNECTION->Execute($sql);
		
		$sql = "
			DELETE ru FROM s_core_rewrite_urls ru
			LEFT JOIN s_articles a
			ON a.id = REPLACE(ru.org_path, 'sViewport=detail&sArticle=', '')
			WHERE ru.org_path LIKE 'sViewport=detail&sArticle=%'
			AND a.id IS NULL
		";
		$this->sSYSTEM->sDB_CONNECTION->Execute($sql);
	}
	
	protected function sCreateRewriteTableStatic()
	{
		if(!empty($this->sSYSTEM->sCONFIG['sSEOSTATICURLS']))
		{
			$static = array();
			$urls = $this->template->fetch('string:'.$this->sSYSTEM->sCONFIG['sSEOSTATICURLS'], $this->data);
			if(!empty($urls))
			foreach (explode("\n", $urls) as $url)
			{
				list($key, $value) = explode(',', trim($url));
				if(empty($key)||empty($value)) continue;
				$static[$key] = $value;
			}
		}
		elseif(!isset($this->sSYSTEM->sCONFIG['sSEOSTATICURLS']))
		{
			$static = array(
	    		'sViewport=sale&sAction=doSale' => 'Bestellung abgeschlossen',
	    		'sViewport=admin&sAction=orders' => $this->sSYSTEM->sCONFIG['sSnippets']['sIndexmyorders'],
	    		'sViewport=admin&sAction=downloads' => $this->sSYSTEM->sCONFIG['sSnippets']['sIndexmyinstantdownloads'],
	    		'sViewport=admin&sAction=billing' => $this->sSYSTEM->sCONFIG['sSnippets']['sIndexchangebillingaddress'],
	    		'sViewport=admin&sAction=shipping' => $this->sSYSTEM->sCONFIG['sSnippets']['sIndexchangedeliveryaddress'],
	    		'sViewport=admin&sAction=payment' => $this->sSYSTEM->sCONFIG['sSnippets']['sIndexchangepayment'],
	    		'sViewport=admin&sAction=ticketview' => $this->sSYSTEM->sCONFIG['sSnippets']['sTicketSysSupportManagement'],
	    		'sViewport=logout' => $this->sSYSTEM->sCONFIG['sSnippets']['sIndexlogout'],
	    		'sViewport=support&sFid='.$this->sSYSTEM->sCONFIG['sINQUIRYID'].'&sInquiry=basket' => $this->sSYSTEM->sCONFIG['sSnippets']['sBasketInquiry'],
	    		'sViewport=support&sFid='.$this->sSYSTEM->sCONFIG['sINQUIRYID'].'&sInquiry=detail' => $this->sSYSTEM->sCONFIG['sSnippets']['sArticlequestionsaboutarticle']
	    	);
	    	foreach($this->sSYSTEM->sCONFIG['sViewports'] as $viewportID => $viewport)
	        {
	        	if($viewportID=='search') continue;
	        	if(!isset($static['sViewport='.$viewportID]))
	        		$static['sViewport='.$viewportID] = $viewport['name'];
	        }
		}
		foreach($static as $org_path => $name)
        {
        	$path = $this->sCleanupPath($name);
        	$this->sInsertUrl($org_path, $path);
        }
	}
	
	protected function sCreateRewriteTableCategories()
	{
		if(empty($this->sSYSTEM->sCONFIG['sROUTERCATEGORYTEMPLATE'])) return;

		$categories_ids = $this->sSYSTEM->sLanguageData[$this->sSYSTEM->sLanguage]['parentID'];
		while (!empty($categories_ids))
		{
	    	$sql = "SELECT id as `key`, c.* FROM s_categories c WHERE c.parent!=1 AND c.active=1 AND c.parent IN ($categories_ids)";
			$categories = $this->sSYSTEM->sDB_CONNECTION->GetAssoc($sql);
			if(empty($categories)) break;
			
			$categories_ids = implode(',',array_keys($categories));
			
			foreach ($categories as $row)
	        {
	        	if(!empty($row['external'])) continue;
	        	$org_path = 'sViewport=cat&sCategory='.$row['id'];
	        	
	        	$this->data->assign('sCategory', $row);
	        	$path = $this->template->fetch('string:'.$this->sSYSTEM->sCONFIG['sROUTERCATEGORYTEMPLATE'], $this->data);
	        	$path = $this->sCleanupPath($path, false);
	        	
	        	$this->sInsertUrl($org_path, $path);
	        }
		}
	}
	
	protected function sCreateRewriteTableArticles($last_update)
	{
		if(empty($this->sSYSTEM->sCONFIG['sROUTERARTICLETEMPLATE'])) {
			return $last_update;
		}
		
		$sql = 'UPDATE `s_articles` SET `changetime`= NOW() WHERE `changetime`=?';
	    Shopware()->Db()->query($sql, array('0000-00-00 00:00:00'));
		    	
    	$sql = '
			SELECT a.*, IFNULL(atr.name, a.name) as name, d.ordernumber, d.suppliernumber, s.name as supplier, datum as date, releasedate, changetime as changed,
				at.attr1, at.attr2, at.attr3, at.attr4, at.attr5, at.attr6, at.attr7, at.attr8, at.attr9, at.attr10,
				at.attr11, at.attr12, at.attr13, at.attr14, at.attr15, at.attr16, at.attr17, at.attr18, at.attr19, at.attr20
			FROM `s_articles` a
			INNER JOIN s_articles_categories ac
			ON ac.articleID=a.id
			AND ac.categoryID=?
			INNER JOIN s_articles_details d
			ON d.articleID=a.id
			AND kind=1
			INNER JOIN s_articles_attributes at
			ON at.articledetailsID=d.id
			LEFT JOIN s_articles_translations atr
			ON atr.articleID=a.id
			AND atr.languageID=?
			LEFT JOIN s_articles_supplier s
			ON s.id=a.supplierID
			WHERE a.active=1
			AND a.changetime > ?
			ORDER BY a.changetime, a.id
			LIMIT 1000
		';
		$result = $this->sSYSTEM->sDB_CONNECTION->Execute($sql, array(
			$this->sSYSTEM->sLanguageData[$this->sSYSTEM->sLanguage]["parentID"],
			$this->sSYSTEM->sSubShop['id'],
			$last_update
		));
		
		if($result !== false) {
	        while ($row = $result->FetchRow()) {
	        	$this->data->assign('sArticle', $row);
	        	$path = $this->template->fetch('string:'.$this->sSYSTEM->sCONFIG['sROUTERARTICLETEMPLATE'], $this->data);
	        	$path = $this->sCleanupPath($path, false);
	        	
	        	$org_path = 'sViewport=detail&sArticle='.$row['id'];
	        	$this->sInsertUrl($org_path, $path);
	        	$last_update = $row['changed'];
	        	$last_id = $row['id'];
	        }
    	}
    	
    	if(!empty($last_id)) {
    		$sql = 'UPDATE s_articles SET changetime=DATE_ADD(changetime, INTERVAL 1 SECOND) WHERE changetime=? AND id > ?';
    		$this->sSYSTEM->sDB_CONNECTION->Execute($sql, array($last_update, $last_id));
    	}
    	
    	return $last_update;
	}
	
	protected function sCreateRewriteTableContent()
	{
    	$sql = 'SELECT id, description as name FROM `s_emarketing_promotion_main`';
		$result = $this->sSYSTEM->sDB_CONNECTION->Execute($sql);
		if($result!==false)
    	{
	        while ($row = $result->FetchRow())
	        {
	        	$org_path = 'sViewport=campaign&sCampaign='.$row['id'];
	        	$path = $this->sCleanupPath($row['name']);
	        	$this->sInsertUrl($org_path, $path);
	        }
    	}
    	
		$sql = 'SELECT id, name, ticket_typeID FROM `s_cms_support`';
		$result = $this->sSYSTEM->sDB_CONNECTION->Execute($sql);
		if($result!==false)
    	{
	        while ($row = $result->FetchRow())
	        {
	        	$org_path = 'sViewport=ticket&sFid='.$row['id'];
	        	$path = $this->sCleanupPath($row['name']);
	        	$this->sInsertUrl($org_path, $path);
	        }
    	}
    	
		$sql = 'SELECT id, description as name FROM `s_cms_static` WHERE link=\'\'';
		$result = $this->sSYSTEM->sDB_CONNECTION->Execute($sql);
		if($result!==false)
    	{
	        while ($row = $result->FetchRow())
	        {
	        	$org_path = 'sViewport=custom&sCustom='.$row['id'];
	        	$path = $this->sCleanupPath($row['name']);
	        	$this->sInsertUrl($org_path, $path);
	        }
    	}
    	
		$sql = 'SELECT id, description as name FROM `s_cms_groups`';
		$result = $this->sSYSTEM->sDB_CONNECTION->Execute($sql);
		if($result!==false)
    	{
	        while ($row = $result->FetchRow())
	        {
	        	$org_path = 'sViewport=content&sContent='.$row['id'];
	        	$path = $this->sCleanupPath($row['name']);
	        	$this->sInsertUrl($org_path, $path);
	        }
    	}
	}
	
	public function sInsertUrl($org_path, $path)
	{
		if(empty($path) || empty($org_path)) {
			return false;
		}
		$sql_rewrite = 'UPDATE s_core_rewrite_urls SET main=0 WHERE org_path=? AND path!=? AND subshopID=?';
		$this->sSYSTEM->sDB_CONNECTION->Execute($sql_rewrite, array($org_path, $path, $this->sSYSTEM->sSubShop['id']));
		$sql_rewrite = '
			INSERT IGNORE INTO s_core_rewrite_urls (org_path, path, main, subshopID)
			VALUES (?, ?, 1, ?)
			ON DUPLICATE KEY UPDATE main=1
		';
		$this->sSYSTEM->sDB_CONNECTION->Execute($sql_rewrite, array($org_path, $path, $this->sSYSTEM->sSubShop['id']));
	}
	
	public function sSmartyCategoryPath($params)
	{
		if(!empty($params['articleID']))
			$parts = $this->sCategoryPathByArticleID($params['articleID'], isset($params['categoryID']) ? $params["categoryID"] : 0);
		elseif(!empty($params['categoryID']))
			$parts = $this->sCategoryPath($params['categoryID']);
		if(empty($params['separator']))
			$params['separator'] = '/';
		foreach ($parts as &$part)
		{
			$part = str_replace($params['separator'],'',$part);
		}
		$parts = implode($params['separator'], $parts);
		return $parts;
	}
	
	public function sCategoryPath ($categoryID)
	{
		$path = array();
		while (!empty($categoryID))
		{
			$sql = "
			    SELECT parent as categoryID, description
			    FROM s_categories c
				WHERE id=$categoryID
				AND parent!=1
				AND active=1
			";
			$category = $this->sSYSTEM->sDB_CONNECTION->GetRow($sql);
			if(empty($category))
				break;
			$path[] = htmlspecialchars_decode($category["description"]);
			$categoryID = $category["categoryID"];
		}
		$path = array_reverse($path);
		return $path;
	}
	
	public function sCategoryPathByArticleID ($articleID, $categoryID=null)
	{
		if(empty($categoryID))
		{
			$categoryID = $this->sSYSTEM->sLanguageData[$this->sSYSTEM->sLanguage]["parentID"];
		}
		$path = array();
		while (!empty($categoryID))
		{
			$sql = "
			    SELECT c.id as categoryID, c.description
			    FROM s_articles_categories a, s_categories c
				WHERE a.articleID=$articleID 
				AND c.parent=$categoryID 
				AND a.categoryID = c.id
				AND c.active=1
				ORDER BY a.id ASC LIMIT 1
			";
			$category = $this->sSYSTEM->sDB_CONNECTION->GetRow($sql);
			if(empty($category))
				break;
			$path[] = htmlspecialchars_decode($category["description"]);
			$categoryID = $category["categoryID"];
		}
		return $path;
	}
}