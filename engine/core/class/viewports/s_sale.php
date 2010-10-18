<?php
include_once("s_login.php");

class sViewportSale{
	
	var $sSYSTEM;
	var $sViewportLogin;
	var $sPayment;
	var $sPaymentAccepted;		// Set to true if payment was successfully authentificated by payment provider
	
	function sViewportSale(&$sSYSTEM = "",&$sViewportLogin = ""){
			if (!is_object($sViewportLogin)){
				$this->sViewportLogin = new sViewportLogin($sSYSTEM,$this);
				$this->sViewportLogin->sSYSTEM = $sSYSTEM;
			}else {
				$this->sViewportLogin = $sViewportLogin;
			}
	}
	
		
	function sRender(){
		// Check if user is logged in 
		if (!$this->sSYSTEM->sMODULES['sAdmin']->sCheckUser()){	
			$this->sSYSTEM->_GET["sViewport"] = "login";
			$this->sSYSTEM->_POST["sTarget"] = "sale";
			return $this->sViewportLogin->sRender();
		} else {
			$userData = $this->sSYSTEM->sMODULES['sAdmin']->sGetUserData();	
			$variables["sUserData"] = $userData;
		}
		/*/
		 * Individual Anpassung (30.10.08): Automatsiche Umsatzsteuerbefreiung bei nicht EU-Ländern
		/*/
		$variables["sNet"] = 0;
		if(is_array($variables["sUserData"]["additional"]["countryShipping"]))
		{
			$sTaxFree = false;
			// Shipping information available
			if (!empty( $variables["sUserData"]["additional"]["countryShipping"]["taxfree"])){
				$sTaxFree = true;
			}elseif ((!empty($variables["sUserData"]["additional"]["countryShipping"]["taxfree_ustid"]) || !empty($variables["sUserData"]["additional"]["countryShipping"]["taxfree_ustid_checked"])) && !empty($variables["sUserData"]["billingaddress"]["ustid"]) && $variables["sUserData"]["additional"]["country"]["id"] == $variables["sUserData"]["additional"]["countryShipping"]["id"]){
				$sTaxFree = true;
				// Wenn UST-freie Belieferung für Zielland und UST-ID eingegeben und Rechnungsland ==  Lieferland, Steuerfrei
			}		
		}
		if(!empty($sTaxFree))
		{
			$this->sSYSTEM->sUSERGROUPDATA["tax"] = 0;
			$this->sSYSTEM->sCONFIG['sARTICLESOUTPUTNETTO'] = 1;
			$this->sSYSTEM->_SESSION["sUserGroupData"] = $this->sSYSTEM->sUSERGROUPDATA;
			$variables["sNet"] = 1;
		}
		/*/
		 * Individual Anpassung ENDE
		/*/

		
		
		$this->sSYSTEM->_GET["sViewport"] = "sale";
		
		// Manage vouchers
		if ($this->sSYSTEM->_POST['sVoucher']){
			$voucher = $this->sSYSTEM->sMODULES['sBasket']->sAddVoucher($this->sSYSTEM->_POST['sVoucher'],'');
			if ($voucher["sErrorMessages"]){
				$variables["sVoucherError"] = $voucher["sErrorMessages"];
			}
		}

		$basketData = $this->sSYSTEM->sMODULES['sBasket']->sGetBasket();	
		$variables["sBasket"] = $basketData;
		
		if (!count($basketData)){
			// This is possible a fatal error
			if (!$this->sSYSTEM->_POST["sAction"] && !$this->sSYSTEM->_GET["sAction"]){
				// No articles in basket
				$templates = array(
				"sContainer"=>"/error/error.tpl",
				"sContainerRight"=>""
				);
			
				$variables = array("sError"=>"sSALE - No articles in basket");
				return array("templates"=>$templates,"variables"=>$variables);
			}else {
				if (!$userData["additional"]["payment"]["embediframe"]){
					$templates = array("sContainer"=>"/orderprocess/order_finished.tpl");
					// Fix - display order-information -
					$variables = $this->sSYSTEM->_SESSION["sOrderVariables"];
					$variables["sContainerRight"] = "";
					return array("templates"=>$templates,"variables"=>$variables);
				}
			}
		}else {
			
		}
		
		/*
		Shopware 2.0.4 - Different dispatches -
		*/
		if(isset($this->sSYSTEM->_SESSION['sDispatch'])&&!isset($this->sSYSTEM->_POST['sDispatch'])){
			$this->sSYSTEM->_POST['sDispatch'] = (int) $this->sSYSTEM->_SESSION['sDispatch'];
		}

		$variables["sDispatches"] = $this->sSYSTEM->sMODULES['sAdmin']->sGetDispatches($userData["additional"]["countryShipping"]["id"]);
		
		$dispatchCounter = 0;
		foreach ($variables["sDispatches"] as $dispatchKey => $dispatchValue){
			// Default = first country in list
			if (!$dispatchCounter) $selectedDispatch = $dispatchValue;
			
			if ($dispatchValue["id"]==$this->sSYSTEM->_POST["sDispatch"]){
				// Overwrite default if any country was selected
				$variables["sDispatches"][$dispatchKey]["flag"] = true;
				$selectedDispatch = $dispatchValue;
			}
			$dispatchCounter++;
		}
		if(empty($variables["sDispatches"])&&!empty($this->sSYSTEM->sCONFIG['sPREMIUMSHIPPIUNGNOORDER']))
		{
			$variables["sDispatchNoOrder"] = true;
		}
		if (count($variables["sDispatches"])==1){
			unset($variables["sDispatches"]);
			
		}
		if(isset($selectedDispatch["id"])){
			$this->sSYSTEM->_SESSION["sDispatch"] = $selectedDispatch["id"];
		}
		$variables["selectedDispatch"] = $selectedDispatch;
		// 
		/*
		// Shopware 2.0.4 - Different dispatches -
		*/
		

		$this->sSYSTEM->_SESSION['sPaymentID'] = $userData["additional"]["payment"]["id"];
		
		
		// Calculating shipping-costs
		$shippingCosts = $this->sSYSTEM->sMODULES['sAdmin']->sGetShippingcosts($userData["additional"]["countryShipping"],$userData["additional"]["payment"]["surcharge"],$userData["additional"]["payment"]["surchargestring"]);
		
		$basketData = $this->sSYSTEM->sMODULES['sBasket']->sGetBasket();	
		$variables["sBasket"] = $basketData;
		if ($shippingCosts["brutto"]){
			$basketData["AmountNetNumeric"] += $shippingCosts["netto"];
			$basketData["AmountNumeric"] += $shippingCosts["brutto"];
		}
		
		if ($basketData["AmountWithTaxNumeric"]){
			// Check for rounding-errors
			/*if (round(($basketData["AmountWithTaxNumeric"]+$shippingCosts["brutto"]),2)!=round($basketData["AmountNetNumeric"]*1.19,2)){
				
				$difference = round(($basketData["AmountWithTaxNumeric"]+$shippingCosts["brutto"]),2) - round($basketData["AmountNetNumeric"]*1.19,2);
				$difference = round($difference,2);
				
				if ($difference>0.01 || ($difference*-1)>0.01){
					
					unset($difference);
				}
			}*/
			$basketData["AmountWithTaxNumeric"] += $shippingCosts["brutto"];
			//$basketData["AmountWithTaxNumeric"] -= $difference;
		}else {
			// Check for round errors
			/*if (round(($basketData["AmountNumeric"]/119*100),2)!=round($basketData["AmountNetNumeric"],2)){
				$difference = round(($basketData["AmountNumeric"]/119*100),2)-round($basketData["AmountNetNumeric"],2);
				$difference = round($difference,2);
				if ($difference>0.01 || ($difference*-1)>0.01){
					unset($difference);
				}
			}*/
			//$basketData["AmountNetNumeric"]+= $difference;
		}
		
		$basketData["Amount"] = $this->sSYSTEM->sMODULES['sArticles']->sFormatPrice($basketData["AmountNumeric"]);
		$basketData["AmountWithTax"] =$this->sSYSTEM->sMODULES['sArticles']->sFormatPrice($basketData["AmountWithTaxNumeric"]);
		$basketData["AmountNet"] = $this->sSYSTEM->sMODULES['sArticles']->sFormatPrice(round($basketData["AmountNetNumeric"],2));
	
		$variables["sAmount"] = $basketData["Amount"];
		$variables["sAmountWithTax"] = $basketData["AmountWithTax"];
		$variables["sAmountNet"] = $basketData["AmountNet"];
	
		$variables["sShippingcostsNumeric"] = $shippingCosts["brutto"];
		$variables["sBasketAfterOrder"] = $basketData;
		
		// PHP 4.4. fix
		if ($this->sSYSTEM->sUSERGROUPDATA["groupkey"]!=$this->sSYSTEM->_SESSION["sUserGroupData"]["groupkey"]){
			$this->sSYSTEM->sUSERGROUPDATA = $this->sSYSTEM->_SESSION["sUserGroupData"];
		}
		
		// Setting the correct shippingcosts (depends on customer - group)
		if (($this->sSYSTEM->sCONFIG['sARTICLESOUTPUTNETTO'] && !$this->sSYSTEM->sUSERGROUPDATA["tax"]) || (!$this->sSYSTEM->sUSERGROUPDATA["tax"] && $this->sSYSTEM->sUSERGROUPDATA["id"])){
			$variables["sShippingcosts"] = $this->sSYSTEM->sMODULES['sArticles']->sFormatPrice($shippingCosts["netto"]);
		}else {
			$variables["sShippingcosts"] = $this->sSYSTEM->sMODULES['sArticles']->sFormatPrice($shippingCosts["brutto"]);
		}
		
		if ($this->sSYSTEM->_GET['sAction'] != "doSale") unset ($this->sSYSTEM->_GET['sAction']);
		if ($this->sSYSTEM->_GET['sAction']) $this->sSYSTEM->_POST['sAction'] = $this->sSYSTEM->_GET['sAction'];
		

		
		
		// Check for ESD - Articles 
		if ($this->sSYSTEM->sMODULES['sBasket']->sCheckForESD() && !$userData["additional"]["payment"]["esdactive"])
		{
			$variables["sShowEsdNote"] = true;
		}
		
		if (count($basketData) && $this->checkForArticle($basketData["content"])){
			
			$this->sSYSTEM->_SESSION["sOrderVariables"] = $variables;
		}
		
		// Embedded iframe 
		if ($userData["additional"]["payment"]["embediframe"]){
			$variables["sEmbedded"] = $userData["additional"]["payment"]["embediframe"];
		}
		
		// Check for minimum-surcharge
		$variables["sMinimumSurcharge"] = $this->sSYSTEM->sMODULES['sBasket']->sCheckMinimumCharge();
		if ($variables["sMinimumSurcharge"]) $variables["sMinimumSurcharge"] = $this->sSYSTEM->sMODULES['sArticles']->sFormatPrice($variables["sMinimumSurcharge"]);
		
		
		// If using external payment, check if payment was successful
		if ($variables["sEmbedded"] && ($this->sSYSTEM->_POST["sAction"]=="doSale" || $this->sSYSTEM->_GET["sAction"]=="doSale") && !$this->sPaymentAccepted){
			
			unset($this->sSYSTEM->_POST["sAction"]);
			unset($this->sSYSTEM->_GET["sAction"]);
			$variables = $this->sSYSTEM->_SESSION["sOrderVariables"];
			
			
			if ($this->sSYSTEM->_GET["sUniqueID"]){
				$getOrderInfo = $this->sSYSTEM->sDB_CONNECTION->GetRow("
				SELECT transactionID, ordernumber FROM s_order WHERE 
					temporaryID='".$this->sSYSTEM->_GET["sUniqueID"]."'
				AND
					userID=".$this->sSYSTEM->_SESSION["sUserId"]);
				
				if ($getOrderInfo["ordernumber"]){
					$variables["sOrderNumber"] = $getOrderInfo["ordernumber"];
					$variables["sTransactionumber"] = $getOrderInfo["transactionID"];
				}else {
					$readFailure = true;
				}
			}else {
				// Fehler, keine Bestellung auslesbar ...
				$readFailure = true;
			}
			
			if ($readFailure) $variables["sOrdernumber"] = "Bestellung nicht ausgeführt";
			$variables["showConfirmation"] = true;
			#$variables["sBasket"] = $variables["basket"];
			#print_r($variables);
			
			$templates = array("sContainer"=>"/orderprocess/order_finished.tpl");
			return array("templates"=>$templates,"variables"=>$variables);
		}elseif ($variables["sMinimumSurcharge"]){
			unset($this->sSYSTEM->_POST["sAction"]);
			unset($this->sSYSTEM->_GET["sAction"]);
		}
		
		if (!$this->sSYSTEM->_POST["sAGB"] && $this->sSYSTEM->_POST["sAction"]=="doSale" && $this->sSYSTEM->sCONFIG['sIGNOREAGB']!="1"){
				$variables["sAGBError"] = true;
				unset ($this->sSYSTEM->_POST["sAction"]);
		}
		
		
		// Get payment - details
		$paymentId = $userData["additional"]["user"]["paymentID"];
		$getPaymentDetails = $this->sSYSTEM->sMODULES['sAdmin']->sGetPaymentMeanById($paymentId);
		if ($getPaymentDetails["paymentTable"]){
			$paymentClass = $this->sSYSTEM->sMODULES['sAdmin']->sInitiatePaymentClass($getPaymentDetails);
			if (!is_object($paymentClass)){
				$this->sSYSTEM->E_CORE_ERROR("s_admin#","Could not load payment-class".print_r($getPaymentDetails,true));
			}else {
				$loadPaymentData = $paymentClass->getData();
				//print_r($loadPaymentData);
				foreach ($loadPaymentData as $paymentDataPostFieldKey => $paymentDataPostFieldValue){
					//$this->sSYSTEM->_POST[$paymentDataPostFieldKey] = $paymentDataPostFieldValue;
					if (!is_numeric($paymentDataPostFieldKey)){
						$userData["additional"]["payment"]["description"] .= "\n$paymentDataPostFieldKey:$paymentDataPostFieldValue";
					}
				}
			}
		}
		
		// Call Shopware order - class
		
		$this->sSYSTEM->sMODULES['sOrder']->sUserData = $userData;
		$this->sSYSTEM->sMODULES['sOrder']->sComment = addslashes(strip_tags($this->sSYSTEM->_POST["sComment"]));	// Optional comment
		
		$this->sSYSTEM->sMODULES['sOrder']->sBasketData = $basketData;
		$this->sSYSTEM->sMODULES['sOrder']->sAmount = $variables["sAmount"];
		$this->sSYSTEM->sMODULES['sOrder']->sAmountWithTax = $variables["sAmountWithTax"];
		$this->sSYSTEM->sMODULES['sOrder']->sAmountNet = $variables["sAmountNet"];
		$this->sSYSTEM->sMODULES['sOrder']->sShippingcosts = $variables["sShippingcosts"];
		$this->sSYSTEM->sMODULES['sOrder']->sShippingcostsNumeric = round($shippingCosts["brutto"],2);
		$this->sSYSTEM->sMODULES['sOrder']->sShippingcostsNumericNet = round($shippingCosts["netto"],2);
		$this->sSYSTEM->sMODULES['sOrder']->bookingId = $this->sSYSTEM->_POST["sBooking"];
		$this->sSYSTEM->sMODULES['sOrder']->dispatchId = $this->sSYSTEM->_SESSION["sDispatch"];
		$this->sSYSTEM->sMODULES['sOrder']->sNet = $variables["sNet"];
		
		// If payment was triggered by external service - add unique transaction id to order
		if ($this->sPaymentAccepted){
			$this->sSYSTEM->sMODULES['sOrder']->uniqueID = addslashes($_REQUEST["param_uniqueId"]);
		}
		
		// Temporary create order
		$this->sSYSTEM->sMODULES['sOrder']->sDeleteTemporaryOrder();	// Delete previous temporary orders
		$this->sSYSTEM->sMODULES['sOrder']->sCreateTemporaryOrder();	// Create new temporary order
		eval($this->sSYSTEM->sCallHookPoint("s_sale.php_sRender_BeforeOrder"));
		// This is calling after submit the order 
		if ($this->sSYSTEM->_POST['sAction']=="doSale"){
			eval($this->sSYSTEM->sCallHookPoint("s_sale.php_sRender_BeforeOrder2"));
			if (!$this->sSYSTEM->_POST["sBooking"] && $variables["sEmbedded"]){
				$variables = $this->sSYSTEM->_SESSION["sOrderVariables"];
				$variables["showConfirmation"] = true;
				$templates = array("sContainer"=>"/orderprocess/order_finished.tpl");
				return array("templates"=>$templates,"variables"=>$variables);
			}
			$templates = array("sContainer"=>"/orderprocess/order_finished.tpl");
			$variables["sContainerRight"] = "";
			
			// Could resolve in conflicts while lost sessions
			if (!$basketData || !count($basketData["content"])){
				//$this->sSYSTEM->E_CORE_ERROR("s_sale##001","Lost basket due session");
				return array("templates"=>$templates,"variables"=>$variables);
			}
			
			
			$this->sSYSTEM->_SESSION["sOrderVariables"]["ordernumber"] = $this->sSYSTEM->sMODULES['sOrder']->sSaveOrder();
			
			if ($this->sSYSTEM->sCONFIG['sDELETECACHEAFTERORDER']){
				$this->sDeleteAllFiles ("cache/database/", true);
			// Fix - display order-information -
			}
			
			$variables = $this->sSYSTEM->_SESSION["sOrderVariables"];
			$variables["showConfirmation"] = true;
			$variables["sBasket"] = array();
			
		}else {
			$templates = array("sContainer"=>"/orderprocess/order_confirm_middle.tpl","sContainerRight"=>"/orderprocess/order_confirm_right.tpl");
		}
		
		
		$variables["sBreadcrumb"] = array(0=>array("name"=>$this->sSYSTEM->sCONFIG['sViewports'][$this->sSYSTEM->_GET["sViewport"]]["name"]));
		eval($this->sSYSTEM->sCallHookPoint("s_sale.php_sRender_BeforeEnd"));
		return array("templates"=>$templates,"variables"=>$variables);
	}
	
	function sDeleteAllFiles ($dir, $rek=false)
	{
		$dir = realpath($dir)."/";
		if(!is_writable($dir)||!is_readable($dir))
			return false;
		$dh = opendir($dir);
		if(!$dh)
			return false;
		while (($file = readdir($dh)) !== false) {
			if($file=="."||$file=="..")
				continue;
	    	if(is_dir($dir . $file))
	    	{
	    		if($rek)
	    		{
		    		if(!$this->sDeleteAllFiles($dir . $file))
		    			return false;
		    		if(!rmdir($dir . $file))
		    			return false;
	    		}
	    	}
	    	elseif(is_file($dir . $file))
	    	{
	    		if(!unlink($dir . $file))
	    			return false;
	    	}
	    }
	    closedir($dh);
	    return true;
	}
	
	function checkForArticle($content){
		
		foreach ($content as $position){
			if ($position["modus"]==0){
				
				return true;
			}
		}
		return false;
	}
}
?>