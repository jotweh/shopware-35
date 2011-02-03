<?php
class Shopware_Controllers_Frontend_Paypal extends Enlight_Controller_Action
{
	protected $sSystem;
	
	public function init()
	{
		$this->sSystem = Shopware()->System();
	}
	
	public function errorAction()
	{
		throw new Enlight_Exception($this->Request()->error);
		
		$this->View()->sError = $this->Request()->error;
	}
	
	public function errorApiAction()
	{
		$variables = array();
		
		$variables["payPalURL"] = $this->sSystem->_SESSION['payPalURL'];;
		 
		if(isset($this->sSystem->_SESSION['curl_error_no'])) { 
			// URL Error, something goes wrong
			throw new Enlight_Exception($this->sSystem->_SESSION['curl_error_msg'], $this->sSystem->_SESSION['curl_error_no']);
			$variables["errorCode"] = $this->sSystem->_SESSION['curl_error_no'] ;
			$variables["errorMessage"] = $this->sSystem->_SESSION['curl_error_msg'] ;
			$variables["urlError"] = true;
			session_unset();	
		} else {
			$resArray =	$this->sSystem->_SESSION['reshash']; 
				
			$variables["check"] = "OK";
			
			$variables["ACK"] = $resArray['ACK'];
			$variables["CORRELATIONID"] = $resArray['CORRELATIONID'];
			$variables["VERSION"] = $resArray['VERSION'];

			$count=0;
			$e = null;
			while (isset($resArray["L_SHORTMESSAGE".$count])) {
				$e = new Enlight_Exception($resArray["L_SHORTMESSAGE".$count].' '.$resArray["L_LONGMESSAGE".$count], $resArray["L_ERRORCODE".$count], $e);
				$count++;
			}
			if($e!==null) {
				throw $e;
			}

			$count=0;
			while (isset($resArray["L_SHORTMESSAGE".$count])) {
				$paypalAPIError[$count]["errorCode"] = $resArray["L_ERRORCODE".$count];
				$paypalAPIError[$count]["shortMessage"] = $resArray["L_SHORTMESSAGE".$count];
				$paypalAPIError[$count]["longMessage"]  = $resArray["L_LONGMESSAGE".$count];
				$count=$count+1;
			}
			$variables["paypalAPIError"] = $paypalAPIError;
		}

		$this->View()->assign($variables);
	}
	
	public function guestAction()
	{
		$this->sSystem->_SESSION["sRegisterFinished"] = false;
			
		if (!$this->sSystem->sMODULES['sAdmin']->sCheckUser() || $this->sSystem->_SESSION['GuestUser'] == "1" )
		{
			$resArray = $this->sSystem->_SESSION['reshash'];
		
			// get countryId from DB
			$sql = "SELECT id FROM s_core_countries WHERE countryiso=UPPER(?)";
			$getCountryId = Shopware()->Db()->fetchOne($sql, array($resArray["SHIPTOCOUNTRYCODE"]));		

			// set POST parameters
			$this->sSystem->_POST['email'] = urldecode($resArray["EMAIL"]);
			$this->sSystem->_POST['skipLogin'] = "1";
			$this->sSystem->_POST['salutation'] = "mr";
			$this->sSystem->_POST['company'] = utf8_decode($resArray["BUSINESS"]);
			$this->sSystem->_POST['firstname'] = utf8_decode($resArray["FIRSTNAME"]);
			$this->sSystem->_POST['lastname'] = utf8_decode($resArray["LASTNAME"]);
			$this->sSystem->_POST['street'] = utf8_decode($resArray["SHIPTOSTREET"]);
			$this->sSystem->_POST['streetnumber'] = " ";
			$this->sSystem->_POST['zipcode'] = $resArray["SHIPTOZIP"];
			$this->sSystem->_POST['city'] = utf8_decode($resArray["SHIPTOCITY"]);
			$this->sSystem->_POST['country'] = $getCountryId;

			if (!empty($resArray["BUSINESS"])) {
				$this->sSystem->_POST['company'] = utf8_decode($resArray["BUSINESS"]);
			}
			
			// Check data 
			$checkData = ($this->sSystem->sMODULES['sAdmin']->sValidateStep1());
			
			if (!empty($checkData["sErrorMessages"])){				
				return $this->forward('error', null, null, array('error'=>'PayPal Express Error - ValidateStep 1'));
			}
			
			// Define field-rules
			$rules = array(
				"salutation"=>array("required"=>1),
				"company"=>array("addicted"=>array("salutation"=>"company"),"required"=>1),
				"firstname"=>array("required"=>1),
				"lastname"=>array("required"=>1),
				"street"=>array("required"=>1),
				"streetnumber"=>array("required"=>0),
				"zipcode"=>array("required"=>1),
				"city"=>array("required"=>1),
				"phone"=>array("required"=>0),
				"fax"=>array("required"=>0),
				"country"=>array("required"=>1),
				"department"=>array("required"=>0),
				"shippingAddress"=>array("required"=>0),
				//"ustid"=>array("required"=>0),
				"text1"=>array("required"=>0),
				"text2"=>array("required"=>0),
				"text3"=>array("required"=>0),
				"text4"=>array("required"=>0),
				"text5"=>array("required"=>0),
				"text6"=>array("required"=>0),
				"sValidation"=>array("required"=>0),
				"birthyear"=>array("required"=>0),
				"birthmonth"=>array("required"=>0),
				"birthday"=>array("required"=>0)
			);
			
					
			// Check data 
			$checkData = ($this->sSystem->sMODULES['sAdmin']->sValidateStep2($rules));
			
			if (!empty($checkData["sErrorMessages"])){
				return $this->forward('error', null, null, array('error'=>'PayPal Express Error - ValidateStep 2'));
			}

			// Define field-rules for Shipping Adress
			$rules = array(
				"salutation"=>array("required"=>1),
				"company"=>array("addicted"=>array("salutation"=>"company"),"required"=>1),
				"firstname"=>array("required"=>1),
				"lastname"=>array("required"=>1),
				"street"=>array("required"=>1),
				"streetnumber"=>array("required"=>0),
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
			
			//divide Firstname and Lastname from SHIPTONAME
			$SHIPTONAME = utf8_decode($resArray["SHIPTONAME"]);
			$shiptonameArray = explode(" ", $SHIPTONAME);
			
			$max = count($shiptonameArray)-1;
			
			for ($i = 0; $i < $max; $i++) {
				$SHIPTOFIRSTNAME.= $shiptonameArray[$i];
			}
				$SHIPTOLASTNAME = $shiptonameArray[$max];
			
			if (empty($SHIPTOFIRSTNAME)) $SHIPTOFIRSTNAME = " ";
			if (empty($SHIPTOLASTNAME)) $SHIPTOLASTNAME = " ";
			
			//set Firstname and Lastname for ShippingAdress
			$this->sSystem->_POST['firstname'] = $SHIPTOFIRSTNAME;
			$this->sSystem->_POST['lastname'] = $SHIPTOLASTNAME;

			// Check data 
			$checkData = ($this->sSystem->sMODULES['sAdmin']->sValidateStep2ShippingAddress($rules));
			
			if (!empty($checkData["sErrorMessages"])) {
				return $this->forward('error', null, null, array('error'=>'PayPal Express Error - ValidateStep ShippingAddress'));
			}

			// Go to Register User!
			return $this->forward('register');
		}
		
		$this->redirect(array('controller'=>'checkout', 'action'=>'confirm'));
	}
	
	public function registerAction()
	{		
		$sql = "SELECT id FROM s_core_paymentmeans WHERE class='paypalexpress.php'";
		$PaypalExpress = Shopware()->Db()->fetchOne($sql);			
		$this->sSystem->_SESSION['sPaymentID'] = $this->sSystem->_POST['sPayment'] = $PaypalExpress ? $PaypalExpress : 20;
		
		// Load class to check this paymentmean
		$checkData = $this->sSystem->sMODULES['sAdmin']->sValidateStep3();
		
		if (!empty($checkData["checkPayment"]["sErrorMessages"])){
			return $this->forward('error', null, null, array('error'=>'PayPal Express Error - Registration'));
		}
	
		// Alles roger
		$this->sSystem->_SESSION["sRegister"]["payment"]["object"] = $checkData["paymentData"];

		//If User has already an Guest account! update shipping adress
		if ($this->sSystem->sMODULES['sAdmin']->sCheckUser()) {				
			$this->sSystem->sMODULES['sAdmin']->sUpdateShipping();	
		} else {
			// Save registration and link to admin-viewport (2.do - consider ordering-process)
			$this->sSystem->sMODULES['sAdmin']->sSaveRegister($checkData["sPaymentObject"]);
		}
				
		// Update user account
		$this->sSystem->sMODULES['sAdmin']->sUpdatePayment();	

		$this->redirect(array('controller'=>'checkout', 'action'=>'confirm'));
	}
		
	public function pendingAction()
	{
	}
}