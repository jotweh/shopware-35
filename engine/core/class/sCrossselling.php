<?php
/**
 * Cross-selling functions
 * @link http://www.shopware.de
 * @package core
 * @subpackage class
 * @copyright (C) Shopware AG 2002-2010
 * @version Shopware 3.5.0
 */
class sCrossselling 
{
	var $sSYSTEM;
	var $sBlacklist;	// Array with blacklisted articles (already in basket) 
	
	public function sGetSimilaryShownArticles ($articleID,$limit=0)
	{
		if (empty($limit)){
			$limit = empty($this->sSYSTEM->sCONFIG['sMAXCROSSSIMILAR']) ? 4 : (int) $this->sSYSTEM->sCONFIG['sMAXCROSSSIMILAR'];
		}
		$articleID = (int) $articleID;
		$categoryID = $this->sSYSTEM->sLanguageData[$this->sSYSTEM->sLanguage]["parentID"] ? $this->sSYSTEM->sLanguageData[$this->sSYSTEM->sLanguage]["parentID"] : $this->sSYSTEM->sCONFIG["sCATEGORYPARENT"];
		$categoryID = intval($categoryID);
		
		if(!empty($this->sBlacklist))
		{
			$where = 'AND e1.articleID NOT IN ('.implode(',',$this->sBlacklist).')';
		}
		
		$sql = "
			SELECT e1.articleID as id, COUNT(e1.articleID) AS hits
			FROM s_emarketing_lastarticles AS e1,
			s_emarketing_lastarticles AS e2,
			s_articles_categories ac,
			s_articles a
			WHERE ac.categoryID=$categoryID
			AND ac.articleID=e1.articleID
			AND e2.articleID=$articleID
			AND e1.sessionID=e2.sessionID
			AND a.id=e1.articleID
			AND (
				SELECT articleID 
				FROM s_articles_avoid_customergroups 
				WHERE articleID = a.id AND customergroupID = ".$this->sSYSTEM->sUSERGROUPDATA["id"]."
			) IS NULL
			AND a.active=1
			AND a.mode=0
			$where
			GROUP BY e1.articleID
			ORDER BY hits DESC
			LIMIT $limit
		";
		return $this->sSYSTEM->sDB_CONNECTION->GetAll($sql);
	}

	public function sGetAlsoBoughtArticles ($articleID,$limit = 0)
	{
		if (empty($limit)){
			$limit = empty($this->sSYSTEM->sCONFIG['sMAXCROSSALSOBOUGHT']) ? 4 : (int) $this->sSYSTEM->sCONFIG['sMAXCROSSALSOBOUGHT'];
		}
		$articleID = (int) $articleID;
		$categoryID = $this->sSYSTEM->sLanguageData[$this->sSYSTEM->sLanguage]["parentID"] ? $this->sSYSTEM->sLanguageData[$this->sSYSTEM->sLanguage]["parentID"] : $this->sSYSTEM->sCONFIG["sCATEGORYPARENT"];
		$categoryID = intval($categoryID);
		
		if(!empty($this->sBlacklist))
		{
			$where = 'AND b1.articleID NOT IN ('.implode(',',$this->sBlacklist).')';
		}
		
		$sql = "
			SELECT b1.articleID AS id,COUNT(b1.articleID) AS sales
			FROM
				s_order_details AS b1,
				s_order_details AS b2,
				s_articles_categories ac,
				s_articles a
			WHERE  ac.categoryID=$categoryID
			AND ac.articleID=b1.articleID
			AND b2.articleID=$articleID
			AND a.id=b1.articleID
			AND (
				SELECT articleID 
				FROM s_articles_avoid_customergroups 
				WHERE articleID = a.id AND customergroupID = ".$this->sSYSTEM->sUSERGROUPDATA["id"]."
			) IS NULL
			AND a.active=1
			AND a.mode=0
			$where
			AND b1.orderID = b2.orderID AND b1.modus=0
			GROUP BY b1.articleID
			ORDER BY sales DESC LIMIT $limit
		";
		return $this->sSYSTEM->sDB_CONNECTION->GetAll($sql);;		
	}
}

?>