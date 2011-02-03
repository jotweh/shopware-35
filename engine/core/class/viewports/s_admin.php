<?php 
include_once("s_login.php");
include_once("s_sale.php");

class sViewportAdmin{
	
	var $sSYSTEM;
	var $sViewportLogin;
	var $sViewportSale;
	
	function sViewportAdmin(&$sSYSTEM,&$sViewportLogin = ""){
		if (!is_object($sViewportLogin)){
			$this->sViewportLogin = new sViewportLogin($sSYSTEM,$this);
			$this->sViewportLogin->sSYSTEM = $sSYSTEM;
		}else {
			$this->sViewportLogin = $sViewportLogin;
		}
		
		
		$this->sViewportSale = new sViewportSale($sSYSTEM,$sViewportLogin);
		$this->sViewportSale->sSYSTEM = $sSYSTEM;
		
	}
	
	function sRender(){
		
		// Check if user is logged in
		if (!$this->sSYSTEM->sMODULES['sAdmin']->sCheckUser()){
			$this->sSYSTEM->_GET["sViewport"] = "login";
			return $this->sViewportLogin->sRender();
		}
		$this->sSYSTEM->_GET["sViewport"] = "admin";
		$paymentMeans = ($this->sSYSTEM->sMODULES['sAdmin']->sGetPaymentMeans());
		// For edit behaviours
		
		// Save billing-address
		if ($this->sSYSTEM->_POST["sAction"]=="saveBilling"){
			// Define fields
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
			"fax"=>array("required"=>0),
			"country"=>array("required"=>1),
			"department"=>array("required"=>0),
			"shippingAddress"=>array("required"=>0),
			"ustid"=>array("required"=>0),
			"text1"=>array("required"=>0),
			"text2"=>array("required"=>0),
			"text3"=>array("required"=>0),
			"text4"=>array("required"=>0),
			"text5"=>array("required"=>0),
			"text6"=>array("required"=>0)
			);
			
			if (!empty($this->sSYSTEM->_POST["sSelectAddress"])){
				// Load previously used address
				$address = $this->sSYSTEM->sMODULES['sAdmin']->sGetPreviousAddresses("billing",$this->sSYSTEM->_POST["sSelectAddress"]);
				if (!empty($address["id"])){
					
					foreach ($this->sSYSTEM->_POST as $key => $value){
						if (isset($address[$key])) $this->sSYSTEM->_POST[$key] = $address[$key];
					}
				}
			}
			
			// Check-data
			$checkData = ($this->sSYSTEM->sMODULES['sAdmin']->sValidateStep2($rules,true));
			
			if (!empty($this->sSYSTEM->_POST["ustid"])){
				$result = $this->sSYSTEM->sMODULES['sAdmin']->sCheckTaxID($this->sSYSTEM->_POST["ustid"],$this->sSYSTEM->_POST["country"]);
				$checkData = array_merge($checkData,$result);
			}
			
			if (!count($checkData["sErrorMessages"])){
				
				$this->sSYSTEM->sMODULES['sAdmin']->sUpdateBilling();
				if ($this->sSYSTEM->_POST["sTarget"]){
					
					return $this->sViewportSale->sRender();
				}
			}else {
				
				// Uups, something goes wrong - pass error through template
				$this->sSYSTEM->_GET["sAction"] = "billing";
				$variables["sErrorFlag"] = $checkData["sErrorFlag"];
				$variables["sErrorMessages"] = $checkData["sErrorMessages"];
			}
			
		}
		// Save shipping-address
		else if ($this->sSYSTEM->_POST["sAction"]=="saveShipping"){
			// Define fields
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
			"country"=>array("required"=>1),
			"text1"=>array("required"=>0),
			"text2"=>array("required"=>0),
			"text3"=>array("required"=>0),
			"text4"=>array("required"=>0),
			"text5"=>array("required"=>0),
			"text6"=>array("required"=>0)
			);
			
			if ($this->sSYSTEM->sCONFIG["sCOUNTRYSHIPPING"]){
				$rules["country"] = array("required"=>1);
			}else{
				$rules["country"] = array("required"=>0);
			}
			
			if (!empty($this->sSYSTEM->_POST["sSelectAddress"])){
				// Load previously used address
				$address = $this->sSYSTEM->sMODULES['sAdmin']->sGetPreviousAddresses("shipping",$this->sSYSTEM->_POST["sSelectAddress"]);
				if (!empty($address["id"])){
					
					foreach ($this->sSYSTEM->_POST as $key => $value){
						if (isset($address[$key])) $this->sSYSTEM->_POST[$key] = $address[$key];
					}
				}
			}
			// Check-data
			$checkData = ($this->sSYSTEM->sMODULES['sAdmin']->sValidateStep2ShippingAddress($rules,true));
			if (!count($checkData["sErrorMessages"])){
				$this->sSYSTEM->sMODULES['sAdmin']->sUpdateShipping();
				if ($this->sSYSTEM->_POST["sTarget"]) return $this->sViewportSale->sRender();
			}else {
				// Uups, something goes wrong - pass error through template
				$this->sSYSTEM->_GET["sAction"] = "shipping";
				$variables["sErrorFlag"] = $checkData["sErrorFlag"];
				$variables["sErrorMessages"] = $checkData["sErrorMessages"];
			}
		}
		// Save payment
		else if ($this->sSYSTEM->_POST["sAction"]=="savePayment"){
			$checkData = ($this->sSYSTEM->sMODULES['sAdmin']->sValidateStep3($paymentMeans));
			if (!count($checkData["checkPayment"]["sErrorMessages"]) && $checkData["sProcessed"]){
				
				// Get previous payment-mean
				$previousPayment = $this->sSYSTEM->sMODULES['sAdmin']->sGetUserData();
				$previousPayment = $previousPayment["additional"]["user"]["paymentID"];
				
				// Load previous payment
				$previousPayment = $this->sSYSTEM->sMODULES['sAdmin']->sGetPaymentMeanById($previousPayment);
				if ($previousPayment["paymentTable"]){
					$deleteSQL = "
					DELETE FROM {$previousPayment["paymentTable"]} WHERE userID=".$this->sSYSTEM->_SESSION["sUserId"];
					$this->sSYSTEM->sDB_CONNECTION->Execute($deleteSQL);
				}
				
				// Update user account
				$this->sSYSTEM->sMODULES['sAdmin']->sUpdatePayment();
				
				// Update payment-specific data
				if (method_exists($checkData["sPaymentObject"],"sUpdate")){
					$checkData["sPaymentObject"]->sUpdate();
				}
				if ($this->sSYSTEM->_POST["sTarget"]) return $this->sViewportSale->sRender();
			}else {
				// Uups, something goes wrong - pass error through template
				$this->sSYSTEM->_GET["sAction"] = "payment";
				$variables["sErrorFlag"] = $checkData["checkPayment"]["sErrorFlag"];
				$variables["sErrorMessages"] = $checkData["checkPayment"]["sErrorMessages"];
			}
		}
		// Save accountdata
		else if ($this->sSYSTEM->_POST["sAction"]=="saveAccount"){
			// Check-data
			$checkData = ($this->sSYSTEM->sMODULES['sAdmin']->sValidateStep1(true));
			if (!count($checkData["sErrorMessages"])){
				$this->sSYSTEM->sMODULES['sAdmin']->sUpdateAccount();
			}else {
				// Uups, something goes wrong - pass error through template
				$variables["sErrorFlag"] = $checkData["sErrorFlag"];
				$variables["sErrorMessages"] = $checkData["sErrorMessages"];
			}
		}else if ($this->sSYSTEM->_POST["sAction"]=="saveMailProperties"){
			// Save newsletter-configuration
			if (!$this->sSYSTEM->_POST["newsletter"]){
				$this->sSYSTEM->sMODULES['sAdmin']->sUpdateNewsletter(false,$this->sSYSTEM->sMODULES['sAdmin']->sGetUserMailById(),true);
			}else {
				$this->sSYSTEM->sMODULES['sAdmin']->sUpdateNewsletter(true,$this->sSYSTEM->sMODULES['sAdmin']->sGetUserMailById(),true);
			}
		}
		
		
		
		$userData = $this->sSYSTEM->sMODULES['sAdmin']->sGetUserData();
		
		
		switch ($this->sSYSTEM->_GET["sAction"]){
			case "billing":
				$variables["sBillingPreviously"] = $this->sSYSTEM->sMODULES['sAdmin']->sGetPreviousAddresses("billing");
				foreach ($userData["billingaddress"] as $billingKey => $billingValue){
					if (!$this->sSYSTEM->_POST[$billingKey]) $this->sSYSTEM->_POST[$billingKey] = $billingValue;
				}
				$variables["sAccountEdit"] = true;
				$variables["sCountryList"] = $this->sSYSTEM->sMODULES['sAdmin']->sGetCountryList();
				$templates = array("sContainer"=>"/register/register_step_2.tpl","sContainerRight"=>"/contact/contact_right.tpl");
				break;
			case "shipping":
				// Load previous used addresses
				$variables["sShippingPreviously"] = $this->sSYSTEM->sMODULES['sAdmin']->sGetPreviousAddresses("shipping");
				// 
				foreach ($userData["shippingaddress"] as $shippingKey => $shippingValue){
					if (!$this->sSYSTEM->_POST[$shippingKey]) $this->sSYSTEM->_POST[$shippingKey] = $shippingValue;
				}
				$variables["sAccountEdit"] = true;
				$variables["sCountryList"] = $this->sSYSTEM->sMODULES['sAdmin']->sGetCountryList();
				$templates = array("sContainer"=>"/register/register_step_2_shipping.tpl","sContainerRight"=>"/contact/contact_right.tpl");
				break;
			case "payment":
				
				$variables["sPaymentMeans"] = $paymentMeans;
				$variables["sAccountEdit"] = true;
				// Show existing data, if table is set
				$getPaymentDetails = $this->sSYSTEM->sMODULES['sAdmin']->sGetPaymentMeanById( $userData["additional"]["user"]["paymentID"]);
				if ($getPaymentDetails["table"]){
					
					// Have to fetch data, for this payment-mean
					// Initiate-Class
					$paymentClass = $this->sSYSTEM->sMODULES['sAdmin']->sInitiatePaymentClass($getPaymentDetails);
					if (!is_object($paymentClass)){
						$this->sSYSTEM->E_CORE_ERROR("s_admin#","Could not load payment-class".print_r($getPaymentDetails,true));
					}else {
						$loadPaymentData = $paymentClass->getData();
						
						foreach ($loadPaymentData as $paymentDataPostFieldKey => $paymentDataPostFieldValue){
							$this->sSYSTEM->_POST[$paymentDataPostFieldKey] = $paymentDataPostFieldValue;
						}
					}
				}
				$variables["sChoosenPayment"] = $userData["additional"]["user"]["paymentID"];
				
				$templates = array("sContainer"=>"/register/register_step_3.tpl","sContainerRight"=>"/contact/contact_right.tpl");
				break;
			case "orders":
				
				$variables["sOpenOrders"] = $this->sSYSTEM->sMODULES['sAdmin']->sGetOpenOrderData();
				$templates = array("sContainer"=>"/account/account_ordersummary.tpl","sContainerRight"=>"/contact/contact_right.tpl");
				break;
			
			case "downloads":
				$variables["sOpenOrders"] = $this->sSYSTEM->sMODULES['sAdmin']->sGetDownloads();
				$templates = array("sContainer"=>"/account/account_downloads.tpl","sContainerRight"=>"/contact/contact_right.tpl");
				break;	
			
			case "ticket":
				$variables["sOpenOrders"] = $this->sSYSTEM->sMODULES['sAdmin']->sGetDownloads();
				$templates = array("sContainer"=>"/account/account_downloads.tpl","sContainerRight"=>"/contact/contact_right.tpl");
				break;	
			
			default:
				$templates = array("sContainer"=>"/account/account_start.tpl","sContainerRight"=>"/contact/contact_right.tpl");
				break;
		}
		
		if ($this->sSYSTEM->sCheckLicense("","",$this->sSYSTEM->sLicenseData["sTICKET"])){
			$variables["sTICKETLicensed"] = true;
		}
	
		$variables["sUserData"] = $userData;
		
		$variables["sBreadcrumb"] = array(0=>array("name"=>$this->sSYSTEM->sCONFIG['sViewports'][$this->sSYSTEM->_GET["sViewport"]]["name"]));
			
		
		
		return array("templates"=>$templates,"variables"=>$variables);
	}
}
?>