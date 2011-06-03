<?php
/**
 * Shopware Order Model
 * 
 * @link http://www.shopware.de
 * @package core
 * @subpackage class
 * @copyright (C) Shopware AG 2002-2010
 * @version Shopware 3.5.0
 */
class sOrder
{	/**
     * Array with userdata
     *
     * @var array
     */
	var $sUserData;
	/**
     * Array with basketdata
     *
     * @var array
     */
	var $sBasketData;
	/**
     * Array with shipping / dispatch data
     *
     * @var array
     */
	var $sShippingData;
	/**
     * User comment to save within this order
     *
     * @var string
     */
	var $sComment;
	/**
     * Payment-mean object
     *
     * @var object
     */
	var $paymentObject;
	/**
     * Total amount net
     *
     * @var double
     */
	var $sAmountNet;
	/**
     * Total Amount
     *
     * @var double
     */
	var $sAmount;
	/**
     * Total Amount with tax (force)
     *
     * @var double
     */
	var $sAmountWithTax;
	/**
     * Shipppingcosts
     *
     * @var double
     */
	var $sShippingcosts;
	/**
     * Shippingcosts unformated
     *
     * @var double
     */
	var $sShippingcostsNumeric;
	/**
     * Shippingcosts net unformated
     *
     * @var double
     */
	var $sShippingcostsNumericNet;
	/**
     * Pointer to sSystem object
     *
     * @var object
     */
	var $sSYSTEM;
	/**
     * TransactionID (epayment)
     *
     * @var string
     */
	var $bookingId;
	/**
     * Ordernumber
     *
     * @var string
     */
	var $sOrderNumber;
	/**
     * ID of choosen dispatch
     *
     * @var int
     */
	var $dispatchId;
	/**
     * Random id to identify the order
     *
     * @var string
     */
	var $uniqueID;
	/**
     * Net order true /false
     *
     * @var bool
     */
	var $sNet; 	// Complete taxfree
	
	/**
	 * @var string
	 */
	var $o_attr_1, $o_attr_2,$o_attr_3,$o_attr_4,$o_attr_5,$o_attr_6; 

	/**
	 * Get a unique ordernumber
	 * @access public
	 * @return string ordernumber
	 */
	public function sGetOrderNumber()
	{
		eval($this->sSYSTEM->sCallHookPoint("sOrder.php_sGetOrderNumber_Start"));
		$sql = "/*NO LIMIT*/ SELECT number FROM s_order_number WHERE name='invoice' FOR UPDATE";
		$ordernumber = $this->sSYSTEM->sDB_CONNECTION->GetOne($sql);
		$sql = "UPDATE s_order_number SET number=number+1 WHERE name='invoice'";
		$this->sSYSTEM->sDB_CONNECTION->Execute($sql);
		$ordernumber += 1;
		eval($this->sSYSTEM->sCallHookPoint("sOrder.php_sGetOrderNumber_End"));
		$ordernumber = Enlight()->Events()->filter('Shopware_Modules_Order_GetOrdernumber_FilterOrdernumber', $ordernumber, array('subject'=>$this));
		return $ordernumber;
	}

	/**
	 * Check each basketrow for instant downloads
	 * @access public
	 */
	public function sManageEsdOrder(&$basketRow, $orderID,$orderdetailsID){
		$sqlGetEsd = "
		SELECT s_articles_esd.id AS id, serials FROM s_articles_esd,s_articles_details WHERE s_articles_esd.articleID={$basketRow["articleID"]}
		AND articledetailsID=s_articles_details.id AND s_articles_details.ordernumber='{$basketRow["ordernumber"]}'
		";

		$quantity = $basketRow["quantity"];

		$getEsd = $this->sSYSTEM->sDB_CONNECTION->CacheGetRow($this->sSYSTEM->sCONFIG['sCACHEARTICLE'],$sqlGetEsd);
		if ($getEsd["id"]){
			if ($getEsd["serials"]){
				// Check for free serials
				$sqlCheckSerials = "
				SELECT s_articles_esd_serials.id AS id
				FROM s_articles_esd_serials
				LEFT JOIN s_order_esd
				ON (s_articles_esd_serials.id
				  = s_order_esd.serialID)
				WHERE 
				s_order_esd.serialID IS NULL
				AND s_articles_esd_serials.esdID={$getEsd["id"]}
				";

				$getSerialsStatus = $this->sSYSTEM->sDB_CONNECTION->GetAll($sqlCheckSerials);
				if ((count($getSerialsStatus)<=$this->sSYSTEM->sCONFIG['sMINSERIALS']) || count($getSerialsStatus)<=$quantity){
					// No serialnumber anymore, inform merchant
					$sSystemMail           = $this->sSYSTEM->sMailer;
					$subject = $this->sSYSTEM->sCONFIG['sTemplates']['sNOSERIALS']['subject'];
					$subject = str_replace("{sArticleName}",$basketRow["articlename"],$subject);
					$content = $this->sSYSTEM->sCONFIG['sTemplates']['sNOSERIALS']['content'];
					$content = str_replace("{sArticleName}",$basketRow["articlename"],$content);
					$content = str_replace("{sMail}",$this->sUserData["additional"]["user"]["email"],$content);

					$sSystemMail->IsHTML($this->sSYSTEM->sCONFIG['sTemplates']['sNOSERIALS']['ishtml']);
					$sSystemMail->From     = $this->sSYSTEM->sCONFIG['sTemplates']['sNOSERIALS']['frommail'];
					$sSystemMail->FromName = $this->sSYSTEM->sCONFIG['sTemplates']['sNOSERIALS']['fromname'];
					$sSystemMail->Subject  = $subject;
					$sSystemMail->Body     = $content;
					$sSystemMail->ClearAddresses();
					if ($this->sSYSTEM->sCONFIG['sESDMAIL']){
						$sSystemMail->AddAddress($this->sSYSTEM->sCONFIG['sESDMAIL'], "");
					}else {
						$sSystemMail->AddAddress($this->sSYSTEM->sCONFIG['sMAIL'], "");
					}
					if (!$sSystemMail->Send()){
						$this->sSYSTEM->E_CORE_WARNING("##sOrder-sSaveOrder-#ESD-1","Could not send serial-notification mail");
					}

					if (count($getSerialsStatus)>=$quantity){
						for ($i=1;$i<=$quantity;$i++){
							$serial = $getSerialsStatus[$i-1]["id"];
							if (empty($serial)){

							}else {
								$sql = "
								INSERT INTO s_order_esd
								(serialID, esdID, userID, orderID, orderdetailsID, datum)
								VALUES ($serial,{$getEsd["id"]},".$this->sUserData["additional"]["user"]["id"].",$orderID,$orderdetailsID,now())";

								$updateSerial = $this->sSYSTEM->sDB_CONNECTION->Execute($sql);
							}
						}
					}

				}else {
					for ($i=1;$i<=$quantity;$i++){
						// Assign serialnumber
						$serial = $getSerialsStatus[$i-1]["id"];
						if (empty($serial)){

						}else {
							$sql = "
						INSERT INTO s_order_esd
						(serialID, esdID, userID, orderID, orderdetailsID, datum)
						VALUES ($serial,{$getEsd["id"]},".$this->sUserData["additional"]["user"]["id"].",$orderID,$orderdetailsID,now())";

							$updateSerial = $this->sSYSTEM->sDB_CONNECTION->Execute($sql);
						}
					}
				} // Serialnumbers available?
			} else {
				// If serialnumber is needed
				$updateSerial = $this->sSYSTEM->sDB_CONNECTION->Execute("
					INSERT INTO s_order_esd
					(serialID, esdID, userID, orderID, orderdetailsID, datum)
					VALUES (0,{$getEsd["id"]},".$this->sUserData["additional"]["user"]["id"].",$orderID,$orderdetailsID,now())");
			}
		} // If esd-article is available
	}

	/**
	 * Delete temporary created order
	 * @access public
	 */
	public function sDeleteTemporaryOrder(){
		if (empty($this->sSYSTEM->sSESSION_ID)) return;
		$deleteWholeOrder = $this->sSYSTEM->sDB_CONNECTION->GetAll("
		SELECT * FROM s_order WHERE temporaryID = ? LIMIT 2
		",array($this->sSYSTEM->sSESSION_ID));


		foreach ($deleteWholeOrder as $orderDelete){
			$deleteOrder =  $this->sSYSTEM->sDB_CONNECTION->Execute("
			DELETE FROM s_order WHERE id = ?
			",array($orderDelete["id"]));

			$deleteSubOrder = $this->sSYSTEM->sDB_CONNECTION->Execute("
			DELETE FROM s_order_details
			WHERE orderID=?
			",array($orderDelete["id"]));
		}
	}

	/**
	 * Create temporary order (for order cancelation reports)
	 * @access public
	 */
	public function sCreateTemporaryOrder(){

		$this->sShippingData["AmountNumeric"] = $this->sShippingData["AmountNumeric"] ? $this->sShippingData["AmountNumeric"] : "0";
		if (!$this->sShippingcostsNumeric) $this->sShippingcostsNumeric = "0";
		if (!$this->sBasketData["AmountWithTaxNumeric"]) $this->sBasketData["AmountWithTaxNumeric"] = $this->sBasketData["AmountNumeric"];

		// Check if tax-free
		if (($this->sSYSTEM->sCONFIG['sARTICLESOUTPUTNETTO'] && !$this->sSYSTEM->sUSERGROUPDATA["tax"]) || (!$this->sSYSTEM->sUSERGROUPDATA["tax"] && $this->sSYSTEM->sUSERGROUPDATA["id"])){
			$net = "1";
		}else {
			$net = "0";
		}

		$this->sBasketData["AmountNetNumeric"] = round($this->sBasketData["AmountNetNumeric"],2);
		if ($this->dispatchId){
			$dispatchId = $this->dispatchId;
		}else {
			$dispatchId = "0";
		}

		$this->sBasketData["AmountNetNumeric"] = round($this->sBasketData["AmountNetNumeric"],2);

		if (empty($this->sSYSTEM->sCurrency["currency"])) $this->sSYSTEM->sCurrency["currency"] = "EUR";
		if (empty($this->sSYSTEM->sCurrency["factor"])) $this->sSYSTEM->sCurrency["factor"] = "1";
		if (empty($this->sSYSTEM->sLanguageData[$language]["isocode"])){
			$this->sSYSTEM->sLanguageData[$language]["isocode"] = "de";
		}
		if (empty($this->sSYSTEM->sSubShop["id"])){
			// Set to default / main shop
			$getDefaultSubshop = $this->sSYSTEM->sDB_CONNECTION->GetRow("
			SELECT id FROM s_core_multilanguage WHERE default = 1
			");
			if ($getDefaultSubshop["id"]){
				$this->sSYSTEM->sSubShop["id"] = $getDefaultSubshop["id"];
			}else {
				$this->sSYSTEM->sSubShop["id"] = "0";
			}
		}
		$taxfree = "0";
		if (!empty($this->sNet)){
			// Complete net delivery
			$net = "1";
			$this->sBasketData["AmountWithTaxNumeric"] = $this->sBasketData["AmountNetNumeric"];
			$this->sShippingcostsNumeric = $this->sShippingcostsNumericNet;
			$taxfree = "1";
		}
		if (empty($this->sBasketData["AmountWithTaxNumeric"])) $this->sBasketData["AmountWithTaxNumeric"] = '0';
		if (empty($this->sBasketData["AmountNetNumeric"])) $this->sBasketData["AmountNetNumeric"] = '0';


		$sql = "
			INSERT INTO s_order (ordernumber, userID, invoice_amount,invoice_amount_net, invoice_shipping,invoice_shipping_net, ordertime, status, paymentID,  customercomment, net,taxfree, partnerID,temporaryID,referer,language,dispatchID,currency,currencyFactor,subshopID)
			VALUES ('0',
			?,
			?,
			?,
			?,
			?,
			now(),
			-1,
			?,
			?,
			?,
			?,
			?,
			?,
			?,
			?,
			?,
			?,
			?,
			?
			)
		";
		$insertOrder = $this->sSYSTEM->sDB_CONNECTION->Execute($sql, array(
			$this->sUserData["additional"]["user"]["id"],
			$this->sBasketData["AmountWithTaxNumeric"],
			$this->sBasketData["AmountNetNumeric"],
			$this->sShippingcostsNumeric,
			$this->sShippingcostsNumericNet,
			$this->sUserData["additional"]["user"]["paymentID"],
			$this->sComment,
			$net,
			$taxfree,
			(string)$this->sSYSTEM->_SESSION["sPartner"],
			$this->sSYSTEM->sSESSION_ID,
			(string)$this->sSYSTEM->_SESSION['sReferer'],
			$this->sSYSTEM->sLanguageData[$language]["isocode"],
			$dispatchId,
			$this->sSYSTEM->sCurrency["currency"],
			$this->sSYSTEM->sCurrency["factor"],
			$this->sSYSTEM->sSubShop["id"]
		));

		$orderID = $this->sSYSTEM->sDB_CONNECTION->Insert_ID();

		if ($this->sSYSTEM->sDB_CONNECTION->ErrorMsg() || (!$orderID || !$insertOrder)){
			$this->sSYSTEM->E_CORE_ERROR("##sOrder-sTemporaryOrder-#01",$this->sSYSTEM->sDB_CONNECTION->ErrorMsg().$sql);
			die ("Could not create temporary order");
		}

		$position = 0;
		foreach ($this->sBasketData["content"] as $basketRow){
			$position++;

			$amountRow = $this->sSYSTEM->sMODULES['sArticles']->sFormatPrice($basketRow["priceNumeric"] * $basketRow["quantity"]);

			if (!$basketRow["price"]) $basketRow["price"] = "0,00";
			if (!$amountRow) $amountRow = "0,00";


			$basketRow["articlename"] = html_entity_decode($basketRow["articlename"]);
			$basketRow["articlename"] = strip_tags($basketRow["articlename"]);

			if (!$basketRow["itemInfo"]){
				$priceRow = $basketRow["price"];
			}else {
				$priceRow = $basketRow["itemInfo"];
			}

			$basketRow["articlename"] = $this->sSYSTEM->sMODULES['sArticles']->sOptimizeText($basketRow["articlename"]);

			if (!$basketRow["esdarticle"]) $basketRow["esdarticle"] = "0";
			if (!$basketRow["modus"]) $basketRow["modus"] = "0";
			if (!$basketRow["taxID"]) $basketRow["taxID"] = "0";

			$sql = "
				INSERT INTO s_order_details (
					orderID,
					ordernumber,
					articleID,
					articleordernumber,
					price,
					quantity,
					name,
					status,
					releasedate,
					modus,
					esdarticle,
					taxID
				) VALUES (
					?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
				);
			";
			$this->sSYSTEM->sDB_CONNECTION->Execute($sql,array(
				$orderID,
				0,
				$basketRow["articleID"],
				$basketRow["ordernumber"],
				$basketRow["priceNumeric"],
				$basketRow["quantity"],
				$basketRow["articlename"],
				0,
				'0000-00-00',
				$basketRow["modus"],
				$basketRow["esdarticle"],
				$basketRow["taxID"]
			));
			$orderdetailsID = $this->sSYSTEM->sDB_CONNECTION->Insert_ID();
			if ($this->sSYSTEM->sDB_CONNECTION->ErrorMsg() || !$orderID){
				$this->sSYSTEM->E_CORE_ERROR("##sOrder-sTemporaryOrder-Position-#02",$this->sSYSTEM->sDB_CONNECTION->ErrorMsg());
				die ("Could not create temporary order - row");
			}
		} // For every artice in basket
		return;
	}

	/**
	 * Finaly save order and send order confirmation to customer
	 * @access public
	 */
	public function sSaveOrder()
	{

		$this->sComment = stripslashes($this->sComment);
		$this->sComment = stripcslashes($this->sComment);


		$this->sShippingData["AmountNumeric"] = $this->sShippingData["AmountNumeric"] ? $this->sShippingData["AmountNumeric"] : "0";


		if (strlen($this->bookingId)>3){
			$insertOrder = $this->sSYSTEM->sDB_CONNECTION->GetRow("
			SELECT id FROM s_order WHERE transactionID=? AND status != -1
			",array($this->bookingId));
			if ($insertOrder["id"]){
				return false;
			}
		}
		// Insert basic-data of the order
		$orderNumber = $this->sGetOrderNumber();
		$this->sOrderNumber = $orderNumber;

		if (!$this->sShippingcostsNumeric) $this->sShippingcostsNumeric = "0";

		if (!$this->sBasketData["AmountWithTaxNumeric"]) $this->sBasketData["AmountWithTaxNumeric"] = $this->sBasketData["AmountNumeric"];

		// Check if tax-free
		if (($this->sSYSTEM->sCONFIG['sARTICLESOUTPUTNETTO'] && !$this->sSYSTEM->sUSERGROUPDATA["tax"]) || (!$this->sSYSTEM->sUSERGROUPDATA["tax"] && $this->sSYSTEM->sUSERGROUPDATA["id"])){
			$net = "1";
		}else {
			$net = "0";
		}

		if ($this->dispatchId){
			$dispatchId = $this->dispatchId;
		}else {
			$dispatchId = "0";
		}

		$this->sBasketData["AmountNetNumeric"] = round($this->sBasketData["AmountNetNumeric"],2);

		if (empty($this->sSYSTEM->sCurrency["currency"])) $this->sSYSTEM->sCurrency["currency"] = "EUR";
		if (empty($this->sSYSTEM->sCurrency["factor"])) $this->sSYSTEM->sCurrency["factor"] = "1";
		if (empty($this->sSYSTEM->sLanguageData[$this->sSYSTEM->sLanguage]["isocode"])){
			$this->sSYSTEM->sLanguageData[$this->sSYSTEM->sLanguage]["isocode"] = "de";
		}
		if (empty($this->sSYSTEM->sSubShop["id"])){
			// Set to default / main shop
			$getDefaultSubshop = $this->sSYSTEM->sDB_CONNECTION->GetRow("
			SELECT id FROM s_core_multilanguage WHERE default = 1
			");
			if ($getDefaultSubshop["id"]){
				$this->sSYSTEM->sSubShop["id"] = $getDefaultSubshop["id"];
			}else {
				$this->sSYSTEM->sSubShop["id"] = "0";
			}
		}
		eval($this->sSYSTEM->sCallHookPoint("sOrder.php_sSaveOrder_BeforeInsertMain"));
		$taxfree = "0";
		if (!empty($this->sNet)){
			// Complete net delivery
			$net = "1";
			$this->sBasketData["AmountWithTaxNumeric"] = $this->sBasketData["AmountNetNumeric"];
			$this->sShippingcostsNumeric = $this->sShippingcostsNumericNet;
			$taxfree = "1";
		}

		//unset($this->sSYSTEM->_SESSION["sPartner"]);
		if (empty($this->sSYSTEM->_SESSION["sPartner"])){
			//"additional"]["user"]
			$pid = $this->sUserData["additional"]["user"]["affiliate"];

			if (!empty($pid) && $pid != "0"){
				// Get Partner code
				$partner = $this->sSYSTEM->sDB_CONNECTION->GetOne("
				SELECT idcode FROM s_emarketing_partner WHERE id = ?
				",array($pid));
			}
		}else {
			$partner = $this->sSYSTEM->_SESSION["sPartner"];
		}

		$sql = "
		INSERT INTO s_order (ordernumber, userID, invoice_amount,invoice_amount_net, invoice_shipping,invoice_shipping_net, ordertime, status, cleared, paymentID, transactionID, customercomment, net,taxfree, partnerID,temporaryID,referer,language,dispatchID,currency,currencyFactor,subshopID,o_attr1,o_attr2,o_attr3,o_attr4,o_attr5,o_attr6,remote_addr)
		VALUES ('".$orderNumber."',
			".$this->sUserData["additional"]["user"]["id"].",
			".$this->sBasketData["AmountWithTaxNumeric"].",
			".$this->sBasketData["AmountNetNumeric"].",
			".floatval($this->sShippingcostsNumeric).",
			".floatval($this->sShippingcostsNumericNet).",
			now(),
			0,
			17,
			".$this->sUserData["additional"]["user"]["paymentID"].",
			'".$this->bookingId."',
			".$this->sSYSTEM->sDB_CONNECTION->qstr($this->sComment).",
			$net,
			$taxfree,
			".$this->sSYSTEM->sDB_CONNECTION->qstr((string) $partner).",
			".$this->sSYSTEM->sDB_CONNECTION->qstr((string) $this->uniqueID).",
			".$this->sSYSTEM->sDB_CONNECTION->qstr((string) $this->sSYSTEM->_SESSION['sReferer']).",
			'".$this->sSYSTEM->sLanguageData[$this->sSYSTEM->sLanguage]["isocode"]."',
			'$dispatchId',
			'".$this->sSYSTEM->sCurrency["currency"]."',
			'".$this->sSYSTEM->sCurrency["factor"]."',
			'".$this->sSYSTEM->sSubShop["id"]."',
			".$this->sSYSTEM->sDB_CONNECTION->qstr((string) $this->o_attr_1).",
			".$this->sSYSTEM->sDB_CONNECTION->qstr((string) $this->o_attr_2).",
			".$this->sSYSTEM->sDB_CONNECTION->qstr((string) $this->o_attr_3).",
			".$this->sSYSTEM->sDB_CONNECTION->qstr((string) $this->o_attr_4).",
			".$this->sSYSTEM->sDB_CONNECTION->qstr((string) $this->o_attr_5).",
			".$this->sSYSTEM->sDB_CONNECTION->qstr((string) $this->o_attr_6).",
			".$this->sSYSTEM->sDB_CONNECTION->qstr((string) $_SERVER['REMOTE_ADDR'])."
		)
		";

		$sql = Enlight()->Events()->filter('Shopware_Modules_Order_SaveOrder_FilterSQL', $sql, array('subject'=>$this));

		eval($this->sSYSTEM->sCallHookPoint("sOrder.php_sSaveOrder_BeforeInsertMain2"));


		$insertOrder = $this->sSYSTEM->sDB_CONNECTION->Execute($sql);

		$orderID = $this->sSYSTEM->sDB_CONNECTION->Insert_ID();

		if ($this->sSYSTEM->sDB_CONNECTION->ErrorMsg() || (!$orderID || !$insertOrder)){
			mail($this->sSYSTEM->sCONFIG['sMAIL'],"Shopware Order Fatal-Error {$_SERVER["HTTP_HOST"]}",$this->sSYSTEM->sDB_CONNECTION->ErrorMsg().$sql);
			die("Fatal order failure, please try again later, order was not processed");
		}

		$orderDay = date("d.m.Y");
		$orderTime = date("H:i");

		$position = 0;
		foreach ($this->sBasketData["content"] as $basketRow){
			$position++;
			eval($this->sSYSTEM->sCallHookPoint("sOrder.php_sSaveOrder_ContentLoop"));
			$amountRow = $this->sSYSTEM->sMODULES['sArticles']->sFormatPrice($basketRow["priceNumeric"] * $basketRow["quantity"]);

			if (!$basketRow["price"]) $basketRow["price"] = "0,00";
			if (!$amountRow) $amountRow = "0,00";

			$basketRow["articlename"] = str_replace("<br />","\n",$basketRow["articlename"]);
			$basketRow["articlename"] = html_entity_decode($basketRow["articlename"]);
			$basketRow["articlename"] = strip_tags($basketRow["articlename"]);

			if (!$basketRow["itemInfo"]){
				$priceRow = $basketRow["price"];
			}else {
				$priceRow = $basketRow["itemInfo"];
			}

			//Bundle-Article
			if($basketRow["modus"] == 10){
				$sqlBundleTax = "
					SELECT `taxID`
					FROM `s_articles_bundles`
					WHERE `ordernumber` = ?
				";
				$bundleTax = $this->sSYSTEM->sDB_CONNECTION->GetOne($sqlBundleTax, array($basketRow["ordernumber"]));
				if(!empty($bundleTax)) $basketRow["taxID"] = $bundleTax;
			}


			$basketRow["articlename"] = $this->sSYSTEM->sMODULES['sArticles']->sOptimizeText($basketRow["articlename"]);

			if (!$basketRow["esdarticle"]) $basketRow["esdarticle"] = "0";
			if (!$basketRow["modus"]) $basketRow["modus"] = "0";
			if (!$basketRow["taxID"]) $basketRow["taxID"] = "0";
			if ($this->sNet == true){
				$basketRow["taxID"] = "0";
			}

			$sql = "
			INSERT INTO s_order_details
				(orderID,
				ordernumber,
				articleID,
				articleordernumber,
				price,
				quantity,
				name,
				status,
				releasedate,
				modus,
				esdarticle,
				taxID,
				od_attr1,
				od_attr2,
				od_attr3,
				od_attr4,
				od_attr5,
				od_attr6
				)
				VALUES (
				$orderID,
				'$orderNumber',
				{$basketRow["articleID"]},
				'{$basketRow["ordernumber"]}',
				{$basketRow["priceNumeric"]},
				{$basketRow["quantity"]},
				'".addslashes($basketRow["articlename"])."',
				0,
				'0000-00-00',
				{$basketRow["modus"]},
				{$basketRow["esdarticle"]},
				{$basketRow["taxID"]},
				".$this->sSYSTEM->sDB_CONNECTION->qstr((string) $basketRow["ob_attr1"]).",
				".$this->sSYSTEM->sDB_CONNECTION->qstr((string) $basketRow["ob_attr2"]).",
				".$this->sSYSTEM->sDB_CONNECTION->qstr((string) $basketRow["ob_attr3"]).",
				".$this->sSYSTEM->sDB_CONNECTION->qstr((string) $basketRow["ob_attr4"]).",
				".$this->sSYSTEM->sDB_CONNECTION->qstr((string) $basketRow["ob_attr5"]).",
				".$this->sSYSTEM->sDB_CONNECTION->qstr((string) $basketRow["ob_attr6"])."
			)";
			$sql = Enlight()->Events()->filter('Shopware_Modules_Order_SaveOrder_FilterDetailsSQL', $sql, array('subject'=>$this,'row'=>$basketRow,'user'=>$this->sUserData,'order'=>array("id"=>$orderID,"number"=>$orderNumber)));

			eval($this->sSYSTEM->sCallHookPoint("sOrder.php_sSaveOrder_BeforeInsert"));
			// Check for individual voucher - code
			if ($basketRow["modus"]==2){
				// $basketRow["articleID"] => s_emarketing_voucher_codes.id
				// $basketRow["ordernumber"] => Check mode
				$getVoucher = $this->sSYSTEM->sDB_CONNECTION->GetRow("
				SELECT modus,id FROM s_emarketing_vouchers
				WHERE ordercode=?
				",array($basketRow["ordernumber"]));

				if ($getVoucher["modus"]==1){
					// Update Voucher - Code
					$updateVoucher = $this->sSYSTEM->sDB_CONNECTION->Execute("
					UPDATE s_emarketing_voucher_codes
					SET cashed = 1, userID= ?
					WHERE id = ?
					",array($this->sUserData["additional"]["user"]["id"],$basketRow["articleID"]));

				}
			}

			if ($basketRow["esdarticle"]) $esdOrder = true;

			$this->sSYSTEM->sDB_CONNECTION->Execute($sql);
			$orderdetailsID = $this->sSYSTEM->sDB_CONNECTION->Insert_ID();
			if ($this->sSYSTEM->sDB_CONNECTION->ErrorMsg() || !$orderdetailsID){
				mail($this->sSYSTEM->sCONFIG['sMAIL'],"Shopware Order Fatal-Error {$_SERVER["HTTP_HOST"]}",$this->sSYSTEM->sDB_CONNECTION->ErrorMsg().$sql);
				die("Fatal order failure, please try again later, order was not processed");
			}

			// Update sales and stock
			if ($basketRow["priceNumeric"] >= 0 && !$basketRow["esdarticle"]){
				$this->sSYSTEM->sDB_CONNECTION->Execute("
				UPDATE s_articles_details SET sales=sales+{$basketRow["quantity"]},instock=instock-{$basketRow["quantity"]}  WHERE ordernumber='{$basketRow["ordernumber"]}'
				");

				// Check if position is configurator combination
				$checkConfigurator = $this->sSYSTEM->sDB_CONNECTION->GetRow("SELECT valueID FROM s_articles_groups_value
				WHERE ordernumber = '{$basketRow["ordernumber"]}'
				");

				if ($checkConfigurator["valueID"]){
					// Update instock
					$this->sSYSTEM->sDB_CONNECTION->Execute("
					UPDATE s_articles_groups_value SET instock=instock-{$basketRow["quantity"]}  WHERE ordernumber='{$basketRow["ordernumber"]}'
					");
				}

				eval($this->sSYSTEM->sCallHookPoint("sOrder.php_sSaveOrder_ModifyInstock"));
			}

			if (!empty($basketRow["laststock"])&&!empty($this->sSYSTEM->sCONFIG['sDEACTIVATENOINSTOCK']) && !empty($basketRow['articleID']))
			{
				$sql = 'SELECT MAX(instock) as max_instock FROM s_articles_groups_value WHERE articleID=?';
				$max_instock = $this->sSYSTEM->sDB_CONNECTION->GetOne($sql,array($basketRow['articleID']));
				if($max_instock===null)
				{
					$sql = 'SELECT MAX(instock) as max_instock FROM s_articles_details WHERE articleID=?';
					$max_instock = $this->sSYSTEM->sDB_CONNECTION->GetOne($sql,array($basketRow['articleID']));
				}
				$max_instock = (int) $max_instock;
				if($max_instock<=0)
				{
					$sql = 'UPDATE s_articles SET active=0 WHERE id=?';
					$this->sSYSTEM->sDB_CONNECTION->Execute($sql,array($basketRow['articleID']));
					// Ticket #5517
					$this->sSYSTEM->sDB_CONNECTION->Execute("
					UPDATE s_articles_details SET active = 0 WHERE ordernumber = ?
					",array($basketRow['ordernumber']));
				}
			}

			// For esd-articles, assign serialnumber if needed
			// Check if this article is esd-only (check in variants, too -> later)
			if ($basketRow["esdarticle"]){
				$this->sManageEsdOrder($basketRow,$orderID,$orderdetailsID);
			} // If article is marked as esd-article

		} // For every artice in basket
		Enlight()->Events()->notify('Shopware_Modules_Order_SaveOrder_ProcessDetails', array('subject'=>$this,'details'=>$this->sBasketData["content"]));

		// Assign variables
		foreach ($this->sUserData["billingaddress"] as $key => $value){
			$this->sUserData["billingaddress"][$key] = html_entity_decode($value);
		}
		foreach ($this->sUserData["shippingaddress"] as $key => $value){
			$this->sUserData["shippingaddress"][$key] = html_entity_decode($value);
		}
		foreach ($this->sUserData["additional"]["country"] as $key => $value){
			$this->sUserData["additional"]["country"][$key] = html_entity_decode($value);
		}

		$this->sUserData["additional"]["payment"]["description"] = html_entity_decode($this->sUserData["additional"]["payment"]["description"]);
		//$this->sUserData["additional"]["payment"]["description"] = str_replace("&euro;","€",$this->sUserData["additional"]["payment"]["description"] );
		eval($this->sSYSTEM->sCallHookPoint("sOrder.php_sSaveOrder_ModifyUserData"));

		if ($this->sUserData["additional"]["payment"]["table"]){

			$paymentTable = $this->sSYSTEM->sDB_CONNECTION->GetRow("
			SELECT * FROM {$this->sUserData["additional"]["payment"]["table"]}
			WHERE userID=?",array($this->sUserData["additional"]["user"]["id"]));
			$this->sSYSTEM->sSMARTY->assign("sPaymentTable",$paymentTable);
		}else {
			$this->sSYSTEM->sSMARTY->assign("sPaymentTable","");
		}

		$sOrderDetails = array();
		foreach ($this->sBasketData["content"] as $key=>$content)
		{
			eval($this->sSYSTEM->sCallHookPoint("sOrder.php_sSaveOrder_ModifyContent"));
			$content["articlename"] = trim(html_entity_decode($content["articlename"]));
			$content["articlename"] = str_replace(array("<br />","<br>"),"\n",$content["articlename"]);
			$content["articlename"] = str_replace("&euro;","€",$content["articlename"]);
			$content["articlename"] = trim($content["articlename"]);
			while(strpos($content["articlename"],"\n\n")!==false)
			$content["articlename"] = str_replace("\n\n","\n",$content["articlename"]);
			$content["ordernumber"] = trim(html_entity_decode($content["ordernumber"]));
			$sOrderDetails[] = $content;
		}
		eval($this->sSYSTEM->sCallHookPoint("sOrder.php_sSaveOrder_VariablesAssign"));

		$variables = array(
			"sOrderDetails"=>$sOrderDetails,
			"billingaddress"=>$this->sUserData["billingaddress"],
			"shippingaddress"=>$this->sUserData["shippingaddress"],
			"additional"=>$this->sUserData["additional"],
			"sShippingCosts"=>$this->sSYSTEM->sMODULES['sArticles']->sFormatPrice($this->sShippingcosts)." ".$this->sSYSTEM->sCurrency["currency"],
			"sAmount"=>$this->sAmountWithTax ? $this->sSYSTEM->sMODULES['sArticles']->sFormatPrice($this->sAmountWithTax)." ".$this->sSYSTEM->sCurrency["currency"] : $this->sSYSTEM->sMODULES['sArticles']->sFormatPrice($this->sAmount)." ".$this->sSYSTEM->sCurrency["currency"],
			"sAmountNet"=>$this->sSYSTEM->sMODULES['sArticles']->sFormatPrice($this->sBasketData["AmountNetNumeric"])." ".$this->sSYSTEM->sCurrency["currency"],
			"ordernumber"=>$orderNumber,
			"sOrderDay"=>$orderDay,
			"sOrderTime"=>$orderTime,
			"sComment"=>$this->sComment,
			"sEsd"=>$esdOrder
		);

		if ($dispatchId){
			$variables["sDispatch"] = $this->sSYSTEM->sMODULES['sAdmin']->sGetDispatch($dispatchId);
		}
		if ($this->bookingId){
			$variables['sBookingID'] = $this->bookingId;
		}
		eval($this->sSYSTEM->sCallHookPoint("sOrder.php_sSaveOrder_BeforeSend"));

		$this->sendMail($variables);

		// Check if voucher is affected
		$this->sTellFriend();

		// Save Billing and Shipping-Address to retrace in future
		$this->sSaveBillingAddress($this->sUserData["billingaddress"],$orderID);
		$this->sSaveShippingAddress($this->sUserData["shippingaddress"],$orderID);

		eval($this->sSYSTEM->sCallHookPoint("sOrder.php_sSaveOrder_BeforeDelete"));
		// Completed - Garbage basket / temporary - order
		$this->sDeleteTemporaryOrder();

		// Bundlebestellung abgeschlossen
		$this->sSaveOrderBundle($orderNumber);

		// Live-Shopping-Bestellung abgeschlossen
		$this->sSaveOrderLiveShopping();

		$deleteSession =$this->sSYSTEM->sDB_CONNECTION->Execute("
		DELETE FROM s_order_basket WHERE sessionID=?
		",array($this->sSYSTEM->sSESSION_ID));		

		eval($this->sSYSTEM->sCallHookPoint("sOrder.php_sSaveOrder_BeforeEnd"));


		if (isset(Shopware()->Session()->sOrderVariables))
		{
			$variables = Shopware()->Session()->sOrderVariables;
			$variables['ordernumber'] = $orderNumber;
			Shopware()->Session()->sOrderVariables = $variables;
		}

		return $orderNumber;

	} // End public function Order

	/**
	 * Refresh bundle sales
	 * @access public
	 */
	public function sSaveOrderBundle(){
		return $this->sSYSTEM->sMODULES["sBundle"]->sSaveOrderBundle();
	}

	/**
	 * Refresh liveshopping sales
	 * @access public
	 */
	public function sSaveOrderLiveShopping($orderNumber=""){
		return $this->sSYSTEM->sMODULES["sLiveshopping"]->sSaveOrderLiveShopping($orderNumber);
	}

	/**
	 * send order confirmation mail
	 * @access public
	 */
	public function sendMail($variables){
		eval($this->sSYSTEM->sCallHookPoint("sOrder.php_sendMail_Start"));

		$variables = Enlight()->Events()->filter('Shopware_Modules_Order_SendMail_FilterVariables', $variables, array('subject'=>$this));


		$this->sSYSTEM->sSMARTY->register_modifier("fill",array(&$this,"smarty_modifier_fill"));
		$this->sSYSTEM->sSMARTY->register_modifier("padding",array(&$this,"smarty_modifier_padding"));
		$this->sSYSTEM->sSMARTY->assign("sOrderDetails",$variables["sOrderDetails"]);


		$this->sSYSTEM->sSMARTY->assign("sTable",$variables["sTable"]);
		$this->sSYSTEM->sSMARTY->assign('billingaddress',$variables["billingaddress"]);
		$this->sSYSTEM->sSMARTY->assign('shippingaddress',$variables["shippingaddress"]);
		$this->sSYSTEM->sSMARTY->assign('additional',$variables["additional"]);

		$this->sSYSTEM->sSMARTY->assign('sShippingCosts',$variables["sShippingCosts"]);
		$this->sSYSTEM->sSMARTY->assign('sAmount',$variables["sAmount"]);
		$this->sSYSTEM->sSMARTY->assign('sAmountNet',$variables["sAmountNet"]);

		$this->sSYSTEM->sSMARTY->assign('sOrderNumber',$variables["ordernumber"]);
		$this->sSYSTEM->sSMARTY->assign('sOrderDay',$variables["sOrderDay"]);
		$this->sSYSTEM->sSMARTY->assign('sOrderTime',$variables["sOrderTime"]);
		$this->sSYSTEM->sSMARTY->assign('sComment',$variables["sComment"]);
		$this->sSYSTEM->sSMARTY->assign('sCurrency',$this->sSYSTEM->sCurrency["currency"]);
		$this->sSYSTEM->sSMARTY->assign('sLanguage',$this->sSYSTEM->sLanguageData[$language]["isocode"]);
		$this->sSYSTEM->sSMARTY->assign('sSubShop',$this->sSYSTEM->sSubShop["id"]);

		$this->sSYSTEM->sSMARTY->assign('sEsd',$variables["sEsd"]);
		$this->sSYSTEM->sSMARTY->assign('sNet',$this->sNet);
		$this->sSYSTEM->sSMARTY->assign('sConfig',$this->sSYSTEM->sCONFIG);

		if ($variables["sDispatch"]){
			$this->sSYSTEM->sSMARTY->assign('sDispatch',$variables["sDispatch"]);
		}
		if ($variables['sBookingID']){
			$this->sSYSTEM->sSMARTY->assign('sBookingID',$variables['sBookingID']);
		}

		$mail           = $this->sSYSTEM->sMailer;
		$mail->CharSet  =  "iso-8859-1";
		$template = $this->sSYSTEM->sCONFIG['sTemplates']["sORDER"];

		if (!empty($template["ishtml"])){
			$mail->IsHTML(1);
			$mail->Body     = $this->sSYSTEM->sSMARTY->fetch("string:".$template["contentHTML"]);
			$mail->AltBody = $this->sSYSTEM->sSMARTY->fetch("string:".$template["content"]);
		}else {
			$mail->IsHTML(0);
			$mail->Body     = $this->sSYSTEM->sSMARTY->fetch("string:".$template["content"]);
		}

		eval($this->sSYSTEM->sCallHookPoint("sOrder.php_sendMail_BeforeSend"));
		$mail->From     = $this->sSYSTEM->sSMARTY->fetch("string:".$template["frommail"]);
		$mail->FromName = $this->sSYSTEM->sSMARTY->fetch("string:".$template["fromname"]);
		$mail->Subject  = $this->sSYSTEM->sSMARTY->fetch("string:".$template["subject"]);

		// Add attachment to eMail
		$attachment = $this->sSYSTEM->sSMARTY->fetch("string:".$template["attachment"]);
		if (!empty($attachment)){
			$attachments = explode("/",$attachment);
			if (empty($attachments[0])){
				$attachments[0] = $attachment;
			}
			foreach ($attachments as $attachment){
				$file = explode(";",$attachment);
				$path = $this->sSYSTEM->sCONFIG["sBASEPATH"];
				$path = str_replace($this->sSYSTEM->sCONFIG["sHOST"],"",$path);
				$path = $_SERVER['DOCUMENT_ROOT'].$path."/uploads/".$file[0];
				if (is_file($path)){
					$mail->addAttachment($path,$file[1]);
				}
			}
		}

		$mail->ClearAddresses();
		eval($this->sSYSTEM->sCallHookPoint("sOrder.php_sendMail_BeforeSend2"));
		$mail->AddAddress($this->sUserData["additional"]["user"]["email"], "");
		if (!$this->sSYSTEM->sCONFIG["sNO_ORDER_MAIL"]){
			$mail->AddBCC($this->sSYSTEM->sCONFIG['sMAIL'], "");

		}
		eval($this->sSYSTEM->sCallHookPoint("sOrder.php_sendMail_BeforeSend3"));
		Enlight()->Events()->notify('Shopware_Modules_Order_SendMail_BeforeSend', array('subject'=>$this, 'mail'=>$mail));
		if (!$mail->Send()){
			$this->sSYSTEM->E_CORE_WARNING("##sOrder-sSaveOrder-#03","Could not send confirmation mail");
		}
		eval($this->sSYSTEM->sCallHookPoint("sOrder.php_sendMail_AfterSend"));
	}

	/**
	 * Save order billing address
	 * @access public
	 */
	public function sSaveBillingAddress($address,$id){
		$sql = "
		INSERT INTO s_order_billingaddress
		(
			userID, 
			orderID, 
			company, 
			department, 
			salutation, 
			firstname, 
			lastname, 
			street, 
			streetnumber, 
			zipcode, 
			city, 
			phone, 
			fax, 
			countryID, 
			ustid, 
			text1,
			text2,
			text3,
			text4,
			text5,
			text6
		)
		VALUES (
			?,
			?,
			?,
			?,
			?,
			?,
			?,
			?,
			?,
			?,
			?,
			?,
			?,
			?,
			?,
			?,
			?,
			?,
			?,
			?,
			?
			)
		";
		$sql = Enlight()->Events()->filter('Shopware_Modules_Order_SaveBilling_FilterSQL', $sql, array('subject'=>$this,'address'=>$address,'id'=>$id));
		$array = array(
			$address["userID"],
			$id,
			$address["company"],
			$address["department"],
			$address["salutation"],
			$address["firstname"],
			$address["lastname"],
			$address["street"],
			$address["streetnumber"],
			$address["zipcode"],
			$address["city"],
			$address["phone"],
			$address["fax"],
			$address["countryID"],
			$address["ustid"],
			$address["text1"],
			$address["text2"],
			$address["text3"],
			$address["text4"],
			$address["text5"],
			$address["text6"]
		);
		$array = Enlight()->Events()->filter('Shopware_Modules_Order_SaveBilling_FilterArray', $array, array('subject'=>$this,'address'=>$address,'id'=>$id));

		return $this->sSYSTEM->sDB_CONNECTION->Execute($sql,$array);
	}

	/**
	 * save order shipping address
	 * @access public
	 */
	public function sSaveShippingAddress($address,$id){
		$sql = "
		INSERT INTO s_order_shippingaddress
		(
			userID, 
			orderID, 
			company, 
			department, 
			salutation, 
			firstname, 
			lastname, 
			street, 
			streetnumber, 
			zipcode, 
			city, 
			countryID, 
			text1,
			text2,
			text3,
			text4,
			text5,
			text6
		)
		VALUES (
			?,
			?,
			?,
			?,
			?,
			?,
			?,
			?,
			?,
			?,
			?,
			?,
			?,
			?,
			?,
			?,
			?,
			?
			)
		";
		$sql = Enlight()->Events()->filter('Shopware_Modules_Order_SaveShipping_FilterSQL', $sql, array('subject'=>$this,'address'=>$address,'id'=>$id));
		$array = array(
			$address["userID"],
			$id,
			$address["company"],
			$address["department"],
			$address["salutation"],
			$address["firstname"],
			$address["lastname"],
			$address["street"],
			$address["streetnumber"],
			$address["zipcode"],
			$address["city"],
			$address["countryID"],
			$address["text1"],
			$address["text2"],
			$address["text3"],
			$address["text4"],
			$address["text5"],
			$address["text6"]
		);
		$array = Enlight()->Events()->filter('Shopware_Modules_Order_SaveShipping_FilterArray', $array, array('subject'=>$this,'address'=>$address,'id'=>$id));

		return $this->sSYSTEM->sDB_CONNECTION->Execute($sql,$array);
	}

	/**
	 * smarty modifier fill
	 */
	public function smarty_modifier_fill ($str, $width=10, $break="...", $fill=" ")
	{
		if(!is_scalar($break))
		$break = "...";
		if(empty($fill)||!is_scalar($fill))
		$fill = " ";
		if(empty($width)||!is_numeric($width))
		$width = 10;
		else
		$width = (int)$width;
		if(!is_scalar($str))
		return str_repeat($fill,$width);
		if(strlen($str)>$width)
		$str = substr($str,0,$width-strlen($break)).$break;
		if($width>strlen($str))
		return $str.str_repeat($fill,$width-strlen($str));
		else
		return $str;
	}

	/**
	 * smarty modifier padding
	 */
	public function smarty_modifier_padding ($str, $width=10, $break="...", $fill=" ")
	{
		if(!is_scalar($break))
		$break = "...";
		if(empty($fill)||!is_scalar($fill))
		$fill = " ";
		if(empty($width)||!is_numeric($width))
		$width = 10;
		else
		$width = (int)$width;
		if(!is_scalar($str))
		return str_repeat($fill,$width);
		if(strlen($str)>$width)
		$str = substr($str,0,$width-strlen($break)).$break;
		if($width>strlen($str))
		return str_repeat($fill,$width-strlen($str)).$str;
		else
		return $str;
	}

	/**
	 * Check if this order could be refered to a previous recommendation
	 * @access public
	 */
	public function sTellFriend(){
		$checkMail = $this->sUserData["additional"]["user"]["email"];

		$tmpSQL = "
		SELECT * FROM s_emarketing_tellafriend WHERE confirmed=0 AND recipient=?
		";
		$checkIfUserFound = $this->sSYSTEM->sDB_CONNECTION->GetRow($tmpSQL,array($checkMail));
		if (count($checkIfUserFound)){
			// User-Datensatz aktualisieren
			$updateUserFound = $this->sSYSTEM->sDB_CONNECTION->Execute("
			UPDATE s_emarketing_tellafriend SET confirmed=1 WHERE recipient=?
			",array($checkMail));
			// --
			// Daten über Werber fetchen
			$getWerberInfo = $this->sSYSTEM->sDB_CONNECTION->GetRow("
			SELECT email, firstname, lastname FROM s_user, s_user_billingaddress
			WHERE s_user_billingaddress.userID = s_user.id AND s_user.id=?
			",array($checkIfUserFound["sender"]));

			if (empty($getWerberInfo)){
				// Benutzer nicht mehr vorhanden
				return;
			}

			$template = $this->sSYSTEM->sCONFIG['sTemplates']['sVOUCHER'];
			$template['content'] = str_replace("{customer}",$getWerberInfo["firstname"]." ".$getWerberInfo["lastname"],$template['content']);
			$template['content'] = str_replace("{user}",$this->sUserData["billingaddress"]["firstname"]." ".$this->sUserData["billingaddress"]["lastname"],$template['content']);
			$template['content'] = str_replace("{voucherValue}",$this->sSYSTEM->sCONFIG['sVOUCHERTELLFRIENDVALUE'],$template['content']);
			$template['content'] = str_replace("{voucherCode}",$this->sSYSTEM->sCONFIG['sVOUCHERTELLFRIENDCODE'],$template['content']);

			$template['contentHTML'] = str_replace("{customer}",$getWerberInfo["firstname"]." ".$getWerberInfo["lastname"],$template['contentHTML']);
			$template['contentHTML'] = str_replace("{user}",$this->sUserData["billingaddress"]["firstname"]." ".$this->sUserData["billingaddress"]["lastname"],$template['contentHTML']);
			$template['contentHTML'] = str_replace("{voucherValue}",$this->sSYSTEM->sCONFIG['sVOUCHERTELLFRIENDVALUE'],$template['contentHTML']);
			$template['contentHTML'] = str_replace("{voucherCode}",$this->sSYSTEM->sCONFIG['sVOUCHERTELLFRIENDCODE'],$template['contentHTML']);

			//To get the new object and not the pointer
			$this->sSYSTEM->sInitMailer();

			$mail           = $this->sSYSTEM->sMailer;
			if ($template['ishtml']){
				$mail->IsHTML(1);
				$mail->Body     = $template['contentHTML'];
				$mail->AltBody     = $template['content'];
			}else {
				$mail->IsHTML(0);
				$mail->Body     = $template['content'];
			}
			$mail->From     = $template['frommail'];
			$mail->FromName = $template['fromname'];
			$mail->Subject  = $template['subject'];
			$mail->ClearAddresses();
			$mail->AddAddress($getWerberInfo["email"], "");
			if (!$mail->Send()){
				$this->sSYSTEM->E_CORE_WARNING("##sOrder-sSaveOrder-#04","Could not send voucher mail");
			}
			// --
		} // - if user found
	} // Tell-a-friend
	
	/**
	 * Send status mail
	 *
	 * @param Enlight_Components_Mail $mail
	 * @return Enlight_Components_Mail
	 */
	public function sendStatusMail(Enlight_Components_Mail $mail)
	{
		Enlight()->Events()->notify('Shopware_Controllers_Backend_OrderState_Send_BeforeSend', array(
			'subject'=>$this, 'mail'=>$mail
		));
		
		if(!empty(Shopware()->Config()->OrderStateMailAck)){
			$mail->addBcc(Shopware()->Config()->OrderStateMailAck);	
		}

		return $mail->send();
	}
	
	/**
	 * Create status mail
	 *
	 * @param int $orderId
	 * @param int $statusId
	 * @param string $template
	 * @return Enlight_Components_Mail
	 */
	public function createStatusMail($orderId, $statusId, $template=null)
	{
		$statusId = (int) $statusId;
		$orderId = (int) $orderId;

		if (empty($template)){
			$template = 'sORDERSTATEMAIL' . $statusId;
		}
		
		if(empty($orderId) || empty($statusId)) {
			return;
		}
		
		$order = Shopware()->Api()->Export()->sGetOrders(array('orderID' => $orderId));
		$order = current($order);
		
		if (!empty($order['dispatchID'])){
			$dispatch = Shopware()->Db()->fetchRow('
				SELECT name, description FROM s_shippingcosts_dispatch
				WHERE id=?
			', array($order['dispatchID']));
		}
		
		$orderDetails = Shopware()->Api()->Export()->sOrderDetails(array('orderID' => $orderId));
		$orderDetails = array_values($orderDetails);
		
		$user = Shopware()->Api()->Export()->sOrderCustomers(array('orderID' => $orderId));
		$user = current($user);
		
		if(empty($order) || empty($orderDetails) || empty($user)) {
			return;
		}
		
		$shop = new Shopware_Models_Shop($order['subshopID']);
		$shop->setCache();
		$shop->registerResources(Shopware()->Bootstrap());
		
		if(empty($shop->Config()->Templates[$template]['content'])) {
			return;
		} else {
			$template = $shop->Config()->Templates[$template];
		}
		
		$templateEngine = Shopware()->Template();
		$templateData = $templateEngine->createData();
		
		$templateData->assign('sConfig', $shop->Config());
		$templateData->assign('sOrder', $order);
		$templateData->assign('sOrderDetails', $orderDetails);
		$templateData->assign('sUser', $user);
		if (!empty($dispatch)) {
			$templateData->assign('sDispatch', $dispatch);
		}
		
		$result = Enlight()->Events()->notify('Shopware_Controllers_Backend_OrderState_Notify', array(
			'subject' => $this,
			'id' => $orderId, 'status' => $statusId,
			'mailname'=>$template->name, 'template'=>$template
		));
		if (!empty($result)){
			$templateData->assign('EventResult', $result->getValues());
		}
		
		$return = array(
			'content' => $templateEngine->fetch('string:'.$template->content, $templateData), 
			'subject' => trim($templateEngine->fetch('string:'.$template->subject , $templateData)),
			'email' => trim($user['email']),
			'frommail' => trim($templateEngine->fetch('string:'.$template->frommail, $templateData)),
			'fromname' => trim($templateEngine->fetch('string:'.$template->fromname, $templateData))
		);
		
		
		
		$mail = clone Shopware()->Mail();
		
		$mail->clearRecipients();
		
		$mail->setSubject($return['subject']);
		$mail->setBodyText($return['content']);
		$mail->setFrom($return['frommail'], $return['fromname']);
		$mail->addTo($return['email']);
		
		return $mail;
	}
	
	/**
	 * Set payment status by order id
	 *
	 * @param int $orderId
	 * @param int $paymentStatusId
	 * @param bool $sendStatusMail
	 */
	public function setPaymentStatus($orderId, $paymentStatusId, $sendStatusMail=false)
	{
		$sql = 'UPDATE `s_order` SET `cleared`=? WHERE `id`=?;';
		$result = Shopware::Instance()->Db()->query($sql, array($paymentStatusId, $orderId));
		
		if(!empty($sendStatusMail) && $result->rowCount()) {
			$mail = $this->createStatusMail($orderId, $paymentStatusId);
			if($mail) {
				$this->sendStatusMail($mail);
			}
		}
	}
}