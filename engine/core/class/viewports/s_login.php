<?php
include_once("s_admin.php");
include_once("s_sale.php");


class sViewportLogin{
	var $sSYSTEM;
	var $sViewportAdmin;
	var $sViewportSale;
	
	function sViewportLogin(&$sSYSTEM,&$parentClass){
		if(get_class($parentClass)=="sViewportAdmin"){
			$this->sViewportAdmin = $parentClass;
		}else {
			$this->sViewportAdmin = new sViewportAdmin($sSYSTEM,$this);
			$this->sViewportAdmin->sSYSTEM = $sSYSTEM;
			
		}
		
		if(get_class($parentClass)=="sViewportSale"){
			$this->sViewportSale = $parentClass;
		}else {
			$this->sViewportSale = new sViewportSale($sSYSTEM,$this);
			$this->sViewportSale->sSYSTEM = $sSYSTEM;
			
		}
		
		//$this->sViewportAdmin = new sViewportAdmin($sSYSTEM,$this);
		//$this->sViewportAdmin->sSYSTEM = $sSYSTEM;
		
		#$this->sViewportSale = new sViewportSale;
		#$this->sViewportSale->sSYSTEM = $sSYSTEM;
	}
	
	function sRender(){
		
	
		// Login the user
		if ($this->sSYSTEM->_POST['sAction']=="login"){
			$checkUser = $this->sSYSTEM->sMODULES['sAdmin']->sLogin();
			
			if (!count($checkUser["sErrorMessages"])){
				// Load wished viewport
				//echo "##".$this->sSYSTEM->_POST['sTarget'];
				switch ($this->sSYSTEM->_POST['sTarget']){
					case "admin":
						return $this->sViewportAdmin->sRender();
						break;
					case "sale":
						return $this->sViewportSale->sRender();
						break;
					default:
						return $this->sViewportAdmin->sRender();
				}
			}else {
				// Uups, something goes wrong - pass error through template
				$variables["sErrorFlag"] = $checkUser["sErrorFlag"];
				$variables["sErrorMessages"] = $checkUser["sErrorMessages"];
			}
		}
		
		$variables["sTarget"] = $this->sSYSTEM->_POST["sTarget"];
		$templates = array("sContainer"=>"/login/login_middle.tpl","sContainerRight"=>"/contact/contact_right.tpl");
		
	
		$variables["sBreadcrumb"] = array(0=>array("name"=>$this->sSYSTEM->sCONFIG['sViewports'][$this->sSYSTEM->_GET["sViewport"]]["name"]));
			
		
		
		return array("templates"=>$templates,"variables"=>$variables);
	}
}
?>