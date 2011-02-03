<?php
class sViewportCustom{
	var $sSYSTEM;
	
	function sRender(){
		if (isset($this->sSYSTEM->_POST['sCustom'])) $this->sSYSTEM->_GET['sCustom'] = $this->sSYSTEM->_POST['sCustom'];
		if (!isset($this->sSYSTEM->_GET['sCustom'])) return;
		
		$sStaticPages = $this->sSYSTEM->sMODULES['sCms']->sGetStaticPage();
		
		if ($sStaticPages["html"]){
			$variables["sContent"] = $sStaticPages["html"];
			$templates = array("sContainer"=>"/custom/custom_middle.tpl");
		}
			if ($sStaticPages["id"]){
				for ($i=1;$i<=3;$i++){
					if ($sStaticPages["tpl".$i."variable"]){
						
							$templates[$sStaticPages["tpl".$i."variable"]] = $sStaticPages["tpl".$i."path"];
						
					}
				}
			}else {
				$variables["sError"] = "Basket is empty";
				$templates = array("sContainer"=>"/custom/custom_error.tpl","sContainerRight"=>"/custom/custom_error.tpl");
			}
		
		
		$variables["sBreadcrumb"] = array(0=>array("name"=>$sStaticPages['description']));
			
		
		
		return array("templates"=>$templates,"variables"=>$variables);
	}
}
?>