<?php
include_once("s_admin.php");
include_once("s_sale.php");

class sViewportRegister3{
	var $sSYSTEM;
	var $sViewportAdmin;
	var $sViewportSale;
	
	function sViewportRegister3(&$sSYSTEM = "",&$parentClass = ""){
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
	}
	
	function sRender(){
		
		
		$paymentMeans = ($this->sSYSTEM->sMODULES['sAdmin']->sGetPaymentMeans());
		
		if(isset($this->sSYSTEM->_SESSION['sPaymentID'])&&!isset($this->sSYSTEM->_POST['sPayment']))
			$this->sSYSTEM->_POST['sPayment'] = (int) $this->sSYSTEM->_SESSION['sPaymentID'];
		elseif (isset($this->sSYSTEM->_POST['sPayment']))
			$this->sSYSTEM->_SESSION['sPaymentID'] = (int) $this->sSYSTEM->_POST['sPayment'];

		if ($this->sSYSTEM->_POST['sAction']=="register3"){
		// Load class to check this paymentmean
		
		
			$checkData = ($this->sSYSTEM->sMODULES['sAdmin']->sValidateStep3($paymentMeans));
			
			
			
			if (!count($checkData["checkPayment"]["sErrorMessages"])){
				// Alles roger
				$this->sSYSTEM->_SESSION["sRegister"]["payment"]["object"] = $checkData["paymentData"];
				
				
				// Save registration and link to admin-viewport (2.do - consider ordering-process)
				
				$this->sSYSTEM->sMODULES['sAdmin']->sSaveRegister($checkData["sPaymentObject"]);
				
				if (method_exists($checkData["sPaymentObject"],"sUpdate")){

					$checkData["sPaymentObject"]->sUpdate();
				}
				
				// Go to Order-Confirmation page or User-admin-area
				if (!count($this->sSYSTEM->sMODULES['sBasket']->sGetBasketIds())){
					// Go to admin
					
					
					$this->sSYSTEM->_GET["sViewport"] = "admin";
					return $this->sViewportAdmin->sRender();
				}else {
					
					// Go to order-confirmation
					$this->sSYSTEM->_GET["sViewport"] = "sale";
					return $this->sViewportSale->sRender();
				}	
			} else {
				// Uups, something goes wrong - pass error into template
				$variables["sChoosenPayment"] = $this->sSYSTEM->_POST['sPayment'];
				$variables["sErrorFlag"] = $checkData["checkPayment"]["sErrorFlag"];
				$variables["sErrorMessages"] = $checkData["checkPayment"]["sErrorMessages"];
				
				$templates = array("sContainer"=>"/register/register_step_3.tpl","sContainerRight"=>"/register/register_right.tpl");
			}
		}else {
			$variables["sChoosenPayment"] = $this->sSYSTEM->_POST['sPayment'];
			$templates = array("sContainer"=>"/register/register_step_3.tpl","sContainerRight"=>"/register/register_right.tpl");
		}
		
		$variables["sPaymentMeans"] = $this->sSYSTEM->sMODULES['sAdmin']->sGetPaymentMeans();
		
		$variables["sBreadcrumb"] = array(0=>array("name"=>$this->sSYSTEM->sCONFIG['sViewports'][$this->sSYSTEM->_GET["sViewport"]]["name"]));
		
		return array("templates"=>$templates,"variables"=>$variables);
	}
}
?>