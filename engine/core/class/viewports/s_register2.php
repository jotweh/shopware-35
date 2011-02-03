<?php
include("s_register3.php");
include("s_register2shipping.php");

class sViewportRegister2{
	var $sSYSTEM;
	var $sViewportRegister3;
	var $sViewportRegister2Shipping;
	
	function sViewportRegister2(&$sSYSTEM){
		$this->sViewportRegister3 = new sViewportRegister3;
		$this->sViewportRegister3->sSYSTEM = $sSYSTEM;
		
		$this->sViewportRegister2Shipping = new sViewportRegister2shipping($this->sSYSTEM,$this->sViewportRegister3);
		$this->sViewportRegister2Shipping->sSYSTEM = $sSYSTEM;
		
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
		"phone"=>array("required"=>1),
		"country"=>array("required"=>1),
		"department"=>array("required"=>0),
		"fax"=>array("required"=>0),
		"shippingAddress"=>array("required"=>0),
		"ustid"=>array("required"=>0),
		);
		
		if ($this->sSYSTEM->_POST['sAction']=="register2"){
		// Check data 
			$checkData = ($this->sSYSTEM->sMODULES['sAdmin']->sValidateStep2($rules));
			if (!count($checkData["sErrorMessages"])){
				// Next step 
				
				if ($this->sSYSTEM->_POST["shippingAddress"]){
					$this->sSYSTEM->_GET["sViewport"] = "register2shipping";
					return $this->sViewportRegister2Shipping->sRender();					
				}else {
					$this->sSYSTEM->_GET["sViewport"] = "register3";
					return $this->sViewportRegister3->sRender();
				}
				
			}else {
				// Uups, something goes wrong - pass error through template
				$variables["sErrorFlag"] = $checkData["sErrorFlag"];
				$variables["sErrorMessages"] = $checkData["sErrorMessages"];
			}
		}
		
		$variables["sCountryList"] = $this->sSYSTEM->sMODULES['sAdmin']->sGetCountryList();

		$templates = array("sContainer"=>"/register/register_step_2.tpl","sContainerRight"=>"/register/register_right.tpl");	
		$variables["sBreadcrumb"] = array(0=>array("name"=>$this->sSYSTEM->sCONFIG['sViewports'][$this->sSYSTEM->_GET["sViewport"]]["name"]));
		return array("templates"=>$templates,"variables"=>$variables);
	}
}
?>