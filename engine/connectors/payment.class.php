<?php
require_once(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'Shopware'.DIRECTORY_SEPARATOR.'Shopware.php');

/**
 * Shopware Payment Application
 */
class sPayment extends Shopware
{
	public $sDB_CONNECTION;
	public $sMODULES;
	public $sSYSTEM;
	public $config;

	public $sTax;
	public $sUser;
	public $sBasket;
	
	public $verbose = false;
	public $neededArguments = array();
	public $arguments = array('coreID'=>'param_sCoreId','dispatchID'=>'dispatchID','transactionID'=>'transactionID','comment'=>'comment');

	/**
	 * Enter description here...
	 *
	 * @param unknown_type $debug
	 * @param unknown_type $path
	 * @param unknown_type $initSession
	 */
	public function __construct ($debug, $path='', $initSession=true)
	{
		parent::__construct();
		
		$this->Bootstrap()->loadResource('Zend');
		$this->Bootstrap()->loadResource('Db');
        $this->Bootstrap()->loadResource('Plugins');
        
        $errorHandler = $this->Plugins()->Core()->ErrorHandler();
        if($errorHandler) {
       		$errorHandler->registerErrorHandler(E_ALL | E_STRICT);
        }
        
        if(!empty($_REQUEST[$this->arguments['coreID']])) {
        	$this->Bootstrap()->registerResource('SessionID', $_REQUEST[$this->arguments['coreID']]);
        }
        if(!empty($_REQUEST['sLanguage'])) {
        	$_POST['sLanguage'] = (int) $_REQUEST['sLanguage'];
        }
        
        Enlight_Components_Session::writeClose();

		$this->sSYSTEM  = $this->System();
		$this->sDB_CONNECTION = $this->Adodb();
		$this->sMODULES = $this->Modules();
		$this->config = $this->Config();
		
		if(empty($this->config['sTEMPLATEOLD'])) {
			$this->config['sIGNOREAGB'] = true;
		}
		
		//$this->initUser();
	}
		
	/**
	 * Login user method
	 *
	 * @return unknown
	 */
	public function loginUser()
	{
		if (empty($this->sSYSTEM->_SESSION['sUserId'])&&!empty($_REQUEST[$this->arguments['coreID']])) {
			$userData = $this->sDB_CONNECTION->GetRow('SELECT * FROM s_user WHERE sessionID=? ORDER BY lastlogin DESC', array($_REQUEST[$this->arguments['coreID']]));
			if (empty($userData['id'])) {
				return false;
			}
			$this->sSYSTEM->_SESSION['sUserId'] = $userData['id'];
			$this->sSYSTEM->_SESSION['sUserPassword'] = $userData['password'];
			$this->sSYSTEM->_SESSION['sUserMail'] = $userData['email'];

			// Trying to read ip-address
			$sql = 'SELECT remoteaddr FROM s_statistics_currentusers WHERE userID=?';
			$ip = $this->sSYSTEM->sDB_CONNECTION->GetOne($sql, array($userData['id']));
			if (!empty($ip)) {
				$_SERVER['REMOTE_ADDR'] = $ip;
			}
		}

		if(!Shopware()->Modules()->Admin()->sCheckUser()){
			return false;
		}
		return true;
	}

	/**
	 * Init user method
	 */
	public function initUser()
	{
		$this->sUser = Shopware()->Modules()->Admin()->sGetUserData();

		$sTaxFree = false;
		if (!empty( $this->sUser['additional']['countryShipping']['taxfree'])){
			$sTaxFree = true;
		} elseif (
			(!empty($this->sUser['additional']['countryShipping']['taxfree_ustid']) || !empty($this->sUser['additional']['countryShipping']['taxfree_ustid_checked']))
			&& !empty($this->sUser['billingaddress']['ustid'])
			&& $this->sUser['additional']['country']['id'] == $this->sUser['additional']['countryShipping']['id']) {
			$sTaxFree = true;
		}
		if(!empty($sTaxFree))
		{
			Shopware()->System()->sUSERGROUPDATA['tax'] = 0;
			Shopware()->System()->sCONFIG['sARTICLESOUTPUTNETTO'] = 1;
			Shopware()->System()->_SESSION['sUserGroupData'] = Shopware()->System()->sUSERGROUPDATA;
			$this->sUser['additional']['charge_vat'] = false;
			$this->sUser['additional']['show_net'] = false;
		}
		else
		{
			$this->sUser['additional']['charge_vat'] = true;
			$this->sUser['additional']['show_net'] = !empty(Shopware()->System()->sUSERGROUPDATA['tax']);
		}
		
		$this->sTax = $this->sUser['additional']['charge_vat'];
	}

	/**
	 * Returns amount method
	 */
	public function getAmount()
	{
		if(!$this->sBasket) {
			$this->initBasket();
		}
		if ($this->sTax){
			return empty($this->sBasket['AmountWithTaxNumeric']) ? $this->sBasket['AmountNumeric'] : $this->sBasket['AmountWithTaxNumeric'];
		} else {
			return $this->sBasket['AmountNetNumeric'];
		}
	}
	
	/**
	 * Returns amount net method
	 */
	public function getAmountNet()
	{
		if(!$this->sBasket) {
			$this->initBasket();
		}
		return $this->sBasket['AmountNetNumeric'];
	}
	
	/**
	 * Init basket method
	 */
	public function initBasket()
	{
		if(!$this->sUser) {
			$this->initUser();
		}
		$country = $this->sUser['additional']['countryShipping'];
		$payment = $this->sUser['additional']['payment'];
		
		$this->sBasket = Shopware()->Modules()->Basket()->sGetBasket();
		
		$shippingcosts = Shopware()->Modules()->Admin()->sGetShippingcosts($country, $payment['surcharge'], $payment['surchargestring']);
		$shippingcosts =  empty($shippingcosts) ? array('brutto'=>0, 'netto'=>0) : $shippingcosts;
		
		$this->sBasket['sShippingcostsWithTax'] = $shippingcosts['brutto'];
		$this->sBasket['sShippingcostsNet'] = $shippingcosts['netto'];
		
		if (!empty($shippingcosts['brutto']))
		{
			$this->sBasket['AmountNetNumeric'] += $shippingcosts['netto'];
			$this->sBasket['AmountNumeric'] += $shippingcosts['brutto'];
			if (!empty($this->sBasket['AmountWithTaxNumeric']))
			{
				$this->sBasket['AmountWithTaxNumeric'] += $shippingcosts['brutto'];
			}
		}
		if ((!Shopware()->System()->sUSERGROUPDATA['tax'] && Shopware()->System()->sUSERGROUPDATA['id']))
		{			
			$this->sBasket['sShippingcosts'] = $shippingcosts['netto'];
			$this->sBasket['sAmount'] = round($this->sBasket['AmountNetNumeric'], 2);
			$this->sBasket['sAmountTax'] = round($this->sBasket['AmountWithTaxNumeric']-$this->sBasket['AmountNetNumeric'], 2);
			$this->sBasket['sAmountWithTax'] = round($this->sBasket['AmountWithTaxNumeric'], 2);
		}
		else
		{			
			$this->sBasket['sShippingcosts'] = $shippingcosts['brutto'];
			$this->sBasket['sAmount'] = $this->sBasket['AmountNumeric'];
			$this->sBasket['sAmountTax'] = round($this->sBasket['AmountNumeric']-$this->sBasket['AmountNetNumeric'], 2);
		}
	}

	/**
	 * Returns basket method
	 *
	 * @return array
	 */
	public function getBasket()
	{
		if(!$this->sBasket) {
			$this->initBasket();
		}
		return $this->sBasket;
	}
	
	/**
	 * Returns amount tax method
	 */
	public function getAmountTax()
	{
		if(!$this->sBasket) {
			$this->initBasket();
		}
		if ($this->sTax){
			return $this->sBasket['sAmountTax'];
		} else {
			return 0;
		}
	}
	
	/**
	 * Returns shipping costs method
	 */
	public function getShippingCosts()
	{
		if(!$this->sBasket) {
			$this->initBasket();
		}
		if ($this->sTax){
			return $this->sBasket['sShippingcostsWithTax'];
		} else {
			return $this->sBasket['sShippingcostsNet'];
		}
	}
	
	/**
	 * Returns shipping costs method
	 */
	public function getShippingCostsNet()
	{
		if(!$this->sBasket) {
			$this->initBasket();
		}
		return $this->sBasket['sShippingcostsNet'];
	}

	/**
	 * Save order method
	 */
	public function submitOrder($viewport, $path ,$viewportName ,$paymentState=0)
	{
		if(!$this->loginUser()){
			$this->throughError('Could not login user');
		}
		if(!$this->sUser) {
			$this->initUser();
		}
		if(!$this->sBasket) {
			$this->initBasket();
		}
		
		$request = new Enlight_Controller_Request_RequestHttp();
		if(!empty($_REQUEST)) {
			$request->setParams($_REQUEST);
		}
		if(!empty($this->arguments)) {
			foreach ($this->arguments as $key=>$alias) {
				if($request->getParam($alias)!==null) {
					$request->setParam($key, $request->getParam($alias));
				}
			}
		}
		$request->setParam('uniqueID', $request->getParam('uniqueID', $request->getParam('param_uniqueId')));
		$request->setParam('comment', $request->getParam('comment', Shopware()->Session()->sComment));
		
		$sql = 'SELECT ordernumber FROM s_order WHERE transactionID=? AND status != -1';
		$ordernumber = $this->Db()->fetchOne($sql, array($request->getParam('transactionID')));
		
		if(empty($ordernumber)) {	
	       	$order = Shopware()->Modules()->Order();
			$order->sUserData = $this->sUser;
			$order->sComment = $request->getParam('comment');
			$order->sBasketData = $this->sBasket;
			$order->sAmount = $this->sBasket['sAmount'];
			$order->sAmountWithTax = !empty($this->sBasket['AmountWithTaxNumeric']) ? $this->sBasket['AmountWithTaxNumeric'] : $this->sBasket['AmountNumeric'];
			$order->sAmountNet = $this->sBasket['AmountNetNumeric'];
			$order->sShippingcosts = $this->sBasket['sShippingcosts'];
			$order->sShippingcostsNumeric = $this->sBasket['sShippingcostsWithTax'];
			$order->sShippingcostsNumericNet = $this->sBasket['sShippingcostsNet'];
			$order->bookingId = $request->getParam('transactionID');
			$order->dispatchId = Shopware()->Session()->sDispatch;
			$order->sNet = !$this->sTax;
			$order->uniqueID = $request->getParam('uniqueID');
			$ordernumber = $order->sSaveOrder();
			
			if(!empty(Shopware()->Config()->DeleteCacheAfterOrder)) {
				Shopware()->Cache()->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array('Shopware_Adodb'));
			}
		}
        
        if (!empty($ordernumber) && !empty($paymentState)) {
        	$sql = 'UPDATE s_order SET cleared=? WHERE ordernumber=?';
			$this->Db()->query($sql, array($paymentState, $ordernumber));
		}
		
		return $ordernumber;
	}
	
	/**
	 * Through error method
	 *
	 * @param unknown_type $msg
	 * @param unknown_type $hold
	 */
	public function throughError($msg, $hold=true)
	{
		if ($this->verbose && $hold){
			throw new Enlight_Exception($msg);
		} else {
			trigger_error($msg, E_USER_ERROR);
		}
	}
	
	/**
	 * Format cent method
	 *
	 * @param unknown_type $amount
	 * @return unknown
	 */
	public static function formatAmountCent($amount)
	{
		return intval(round($amount*100));
	}

	/**
	 * Init payment method
	 */
	public function initPayment()
	{
		if (!empty($this->gatewayIps)){
			if (!in_array($_SERVER['REMOTE_ADDR'],$this->gatewayIps)){
				$this->throughError($_SERVER['REMOTE_ADDR'].' is not allowed to access payment-gateway');
			}
		}

		if (!empty($this->neededArguments)){
			foreach ($this->neededArguments as $key) {
				if (!$_REQUEST[$key]){
					$this->throughError('Missing parameter "'.$key.'" in payment request');
				}
			}
		}
	}
	
	/**
	 * Catch errors method
	 * 
	 * @deprecated 
	 */
	public function catchErrors()
	{
		
	}
}