<?php
class sViewportOrders{
	var $sSYSTEM;
	
	function sRender(){
		

		$templates = array("sContainer"=>"/account/account_ordersummary.tpl","sContainerRight"=>"/contact/contact_right.tpl");
		
	
		
		$variables["sBreadcrumb"] = array(0=>array("name"=>$this->sSYSTEM->sCONFIG['sViewports'][$this->sSYSTEM->_GET["sViewport"]]["name"]));
			
		
		
		return array("templates"=>$templates,"variables"=>$variables);
	}
}
?>