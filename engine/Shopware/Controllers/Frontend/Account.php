<?php
/**
 * Account controller
 * 
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Heiner Lohaus
 * @package Shopware
 * @subpackage Controllers
 */
class Shopware_Controllers_Frontend_Account extends Enlight_Controller_Action
{
    /**
     * @var sAdmin
     */
	protected $admin;
	
	/**
	 * Init controller method
	 */
	public function init()
	{
		$this->admin = Shopware()->Modules()->Admin();
	}
	
	/**
	 * Pre dispatch method
	 */
	public function preDispatch()
	{
		if(!in_array($this->Request()->getActionName(), array('login', 'logout', 'password', 'ajax_login', 'ajax_logout'))
			&& !$this->admin->sCheckUser())
		{
			$this->forward('login');
		}
		$this->View()->sUserData = $this->admin->sGetUserData();
	}
	
	/**
	 * Index action method
	 * 
	 * Read orders and notes
	 */
	public function indexAction()
	{
		$this->View()->sOrders = $this->admin->sGetOpenOrderData();
		$this->View()->sNotes = Shopware()->Modules()->Basket()->sGetNotes();
		if($this->Request()->getParam('success')) {
			$this->View()->sSuccessAction = $this->Request()->getParam('success');
		}
	}
	
	/**
	 * Billing action method
	 * 
	 * Read billing address data
	 */
	public function billingAction()
	{
		$this->View()->sBillingPreviously = $this->admin->sGetPreviousAddresses('billing');
		$this->View()->sCountryList = $this->admin->sGetCountryList();
		$this->View()->sTarget = $this->Request()->getParam('sTarget', $this->Request()->getControllerName());
		
		if(!empty($this->View()->sUserData['billingaddress']))
		{
			$address = $this->View()->sUserData['billingaddress'];
			$address['country'] = $address['countryID'];
			unset($address['id'], $address['countryID']);
			if(!empty($address['birthday']))
			{
				list($address['birthyear'], $address['birthmonth'], $address['birthday']) = explode('-', $address['birthday']);
			}
			if($this->Request()->isPost())
			{
				$address = array_merge($address, $this->Request()->getPost());
			}
			
			$this->View()->sFormData = $address;
		}
	}
	
	/**
	 * Shipping action method
	 * 
	 * Read shipping address data
	 */
	public function shippingAction()
	{
		$this->View()->sShippingPreviously = $this->admin->sGetPreviousAddresses('shipping');
		$this->View()->sCountryList = $this->admin->sGetCountryList();
		$this->View()->sTarget = $this->Request()->getParam('sTarget', $this->Request()->getControllerName());
		
		if(!empty($this->View()->sUserData['shippingaddress']))
		{
			$address = $this->Request()->getPost()+$this->View()->sUserData['shippingaddress'];
			$address['country'] = $address['countryID'];
			unset($address['id'], $address['countryID']);
			$this->View()->sFormData = $address;
		}
	}
	
	/**
	 * Payment action method
	 * 
	 * Read and change payment mean and payment data
	 */
	public function paymentAction()
	{
		$this->View()->sPaymentMeans = $this->admin->sGetPaymentMeans();
		$this->View()->sFormData = array('payment'=>$this->View()->sUserData['additional']['user']['paymentID']);
		$this->View()->sTarget = $this->Request()->getParam('sTarget', $this->Request()->getControllerName());
		
		$getPaymentDetails = $this->admin->sGetPaymentMeanById($this->View()->sFormData['payment']);
		if ($getPaymentDetails['table'])
		{
			$paymentClass = $this->admin->sInitiatePaymentClass($getPaymentDetails);
			if (!empty($paymentClass))
			{
				$this->View()->sFormData += $paymentClass->getData();
			}
		}
		
		if($this->Request()->isPost())
		{
			$values = $this->Request()->getPost();
			$values['payment'] = $this->Request()->getPost('register');
			$values['payment'] = $values['payment']['payment'];
			$this->View()->sFormData = $values;
		}
	}
	
	
	/**
	 * Orders action method
	 * 
	 * Read last orders
	 */
	public function ordersAction()
	{
		$this->View()->sOpenOrders = $this->admin->sGetOpenOrderData();
	}
	
	/**
	 * Downloads action method
	 * 
	 * Read last downloads
	 */
	public function downloadsAction()
	{
		$this->View()->sDownloads = $this->admin->sGetDownloads();
	}
	
	/**
	 * Logout action method
	 * 
	 * Logout account and delete session
	 */
	public function logoutAction()
	{
		Shopware()->Session()->unsetAll();
		Shopware()->Session()->Shop = Shopware()->Shop();
		Shopware()->Modules()->Basket()->sGetBasket();
		Shopware()->Modules()->Admin()->sGetShippingcosts();
	}
	
	/**
	 * Login action method
	 * 
	 * Login account and show login erros
	 */
	public function loginAction()
	{
		$this->View()->sTarget = $this->Request()->getParam('sTarget');

		if($this->Request()->isPost())
		{
			$checkUser = $this->admin->sLogin();
			
			if (!empty($checkUser['sErrorMessages']))
			{
				$this->View()->sFormData = $this->Request()->getPost();
				$this->View()->sErrorFlag = $checkUser['sErrorFlag'];
				$this->View()->sErrorMessages = $checkUser['sErrorMessages'];
			}
		}
		
		if(empty($this->View()->sErrorMessages) && $this->admin->sCheckUser())
		{
			if(!$target = $this->Request()->getParam('sTarget'))
			{
				$target = 'account';
			}
			$this->redirect(array('controller'=>$target));
		}
	}
	
	/**
	 * Save billing action
	 *
	 * Save billing address data
	 */
	public function saveBillingAction()
	{
		if($this->Request()->isPost())
		{
			$rules = array(
				'salutation'=>array('required'=>1),
				'company'=>array('required'=>0),
				'firstname'=>array('required'=>1),
				'lastname'=>array('required'=>1),
				'street'=>array('required'=>1),
				'streetnumber'=>array('required'=>1),
				'zipcode'=>array('required'=>1),
				'city'=>array('required'=>1),
				'phone'=>array('required'=>1),
				'fax'=>array('required'=>0),
				'country'=>array('required'=>1),
				'department'=>array('required'=>0),
				'shippingAddress'=>array('required'=>0),
				//'ustid'=>array('required'=>0),
				'text1'=>array('required'=>0),
				'text2'=>array('required'=>0),
				'text3'=>array('required'=>0),
				'text4'=>array('required'=>0),
				'text5'=>array('required'=>0),
				'text6'=>array('required'=>0),
				'birthyear'=>array('required'=>0),
				'birthmonth'=>array('required'=>0),
				'birthday'=>array('required'=>0),
			);
			if ($this->Request()->getParam('sSelectAddress'))
			{
				$address = $this->admin->sGetPreviousAddresses('billing', $this->Request()->getParam('sSelectAddress'));
				if (!empty($address['hash']))
				{
					$address = array_merge($this->View()->sUserData['billingaddress'], $address);
					$this->admin->sSYSTEM->_POST = $address;
				}
			}
			
			$values = $this->Request()->getPost('register');
						
			if((!empty($values['personal']['customer_type'])&&$values['personal']['customer_type']=='private'))
			{
				$values['billing']['company'] = '';
				$values['billing']['department'] = '';
				$values['billing']['ustid'] = '';
			}
			elseif((!empty($values['personal']['customer_type'])||!empty($values['billing']['company'])))
			{
				$rules['ustid'] = array('required'=>0);
			}
						
			if(!empty($values))
			{
				$this->admin->sSYSTEM->_POST = array_merge($values['personal'], $values['billing'], $this->admin->sSYSTEM->_POST);
			}
			
			$checkData = $this->admin->sValidateStep2($rules, true);
			
			if (!empty($checkData['sErrorMessages']))
			{
				$this->View()->sErrorFlag = $checkData['sErrorFlag'];
				$this->View()->sErrorMessages = $checkData['sErrorMessages'];
				return $this->forward('billing');
			}
			else
			{
				$this->admin->sUpdateBilling();
			}
		}
		if(!$target = $this->Request()->getParam('sTarget'))
		{
			$target = 'account';
		}
		$this->redirect(array('controller'=>$target, 'action'=>'index', 'success'=>'billing'));
	}
	
	/**
	 * Save shipping action
	 *
	 * Save shipping address data
	 */
	public function saveShippingAction()
	{
		if($this->Request()->isPost())
		{
			$rules = array(
				'salutation'=>array('required'=>1),
				'company'=>array('required'=>0),
				'firstname'=>array('required'=>1),
				'lastname'=>array('required'=>1),
				'street'=>array('required'=>1),
				'streetnumber'=>array('required'=>1),
				'zipcode'=>array('required'=>1),
				'city'=>array('required'=>1),
				'department'=>array('required'=>0),
				'country'=>array('required'=>1),
				'text1'=>array('required'=>0),
				'text2'=>array('required'=>0),
				'text3'=>array('required'=>0),
				'text4'=>array('required'=>0),
				'text5'=>array('required'=>0),
				'text6'=>array('required'=>0)
			);
			
			if (Shopware()->Config()->get('sCOUNTRYSHIPPING')){
				$rules['country'] = array('required'=>1);
			} else {
				$rules['country'] = array('required'=>0);
			}
			
			
			if ($this->Request()->getParam('sSelectAddress'))
			{
				$address = $this->admin->sGetPreviousAddresses('shipping', $this->Request()->getParam('sSelectAddress'));
				if (!empty($address['hash']))
				{
					$address = array_merge($this->View()->sUserData['shippingaddress'], $address);
					$this->admin->sSYSTEM->_POST = $address;
				}
			}
			else
			{
				$this->admin->sSYSTEM->_POST =  $this->Request()->getPost();
			}
			
			$values = $this->Request()->getPost('register');
			
			if(!empty($values))
			{
				$this->admin->sSYSTEM->_POST = array_merge($values['shipping'], $this->admin->sSYSTEM->_POST);
			}
			
			$checkData = $this->admin->sValidateStep2ShippingAddress($rules, true);
			if (!empty($checkData['sErrorMessages']))
			{
				$this->View()->sErrorFlag = $checkData['sErrorFlag'];
				$this->View()->sErrorMessages = $checkData['sErrorMessages'];
				return $this->forward('shipping');
			}
			else
			{
				$this->admin->sUpdateShipping();
			}
		}
		if(!$target = $this->Request()->getParam('sTarget'))
		{
			$target = 'account';
		}
		$this->redirect(array('controller'=>$target, 'action'=>'index', 'success'=>'shipping'));
	}
	
	/**
	 * Save shipping action
	 *
	 * Save shipping address data
	 */
	public function savePaymentAction()
	{
		if($this->Request()->isPost())
		{
			$values = $this->Request()->getPost('register');
			$this->admin->sSYSTEM->_POST['sPayment'] = $values['payment'];
			
			$checkData = $this->admin->sValidateStep3();
			
			if (!empty($checkData['checkPayment']['sErrorMessages']) || empty($checkData['sProcessed']))
			{
				$this->View()->sErrorFlag = $checkData['checkPayment']['sErrorFlag'];
				$this->View()->sErrorMessages = $checkData['checkPayment']['sErrorMessages'];
				return $this->forward('payment');
			}
			else
			{
				$previousPayment = $this->admin->sGetUserData();
				$previousPayment = $previousPayment['additional']['user']['paymentID'];
	
				$previousPayment = $this->admin->sGetPaymentMeanById($previousPayment);
				if ($previousPayment['paymentTable']){
					$deleteSQL = 'DELETE FROM '.$previousPayment['paymentTable'].' WHERE userID=?';
					Shopware()->Db()->query($deleteSQL, array(Shopware()->Session()->sUserId));
				}
	
				$this->admin->sUpdatePayment();
	
				if (method_exists($checkData['sPaymentObject'],'sUpdate')){
					$checkData['sPaymentObject']->sUpdate();
				}
			}
		}
		
		if(!$target = $this->Request()->getParam('sTarget'))
		{
			$target = 'account';
		}
		$this->redirect(array('controller'=>$target, 'action'=>'index', 'success'=>'payment'));
	}
	
	/**
	 * Save newsletter action
	 *
	 * Save newsletter address data
	 */
	public function saveNewsletterAction()
	{
		if($this->Request()->isPost())
		{
			$status = $this->Request()->getPost('newsletter') ? true : false;
			$this->admin->sUpdateNewsletter($status, $this->admin->sGetUserMailById(), true);
			$this->View()->sSuccessAction = 'newsletter';
		}
		$this->forward('index');
	}
	
	/**
	 * Save account action
	 *
	 * Save account address data and create error messages
	 *
	 */
	public function saveAccountAction()
	{
		if($this->Request()->isPost())
		{
			$checkData = $this->admin->sValidateStep1(true);
			if (!empty($checkData["sErrorMessages"])){
				foreach ($checkData["sErrorMessages"] as $key=>$error_message) {
					$checkData["sErrorMessages"][$key] = $this->View()->fetch('string:'.$error_message);
				}
			}
			if (empty($checkData['sErrorMessages'])){
				$this->admin->sUpdateAccount();
				$this->View()->sSuccessAction = 'account';
			} else {
				$this->View()->sErrorFlag = $checkData['sErrorFlag'];
				$this->View()->sErrorMessages = $checkData['sErrorMessages'];
			}
		}
		$this->forward('index');
	}
	
	/**
	 * Download action
	 *
	 * Read and test download file 
	 */
	public function downloadAction()
	{		
		$esdID = $this->request->getParam('esdID');
		
		if(empty($esdID))
		{
			return $this->forward('downloads');
		}

		$sql = '
			SELECT file, articleID
			FROM s_articles_esd ae, s_order_esd oe
			WHERE ae.id=oe.esdID
			AND	oe.userID=?
			AND oe.orderdetailsID=?
		';
		$download = Shopware()->Db()->fetchRow($sql, array(Shopware()->Session()->sUserId, $esdID));
		
		if(empty($download))
		{
			$sql = '
				SELECT e.file, ad.articleID
				FROM s_articles_esd e, s_order_details od, s_articles_details ad, s_order o
				WHERE e.articledetailsID=ad.id
				AND ad.ordernumber=od.articleordernumber
				AND o.id=od.orderID
				AND o.userID=?
				AND od.id=?
			';
			$download = Shopware()->Db()->fetchRow($sql, array(Shopware()->Session()->sUserId, $esdID));
		}
		
		if(empty($download['file']))
		{
			$this->View()->sErrorCode = 1;
			return $this->forward('downloads');
		}
		
		$file = 'files/'.Shopware()->Config()->get('sESDKEY').'/'.$download['file'];
		
		if(!file_exists(Shopware()->OldPath().$file))
		{
			$this->View()->sErrorCode = 2;
			return $this->forward('downloads');
		}
		$this->redirect($file);
	}
	
	/**
	 * Read saved billing address
	 */
	public function selectBillingAction()
	{
		$this->View()->sTarget = $this->Request()->getParam('sTarget', $this->Request()->getControllerName());
		$this->View()->sBillingAddresses = $this->admin->sGetPreviousAddresses('billing');
	}
	
	/**
	 * Read saved shipping address
	 */
	public function selectShippingAction()
	{
		$this->View()->sTarget = $this->Request()->getParam('sTarget', $this->Request()->getControllerName());
		$this->View()->sShippingAddresses = $this->admin->sGetPreviousAddresses('shipping');
	}
	
	/**
	 * Send new account password
	 */
	public function passwordAction()
	{
		$this->View()->sTarget = $this->Request()->getParam('sTarget');

		if($this->Request()->isPost()) {
			$checkUser = $this->sendPassword($this->Request()->getParam('email'));
			
			if (!empty($checkUser['sErrorMessages'])) {
				$this->View()->sFormData = $this->Request()->getPost();
				$this->View()->sErrorFlag = $checkUser['sErrorFlag'];
				$this->View()->sErrorMessages = $checkUser['sErrorMessages'];
			} else {
				$this->View()->sSuccess = true;
			}
		}
	}
	
	/**
	 * Send new password by email address
	 *
	 * @param string $email
	 * @return array
	 */
	public function sendPassword($email)
	{
		if (empty($email)) {
			return array('sErrorMessages'=>array(Shopware()->Config()->Snippets()->get('sErrorForgotMail')));
		}
		
		$userID = Shopware()->System()->sMODULES['sAdmin']->sGetUserByMail($email);
		if (empty($userID)) {
			return array('sErrorMessages'=>array(Shopware()->Config()->Snippets()->get('sErrorForgotMailUnknown')));
		}
		
		$password = substr(md5(uniqid(rand())), 0, 6);
		$md5_password = md5($password);
		
		$sql = 'UPDATE s_user SET password=? WHERE id=?';
		Shopware()->Db()->query($sql, array($md5_password, $userID));
		
		$template = Shopware()->Config()->get('sTemplates')->sPASSWORD;
		
		$template['content'] = str_replace('{sMail}', $email, $template['content']);
		$template['content'] = str_replace('{sPassword}', $password, $template['content']);
		$template['content'] = str_replace('{sShopURL}', 'http://'.Shopware()->Config()->BasePath, $template['content']);
		$template['subject'] = str_replace('{sShop}', Shopware()->Config()->ShopName, $template['subject']);	
		
		$template['contentHTML'] = str_replace('{sMail}', $email, $template['contentHTML']);
		$template['contentHTML'] = str_replace('{sPassword}', $password, $template['contentHTML']);
		$template['contentHTML'] = str_replace('{sShop}', Shopware()->Config()->ShopName, $template['contentHTML']);	
		$template['contentHTML'] = str_replace('{sShopURL}', 'http://'.Shopware()->Config()->BasePath, $template['contentHTML']);
							
		$mail           = clone Shopware()->Mail();
		$mail->From     = $template['frommail'];
		$mail->FromName = $template['fromname'];
		$mail->Subject  = $template['subject'];
		
		if ($template['ishtml']){
			$mail->IsHTML(1);
			$mail->Body     = $template['contentHTML'];
			$mail->AltBody     = $template['content'];
		} else {
			$mail->IsHTML(0);
			$mail->Body     = $template['content'];
		}
		
		$mail->ClearAddresses();
	
		$mail->AddAddress($email, '');
		
		if (!$mail->Send()){
			Shopware()->System()->E_CORE_WARNING ('##01 ForgotPassword','Could not send eMail');
		} else {
			return array('sSuccess'=>true);
		}
	}
	
	/**
	 * Login account by ajax request
	 */
	public function ajaxLoginAction()
	{
		Enlight()->Plugins()->Controller()->Json()->setPadding();
		
		if($this->admin->sCheckUser()) {
			return $this->View()->setTemplate();
		}		
				
		if(!$this->Request()->getParam('accountmode')) {
			return;
		}
		
		if (empty(Shopware()->Session()->sRegister)) {
			Shopware()->Session()->sRegister = new ArrayObject(array(), ArrayObject::ARRAY_AS_PROPS);
		}
		
		$this->admin->sSYSTEM->_POST = array();
		$this->admin->sSYSTEM->_POST['email'] = utf8_decode($this->Request()->getParam('email'));
		$this->admin->sSYSTEM->_POST['password'] = utf8_decode($this->Request()->getParam('password'));

		if($this->Request()->getParam('accountmode')==0 || $this->Request()->getParam('accountmode')==1) {
			Shopware()->Session()->sRegister['auth']['email'] = $this->admin->sSYSTEM->_POST['email'];
			Shopware()->Session()->sRegister['auth']['accountmode'] = (int) $this->Request()->getParam('accountmode');
			
			$this->View()->setTemplate();
		} else {
			$checkData = $this->admin->sLogin();
			
			if (empty($checkData['sErrorMessages'])) {
				$this->View()->setTemplate();
			} else {
				$this->View()->sFormData = $this->Request()->getParam();
				$this->View()->sErrorFlag = $checkData['sErrorFlag'];
				$this->View()->sErrorMessages = $checkData['sErrorMessages'];
			}
		}
	}
	
	/**
	 * Logout account by ajax request
	 */
	public function ajaxLogoutAction()
	{
		Enlight()->Plugins()->Controller()->Json()->setPadding();
		
		Shopware()->Session()->unsetAll();
		Shopware()->Session()->Shop = Shopware()->Shop();
		Shopware()->Modules()->Basket()->sGetBasket();
		Shopware()->Modules()->Admin()->sGetShippingcosts();
	}
}