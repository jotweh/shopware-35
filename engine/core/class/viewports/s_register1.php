<?php
include("s_register2.php");

class sViewportRegister1{
	var $sSYSTEM;
	var $sViewportRegister2;
	
	function sViewportRegister1(&$sSYSTEM){
		$this->sViewportRegister2 = new sViewportRegister2;
		$this->sViewportRegister2->sSYSTEM = $sSYSTEM;
	}
	function sRender(){
		$this->sSYSTEM->_SESSION["sRegisterFinished"] = false;
	
		if ($this->sSYSTEM->_GET['sAccountMode']=="ACCOUNT"){
			$this->sSYSTEM->_SESSION['sAccountMode'] = "ACCOUNT";	// Default: Account
		}else if($this->sSYSTEM->_GET['sAccountMode']=="NO_ACCOUNT"){
			$this->sSYSTEM->_SESSION['sAccountMode'] = "NO_ACCOUNT";	// Create no account
		}else {
			$this->sSYSTEM->_SESSION['sAccountMode'] = "ACCOUNT"; // Default: Account
		}

		$variables['sAccountMode'] = $this->sSYSTEM->_SESSION['sAccountMode'];
		
		if ($this->sSYSTEM->_POST['sAction']=="register1"){
		// Check data 
			$checkData = ($this->sSYSTEM->sMODULES['sAdmin']->sValidateStep1());
			if (!count($checkData["sErrorMessages"])){
				// Next step
				$this->sSYSTEM->_GET["sViewport"] = "register2";
				return $this->sViewportRegister2->sRender();
			}else {
				// Uups, something goes wrong - pass error through template
				$variables["sErrorFlag"] = $checkData["sErrorFlag"];
				$variables["sErrorMessages"] = $checkData["sErrorMessages"];
			}
		}
		
		$templates = array("sContainer"=>"/register/register_step_1.tpl","sContainerRight"=>"/register/register_right.tpl");
		$variables["sBreadcrumb"] = array(0=>array("name"=>$this->sSYSTEM->sCONFIG['sViewports'][$this->sSYSTEM->_GET["sViewport"]]["name"]));
		return array("templates"=>$templates,"variables"=>$variables);
	}
}
?>