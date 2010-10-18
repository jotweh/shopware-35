<?php
/**
 * Provides static pages / content listings / details
 * @link http://www.shopware.de
 * @package core
 * @subpackage class
 * @copyright (C) Shopware AG 2002-2010
 * @version Shopware 3.5.0
 */
class sCms
{
	/**
	* Pointer to Shopware-Core-public functions
	*
	* @var    object
	* @access private
	*/
	var $sSYSTEM;
	
	 /**
	 * Eine bestimmte, statische Seite auslesen (z.B. AGB etc.)
	 * @access public
	 * @return array
	 */
	public function sGetStaticPage($staticID=null)
	{	
		if(empty($staticID)&&!empty($this->sSYSTEM->_GET['sCustom'])) $staticID = $this->sSYSTEM->_GET['sCustom'];
		if(empty($staticID)) return false;
		// Query all information of the static-template
		$queryStaticTemplate = $this->sSYSTEM->sDB_CONNECTION->CacheGetRow($this->sSYSTEM->sCONFIG['sCACHESTATIC'],"
		SELECT * FROM s_cms_static WHERE id=?",array($this->sSYSTEM->_GET['sCustom']));
		if (empty($queryStaticTemplate)){
			return;
		}
		return $queryStaticTemplate;
	}
	
	 /**
	 * Dynamische Inhalte einer Gruppe auslesen
	 * @param int $group Gruppen-ID
	 * @param int $sPage Aktuelle Seite
	 * @access public
	 * @return array
	 */
	public function sGetDynamicContentByGroup($group,$sPage=1){
		
		// Get count of topics
		$sql = "
		SELECT COUNT(id) as countTopics FROM s_cms_content WHERE groupID=? GROUP BY groupID
		";
		
		
		$getCountTopics = $this->sSYSTEM->sDB_CONNECTION->CacheGetRow($this->sSYSTEM->sCONFIG['sCACHESTATIC'],$sql,array($group));
		
		if ($sPage > $getCountTopics["countTopics"] || $sPage <= 0 ) $sPage = 1;
		
		$limitStart = $sPage * $this->sSYSTEM->sCONFIG["sCONTENTPERPAGE"] - $this->sSYSTEM->sCONFIG["sCONTENTPERPAGE"];
		$limitEnd = intval($this->sSYSTEM->sCONFIG["sCONTENTPERPAGE"]);
		
		// Calculate number of pages
		$numberPages = intval($getCountTopics["countTopics"] / $this->sSYSTEM->sCONFIG["sCONTENTPERPAGE"]) != $getCountTopics["countTopics"] / $this->sSYSTEM->sCONFIG["sCONTENTPERPAGE"] ? intval($getCountTopics["countTopics"] / $this->sSYSTEM->sCONFIG["sCONTENTPERPAGE"])+1 : intval($getCountTopics["countTopics"] / $this->sSYSTEM->sCONFIG["sCONTENTPERPAGE"]);
			
		// Make Array with page-structure to render in template
		$pages = array();
		
		for ($i=1;$i<=$numberPages;$i++){
			if ($i==$sPage){
				$pages["numbers"][$i]["markup"] = true;
			}else {
				$pages["numbers"][$i]["markup"] = false;
			}
			$pages["numbers"][$i]["value"] = $i;
			$pages["numbers"][$i]["link"] = $this->sSYSTEM->sCONFIG['sBASEFILE'].$this->sSYSTEM->sBuildLink(array("sPage"=>$i),false);
		} 
		
		
		// Query - Topic
		$sql = "
			SELECT id, description,text,img,link,attachment, datum as `date`, DATE_FORMAT(datum,'%d.%m.%Y') AS datumFormated
			FROM s_cms_content WHERE groupID=? 
			ORDER BY datum DESC
		";
		$sql = Shopware()->Db()->limit($sql, $limitEnd, $limitStart);
		
		$queryDynamic = Shopware()->Db()->fetchAll($sql, array($group));
				
		foreach ($queryDynamic as $dynamicKey => $dynamicValue){
			$tempDatum = explode(".",$queryDynamic[$dynamicKey]["datum"]);
			
			// Building Link for more information page (optional)
			$queryDynamic[$dynamicKey]["linkDetails"] = $this->sSYSTEM->sCONFIG['sBASEFILE'].$this->sSYSTEM->sBuildLink(array("sCid"=>$dynamicValue["id"]),false);
			
			// Get Image
			if ($queryDynamic[$dynamicKey]["img"]){
				$queryDynamic[$dynamicKey]["imgBig"] = $this->sSYSTEM->sPathCmsImg.$queryDynamic[$dynamicKey]["img"].".jpg";
				$queryDynamic[$dynamicKey]["img"] = $this->sSYSTEM->sPathCmsImg.$queryDynamic[$dynamicKey]["img"]."Thumb.jpg";
			}
			// Get attachment
			if ($queryDynamic[$dynamicKey]["attachment"]){
				$queryDynamic[$dynamicKey]["attachment"] =  "http://".$this->sSYSTEM->sCONFIG["sBASEPATH"].$this->sSYSTEM->sCONFIG["sCMSFILES"]."/".$queryDynamic[$dynamicKey]["attachment"];
			}
			
			$queryDynamic[$dynamicKey]["dateExploded"] = $tempDatum;
		}
		return array("sContent"=>$queryDynamic,"sPages"=>$pages);
	}
	
	 /**
	 * Detailinformationen eines Gruppen-Eintrags
	 * @param int $group Gruppen-ID
	 * @param int $id ID des Eintrags
	 * @access public
	 * @return array
	 */
	public function sGetDynamicContentById($group,$id){

		// Query - Topic
		$sql = "
		SELECT id, description,text,img,link,attachment,DATE_FORMAT(datum,'%d.%m.%Y') AS datum FROM s_cms_content WHERE groupID=? 
		AND id=?
		";
	
		$queryDynamic = $this->sSYSTEM->sDB_CONNECTION->CacheGetRow($this->sSYSTEM->sCONFIG['sCACHESTATIC'],$sql,array($group,$id));
		
		if ($queryDynamic["id"]){
			$tempDatum = explode(".",$queryDynamic["datum"]);
			
			// Building Link for more information page (optional)
			$queryDynamic["linkDetails"] = $this->sSYSTEM->sCONFIG['sBASEFILE'].$this->sSYSTEM->sBuildLink(array("sCid"=>$dynamicValue["id"]),false);
			
			// Get Image
			if ($queryDynamic["img"]){
				$queryDynamic["imgBig"] = $this->sSYSTEM->sPathCmsImg.$queryDynamic["img"].".jpg";
				$queryDynamic["img"] = $this->sSYSTEM->sPathCmsImg.$queryDynamic["img"]."Thumb.jpg";
			}
			// Get attachment
			if ($queryDynamic["attachment"]){
				$queryDynamic["attachment"] =  "http://".$this->sSYSTEM->sCONFIG["sBASEPATH"].$this->sSYSTEM->sCONFIG["sCMSFILES"]."/".$queryDynamic["attachment"];
			}
			
			$queryDynamic["dateExploded"] = $tempDatum;
		}else {
			// Error-Handler
			$this->sSYSTEM->E_CORE_WARNING ("sCMS##sGetContentById","Content with id $id not found");
			return false;
		}
		
		return array("sContent"=>$queryDynamic);
	}
	
	 /**
	 * Name einer Gruppe anhand der ID
	 * @param int $group Gruppen-ID
	 * @access public
	 * @return string Name
	 */
	public function sGetDynamicGroupName ($group){
		$sql = "
		SELECT description FROM s_cms_groups WHERE id=?
		";
	
		$queryDynamic = $this->sSYSTEM->sDB_CONNECTION->CacheGetRow($this->sSYSTEM->sCONFIG['sCACHESTATIC'],$sql,array($group));
		
		return $queryDynamic["description"];
	}
}
?>