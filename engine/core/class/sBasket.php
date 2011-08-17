<?php
/**
 * Shoppingcart features
 * @link http://www.shopware.de
 * @package core
 * @subpackage class
 * @copyright (C) Shopware AG 2002-2010
 * @version Shopware 3.5.0
 */
class sBasket
{
	var $sSYSTEM;
	var $sBASKET;

	/**
	 * Gesamtsumme des aktuellen Warenkorbs auslesen
	 * @access public
	 * @return array
	 */
	public function sGetAmount(){
		return  $this->sSYSTEM->sDB_CONNECTION->GetRow("SELECT SUM(quantity*(floor(price * 100 + .55)/100))
		AS totalAmount FROM s_order_basket WHERE sessionID=? GROUP BY sessionID",array($this->sSYSTEM->sSESSION_ID));
	}

	/**
	 * Gesamtsumme der Artikel des aktuellen Warenkorbs auslesen
	 * @access public
	 * @return array
	 */
	public function sGetAmountArticles(){
		return  $this->sSYSTEM->sDB_CONNECTION->GetRow("SELECT SUM(quantity*(floor(price * 100 + .55)/100))
		AS totalAmount FROM s_order_basket WHERE sessionID=? AND modus=0 GROUP BY sessionID",array($this->sSYSTEM->sSESSION_ID));
	}

	/**
	 * Verfügbarkeit der Positionen des Warenkorbs überprüfen
	 * @access public
	 * @return array
	 */
	public function sCheckBasketQuantities(){

		$sql = "SELECT
			SUM(s_articles_details.instock - s_order_basket.quantity) AS diffStock, s_order_basket.ordernumber
			FROM s_articles,s_articles_details,s_order_basket
			WHERE 
			s_articles.mode = 0 AND s_articles.active = 1 AND s_articles.laststock = 1 AND s_articles.id = s_articles_details.articleID
			AND s_articles_details.ordernumber = s_order_basket.ordernumber AND s_order_basket.modus = 0 AND
			s_order_basket.sessionID = ?
			GROUP BY  s_order_basket.ordernumber
			";
		$sql2 = "SELECT
			SUM(s_articles_groups_value.instock - s_order_basket.quantity) AS diffStock, s_order_basket.ordernumber
			FROM s_articles,s_articles_groups_value,s_order_basket
			WHERE 
			s_articles.mode = 0 AND s_articles.active = 1 AND s_articles.laststock = 1 AND s_articles.id = s_articles_groups_value.articleID
			AND s_articles_groups_value.ordernumber = s_order_basket.ordernumber AND s_order_basket.modus = 0 AND
			s_order_basket.sessionID = ?
			GROUP BY  s_order_basket.ordernumber
			";
		$result = $this->sSYSTEM->sDB_CONNECTION->GetAll($sql,array($this->sSYSTEM->sSESSION_ID));
		$result2 = $this->sSYSTEM->sDB_CONNECTION->GetAll($sql2,array($this->sSYSTEM->sSESSION_ID));
		$result = array_merge($result,$result2);
		$hideBasket = false;
		foreach ($result as $article){
			if ($article["diffStock"]<0){
				$hideBasket = true;
				$articles[$article["ordernumber"]]["OutOfStock"] = true;
			}else {
				$articles[$article["ordernumber"]]["OutOfStock"] = false;
			}

		}
		return array("hideBasket"=>$hideBasket,"articles"=>$articles);
	}

	/**
	 * Warenwert für bestimmte Artikel / Hersteller auslesen
	 * @access public
	 * @return array
	 */
	public function sGetAmountRestrictedArticles($articles,$supplier){
		if (!is_array($articles) && empty($supplier)) return $this->sGetAmountArticles();
		if (is_array($articles)){
			foreach ($articles as $article){
				$article = $article;
				$newArticles[] = $article;
			}
			$in = implode(",",$newArticles);
			$articleSQL = "ordernumber IN ($in) ";
		}
		if (!empty($supplier)){
			if (empty($articleSQL)){
				$articleSQL = "1 != 1 ";
			}
			$supplierSQL = "OR s_articles.supplierID = $supplier ";
		}
		return  $this->sSYSTEM->sDB_CONNECTION->GetRow("SELECT SUM(quantity*(floor(price * 100 + .55)/100))
		AS totalAmount FROM s_order_basket, s_articles WHERE sessionID=? AND modus=0 AND s_order_basket.articleID=s_articles.id 
		AND
		(
		$articleSQL
		$supplierSQL
		)
		GROUP BY sessionID",array($this->sSYSTEM->sSESSION_ID));
	}

	/**
	 * Gutschein aktualisieren
	 * @access public
	 * @return array
	 */
	public function sUpdateVoucher()
	{
		$sql = 'SELECT id basketID, ordernumber, articleID as voucherID FROM s_order_basket WHERE modus=2 AND sessionID=?';
		$voucher = $this->sSYSTEM->sDB_CONNECTION->GetRow($sql, array($this->sSYSTEM->sSESSION_ID));
		if(!empty($voucher))
		{
			$sql = 'SELECT vouchercode FROM s_emarketing_vouchers WHERE ordercode=?';
			$voucher['code'] = $this->sSYSTEM->sDB_CONNECTION->GetOne($sql, array($voucher['ordernumber']));
			if (empty($voucher['code']))
			{
				$sql = 'SELECT code FROM s_emarketing_voucher_codes WHERE id=?';
				$voucher['code'] = $this->sSYSTEM->sDB_CONNECTION->GetOne($sql, array($voucher['voucherID']));
			}
			$this->sDeleteArticle($voucher['basketID']);
			$this->sAddVoucher($voucher['code']);
		}
	}
	/**
	 * Insert basket discount
	 * 
	 * @access public
	 * @return void
	 */
	public function sInsertDiscount () {
		
		// Get possible discounts
		$getDiscounts = $this->sSYSTEM->sDB_CONNECTION->GetAll("
		SELECT basketdiscount, basketdiscountstart FROM s_core_customergroups_discounts
		WHERE groupID=?
		ORDER BY basketdiscountstart ASC
		",array($this->sSYSTEM->sUSERGROUPDATA["id"]));


		$rs = $this->sSYSTEM->sDB_CONNECTION->Execute("
		DELETE FROM s_order_basket WHERE sessionID=? AND modus=3
		",array($this->sSYSTEM->sSESSION_ID));

		// No discounts
		if (!count($getDiscounts)){
			return;
		}

		$basketAmount = $this->sSYSTEM->sDB_CONNECTION->GetOne("SELECT SUM(quantity*(floor(price * 100 + .55)/100))
		AS totalAmount FROM s_order_basket WHERE sessionID=? AND modus!=4 GROUP BY sessionID",array($this->sSYSTEM->sSESSION_ID));

		if (!$basketAmount) return;	// No articles in basket, return

		if ($this->sSYSTEM->sCurrency["factor"]){
		}else {
			$factor = 1;
		}


		// Iterate through discounts and find nearly one
		foreach ($getDiscounts as $discountRow){
			if ($basketAmount<$discountRow["basketdiscountstart"]){
				break;
			}else {
				$basketDiscount = $discountRow["basketdiscount"];
			}
		}

		if (!$basketDiscount) return;

		$insertTime = date("Y-m-d H:i:s");
		$discount = $basketAmount / 100 * $basketDiscount;
		$discount = $discount * -1;
		$discount = round($discount,2);
		
		if (!empty($this->sSYSTEM->sCONFIG["sTAXAUTOMODE"])){
			$tax = $this->sSYSTEM->sMODULES['sBASKET']->getMaxTax();
		}else {
			$tax = $this->sSYSTEM->sCONFIG['sDISCOUNTTAX'];
		}

		if (!empty($this->sSYSTEM->sCONFIG["sTAXAUTOMODE"])){
			$tax = $this->sSYSTEM->sMODULES['sBASKET']->getMaxTax();
		}
		if (!$tax) $tax = 119;

		if (!$this->sSYSTEM->sUSERGROUPDATA["tax"] && $this->sSYSTEM->sUSERGROUPDATA["id"]){
			$discountNet = $discount;
		}else {
			$discountNet = round($discount / (100+$tax) * 100,3);
		}

		// Add discount - info to user-account
		$this->sSYSTEM->sUSERGROUPDATA["basketdiscount"] = $basketDiscount;
		// --
		$name = isset($this->sSYSTEM->sCONFIG['sDISCOUNTNUMBER']) ? $this->sSYSTEM->sCONFIG['sDISCOUNTNUMBER']: "DISCOUNT";

		$sql = "
			INSERT INTO s_order_basket (sessionID, articlename, articleID,
			ordernumber, quantity, price, netprice, datum,
			modus, currencyFactor)
			VALUES ('".$this->sSYSTEM->sSESSION_ID."','- $basketDiscount % {$this->sSYSTEM->sCONFIG["sDISCOUNTNAME"]}',0,'$name',
			1, $discount, $discountNet, '$insertTime',3,".$this->sSYSTEM->sCurrency["factor"].")
			";

		$insertDiscount = $this->sSYSTEM->sDB_CONNECTION->Execute($sql);

	}

	/**
	 * Überprüfen ob Rabatt im Warenkorb liegt
	 * @access public
	 * @return void
	 */
	public function sCheckForDiscount(){
		$rs = $this->sSYSTEM->sDB_CONNECTION->GetRow("
		SELECT id FROM s_order_basket WHERE sessionID=? AND modus=3
		",array($this->sSYSTEM->sSESSION_ID));

		if ($rs["id"]){
			return true;
		}else {
			return false;
		}
	}

	/**
	 * Prämienartikel in Warenkorb einfügen
	 * @access public
	 * @return void
	 */
	public function sInsertPremium()
	{
		static $last_premium;

		$sBasketAmount = $this->sGetAmount();
		if(empty($sBasketAmount["totalAmount"]))
		$sBasketAmount = 0;
		else
		$sBasketAmount = $sBasketAmount["totalAmount"];



		if(empty($this->sSYSTEM->_GET["sAddPremium"]))
		{
			$sql = "
				SELECT b.id
				FROM s_order_basket b
				LEFT JOIN s_addon_premiums p
				ON p.startprice<=$sBasketAmount
				AND p.ordernumber=b.ordernumber
				WHERE b.modus=1
				AND p.id IS NULL
				AND b.sessionID='{$this->sSYSTEM->sSESSION_ID}'
			";
			$deletePremium = $this->sSYSTEM->sDB_CONNECTION->GetOne($sql);
			if(empty($deletePremium))
			return true;
			$this->sSYSTEM->sDB_CONNECTION->Execute("
				DELETE FROM s_order_basket WHERE id=$deletePremium
			");
			return true;
		}

		if(empty($this->sSYSTEM->_GET["sAddPremium"]))
		return false;

		if(isset($last_premium)&&$last_premium==$this->sSYSTEM->_GET["sAddPremium"])
		return false;

		$last_premium = $this->sSYSTEM->_GET["sAddPremium"];

		$this->sSYSTEM->sDB_CONNECTION->Execute("
			DELETE FROM s_order_basket WHERE sessionID='".$this->sSYSTEM->sSESSION_ID."' AND modus=1
		");

		$ordernumber = $this->sSYSTEM->sDB_CONNECTION->qstr($this->sSYSTEM->_GET["sAddPremium"]);

		$sql = "
			SELECT p.ordernumber, a.id as articleID, a.name, d.additionaltext
			FROM 
				s_addon_premiums p,
				s_articles_details d,
				s_articles a,
				s_articles_details d2
			WHERE d.ordernumber=$ordernumber
			AND p.startprice<=$sBasketAmount
			AND p.articleID=d2.ordernumber
			AND d2.articleID=d.articleID
			AND d.articleID=a.id
		";


		$premium = $this->sSYSTEM->sDB_CONNECTION->GetRow($sql);
		if(empty($premium))
		return false;

		$premium = $this->sSYSTEM->sMODULES['sArticles']->sGetTranslation($premium,$premium["articleID"],"article",$this->sSYSTEM->sLanguage);
		$articlename = $this->sSYSTEM->sDB_CONNECTION->qstr(trim($premium["name"]." ".$premium["additionaltext"]));
		$premium["ordernumber"] = $this->sSYSTEM->sDB_CONNECTION->qstr($premium["ordernumber"]);

		$sql = "
			INSERT INTO s_order_basket (
				sessionID, articlename, articleID, ordernumber, quantity, price, netprice, datum, modus, currencyFactor
			) VALUES (
				'{$this->sSYSTEM->sSESSION_ID}', $articlename, {$premium["articleID"]}, {$premium["ordernumber"]}, 1, 0, 0, NOW(), 1,".$this->sSYSTEM->sCurrency["factor"]."
			)
		";
		return $this->sSYSTEM->sDB_CONNECTION->Execute($sql);
	}

	/**
	 * Anzahl Artikel / Positionen des Warenkorbs auslesen
	 * @access public
	 * @return int - Anzahl Positionen
	 */
	public function sCountArticles()
	{
		$sql = 'SELECT COUNT(*) FROM s_order_basket WHERE modus=0 AND sessionID=?';
		return $this->sSYSTEM->sDB_CONNECTION->GetOne($sql, array($this->sSYSTEM->sSESSION_ID));
	}

	/**
	 * Get the max used tax-rate in basket in percent
	 * @return 
	 */
	public function getMaxTax(){

		$sql = "
			SELECT
				MAX(t.tax) as max_tax
			FROM s_order_basket b
			LEFT JOIN s_articles a
			ON b.articleID=a.id
			AND b.modus=0
			LEFT JOIN s_core_tax t
			ON t.id=a.taxID
			WHERE b.sessionID=?
			GROUP BY b.sessionID
		";
		$taxRate = $this->sSYSTEM->sDB_CONNECTION->GetOne($sql,array(empty($this->sSYSTEM->sSESSION_ID) ? session_id() : $this->sSYSTEM->sSESSION_ID));
		return $taxRate;
	}
	/**
	 * Gutschein in den Warenkorb legen
	 * @param string $sTicket - Gutschein-Code
	 * @access public
	 * @return array
	 */
	public function sAddVoucher($sTicket,$BASKET=''){
		if (Enlight()->Events()->notifyUntil('Shopware_Modules_Basket_AddVoucher_Start', array('subject'=>$this,'code'=>$sTicket,"basket"=>$BASKET))){
			return false;
		}

		$sTicket = stripslashes($sTicket);
		$sTicket = strtolower($sTicket);
		$sql = "
		SELECT * FROM s_emarketing_vouchers WHERE LOWER(vouchercode)=?
		AND ((valid_to>=now() AND valid_from<=now()) OR valid_to='0000-00-00')
		";

		$ticketResult = $this->sSYSTEM->sDB_CONNECTION->GetRow($sql,array($sTicket));
		// Check if voucher was already cashed
		if ($this->sSYSTEM->_SESSION["sUserId"] && $ticketResult["id"]){
			$userid = $this->sSYSTEM->_SESSION["sUserId"];
			$sql = "
			SELECT s_order_details.id AS id FROM s_order, s_order_details
			WHERE s_order.userID = $userid AND s_order_details.orderID=s_order.id 
			AND s_order_details.articleordernumber = '{$ticketResult["ordercode"]}'
			AND s_order_details.ordernumber!='0'
			";

			$queryVoucher = $this->sSYSTEM->sDB_CONNECTION->GetAll($sql);
			if (count($queryVoucher)>=$ticketResult["numorder"] && !$ticketResult["modus"]){

				$sErrorMessages[] = $this->sSYSTEM->sCONFIG['sErrors']['sVoucherAlreadyCashed'];

				return array("sErrorFlag"=>$sErrorFlag,"sErrorMessages"=>$sErrorMessages);
			}
		}

		if ($ticketResult["id"]){
			//echo "NO INDIVIDUAL CODE $sTicket";
			// Check if ticket is available anymore
			$countTicket = $this->sSYSTEM->sDB_CONNECTION->GetRow("
			SELECT COUNT(id) AS vouchers FROM s_order_details WHERE articleordernumber='{$ticketResult["ordercode"]}'
			AND s_order_details.ordernumber!='0'
			");
		}else {
			// Check for individual voucher - code
			$sql = "
			SELECT s_emarketing_voucher_codes.id AS id, s_emarketing_voucher_codes.code AS vouchercode,description, numberofunits,customergroup, value,restrictarticles, minimumcharge, shippingfree, bindtosupplier,
			taxconfig,
			valid_from,valid_to,ordercode, modus,percental,strict,subshopID FROM s_emarketing_vouchers, s_emarketing_voucher_codes
			WHERE
				modus = 1
			AND
				s_emarketing_vouchers.id = s_emarketing_voucher_codes.voucherID
			AND
				LOWER(code) = ?
			AND 
				cashed != 1
			AND ((s_emarketing_vouchers.valid_to>=now() AND s_emarketing_vouchers.valid_from<=now()) OR s_emarketing_vouchers.valid_to='0000-00-00')
			";
			$ticketResult = $this->sSYSTEM->sDB_CONNECTION->GetRow($sql,array($sTicket));
			if ($ticketResult["description"]){
				$indivualCode = true;
			}else {
				$indivualCode = false;
			}
		}


		// Check if ticket exists
		if (!count($ticketResult) || !$sTicket || ($ticketResult["numberofunits"]<=$countTicket["vouchers"] && !$indivualCode)){
			$sErrorMessages[] = $this->sSYSTEM->sCONFIG['sErrors']['sVoucherNotFound'];
			return array("sErrorFlag"=>$sErrorFlag,"sErrorMessages"=>$sErrorMessages);
		}

		if (!empty($ticketResult["strict"])){
			$restrictDiscount = true;
		}else {
			$restrictDiscount = false;
		}

		if (!empty($ticketResult["subshopID"])){
			if ($this->sSYSTEM->sSubShop["id"] != $ticketResult["subshopID"]){
				$sErrorMessages[] = $this->sSYSTEM->sCONFIG['sErrors']['sVoucherNotFound'];
				return array("sErrorFlag"=>$sErrorFlag,"sErrorMessages"=>$sErrorMessages);
			}
		}

		// Check if any voucher is already in basket
		$chkBasket = $this->sSYSTEM->sDB_CONNECTION->GetRow("
		SELECT id FROM s_order_basket WHERE sessionID='".$this->sSYSTEM->sSESSION_ID."' AND modus=2
		");


		if (count($chkBasket)){
			$sErrorMessages[] = $this->sSYSTEM->sCONFIG['sErrors']['sVoucherOnlyOnePerOrder'];
			return array("sErrorFlag"=>$sErrorFlag,"sErrorMessages"=>$sErrorMessages);
		}

		// Restrict to customergroup
		if (!empty($ticketResult["customergroup"]) && !empty($this->sSYSTEM->_SESSION["sUserId"])){
			$userid = $this->sSYSTEM->_SESSION["sUserId"];

			// Get customergroup
			$queryCustomergroup = $this->sSYSTEM->sDB_CONNECTION->GetRow("
			SELECT s_core_customergroups.id, customergroup FROM s_user, s_core_customergroups WHERE s_user.id=$userid
			AND s_user.customergroup = s_core_customergroups.groupkey
			");

			$customergroup = $queryCustomergroup["customergroup"];

			if ($customergroup!=$ticketResult["customergroup"] &&  $ticketResult["customergroup"]!=$queryCustomergroup["id"]){
				$sErrorMessages[] = $this->sSYSTEM->sCONFIG['sErrors']['sVoucherWrongCustomergroup'];
				return array("sErrorFlag"=>$sErrorFlag,"sErrorMessages"=>$sErrorMessages);
			}
		}

		// Restrict to articles
		if (!empty($ticketResult["restrictarticles"]) && strlen($ticketResult["restrictarticles"])>5){
			$restrictedArticles = explode(";",$ticketResult["restrictarticles"]);
			if (count($restrictedArticles)==0) $restrictedArticles[] = $ticketResult["restrictarticles"];
			foreach ($restrictedArticles as $k => $restrictedArticle) $restrictedArticles[$k] = (string)$this->sSYSTEM->sDB_CONNECTION->qstr($restrictedArticle);

			$sql = "
			SELECT id FROM s_order_basket WHERE sessionID='".$this->sSYSTEM->sSESSION_ID."' AND modus=0
			AND ordernumber IN (".implode(",",$restrictedArticles).")
			";

			$getOrdernumbers = $this->sSYSTEM->sDB_CONNECTION->GetOne($sql);
			$foundMatchingArticle = false;

			if (!empty($getOrdernumbers)) $foundMatchingArticle = true;

			if (empty($foundMatchingArticle)){

				$sErrorMessages[] = $this->sSYSTEM->sCONFIG['sErrors']['sVoucherBoundToArticles'];
				return array("sErrorFlag"=>$sErrorFlag,"sErrorMessages"=>$sErrorMessages);
			}
		}
		// Restrict to supplier
		if ($ticketResult["bindtosupplier"]){
			$searchHersteller = $ticketResult["bindtosupplier"];
			$sql = "
			SELECT s_order_basket.id FROM s_order_basket, s_articles, s_articles_supplier WHERE
			s_order_basket.articleID=s_articles.id AND s_articles.supplierID=$searchHersteller
			AND s_order_basket.sessionID='".$this->sSYSTEM->sSESSION_ID."'
			";

			$chkHersteller = $this->sSYSTEM->sDB_CONNECTION->GetRow($sql);

			if (!count($chkHersteller)){
				// Name des Herstellers abfragen
				$queryHersteller = $this->sSYSTEM->sDB_CONNECTION->GetRow("
				SELECT name FROM s_articles_supplier WHERE id=$searchHersteller
				");

				$sErrorMessages[] = str_replace("{sSupplier}",$queryHersteller["name"],$this->sSYSTEM->sCONFIG['sErrors']['sVoucherBoundToSupplier']);
				return array("sErrorFlag"=>$sErrorFlag,"sErrorMessages"=>$sErrorMessages);
			}
		}

		if (!empty($restrictDiscount) && (!empty($restrictedArticles) || !empty($searchHersteller))){
			$amount =  $this->sGetAmountRestrictedArticles($restrictedArticles,$searchHersteller);
		}else {
			$amount =  $this->sGetAmountArticles();
		}
		if ($this->sSYSTEM->sCurrency["factor"] && empty($ticketResult["percental"])){
			$factor = $this->sSYSTEM->sCurrency["factor"];
			$ticketResult["value"] *= $factor;
		}else {
			$factor = 1;
		}

		if (($amount["totalAmount"]/$factor) < $ticketResult["minimumcharge"]){

			$sErrorMessages[] = str_replace("{sMinimumCharge}",$ticketResult["minimumcharge"],$this->sSYSTEM->sCONFIG['sErrors']['sVoucherMinimumCharge']);
			return array("sErrorFlag"=>$sErrorFlag,"sErrorMessages"=>$sErrorMessages);
		}

		$timeInsert = date("Y-m-d H:i:s");

		$vouchername = $this->sSYSTEM->sCONFIG['sVOUCHERNAME'];
		if ($ticketResult["percental"]){
			$value = $ticketResult["value"];
			$vouchername .= " ".$value." %";
			$ticketResult["value"] = ($amount["totalAmount"] / 100) * floatval($value);
		}


		// Free tax configuration for vouchers
		// Trac ticket 4708 st.hamann
		if ((!$this->sSYSTEM->sUSERGROUPDATA["tax"] && $this->sSYSTEM->sUSERGROUPDATA["id"]) || $ticketResult["taxconfig"]=="none"){
			// if net customergroup - calculate without tax
			$tax = $ticketResult["value"] * -1;
		}else {
			if ($ticketResult["taxconfig"] == "default" || empty($ticketResult["taxconfig"])){
				$tax = round($ticketResult["value"]/(100+$this->sSYSTEM->sCONFIG['sVOUCHERTAX'])*100,3)*-1;
				// Pre 3.5.4 behaviour
			}elseif ($ticketResult["taxconfig"]=="auto"){
				// Check max. used tax-rate from basket
				$tax = $this->getMaxTax();
				$tax = round($ticketResult["value"]/(100+$tax)*100,3)*-1;
			}elseif (intval($ticketResult["taxconfig"])){
				// Fix defined tax
				$temporaryTax =$ticketResult["taxconfig"];
				$getTaxRate = $this->sSYSTEM->sDB_CONNECTION->getOne("
				SELECT tax FROM s_core_tax WHERE id = ?
				",array($temporaryTax));
				$tax = round($ticketResult["value"]/(100+$getTaxRate)*100,3)*-1;
			}else {
				// No tax
				$tax = $ticketResult["value"] * -1;
			}
		}

		$ticketResult["value"] = $ticketResult["value"] * -1;

		if ($ticketResult["shippingfree"]){
			$shippingfree = "1";
		}else {
			$shippingfree = "0";
		}

		$sql = "
		INSERT INTO s_order_basket (sessionID, articlename, articleID, ordernumber, shippingfree, quantity, price, netprice, datum, modus, currencyFactor)
		VALUES ('".$this->sSYSTEM->sSESSION_ID."','".$vouchername."',{$ticketResult["id"]},'{$ticketResult["ordercode"]}',$shippingfree,1,{$ticketResult["value"]},$tax,'$timeInsert',2,".$this->sSYSTEM->sCurrency["factor"].")
		";
		$sql = Enlight()->Events()->filter('Shopware_Modules_Basket_AddVoucher_FilterSql',$sql, array('subject'=>$this,"voucher"=>$ticketResult,"name"=>$vouchername,"shippingfree"=>$shippingfree,"tax"=>$tax));

		if (!$this->sSYSTEM->sDB_CONNECTION->Execute($sql)){
			$this->sSYSTEM->E_CORE_WARNING ("sBasket-AddVoucher ##01","Could not add voucher".$sql);
		}

		return;
	}

	/**
	 * Gesamtgewicht des Warenkorbs auslesen
	 * @access public
	 * @return double - Gewicht in KG
	 */
	public function sGetBasketWeight()
	{
		$sql = '
			SELECT SUM(d.weight*b.quantity) as weight
			FROM s_order_basket b
			
			LEFT JOIN s_articles a
			ON b.articleID=a.id
			AND b.modus=0
			AND b.esdarticle=0
			
			LEFT JOIN s_articles_groups_value g
			ON g.ordernumber=b.ordernumber
			AND g.articleID=a.id
			
			LEFT JOIN s_articles_details d
			ON (d.ordernumber=b.ordernumber OR g.valueID IS NOT NULL)
			AND d.articleID=a.id
	
			WHERE b.sessionID=?
		';
		$weight = $this->sSYSTEM->sDB_CONNECTION->GetOne($sql, array($this->sSYSTEM->sSESSION_ID));
		return $weight;
	}


	/**
	 * IDs der im Warenkorb befindlichen Artikel auslesen
	 * @access public
	 * @return array
	 */
	public function sGetBasketIds(){
		$getArticles = $this->sSYSTEM->sDB_CONNECTION->GetAll("SELECT DISTINCT articleID FROM s_order_basket WHERE sessionID=?
		AND modus=0
		ORDER BY modus ASC, datum DESC",array($this->sSYSTEM->sSESSION_ID));

		foreach ($getArticles as $article){
			$articles[] = $article["articleID"];
		}

		return $articles;
	}

	/**
	 * Aufschlag/Rabatt Zahlungsart einfügen
	 * @access public
	 * @return void
	 */

	public function sCheckMinimumCharge(){
		if ($this->sSYSTEM->sUSERGROUPDATA["minimumorder"] && !$this->sSYSTEM->sUSERGROUPDATA["minimumordersurcharge"]){
			$amount = $this->sGetAmount();
			if ($amount["totalAmount"]<($this->sSYSTEM->sUSERGROUPDATA["minimumorder"]*$this->sSYSTEM->sCurrency["factor"])){
				return ($this->sSYSTEM->sUSERGROUPDATA["minimumorder"]*$this->sSYSTEM->sCurrency["factor"]);
			}else {
				return false;
			}
		}
		return false;
	}

	/**
	 * Aufschlag Zahlungsart
	 * @access public
	 * @return array
	 */
	public function sInsertSurcharge(){
		/*if (!empty($this->sSYSTEM->sCONFIG['sPREMIUMSHIPPIUNG']))
		{
		return true;
		}*/

		$name = isset($this->sSYSTEM->sCONFIG['sSURCHARGENUMBER']) ? $this->sSYSTEM->sCONFIG['sSURCHARGENUMBER']: "SURCHARGE";

		$rs = $this->sSYSTEM->sDB_CONNECTION->Execute("
		DELETE FROM s_order_basket WHERE sessionID='".$this->sSYSTEM->sSESSION_ID."' AND ordernumber='$name'
		");

		if (!$this->sCountArticles()) return false;

		if ($this->sSYSTEM->sUSERGROUPDATA["minimumorder"] && $this->sSYSTEM->sUSERGROUPDATA["minimumordersurcharge"]){

			$amount = $this->sGetAmount();


			if ($amount["totalAmount"]<$this->sSYSTEM->sUSERGROUPDATA["minimumorder"]){

				if (!empty($this->sSYSTEM->sCONFIG["sTAXAUTOMODE"])){
					$tax = $this->sSYSTEM->sMODULES['sBASKET']->getMaxTax();
				}else {
					$tax = $this->sSYSTEM->sCONFIG['sDISCOUNTTAX'];
				}
				if (!$tax) $tax = 119;

				if ((!$this->sSYSTEM->sUSERGROUPDATA["tax"] && $this->sSYSTEM->sUSERGROUPDATA["id"])){
					$discountNet = $this->sSYSTEM->sUSERGROUPDATA["minimumordersurcharge"];
				}else {
					$discountNet = round($this->sSYSTEM->sUSERGROUPDATA["minimumordersurcharge"] / (100+$tax) * 100,3);
				}

				if ($this->sSYSTEM->sCurrency["factor"]){
					$factor = $this->sSYSTEM->sCurrency["factor"];
					$discountNet /= $factor;
				}else {
					$factor = 1;
				}

				$surcharge = $this->sSYSTEM->sUSERGROUPDATA["minimumordersurcharge"]/$factor;



				$this->sSYSTEM->sDB_CONNECTION->Execute("
				INSERT INTO s_order_basket (sessionID, articlename, articleID, ordernumber, quantity,price,netprice, datum,modus, currencyFactor)
				VALUES ('".$this->sSYSTEM->sSESSION_ID."',
				'{$this->sSYSTEM->sCONFIG["sSURCHARGENAME"]}',
				0,
				'$name',
				1,
				".$surcharge.",
				".$discountNet.",
				now(),
				4,
				".$this->sSYSTEM->sCurrency["factor"]."
				)
				");

			}
		}
	}

	/**
	 * Prozent-Rabatt einfügen
	 * @access public
	 * @return void
	 */
	public function sInsertSurchargePercent()
	{
		if (!empty($this->sSYSTEM->sCONFIG['sPREMIUMSHIPPIUNG']))
		{
			return true;
		}
		if (!$this->sSYSTEM->_SESSION["sUserId"]){
			if (!$this->sSYSTEM->_SESSION["sPaymentID"]){
				return false;
			}else {
				$paymentInfo = $this->sSYSTEM->sDB_CONNECTION->GetRow("
				SELECT debit_percent FROM s_core_paymentmeans WHERE id=".intval($this->sSYSTEM->_SESSION["sPaymentID"]));
			}
		} else {
			$userData =  $this->sSYSTEM->sDB_CONNECTION->GetRow("SELECT paymentID FROM s_user WHERE id=".intval($this->sSYSTEM->_SESSION["sUserId"]));
			$paymentInfo = $this->sSYSTEM->sDB_CONNECTION->GetRow("
			SELECT debit_percent FROM s_core_paymentmeans WHERE id=".$userData["paymentID"]);

		}

		$name = isset($this->sSYSTEM->sCONFIG['sPAYMENTSURCHARGENUMBER']) ? $this->sSYSTEM->sCONFIG['sPAYMENTSURCHARGENUMBER']: "PAYMENTSURCHARGE";
		// Depends on payment-mean
		$percent = $paymentInfo["debit_percent"];

		$rs = $this->sSYSTEM->sDB_CONNECTION->Execute("
		DELETE FROM s_order_basket WHERE sessionID='".$this->sSYSTEM->sSESSION_ID."' AND ordernumber='$name'
		");

		if (!$this->sCountArticles()) return false;

		if ($percent){

			$amount = $this->sGetAmount();

			if ($percent>=0){
				$surchargename = $this->sSYSTEM->sCONFIG["sPAYMENTSURCHARGEADD"];
			}else {
				$surchargename = $this->sSYSTEM->sCONFIG["sPAYMENTSURCHARGEDEV"];
			}
			//print_r($amount); exit;
			$surcharge = $amount["totalAmount"] / 100 * $percent;
			//echo $amount["totalAmount"]." - ".$surcharge." <br />";
			if (!empty($this->sSYSTEM->sCONFIG["sTAXAUTOMODE"])){
				$tax = $this->sSYSTEM->sMODULES['sBASKET']->getMaxTax();
			}else {
				$tax = $this->sSYSTEM->sCONFIG['sDISCOUNTTAX'];
			}
			
			if (!$tax) $tax = 119;

			if ((!$this->sSYSTEM->sUSERGROUPDATA["tax"] && $this->sSYSTEM->sUSERGROUPDATA["id"])){
				$discountNet = $surcharge;
			}else {
				$discountNet = round($surcharge / (100+$tax) * 100,3);
			}

			if ($this->sSYSTEM->sCurrency["factor"]){
				$factor = $this->sSYSTEM->sCurrency["factor"];
				/*$discountNet /= $factor;
				$surcharge /= $factor;*/
			}else {
				$factor = 1;
			}



			$this->sSYSTEM->sDB_CONNECTION->Execute("
				INSERT INTO s_order_basket (sessionID, articlename, articleID, ordernumber, quantity,price,netprice, datum,modus,currencyFactor)
				VALUES ('".$this->sSYSTEM->sSESSION_ID."',
				'$surchargename',
				0,
				'$name',
				1,
				".$surcharge.",
				".$discountNet.",
				now(),
				4,
				".$this->sSYSTEM->sCurrency["factor"]."
				)
				");


		}
	}

	/**
	 * Anzahl Artikel im Warenkorb auslesen
	 * @access public
	 * @return array
	 */
	public function sCountBasket(){
		$getArticles = $this->sSYSTEM->sDB_CONNECTION->GetAll("SELECT id FROM s_order_basket WHERE sessionID=? AND modus=0
		",array($this->sSYSTEM->sSESSION_ID));
		return count($getArticles);
	}

	/**
	 * Den gesamten Warenkorb auslesen
	 * @access public
	 * @return array
	 */
	public function sGetBasket(){

		eval($this->sSYSTEM->sCallHookPoint("sBasket.php_sGetBasket_Start"));
		if (!$this->sSYSTEM->_GET["sViewport"]){
			$sViewport = $this->sSYSTEM->_POST["sViewport"];
		}else {
			$sViewport = $this->sSYSTEM->_GET["sViewport"];
		}

		if ($sViewport){
			$updateBasketStatus = $this->sSYSTEM->sDB_CONNECTION->Execute(
			"UPDATE s_order_basket
			SET 
			lastviewport = ?,
			useragent = ?,
			userID = '".intval($this->sSYSTEM->_SESSION['sUserId'])."'
			WHERE sessionID=?",array($sViewport,$_SERVER["HTTP_USER_AGENT"],array($this->sSYSTEM->sSESSION_ID)));
			// Refresh basket-prices
			$basketData = $this->sSYSTEM->sDB_CONNECTION->GetAll("
			SELECT id,modus, quantity FROM s_order_basket
			WHERE sessionID=?",array($this->sSYSTEM->sSESSION_ID));
			foreach ($basketData as $basketContent){
				if (empty($basketContent["modus"])){
					$this->sSYSTEM->sMODULES['sBasket']->sUpdateArticle ($basketContent["id"],$basketContent["quantity"]);
				}
			}
		}
		$this->sUpdateBundles();
		// Check, if we have some free products for the client
		$this->sInsertPremium();

		// Delete previous given discounts
		if (empty($this->sSYSTEM->sCONFIG['sPREMIUMSHIPPIUNG']))
		{
			$rs = $this->sSYSTEM->sDB_CONNECTION->Execute("
			DELETE FROM s_order_basket WHERE sessionID=? AND modus=3
			",array($this->sSYSTEM->sSESSION_ID));
		}
		// Check for surcharges
		$this->sInsertSurcharge();
		// Check for skonto / percent surcharges
		$this->sInsertSurchargePercent();

		// Calculate global basket discount
		$this->sInsertDiscount();

		$sql = "
		SELECT s_order_basket.*, a.packunit, minpurchase,taxID,IF (ad.instock,ad.instock,av.instock) AS `instock`,suppliernumber,maxpurchase,purchasesteps,purchaseunit,unitID,laststock,shippingtime,releasedate,releasedate AS sReleaseDate,stockmin,esd, su.description AS itemUnit, ob_attr1,ob_attr2,ob_attr3,ob_attr4,ob_attr5,ob_attr6 FROM 
			s_order_basket
		LEFT JOIN s_articles_details AS ad ON ad.ordernumber = s_order_basket.ordernumber
		LEFT JOIN s_articles_groups_value AS av ON av.ordernumber = s_order_basket.ordernumber
		LEFT JOIN s_articles a ON (a.id = ad.articleID OR a.id = av.articleID)
		LEFT JOIN s_core_units su ON su.id = a.unitID
		WHERE sessionID=?
		ORDER BY id ASC, datum DESC
		";	// Modified 3.0.2 - 07.01.2009 - STH
		$sql = Enlight()->Events()->filter('Shopware_Modules_Basket_GetBasket_FilterSQL', $sql, array('subject'=>$this));

		eval($this->sSYSTEM->sCallHookPoint("sBasket.php_sGetBasket_AfterSQL"));
		$getArticles = $this->sSYSTEM->sDB_CONNECTION->GetAll($sql,array($this->sSYSTEM->sSESSION_ID));
		$countItems = count($getArticles);

		if (!empty($countItems)){
			// Reformating data, add additional datafields to array
			foreach ($getArticles as $key => $value){
				$getArticles[$key] = Enlight()->Events()->filter('Shopware_Modules_Basket_GetBasket_FilterItemStart', $getArticles[$key], array('subject'=>$this,'getArticles'=>$getArticles));

				eval($this->sSYSTEM->sCallHookPoint("sBasket.php_sGetBasket_Loop1"));
				if (empty($getArticles[$key]["modus"])){
					$getArticles[$key]["shippinginfo"] = true;
				}else {
					$getArticles[$key]["shippinginfo"] = false;
				}
				if (!empty($getArticles[$key]["releasedate"]) && strtotime($getArticles[$key]["releasedate"]) <= time()){
					$getArticles[$key]["sReleaseDate"] = $getArticles[$key]["releasedate"] = "";
				}
				$getArticles[$key]["esd"] = $getArticles[$key]["esdarticle"];
				if (empty($getArticles[$key]["minpurchase"])) $getArticles[$key]["minpurchase"] = 1;
				if (empty($getArticles[$key]["purchasesteps"])) $getArticles[$key]["purchasesteps"] = 1;
				if ($getArticles[$key]["purchasesteps"]<=0) unset($getArticles[$key]["purchasesteps"]);

				if (empty($getArticles[$key]["maxpurchase"])) {
					$getArticles[$key]["maxpurchase"] = $this->sSYSTEM->sCONFIG['sMAXPURCHASE'];
				}
				if(!empty($getArticles[$key]["laststock"])
				&& $getArticles[$key]["instock"] < $getArticles[$key]["maxpurchase"]) {
					$getArticles[$key]["maxpurchase"] = $getArticles[$key]["instock"];
				}

				// If unitID is set, query it
				if (!empty($getArticles[$key]["unitID"])){
					$getUnitData = $this->sSYSTEM->sMODULES['sArticles']->sGetUnit($getArticles[$key]["unitID"]);
					$getArticles[$key]["itemUnit"] = $getUnitData["description"];
				} else {
					unset($getArticles[$key]["unitID"]);
				}

				if (!empty($getArticles[$key]["packunit"])){
					$getPackUnit = $this->sSYSTEM->sMODULES['sArticles']->sGetTranslation(array(),$getArticles[$key]["articleID"],"article",$this->sSYSTEM->sLanguage);
					if (!empty($getPackUnit["packunit"])){
						$getArticles[$key]["packunit"] = $getPackUnit["packunit"];
					}
				}

				eval($this->sSYSTEM->sCallHookPoint("sBasket.php_sGetBasket_Loop2"));
				$quantity = $getArticles[$key]["quantity"];
				$price = $getArticles[$key]["price"];
				$netprice = $getArticles[$key]["netprice"];

				if ($value["modus"]==2){
					$sql = "
						SELECT vouchercode,taxconfig FROM s_emarketing_vouchers WHERE ordercode='{$getArticles[$key]["ordernumber"]}'
						";

					$ticketResult = $this->sSYSTEM->sDB_CONNECTION->GetRow($sql);

					if (!$ticketResult["vouchercode"]){
						// Query Voucher-Code
						$queryVoucher = $this->sSYSTEM->sDB_CONNECTION->GetRow("
							SELECT code FROM s_emarketing_voucher_codes WHERE id = {$getArticles[$key]["articleID"]}
							AND cashed!=1
							");
						$ticketResult["vouchercode"] = $queryVoucher["code"];
					}
					$this->sDeleteArticle($getArticles[$key]["id"]);

					//if voucher was deleted, do not restore
					if($this->sSYSTEM->_GET['sDelete'] != 'voucher')
					{
						$this->sAddVoucher($ticketResult["vouchercode"]);

					}
				}
				// If shop is in net mode, we have to consider
				// the tax separately
				if (($this->sSYSTEM->sCONFIG['sARTICLESOUTPUTNETTO'] && !$this->sSYSTEM->sUSERGROUPDATA["tax"]) || (!$this->sSYSTEM->sUSERGROUPDATA["tax"] && $this->sSYSTEM->sUSERGROUPDATA["id"]))
				{

					if (empty($value["modus"])){
						$tax = $this->sSYSTEM->sMODULES['sArticles']->sGetArticleTaxById($getArticles[$key]["articleID"]);
						$priceWithTax = round($netprice,2) / 100 * (100+$tax);

						$getArticles[$key]["amountWithTax"] = $quantity * $priceWithTax;
						// If basket comprised any discount, calculate brutto-value for the discount
						if ($this->sSYSTEM->sUSERGROUPDATA["basketdiscount"] && $this->sCheckForDiscount()){
							$discount += ($getArticles[$key]["amountWithTax"]/100*$this->sSYSTEM->sUSERGROUPDATA["basketdiscount"]);
						}

					}elseif ($value["modus"]==3){
						if (!empty($this->sSYSTEM->sCONFIG["sTAXAUTOMODE"])){
							$tax = $this->sSYSTEM->sMODULES['sBASKET']->getMaxTax();
						}else {
							$tax = $this->sSYSTEM->sCONFIG['sDISCOUNTTAX'];
						}
						$getArticles[$key]["amountWithTax"] = round(1 * (round($price,2) / 100 * (100+$tax)),2);
						// Basket discount
					}elseif ($value["modus"]==2){
						// Validate voucher
						if (empty($ticketResult["taxconfig"]) || $ticketResult["taxconfig"] == "default"){
							$tax = $this->sSYSTEM->sCONFIG['sVOUCHERTAX'];
						}elseif ($ticketResult["taxconfig"] == "auto"){
							$tax = $this->getMaxTax();
						}elseif ($ticketResult["taxconfig"]=="none"){
							$tax = 0;
						}elseif (intval($ticketResult["taxconfig"])){
							// Fix defined tax
							$temporaryTax =$ticketResult["taxconfig"];
							$tax = $this->sSYSTEM->sDB_CONNECTION->getOne("
							SELECT tax FROM s_core_tax WHERE id = ?
							",array($temporaryTax));
						}

						$getArticles[$key]["amountWithTax"] = round(1 * (round($price,2) / 100 * (100+$tax)),2);
						
						if ($this->sSYSTEM->sUSERGROUPDATA["basketdiscount"] && $this->sCheckForDiscount()){
							$discount += ($getArticles[$key]["amountWithTax"]/100*($this->sSYSTEM->sUSERGROUPDATA["basketdiscount"]));
						}
					}elseif ($value["modus"]==4 || $value["modus"]==10){
						if (!empty($this->sSYSTEM->sCONFIG["sTAXAUTOMODE"])){
							$tax = $this->sSYSTEM->sMODULES['sBASKET']->getMaxTax();
						}else {
							$tax = $this->sSYSTEM->sCONFIG['sDISCOUNTTAX'];
						}
						$getArticles[$key]["amountWithTax"] = round(1 * ($price / 100 * (100+$tax)),2);
						if ($this->sSYSTEM->sUSERGROUPDATA["basketdiscount"] && $this->sCheckForDiscount()){
							$discount += ($getArticles[$key]["amountWithTax"]/100*$this->sSYSTEM->sUSERGROUPDATA["basketdiscount"]);
						}
					}
				}

				$getArticles[$key]["amount"] = $quantity * round($price,2);

				//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

				//reset purchaseunit and save the original value in purchaseunitTemp
				if ($getArticles[$key]["purchaseunit"]>0){
					$getArticles[$key]["purchaseunitTemp"] = $getArticles[$key]["purchaseunit"];
					$getArticles[$key]["purchaseunit"] = 1;
				}

				//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

				eval($this->sSYSTEM->sCallHookPoint("sBasket.php_sGetBasket_Loop3"));
				// If price per unit is not referering to 1, calculate base-price
				// Choose 1000, quantity refers to 500, calculate price / 1000 * 500 as reference
				if ($getArticles[$key]["purchaseunit"]>0){
					$getArticles[$key]["itemInfo"] = $getArticles[$key]["purchaseunit"]." {$getUnitData["description"]} á ".$this->sSYSTEM->sMODULES['sArticles']->sFormatPrice($getArticles[$key]["amount"] / $quantity * $getArticles[$key]["purchaseunit"]);
					$getArticles[$key]["itemInfoArray"]["reference"] = $getArticles[$key]["purchaseunit"];
					$getArticles[$key]["itemInfoArray"]["unit"] = $getUnitData;
					$getArticles[$key]["itemInfoArray"]["price"] = $this->sSYSTEM->sMODULES['sArticles']->sFormatPrice($getArticles[$key]["amount"] / $quantity * $getArticles[$key]["purchaseunit"]);
					//echo $getArticles[$key]["quantityInfo"];
				}


				if ($value["modus"]==2){
					// Gutscheine
					if (!$this->sSYSTEM->sUSERGROUPDATA["tax"] && $this->sSYSTEM->sUSERGROUPDATA["id"]){
						$getArticles[$key]["amountnet"] = $quantity * round($price,2);
					}else  {

						$getArticles[$key]["amountnet"] = $quantity * round($netprice,2);
					}

				}else {
					if (!$this->sSYSTEM->sUSERGROUPDATA["tax"] && $this->sSYSTEM->sUSERGROUPDATA["id"]){
						$getArticles[$key]["amountnet"] = $quantity * round($netprice,2);
					}else {
						$getArticles[$key]["amountnet"] = $quantity * $netprice;
					}
				}

				if(!empty($getArticles[$key]["bundleID"]) && !$this->sSYSTEM->sUSERGROUPDATA["tax"] && $this->sSYSTEM->sUSERGROUPDATA["id"]){

					$sql = "SELECT t.tax FROM `s_articles_bundles` ab
							INNER JOIN `s_core_tax` t ON t.id = ab.taxID
							WHERE ab.id = ?";
					$bundle_tax = $this->sSYSTEM->sDB_CONNECTION->GetOne($sql, array($getArticles[$key]["bundleID"]));

					$getArticles[$key]["amountWithTax"] =$getArticles[$key]["amountnet"] * (100+$bundle_tax) / 100;
				}

				$totalAmount += round($getArticles[$key]["amount"],2);
				// Needed if shop is in net-mode
				$totalAmountWithTax += round($getArticles[$key]["amountWithTax"],2);
				// Ignore vouchers and premiums by counting articles
				if (!$getArticles[$key]["modus"]){
					$totalCount++;
				}

				//echo $getArticles[$key]["amountnet"]."|". round($getArticles[$key]["amountnet"],2)."\n";

				$totalAmountNet += $getArticles[$key]["amountnet"];

				$getArticles[$key]["priceNumeric"] = $getArticles[$key]["price"];
				$getArticles[$key]["price"] = $this->sSYSTEM->sMODULES['sArticles']->sFormatPrice($getArticles[$key]["price"]);
				$getArticles[$key]["amount"] =  $this->sSYSTEM->sMODULES['sArticles']->sFormatPrice($getArticles[$key]["amount"]);
				$getArticles[$key]["amountnet"] = $this->sSYSTEM->sMODULES['sArticles']->sFormatPrice($getArticles[$key]["amountnet"]);

				//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

				if (!empty($getArticles[$key]["purchaseunitTemp"])){
					$getArticles[$key]["purchaseunit"] = $getArticles[$key]["purchaseunitTemp"];
					$getArticles[$key]["itemInfo"] = $getArticles[$key]["purchaseunit"]." {$getUnitData["description"]} á ".$this->sSYSTEM->sMODULES['sArticles']->sFormatPrice(str_replace(",",".",$getArticles[$key]["amount"]) / $quantity);
				}

				//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

				eval($this->sSYSTEM->sCallHookPoint("sBasket.php_sGetBasket_Loop4"));
				if (empty($value["modus"])){
					// Article-Image
					if (!empty($getArticles[$key]["ob_attr1"])){

						$getArticles[$key]["image"] = $this->sSYSTEM->sMODULES['sArticles']->sGetConfiguratorImage($this->sSYSTEM->sMODULES['sArticles']->sGetArticlePictures($getArticles[$key]["articleID"],false,$this->sSYSTEM->sCONFIG['sTHUMBBASKET'],false,true),$getArticles[$key]["ob_attr1"]);
					}else {
						$getArticles[$key]["image"] = $this->sSYSTEM->sMODULES['sArticles']->sGetArticlePictures($getArticles[$key]["articleID"],true,$this->sSYSTEM->sCONFIG['sTHUMBBASKET'],$getArticles[$key]["ordernumber"]);
					}
				}
				// Links to details, basket
				$getArticles[$key]["linkDetails"] = $this->sSYSTEM->sCONFIG['sBASEFILE']."?sViewport=detail&sArticle=".$getArticles[$key]["articleID"];
				if($value["modus"]==2)
				{
					$getArticles[$key]["linkDelete"] = $this->sSYSTEM->sCONFIG['sBASEFILE']."?sViewport=basket&sDelete=voucher";
				}else{
					$getArticles[$key]["linkDelete"] = $this->sSYSTEM->sCONFIG['sBASEFILE']."?sViewport=basket&sDelete=".$getArticles[$key]["id"];
				}

				$getArticles[$key]["linkNote"] = $this->sSYSTEM->sCONFIG['sBASEFILE']."?sViewport=note&sAdd=".$getArticles[$key]["ordernumber"];
				eval($this->sSYSTEM->sCallHookPoint("sBasket.php_sGetBasket_Loop5"));
				$getArticles[$key] = Enlight()->Events()->filter('Shopware_Modules_Basket_GetBasket_FilterItemEnd', $getArticles[$key], array('subject'=>$this,'getArticles'=>$getArticles));
			}

			if ($totalAmount < 0 || empty($totalCount)) {
				/*
				$deleteBasket = $this->sSYSTEM->sDB_CONNECTION->Execute("
				DELETE FROM s_order_basket WHERE sessionID='".$this->sSYSTEM->sSESSION_ID."'
				");
				*/
				return array();
			}

			// Total-Amount brutto (or netto if shop-mode is to show net-prices)
			$totalAmountNumeric = $totalAmount;
			$totalAmount = $this->sSYSTEM->sMODULES['sArticles']->sFormatPrice($totalAmount);

			// Total-Amount brutto (in any case)
			$totalAmountWithTaxNumeric = $totalAmountWithTax;
			$totalAmountWithTax = $this->sSYSTEM->sMODULES['sArticles']->sFormatPrice($totalAmountWithTax);

			// Total-Amount netto
			$totalAmountNetNumeric = $totalAmountNet;

			$totalAmountNet = $this->sSYSTEM->sMODULES['sArticles']->sFormatPrice($totalAmountNet);
			eval($this->sSYSTEM->sCallHookPoint("sBasket.php_sGetBasket_BeforeEnd1"));
			$result = array("content"=>$getArticles,"Amount"=>$totalAmount,"AmountNet"=>$totalAmountNet,"Quantity"=>$totalCount,"AmountNumeric"=>$totalAmountNumeric,"AmountNetNumeric"=>$totalAmountNetNumeric,"AmountWithTax"=>$totalAmountWithTax,"AmountWithTaxNumeric"=>$totalAmountWithTaxNumeric);
			eval($this->sSYSTEM->sCallHookPoint("sBasket.php_sGetBasket_BeforeEnd2"));

			if (!empty($this->sSYSTEM->_SESSION["sLastArticle"])){	// r302, sth
				$result["sLastActiveArticle"] = array("id"=>$this->sSYSTEM->_SESSION["sLastArticle"],"link"=> $this->sSYSTEM->sCONFIG['sBASEFILE']."?sViewport=detail&sDetails=".$this->sSYSTEM->_SESSION["sLastArticle"]);
			}

			if (!empty($result["content"])){	// r302, sth
				foreach ($result["content"] as $key => $value){
					if(!empty($value['amountWithTax']))
					{
						$t = round(str_replace(",",".",$value['amountWithTax']),2);
					}
					else
					{
						$t = str_replace(",",".",$value["price"]);
						$t = floatval(round($t*$value["quantity"],2));
					}
					if (!$this->sSYSTEM->sUSERGROUPDATA["tax"] && $this->sSYSTEM->sUSERGROUPDATA["id"]){
						$p = floatval($this->sSYSTEM->sMODULES['sArticles']->sRound($this->sSYSTEM->sMODULES['sArticles']->sRound(round($value["netprice"],2)*$value["quantity"])));
					}else {
						$p = floatval($this->sSYSTEM->sMODULES['sArticles']->sRound($this->sSYSTEM->sMODULES['sArticles']->sRound($value["netprice"]*$value["quantity"])));
					}
					$calcDifference = $this->sSYSTEM->sMODULES['sArticles']->sFormatPrice($t - $p);
					$result["content"][$key]["tax"] = $calcDifference;
				}
			}
			$result = Enlight()->Events()->filter('Shopware_Modules_Basket_GetBasket_FilterResult', $result, array('subject'=>$this));
			
			return $result;
		}else {
			return array();
		}
	}

	/**
	 * Artikel auf Merkzettel setzen
	 * @param int $articleID
	 * @param string $articleName 
	 * @param string $articleOrdernumber
	 * @access public
	 * @return void
	 */
	public function sAddNote($articleID, $articleName, $articleOrdernumber){
		$datum = date("Y-m-d H:i:s");

		if (!empty($this->sSYSTEM->_COOKIE)&&empty($this->sSYSTEM->_COOKIE["sUniqueID"]))
		{
			$this->sSYSTEM->_COOKIE["sUniqueID"] = md5(uniqid(rand()));
			setcookie("sUniqueID", $this->sSYSTEM->_COOKIE["sUniqueID"], Time()+(86400*360), '/');
		}

		// Check if this article is already noted
		$checkForArticle = $this->sSYSTEM->sDB_CONNECTION->GetRow("
		SELECT id FROM s_order_notes WHERE sUniqueID=? AND ordernumber=?
		",array($this->sSYSTEM->_COOKIE["sUniqueID"],$articleOrdernumber));

		if (!$checkForArticle["id"]){
			$queryNewPrice = $this->sSYSTEM->sDB_CONNECTION->Execute("
			INSERT INTO s_order_notes (sUniqueID, userID,articlename, articleID, ordernumber, datum)
			VALUES (?,?,?,?,?,?)
			",array(empty($this->sSYSTEM->_COOKIE["sUniqueID"]) ? $this->sSYSTEM->sSESSION_ID : $this->sSYSTEM->_COOKIE["sUniqueID"],$this->sSYSTEM->_SESSION['sUserId'] ?$this->sSYSTEM->_SESSION['sUserId'] : "0" ,$articleName,$articleID,$articleOrdernumber,$datum));

			if (!$queryNewPrice){
				$this->sSYSTEM->E_CORE_WARNING ("sBasket##sAddNote##01","Error in SQL-query");
				return false;
			}
		}
		return true;
	}

	/**
	 * Alle auf dem Merkzettel stehenden Artikel auslesen
	 * 
	 * @return array
	 */
	public function sGetNotes()
	{
		$getArticles = $this->sSYSTEM->sDB_CONNECTION->GetAll('
			SELECT n.* FROM s_order_notes n, s_articles a
			WHERE (sUniqueID=? OR (userID!=0 AND userID=?))
			AND a.id = n.articleID AND a.active = 1
			ORDER BY n.datum DESC
		', array(
			empty($this->sSYSTEM->_COOKIE['sUniqueID']) ? $this->sSYSTEM->sSESSION_ID : $this->sSYSTEM->_COOKIE['sUniqueID'],
			isset($this->sSYSTEM->_SESSION['sUserId']) ? $this->sSYSTEM->_SESSION['sUserId'] : 0
		));
		
		if(empty($getArticles)) {
			return $getArticles;
		}

		// Reformating data, add additional datafields to array
		foreach ($getArticles as $key => $value){
			// Article-Image
			$getArticles[$key] = $this->sSYSTEM->sMODULES['sArticles']->sGetPromotionById("fix", 0, (int) $value["articleID"]);
			if(empty($getArticles[$key])) {
				$this->sDeleteNote($value["id"]);
				unset($getArticles[$key]);
				continue;
			}
			$getArticles[$key]["articlename"] = $getArticles[$key]["articleName"];
			$getArticles[$key]["image"] = $this->sSYSTEM->sMODULES['sArticles']->sGetArticlePictures(
				$value["articleID"], true, $this->sSYSTEM->sCONFIG['sTHUMBBASKET']
			);
			// Links to details, basket
			$getArticles[$key]["id"] = $value["id"];
			$getArticles[$key]["linkBasket"] = $this->sSYSTEM->sCONFIG['sBASEFILE']."?sViewport=basket&sAdd=".$value["ordernumber"];
			$getArticles[$key]["linkDelete"] = $this->sSYSTEM->sCONFIG['sBASEFILE']."?sViewport=note&sDelete=".$value["id"];
			$getArticles[$key]["datum_add"] = $value["datum"];
		}
		return $getArticles;
	}
	
	/**
	 * Returns the number of notepad entries
	 * 
	 * @return int
	 */
	public function sCountNotes()
	{
		$count = (int) $this->sSYSTEM->sDB_CONNECTION->GetOne('
			SELECT COUNT(*) FROM s_order_notes n, s_articles a
			WHERE (sUniqueID=? OR (userID!=0 AND userID = ?))
			AND a.id = n.articleID AND a.active = 1
		', array(
			empty($this->sSYSTEM->_COOKIE['sUniqueID']) ? $this->sSYSTEM->sSESSION_ID : $this->sSYSTEM->_COOKIE['sUniqueID'],
			isset($this->sSYSTEM->_SESSION['sUserId']) ? $this->sSYSTEM->_SESSION['sUserId'] : 0
		));
		return $count;
	}

	/**
	 * Update basket bundles
	 *
	 * @return void
	 */
	public function sUpdateBundles()
	{
		return $this->sSYSTEM->sMODULES["sBundle"]->sUpdateBundles();
	}

	/**
	 * Menge / Preis eines bestimmten Artikels aktualisieren
	 * @param int $id - s_order_basket.id
	 * @param int $quantity - Neue Menge
	 * @access public
	 * @return void
	 */
	public function sUpdateArticle ($id,$quantity){


		//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

		$tempQuantity = $quantity;

		//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

		eval($this->sSYSTEM->sCallHookPoint("sBasket.php_sUpdateArticle_Start"));
		// Int values should be int values ;)
		$quantity = intval($quantity);
		$id = intval($id);

		if (Enlight()->Events()->notifyUntil('Shopware_Modules_Basket_UpdateArticle_Start', array('subject'=>$this,'id'=>$id,"quantity"=>$quantity))){
			return false;
		}

		if (!$id){
			$this->sSYSTEM->E_CORE_WARNING("Basket Update ##00","NO ID ($id)");
			return;
		}

		$sql = "
			SELECT IFNULL(s.type, IF(g.groupID, 1, NULL)) as type
			FROM s_order_basket b
			LEFT JOIN s_articles_groups_settings s
			ON s.articleID=b.articleID
			JOIN s_articles_groups g
			ON g.articleID=b.articleID
			WHERE b.id=? AND b.sessionID=?
			AND b.modus=0
			LIMIT 1
		";
		$checkIfIsConfiguratorArticle = $this->sSYSTEM->sDB_CONNECTION->GetRow($sql, array($id, $this->sSYSTEM->sSESSION_ID));

		// Query for mininum-purchase
		$queryAdditionalInfo = $this->sSYSTEM->sDB_CONNECTION->GetRow("
		SELECT minpurchase, purchasesteps, maxpurchase, purchaseunit, pricegroupID,pricegroupActive, ordernumber, articleID FROM s_articles, s_order_basket
		WHERE s_order_basket.articleID = s_articles.id AND
		s_order_basket.id=?
		AND
		s_order_basket.sessionID=?
		",array($id,$this->sSYSTEM->sSESSION_ID));

		// Check if quantity matches minimum-purchase
		if (!$queryAdditionalInfo["minpurchase"]){
			$queryAdditionalInfo["minpurchase"] = 1;
		}

		if ($quantity<$queryAdditionalInfo["minpurchase"]){
			$quantity = $queryAdditionalInfo["minpurchase"];
		}

		// Check if quantity matches the step-requirements
		if (!$queryAdditionalInfo["purchasesteps"]){
			$queryAdditionalInfo["purchasesteps"] = 1;
		}

		if (($quantity/$queryAdditionalInfo["purchasesteps"])!=intval($quantity / $queryAdditionalInfo["purchasesteps"])){
			$quantity = intval($quantity / $queryAdditionalInfo["purchasesteps"])*$queryAdditionalInfo["purchasesteps"];
		}

		if(empty($queryAdditionalInfo["maxpurchase"]) && !empty($this->sSYSTEM->sCONFIG['sMAXPURCHASE'])){
			$queryAdditionalInfo["maxpurchase"] = $this->sSYSTEM->sCONFIG['sMAXPURCHASE'];
		}

		// Check if quantity matches max-purchase
		if ($quantity>$queryAdditionalInfo["maxpurchase"] && !empty($queryAdditionalInfo["maxpurchase"]))
		{
			$quantity = $queryAdditionalInfo["maxpurchase"];
		}

		eval($this->sSYSTEM->sCallHookPoint("sBasket.php_sUpdateArticle_Start2"));


		// Regular price-query (not an article-configurator)
		if (!isset($checkIfIsConfiguratorArticle["type"])||$checkIfIsConfiguratorArticle["type"]==3)
		{

			if ($this->sSYSTEM->sSESSION_ID && $id){
				/*
				SW 2.1 Pricegroups
				*/
				if ($queryAdditionalInfo["pricegroupActive"]){
					$quantitySQL = "AND s_articles_prices.from = 1 LIMIT 1";
				}else {
					$quantitySQL = "AND s_articles_prices.from <= $quantity AND (s_articles_prices.to >= $quantity OR s_articles_prices.to=0)";
				}
				// Get the ordernumber
				$sql = "SELECT s_articles_prices.price AS price,s_core_tax.tax AS tax,s_articles_details.id AS articleDetailsID, s_articles_details.articleID, s_order_basket.config, s_order_basket.ordernumber FROM s_articles_details, s_articles_prices, s_order_basket,
				s_articles, s_core_tax
				WHERE s_order_basket.id=$id AND s_order_basket.sessionID='".$this->sSYSTEM->sSESSION_ID."'
				AND s_order_basket.ordernumber = s_articles_details.ordernumber
				AND s_articles_details.id=s_articles_prices.articledetailsID 
				AND s_articles_details.articleID = s_articles.id
				AND s_articles.taxID = s_core_tax.id
				AND s_articles_prices.pricegroup='".$this->sSYSTEM->sUSERGROUP."'
				$quantitySQL
				";

				//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

				if (!empty($queryAdditionalInfo["purchaseunit"]))
				{
					$queryAdditionalInfo["purchaseunit"] = 1;
				}

				//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

				eval($this->sSYSTEM->sCallHookPoint("sBasket.php_sUpdateArticle_Start3"));
				$queryNewPrice = $this->sSYSTEM->sDB_CONNECTION->GetRow($sql);

				/*
				SW 2.1 - Load prices from defaultgroup if no own prices defined
				*/
				if (!$queryNewPrice["price"]){
					// In the case no price is available for this customergroup, use price of default customergroup
					$sql = "SELECT s_articles_prices.price AS price,s_core_tax.tax AS tax,s_articles_details.id AS articleDetailsID, s_articles_details.articleID, s_order_basket.config, s_order_basket.ordernumber FROM s_articles_details, s_articles_prices, s_order_basket,
					s_articles, s_core_tax
					WHERE s_order_basket.id=$id AND s_order_basket.sessionID='".$this->sSYSTEM->sSESSION_ID."'
					AND s_order_basket.ordernumber = s_articles_details.ordernumber
					AND s_articles_details.id=s_articles_prices.articledetailsID 
					AND s_articles_details.articleID = s_articles.id
					AND s_articles.taxID = s_core_tax.id
					AND s_articles_prices.pricegroup='EK'
					$quantitySQL
					";

					eval($this->sSYSTEM->sCallHookPoint("sBasket.php_sUpdateArticle_Start4"));

					$queryNewPrice = $this->sSYSTEM->sDB_CONNECTION->GetRow($sql);
				}

				if (empty($queryNewPrice["price"])&&empty($queryNewPrice["config"])){

					// If no price is set for default customergroup, delete article from basket
					$this->sDeleteArticle($id);
					return false;
				}


				$netprice = $queryNewPrice["price"];

				/*
				Recalculate Price if purchaseunit is set
				*/

				$brutto = $this->sSYSTEM->sMODULES['sArticles']->sCalculatingPriceNum($netprice,$queryNewPrice["tax"],false,false,$queryNewPrice);
				// Recalculate netprice on base brutto
				//$netprice = $this->sSYSTEM->sMODULES['sArticles']->sRound($brutto / (100 + $queryNewPrice["tax"]) * 100);

				if(!empty($queryNewPrice["config"]))
				{
					$config = unserialize($queryNewPrice["config"]);
					$sql_where = array();
					if(!empty($config))
					foreach ($config as $value)
					{
						$sql_where[] = "(o.optionID=".intval($value["optionID"])." AND g.groupID=".intval($value["groupID"]).")";

					}
					if(!empty($sql_where))
					$sql_where = implode(" OR ",$sql_where);
					else
					$sql_where = "1!=1";
					$sql = "
						SELECT IFNULL(p.price, p2.price) as price, o.optionID
						FROM s_articles_groups g
						LEFT JOIN s_articles_groups_option o
						ON o.articleID=g.articleID
						AND o.groupID=g.groupID
						AND ($sql_where)
						
						LEFT JOIN s_articles_groups_prices p
						ON p.optionID = o.optionID
						AND p.groupkey='{$this->sSYSTEM->sUSERGROUP}'
						
						LEFT JOIN s_articles_groups_prices p2
						ON p2.optionID = o.optionID
						AND p2.groupkey='EK'
						
						WHERE g.articleID={$queryNewPrice["articleID"]} 
						GROUP BY g.groupID
					";
					eval($this->sSYSTEM->sCallHookPoint("sBasket.php_sUpdateArticle_Start5"));
					$sUpPriceValues = $this->sSYSTEM->sDB_CONNECTION->GetAll($sql);
					if(!empty($sUpPriceValues))
					{
						foreach ($sUpPriceValues as $value) {
							if(empty($value["optionID"]))
							{
								$this->sDeleteArticle($id);
								return false;
							}
							$netprice += $value["price"];
							$brutto += $this->sSYSTEM->sMODULES['sArticles']->sCalculatingPriceNum($value["price"],$queryNewPrice["tax"],false,false,$queryNewPrice);
						}
					}
				}



				/**
				 * LIVE-SHOPPING Normal & Variante - START
				 */				
				$queryNewPrice = $this->sSYSTEM->sMODULES["sArticles"]->sGetLiveShopping('fix', 0, $queryNewPrice, false, '', '', 0);

				if(isset($queryNewPrice['liveshoppingData']))
				{

					foreach ($queryNewPrice['liveshoppingData'] as $key => $liveshopping) {
						//Überprüfung, ob eine Artikelbeschränkung vorliegt
						if(!empty($liveshopping['sLiveStints'])) {
							if(!in_array($queryNewPrice['ordernumber'], $liveshopping['sLiveStints']) &&
							!in_array( strtolower($queryNewPrice['ordernumber']), $liveshopping['sLiveStints'])
							){
								unset($queryNewPrice['liveshoppingData'][$key]);
							}
						}
					}
				}

				if(!empty($queryNewPrice['liveshoppingData']))
				{
					foreach ($queryNewPrice['liveshoppingData'] as $live) {
						$netprice = $live['net_price'];
						$brutto = $live['price'];
						$sqlLive = sprintf("liveshoppingID = '%s', ", $live['id']);
						break;
					}
				}
				/**
				 * LIVE-SHOPPING Normal & Variante - END
				 */

				// Check if tax-free
				if (($this->sSYSTEM->sCONFIG['sARTICLESOUTPUTNETTO'] && !$this->sSYSTEM->sUSERGROUPDATA["tax"]) || (!$this->sSYSTEM->sUSERGROUPDATA["tax"] && $this->sSYSTEM->sUSERGROUPDATA["id"])){
					// Brutto is equal to net - price
					$netprice = $brutto;

				}else {
					// Consider global discount for net price
					$netprice =  $this->sSYSTEM->sMODULES['sArticles']->sCalculatingPriceNum($netprice,0,false,true,$queryNewPrice);
				}

				// Recalculate price per item, if purchase-unit is set
				if ($queryAdditionalInfo["purchaseunit"]>0){
					$brutto = $brutto / $queryAdditionalInfo["purchaseunit"];
					$netprice = $netprice / $queryAdditionalInfo["purchaseunit"];
				}
				if (empty($this->sSYSTEM->sCurrency["factor"])) $this->sSYSTEM->sCurrency["factor"] = 1;
				/*
				SW 2.1 Pricegroups
				*/


				//Preisgruppe (bei Liveshopping nicht berechnen)
				if ($queryAdditionalInfo["pricegroupActive"] && empty($queryNewPrice['liveshoppingData'])){

					$brutto = $this->sSYSTEM->sMODULES['sArticles']->sGetPricegroupDiscount($this->sSYSTEM->sUSERGROUP,$queryAdditionalInfo["pricegroupID"],$brutto,$quantity,false);
					$brutto = $this->sSYSTEM->sMODULES['sArticles']->sRound($brutto);
					if (($this->sSYSTEM->sCONFIG['sARTICLESOUTPUTNETTO'] && !$this->sSYSTEM->sUSERGROUPDATA["tax"]) || (!$this->sSYSTEM->sUSERGROUPDATA["tax"] && $this->sSYSTEM->sUSERGROUPDATA["id"])){
						$netprice = $this->sSYSTEM->sMODULES['sArticles']->sRound($this->sSYSTEM->sMODULES['sArticles']->sGetPricegroupDiscount($this->sSYSTEM->sUSERGROUP,$queryAdditionalInfo["pricegroupID"],$netprice,$quantity,false));

					}else {
						$netprice = $brutto / (100 + $queryNewPrice["tax"])* 100;//$this->sSYSTEM->sMODULES['sArticles']->sGetPricegroupDiscount($this->sSYSTEM->sUSERGROUP,$queryAdditionalInfo["pricegroupID"],$netprice,$quantity,false);
					}
				}


				//if($this->sSYSTEM->_GET["sViewport"] == 'basket' || empty($queryNewPrice['liveshoppingData'])){
				$sql = "
					UPDATE s_order_basket SET $sqlLive quantity=$quantity, price=$brutto, netprice=$netprice, currencyFactor=".$this->sSYSTEM->sCurrency["factor"]." WHERE id=$id AND
					sessionID='".$this->sSYSTEM->sSESSION_ID."' AND modus=0
					";
				$sql = Enlight()->Events()->filter('Shopware_Modules_Basket_UpdateArticle_FilterSqlDefault',$sql, array('subject'=>$this,'id'=>$id,"quantity"=>$quantity,"price"=>$brutto,"netprice"=>$netprice,"currencyFactor"=>$this->sSYSTEM->sCurrency["factor"]));
				eval($this->sSYSTEM->sCallHookPoint("sBasket.php_sUpdateArticle_Start6"));
				$update = $this->sSYSTEM->sDB_CONNECTION->Execute($sql);
				/*}else {
				$update = true;
				}*/

			}

		}else {
			// Configurator - Article
			$sql = "
			SELECT s_articles_groups_prices.price AS price, s_core_tax.tax AS tax FROM 
			s_articles_groups_value, s_articles, s_core_tax,s_articles_groups_prices, s_order_basket
			WHERE
			s_articles.taxID = s_core_tax.id AND s_articles.id=s_articles_groups_value.articleID AND
			s_articles_groups_value.ordernumber=s_order_basket.ordernumber
			AND s_articles_groups_value.articleID=s_articles_groups_prices.articleID
			AND s_articles_groups_value.valueID = s_articles_groups_prices.valueID
			AND s_articles_groups_prices.groupkey='".$this->sSYSTEM->sUSERGROUP."'
			AND s_order_basket.id=$id AND sessionID='".$this->sSYSTEM->sSESSION_ID."'
			";

			eval($this->sSYSTEM->sCallHookPoint("sBasket.php_sUpdateArticle_Start7"));
			$getPrice = $this->sSYSTEM->sDB_CONNECTION->GetRow($sql);

			if (!$getPrice["price"]){
				$sql = "
				SELECT s_articles_groups_prices.price AS price, s_core_tax.tax AS tax FROM 
				s_articles_groups_value, s_articles, s_core_tax,s_articles_groups_prices, s_order_basket
				WHERE
				s_articles.taxID = s_core_tax.id AND s_articles.id=s_articles_groups_value.articleID AND
				s_articles_groups_value.ordernumber=s_order_basket.ordernumber
				AND s_articles_groups_value.articleID=s_articles_groups_prices.articleID
				AND s_articles_groups_value.valueID = s_articles_groups_prices.valueID
				AND s_articles_groups_prices.groupkey='EK'
				AND s_order_basket.id=$id AND sessionID='".$this->sSYSTEM->sSESSION_ID."'
				";
				eval($this->sSYSTEM->sCallHookPoint("sBasket.php_sUpdateArticle_Start8"));
				$getPrice = $this->sSYSTEM->sDB_CONNECTION->GetRow($sql);

			}

			if (!$getPrice["price"]){
				// If no price is set for default customergroup, delete article from basket
				$this->sDeleteArticle($id);
				return false;
			}
			/*
			SW 2.1 Pricegroups
			*/

			if ($queryAdditionalInfo["pricegroupActive"]){
				$getPrice["price"] = $this->sSYSTEM->sMODULES['sArticles']->sGetPricegroupDiscount($this->sSYSTEM->sUSERGROUP,$queryAdditionalInfo["pricegroupID"],$getPrice["price"],$quantity,false);
			}

			if (($this->sSYSTEM->sCONFIG['sARTICLESOUTPUTNETTO'] && !$this->sSYSTEM->sUSERGROUPDATA["tax"]) || (!$this->sSYSTEM->sUSERGROUPDATA["tax"] && $this->sSYSTEM->sUSERGROUPDATA["id"])){
				// If netto set both values to net-price
				$brutto = $this->sSYSTEM->sMODULES['sArticles']->sCalculatingPriceNum($getPrice["price"],$getPrice["tax"]);
				$netprice = $brutto;

			}else {
				// If brutto, save net
				$netprice = $this->sSYSTEM->sMODULES['sArticles']->sCalculatingPriceNum($getPrice["price"],0,false,true);
				$brutto = $this->sSYSTEM->sMODULES['sArticles']->sCalculatingPriceNum($getPrice["price"],$getPrice["tax"]);
			}


			// Recalculate price per item, if purchase-unit is set
			if ($queryAdditionalInfo["purchaseunit"]>0){
				$brutto = $brutto / $queryAdditionalInfo["purchaseunit"];
				//echo "Before $netprice<br />";
				$netprice = $netprice / $queryAdditionalInfo["purchaseunit"];
				//echo "After $netprice<br />";
			}

			/**
			 * LIVE-SHOPPING - START
			 */			
			$tmpArticle = array('articleID'=>$queryAdditionalInfo['articleID'], 'ordernumber'=>$queryAdditionalInfo['ordernumber']);
			$liveConf = $this->sSYSTEM->sMODULES["sArticles"]->sGetLiveShopping('fix', 0, $tmpArticle, false, '', '', 0);

			if(isset($liveConf['liveshoppingData']))
			{

				foreach ($liveConf['liveshoppingData'] as $key => $liveshopping) {
					//Überprüfung, ob eine Artikelbeschränkung vorliegt
					if(!empty($liveshopping['sLiveStints'])) {
						// Fix #5526 - Thanks to holger
						if(!in_array($tmpArticle['ordernumber'], $liveshopping['sLiveStints'])
						&& !in_array( strtolower($tmpArticle['ordernumber']), $liveshopping['sLiveStints'])
						){
							unset($liveConf['liveshoppingData'][$key]);
						}
					}
				}
			}

			if(!empty($liveConf['liveshoppingData']))
			{
				foreach ($liveConf['liveshoppingData'] as $live) {
					$netprice = $live['net_price'];
					$brutto = $live['price'];
					$sqlLive = sprintf("liveshoppingID = '%s', ", $live['id']);
					break;
				}
			}
			/**
			 * LIVE-SHOPPING - END
			 */


			$queryNewPrice = true;
			if (empty($this->sSYSTEM->sCurrency["factor"])) $this->sSYSTEM->sCurrency["factor"] = 1;
			//if($this->sSYSTEM->_GET["sViewport"] == 'basket' || empty($queryNewPrice['liveshoppingData'])){
			$sql = "
				UPDATE s_order_basket SET $sqlLive quantity=$quantity,price=$brutto, netprice=$netprice, currencyFactor=".$this->sSYSTEM->sCurrency["factor"]." WHERE id=$id AND
				sessionID='".$this->sSYSTEM->sSESSION_ID."' AND modus=0
				";
			$sql = Enlight()->Events()->filter('Shopware_Modules_Basket_UpdateArticle_FilterSqlConfigurator',$sql, array('subject'=>$this,'id'=>$id,"quantity"=>$quantity,"price"=>$brutto,"netprice"=>$netprice,"currencyFactor"=>$this->sSYSTEM->sCurrency["factor"]));
			$update = $this->sSYSTEM->sDB_CONNECTION->Execute($sql);
			/*}else {
			$update = true;
			}*/

		}
		$this->sUpdateVoucher();

		if (!$update || !$queryNewPrice){
			$this->sSYSTEM->E_CORE_WARNING("Basket Update ##01","Could not update quantity".$sql);
		}
		return;
	}

	/**
	 * Prüfen ob ein Download-Artikel im Warenkorb liegt
	 * @access public
	 * @return void
	 */

	public function sCheckForESD(){
		$sql = "SELECT id FROM s_order_basket WHERE sessionID=? AND esdarticle=1 LIMIT 1
		";

		$getArticles = $this->sSYSTEM->sDB_CONNECTION->GetRow($sql,array($this->sSYSTEM->sSESSION_ID));

		if ($getArticles["id"]){
			return true;
		}else {
			return false;
		}

	}

	/**
	 * Den kompletten Warenkorb leeren
	 * @access public
	 * @return void
	 */
	public function sDeleteBasket (){
		if (empty($this->sSYSTEM->sSESSION_ID)) return false;
		$sql = "
		DELETE FROM s_order_basket WHERE sessionID=?";
		$delete = $this->sSYSTEM->sDB_CONNECTION->Execute($sql,array($this->sSYSTEM->sSESSION_ID));
	}


	/**
	 * Eine bestimmte Position des Warenkorbs löschen
	 * @param int $id s_order_basket.id
	 * @access public
	 * @return void
	 */
	public function sDeleteArticle ($id){
		$id = (int)$id;
		$modus = $this->sSYSTEM->sDB_CONNECTION->GetOne('SELECT `modus` FROM `s_order_basket` WHERE `id`=?', array($id));

		if ($id && $id != "voucher"){
			$sql = "
			DELETE FROM s_order_basket WHERE sessionID=? AND id=?
			";
			$delete = $this->sSYSTEM->sDB_CONNECTION->Execute($sql,array($this->sSYSTEM->sSESSION_ID,$id));
			if (!$delete){
				$this->sSYSTEM->E_CORE_WARNING("Basket Delete ##01","Could not delete item ($sql)");
			}
			if(empty($modus))
			{
				$this->sUpdateVoucher();
			}
			return;
		} else {
			return;
		}
	}

	/**
	 * Eine bestimmte Position des Merkzettels löschen
	 * @param int $id s_order_notes.id
	 * @access public
	 * @return void
	 */
	public function sDeleteNote ($id){
		$id = (int)$id;

		if (!empty($id)){
			$sql = "
			DELETE FROM s_order_notes WHERE (sUniqueID=? OR (userID = ?  AND userID != 0)) AND id=?
			";
			$delete = $this->sSYSTEM->sDB_CONNECTION->Execute($sql,array($this->sSYSTEM->_COOKIE["sUniqueID"],$this->sSYSTEM->_SESSION['sUserId'],$id));
			if (!$delete){
				$this->sSYSTEM->E_CORE_WARNING("Basket sDeleteNote ##01","Could not delete item ($sql)");
			}
			return true;
		} else {
			return false;
		}
	}
	/**
	 * Artikel in den Warenkorb einfügen
	 * @param int $id Bestellnummer (s_order_details.ordernumber)
	 * @param int $quantity Menge
	 * @access public
	 * @return void
	 */
	public function sAddArticle ($id, $quantity=1)
	{

		if ($this->sSYSTEM->sBotSession) return false;
		eval($this->sSYSTEM->sCallHookPoint("sBasket.php_sAddArticle_Start"));


		$quantity = (empty($quantity)||!is_numeric($quantity)) ? 1 : (int) $quantity;
		if ($quantity<=0) $quantity = 1;

		if (empty($this->sSYSTEM->sSESSION_ID)) return false;

		if (Enlight()->Events()->notifyUntil('Shopware_Modules_Basket_AddArticle_Start', array('subject'=>$this,'id'=>$id,"quantity"=>$quantity))){
			return false;
		}

		$sql = "
			SELECT s_articles.id AS articleID ,free, name AS articleName, taxID, additionaltext, shippingfree,laststock,instock, s_articles_details.id as articledetailsID, ordernumber  
			FROM s_articles, s_articles_details 
			WHERE s_articles_details.ordernumber=?
			AND s_articles_details.articleID=s_articles.id
			AND s_articles.active = 1
			AND (
				SELECT articleID 
				FROM s_articles_avoid_customergroups 
				WHERE articleID = s_articles.id AND customergroupID = ".$this->sSYSTEM->sUSERGROUPDATA["id"]."
			) IS NULL
		";
		$getArticle = $this->sSYSTEM->sDB_CONNECTION->GetRow($sql,array($id));

		$getName = $this->sSYSTEM->sMODULES["sArticles"]->sGetArticleNameByOrderNumber($getArticle["ordernumber"],true);

		if (!empty($getName)){
			$getArticle["articleName"] = $getName["articleName"];
			$getArticle["additionaltext"] = $getName["additionaltext"];

		}

		eval($this->sSYSTEM->sCallHookPoint("sBasket.php_sAddArticle_Start2"));
		if(!empty($getArticle))
		{
			// Checking Instock / Laststock

			$sql_where = array();
			if (!empty($_REQUEST["group"])){
				foreach ($_REQUEST["group"] as $groupID=>$optionID)
				{
					if(!empty($optionID))
					{
						$sql_where[] = "(o.optionID=".intval($optionID)." AND g.groupID=".intval($groupID).")";
					}
				}
			}
			if(!empty($sql_where))
			$sql_where = implode(" OR ",$sql_where);
			else
			$sql_where = "1!=1";
			$sql = "
				SELECT  p.price, o.optionID, g.groupID, g.groupname, o.optionname
				FROM s_articles_groups g
				LEFT JOIN s_articles_groups_option o
				ON o.articleID=g.articleID
				AND o.groupID=g.groupID
				AND ($sql_where)
				LEFT JOIN s_articles_groups_prices p
				ON p.optionID = o.optionID
				AND p.groupkey='{$this->sSYSTEM->sUSERGROUP}'
				WHERE g.articleID={$getArticle["articleID"]} 
				GROUP BY g.groupID
			";
			eval($this->sSYSTEM->sCallHookPoint("sBasket.php_sAddArticle_Start3"));
			$sUpPriceValues = $this->sSYSTEM->sDB_CONNECTION->GetAll($sql);
		}
		$pictureRelations = array();
		if(!empty($sUpPriceValues))
		{
			foreach ($sUpPriceValues as $value) {
				if(empty($value["optionID"]))
				return false;

				$groupTranslation = $this->sSYSTEM->sMODULES["sArticles"]->sGetArticleConfigTranslation($getArticle["articleID"]);
				$pictureGroup = $value["groupname"];
				if (!empty($groupTranslation["groups"][$value["groupID"]]["gruppenName"])){
					$value["groupname"] = $groupTranslation["groups"][$value["groupID"]]["gruppenName"];
				}
				$pictureRelations[] = strtolower(str_replace(" ","",$pictureGroup.":".$value["optionname"]));
				if (!empty($groupTranslation["options"][$value["optionID"]]["optionName"])){
					$value["optionname"] = $groupTranslation["options"][$value["optionID"]]["optionName"];
				}

				$getArticle["additionaltext"] .= "<br />{$value["groupname"]}: {$value["optionname"]}";
			}
		}

		// Check for article-configurator

		if (!count($getArticle)){
			$sql = "
			SELECT s_articles.id AS articleID , name AS articleName, taxID,laststock,instock, shippingfree, valueID as subID, ordernumber  FROM s_articles,
			s_articles_groups_value 
			WHERE s_articles_groups_value.ordernumber=?
			AND s_articles_groups_value.articleID=s_articles.id
			AND (
				SELECT articleID 
				FROM s_articles_avoid_customergroups 
				WHERE articleID = s_articles.id AND customergroupID = ".$this->sSYSTEM->sUSERGROUPDATA["id"]."
			) IS NULL
			";

			$article = $this->sSYSTEM->sDB_CONNECTION->GetRow ($sql,array($id));
			$groupTranslation = $this->sSYSTEM->sMODULES["sArticles"]->sGetArticleConfigTranslation($article["articleID"]);
			//die(print_r($this->sSYSTEM->sMODULES["sArticles"]->sGetArticleConfigTranslation($article["articleID"])));
			$getName = $this->sSYSTEM->sMODULES["sArticles"]->sGetArticleNameByOrderNumber($article["ordernumber"]);
			if (!empty($getName)){
				$article["articleName"] = $getName;
			}
			$sConfigurator = true;
			$sConfiguratorOrderNumber = $id;

			// Additional-Text
			$sqlZusatzText = $this->sSYSTEM->sDB_CONNECTION->GetRow("
			SELECT * FROM s_articles_groups_value
			WHERE ordernumber=?
			",array($id));

			for ($i=1;$i<=20;$i++)
			{
				if ($sqlZusatzText["attr$i"])
				{
					// Group-Name
					$sql = "
					SELECT groupname FROM s_articles_groups WHERE articleID=?
					AND groupID=?
					";
					$sqlFetchGroupName = $this->sSYSTEM->sDB_CONNECTION->GetRow($sql,array($article["articleID"],$i));

					if (!count($sqlFetchGroupName)){
						return false;
					}else {
						$pictureGroup = $sqlFetchGroupName["groupname"];
						if (!empty($groupTranslation["groups"][$i]["gruppenName"])){
							$sqlFetchGroupName["groupname"] = $groupTranslation["groups"][$i]["gruppenName"];
						}
						$tmpText = "{$sqlFetchGroupName["groupname"]}: ";
						$sqlFetchOptionName = $this->sSYSTEM->sDB_CONNECTION->GetRow("
						SELECT optionname FROM s_articles_groups_option WHERE
						articleID=? AND optionID=?
						",array($article["articleID"],$sqlZusatzText["attr$i"]));

						if (!count($sqlFetchOptionName)){
							$this->sSYSTEM->E_CORE_WARNING ("BASKET-INSERT CONFIGURATOR #01","OptionName not found".$sql);
						}else {
							$pictureRelations[] = strtolower(str_replace(" ","",$pictureGroup.":".$sqlFetchOptionName["optionname"]));
							if (!empty($groupTranslation["options"][$sqlZusatzText["attr$i"]]["optionName"])){
								$sqlFetchOptionName["optionname"] = $groupTranslation["options"][$sqlZusatzText["attr$i"]]["optionName"];
							}
							$tmpText .= $sqlFetchOptionName["optionname"];


							$article["additionaltext"] .= "<br />".$tmpText;
						} // Could not query option-value
					} // Could not query group-name

				} // If option is set
			} // For every option

			$getArticle = $article;
		}
		// -- article - configurator
		//die(print_r($getArticle));

		if (!count($getArticle)){

			//$this->sSYSTEM->E_CORE_WARNING ("BASKET-INSERT #00","Article $id not found");
			//unset($this->sSYSTEM->_GET["sAdd"]);
			return false;
		}else {

			// Check if article is already in basket
			$sql = "
			SELECT id, quantity FROM s_order_basket WHERE articleID=? AND sessionID=? AND
			ordernumber=?";
			eval($this->sSYSTEM->sCallHookPoint("sBasket.php_sAddArticle_CheckIfArticleIsInBasket"));
			$chkBasketForArticle = $this->sSYSTEM->sDB_CONNECTION->GetRow($sql,array($getArticle["articleID"],$this->sSYSTEM->sSESSION_ID,$getArticle["ordernumber"]));

			// Shopware 3.5.0 / sth / laststock - instock check
			if (!empty($chkBasketForArticle["id"])){

				if ($getArticle["laststock"] == true && $getArticle["instock"] < ($chkBasketForArticle["quantity"] + $quantity) ){
					$quantity -= $chkBasketForArticle["quantity"];

				}
			}else {
				if ($getArticle["laststock"] == true && $getArticle["instock"] <= $quantity){
					$quantity = $getArticle["instock"];
					if ($quantity <= 0){
						return;
					}
				}
			}
			// --


			$insertTime = date("Y-m-d H:i:s");

			if ($chkBasketForArticle&&empty($sUpPriceValues)){

				// Article is already in basket, update quantity
				$quantity += $chkBasketForArticle["quantity"];

				$this->sUpdateArticle($chkBasketForArticle["id"],$quantity);
				return $chkBasketForArticle["id"];
			}else {
				// Get prices
				if ($sConfigurator){
					// Read price from configurator
					$sql = "
						SELECT s_articles_groups_prices.price AS price, s_core_tax.tax AS tax FROM 
						s_articles_groups_value, s_articles, s_core_tax,s_articles_groups_prices
						WHERE
						s_articles.taxID = s_core_tax.id AND s_articles.id=s_articles_groups_value.articleID AND
						ordernumber=?
						AND s_articles_groups_value.articleID=s_articles_groups_prices.articleID
						AND s_articles_groups_value.valueID = s_articles_groups_prices.valueID
						AND s_articles_groups_prices.groupkey=?
						";

					$getPrice = $this->sSYSTEM->sDB_CONNECTION->GetRow($sql,array($sConfiguratorOrderNumber,$this->sSYSTEM->sUSERGROUP));

					if (empty($getPrice["price"])){
						$sql = "
							SELECT s_articles_groups_prices.price AS price, s_core_tax.tax AS tax FROM 
							s_articles_groups_value, s_articles, s_core_tax,s_articles_groups_prices
							WHERE
							s_articles.taxID = s_core_tax.id AND s_articles.id=s_articles_groups_value.articleID AND
							ordernumber=?
							AND s_articles_groups_value.articleID=s_articles_groups_prices.articleID
							AND s_articles_groups_value.valueID = s_articles_groups_prices.valueID
							AND s_articles_groups_prices.groupkey='EK'
							";
						$getPrice = $this->sSYSTEM->sDB_CONNECTION->GetRow($sql,array($sConfiguratorOrderNumber));
					}

				}else {
					// Read price from default-price-table
					$sql = "SELECT price,s_core_tax.tax AS tax FROM s_articles_prices,s_core_tax WHERE
					s_articles_prices.pricegroup=?
					AND s_articles_prices.articledetailsID=?
					AND s_core_tax.id=?
					";
					$getPrice = $this->sSYSTEM->sDB_CONNECTION->GetRow($sql,array($this->sSYSTEM->sUSERGROUP,$getArticle["articledetailsID"],$getArticle["taxID"]));
					if (empty($getPrice["price"])){
						$sql = "SELECT price,s_core_tax.tax AS tax FROM s_articles_prices,s_core_tax WHERE
						s_articles_prices.pricegroup='EK'
						AND s_articles_prices.articledetailsID=?
						AND s_core_tax.id=?
						";	
						$getPrice = $this->sSYSTEM->sDB_CONNECTION->GetRow($sql,array($getArticle["articledetailsID"],$getArticle["taxID"]));
					}

				}


				if (!$getPrice["price"] && !$getArticle["free"]){
					// No price could acquired
					$this->sSYSTEM->E_CORE_WARNING ("BASKET-INSERT #01","No price acquired");
					return;
				}else {

					// Price was found
					if (!$sConfigurator){

						// No configuration article
						$getPrice["netprice"] = $getPrice["price"];

						$getPrice["price"] = $this->sSYSTEM->sMODULES['sArticles']->sCalculatingPriceNum($getPrice["price"],$getPrice["tax"],false,false,$getArticle);
						if(!empty($sUpPriceValues))
						{
							foreach ($sUpPriceValues as $value)
							{
								$getPrice["netprice"] += $value["price"];
								$getPrice["price"] += $this->sSYSTEM->sMODULES['sArticles']->sCalculatingPriceNum($value["price"],$getPrice["tax"],false,false,$getArticle);
							}
						}

					}else {
						// If configuration article
						if (($this->sSYSTEM->sCONFIG['sARTICLESOUTPUTNETTO'] && !$this->sSYSTEM->sUSERGROUPDATA["tax"]) || (!$this->sSYSTEM->sUSERGROUPDATA["tax"] && $this->sSYSTEM->sUSERGROUPDATA["id"])){
							// If netto set both values to net-price
							$getPrice["price"] = $this->sSYSTEM->sMODULES['sArticles']->sCalculatingPriceNum($getPrice["price"],$getPrice["tax"],false,false,$getArticle);
							$getPrice["netprice"] = $getPrice["price"];
						}else {
							// If brutto, save net
							$getPrice["netprice"] = $getPrice["price"];
							$getPrice["price"] = $this->sSYSTEM->sMODULES['sArticles']->sCalculatingPriceNum($getPrice["price"],$getPrice["tax"],false,false,$getArticle);
						}
					}
					// For variants, extend the article-name
					if ($getArticle["additionaltext"]){
						$getArticle["articleName"].= " ".$getArticle["additionaltext"];
					}

					if (!$getArticle["shippingfree"]) $getArticle["shippingfree"] = "0";

					// Check if article is an esd-article
					// - add flag to basket
					$sqlGetEsd = "
					SELECT s_articles_esd.id AS id, serials FROM s_articles_esd,s_articles_details WHERE s_articles_esd.articleID=?
					AND s_articles_esd.articledetailsID=s_articles_details.id AND s_articles_details.ordernumber=?
					";
					$getEsd = $this->sSYSTEM->sDB_CONNECTION->GetRow($sqlGetEsd,array($getArticle["articleID"],$getArticle["ordernumber"]));
					if ($getEsd["id"]){
						$sEsd = "1";
					}else {
						$sEsd = "0";
					}
					// ----
					$getArticle["articleName"] = $this->sSYSTEM->sDB_CONNECTION->qstr($getArticle["articleName"]);

					$quantity = (int) $quantity;

					eval($this->sSYSTEM->sCallHookPoint("sBasket.php_sAddArticle_Start4"));
					$sql = "
						INSERT INTO s_order_basket (id,sessionID,userID,articlename,articleID,
						ordernumber, shippingfree, quantity, price, netprice, datum, esdarticle, partnerID, config, ob_attr1)
						VALUES (
						'',
						'".$this->sSYSTEM->sSESSION_ID."',
						'".$this->sSYSTEM->_SESSION['sUserId']."',
						{$getArticle["articleName"]},
						{$getArticle["articleID"]},
						'{$getArticle["ordernumber"]}',
						{$getArticle["shippingfree"]},
						$quantity,
						{$getPrice["price"]},
						{$getPrice["netprice"]},
						'$insertTime',
						$sEsd,
						'".$this->sSYSTEM->_SESSION["sPartner"]."',
						".(empty($sUpPriceValues) ? "''" : $this->sSYSTEM->sDB_CONNECTION->qstr(serialize($sUpPriceValues))).",
						".$this->sSYSTEM->sDB_CONNECTION->qstr(implode($pictureRelations,"$$"))."
						)
					";


					eval($this->sSYSTEM->sCallHookPoint("sBasket.php_sAddArticle_Start5"));
					$sql = Enlight()->Events()->filter('Shopware_Modules_Basket_AddArticle_FilterSql',$sql, array('subject'=>$this,"article"=>$getArticle,"price"=>$getPrice,"esd"=>$sEsd,"quantity"=>$quantity,"partner"=>$this->sSYSTEM->_SESSION["sPartner"]));

					$rs = $this->sSYSTEM->sDB_CONNECTION->Execute($sql);
					if (!$rs){
						$this->sSYSTEM->E_CORE_WARNING ("BASKET-INSERT #02","SQL-Error".$sql);
					}

					$insertId = $this->sSYSTEM->sDB_CONNECTION->Insert_ID();

					$this->sUpdateArticle($insertId,$quantity);

				} // If - Price was found
			} // If - Article is not in basket
		} // If - Article was found

		return $insertId;


	}

	/**
	* Fügt einen Bundleartikel zum Warenkorb hinzu
	* $ordernumber = Bestellnummer des Hauptartikels
	* $bundleID = s_articles_bundle.id
	**/
	public function sAddBundleArticle($ordernumber, $bundleID, $insertBundleArticles=true){
		return $this->sSYSTEM->sMODULES["sBundle"]->sAddBundleArticle($ordernumber, $bundleID, $insertBundleArticles);
	}

	/**
	* Überprüft, ob sich im Warenkorb Bundleartikel befinden
	* und diese vollständig sind
	**/
	public function sCheckBasketBundles()
	{
		return $this->sSYSTEM->sMODULES["sBundle"]->sCheckBasketBundles();
	}
}
?>