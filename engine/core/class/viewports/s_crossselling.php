<?php
class sViewportCrossselling{
	var $sSYSTEM;
	
	function sRender(){
		

		$templates = array("sContainer"=>"/basket/basket_crossselling_middle.tpl","sContainerRight"=>"/basket/basket_crossselling_right.tpl");
		
	
		
		$variables["sBreadcrumb"] = array(0=>array("name"=>$this->sSYSTEM->sCONFIG['sViewports'][$this->sSYSTEM->_GET["sViewport"]]["name"]));
			
		
		
		return array("templates"=>$templates,"variables"=>$variables);
	}
}
?>