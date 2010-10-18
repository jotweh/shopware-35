<?php
class Shopware_Controllers_Frontend_Register extends Enlight_Controller_Action
{
	protected $session;
	protected $admin;
	protected $system;
	protected $post;
	protected $error;
	
	public function init()
	{
		$this->session = Shopware()->Session();
		$this->admin = Shopware()->Modules()->Admin();
		$this->system = Shopware()->Modules()->System();
		$this->post = $this->request->getParam('register');
	}
	
	public function preDispatch()
	{
		if(!isset($this->View()->register))
		{
			$this->View()->register = new ArrayObject(array(), ArrayObject::ARRAY_AS_PROPS);
		}
		
		if(!isset($this->session['sRegister']))
		{
			$this->session['sRegister'] = new ArrayObject(array(), ArrayObject::ARRAY_AS_PROPS);
		}
	}
	
	public function indexAction()
	{
		if(!empty($this->session['sUserId']))
		{
			if ($this->request->getParam('sValidation')||!Shopware()->Modules()->Basket()->sCountBasket()) {
				return $this->forward('index', 'account');
			} else {
				return $this->forward('confirm', 'checkout');
			}
		}
		$skipLogin = $this->request->getParam('skipLogin');
		if ($skipLogin=="1"){
			$this->View()->skipLogin = $skipLogin;
		}
		$this->personalAction();
		$this->billingAction();
		$this->shippingAction();
		$this->paymentAction();
	}
	
	public function saveRegisterAction()
	{
		if($this->request->isPost())
		{
			$this->savePersonalAction();
			$this->saveBillingAction();
			if(!empty($this->post['billing']['shippingAddress']))
			{
				$this->saveShippingAction();
			}
			$this->savePaymentAction();
						
			if(empty($this->error))
			{	
				$this->saveRegister();
			}
		}
		$this->forward('index');
	}
	
	public function saveRegister()
	{
		$paymentData = isset($this->session['sRegister']['payment']['object']) ? $this->session['sRegister']['payment']['object'] : false;
				
		$this->admin->sSaveRegister();
		
		if(!empty($paymentData))
		{
			$paymentObject = $this->admin->sInitiatePaymentClass($paymentData);
			$this->admin->sSYSTEM->_POST = $this->request->getPost();
			if (!empty($paymentObject)&&method_exists($paymentObject,'sInit'))
			{
				$checkPayment = $paymentObject->sInit(Shopware()->System());
			}
			if (!empty($paymentObject)&&method_exists($paymentObject,'sUpdate'))
			{
				$paymentObject->sUpdate();
			}
		}
	}
	
	public function personalAction()
	{
		if(!isset($this->View()->register->personal))
		{
			$this->View()->register->personal = new ArrayObject(array(), ArrayObject::ARRAY_AS_PROPS);
		}
		if(!isset($this->View()->register->personal->form_data))
		{
			$this->View()->register->personal->form_data = new ArrayObject(array(), ArrayObject::ARRAY_AS_PROPS);
		}
				
		if (!empty($this->session['sRegister']['auth']))
		foreach ($this->session['sRegister']['auth'] as $key => $value)
		{
			if(!isset($this->View()->register->personal->form_data->$key))
			{
				$this->View()->register->personal->form_data->$key = $value;
			}
		}
		
		if (!empty($this->session['sRegister']['billing']))
		foreach ($this->session['sRegister']['billing'] as $key => $value)
		{
			if(!isset($this->View()->register->personal->form_data->$key))
			{
				$this->View()->register->personal->form_data->$key = $value;
			}
		}
		
		if($this->request->getParam('sValidation'))
		{
			$this->View()->register->personal->form_data->sValidation = $this->request->getParam('sValidation');
		}
	}
	
	public function savePersonalAction()
	{
		if(!isset($this->View()->register->personal))
		{
			$this->View()->register->personal = new ArrayObject(array(), ArrayObject::ARRAY_AS_PROPS);
		}
		
		if(!empty($this->post['personal']))
		{
			$this->View()->register->personal->form_data = new ArrayObject($this->post['personal'], ArrayObject::ARRAY_AS_PROPS);
		}
		
		$checkData = $this->validatePersonal();
		if (!empty($checkData['sErrorMessages']))
		{
			foreach ($checkData['sErrorMessages'] as $key=>$error_message) {
				$checkData['sErrorMessages'][$key] = $this->View()->fetch('string:'.$error_message);
			}
			$this->error = true;
			$this->View()->register->personal->error_flags = new ArrayObject($checkData['sErrorFlag'], ArrayObject::ARRAY_AS_PROPS);
			$this->View()->register->personal->error_messages = new ArrayObject($checkData['sErrorMessages'], ArrayObject::ARRAY_AS_PROPS);
		}
	}
	
	public function billingAction()
	{
		if(!isset($this->View()->register->billing))
		{
			$this->View()->register->billing = new ArrayObject(array(), ArrayObject::ARRAY_AS_PROPS);
		}
		if(!isset($this->View()->register->billing->form_data))
		{
			$this->View()->register->billing->form_data = new ArrayObject(array(), ArrayObject::ARRAY_AS_PROPS);
		}
		
		$this->View()->register->billing->country_list = $this->admin->sGetCountryList();

		if(!empty($this->session['sRegister']['billing']))
		foreach ($this->session['sRegister']['billing'] as $key => $value)
		{
			if(!isset($this->View()->register->billing->form_data->$key))
			{
				$this->View()->register->billing->form_data->$key = $value;
			}
		}
		
		if(!empty($this->session['sCountry'])&&!isset($this->View()->register->billing->form_data->country))
		{
			$this->View()->register->billing->form_data->country = $this->session['sCountry'];
		}
	}
	
	public function saveBillingAction()
	{
		if(!isset($this->View()->register->billing))
		{
			$this->View()->register->billing = new ArrayObject(array(), ArrayObject::ARRAY_AS_PROPS);
		}
		if(!empty($this->post['billing']))
		{
			$this->View()->register->billing->form_data = new ArrayObject($this->post['billing'], ArrayObject::ARRAY_AS_PROPS);
			if(!empty($this->View()->register->billing->form_data['ustid']))
			{
				$this->View()->register->billing->form_data['ustid'] = preg_replace('#[^0-9A-Z\+\*\.]#','',strtoupper($this->View()->register->billing->form_data['ustid']));
			}
		}
		
		$checkData = $this->validateBilling();
			
		if (!empty($checkData['sErrorMessages']))
		{
			$this->error = true;
			$this->View()->register->billing->error_flags = new ArrayObject($checkData['sErrorFlag'], ArrayObject::ARRAY_AS_PROPS);
			$this->View()->register->billing->error_messages = new ArrayObject($checkData['sErrorMessages'], ArrayObject::ARRAY_AS_PROPS);
		}
	}
	
	public function shippingAction()
	{
		if(!isset($this->View()->register->shipping))
		{
			$this->View()->register->shipping = new ArrayObject(array(), ArrayObject::ARRAY_AS_PROPS);
		}
		if(!isset($this->View()->register->shipping->form_data))
		{
			$this->View()->register->shipping->form_data = new ArrayObject(array(), ArrayObject::ARRAY_AS_PROPS);
		}
		
		$this->View()->register->shipping->country_list = $this->admin->sGetCountryList();
		if(!empty($this->session['sRegister']['shipping']))
		foreach ($this->session['sRegister']['shipping'] as $key => $value)
		{
			$this->View()->register->shipping->form_data[$key] = $value;
		}
	}
	
	public function saveShippingAction()
	{
		if(!isset($this->View()->register->shipping))
		{
			$this->View()->register->shipping = new ArrayObject(array(), ArrayObject::ARRAY_AS_PROPS);
		}
		if(!empty($this->post['shipping']))
		{
			$this->View()->register->shipping->form_data = new ArrayObject($this->post['shipping'], ArrayObject::ARRAY_AS_PROPS);
		}

		$checkData = $this->validateShipping();
		
		if (!empty($checkData['sErrorMessages']))
		{
			$this->error = true;
			$this->View()->register->shipping->error_flags = new ArrayObject($checkData['sErrorFlag'], ArrayObject::ARRAY_AS_PROPS);
			$this->View()->register->shipping->error_messages = new ArrayObject($checkData['sErrorMessages'], ArrayObject::ARRAY_AS_PROPS);
		}
	}
	
	public function paymentAction()
	{
		if(!isset($this->View()->register->payment))
		{
			$this->View()->register->payment = new ArrayObject(array(), ArrayObject::ARRAY_AS_PROPS);
		}
		
		if(!isset($this->View()->register->payment->form_data))
		if(!empty($this->session['sPayment']))
		{
			$this->View()->register->payment->form_data = array('payment'=>$this->session['sPayment']);
		}
		else
		{
			$this->View()->register->payment->form_data = array('payment'=>Shopware()->Config()->get('DefaultPayment'));
		}
		
		$this->View()->register->payment->payment_means = $this->admin->sGetPaymentMeans();
		
		if(!empty($this->session['sRegister']['shipping']))
		foreach ($this->session['sRegister']['shipping'] as $key => $value)
		{
			$this->View()->form_data->register['shipping'][$key] = $value;
		}
	}
	
	public function savePaymentAction()
	{
		if(!isset($this->View()->register->payment))
		{
			$this->View()->register->payment = new ArrayObject(array(), ArrayObject::ARRAY_AS_PROPS);
		}
		if(!empty($this->post['payment']))
		{
			$this->View()->register->payment->form_data = $this->request->getPost();
			$this->View()->register->payment->form_data['payment'] = $this->post['payment'];
		}
		$this->admin->sSYSTEM->_POST = $this->request->getPost();
		$checkData = $this->validatePayment();
		if(!empty($checkData['sErrorMessages']))
		{
			$this->error = true;
			$this->View()->register->payment->error_flags = new ArrayObject($checkData['sErrorFlag'], ArrayObject::ARRAY_AS_PROPS);
			$this->View()->register->payment->error_messages = new ArrayObject($checkData['sErrorMessages'], ArrayObject::ARRAY_AS_PROPS);
		}
		else
		{
			$this->session['sRegister']['payment'] = array('object'=>$checkData['paymentData']);
		}
	}

	public function validatePersonal()
	{
		$this->admin->sSYSTEM->_POST = $this->post['personal'];
		
		$result = array();
		
		$checkData = $this->admin->sValidateStep1();
		if(!empty($checkData['sErrorMessages']))
		{
			$result = $checkData;
		}
		
		$rules = array(
			'customer_type'=>array('required'=>0),
			'salutation'=>array('required'=>1),
			//'company'=>array('addicted'=>array('salutation'=>'company'),'required'=>1),
			'firstname'=>array('required'=>1),
			'lastname'=>array('required'=>1),
			//'street'=>array('required'=>1),
			//'streetnumber'=>array('required'=>1),
			//'zipcode'=>array('required'=>1),
			//'city'=>array('required'=>1),
			'phone'=>array('required'=>1),
			//'country'=>array('required'=>1),
			//'department'=>array('required'=>0),
			'fax'=>array('required'=>0),
			//'shippingAddress'=>array('required'=>0),
			//'ustid'=>array('required'=>0),
			'text1'=>array('required'=>0),
			'text2'=>array('required'=>0),
			'text3'=>array('required'=>0),
			'text4'=>array('required'=>0),
			'text5'=>array('required'=>0),
			'text6'=>array('required'=>0),
			'sValidation'=>array('required'=>0),
			'birthyear'=>array('required'=>0),
			'birthmonth'=>array('required'=>0),
			'birthday'=>array('required'=>0),
			'dpacheckbox'=>array('required'=>(Shopware()->Config()->get('ACTDPRCHECK'))?1:0)
		);
		$rules = Enlight()->Events()->filter('Shopware_Controllers_Frontend_Register_validatePersonal_FilterRules', $rules, array('subject'=>$this));

		$checkData = $this->admin->sValidateStep2($rules);
				
		if(!empty($checkData['sErrorMessages']))
		{
			$result = array_merge_recursive($result, $checkData);
		}
		
		return $result;
	}
	
	public function validateBilling()
	{
		$rules = array(
			//'salutation'=>array('required'=>1),
			'company'=>array('required'=>0),
			//'firstname'=>array('required'=>1),
			//'lastname'=>array('required'=>1),
			'street'=>array('required'=>1),
			'streetnumber'=>array('required'=>1),
			'zipcode'=>array('required'=>1),
			'city'=>array('required'=>1),
			//'phone'=>array('required'=>1),
			'country'=>array('required'=>1),
			'department'=>array('required'=>0),
			//'fax'=>array('required'=>0),
			'shippingAddress'=>array('required'=>0),
			//'ustid'=>array('required'=>0),
			//'text1'=>array('required'=>0),
			//'text2'=>array('required'=>0),
			//'text3'=>array('required'=>0),
			//'text4'=>array('required'=>0),
			//'text5'=>array('required'=>0),
			//'text6'=>array('required'=>0),
			//'sValidation'=>array('required'=>0),
			//'birthyear'=>array('required'=>0),
			//'birthmonth'=>array('required'=>0),
			//'birthday'=>array('required'=>0),
			//'dpacheckbox'=>array('required'=>Shopware()->Config()->get('sACTDPRCHECK'))?1:0
		);
		
		if(!empty($this->post['personal']['customer_type']) && $this->post['personal']['customer_type'] == 'buisness')
		{
			$rules['company'] = array('required'=>1);
			$rules['ustid'] = array('required'=>0);
		}
		$rules = Enlight()->Events()->filter('Shopware_Controllers_Frontend_Register_validateBilling_FilterRules', $rules, array('subject'=>$this));

		$this->admin->sSYSTEM->_POST = $this->post['billing'];
		
		$checkData = $this->admin->sValidateStep2($rules, false);
		
		return $checkData;
	}
	
	public function validateShipping()
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
			'text1'=>array('required'=>0),
			'text2'=>array('required'=>0),
			'text3'=>array('required'=>0),
			'text4'=>array('required'=>0),
			'text5'=>array('required'=>0),
			'text6'=>array('required'=>0),
			'country'=>array('required'=>(Shopware()->Config()->get('sCOUNTRYSHIPPING'))?1:0)
		);
		$rules = Enlight()->Events()->filter('Shopware_Controllers_Frontend_Register_validateShipping_FilterRules', $rules, array('subject'=>$this));

		$this->admin->sSYSTEM->_POST = $this->post['shipping'];	
			
		$checkData = $this->admin->sValidateStep2ShippingAddress($rules);
		return $checkData;
	}
	
	public function validatePayment()
	{
		if(empty($this->post['payment']))
		{
			return array(
				'sErrorFlag' => array('payment'),
				'sErrorMessages' => array(Shopware()->Snippets()->getSnippet()->get('sErrorBillingAdress')),
			);
		}
		$this->admin->sSYSTEM->_POST['sPayment'] = $this->post['payment'];
				
		$checkData = $this->admin->sValidateStep3();
		
		if(!empty($checkData['checkPayment']['sErrorMessages']))
		{
			return array(
				'sErrorFlag' => $checkData['checkPayment']['sErrorFlag'],
				'sErrorMessages' => $checkData['checkPayment']['sErrorMessages'],
			);
		}
		return $checkData;
	}
	
	public function ajaxValidateEmailAction()
	{
		$this->View()->setTemplate(null);
		
		$error_flags = array();
		$error_messages = array();
		
		if (empty($this->post['personal']['email'])) {
			
		} elseif (($validator = new Zend_Validate_EmailAddress()) && !$validator->isValid($this->post['personal']['email'])) {
			$error_messages[] = Shopware()->Snippets()->getSnippet()->get('RegisterAjaxEmailNotValid', 'Bitte geben Sie eine gültige eMail-Adresse ein.', true);
			$error_flags['email'] = true;
			if(!empty($this->post['personal']['emailConfirmation'])) {
				$error_flags['emailConfirmation'] = true;
			}
		} elseif(empty($this->post['personal']['skipLogin'])&&$this->admin->sGetUserByMail($this->post['personal']['email'])) {
			$error_messages[] = Shopware()->Snippets()->getSnippet()->get('RegisterAjaxEmailForgiven', 'Diese eMail-Adresse ist bereits registriert.', true);
			$error_flags['email'] = true;
			if(!empty($this->post['personal']['emailConfirmation'])) {
				$error_flags['emailConfirmation'] = true;
			}
		} elseif (empty($this->post['personal']['emailConfirmation'])) {
			$error_flags['email'] = false;
		} elseif($this->post['personal']['emailConfirmation']!=$this->post['personal']['email']) {
			$error_messages[] = Shopware()->Snippets()->getSnippet()->get('RegisterAjaxEmailNotEqual', 'Die eMail-Adressen stimmen nicht überein.', true);
			$error_flags['email'] = true;
			$error_flags['emailConfirmation'] = true;
		} else {
			$error_flags['email'] = false;
			$error_flags['emailConfirmation'] = false;
		}
		
		foreach ($error_messages as $key=>$error_message) {
			$error_messages[$key] = utf8_encode($this->View()->fetch('string:'.$error_message));
		}
		
		echo Zend_Json::encode(array('success'=>empty($error_messages), 'error_flags'=>$error_flags, 'error_messages'=>$error_messages));
	}
	
	public function ajaxValidatePasswordAction()
	{
		$this->View()->setTemplate(null);
		
		$error_messages = array();
		$error_flags = array();
		
		if(empty($this->post['personal']['password'])) {
			
		} elseif (strlen($this->post['personal']['password']) < Shopware()->Config()->get('MinPassword')){
			$error_messages[] = Shopware()->Snippets()->getSnippet()->get('RegisterPasswordLength', 'Bitte wählen Sie ein Passwort welches aus mindestens {config name="MinPassword"} Zeichen besteht.', true);
			$error_flags['password'] = true;
			if(!empty($this->post['personal']['passwordConfirmation'])) {
				$error_flags['passwordConfirmation'] = true;
			}
		} elseif(empty($this->post['personal']['passwordConfirmation'])) {
			$error_flags['password'] = false;
		} elseif (!empty($this->post['personal']['passwordConfirmation']) && $this->post['personal']['password']!=$this->post['personal']['passwordConfirmation'])  {
			$error_messages[] = Shopware()->Snippets()->getSnippet()->get('RegisterPasswordNotEqual', 'Die Passwörter stimmen nicht überein.', true);
			$error_flags['password'] = true;
			$error_flags['passwordConfirmation'] = true;
		} else {
			$error_flags['password'] = false;
			$error_flags['passwordConfirmation'] = false;
		}
		
		foreach ($error_messages as $key=>$error_message) {
			$error_messages[$key] = utf8_encode($this->View()->fetch('string:'.$error_message));
		}
		
		echo Zend_Json::encode(array('success'=>empty($error_messages), 'error_flags'=>$error_flags, 'error_messages'=>$error_messages));
	}
	
	public function ajaxValidateBillingAction()
	{
		$this->View()->setTemplate(null);
		
		$rules = array(
			'salutation'=>array('required'=>1),
			'company'=>array('required'=>0),
			'firstname'=>array('required'=>1),
			'lastname'=>array('required'=>1),
			'street'=>array('required'=>1),
			'streetnumber'=>array('required'=>1),
			'zipcode'=>array('required'=>1),
			'city'=>array('required'=>1),
			'country'=>array('required'=>1),
			'department'=>array('required'=>0),
		);
		if(!empty($this->post['personal']['customer_type'])&&$this->post['personal']['customer_type']=='buisness')
		{
			$rules['company']['required'] = 1;
		}
		$this->admin->sSYSTEM->_POST = array_merge($this->post['personal'], $this->post['billing']);
		$checkData = $this->admin->sValidateStep2($rules);
		
		
		$error_messages = array();
		$error_flags = array();
		
		if(!empty($checkData['sErrorMessages']))
		{
			foreach ($checkData['sErrorMessages'] as $error_message)
			{
				$error_messages[] = utf8_encode($error_message);
			}
		}
		
		foreach ($rules as $field => $rule)
		{
			$error_flags[$field] = !empty($checkData['sErrorFlag'][$field]);
		}
		
		echo Zend_Json::encode(array('success'=>empty($error_messages), 'error_flags'=>$error_flags, 'error_messages'=>$error_messages));
	}
}