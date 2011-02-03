<?php

class sViewportBasket{
	var $sSYSTEM;
	
	function sRender(){
		
		
		if ($this->sSYSTEM->sMODULES['sAdmin']->sCheckUser()){
			$variables["sUserData"] = $this->sSYSTEM->sMODULES['sAdmin']->sGetUserData();	
			$this->sSYSTEM->_POST['sCountry'] = (int) $variables["sUserData"]["shippingaddress"]["countryID"];
			$this->sSYSTEM->_POST['sPayment'] = (int) $variables["sUserData"]["additional"]["user"]["paymentID"];
			
		} else {
			if(isset($this->sSYSTEM->_SESSION['sPaymentID'])&&!isset($this->sSYSTEM->_POST['sPayment']))
				$this->sSYSTEM->_POST['sPayment'] = (int) $this->sSYSTEM->_SESSION['sPaymentID'];
			if(isset($this->sSYSTEM->_SESSION['sCountry'])&&!isset($this->sSYSTEM->_POST['sCountry']))
				$this->sSYSTEM->_POST['sCountry'] = (int) $this->sSYSTEM->_SESSION['sCountry'];
			
		}
	
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
		elseif(!empty($this->sSYSTEM->_POST['sCountry'])) 
		{
			$this->sSYSTEM->_POST['sCountry'] = (int) $this->sSYSTEM->_POST['sCountry'];
			$sTaxFree = $this->sSYSTEM->sDB_CONNECTION->GetOne("
				SELECT taxfree FROM s_core_countries
				WHERE id={$this->sSYSTEM->_POST['sCountry']}
			");
		}
		if(!empty($sTaxFree))
		{
			$this->sSYSTEM->sUSERGROUPDATA["tax"] = 0;
			$this->sSYSTEM->sCONFIG['sARTICLESOUTPUTNETTO'] = 1;
			$this->sSYSTEM->_SESSION["sUserGroupData"] = $this->sSYSTEM->sUSERGROUPDATA;
		}
		/*/
		 * Individual Anpassung ENDE
		/*/

		/*
		If user returns to basket (browser back button etc.)
		Skipping article insert (2.1)
		*/
		if (!empty($this->sSYSTEM->_GET["sActionIdentifier"])){
				$sai = $this->sSYSTEM->_GET["sActionIdentifier"];
				if ($sai==$this->sSYSTEM->_SESSION["sActionIdentifier"]["sBasket"]){
					$skipInsert = true;
				}else {
					$skipInsert = false;
				}
		}
		
		
		
		// Live-Check if article is in stock 
		// r303,sth
		if (!empty($this->sSYSTEM->_GET["sAdd"]) || !empty($this->sSYSTEM->_GET["sArticle"])){
			
			if(!empty($this->sSYSTEM->_GET["sRelatedOrdernumbers"]))
				$this->sSYSTEM->_GET["sAddAccessories"] = $this->sSYSTEM->_GET["sRelatedOrdernumbers"];
			
			if (!empty($this->sSYSTEM->_GET["sAdd"])){
				$ordernumber = stripslashes($this->sSYSTEM->_GET["sAdd"]);
				
			}else {
				$getOrdernumber = "SELECT s_order_basket.ordernumber FROM s_order_basket
				WHERE id = ? AND sessionID= ?
				";
				$ordernumber = $this->sSYSTEM->sDB_CONNECTION->GetOne($getOrdernumber,array($this->sSYSTEM->_GET["sArticle"],$this->sSYSTEM->sSESSION_ID));
			}
			
			$instockSQL = "SELECT a.id AS id, instock,laststock FROM s_articles_details ad LEFT JOIN s_articles a ON a.id = ad.articleID 
			WHERE ad.ordernumber=?
			UNION SELECT valueID AS id, instock, laststock FROM s_articles_groups_value av 
			LEFT JOIN s_articles a ON a.id = av.articleID
			WHERE av.ordernumber=?
			";
			
			$getInstock = $this->sSYSTEM->sDB_CONNECTION->GetRow($instockSQL,array($ordernumber,$ordernumber));
			
			if (!empty($getInstock["id"]) && !empty($getInstock["laststock"])){
				
				$quantity = (int)$this->sSYSTEM->_GET["sQuantity"];
				if (empty($quantity)) $quantity = 1;
				$sql = "
				SELECT id, quantity FROM s_order_basket WHERE sessionID='".$this->sSYSTEM->sSESSION_ID."' AND
				ordernumber=?";
				
				$getQuantity = $this->sSYSTEM->sDB_CONNECTION->GetRow($sql, array($ordernumber));
				
				if (!empty($getQuantity["id"]) && !empty($this->sSYSTEM->_GET["sAdd"])){
					$quantity += $getQuantity["quantity"];
				}
				
				if ($getInstock["instock"]<=$quantity){
					// Check if article is already in basket
					if ($getInstock["instock"]>0 && ($getInstock["instock"] < $quantity)){
						$variables["sBasketInfo"] = $this->sSYSTEM->sCONFIG["sSnippets"]["sBasketLessStockRest"];	//sBasketLessStockRest
						$variables["sBasketInfo"] = str_replace("x",$getInstock["instock"],$variables["sBasketInfo"]);
						$variables["sBasketInfo"] = str_replace("y",$quantity,$variables["sBasketInfo"]);
						
						if (empty($this->sSYSTEM->_GET["sAdd"])){
							if ($quantity>$getInstock["instock"]){
								$quantity = $getInstock["instock"];	
							}
							$this->sSYSTEM->_GET["sQuantity"] = $quantity;
						}else {
							
							if ($quantity>$getInstock["instock"]) $quantity = $getInstock["instock"]; 
							$this->sSYSTEM->_GET["sQuantity"] = $quantity; 
							
						}
						
						if ($this->sSYSTEM->_GET["sQuantity"]<=0) unset($this->sSYSTEM->_GET["sAdd"]);
						
					}elseif($getInstock["instock"]<=0) {
						
						$variables["sBasketInfo"] = $this->sSYSTEM->sCONFIG["sSnippets"]["sBasketLessStock"];	//sBasketLessStockRest
						unset($this->sSYSTEM->_GET["sAdd"]);
					}
					
					
				}
			}elseif($this->sSYSTEM->sCONFIG["sINSTOCKINFO"]){
				if (!empty($this->sSYSTEM->_GET["sAdd"])){
					$instockSQL = "SELECT a.id AS id, instock,laststock FROM s_articles_details ad LEFT JOIN s_articles a ON a.id = ad.articleID 
					WHERE ad.ordernumber=?
					UNION SELECT valueID AS id, instock, laststock FROM s_articles_groups_value av 
					LEFT JOIN s_articles a ON a.id = av.articleID
					WHERE av.ordernumber=?
					";
					$getInstock = $this->sSYSTEM->sDB_CONNECTION->GetRow($instockSQL,array($this->sSYSTEM->_GET["sAdd"],$this->sSYSTEM->_GET["sAdd"]));
				}elseif (!empty($this->sSYSTEM->_GET["sArticle"])){
					$instockSQL = "
						SELECT a.id AS id, instock,laststock 
						FROM 
						s_order_basket,
						s_articles_details ad 
						LEFT JOIN s_articles a ON a.id = ad.articleID 
						WHERE ad.ordernumber=s_order_basket.ordernumber
						AND s_order_basket.id = ?
					UNION 
						SELECT valueID AS id, instock, laststock FROM s_order_basket,s_articles_groups_value av 
						LEFT JOIN s_articles a ON a.id = av.articleID
						WHERE av.ordernumber=s_order_basket.ordernumber
						AND s_order_basket.id = ?
					
					";
					$getInstock = $this->sSYSTEM->sDB_CONNECTION->GetRow($instockSQL,array($this->sSYSTEM->_GET["sArticle"],$this->sSYSTEM->_GET["sArticle"]));
				}else {
					
				}
				
				
				$quantity = (int)$this->sSYSTEM->_GET["sQuantity"];
				if (empty($quantity)) $quantity = 1;
				$sql = "
				SELECT id, quantity FROM s_order_basket WHERE sessionID='".$this->sSYSTEM->sSESSION_ID."' AND
				ordernumber=?";
				
				$getQuantity = $this->sSYSTEM->sDB_CONNECTION->GetRow($sql, array($ordernumber));
				
				if (!empty($getQuantity["id"]) && !empty($this->sSYSTEM->_GET["sAdd"])){
					$quantity += $getQuantity["quantity"];
				}
				if ($getInstock["instock"]<=$quantity){
						
					// Check if article is already in basket
					if ($getInstock["instock"]>0 && ($getInstock["instock"] < $quantity)){
					
						$variables["sBasketInfo"] = $this->sSYSTEM->sCONFIG["sSnippets"]["sBasketLessStockRest"];	//sBasketLessStockRest
						$variables["sBasketInfo"] = str_replace("x",$getInstock["instock"],$variables["sBasketInfo"]);
						$variables["sBasketInfo"] = str_replace("y",$quantity,$variables["sBasketInfo"]);
						if (empty($this->sSYSTEM->_GET["sAdd"])){
							if ($quantity>$getInstock["instock"]){
								$quantity = $getInstock["instock"];	
							}
						}else {
							if ($quantity>$getInstock["instock"]) $quantity = $getInstock["instock"]; 
						}
					}
				}
			}
		}
		
		// Add article to basket
		if (!empty($this->sSYSTEM->_GET["sAdd"]) && !$skipInsert){
			
			if ($this->sSYSTEM->_GET["sAddAccessories"]){
				$Accessories = explode(";",$this->sSYSTEM->_GET["sAddAccessories"]);
				foreach ($Accessories as $Accessory){
					if ($Accessory){
						$this->sSYSTEM->sMODULES['sBasket']->sAddArticle($Accessory,1);
					}
				}
			}
			
			$this->sSYSTEM->sMODULES['sBasket']->sAddArticle($this->sSYSTEM->_GET["sAdd"],$this->sSYSTEM->_GET["sQuantity"]);
			// Unique identify this insert
			if (!empty($this->sSYSTEM->_GET["sActionIdentifier"])){
				$this->sSYSTEM->_SESSION["sActionIdentifier"]["sBasket"] = $this->sSYSTEM->_GET["sActionIdentifier"];
			}
			
			// Cross-Selling
			$articleID = $this->sSYSTEM->sMODULES['sArticles']->sGetArticleIdByOrderNumber($this->sSYSTEM->_GET["sAdd"]);
			$articleName = $this->sSYSTEM->sMODULES['sArticles']->sGetArticleNameByOrderNumber($this->sSYSTEM->_GET["sAdd"]);
			
			$variables["sArticleName"] = $articleName;
		
			
			if ($articleID){
				$this->sSYSTEM->sMODULES['sCrossselling']->sBlacklist = $this->sSYSTEM->sMODULES['sBasket']->sGetBasketIds();
				
				// Articles which was shown in prior sessions
				$getSimilaryShownArticles = $this->sSYSTEM->sMODULES['sCrossselling']->sGetSimilaryShownArticles($articleID);
				if (count($getSimilaryShownArticles)){
					foreach ($getSimilaryShownArticles as $article){
						$tmpContainer = $this->sSYSTEM->sMODULES['sArticles']->sGetPromotionById("fix",0,$article['id']);
						if (count($tmpContainer) && isset($tmpContainer["articleName"])){
							$variables["sCrossSimilarShown"][] = $tmpContainer;
						}
					}
				}
				
				// Articles which was bought in prior sessions
				$getAlsoBoughtArticles = $this->sSYSTEM->sMODULES['sCrossselling']->sGetAlsoBoughtArticles($articleID);
				if (count($getAlsoBoughtArticles)){
					foreach ($getAlsoBoughtArticles as $article){
						$tmpContainer = $this->sSYSTEM->sMODULES['sArticles']->sGetPromotionById("fix",0,$article['id']);
						if (count($tmpContainer) && isset($tmpContainer["articleName"])){
							$variables["sCrossBoughtToo"][] = $tmpContainer;
						}
					}
				}
				
			}else {
				
				//$variables["sNotFound"] = true;
			}
		}

		if (!empty($this->sSYSTEM->_GET["sQuantity"]) && empty($this->sSYSTEM->_GET["sAdd"])){
			$this->sSYSTEM->sMODULES['sBasket']->sUpdateArticle ($this->sSYSTEM->_GET["sArticle"],$this->sSYSTEM->_GET["sQuantity"]);
		}
		// Delete article from basket
		if (!empty($this->sSYSTEM->_GET["sDelete"])){
			$this->sSYSTEM->sMODULES['sBasket']->sDeleteArticle($this->sSYSTEM->_GET["sDelete"]);
		}
		
		//Bundle -START 
		if (!empty($this->sSYSTEM->_GET["sAddBundle"]) && !empty($this->sSYSTEM->_GET["sBID"])){ 
			$this->sSYSTEM->sMODULES['sBasket']->sAddBundleArticle($this->sSYSTEM->_GET["sAddBundle"], $this->sSYSTEM->_GET["sBID"]); 
		} 
		
		$this->sSYSTEM->sMODULES['sBasket']->sCheckBasketBundles(); 
		//Bundle -END 
		
		
		//PayPal-Exress-START Get PayPal Status from s_core_paymentmeans		
		$sql = "SELECT active FROM s_core_paymentmeans WHERE class='paypalexpress.php'";
		$PaypalExpress = $this->sSYSTEM->sDB_CONNECTION->GetRow($sql);
		
		if($this->sSYSTEM->sLanguage == 1) 	$variables['sLang'] = 'DE';		
		
		if (($PaypalExpress['active']) && ($this->sSYSTEM->sCONFIG['sXPRESS'])){						

			$variables['PaypalStatus'] = true;
			$checkUser = $this->sSYSTEM->sMODULES['sAdmin']->sCheckUser();
			if ($this->sSYSTEM->_SESSION['GuestUser'] != "1" && $checkUser) {
				$variables['checkUser'] = true;			
			} else {
				$variables['checkUser'] = false;			
				
			}
			if (!empty($checkUser)){
				$variables['PaypalStatus'] = false;
			}
			
			if (preg_match("/443/",$_SERVER['SERVER_PORT'])){
				$variables["serverName"] = "https://".$this->sSYSTEM->sCONFIG["sBASEPATH"]."/";
			}else {
				$variables["serverName"] = "http://".$this->sSYSTEM->sCONFIG["sBASEPATH"]."/";
			}
		}
		//PayPal-Express-END
		$templates = array("sContainer"=>"/basket/basket_middle.tpl","sContainerRight"=>"/basket/basket_right2.tpl");
		

		
		//print_r($variables["sPremiums"]);
		$variables["sBreadcrumb"] = array(0=>array("name"=>$this->sSYSTEM->sCONFIG['sViewports'][$this->sSYSTEM->_GET["sViewport"]]["name"]));
		
			
		/*
		Shopware 2.0.4 - Different dispatches -
		*/
		
		if(isset($this->sSYSTEM->_SESSION['sDispatch'])&&!isset($this->sSYSTEM->_POST['sDispatch'])){
			$this->sSYSTEM->_POST['sDispatch'] = (int) $this->sSYSTEM->_SESSION['sDispatch'];
		}
				
		if (!$this->sSYSTEM->_POST["sCountry"]){
			// Erstes Land aus der Liste auslesen
			$getDefaultCountry = $this->sSYSTEM->sDB_CONNECTION->GetRow("
			SELECT id FROM s_core_countries ORDER BY position ASC LIMIT 1
			");
			$this->sSYSTEM->_POST["sCountry"] = $getDefaultCountry["id"]; // Default: Deutschland
		}
		
		$variables["sDispatches"] = $this->sSYSTEM->sMODULES['sAdmin']->sGetDispatches($this->sSYSTEM->_POST["sCountry"]);
		if(empty($variables["sDispatches"])&&!empty($this->sSYSTEM->sCONFIG['sPREMIUMSHIPPIUNGNOORDER']))
		{
			$variables["sDispatchNoOrder"] = true;
		}
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

		if(isset($selectedDispatch["id"])){
			$this->sSYSTEM->_SESSION["sDispatch"] = $selectedDispatch["id"];
		} else {
			$this->sSYSTEM->_SESSION['sDispatch'] = null;
		}
		
		
		$variables["selectedDispatch"] = $selectedDispatch;
		
		// 
		/*
		// Shopware 2.0.4 - Different dispatches -
		*/
		
		// Information for calculating shipping-costs
		$variables["sCountryList"] = $this->sSYSTEM->sMODULES['sAdmin']->sGetCountryList();
		
		
		$countryCounter = 0;
		foreach ($variables["sCountryList"] as $countryKey => $countryValue){
			// Default = first country in list
			if (!$countryCounter) $selectedCountry = $countryValue;
			
			if ($countryValue["id"]==$this->sSYSTEM->_POST["sCountry"]){
				// Overwrite default if any country was selected
				$variables["sCountryList"][$countryKey]["flag"] = true;
				$selectedCountry = $countryValue;
			}
			$countryCounter++;
		}
		
		if(isset($selectedCountry["id"]))
			$this->sSYSTEM->_SESSION["sCountry"] = $selectedCountry["id"];
		
		$variables["sPayments"] = ($this->sSYSTEM->sMODULES['sAdmin']->sGetPaymentMeans());
		
		$paymentCounter = 0;
		foreach ($variables["sPayments"] as $paymentKey => $paymentValue){
			// Default payment = first payment in list
			if (!$paymentCounter){
				$selectedPayment = $paymentValue;
			}
			
			if ($paymentValue["id"]==$this->sSYSTEM->_POST["sPayment"]){
				$variables["sPayments"][$paymentKey]["flag"] = true;
				$selectedPayment = $paymentValue;
				
			}
			$paymentCounter++;
		}
		
		// Pass country-id, to filter list for payment-means allowed in country
		if(isset($selectedCountry["id"]))
			$this->sSYSTEM->_SESSION["sCountry"] = $selectedCountry["id"];
		if(isset($selectedPayment["id"]))
			$this->sSYSTEM->_SESSION["sPaymentID"] = $selectedPayment["id"];
			
		
		// Check for minimum-surcharge
		$variables["sMinimumSurcharge"] = $this->sSYSTEM->sMODULES['sBasket']->sCheckMinimumCharge();
		if ($variables["sMinimumSurcharge"]) $variables["sMinimumSurcharge"] = $this->sSYSTEM->sMODULES['sArticles']->sFormatPrice($variables["sMinimumSurcharge"]);
		
		
		
		// Calculating shipping-costs
		$variables["sShippingcosts"] = $this->sSYSTEM->sMODULES['sAdmin']->sGetShippingcosts($selectedCountry,$selectedPayment["surcharge"],$selectedPayment["surchargestring"]);
		
		$variables["sBasket"] = $this->sSYSTEM->sMODULES['sBasket']->sGetBasket();
		//$variables["sBasket"] = $this->sSYSTEM->sMODULES['sBasket']->sGetBasketWeight();
	
		if ($variables["sShippingcosts"]["brutto"]){
			$variables["sShippingcostsDifference"] = $variables["sShippingcosts"]["difference"];
			
			// Task, differ between net and brutto
			$variables["sBasket"]["AmountNetNumeric"] += $variables["sShippingcosts"]["netto"];
			$variables["sBasket"]["AmountNumeric"] += $variables["sShippingcosts"]["brutto"];
			
			if ((!$this->sSYSTEM->sUSERGROUPDATA["tax"] && $this->sSYSTEM->sUSERGROUPDATA["id"])){
				$variables["sShippingcosts"] = $this->sSYSTEM->sMODULES['sArticles']->sFormatPrice($variables["sShippingcosts"]["netto"]);
				$variables["sAmount"] = $this->sSYSTEM->sMODULES['sArticles']->sFormatPrice(round($variables["sBasket"]["AmountNetNumeric"],2));
			}else {
				$variables["sShippingcosts"] = $this->sSYSTEM->sMODULES['sArticles']->sFormatPrice($variables["sShippingcosts"]["brutto"]);
				$variables["sAmount"] = $this->sSYSTEM->sMODULES['sArticles']->sFormatPrice($variables["sBasket"]["AmountNumeric"]);
				$variables["sAmountTax"] = $this->sSYSTEM->sMODULES['sArticles']->sFormatPrice($variables["sBasket"]["AmountNumeric"]-$variables["sBasket"]["AmountNetNumeric"]);
			}
		
		}else {
		
			// Shipping-Free
			$variables["sAmount"] = $this->sSYSTEM->sMODULES['sArticles']->sFormatPrice($variables["sBasket"]["AmountNumeric"]);
			$variables["sShippingcosts"] = "0,00";
		}
		
		$variables["sPremiums"] = $this->sSYSTEM->sMODULES['sMarketing']->sGetPremiums();
		
		// Show inquiry-formular
		$variables["sInquiryLink"] = $this->sSYSTEM->sCONFIG['sBASEFILE']."?sViewport=support&sFid=".$this->sSYSTEM->sCONFIG["sINQUIRYID"]."&sInquiry=basket";
		if (!empty($this->sSYSTEM->sCONFIG["sINQUIRYVALUE"])){
			if (empty($this->sSYSTEM->sCurrency["factor"])) $this->sSYSTEM->sCurrency["factor"] = 1;
			$value = $this->sSYSTEM->sCONFIG["sINQUIRYVALUE"]*$this->sSYSTEM->sCurrency["factor"];
			if ((!$this->sSYSTEM->sUSERGROUPDATA["tax"] && $this->sSYSTEM->sUSERGROUPDATA["id"])){
				$amount = $variables["sBasket"]["AmountWithTaxNumeric"];
			}else {
				$amount = $variables["sBasket"]["AmountNumeric"];
			}
			if (!empty($amount) && $amount >= $value){
				// Show Link to inquiry formular
				$variables["sInquiry"] = $variables["sInquiryLink"];
			}
		}
		
		
		return array("templates"=>$templates,"variables"=>$variables);
	}
}
?>