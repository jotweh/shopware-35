<?php
include_once("s_sale.php");

class sViewportPaypalexpressGAReg{
	var $sSYSTEM;
	var $sViewportSale;
	
	function sViewportPaypalexpressGAReg(&$sSYSTEM,&$sViewportSale){
		if (!is_object($sViewportSale)){
			$this->sViewportSale = new sViewportSale;
			$this->sViewportSale->sSYSTEM = $sSYSTEM;
		}else {
			$this->sViewportSale = $sViewportSale;
		}
		
	}	
	

	function sRender(){

		$sql = "SELECT id FROM s_core_paymentmeans WHERE class='paypalexpress.php'";
		$PaypalExpress = Shopware()->Db()->fetchOne($sql);			
		$this->sSYSTEM->_SESSION['sPaymentID'] = $this->sSYSTEM->_POST['sPayment'] = $PaypalExpress ? $PaypalExpress : 20;

		if ($this->sSYSTEM->_POST['sAction']=="doReg"){
		// Load class to check this paymentmean
		
			$checkData = ($this->sSYSTEM->sMODULES['sAdmin']->sValidateStep3());

			if (!count($checkData["checkPayment"]["sErrorMessages"])){
				// Alles roger
				$this->sSYSTEM->_SESSION["sRegister"]["payment"]["object"] = $checkData["paymentData"];

				//If User has already an Guest account! update shipping adress
				if ($this->sSYSTEM->_SESSION['GuestUser'] == "1" && $this->sSYSTEM->sMODULES['sAdmin']->sCheckUser()) {				
					$this->sSYSTEM->sMODULES['sAdmin']->sUpdateShipping();	
				} else {
					// Save registration and link to admin-viewport (2.do - consider ordering-process)
					$this->sSYSTEM->sMODULES['sAdmin']->sSaveRegister($checkData["sPaymentObject"]);
				}
											
				// Update user account
				$this->sSYSTEM->sMODULES['sAdmin']->sUpdatePayment();	
				
				// Go to Order-Confirmation page or User-admin-area
				if (!count($this->sSYSTEM->sMODULES['sBasket']->sGetBasketIds())){
					// Go to basket
					$url = 'http://'.$this->sSYSTEM->sCONFIG["sBASEPATH"].'/'.$this->sSYSTEM->sCONFIG["sBASEFILE"].'/sViewport,basket';		
					header("Location: $url");	
					exit();
				}else {
					// Go to sale					
					$url = 'http://'.$this->sSYSTEM->sCONFIG["sBASEPATH"].'/'.$this->sSYSTEM->sCONFIG["sBASEFILE"].'/sViewport,sale';		
					header("Location: $url");
					exit();
				}	
			} else {
				// Uups, something goes wrong - pass error into template
				$variables["sChoosenPayment"] = $this->sSYSTEM->_POST['sPayment'];
				$variables["sErrorFlag"] = $checkData["checkPayment"]["sErrorFlag"];
				$variables["sErrorMessages"] = $checkData["checkPayment"]["sErrorMessages"];
				
				$templates = array("sContainer"=>"/error/paypalexpress_error.tpl", "sContainerRight"=>"");			
				$variables = array("sError"=>"PayPal Express Error - Registration");
				return array("templates"=>$templates,"variables"=>$variables);
			}
		}
		

		// Go to order-confirmation
		$this->sSYSTEM->_GET["sViewport"] = "sale";
		return $this->sViewportSale->sRender();

	}
}
?>