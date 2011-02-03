<?php

class sViewportCampaign{
	var $sSYSTEM;
	
	function sRender(){
		
		// Get all related-infos
		if (!$this->sSYSTEM->_GET['sCampaign']) return;
		
		$this->sSYSTEM->_GET['sCampaign'] = intval($this->sSYSTEM->_GET['sCampaign']);
		
		$campaignData = $this->sSYSTEM->sMODULES['sMarketing']->sCampaignsGetDetail($this->sSYSTEM->_GET['sCampaign']);
		
		if (!$campaignData){
			$templates = array(
			"sContainer"=>"/error/error.tpl",
			"sContainerRight"=>""
			);
			
			$variables = array("sError"=>"sCampaign - Campaign not found");
			return array("templates"=>$templates,"variables"=>$variables);
		}
		

		$variables["sCampaign"] = $campaignData;
		
		$templates = array("sContainer"=>"/campaign/campaign_middle.tpl","sContainerRight"=>"");

		$variables["sBreadcrumb"] = array(0=>array("name"=>$campaignData["description"]));
		
		
		return array("templates"=>$templates,"variables"=>$variables);
	}
}
?>