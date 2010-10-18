<?php
include_once("s_register3.php");

class sViewportRegister2shipping{
	var $sSYSTEM;
	var $sViewportRegister3;
	
	function sViewportRegister2shipping(&$sSYSTEM,&$sViewportRegister3){
		if (!is_object($sViewportRegister3)){
			$this->sViewportRegister3 = new sViewportRegister3;
			$this->sViewportRegister3->sSYSTEM = $sSYSTEM;
		}else {
			$this->sViewportRegister3 = $sViewportRegister3;
		}
		
	}
	
	function sRender(){
		
		// Define field-rules
		$rules = array(
		"salutation"=>array("required"=>1),
		"company"=>array("addicted"=>array("salutation"=>"company"),"required"=>1),
		"firstname"=>array("required"=>1),
		"lastname"=>array("required"=>1),
		"street"=>array("required"=>1),
		"streetnumber"=>array("required"=>1),
		"zipcode"=>array("required"=>1),
		"city"=>array("required"=>1),
		"department"=>array("required"=>0),
		"text1"=>array("required"=>0),
		"text2"=>array("required"=>0),
		"text3"=>array("required"=>0),
		"text4"=>array("required"=>0),
		"text5"=>array("required"=>0),
		"text6"=>array("required"=>0)
		);
		
		if (!empty($this->sSYSTEM->sCONFIG["sCOUNTRYSHIPPING"])){
			$rules["country"] = array("required"=>1);	
		}else{
			$rules["country"] = array("required"=>0);	
		}
		
		if ($this->sSYSTEM->_POST['sAction']=="register2shipping"){
		// Check data 
			$checkData = ($this->sSYSTEM->sMODULES['sAdmin']->sValidateStep2ShippingAddress($rules));
			if (!count($checkData["sErrorMessages"])){
				// Next step 
				
			
				$this->sSYSTEM->_GET["sViewport"] = "register3";
				return $this->sViewportRegister3->sRender();
				
				
			}else {
				// Uups, something goes wrong - pass error through template
				$variables["sErrorFlag"] = $checkData["sErrorFlag"];
				$variables["sErrorMessages"] = $checkData["sErrorMessages"];
			}
		}
		
		$variables["sCountryList"] = $this->sSYSTEM->sMODULES['sAdmin']->sGetCountryList();
		
		if (count($this->sSYSTEM->_SESSION["sRegister"]["shipping"])){
			foreach ($this->sSYSTEM->_SESSION["sRegister"]["shipping"] as $key => $value){
				$this->sSYSTEM->_POST[$key] = $value;
			}
		}
		
		$variables["sCountryList"] = $this->sSYSTEM->sMODULES['sAdmin']->sGetCountryList();
		
		$templates = array("sContainer"=>"/register/register_step_2_shipping.tpl","sContainerRight"=>"/register/register_right.tpl");	
		$variables["sBreadcrumb"] = array(0=>array("name"=>$this->sSYSTEM->sCONFIG['sViewports'][$this->sSYSTEM->_GET["sViewport"]]["name"]));
		return array("templates"=>$templates,"variables"=>$variables);
	}
}
?>