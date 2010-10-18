<?php
include("$path/sCore.php");

class myCore extends sCore{
	/**
	  * Render modules/boxes in all templates overall
	  * Could be used to show the basket all over the shop, for example
	  * @param  array   $sRender contains the variables/templates which
	  * 				are already scheduled to be rendered
	  * @param  string  $sPath contains path to template-root
	  * @param  string  $sLanguage contains prefix for current language
	  * @return array   Array with all content of this viewport
	  *                 to get rendered by the viewport-manager
	  * @access public
	*/
	function sCustomRenderer($sRender,$sPath,$sLanguage){
		if (!is_array($sRender)) $sRender = array();
		
		$sRender = array_merge($sRender,parent::sCustomRenderer($sRender,$sPath,$sLanguage));
		
		for($i=65;$i<=90;$i++){
			$sRender['variables']['sAlphabet'][] = chr($i);
		}
		
		/*
		$campaignGroups = $this->sSYSTEM->sCONFIG['sCAMPAIGNSPOSITIONS'];
		$campaignGroups = explode(";",$campaignGroups);
		
		if (empty($this->sSYSTEM->_GET["sViewport"])) $this->sSYSTEM->_GET["sViewport"] = "";
		
		if ($this->sSYSTEM->_GET["sViewport"]!="cat"){
			unset($sRender['variables']["sCampaigns"]);
			foreach ($campaignGroups as $campaignGroup){
				$groupData = explode(":",$campaignGroup);
				$sRender['variables']["sCampaigns"][$groupData[1]] = $this->sSYSTEM->sMODULES['sMarketing']->sCampaignsGetList($this->sSYSTEM->sLanguageData[$this->sSYSTEM->sLanguage]["parentID"] ?  $this->sSYSTEM->sLanguageData[$this->sSYSTEM->sLanguage]["parentID"] : $this->sSYSTEM->_GET["sCategory"],$groupData[1]);
			}
		}
		*/
		
		$sRender['variables']['sPromotion'] = array();
		
		/*
		foreach ($this->sSYSTEM->sMODULES["sCategories"]->sGetMainCategories() as $category){
			$id = $category["id"];
			$result[] = array("link"=>$category["link"],"name"=>$category["description"],"sub"=>$this->sSYSTEM->sMODULES["sCategories"]->sGetWholeCategoryTree($id));
		}
		
		$sRender['variables']["sCategoryTree"] = $result;
		*/
		
		$sRender['variables']['sGroup'] = $this->sSYSTEM->sUSERGROUPDATA;
		
		return $sRender;
	}
}
?>