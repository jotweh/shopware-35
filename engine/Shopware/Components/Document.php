<?php
/**
 * Shopware document generator
 * @link http://www.shopware.de
 * @package Components
 * @subpackage Document
 * @copyright (C) Shopware AG 2002-2010
 * @version Shopware 3.5.0
 */
class Shopware_Components_Document extends Enlight_Class implements Enlight_Hook
{
	/**
     * Object from Type Model\Order
     *
     * @var object Model\Order
     */
	public $_order;
	/**
     * Shopware Template Object (Smarty)
     *
     * @var object
     */
	public $_template;
	/**
     * Shopware View Object (Smarty)
     *
     * @var object
     */
	public $_view;
	/**
     * Configuration
     * @var array
     */
	public $_config;
	/**
     * compatibilityMode = true means that html2ps will be used instead of mpdf. 
     * Additionally old templatebase will be used (For pre 3.5 versions)
     *
     * @var bool
     */
	protected $_compatibilityMode = false;
	/**
	 * Define output 
     *
     * @var html,pdf,return
     */
	protected $_renderer = "html";
	/**
	 * Are properties already assigned to smarty? 
     *
     * @var bool
     */
	protected $_valuesAssigend = false;
	/**
	 * Subshop-Configuration
     *
     * @var array
     */
	public $_subshop;
	/**
	 * Path to load templates from
     *
     * @var string
     */
	public $_defaultPath = "templates/_default";
	/**
	 * Generate preview only
     *
     * @var bool
     */
	protected $_preview = false;
	/**
	 * Typ/ID of document [0,1,2,3] - s_core_documents
     *
     * @var int
     */
	protected $_typID;
	/**
	 * Document-Metadata / Properties
     *
     * @var array
     */
	protected $_document;
	/**
	 * Invoice / Document number
     *
     * @var int
     */
	protected $_documentID;
	/**
	 * Primary key of the created document row (s_order_documents) 
     *
     * @var int
     */
	protected $_documentRowID;
	/**
	 * Hash of the created document row (s_order_documents.hash), will be used as filename when preview is false 
     *
     * @var string
     */
	protected $_documentHash;
	/**
	 * Invoice ID for reference in shipping documents etc. 
     *
     * @var string
     */
	protected $_documentBid;
	
	
	/**
	 * Static function to initiate document class
	 * @param int $orderID s_order.id 
	 * @param int $documentID s_core_documents.id
	 * @param array $config - configuration array, for possible values see backend\document controller
	 * @access public
	 */
	public static function initDocument($orderID, $documentID, array $config = array()) {
		if (empty($orderID)){
			$config["_preview"] = true;
		}

		$d = Enlight_Class::Instance('Shopware_Components_Document');//new Shopware_Components_Document();
		
		//$d->setOrder(new Shopware_Models_Order($orderID,$config));
		$d->setOrder(Enlight_Class::Instance('Shopware_Models_Order', array($orderID,$config)));
		
		
		$d->setConfig($config);
		
		$d->setDocumentId($documentID);
		
		if (!empty($orderID)){
			$d->_subshop = Shopware()->Db()->fetchRow("
			SELECT s_core_multilanguage.id,doc_template, template, isocode,locale FROM s_order,s_core_multilanguage WHERE s_order.subshopID = s_core_multilanguage.id AND s_order.id = ?
			",array($orderID));
			
			if (empty($d->_subshop["doc_template"])) $d->setTemplate($d->_defaultPath);
			
			if (empty($d->_subshop["id"])){
				throw new Enlight_Exception("Could not load template path for order $orderID");
			}
			// Check if old (pre 3.5.0) templatebase is used, then switch to compatibility mode
			if (isset($config["_compatibilityMode"]) && $config["_compatibilityMode"]==true){
				// Force compatiblity mode
				$d->_compatibilityMode = true;
			}else {
				$path = explode("/",$d->_subshop["doc_template"]);
				if (preg_match('/^[0-9]+$/',$path[0])){
					$d->_compatibilityMode = true;
				}else {
					$d->_compatibilityMode = false;
				}
			}
		}else {
			if (!empty($config["_compatibilityMode"]) && $config["_compatibilityMode"]==true){
				// Force compatiblity mode
				//die( $config["_compatibilityMode"]."#");
				$d->_compatibilityMode = true;
			}
			$d->_subshop = Shopware()->Db()->fetchRow("
			SELECT s_core_multilanguage.id,doc_template, template, isocode,locale FROM s_core_multilanguage WHERE s_core_multilanguage.default = 1
			");
			if ($d->_compatibilityMode != true){
				$d->setTemplate($d->_defaultPath);
				$d->_subshop["doc_template"] = $d->_defaultPath;
				
			}else {
				$d->setTemplate("0/de/forms");
				$d->_subshop["doc_template"] = "0/de/forms";
			}
			
		}
		
		$d->initTemplateEngine();
		
		return $d;
	}
	
	/**
	 * Start renderer / pdf-generation
	 * @param string optional define renderer (pdf,html,return)
	 * @access public
	 */
	public function render($_renderer = ""){
		if (!empty($_renderer)) $this->_renderer = $_renderer;
		if ($this->_valuesAssigend == false){
			$this->assignValues();
		}
		
		// In compatibility mode load old template (0/de/forms/documents)
		if ($this->_compatibilityMode == true){
			//var_dump($this->_view);
			$data = $this->_template->fetch("base.tpl",$this->_view);
			$data = preg_replace('#(<script[^>]*?>.*?</script>)|(<style[^>]*?>.*?</style>)|<!--[^\[].*?-->#msi' ,'$1$2', $data);
			$data = str_replace("-->","",$data);
				
		}else {
			
			$data = $this->_template->fetch("documents/".$this->_document["template"],$this->_view);
		}
		
		if ($this->_renderer == "html" || !$this->_renderer){
			echo $data;
		}elseif ($this->_renderer == "pdf"){
			
			if ($this->_compatibilityMode == false){
				if ($this->_preview == true || !$this->_documentHash){
					include_once(Shopware()->OldPath()."engine/Enlight/Vendor/mpdf/mpdf.php");
					$mpdf = new mPDF("win-1252","A4","","",$this->_document["left"],$this->_document["right"],$this->_document["top"],$this->_document["bottom"]); 
					$mpdf->WriteHTML(utf8_encode($data));
					$mpdf->Output();
					exit;
				}else {
					$path = Shopware()->OldPath()."files/documents"."/".$this->_documentHash.".pdf";
					include_once(Shopware()->OldPath()."engine/Enlight/Vendor/mpdf/mpdf.php");
					$mpdf = new mPDF("win-1252","A4","","",$this->_document["left"],$this->_document["right"],$this->_document["top"],$this->_document["bottom"]); 
					$mpdf->WriteHTML(utf8_encode($data));
					$mpdf->Output($path,"F");
				}
				
			}else {
				// Deprecated
				include(Shopware()->OldPath()."engine/backend/php/pdf.php");
				$pdf = new Shopware_Deprecated_Html2PS();
				if ($this->_preview == true){
					$path = Shopware()->OldPath()."files/documents"."/test.pdf";
					$pdf->convert(array("base"=>$data), $path);
					header('Content-type: application/pdf');
					header("Cache-Control: public");
					header("Content-Description: File Transfer");
					header('Content-Disposition: attachment; filename="preview.pdf"');
					header('Content-Length: '. filesize($path));
					readfile($path);
				}else {
					$path = Shopware()->OldPath()."files/documents"."/".$this->_documentRowID.".pdf";
					$pdf->convert(array("base"=>$data), $path);
				}
			}
		}else {
			
		}
		
	}
	
	/**
	 * Assign configuration / data to template
	 * @access public
	 */
	public function assignValues(){
		if ($this->_compatibilityMode==false){
			$this->loadConfiguration4x();
		}
		if (!$this->_preview){
			$this->saveDocument();
		}
		if ($this->_compatibilityMode==true){
			return $this->assignValues3x();
		}else {
			return $this->assignValues4x();
		}
	}
	
	/**
	 * Assign configuration / data to template, new templatebase
	 * @access protected
	 */
	protected function assignValues4x(){
		
		if ($this->_preview == true){
			$id = 12345;
		}else {
			$id = $this->_documentID;
		}
		
		$Document = $this->_document->getArrayCopy();
		if (empty($this->_config["date"])){
			$this->_config["date"] = date("d.m.Y");
		}
		$Document = array_merge($Document,array("comment"=>$this->_config["docComment"],"id"=>$id,"bid"=>$this->_documentBid,"date"=>$this->_config["date"],"deliveryDate"=>$this->_config["delivery_date"],"netto"=>$this->_order->order->taxfree ? true : $this->_config["netto"],"nettoPositions"=>$this->_order->order->net));
		$Document["voucher"] = $this->getVoucher($this->_config["voucher"]);
		$this->_view->assign('Document',$Document);
		
		$this->_view->assign('Order',$this->_order->__toArray());
		$this->_view->assign('Containers',$this->_document->containers->getArrayCopy());
		
		$order = clone $this->_order;
		
		$positions = $order->positions->getArrayCopy();
		
		if ($this->_config["_previewForcePagebreak"]){
			$positions = array_merge($positions,$positions);
			$positions = array_merge($positions,$positions);
			$positions = array_merge($positions,$positions);
		}
					
		$positions = array_chunk($positions,$this->_document["pagebreak"],true);
		$this->_view->assign('Pages',$positions);
		
		$user = array(
			"shipping"=>$order->shipping,
			"billing"=>$order->billing,
			"additional"=>array("countryShipping"=>$order->shipping->country,"country"=>$order->billing->country)
		);
		$this->_view->assign('User',$user);
		
		
	}
	
	/**
	 * Assign configuration / data / in compatiblity mode - deprecated
	 * @access protected
	 */
	protected function assignValues3x(){
		$style = $this->loadConfiguration3x();
		$this->_view->assign('style',$style);
		
		$order = clone $this->_order;
		$tax = $order->tax;

		$this->_view->assign('sConfig', Shopware()->Config());
		
		$positions = array_combine(range(1, count($order->positions),1), $order->positions->getArrayCopy());
		
		$currency = new Zend_Currency("de_DE",$this->_order->currency->currency);
		
		$currency->setFormat(array("display"=>Zend_Currency::NO_SYMBOL));
		
		foreach ($tax as $key => &$value){
			$value = $currency->toCurrency($value);
		}
		foreach ($positions as $key => &$value){
			$value["price"] = $currency->toCurrency($value["price"]);
			$value["netto"] = $currency->toCurrency($value["netto"]);
			$value["amount_netto"] = $currency->toCurrency($value["amount_netto"]);
			$value["amount"] = $currency->toCurrency($value["amount"]);
		}
		
		if(is_numeric($style['content_middle']['number'])){
			$step = $style['content_middle']['number'];
		}
		else {
			$step = 10;
		}
		if ($this->_config["_previewForcePagebreak"]){
			$positions = array_merge_recursive($positions,$positions);
			$positions = array_merge_recursive($positions,$positions);
			$positions = array_merge_recursive($positions,$positions);
		}
		$i = 1;
		$b = 0;
		
		foreach ($positions as $key => $value2)
		{
			$b++;
			if($i*$step < $b)
			{
				$i++;
			}
			$ret[$i][$b] = $value2;	
		}

		$getOrders[0]['pages'] = count($getOrders[0]['details']);
		
		$this->_view->assign('sBillingData',
		array(
			"details"=>$ret,
			"pages"=>count($ret),
			"payment"=>htmlentities($order->payment->description),
			"netto2"=>$this->_config["netto"],
			"netto"=>$order->order->net,
			"invoice_amount"=>$currency->toCurrency($order->amount),
			"invoice_amount_netto"=>$currency->toCurrency($order->amountNetto),
			"tax"=>$tax,
			"ordernumber"=>$order->order->ordernumber,
			"comment"=>$order->order->comment
		));
		
		$user = array(
			"shippingaddress"=>$order->shipping,
			"billingaddress"=>$order->billing,
			"additional"=>array("countryShipping"=>$order->shipping->country,"country"=>$order->billing->country)
		);

		$this->_view->assign('sUserData',$user);
		$this->_view->assign('sDispatch',$order->dispatch);
		
		if ($this->_preview == true){
			$this->_view->assign('ID',12345);
			$settings = array("bid"=>"12345","date"=>date("Y-m-d"),"delivery_date"=>date("Y-m-d"));
		}else {
			$this->_view->assign('ID',$this->_documentID);
			$settings = array("bid"=>$this->_documentID,"date"=>$this->_config["date"],"delivery_date"=>$this->_config["delivery_date"]);
		}
		
		$this->_view->assign('sSettings',$settings);
		$this->_view->assign('sVoucher',$this->getVoucher($this->_config["voucher"]));
		
		$this->_view->assign("sCurrency",$order->currency->char);
		$this->_view->assign("sCurrencyFactor",$order->currency->factor);
		
		$this->_template->left_delimiter = "<!--{";
		$this->_template->right_delimiter = "}-->";
		$this->_view->assign('css',$this->_template->fetch("css/base.css",$this->_view));
		$this->_template->left_delimiter = "{";
		$this->_template->right_delimiter = "}";
		// Typ: 0 = RG / 1 = LS / 2 = GS / 3 = Storno
		
		$this->_view->assign('typ',$this->_typID);
	}
	
	/**
	 * Load old document configuration (deprecated)
	 * @access protected
	 */
	protected function loadConfiguration3x(){
		$result = Shopware()->Db()->fetchAll("SELECT * FROM `s_billing_template` WHERE `show` =1");
		$style = array();
		if(!empty($this->_subshop['isocode']))
		{
			//mapping
			$trans_mapping = array(
				"margin" => 1,	
				"header" => 2,	
				"footer" => 3,	
				"headline" => 4,	
				"sender" => 5,	
				"content_middle" => 6,	
			);
			
			//Replace Translations
			$new_result = array();
			foreach ($result as $ele) {
				$trans_key = $trans_mapping[$ele['group']];
				$trans = Shopware()->System()->sGetTranslation(array(), $trans_key, "config_documents", $this->_subshop['isocode']);
				
				//mask ids
				$mask_trans = array();
				foreach ($trans as $tkey => $tval) {
					$tkey = preg_replace("/[^a-z]/", "", $tkey);
					$mask_trans[$tkey] = $tval;
				}
					
				//check for rewrite
				if(!empty($mask_trans[$ele['name']]))
				{
					$ele['value'] = $mask_trans[$ele['name']];
				}
				$new_result[] = $ele;
			}
			//Array übergeben
			$result = $new_result;	
		}

		foreach ($result as $value)
		{
			$style[$value['group']][$value['name']] = $value['value'];
		}
		return $style;
	}
	
	/**
	 * Load template / document configuration (s_core_documents / s_core_documents_box)
	 * @access public
	 */
	protected function loadConfiguration4x(){
		$id = $this->_typID;
		
		$this->_document = new ArrayObject(Shopware()->Db()->fetchRow("
		SELECT * FROM s_core_documents WHERE id = ?
		",array($id+1),ArrayObject::ARRAY_AS_PROPS));
		
		
		// Load Containers
		$this->_document->containers = new ArrayObject(Shopware()->Db()->fetchAll("
		SELECT * FROM s_core_documents_box WHERE documentID = ?
		",array($id+1),ArrayObject::ARRAY_AS_PROPS));
		
		$translation = Shopware()->System()->sGetTranslation(array(), 1, "documents", $this->_subshop['isocode']);//$this->_subshop['isocode']
		
		foreach ($this->_document->containers as $key => $container){
			if (!is_numeric($key)) continue;
			if (!empty($translation[$id][$container["name"]."_Value"])){
				$this->_document->containers[$key]["value"] = $translation[$id][$container["name"]."_Value"];
			}
			if (!empty($translation[$id][$container["name"]."_Style"])){
				$this->_document->containers[$key]["style"] = $translation[$id][$container["name"]."_Style"];
			}
			$this->_document->containers[$container["name"]] = $this->_document->containers[$key];
			unset($this->_document->containers[$key]);
		}
		
		
	}
	
	/**
	 * Set template path
	 * @access public
	 */
	public function setTemplate($path){
		if (!empty($path)){
			$this->_subshop["doc_template"] = $path;
		}
	}
	
	/**
	 * Set renderer
	 * @access public
	 */
	public function setRenderer($renderer){
		$this->_renderer = $renderer;
	}
	
	/**
	 * Set type of document (0,1,2,3) > s_core_documents
	 * @access public
	 */
	public function setDocumentId($id){
		$this->_typID = $id;
	}
	
	/**
	 * Get voucher (s_vouchers.id)
	 * @access public
	 */
	public function getVoucher($id){
		if (empty($id)) return false;
		$getVoucher = array();
		// Check if voucher is available
		$sqlVoucher = "SELECT s_emarketing_voucher_codes.id AS id, code, description, value, percental FROM s_emarketing_vouchers, s_emarketing_voucher_codes
		 WHERE  modus = 1 AND (valid_to >= now() OR valid_to=0)
		 AND s_emarketing_voucher_codes.voucherID = s_emarketing_vouchers.id
		 AND s_emarketing_voucher_codes.userID = 0
		 AND s_emarketing_voucher_codes.cashed = 0
		 AND s_emarketing_vouchers.id=?
		 GROUP BY s_emarketing_voucher_codes.voucherID
		 ";
		
		$getVoucher = Shopware()->Db()->fetchRow($sqlVoucher,array($id));
		if ($getVoucher["id"]){
			// Update Voucher and pass-information to template
			$updateVoucher = Shopware()->Db()->query("
			UPDATE s_emarketing_voucher_codes
			SET
				userID = ?
			WHERE
				id = ?
			",array($this->_order->userID,$getVoucher["id"]));
			if ($this->_order->currency->factor!=1){
				$getVoucher["value"]*=$this->_order->currency->factor;
			}
			$getVoucher["value"] = $getVoucher["value"];
			if (!empty($getVoucher["percental"])){
				$getVoucher["prefix"] = "%";
			}else {
				$getVoucher["prefix"] = $this->_order->currency->char;
			}
		}

		return $getVoucher;
	}
	
	/**
	 * Initiate smarty template engine
	 * @access protected
	 */
	protected function initTemplateEngine(){
		$this->_template = clone Shopware()->Template();	
		$this->_view = $this->_template->createData();
		
		
		
		if ($this->_compatibilityMode == true){
			$path = "templates/".$this->_subshop["doc_template"];
			$path .= '/documents/';
			$this->_template->setTemplateDir(Shopware()->OldPath().$path);
		}else {
			$path = $this->_subshop["doc_template"];
			$this->_template->setTemplateDir(array(
				Shopware()->DocPath().$path,
				Shopware()->DocPath().'templates/_local/',
				Shopware()->DocPath().'templates/_default/'
			));
		}
		
		$this->_template->setCompileId(str_replace('/', '_', $path).'_'.$this->_subshop['id']);
		
		if (!is_dir($path)){
			throw new Enlight_Exception("Path ".Shopware()->OldPath().$path." not found");
		}		
	}
	
	/**
	 * Set order
	 * @access protected
	 */
	protected function setOrder(Shopware_Models_Order $order){
		$this->_order = $order;
		$shop = new Shopware_Models_Shop($this->_order->order->subshopID);
		$shop->setCurrency($this->_order->order->currencyID);
		$shop->setCache();
		$shop->registerResources(Shopware()->Bootstrap());
	}
	
	/**
	 * Set object configuration from array
	 * @access protected
	 */
	protected function setConfig (array $config){
		$this->_config = $config;
		foreach ($config as $key => $v){
			if (property_exists($this,$key)){
				$this->$key = $v;
			}
		}
	}
	
	/**
	 * Save document in database / generate number
	 * @access protected
	 */
	protected function saveDocument(){	
		if ($this->_preview==true) return;
		
		$bid = $this->_config["bid"];
		if (!empty($bid)){
			$this->_documentBid = $bid;
		}
		if (empty($bid)) $bid = 0;
		
		// Check if this kind of document already exists
		$typID = $this->_typID;
		
		$checkForExistingDocument = Shopware()->Db()->fetchRow("
		SELECT ID,docID,hash FROM s_order_documents WHERE userID = ? AND orderID = ? AND type = ?
		",array($this->_order->userID,$this->_order->id,$typID));
		
		if (!empty($checkForExistingDocument["ID"])){
			// Document already exist. Update date and amount!
			$update = "
			UPDATE `s_order_documents` SET `date` = now(),`amount` = ?
			WHERE `type` = ? AND userID = ? AND orderID = ? LIMIT 1
			";
			$amount = $this->_config["netto"] == true ? round($this->_order->amountNetto,2) : round($this->_order->amount,2);
			if ($typID == 3){
				$amount *= -1;
			}
			$update = Shopware()->Db()->query($update,array(
				$amount,
				$typID,
				$this->_order->userID,
				$this->_order->id
			));
			
			$rowID = $checkForExistingDocument["ID"];
			$bid = $checkForExistingDocument["docID"];
			$hash = $checkForExistingDocument["hash"];
		}else {
			// Create new document
			if ($this->_compatibilityMode==false){
				$hash = md5(uniqid(rand()));
			}
			$amount = $this->_config["netto"] == true ? round($this->_order->amountNetto,2) : round($this->_order->amount,2);
			if ($typID == 3){
				$amount *= -1;
			}
			$sql = "
			INSERT INTO s_order_documents (`date`, `type`, `userID`, `orderID`, `amount`, `docID`,`hash`)
			VALUES ( NOW() , ? , ? , ?, ?, ?,?)
			";
			$insert = Shopware()->Db()->query($sql,array(
				$typID,
				$this->_order->userID,
				$this->_order->id,
				$amount,
				$bid,
				$this->_compatibilityMode == true ? "" : $hash
			));
			$rowID = Shopware()->Db()->lastInsertId();
			// Update numberrange, except for cancellations
			if ($typID!=3){
				if (!empty($this->_document->numbers)){
					$numberrange = $this->_document->numbers;
				}else {
					$numberrange = "doc_".$typID;
				}
				
				$checkForSeparateNumbers = Shopware()->Db()->fetchRow("
					SELECT id, separate_numbers 
					FROM `s_core_multilanguage`
					WHERE `id` = ?
				",array($this->_subshop["id"]));
				
				if(!empty($checkForSeparateNumbers['separate_numbers']))
				{
					$numberrange.= "_".$checkForSeparateNumbers['id'];
				}
				$getNumber = Shopware()->Db()->fetchRow("
					SELECT `number`+1 as next FROM `s_order_number` WHERE `name` = ?"
				,array($numberrange));
				
				Shopware()->Db()->query("
					UPDATE `s_order_documents` SET `docID` = ? WHERE `ID` = ? LIMIT 1 ;
				",array($getNumber['next'],$rowID));
				
				Shopware()->Db()->query("
					UPDATE `s_order_number` SET `number` = ? WHERE `name` = ? LIMIT 1 ;
				",array($getNumber['next'],$numberrange));
				
				$bid = $getNumber["next"];
				
			}
		}
		$this->_documentID = $bid;
		$this->_documentRowID = $rowID;
		$this->_documentHash = $hash;
	}
	
}

?>