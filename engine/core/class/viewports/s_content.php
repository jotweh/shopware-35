<?php

class sViewportContent{
	var $sSYSTEM;
	
	function sRender(){
		
		// Get all related-infos
		if (!$this->sSYSTEM->_GET['sContent']) return;
		
		$this->sSYSTEM->_GET['sCid'] = intval($this->sSYSTEM->_GET['sCid']);
		
		// Display content - detail page
		if ($this->sSYSTEM->_GET['sCid']){
			$sContent = $this->sSYSTEM->sMODULES['sCms']->sGetDynamicContentById($this->sSYSTEM->_GET['sContent'],$this->sSYSTEM->_GET['sCid']);
			$variables["sContentItem"] = $sContent["sContent"];
			$variables["sPages"] = $sContent["sPages"];
			$templates = array("sContainer"=>"/content/content_details.tpl","sContainerRight"=>"/content/content_right.tpl");
		
		// Display content - listing
		}else {
			$sContent = $this->sSYSTEM->sMODULES['sCms']->sGetDynamicContentByGroup($this->sSYSTEM->_GET['sContent'],intval($this->sSYSTEM->_GET['sPage']));
			$variables["sContent"] = $sContent["sContent"];
			$variables["sPages"] = $sContent["sPages"];
			$templates = array("sContainer"=>"/content/content_listing.tpl","sContainerRight"=>"/content/content_right.tpl");
		}
		
		$variables["sContentName"] = $this->sSYSTEM->sMODULES['sCms']->sGetDynamicGroupName($this->sSYSTEM->_GET['sContent']);
		$variables["sBreadcrumb"] = array(0=>array("name"=>$variables["sContentName"]));
		
		
		return array("templates"=>$templates,"variables"=>$variables);
	}
}
?>