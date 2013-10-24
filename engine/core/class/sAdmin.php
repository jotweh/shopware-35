<?php
/**
 * Managing user sessions / payment / dispatches
 * @link http://www.shopware.de
 * @package core
 * @subpackage class
 * @copyright (C) Shopware AG 2011
 * @author st.hamann
 * @author h.lohaus
 * @version Shopware 3.5.0
 */
class sAdmin
{
	/**
     * Pointer to sSystem object
     *
     * @var object
     */
	var $sSYSTEM;
	
	 /** Logout user and destroy session
	 * @access public
	 * @return -
	 */
	public function sLogout (){
		if (Enlight()->Events()->notifyUntil('Shopware_Modules_Admin_Logout_Start', array('subject'=>$this))){
			return false;
		}

		unset($this->sSYSTEM->_SESSION);
		unset($_SESSION);
		unset($this->sSYSTEM->sUSERGROUPDATA);

		$this->sSYSTEM->sUSERGROUPDATA = $this->sSYSTEM->sDB_CONNECTION->GetRow("
		SELECT * FROM s_core_customergroups WHERE `groupkey` = 'EK'
		");

		$this->sSYSTEM->_SESSION["sUserGroup"] = "EK";
		$this->sSYSTEM->sUSERGROUP = "EK";
		session_destroy();
		session_regenerate_id();
	}
		
	 /**
	 * Deprecated, old vatid check
	 * @access public
	 * @return -
	 */
	public function sCheckTaxID($id, $country)
	{
		return array();
	}
	
	/**
	 * Checks vat id with webservice
	 * @access public
	 * @return array assoziative array with success / error codes
	 */
	public function sValidateVat()
	{
		if(empty($this->sSYSTEM->sCONFIG['sVATCHECKENDABLED']))
		{
			return array();
		}
		if(empty($this->sSYSTEM->_POST["ustid"])&&empty($this->sSYSTEM->sCONFIG['sVATCHECKREQUIRED']))
		{
			return array();
		}

		$messages = array();
		$ustid = preg_replace('#[^0-9A-Z\+\*\.]#','',strtoupper($this->sSYSTEM->_POST['ustid']));
		$country = $this->sSYSTEM->sDB_CONNECTION->GetOne(
			'SELECT countryiso FROM s_core_countries WHERE id=?',
			array($this->sSYSTEM->_POST['country'])
		);
		if(empty($this->sSYSTEM->_POST["ustid"]))
		{
			$messages[] = $this->sSYSTEM->sCONFIG['sSnippets']['sVatCheckErrorEmpty'];
		}
		elseif(empty($ustid)||!preg_match("#^([A-Z]{2})([0-9A-Z+*.]{2,12})$#", $ustid, $vat))
		{
			$messages[] = $this->sSYSTEM->sCONFIG['sSnippets']['sVatCheckErrorInvalid'];
		}
		elseif(empty($country)||$country!=$vat[1])
		{
			$field_names = explode(',',$this->sSYSTEM->sCONFIG['sSnippets']['sVatCheckErrorFields']);
			$field_name = isset($field_names[4]) ? $field_names[4] : 'Land';
			$messages[] = sprintf($this->sSYSTEM->sCONFIG['sSnippets']['sVatCheckErrorField'], $field_name);
		}
		elseif ($country=='DE')
		{
			
		}
		elseif(!empty($this->sSYSTEM->sCONFIG['sVATCHECKADVANCEDNUMBER']))
		{
			$data = array(
				'UstId_1' => $this->sSYSTEM->sCONFIG['sVATCHECKADVANCEDNUMBER'],
				'UstId_2' => $vat[1].$vat[2],
				'Firmenname' => '',
				'Ort' => '',
				'PLZ' => '',
				'Strasse' => '',
				'Druck' => empty($this->sSYSTEM->sCONFIG['sVATCHECKCONFIRMATION']) ? 'nein' : 'ja'
			);
			
			if(!empty($this->sSYSTEM->sCONFIG['sVATCHECKADVANCED'])
			  && strpos($this->sSYSTEM->sCONFIG['sVATCHECKADVANCEDCOUNTRIES'], $vat[1])!==false)
			{
				$data['Firmenname'] = $this->sSYSTEM->_POST['company'];
				$data['Ort'] = $this->sSYSTEM->_POST['city'];
				$data['PLZ'] = $this->sSYSTEM->_POST['zipcode'];
				$data['Strasse'] = $this->sSYSTEM->_POST['street'].' '.$this->sSYSTEM->_POST['streetnumber'];
			}
			
			$request = 'http://evatr.bff-online.de/evatrRPC?';
			$request .= http_build_query($data,'','&');
			
			$context = stream_context_create(array('http' => array(
			    'method' => 'GET',
			    'header' => 'Content-Type: text/html; charset=ISO-8859-1',
			    'timeout' => 5,
			    'user_agent'=> 'Shopware/'.$this->sSYSTEM->sCONFIG['sVERSION']
			)));
			$response = @file_get_contents($request, false, $context);
			
			$reg = '#<param>\s*<value><array><data>\s*<value><string>([^<]*)</string></value>\s*<value><string>([^<]*)</string></value>\s*</data></array></value>\s*</param>#msi';
			if(!empty($response)&&preg_match_all($reg,$response,$matches))
			{
				$response = array_combine($matches[1], $matches[2]);
				$messages = $this->sCheckVatResponse($response);
			}
			elseif(empty($this->sSYSTEM->sCONFIG['sVATCHECKNOSERVICE']))
			{
				$messages[] = sprintf($this->sSYSTEM->sCONFIG['sSnippets']['sVatCheckUnknownError'], 10);
			}
		}
		elseif(false&&class_exists('SoapClient'))
		{
			$url = 'http://ec.europa.eu/taxation_customs/vies/services/checkVatService.wsdl';
			if(!file_get_contents($url))
			{
				$messages[] = sprintf($this->sSYSTEM->sCONFIG['sSnippets']['sVatCheckUnknownError'], 11);
			}
			else
			{
				$client = new SoapClient($url, array('exceptions'=>0,'connection_timeout'=>5));
				$response = $client->checkVat(array('countryCode'=>$vat[1], 'vatNumber'=>$vat[2]));
			}
			if(is_soap_fault($response))
			{
				$messages[] = sprintf($this->sSYSTEM->sCONFIG['sSnippets']['sVatCheckUnknownError'], 12);
				if(!empty($this->sSYSTEM->sCONFIG['sVATCHECKDEBUG']))
				{
					$messages[] = "SOAP-Fehler: (Fehlercode: {$response->faultcode}, Fehlermeldung: {$response->faultstring})";
				}
			}
			elseif(empty($response->valid))
			{
				$messages[] = $this->sSYSTEM->sCONFIG['sSnippets']['sVatCheckErrorInvalid'];
			}
		}
		else
		{
			$messages[] = sprintf($this->sSYSTEM->sCONFIG['sSnippets']['sVatCheckUnknownError'], 20);
		}
		if(!empty($messages)&&empty($this->sSYSTEM->sCONFIG['sVATCHECKREQUIRED']))
		{
			$messages[] = $this->sSYSTEM->sCONFIG['sSnippets']['sVatCheckErrorInfo'];
		}
		$messages = Enlight()->Events()->filter('Shopware_Modules_Admin_CheckTaxID_MessagesFilter', $messages,
		  array('subject'=>$this,"post"=>$this->sSYSTEM->_POST)
		);
		return $messages;
	}

	/**
	 * Process answer from german vat webservice 
	 * @param  $response
	 * @return array
	 */
	public function sCheckVatResponse ($response)
	{
		if(!empty($this->sSYSTEM->sCONFIG['sVATCHECKNOSERVICE']))
		{
			if(in_array($response['ErrorCode'], array(999, 200, 205, 218, 208, 217, 219)))
			{
				return array();
			}
		}
		if(!empty($this->sSYSTEM->sCONFIG['sVATCHECKDEBUG']))
		{
			switch ($response['ErrorCode'])
			{
				case 200: break;
				case 201: $msg = 'Die eingegebene USt-IdNr. ist ung�ltig.'; break;
				case 202: $msg = 'Die eingegebene USt-IdNr. ist ung�ltig. Sie ist nicht in der Unternehmerdatei des betreffenden EU-Mitgliedstaates registriert.'; break;
				case 203: $msg = 'Die eingegebene USt-IdNr. ist ung�ltig. Sie ist erst ab dem '.$response['Gueltig_ab'].' g�ltig.'; break;
				case 204: $msg = 'Die eingegebene USt-IdNr. ist ung�ltig. Sie war im Zeitraum von '.$response['Gueltig_ab'].' bis '.$response['Gueltig_bis'].' g�ltig.'; break;
				case 209: $msg = 'Die eingegebene USt-IdNr. ist ung�ltig. Sie entspricht nicht dem Aufbau der f�r diesen EU-Mitgliedstaat gilt.'; break;
				case 210: $msg = 'Die eingegebene USt-IdNr. ist ung�ltig. Sie entspricht nicht den Pr�fziffernregeln die f�r diesen EU-Mitgliedstaat gelten.'; break;
				case 211: $msg = 'Die eingegebene USt-IdNr. ist ung�ltig. Sie enth�lt unzul�ssige Zeichen.'; break;
				case 212: $msg = 'Die eingegebene USt-IdNr. ist ung�ltig. Sie enth�lt ein unzul�ssiges L�nderkennzeichen.'; break;
				
				case 207: $msg = 'Ihnen wurde die deutsche USt-IdNr. ausschliesslich zu Zwecken der Besteuerung des innergemeinschaftlichen Erwerbs erteilt. Sie sind somit nicht berechtigt, Best�tigungsanfragen zu stellen.'; break;
				case 206: $msg = 'Ihre deutsche USt-IdNr. ist ung�ltig. Eine Best�tigungsanfrage ist daher nicht m�glich. Den Grund hierf�r k�nnen Sie beim Bundeszentralamt f�r Steuern - Dienstsitz Saarlouis - erfragen.'; break;
				case 208: $msg = 'F�r die von Ihnen angefragte USt-IdNr. l�uft gerade eine Anfrage von einem anderen Nutzer. Eine Bearbeitung ist daher nicht m�glich. Bitte versuchen Sie es sp�ter noch einmal.'; break;
				case 213: $msg = 'Die Abfrage einer deutschen USt-IdNr. ist nicht m�glich.'; break;
				case 214: $msg = 'Ihre deutsche USt-IdNr. ist fehlerhaft. Sie beginnt mit \'DE\' gefolgt von 9 Ziffern.'; break;
				case 215: $msg = 'Ihre Anfrage enth�lt nicht alle notwendigen Angaben f�r eine einfache Best�tigungsanfrage'; break;
				case 216: $msg = 'Ihre Anfrage enth�lt nicht alle notwendigen Angaben f�r eine qualifizierte Best�tigungsanfrage'; break;
				case 217: $msg = 'Bei der Verarbeitung der Daten aus dem angefragten EU-Mitgliedstaat ist ein Fehler aufgetreten. Ihre Anfrage kann deshalb nicht bearbeitet werden.'; break;
				case 218: $msg = 'Eine qualifizierte Best�tigung ist zur Zeit nicht m�glich.'; break;
				case 219: $msg = 'Bei der Durchf�hrung der qualifizierten Best�tigungsanfrage ist ein Fehler aufgetreten.'; break;
				case 220: $msg = 'Bei der Anforderung der amtlichen Best�tigungsmitteilung ist ein Fehler aufgetreten. Sie werden kein Schreiben erhalten.'; break;
				case 999: $msg = 'Eine Bearbeitung Ihrer Anfrage ist zurzeit nicht m�glich. Bitte versuchen Sie es sp�ter noch einmal.'; break;
				case 205: $msg = 'Ihre Anfrage kann derzeit durch den angefragten EU-Mitgliedstaat oder aus anderen Gr�nden nicht beantwortet werden'; break;
				
				default:  $msg = sprintf($this->sSYSTEM->sCONFIG['sSnippets']['sVatCheckUnknownError'], 30); break;
			}
		}
		else
		{
			switch ($response['ErrorCode'])
			{
				case 200: break;
				case 201: 
				case 202: 
				case 204:
				case 209:
				case 210:
				case 211:
				case 212: $msg = $this->sSYSTEM->sCONFIG['sSnippets']['sVatCheckErrorInvalid']; break;
				case 203: $msg = sprintf($this->sSYSTEM->sCONFIG['sSnippets']['sVatCheckErrorDate'], $response['Gueltig_ab']); break;
				default:  $msg = sprintf($this->sSYSTEM->sCONFIG['sSnippets']['sVatCheckUnknownError'], 31); break;
			}
		}
		$result = array();
		if(!empty($msg))
		{
			$result[] = $msg;
		}
		else
		{
			$fields = array('Erg_Name', 'Erg_Ort', 'Erg_PLZ', 'Erg_Str');
			$field_names = explode(',',$this->sSYSTEM->sCONFIG['sSnippets']['sVatCheckErrorFields']);
			foreach ($fields as $key=>$field)
			{
				if(isset($response[$field]) && strpos($this->sSYSTEM->sCONFIG['sVATCHECKVALIDRESPONSE'], $response[$field]) === false)
				{
					$name = isset($field_names[$key]) ? $field_names[$key] : $field;
					$result[] = sprintf($this->sSYSTEM->sCONFIG['sSnippets']['sVatCheckErrorField'], $name);
				}
			}
		}
		return $result;
	}
	 /**
	 * Get data from a certain payment
	 * @param int $id - s_core_paymentmeans.id 
	 * @param array $user - array with user data (sGetUserData)
	 * @access public
	 * @return array - payment data
	 */
	public function sGetPaymentMeanById($id,$user=false){
		$id = intval($id);
		$sql = "
		SELECT * FROM s_core_paymentmeans 
		WHERE id=?
		";
		$data =  $this->sSYSTEM->sDB_CONNECTION->GetRow($sql,array($id));
		
		
		if ($this->sSYSTEM->sMODULES['sBasket']->sCheckForESD()){
			$sEsd = true;
		}
		
		if (!count($user)){
			$user = array();
		}
		
		if (!count($this->sSYSTEM->sMODULES['sBasket']->sBASKET)){
			$this->sSYSTEM->sMODULES['sBasket']->sBASKET = $this->sSYSTEM->sMODULES['sBasket']->sGetBasket();
		}
		
		$basket = $this->sSYSTEM->sMODULES['sBasket']->sBASKET;
	
		// Check for risk-management
		// If rule matches, reset to default paymentmean if this paymentmean was not
		// set by shop-owner
		
		// Hide paymentmeans which are not active
		if (!$data["active"] && $data["id"]!=$user["additional"]["user"]["paymentpreset"]){
			$resetPayment = $this->sSYSTEM->sCONFIG["sPAYMENTDEFAULT"];
		}
		
		// If esd - order, hide payment-means, whih
		// are not accessible for esd
		if (!$data["esdactive"] && $sEsd){
			$resetPayment = $this->sSYSTEM->sCONFIG["sPAYMENTDEFAULT"];
		}
		
		// Check additional rules
		if ($this->sManageRisks($data["id"],$basket,$user) && $data["id"]!=$user["additional"]["user"]["paymentpreset"]){
			$resetPayment = $this->sSYSTEM->sCONFIG["sPAYMENTDEFAULT"];
		}
		
		if(!empty($user['additional']['countryShipping']['id'])) {
			$sql = "
				SELECT 1
				FROM s_core_paymentmeans p
				
				LEFT JOIN s_core_paymentmeans_subshops ps
				ON ps.subshopID=?
				AND ps.paymentID=p.id
				
				LEFT JOIN s_core_paymentmeans_countries pc
				ON pc.countryID=?
				AND pc.paymentID=p.id
				
				WHERE (ps.paymentID IS NOT NULL OR (SELECT paymentID FROM s_core_paymentmeans_subshops WHERE paymentID=p.id LIMIT 1) IS NULL)
				AND (pc.paymentID IS NOT NULL OR (SELECT paymentID FROM s_core_paymentmeans_countries WHERE paymentID=p.id LIMIT 1) IS NULL)
				
				AND id=?
			";
			$active = $this->sSYSTEM->sDB_CONNECTION->GetOne($sql, array(
				$this->sSYSTEM->sSubShop['id'],
				$user['additional']['countryShipping']['id'],
				$id
			));
			if(empty($active)) {
				$resetPayment = $this->sSYSTEM->sCONFIG["sPAYMENTDEFAULT"];
			}
		}
		
		if ($resetPayment && $user["additional"]["user"]["id"]){
			$updateAccount =  $this->sSYSTEM->sDB_CONNECTION->Execute("
			UPDATE s_user SET paymentID = ? WHERE id = ?
			",array($resetPayment,$user["additional"]["user"]["id"]));
			$sql = "
			SELECT * FROM s_core_paymentmeans 
			WHERE id=?
			";
			$data =  $this->sSYSTEM->sDB_CONNECTION->GetRow($sql,array($resetPayment));
		}
		
		// Get Translation
		$data = $this->sGetPaymentTranslation($data);

		$data = Enlight()->Events()->filter('Shopware_Modules_Admin_GetPaymentMeanById_DataFilter', $data, array('subject'=>$this,"id"=>$id,"user"=>$user));
		
		return $data;
	}
	
	 /**
	 * Get all available payments
	 * 
	 * @access public
	 * @return array - payments data
	 */
	public function sGetPaymentMeans(){
		
		
		/*
			Get User / Basket-Data
		*/
		if (!count($this->sSYSTEM->sMODULES['sBasket']->sBASKET)){
			$this->sSYSTEM->sMODULES['sBasket']->sBASKET = $this->sSYSTEM->sMODULES['sBasket']->sGetBasket();
		}
		
		$basket = $this->sSYSTEM->sMODULES['sBasket']->sBASKET;
		
		$user = $this->sGetUserData();
		
		if ($this->sSYSTEM->sMODULES['sBasket']->sCheckForESD()){
			$sEsd = true;
		}else {
			$sEsd = false;
		}
		
		$countryID = (int) $user['additional']['countryShipping']['id'];
		$subshopID = (int) $this->sSYSTEM->sSubShop['id'];
		if (empty($countryID)){
			$countryID = $this->sSYSTEM->sDB_CONNECTION->GetOne("
			SELECT id FROM s_core_countries ORDER BY position ASC LIMIT 1
			");
		}
		$sql = "
			SELECT p.*
			FROM s_core_paymentmeans p
			
			LEFT JOIN s_core_paymentmeans_subshops ps
			ON ps.subshopID=$subshopID
			AND ps.paymentID=p.id
			
			LEFT JOIN s_core_paymentmeans_countries pc
			ON pc.countryID=$countryID
			AND pc.paymentID=p.id
			
			WHERE (ps.paymentID IS NOT NULL OR (SELECT paymentID FROM s_core_paymentmeans_subshops WHERE paymentID=p.id LIMIT 1) IS NULL)
			AND (pc.paymentID IS NOT NULL OR (SELECT paymentID FROM s_core_paymentmeans_countries WHERE paymentID=p.id LIMIT 1) IS NULL)
			
			ORDER BY position, name
		";

		
		$getPaymentMeans = $this->sSYSTEM->sDB_CONNECTION->GetAll($sql);
		
		if($getPaymentMeans===false)
		{
			$sql = "SELECT * FROM s_core_paymentmeans ORDER BY position, name";
			$getPaymentMeans = $this->sSYSTEM->sDB_CONNECTION->GetAll($sql);
		}
		
		foreach ($getPaymentMeans as $payKey => $payValue) {

				// Hide paymentmeans which are not active
				if (empty($payValue["active"]) && $payValue["id"]!=$user["additional"]["user"]["paymentpreset"]){
					unset($getPaymentMeans[$payKey]);
					continue;
				}
				
				// If esd - order, hide payment-means, whih
				// are not accessible for esd
				if (empty($payValue["esdactive"]) && $sEsd){
					unset($getPaymentMeans[$payKey]);
					continue;
				}
				
				// Check additional rules
				if ($this->sManageRisks($payValue["id"],$basket,$user) && $payValue["id"]!=$user["additional"]["user"]["paymentpreset"]){
					unset($getPaymentMeans[$payKey]);
					continue;
				}
				
				// Get possible translation
				$getPaymentMeans[$payKey] = $this->sGetPaymentTranslation($getPaymentMeans[$payKey]);

		}
	
		if (!count($getPaymentMeans)){
			$this->sSYSTEM->E_CORE_WARNING("sGetPaymentMeans #00","Could not get any payment-means".$sql);
			return;
		}

		$getPaymentMeans = Enlight()->Events()->filter('Shopware_Modules_Admin_GetPaymentMeans_DataFilter', $getPaymentMeans, array('subject'=>$this));
		
		return $getPaymentMeans;
		
	}
	
	/**
	 * Loads the system class of the specified payment (engine/core/class/paymentmeans)
	 * @param array $paymentData - Array with payment data
	 * @access public
	 * @return object or false - 
	 */
	public function sInitiatePaymentClass($paymentData){
		include_once("paymentmeans/".$paymentData['class']);
			$sPaymentObject = new sPaymentMean();
			$sPaymentObject->sSYSTEM = &$this->sSYSTEM;
			
			if (!$sPaymentObject){
				$this->sSYSTEM->E_CORE_WARNING("sValidateStep3 #02","Payment-Class not found");
				return false;
			}else {
				return $sPaymentObject;
			}
			
	}
	
	 /**
	 * Last step of the registration - validate all user fields that exists in session and
	 * stores the data into database 
	 * @param array $paymentmeans - Array with payment data
	 * @access public
	 * @return -
	 */
	public function sValidateStep3 ($paymentmeans = array())
	{
		if (empty($this->sSYSTEM->_POST['sPayment'])){
			$this->sSYSTEM->E_CORE_WARNING("sValidateStep3 #00","No payment-id");
			return;
		}
	
		$user = $this->sGetUserData();
		$paymentData = $this->sGetPaymentMeanById($this->sSYSTEM->_POST['sPayment'],$user);
		
		if (!count($paymentData)){
			$this->sSYSTEM->E_CORE_ERROR("sValidateStep3 #01","Could not load paymentmean");
			return;
		}else {
			// Include management-class and check input-data
			if(!empty($paymentData['class'])) {
				$sPaymentObject = $this->sInitiatePaymentClass($paymentData);
				$checkPayment = $sPaymentObject->sInit($this->sSYSTEM);
			}
			return array("checkPayment"=>$checkPayment,"paymentData"=>$paymentData,"sProcessed"=>true,"sPaymentObject"=>&$sPaymentObject);
		}
		return;
	}
	
    /**
	 * Updates the billing address of the user
	 * 
	 * @access public
	 * @return bool 
	 */
	public function sUpdateBilling (){
		
		$userObject = $this->sSYSTEM->_POST;
		
		if (!empty($userObject['birthmonth']) && !empty($userObject['birthday']) && !empty($userObject['birthyear']))
		{
			$userObject['birthday'] = mktime(0,0,0, (int) $userObject['birthmonth'], (int) $userObject['birthday'], (int) $userObject['birthyear']);
			if($userObject['birthday']>0)
			{
				$userObject['birthday'] = date('Y-m-d', $userObject['birthday']);
			}
			else
			{
				$userObject['birthday'] = '0000-00-00';
			}
		}
		else
		{
			unset($userObject['birthday']);
		}
		
		$fields = array(
			'company',
			'department',
			'salutation',
			'firstname',
			'lastname',
			'street',
			'streetnumber',
			'zipcode',
			'city',
			'phone',
			'fax',
			'countryID',
			'ustid',
			'text1',
			'text2',
			'text3',
			'text4',
			'text5',
			'text6',
			'birthday'
		);
		
		$data = array();
		foreach ($fields as $field)
		{
			if(isset($userObject[$field]))
			{
				$data[$field] = $userObject[$field];
			}
		}
		
		$data["countryID"] = $userObject["country"];
		
		$where = array(
			'userID='.(int) $this->sSYSTEM->_SESSION['sUserId']
		);		
		
		list($data,$where) = Enlight()->Events()->filter('Shopware_Modules_Admin_UpdateBilling_FilterSql', array($data,$where), array('subject'=>$this,"id"=>$this->sSYSTEM->_SESSION['sUserId'],"user"=>$userObject));
		
		$result = Shopware()->Db()->update('s_user_billingaddress', $data, $where);

		
		if ($this->sSYSTEM->sDB_CONNECTION->ErrorMsg()){
			$this->sSYSTEM->E_CORE_WARNING("sUpdateBilling #01","Could not save data (billing-adress)".$this->sSYSTEM->sDB_CONNECTION->ErrorMsg());
			return false;
		}
		return true; 
	}
	
	 /**
	 * Add, remove customer from maillinglist
	 * @param bool $status 1 = Insert, 0 = Delete
	 * @param string $email - mail address
	 * @access public
	 * @return -
	 */
	public function sUpdateNewsletter ($status,$email,$customer=false){
		if (!$status){
			// Delete
			$changeLetterState = $this->sSYSTEM->sDB_CONNECTION->Execute("
			DELETE FROM s_campaigns_mailaddresses WHERE email=?
			",array($email));
		}else {
			$groupID = $this->sSYSTEM->sCONFIG['sNEWSLETTERDEFAULTGROUP'];
			if (!$groupID) $groupID = "0";
			// Insert
			if (!empty($customer)){
				$changeLetterState = $this->sSYSTEM->sDB_CONNECTION->Execute("
				INSERT INTO s_campaigns_mailaddresses (customer, email)
				VALUES (?,?)
				",array(1,$email));
			}else {
				$changeLetterState = $this->sSYSTEM->sDB_CONNECTION->Execute("
				INSERT INTO s_campaigns_mailaddresses (groupID, email)
				VALUES (?,?)
				",array($groupID,$email));
			}
			
		}
	}
	
	
	 /**
	 * Set previous used address to default address
	 * @param string $type  shipping / billing
	 * @param string $request_hash  secure hash
	 * @access public
	 * @return -
	 */
	public function sGetPreviousAddresses($type, $request_hash=null)
	{
		if (empty($type)) return false;
		if (empty($this->sSYSTEM->_SESSION['sUserId'])) return false;
		
		$id = (int)$this->sSYSTEM->_SESSION['sUserId'];
		
		if ($type=='shipping') {
			$type = 'shipping';
		} else {
			$type = 'billing';
		}
		
		$sql = '
			SELECT
				MD5(CONCAT(company, department, salutation, firstname, lastname, street, streetnumber, zipcode, city, countryID)) as hash,
				company, department, salutation, firstname, lastname,
				street, streetnumber, zipcode, city, countryID as country, countryID, countryname
			FROM s_order_'.$type.'address AS a
			LEFT JOIN s_core_countries co
			ON a.countryID=co.id
			WHERE a.userID=?
			GROUP BY hash
			ORDER BY MAX(a.id) DESC
		';
		
		$addresses = $this->sSYSTEM->sDB_CONNECTION->GetAll($sql, array($this->sSYSTEM->_SESSION['sUserId']));
		
		foreach ($addresses as $address)
		{
			if(!empty($request_hash)&&$address['hash']==$request_hash)
			{
				return $address;
			}
			$address[$address['hash']]['country'] = array();
			$address[$address['hash']]['country']['id'] = $addressValue['countryID'];
			$address[$address['hash']]['country']['countryname'] = $addressValue['countryname'];
			$address[$address['hash']]['country'] = $this->sGetCountryTranslation($country);
		}
		
		if(!empty($request_hash))
		{
			return false;
		}
		
		return $addresses;
	}
	
	 /**
	 * Updates the delivery address of the user
	 * 
	 * @access public
	 * @return bool 
	 */
	public function sUpdateShipping (){
		$userObject = $this->sSYSTEM->_POST;
		
		if (empty($this->sSYSTEM->_SESSION["sUserId"])){
			return false;	
		}
				
		$sql = 'SELECT id FROM s_user_shippingaddress WHERE userID=?';
		$shippingID = Shopware()->Db()->fetchOne($sql, array($this->sSYSTEM->_SESSION['sUserId']));
		
		$fields = array(
			'company',
			'department',
			'salutation',
			'firstname',
			'lastname',
			'street',
			'streetnumber',
			'zipcode',
			'city',
			'countryID',
			'text1',
			'text2',
			'text3',
			'text4',
			'text5',
			'text6'
		);
		
		$data = array();
		foreach ($fields as $field)
		{
			if(isset($userObject[$field]))
			{
				$data[$field] = $userObject[$field];
			}
		}
		$data["countryID"] = isset($userObject["country"]) ? $userObject["country"] : 0;
		
		list($data,$where) = Enlight()->Events()->filter('Shopware_Modules_Admin_UpdateShipping_FilterSql', array($data,$where), array('subject'=>$this,"id"=>$this->sSYSTEM->_SESSION['sUserId'],"user"=>$userObject));
		
		if(empty($shippingID))
		{
			$data["userID"] = (int)$this->sSYSTEM->_SESSION['sUserId'];
			$result = Shopware()->Db()->insert('s_user_shippingaddress', $data, $where);
		}
		else
		{
			
			$where = array('id='.(int)$shippingID);		
			$result = Shopware()->Db()->update('s_user_shippingaddress', $data, $where);
		}
		
		if ($this->sSYSTEM->sDB_CONNECTION->ErrorMsg()){
			$this->sSYSTEM->E_CORE_WARNING("sUpdateShipping #01","Could not save data (billing-adress)".$this->sSYSTEM->sDB_CONNECTION->ErrorMsg());
			return false;
		}
		return true;
	}
	
	 /**
	 * Updates the payment of the user
	 * 
	 * @access public
	 * @return bool 
	 */
	public function sUpdatePayment (){
		if (empty($this->sSYSTEM->_SESSION["sUserId"])){
			return false;
		}
		$sqlPayment = "
		UPDATE s_user SET paymentID=? WHERE id=?";
		
		$sqlPayment = Enlight()->Events()->filter('Shopware_Modules_Admin_UpdatePayment_FilterSql', $sqlPayment, array('subject'=>$this,"id"=>$this->sSYSTEM->_SESSION['sUserId']));
		
		$saveUserData = $this->sSYSTEM->sDB_CONNECTION->Execute($sqlPayment,array($this->sSYSTEM->_POST["sPayment"],$this->sSYSTEM->_SESSION["sUserId"]));
		
		if ($this->sSYSTEM->sDB_CONNECTION->ErrorMsg()){
			$this->sSYSTEM->E_CORE_WARNING("sUpdatePayment #01","Could not save data (payment)".$this->sSYSTEM->sDB_CONNECTION->ErrorMsg());
			return false;
		}
		return true;
	}
	
	 /**
	 * Update  email address and password of the user
	 * 
	 * @access public
	 * @return bool 
	 */
	public function sUpdateAccount (){
		$p = $this->sSYSTEM->_POST;
		
		$email = strtolower($p["email"]);
		
		$password = $p["password"];
		$passwordConfirmation = $p["passwordConfirmation"];
		
	
		if ($password && $passwordConfirmation){
			$password = md5($password);
			$this->sSYSTEM->_SESSION["sUserMail"] = $email;
			$this->sSYSTEM->_SESSION["sUserPassword"] = $password;
			$sqlAccount = "
			UPDATE s_user SET email=?, password=? WHERE id=?";
			$sqlAccount = Enlight()->Events()->filter('Shopware_Modules_Admin_UpdateAccount_FilterPasswordSql', $sqlAccount, array('email'=>$email,'password'=>$password,'subject'=>$this,"id"=>$this->sSYSTEM->_SESSION['sUserId']));
		
			$saveUserData = $this->sSYSTEM->sDB_CONNECTION->Execute($sqlAccount,array($email,$password,$this->sSYSTEM->_SESSION["sUserId"]));
		}else {
			$this->sSYSTEM->_SESSION["sUserMail"] = $email;
			$sqlAccount = "
			UPDATE s_user SET email=? WHERE id=?";
			$sqlAccount = Enlight()->Events()->filter('Shopware_Modules_Admin_UpdateAccount_FilterEmailSql', $sqlAccount, array('email'=>$email,'password'=>$password,'subject'=>$this,"id"=>$this->sSYSTEM->_SESSION['sUserId']));
		
			$saveUserData = $this->sSYSTEM->sDB_CONNECTION->Execute($sqlAccount,array($email,$this->sSYSTEM->_SESSION["sUserId"]));
		}
		

		if ($this->sSYSTEM->sDB_CONNECTION->ErrorMsg()){
			$this->sSYSTEM->E_CORE_WARNING("sUpdateAccount #01","Could not save data (account)".$this->sSYSTEM->sDB_CONNECTION->ErrorMsg());
			return false;
		}
		return true;
		
	}
	
	 /**
	 * Checks the billing address (Registration Step 2)
	 * @param array $rules Defined rules / required fields from controller (register.php)
	 * @param boolean $edit 
	 * @access public
	 * @return array Array with errors that may have occurred
	 */
	public function sValidateStep2 ($rules,$edit=false){
		
		$sErrorMessages = array();
		$sErrorFlag = array();
		
		list($sErrorMessages,$sErrorFlag) = Enlight()->Events()->filter('Shopware_Modules_Admin_ValidateStep2_FilterStart', array($sErrorMessages,$sErrorFlag), array('edit'=>$edit,'rules'=>$rules,'subject'=>$this,"post"=>$this->sSYSTEM->_POST));

		$p = $this->sSYSTEM->_POST;

		foreach ($rules as $ruleKey => $ruleValue){
			if (empty($p[$ruleKey]) && !empty($rules[$ruleKey]["required"]) && empty($rules[$ruleKey]["addicted"])){
				$sErrorFlag[$ruleKey] = true;
			}
		}
		
		if (count($sErrorFlag)){
			// Some error occurs
			$sErrorMessages[] = $this->sSYSTEM->sCONFIG['sErrors']['sErrorBillingAdress'];
		}
		
		if(isset($rules['ustid'])) {
			$sVatMessages = $this->sValidateVat();
			if(!empty($sVatMessages)) {
				$sErrorFlag["ustid"] = true;
				$sErrorMessages = array_merge($sErrorMessages, $sVatMessages);
			}
		}

		if (!$edit){
			if (!count($sErrorMessages)){
				foreach ($rules as $ruleKey => $ruleValue){
					$this->sSYSTEM->_SESSION["sRegister"]["billing"][$ruleKey] = $p[$ruleKey];
				}
			}else {
				foreach ($rules as $ruleKey => $ruleValue){
					unset($this->sSYSTEM->_SESSION["sRegister"]["billing"][$ruleKey]);
				}
			}
		}
		list($sErrorMessages,$sErrorFlag) = Enlight()->Events()->filter('Shopware_Modules_Admin_ValidateStep2_FilterResult', array($sErrorMessages,$sErrorFlag), array('edit'=>$edit,'rules'=>$rules,'subject'=>$this,"post"=>$this->sSYSTEM->_POST));

		return array("sErrorFlag"=>$sErrorFlag,"sErrorMessages"=>$sErrorMessages);
	}
	
	 /**
	 * Checks the shipping address (Registration)
	 * @param array $rules Defined rules / required fields from controller (register.php)
	 * @param boolean $edit 
	 * @access public
	 * @return array Array with errors that may have occurred
	 */
	public function sValidateStep2ShippingAddress ($rules, $edit=false){
		
		$p = $this->sSYSTEM->_POST;
		
		foreach ($rules as $ruleKey => $ruleValue){
			if ($rules[$ruleKey]["addicted"]){
				$addictedField = array_keys($rules[$ruleKey]["addicted"]);
				if ($p[$addictedField[0]]==$rules[$ruleKey]["addicted"][$addictedField[0]] && !$p[$ruleKey]){
					$sErrorFlag[$ruleKey] = true;
				}
			}else  {
				if (!$p[$ruleKey] && $rules[$ruleKey]["required"]){
					$sErrorFlag[$ruleKey] = true;
				}
				
				// Fix, to support billing- and shipping-address in one step
				if (preg_match("/SHIPPING/",$ruleKey)){
					$clearedRuleKey = str_replace("SHIPPING","",$ruleKey);
					//unset($rules[$ruleKey]);
					$p[$clearedRuleKey] = $p[$ruleKey];
					$rules[$clearedRuleKey] = $rules[$ruleKey];
					unset($rules[$ruleKey]);
				}
				// --
			}
		}
		
		if (count($sErrorFlag)){
			// Some error occurs
			$sErrorMessages[] = $this->sSYSTEM->sCONFIG['sErrors']['sErrorShippingAddress'];
		}
		if (!$edit){
			if (!count($sErrorMessages)){
				foreach ($rules as $ruleKey => $ruleValue){
					$this->sSYSTEM->_SESSION["sRegister"]["shipping"][$ruleKey] = $p[$ruleKey];
				}
			}else {
				foreach ($rules as $ruleKey => $ruleValue){
					unset($this->sSYSTEM->_SESSION["sRegister"]["shipping"][$ruleKey]);
				}
			}
		}
		list($sErrorMessages,$sErrorFlag) = Enlight()->Events()->filter('Shopware_Modules_Admin_ValidateStep2Shipping_FilterResult', array($sErrorMessages,$sErrorFlag), array('edit'=>$edit,'rules'=>$rules,'subject'=>$this,"post"=>$this->sSYSTEM->_POST));

		return array("sErrorFlag"=>$sErrorFlag,"sErrorMessages"=>$sErrorMessages);
	}

	 /**
	 * Validate accont information (register)
	 * @param boolean $edit
	 * @access public
	 * @return array Array with errors that may have occurred
	 */
	public function sValidateStep1 ($edit = false)
	{
		$p = $this->sSYSTEM->_POST;
				
		if(isset($p["emailConfirmation"]) || isset($p["email"])) {
			$p["email"] = strtolower($p["email"]);
			// Check email
			if (empty($p["email"]) || !preg_match("/^.+@.+\\..+$/", $p["email"])){
				$sErrorFlag["email"] = true;
				$sErrorMessages[] = $this->sSYSTEM->sCONFIG['sErrors']['sErrorEmail'];
			}
			
			// Check email confirmation if needed
			if(isset($p["emailConfirmation"])) {
				$p["emailConfirmation"] = strtolower($p["emailConfirmation"]);
				if($p["email"] != $p["emailConfirmation"]){
					$sErrorFlag["emailConfirmation"] = true;
					$sErrorMessages[] = $this->sSYSTEM->sCONFIG['sErrors']['sErrorEmailNotEqual'];
				}
			}
		} elseif($edit && empty($p["email"])) {
			$this->sSYSTEM->_POST["email"] = $p["email"] = $this->sSYSTEM->_SESSION["sUserMail"];
		}
		
		if (empty($this->sSYSTEM->_SESSION['sRegister']))
		{
			$this->sSYSTEM->_SESSION['sRegister'] = new ArrayObject(array(), ArrayObject::ARRAY_AS_PROPS);
		}
		
		// Check password if account should be created
		if (!$p["skipLogin"] || $edit){
			if ($edit && (!$p["password"] && !$p["passwordConfirmation"])){
				
			}else {
				if (!$p["password"] || !$p["passwordConfirmation"] || (strlen($p["password"])<$this->sSYSTEM->sCONFIG['sMINPASSWORD'])){
					if(Shopware()->Config()->TemplateOld) { 
						$sErrorMessages[] = $this->sSYSTEM->sCONFIG['sErrors']['sErrorPassword'];
					}else {
						$sErrorMessages[] = Shopware()->Snippets()->getSnippet()->get('RegisterPasswordLength','',true);
					}
					$sErrorFlag["password"] = true;
					$sErrorFlag["passwordConfirmation"] = true;
				} elseif ($p["password"]!=$p["passwordConfirmation"])  {
					$sErrorMessages[] = Shopware()->Snippets()->getSnippet()->get('AccountPasswordNotEqual', 'Die Passw�rter stimmen nicht �berein.', true);
					$sErrorFlag["password"] = true;
					$sErrorFlag["passwordConfirmation"] = true;
				}
			}
			$this->sSYSTEM->_SESSION["sRegister"]["auth"]["accountmode"] = "0";	// Setting account-mode to ACCOUNT
		}else {
			// Assign random password to account
			$p["password"] = md5(uniqid(rand()));
			$this->sSYSTEM->_SESSION["sRegister"]["auth"]["accountmode"] = "1";	// Setting account-mode to NO_ACCOUNT
		}
		
		// Check if email is already registered
		
		if (isset($p["email"]) && ($p["email"]!=$this->sSYSTEM->_SESSION["sUserMail"])){
			$checkIfMailExists = $this->sSYSTEM->sDB_CONNECTION->GetRow("SELECT id FROM s_user WHERE email=? AND accountmode!=1",array($p["email"]));
			if (!empty($checkIfMailExists) && !$p["skipLogin"]){
				$sErrorFlag["email"] = true;
				$sErrorMessages[] = $this->sSYSTEM->sCONFIG['sErrors']['sErrorEmailForgiven'];
			}
		}
		
		// Save data in session
		if (!$edit){
			if (!count($sErrorFlag) && !count($sErrorMessages)){
				
				$this->sSYSTEM->_SESSION["sRegister"]["auth"]["email"] = $p["email"];
				// Receive Newsletter yes / no
				$this->sSYSTEM->_SESSION["sRegister"]["auth"]["receiveNewsletter"] = $p["receiveNewsletter"];
				if ($p["password"]){
					$this->sSYSTEM->_SESSION["sRegister"]["auth"]["password"] = md5($p["password"]);
				}else {
					unset($this->sSYSTEM->_SESSION["sRegister"]["auth"]["password"]);
				}
			}else {
				unset ($this->sSYSTEM->_SESSION["sRegister"]["auth"]["email"]);
				unset ($this->sSYSTEM->_SESSION["sRegister"]["auth"]["password"]);
			}
		}
		
		list($sErrorMessages,$sErrorFlag) = Enlight()->Events()->filter('Shopware_Modules_Admin_ValidateStep1_FilterResult', array($sErrorMessages,$sErrorFlag), array('edit'=>$edit,'subject'=>$this,"post"=>$this->sSYSTEM->_POST));

		return array("sErrorFlag"=>$sErrorFlag,"sErrorMessages"=>$sErrorMessages);
	}
	
	 /**
	 * Frontend user login
	 * @param boolean $ignoreAccountMode Allows customers who have chosen the fast registration, one-time login after registration
	 * @access public
	 * @return array Array with errors that may have occurred
	 */
	public function sLogin ($ignoreAccountMode = false){
		if (Enlight()->Events()->notifyUntil('Shopware_Modules_Admin_Login_Start', array('subject'=>$this,"ignoreAccountMode"=>$ignoreAccountMode,"post"=>$this->sSYSTEM->_POST))){
			return false;
		}
		
		
		// If fields are not set, markup these fields
		if (!$this->sSYSTEM->_POST["email"]) $sErrorFlag["email"] = true;
		if (!$this->sSYSTEM->_POST["password"] && !$this->sSYSTEM->_POST["passwordMD5"]) $sErrorFlag["password"] = true;
		
		if (count($sErrorFlag)){
			 $sErrorMessages[] = $this->sSYSTEM->sCONFIG['sErrors']['sErrorLogin'];
			 unset($this->sSYSTEM->_SESSION["sUserMail"]);
			 unset($this->sSYSTEM->_SESSION["sUserPassword"]);
			 unset($this->sSYSTEM->_SESSION["sUserId"]);
		}
		
		if (!count($sErrorMessages)){
			// If password is already md5-decrypted, use these one
			$password = $this->sSYSTEM->_POST["passwordMD5"] ? $this->sSYSTEM->_POST["passwordMD5"] : md5($this->sSYSTEM->_POST["password"]);
			$email = strtolower($this->sSYSTEM->_POST["email"]);

			if ($ignoreAccountMode){
				$sql = "SELECT id, customergroup FROM s_user WHERE password=? AND email=? AND active=1 AND lockeduntil < now()";
			}else {
				$sql = "SELECT id, customergroup FROM s_user WHERE password=? AND email=? AND active=1 AND accountmode!=1 AND lockeduntil < now()";
			}
			
			$getUser = $this->sSYSTEM->sDB_CONNECTION->GetRow($sql,array($password,$email));
			
			if(count($getUser))
			{			
				$updateTime = $this->sSYSTEM->sDB_CONNECTION->Execute("UPDATE s_user SET lastlogin=NOW(),failedlogins = 0, lockeduntil = '', sessionID=? WHERE id=?",array($this->sSYSTEM->sSESSION_ID,$getUser["id"]));
				Enlight()->Events()->notify('Shopware_Modules_Admin_Login_Successful', array('subject'=>$this,'email'=>$email,'password'=>$password,'user'=>$getUser));
				$this->sSYSTEM->_SESSION["sUserMail"] = $email;
				$this->sSYSTEM->_SESSION["sUserPassword"] = $password;
				$this->sSYSTEM->_SESSION["sUserId"] = $getUser["id"];
				
				$this->sCheckUser();
				
			}else {
				// Check if account is disabled
				 $sql = "SELECT id FROM s_user WHERE password=? AND email=? AND active=0";
				 $getUser = $this->sSYSTEM->sDB_CONNECTION->GetOne($sql,array($password, $email));
				 if ($getUser){
				 	$sErrorMessages[] = $this->sSYSTEM->sCONFIG['sErrors']['sErrorLoginActive'];
				 } else {
					$getLockedUntilTime = Shopware()->Db()->fetchOne("
						SELECT 1 FROM s_user
						WHERE email = ? AND lockeduntil > NOW()
					",array($email));
					if (!empty($getLockedUntilTime)){
						$sErrorMessages[] = $this->sSYSTEM->sCONFIG['sErrors']['sErrorLoginLocked'];
					}else {
			 	 		$sErrorMessages[] = $this->sSYSTEM->sCONFIG['sErrors']['sErrorLogin'];
					}
				 }

				 // Ticket #5427 - Prevent brute force logins
				 if (!empty($email)){
					// Update failed login counter
					$sql = "
						UPDATE s_user SET
							failedlogins = failedlogins + 1,
							lockeduntil = IF(
								failedlogins > 4,
								DATE_ADD(NOW(), INTERVAL (failedlogins + 1) * 30 SECOND),
								'0000-00-00 00:00:00'
							)
						WHERE email = ?
					";
					Shopware()->Db()->query($sql, array($email));
				 } // Ticket #5427 - Prevent brute force logins

				 Enlight()->Events()->notify('Shopware_Modules_Admin_Login_Failure', array('subject'=>$this,'email'=>$email,'password'=>$password,'error'=>$sErrorMessages));
				
				 unset($this->sSYSTEM->_SESSION["sUserMail"]);
				 unset($this->sSYSTEM->_SESSION["sUserPassword"]);
				 unset($this->sSYSTEM->_SESSION["sUserId"]);
			} // user was found
		} // All fields fill 
		
		list($sErrorMessages,$sErrorFlag) = Enlight()->Events()->filter('Shopware_Modules_Admin_Login_FilterResult', array($sErrorMessages,$sErrorFlag), array('subject'=>$this,'email'=>$email,'password'=>$password,'error'=>$sErrorMessages));

		return array("sErrorFlag"=>$sErrorFlag,"sErrorMessages"=>$sErrorMessages);
	}
	
	 /**
	 * Verification of user authorization (logged in) on all secured pages (checkout,account)
	 * @access public
	 * @return boolean
	 */
	public function sCheckUser (){
		
		if (Enlight()->Events()->notifyUntil('Shopware_Modules_Admin_CheckUser_Start', array('subject'=>$this))){
			return false;
		}
		if (empty($this->sSYSTEM->_SESSION["sUserMail"]) || empty($this->sSYSTEM->_SESSION["sUserPassword"]) || empty($this->sSYSTEM->_SESSION["sUserId"])){
			 unset($this->sSYSTEM->_SESSION["sUserMail"]);
			 unset($this->sSYSTEM->_SESSION["sUserPassword"]);
			 unset($this->sSYSTEM->_SESSION["sUserId"]);		 
			 return false;
		}
		
		$sql = "
			SELECT * FROM s_user
			WHERE password=? AND email=? AND id=? AND UNIX_TIMESTAMP(lastlogin)>=(UNIX_TIMESTAMP(now())-?)
		";
		
		$getUser = $this->sSYSTEM->sDB_CONNECTION->GetRow($sql,array(
			$this->sSYSTEM->_SESSION["sUserPassword"],
			$this->sSYSTEM->_SESSION["sUserMail"],
			$this->sSYSTEM->_SESSION["sUserId"],
			$this->sSYSTEM->sCONFIG['sUSERTIMEOUT']
		));
		
		$getUser = Enlight()->Events()->filter('Shopware_Modules_Admin_CheckUser_FilterGetUser', $getUser, array('subject'=>$this,'sql'=>$sql,'session'=>$this->sSYSTEM->_SESSION));
		
		
		if(!empty($getUser["id"]))
		{	
			$this->sSYSTEM->sUSERGROUPDATA = $this->sSYSTEM->sDB_CONNECTION->GetRow("
				SELECT * FROM s_core_customergroups
				WHERE groupkey=?
			",array($getUser["customergroup"]));
		
			if ($this->sSYSTEM->sUSERGROUPDATA["mode"]){
				$this->sSYSTEM->sUSERGROUP = "EK";
			} else {
				$this->sSYSTEM->sUSERGROUP = $getUser["customergroup"];
			}
			
			if(!empty($getUser["pricegroupID"]))
			{
				$sql = "SELECT id FROM s_core_customerpricegroups WHERE id=? AND active=1";
				$getUser["pricegroupID"] = $this->sSYSTEM->sDB_CONNECTION->GetOne($sql,array($getUser["pricegroupID"]));
				if(!empty($getUser["pricegroupID"]))
				{
					$this->sSYSTEM->sUSERGROUP = "PG".$getUser["pricegroupID"];
				}
			}
			
			$this->sSYSTEM->_SESSION["sUserGroup"] = $this->sSYSTEM->sUSERGROUP;
			$this->sSYSTEM->_SESSION["sUserGroupData"] = $this->sSYSTEM->sUSERGROUPDATA;
			
			$updateTime = $this->sSYSTEM->sDB_CONNECTION->Execute("UPDATE s_user SET lastlogin=NOW(), sessionID=? WHERE id=?",array($this->sSYSTEM->sSESSION_ID,$getUser["id"]));
			Enlight()->Events()->notify('Shopware_Modules_Admin_CheckUser_Successful', array('subject'=>$this,'session'=>$this->sSYSTEM->_SESSION,'user'=>$getUser));
				
			return true;
		} else {
			 unset($this->sSYSTEM->_SESSION["sUserMail"]);
			 unset($this->sSYSTEM->_SESSION["sUserPassword"]);
			 unset($this->sSYSTEM->_SESSION["sUserId"]);
			 Enlight()->Events()->notify('Shopware_Modules_Admin_CheckUser_Failure', array('subject'=>$this,'session'=>$this->sSYSTEM->_SESSION,'user'=>$getUser));
					 
			 return false;
		}
	}
	
	 /**
	 * Loads the translation for the country table
	 * @param array $country - (optional) translation for a specific country
	 * @access public
	 * @return array - translated country data
	 */
	public function sGetCountryTranslation($country=""){
		// Load Translation
		$sql = "
		SELECT objectdata FROM s_core_translations WHERE objecttype='config_countries' AND objectlanguage='".$this->sSYSTEM->sLanguageData[$this->sSYSTEM->sLanguage]["isocode"]."'";
		
		$getTranslation = $this->sSYSTEM->sDB_CONNECTION->CacheGetRow($this->sSYSTEM->sCONFIG['sCACHECOUNTRIES'],$sql);
		
		if ($getTranslation["objectdata"]){
			$object = unserialize($getTranslation["objectdata"]);
		}
		
		if (!$country) return $object;
		
		// Pass (possible) translation to country
		if ($object[$country["id"]]["countryname"]){
			$country["countryname"] = $object[$country["id"]]["countryname"];
		}
		if ($object[$country["id"]]["notice"]){
			$country["notice"] = $object[$country["id"]]["notice"];
		}
		
		return $country;
	}
	
	 /**
	 * Loads the translation for the different shipping methods
	 * @param array $dispatch - translation for a specific dispatch
	 * @access public
	 * @return array - translated dispatch
	 */
	public function sGetDispatchTranslation($dispatch=""){
		// Load Translation
		$sql = "
		SELECT objectdata FROM s_core_translations WHERE objecttype='config_dispatch' AND objectlanguage='".$this->sSYSTEM->sLanguageData[$this->sSYSTEM->sLanguage]["isocode"]."'";
		
		$getTranslation = $this->sSYSTEM->sDB_CONNECTION->CacheGetRow($this->sSYSTEM->sCONFIG['sCACHECOUNTRIES'],$sql);
		
		if ($getTranslation["objectdata"]){
			$object = unserialize($getTranslation["objectdata"]);
		}
		
		if (!$dispatch) return $object;
		
		// Pass (possible) translation to country
		if ($object[$dispatch["id"]]["dispatch_name"]){
			$dispatch["name"] = $object[$dispatch["id"]]["dispatch_name"];
		}
		if ($object[$dispatch["id"]]["dispatch_description"]){
			$dispatch["description"] = $object[$dispatch["id"]]["dispatch_description"];
		}
		if ($object[$dispatch["id"]]["dispatch_status_link"]){
			$dispatch["status_link"] = $object[$dispatch["id"]]["dispatch_status_link"];
		}
		
		return $dispatch;
	}
	
	 /**
	 * Loads translation for the different paymentmeans
	 * @param array $payment - translation for a specific payment
	 * @access public
	 * @return array - translated data
	 */
	public function sGetPaymentTranslation($payment=""){
		
		// Load Translation
		$sql = "
		SELECT objectdata FROM s_core_translations WHERE objecttype='config_payment' AND objectlanguage='".$this->sSYSTEM->sLanguageData[$this->sSYSTEM->sLanguage]["isocode"]."'";
		
		$getTranslation = $this->sSYSTEM->sDB_CONNECTION->CacheGetRow($this->sSYSTEM->sCONFIG['sCACHECOUNTRIES'],$sql);
		
		if (!empty($getTranslation["objectdata"])){
			$object = unserialize($getTranslation["objectdata"]);
		}
		
		if (!$payment) return $object;
		
		// Pass (possible) translation to payment
		if (!empty($object[$payment["id"]]["description"])){
			$payment["description"] = $object[$payment["id"]]["description"];
		}
		if (!empty($object[$payment["id"]]["additionaldescription"])){
			$payment["additionaldescription"] = $object[$payment["id"]]["additionaldescription"];
		}
		
		return $payment;
	}
	
	 /**
	 * Get country list
	 * @access public
	 * @return array - country list
	 */
	public function sGetCountryList()
	{
		$getCountries = $this->sSYSTEM->sDB_CONNECTION->CacheGetAll($this->sSYSTEM->sCONFIG['sCACHECOUNTRIES'],"SELECT * FROM s_core_countries ORDER BY position, countryname ASC");
		

		$object = $this->sGetCountryTranslation();
		foreach ($getCountries as $key => $v)
		{
			if (isset($object[$v["id"]]["active"])){
				$v["active"] = $object[$v["id"]]["active"];
			}
			
			if(isset($v['active'])&&empty($v['active']))
			{
				unset($getCountries[$key]);
				continue;
			}
			
			if (!empty($object[$v["id"]]["countryname"])){
				$getCountries[$key]["countryname"] = $object[$v["id"]]["countryname"];
			}
			if (!empty($object[$v["id"]]["notice"])){
				$getCountries[$key]["notice"] = $object[$v["id"]]["notice"];
			}
			
			if ($getCountries[$key]["id"]==$this->sSYSTEM->_POST['country'] || $getCountries[$key]["id"]==$this->sSYSTEM->_POST['countryID'] ){
				$getCountries[$key]["flag"] = true;
			}else {
				$getCountries[$key]["flag"] = false;
			}

		}
		
		$getCountries = Enlight()->Events()->filter('Shopware_Modules_Admin_GetCountries_FilterResult', $getCountries, array('subject'=>$this));

		return $getCountries;

	}
	
	
	 /**
	 * Registration - storage main user data (login data / entry in s_user)
	 * @param array $userObject - Array with all information from the registration process
	 * @access public
	 * @return int - insert id
	 */
	public function sSaveRegisterMainData($userObject){
		
		// Support for merchants
		if ($userObject["billing"]["sValidation"]){
			$sMerchant = $userObject["billing"]["sValidation"];
		}else {
			$sMerchant = "";
		}
		
		if (empty($this->sSYSTEM->sCONFIG["sDefaultCustomerGroup"])) $this->sSYSTEM->sCONFIG["sDefaultCustomerGroup"] = "EK";
		$referer = $this->sSYSTEM->_SESSION['sReferer'];
		
        if (!empty($this->sSYSTEM->_SESSION['sPartner']))
        {
        	$sql = 'SELECT id FROM s_emarketing_partner WHERE idcode = ?';
        	$partner = (int) $this->sSYSTEM->sDB_CONNECTION->GetOne($sql, array($this->sSYSTEM->_SESSION['sPartner']));
        }
        
        $data = array(
	        $userObject["auth"]["password"],
	        $userObject["auth"]["email"],
	        $userObject["payment"]["object"]["id"],
	        $userObject["auth"]["accountmode"],
	        empty($sMerchant) ? "" : $sMerchant,
	        $this->sSYSTEM->sSESSION_ID,
	        empty($partner) ? "" : $partner,
	        $this->sSYSTEM->sCONFIG["sDefaultCustomerGroup"],
	        $this->sSYSTEM->sLanguageData[$this->sSYSTEM->sLanguage]["isocode"],
	        $this->sSYSTEM->sSubShop["id"],
	        empty($referer) ? "" : $referer
	        
        );
		$sql = '
			INSERT INTO s_user 
			(
				password, email, paymentID, active, accountmode,
			 	validation, firstlogin,sessionID, affiliate, customergroup,
			 	language, subshopID, referer
			) 
			VALUES (?,?,?,1,?,?,NOW(),?,?,?,?,?,?)
		';

		list($sql,$data) = Enlight()->Events()->filter('Shopware_Modules_Admin_SaveRegisterMainData_FilterSql', array($sql,$data), array('subject'=>$this));
		
		$saveUserData = $this->sSYSTEM->sDB_CONNECTION->Execute($sql, $data);
		Enlight()->Events()->notify('Shopware_Modules_Admin_SaveRegisterMainData_Return', array('subject'=>$this,'insertObject'=>$saveUserData));
		return $this->sSYSTEM->sDB_CONNECTION->Insert_ID();	
	}
	
	/**
	 * Registration - storage of email in maillist
	 * @param array $userObject - Array with all information from the registration process
	 * @access public
	 * @return null
	 */
	public function sSaveRegisterNewsletter($userObject){
		// Check for duplicates
		$checkDuplicate = $this->sSYSTEM->sDB_CONNECTION->GetRow("
		SELECT id FROM s_campaigns_mailaddresses
		WHERE email=?",array($userObject["auth"]["email"]));
		
		if (empty($checkDuplicate["id"])){
			$saveNewsletter = $this->sSYSTEM->sDB_CONNECTION->Execute("
			INSERT INTO s_campaigns_mailaddresses (customer, groupID, email)
			VALUES (1,0,?)",array($userObject["auth"]["email"]));
		}	
	}
	
	/**
	 * Registration - storage of billing address
	 * @param int $userID - user id (s_user.id) from sSaveRegisterMain
	 * @param array $userObject - Array with all information from the registration process
	 * @access public
	 * @return int - insert id / row id
	 */
	public function sSaveRegisterBilling($userID, $userObject){
			if ($userObject["billing"]["birthmonth"]=="-") unset($userObject["billing"]["birthmonth"]);
			if ($userObject["billing"]["birthday"]=="--") unset($userObject["billing"]["birthday"]);
			if ($userObject["billing"]["birthyear"]=="----") unset($userObject["billing"]["birthyear"]);
			
			if (!empty($userObject["billing"]["birthmonth"]) && 
			!empty($userObject["billing"]["birthday"]) &&
			!empty($userObject["billing"]["birthyear"])){
				$date = $userObject["billing"]["birthyear"]."-".$userObject["billing"]["birthmonth"]."-".$userObject["billing"]["birthday"];
				
				$date = date("Y-m-d",strtotime($date));
				
			}else {
				$date = "0000-00-00";
			}
			$userObject = $userObject["billing"];
			$data = array(
				$userID,
				empty($userObject["company"]) ? "" : $userObject["company"],
				empty($userObject["department"]) ? "" : $userObject["department"],
				empty($userObject["salutation"]) ? "" : $userObject["salutation"],
				$userObject["firstname"],
				$userObject["lastname"],
				$userObject["street"],
				$userObject["streetnumber"],
				$userObject["zipcode"],
				$userObject["city"],
				empty($userObject["phone"]) ? "" : $userObject["phone"],
				empty($userObject["fax"]) ? "" : $userObject["fax"],
				$userObject["country"],
				empty($userObject["ustid"]) ? "" : $userObject["ustid"],
				empty($userObject["text1"]) ? "" : $userObject["text1"],
				empty($userObject["text2"]) ? "" : $userObject["text2"],
				empty($userObject["text3"]) ? "" : $userObject["text3"],
				empty($userObject["text4"]) ? "" : $userObject["text4"],
				empty($userObject["text5"]) ? "" : $userObject["text5"],
				empty($userObject["text6"]) ? "" : $userObject["text6"],
				$date
			);
			
			$sqlBilling = "INSERT INTO s_user_billingaddress 
			(userID,company,department, salutation,firstname,lastname,
			street,streetnumber,zipcode,city,phone,
			fax,countryID,ustid, text1, text2, text3, text4, text5, text6,birthday)
			VALUES
			(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
			

			// Trying to insert
			list($sqlBilling,$data) = Enlight()->Events()->filter('Shopware_Modules_Admin_SaveRegisterBilling_FilterSql', array($sqlBilling,$data), array('subject'=>$this));
				
			$saveUserData = $this->sSYSTEM->sDB_CONNECTION->Execute($sqlBilling,$data);
			Enlight()->Events()->notify('Shopware_Modules_Admin_SaveRegisterBilling_Return', array('subject'=>$this,'insertObject'=>$saveUserData));
		
			return $this->sSYSTEM->sDB_CONNECTION->Insert_ID();
	}
	
	/**
	 * Registration - storage of shipping address
	 * @param int $userID - user id (s_user.id) from sSaveRegisterMain
	 * @param array $userObject - Array with all information from the registration process
	 * @access public
	 * @return int - insert id / row id
	 */
	public function sSaveRegisterShipping($userID, $userObject){
			$sqlShipping = "INSERT INTO s_user_shippingaddress 
			(userID,company,department, salutation,firstname,lastname,
			street,streetnumber,zipcode,city, countryID, text1, text2, text3, text4, text5, text6)
			VALUES
			($userID,
			'{$userObject["shipping"]["company"]}',
			'{$userObject["shipping"]["department"]}',
			'{$userObject["shipping"]["salutation"]}',
			'{$userObject["shipping"]["firstname"]}',
			'{$userObject["shipping"]["lastname"]}',
			'{$userObject["shipping"]["street"]}',
			'{$userObject["shipping"]["streetnumber"]}',
			'{$userObject["shipping"]["zipcode"]}',
			'{$userObject["shipping"]["city"]}',
			'{$userObject["shipping"]["country"]}',
			'{$userObject["shipping"]["text1"]}',
			'{$userObject["shipping"]["text2"]}',
			'{$userObject["shipping"]["text3"]}',
			'{$userObject["shipping"]["text4"]}',
			'{$userObject["shipping"]["text5"]}',
			'{$userObject["shipping"]["text6"]}'
			)";
			$sqlShipping = Enlight()->Events()->filter('Shopware_Modules_Admin_SaveRegisterShipping_FilterSql', $sqlShipping, array('subject'=>$this,'user'=>$userObject,'id'=>$userID));
		

			// Trying to insert
			$saveUserData = $this->sSYSTEM->sDB_CONNECTION->Execute($sqlShipping);
			Enlight()->Events()->notify('Shopware_Modules_Admin_SaveRegisterShipping_Return', array('subject'=>$this,'insertObject'=>$saveUserData));
		
			return $this->sSYSTEM->sDB_CONNECTION->Insert_ID();
	}
	
	/**
	 * Registration - Mail register confirmation
	 * @param string $email - Recipient mail
	 * @access public
	 * @return null
	 */
	public function sSaveRegisterSendConfirmation($email)
	{
		if (Enlight()->Events()->notifyUntil('Shopware_Modules_Admin_SaveRegisterSendConfirmation_Start', array('subject'=>$this,'email'=>$email))){
			return false;
		}
		$mail = $this->sSYSTEM->sMailer;
			
		if  ($this->sSYSTEM->sCONFIG['sTemplates']['sREGISTERCONFIRMATION']['ishtml']){
			$mail->IsHTML(1);
			$mail->Body     = $this->sSYSTEM->sCONFIG['sTemplates']['sREGISTERCONFIRMATION']['contentHTML'];
			$AltBody     = $this->sSYSTEM->sCONFIG['sTemplates']['sREGISTERCONFIRMATION']['content'];
		}else {
			$mail->IsHTML(0);
			$mail->Body     = $this->sSYSTEM->sCONFIG['sTemplates']['sREGISTERCONFIRMATION']['content'];
		}
		
		$mail->From     = $this->sSYSTEM->sCONFIG['sTemplates']['sREGISTERCONFIRMATION']['frommail'];
		$mail->FromName = $this->sSYSTEM->sCONFIG['sTemplates']['sREGISTERCONFIRMATION']['fromname'];
		$mail->Subject  = $this->sSYSTEM->sCONFIG['sTemplates']['sREGISTERCONFIRMATION']['subject'];

		
		foreach ($this->sSYSTEM->_SESSION["sRegister"]["billing"] as $key => $value)
		{
			if($key=="salutation") $value = ($value=="ms") ? "Frau" : "Herr";
			$mail->Body = str_replace("{".$key."}",$value,$mail->Body);
			if (!empty($AltBody)){
				$AltBody = str_replace("{".$key."}",$value,$AltBody);
			}
		}
		$mail->Body = str_replace("{sMAIL}",$email,$mail->Body);
		if (!empty($AltBody)){
			$AltBody = str_replace("{sMAIL}",$email,$AltBody);
			$mail->AltBody = $AltBody;
		}

		$mail->ClearAddresses();
		$mail->AddAddress($email, "");
		if (!empty($this->sSYSTEM->sCONFIG["sSEND_CONFIRM_MAIL"])){
			$mail->AddBCC($this->sSYSTEM->sCONFIG['sMAIL'], "");
		}
		$attachment = $this->sSYSTEM->sCONFIG['sTemplates']['sREGISTERCONFIRMATION']["attachment"];
		
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

		
		Enlight()->Events()->notify('Shopware_Modules_Admin_SaveRegisterSendConfirmation_BeforeSend', array('subject'=>$this,'mail'=>$mail));
		
		$mail->Send();	
	}
	
	/**
	 * Register - finaly check data and call the different sub functions (saveBilling etc.)
	 * @param array $paymentObject - choosen payment data
	 * @return boolean
	 */
	public function sSaveRegister ($paymentObject=null){
		if (Enlight()->Events()->notifyUntil('Shopware_Modules_Admin_SaveRegister_Start', array('subject'=>$this))){
			return false;
		}
		if(!$this->sSYSTEM->_SESSION["sRegisterFinished"])
		{
			if (empty($this->sSYSTEM->_SESSION["sRegister"]["payment"]["object"]["id"])) $this->sSYSTEM->_SESSION["sRegister"]["payment"]["object"]["id"]  = $this->sSYSTEM->sCONFIG['sDEFAULTPAYMENT'];
			$neededFields["auth"] = array("email","password");
			$neededFields["billing"] = array("salutation","firstname","lastname","street","streetnumber","zipcode","city","country");
			$neededFields["payment"] = array("object"=>array("id"));
			
			$neededFields = Enlight()->Events()->filter('Shopware_Modules_Admin_SaveRegister_FilterNeededFields', $neededFields, array('subject'=>$this));
			
			// Check for needed fields
			foreach ($neededFields as $needKey => $needValue){
				foreach ($neededFields[$needKey] as $fieldKey => $fieldValue){
					if (is_array($fieldValue)){
						
						$objKey = $fieldValue[0];
						
						if (empty($this->sSYSTEM->_SESSION["sRegister"][$needKey][$fieldKey][$objKey])){
							$errorFields[] = $needKey."#1($needKey)($fieldKey)($objKey)->".$fieldValue;
						}	
					}else {
						if (empty($this->sSYSTEM->_SESSION["sRegister"][$needKey][$fieldValue])){
							$errorFields[] = $needKey."#2->".$fieldValue;
						}
					}
				}
			}
			
			$errorFields = Enlight()->Events()->filter('Shopware_Modules_Admin_SaveRegister_FilterErrors', $errorFields, array('subject'=>$this));
			
			// Check for occured errors
			if (count($errorFields)){
				if (!$_COOKIE["SHOPWARESID"]){
					$noCookies = "NO SESSION-COOKIE";
				}
				$this->sSYSTEM->E_CORE_WARNING("sSaveRegister #00","Fields are missing $noCookies - ".$this->sSYSTEM->sSESSION_ID." - ".print_r($errorFields,true));
				die ("Session Lost - Bitte aktivieren Sie Cookies in Ihrem Browser!");
				return false;
			}else {
				
				$userObject = $this->sSYSTEM->_SESSION["sRegister"];
				
				if (!$userObject["payment"]["object"]["id"]) $userObject["payment"]["object"]["id"] = $this->sSYSTEM->sCONFIG['sPAYMENTDEFAULT'];

				$userID = $this->sSaveRegisterMainData($userObject);
				
				if ($this->sSYSTEM->sDB_CONNECTION->ErrorMsg() || !$userID){
					$this->sSYSTEM->E_CORE_WARNING("sSaveRegister #01","Could not save data".$this->sSYSTEM->sDB_CONNECTION->ErrorMsg().print_r($userObject));
					die("sSaveRegister #01"."Could not save data".$this->sSYSTEM->sDB_CONNECTION->ErrorMsg());
				}
				
				if ($userObject["auth"]["receiveNewsletter"]){
					$this->sSaveRegisterNewsletter($userObject);
				}

				// Insert billing-adress
				$userBillingID = $this->sSaveRegisterBilling($userID,$userObject);
				
				if ($this->sSYSTEM->sDB_CONNECTION->ErrorMsg() || !$userBillingID){
					$this->sSYSTEM->E_CORE_WARNING("sSaveRegister #02","Could not save data (billing-adress)".$this->sSYSTEM->sDB_CONNECTION->ErrorMsg().print_r($userObject,true));
					die("Could not save data (billing-adress)".$this->sSYSTEM->sDB_CONNECTION->ErrorMsg());
				}
				
				
				if ($this->sSYSTEM->sCONFIG['sSHOPWAREMANAGEDCUSTOMERNUMBERS']){
					if (!Enlight()->Events()->notifyUntil('Shopware_Modules_Admin_SaveRegister_GetCustomerNumber', array('subject'=>$this,'id'=>$userId))){
						$sql = "UPDATE `s_order_number`,`s_user_billingaddress`  SET `s_order_number`.`number`=`s_order_number`.`number`+1, `s_user_billingaddress`.`customernumber`=`s_order_number`.`number`+1 WHERE `s_order_number`.`name` ='user' AND `s_user_billingaddress`.`userID`=?";
						$this->sSYSTEM->sDB_CONNECTION->Execute($sql,array($userId));
					}
				}

				// Insert shipping-adress
				if (count($userObject["shipping"])){
					$userShippingID = $this->sSaveRegisterShipping($userID,$userObject);
					if ($this->sSYSTEM->sDB_CONNECTION->ErrorMsg() || !$userShippingID){
						$this->sSYSTEM->E_CORE_WARNING("sSaveRegister #02","Could not save data (shipping-address)".$this->sSYSTEM->sDB_CONNECTION->ErrorMsg().print_r($userObject,true));
						return false;
					}
				}

				$uMail = $userObject["auth"]["email"];
				$uPass = $userObject["auth"]["password"];
				
				if ($userObject["auth"]["accountmode"]<1){
					$this->sSaveRegisterSendConfirmation($uMail);
					$this->sSYSTEM->_SESSION["sOneTimeAccount"] = false;
				}else {
					$this->sSYSTEM->_SESSION["sOneTimeAccount"] = true;
				}
				
				// Save referer where user comes from
				if(!empty($this->sSYSTEM->_SESSION['sReferer']))
				{
					$referer = addslashes($this->sSYSTEM->_SESSION['sReferer']);
					$sql = "
					INSERT INTO 
						`s_emarketing_referer` ( `userID` , `referer` , `date` )
					VALUES (
						?, ?, NOW()
					);";
					$this->sSYSTEM->sDB_CONNECTION->Execute($sql,array($userID,$referer));
				}
				
				$this->sSYSTEM->_POST["email"] = $uMail;
				$this->sSYSTEM->_POST["passwordMD5"] = $uPass;

				// Login user
				$chkUserLogin = $this->sLogin(true);
									
				// The user is now registered
				$this->sSYSTEM->_SESSION["sRegisterFinished"] = true;
				
				Enlight()->Events()->notify('Shopware_Modules_Admin_SaveRegister_Successful', array('subject'=>$this,'id'=>$userID,'billingID'=>$userBillingID,'shippingID'=>$userShippingID));
	
				// Garbage 
				unset($this->sSYSTEM->_SESSION['sRegister']);
			}	
					
			return true;
		}else {
			$this->sSYSTEM->_POST["email"] = $this->sSYSTEM->_SESSION['sUserMail'];
			$this->sSYSTEM->_POST["passwordMD5"] = $this->sSYSTEM->_SESSION['sUserPassword'];
			$chkUserLogin = $this->sLogin($this->sSYSTEM->_SESSION["sOneTimeAccount"]);
			return true;
		}

	}
	
	
	
	
	/**
	 * Account - get purchased instant downloads 
	 * @access public
	 * @return array - Data from orders who contains instant downloads
	 */
	public function sGetDownloads (){
		$getOrders = $this->sSYSTEM->sDB_CONNECTION->GetAll("
		SELECT id, ordernumber, invoice_amount, invoice_amount_net, invoice_shipping, invoice_shipping_net, DATE_FORMAT(ordertime,'%d.%m.%Y %H:%i') AS datum, status, comment
		FROM s_order WHERE userID=? AND s_order.status>=0 ORDER BY ordertime DESC LIMIT 10
		",array($this->sSYSTEM->_SESSION["sUserId"]));
		
		foreach ($getOrders as $orderKey => $orderValue){
			
			if (($this->sSYSTEM->sCONFIG['sARTICLESOUTPUTNETTO'] && !$this->sSYSTEM->sUSERGROUPDATA["tax"]) || (!$this->sSYSTEM->sUSERGROUPDATA["tax"] && $this->sSYSTEM->sUSERGROUPDATA["id"])){
				$getOrders[$orderKey]["invoice_amount"] = $this->sSYSTEM->sMODULES['sArticles']->sFormatPrice($orderValue["invoice_amount_net"]);
				$getOrders[$orderKey]["invoice_shipping"] = $this->sSYSTEM->sMODULES['sArticles']->sFormatPrice($orderValue["invoice_shipping_net"]);
			}else {
				$getOrders[$orderKey]["invoice_amount"] = $this->sSYSTEM->sMODULES['sArticles']->sFormatPrice($orderValue["invoice_amount"]);
				$getOrders[$orderKey]["invoice_shipping"] = $this->sSYSTEM->sMODULES['sArticles']->sFormatPrice($orderValue["invoice_shipping"]);
			}
			
			$getOrderDetails = $this->sSYSTEM->sDB_CONNECTION->GetAll("
			SELECT * FROM s_order_details WHERE orderID={$orderValue["id"]}
			");
			
			if (!count($getOrderDetails)){
				unset($getOrders[$orderKey]);
			}else {
				$foundESD = false;
				foreach ($getOrderDetails as $orderDetailsKey => $orderDetailsValue){
					$getOrderDetails[$orderDetailsKey]["amount"] = $this->sSYSTEM->sMODULES['sArticles']->sFormatPrice(round($orderDetailsValue["price"] * $orderDetailsValue["quantity"],2));
					$getOrderDetails[$orderDetailsKey]["price"] = $this->sSYSTEM->sMODULES['sArticles']->sFormatPrice($orderDetailsValue["price"]);
					// Check for serial
					if ($getOrderDetails[$orderDetailsKey]["esdarticle"]){
						$foundESD = true;
						$numbers = array();
						$getSerial = $this->sSYSTEM->sDB_CONNECTION->GetAll("
						SELECT serialnumber FROM s_articles_esd_serials, s_order_esd WHERE userID=".$this->sSYSTEM->_SESSION["sUserId"]."
						AND orderID={$orderValue["id"]} AND orderdetailsID={$orderDetailsValue["id"]}
						AND s_order_esd.serialID=s_articles_esd_serials.id
						");
						foreach ($getSerial as $serial){
							$numbers[] = $serial["serialnumber"];
						}
						$getOrderDetails[$orderDetailsKey]["serial"] =  implode(",",$numbers);
						// Building download-link
						$getOrderDetails[$orderDetailsKey]["esdLink"] = $this->sSYSTEM->sCONFIG["sBASEFILE"].'?sViewport=account&sAction=download&esdID='.$orderDetailsValue['id'];
						//$getOrderDetails[$orderDetailsKey]["esdLink"] = "http://".$this->sSYSTEM->sCONFIG["sBASEPATH"]."/engine/core/php/loadesd.php?id=".$orderDetailsValue["id"];
					}else {
						unset($getOrderDetails[$orderDetailsKey]);
					}
					// -- End of serial check
				}
				if (!empty($foundESD)){
					$getOrders[$orderKey]["details"] = $getOrderDetails;
				}else {
					unset($getOrders[$orderKey]);
				}
			}
		}
		
		$getOrders = Enlight()->Events()->filter('Shopware_Modules_Admin_GetDownloads_FilterResult', $getOrders, array('subject'=>$this,'id'=>$this->sSYSTEM->_SESSION["sUserId"]));
		
		return $getOrders;
		
	}
	
	/**
	 * Account - Get all orders that the user did
	 * @access public
	 * @return array - Array with order data / positions
	 */
	public function sGetOpenOrderData ()
	{
		$sql = "
			SELECT o.*, cu.templatechar as currency_html, DATE_FORMAT(ordertime,'%d.%m.%Y %H:%i') AS datum
			FROM s_order o
			LEFT JOIN s_core_currencies as cu
			ON o.currency = cu.currency
			WHERE userID=? AND status != -1
			AND subshopID = ?
			ORDER BY ordertime DESC LIMIT 10
		";
		$getOrders = $this->sSYSTEM->sDB_CONNECTION->GetAll($sql,array($this->sSYSTEM->_SESSION["sUserId"],$this->sSYSTEM->sSubShop["id"]));
		
		foreach ($getOrders as $orderKey => $orderValue){
			
			$getOrders[$orderKey]["invoice_amount"] = $this->sSYSTEM->sMODULES['sArticles']->sFormatPrice($orderValue["invoice_amount"]);
			$getOrders[$orderKey]["invoice_shipping"] = $this->sSYSTEM->sMODULES['sArticles']->sFormatPrice($orderValue["invoice_shipping"]);
		
			
			$getOrderDetails = $this->sSYSTEM->sDB_CONNECTION->GetAll("
			SELECT * FROM s_order_details WHERE orderID={$orderValue["id"]} ORDER BY id ASC
			");
			
			if (!count($getOrderDetails)){
				unset($getOrders[$orderKey]);
			}else {
			
				/** GET ARTICLE DETAILS START - @date: 05-24-2011 */
				$active = 1;
				/** GET ARTICLE DETAILS END */
				
				foreach ($getOrderDetails as $orderDetailsKey => $orderDetailsValue){
					$getOrderDetails[$orderDetailsKey]["amount"] = $this->sSYSTEM->sMODULES['sArticles']->sFormatPrice(round($orderDetailsValue["price"] * $orderDetailsValue["quantity"],2));
					$getOrderDetails[$orderDetailsKey]["price"] = $this->sSYSTEM->sMODULES['sArticles']->sFormatPrice($orderDetailsValue["price"]);
					
					/** GET ARTICLE DETAILS START - @date: 05-24-2011 */
					$tmpArticle = $this->sSYSTEM->sMODULES['sArticles']->sGetPromotionById('fix', 0, $getOrderDetails[$orderDetailsKey]['articleID']);
					
					if(!empty($tmpArticle) && is_array($tmpArticle)) {
					
						// Set article in activate state
						$getOrderDetails[$orderDetailsKey]['active'] = 1;
						if(!empty($tmpArticle['purchaseunit'])) {
							$getOrderDetails[$orderDetailsKey]['purchaseunit'] = $tmpArticle['purchaseunit'];
						}
						
						if(!empty($tmpArticle['referenceunit'])) {
							$getOrderDetails[$orderDetailsKey]['referenceunit'] = $tmpArticle['referenceunit'];
						}
						
						if(!empty($tmpArticle['referenceprice'])) {
							$getOrderDetails[$orderDetailsKey]['referenceprice'] = $tmpArticle['referenceprice'];
						}
						
						if(!empty($tmpArticle['sUnit']) && is_array($tmpArticle['sUnit'])) {
							$getOrderDetails[$orderDetailsKey]['sUnit'] = $tmpArticle['sUnit'];
						}
						
						if(!empty($tmpArticle['price'])) {
							$getOrderDetails[$orderDetailsKey]['currentPrice'] = $tmpArticle['price'];
						}
						
						if(!empty($tmpArticle['pseudoprice'])) {
							$getOrderDetails[$orderDetailsKey]['currentPseudoprice'] = $tmpArticle['pseudoprice'];
						}
						
						// Set article in deactivate state if it's an variant or configurator article
						if($tmpArticle['sVariantArticle'] === true || $tmpArticle['sConfigurator'] === true) {
							$getOrderDetails[$orderDetailsKey]['active'] = 0;
							$active = 0;
						}
					} else {
						$getOrderDetails[$orderDetailsKey]['active'] = 0;
						$active = 0;
					}
					/** GET ARTICLE DETAILS END */
					
					// Check for serial
					if ($getOrderDetails[$orderDetailsKey]["esdarticle"]){
						$numbers = array();
						$sql = "
						SELECT serialnumber FROM s_articles_esd_serials, s_order_esd WHERE userID=?
						AND orderID=? AND orderdetailsID=?
						AND s_order_esd.serialID=s_articles_esd_serials.id
						";
						
						$getSerial = $this->sSYSTEM->sDB_CONNECTION->GetAll($sql,array($this->sSYSTEM->_SESSION["sUserId"],$orderValue["id"],$orderDetailsValue["id"]));
						foreach ($getSerial as $serial){
							$numbers[] = $serial["serialnumber"];
						}
						$getOrderDetails[$orderDetailsKey]["serial"] =  implode(",",$numbers);
						// Building download-link
						$getOrderDetails[$orderDetailsKey]["esdLink"] = $this->sSYSTEM->sCONFIG["sBASEFILE"].'?sViewport=account&sAction=download&esdID='.$orderDetailsValue['id'];
						//$getOrderDetails[$orderDetailsKey]["esdLink"] = "http://".$this->sSYSTEM->sCONFIG["sBASEPATH"]."/engine/core/php/loadesd.php?id=".$orderDetailsValue["id"];
					}
					// -- End of serial check
				}
				/** GET ARTICLE DETAILS START - @date: 05-24-2011 */
				$getOrders[$orderKey]['activeBuyButton'] = $active;
				/** GET ARTICLE DETAILS END */
				
				$getOrders[$orderKey]["details"] = $getOrderDetails;
			}
			$getOrders[$orderKey]["dispatch"] = $this->sGetDispatch($orderValue['dispatchID']);
		}
		
		$getOrders = Enlight()->Events()->filter('Shopware_Modules_Admin_GetOpenOrderData_FilterResult', $getOrders, array('subject'=>$this,'id'=>$this->sSYSTEM->_SESSION["sUserId"],'subshopID'=>$this->sSYSTEM->sSubShop["id"]));
		return $getOrders;
	}

	
	/**
	 * Get a user mail by id
	 * @access public
	 * @return string email
	 */
	public function sGetUserMailById(){
		$email = $this->sSYSTEM->sDB_CONNECTION->GetRow("
		SELECT email FROM
		s_user WHERE id=?",array($this->sSYSTEM->_SESSION["sUserId"]));
		
		return $email["email"];

	}
	
	/**
	 * Get user id by mail
	 * @access public
	 * @return  int id
	 */
	public function sGetUserByMail($email){
		$getUserData = $this->sSYSTEM->sDB_CONNECTION->GetRow("
		SELECT id FROM s_user WHERE email=? AND accountmode!=1
		",array($email));
		
		return $getUserData["id"];
	}
	
	/**
	 * Get user first- and lastname by id
	 * @access public
	 * @return array firstname/lastname
	 */
	public function sGetUserNameById($id){
		return $this->sSYSTEM->sDB_CONNECTION->GetRow("
				SELECT firstname, lastname FROM s_user_billingaddress
				WHERE userID=?
				",array($id));
	}
	
	
	/**
	 *  Get all data from the current logged in user
	 * @access public
	 * @return array
	 */
	public function sGetUserData (){
		if (Enlight()->Events()->notifyUntil('Shopware_Modules_Admin_GetUserData_Start', array('subject'=>$this))){
			return false;
		}
		if (empty($this->sSYSTEM->_SESSION['sRegister']))
		{
			$this->sSYSTEM->_SESSION['sRegister'] = new ArrayObject(array(), ArrayObject::ARRAY_AS_PROPS);
		}
		
		// If user is logged in
		if (!empty($this->sSYSTEM->_SESSION["sUserId"])){
			
			
			// 1.) Get billing-adress
			$sql = "SELECT * 
					FROM 
						s_user_billingaddress 
					WHERE 
						userID=?";
			
			$userData["billingaddress"] = $this->sSYSTEM->sDB_CONNECTION->GetRow($sql,array($this->sSYSTEM->_SESSION["sUserId"]));
			
			if(empty($userData["billingaddress"]['customernumber']) && $this->sSYSTEM->sCONFIG['sSHOPWAREMANAGEDCUSTOMERNUMBERS'])
			{
				$sql = "UPDATE `s_order_number`,`s_user_billingaddress`  SET `s_order_number`.`number`=`s_order_number`.`number`+1, `s_user_billingaddress`.`customernumber`=`s_order_number`.`number`+1 WHERE `s_order_number`.`name` ='user' AND `s_user_billingaddress`.`userID`='{$this->sSYSTEM->_SESSION["sUserId"]}'";
				$this->sSYSTEM->sDB_CONNECTION->Execute($sql);
			}
			
			// 2.) Advanced infos
				// Query country-information
				$userData["additional"]["country"] =  $this->sSYSTEM->sDB_CONNECTION->GetRow("
				SELECT * FROM s_core_countries
				WHERE id=?
				",array($userData["billingaddress"]["countryID"]));
				
				$userData["additional"]["country"] = $this->sGetCountryTranslation($userData["additional"]["country"]);
				// Some basic-data
				$userData["additional"]["user"] = $this->sSYSTEM->sDB_CONNECTION->GetRow("
				SELECT * FROM s_user WHERE id=?",array($this->sSYSTEM->_SESSION["sUserId"]));
				// Payment data
							
				// Newsletter-Properties
				$newsletter = $this->sSYSTEM->sDB_CONNECTION->GetRow("
				SELECT id FROM s_campaigns_mailaddresses
				WHERE email=?
				",array($userData["additional"]["user"]["email"]));
				if ($newsletter["id"]){
					$userData["additional"]["user"]["newsletter"] = 1;
				}else {
					$userData["additional"]["user"]["newsletter"] = 0;
				}
			
			// 3.) Get shipping-adress
			$userData["shippingaddress"] = $this->sSYSTEM->sDB_CONNECTION->GetRow("
			SELECT * FROM s_user_shippingaddress WHERE userID=".$this->sSYSTEM->_SESSION["sUserId"]);
			
			// If shipping-adress is not available, billingadress is coeval the shippingadress
			if (!isset($userData["shippingaddress"]["firstname"])){
				$userData["shippingaddress"] = $userData["billingaddress"];
				$userData["shippingaddress"]["eqalBilling"] = true;
			}else {
				if (($userData["shippingaddress"]["countryID"] != $userData["billingaddress"]["countryID"]) && empty($this->sSYSTEM->sCONFIG["sCOUNTRYSHIPPING"])){
					$update = $this->sSYSTEM->sDB_CONNECTION->Execute("
					UPDATE s_user_shippingaddress SET countryID = ? WHERE id = ?
					",array($userData["billingaddress"]["countryID"],$userData["shippingaddress"]["id"]));
					$userData["shippingaddress"]["countryID"] = $userData["billingaddress"]["countryID"];
				}
			}
			
			if (empty($userData["shippingaddress"]["countryID"])){
				$userData["additional"]["countryShipping"] =  $this->sSYSTEM->sDB_CONNECTION->GetRow("
				SELECT * FROM s_core_countries
				WHERE id=?
				",array($userData["billingaddress"]["countryID"]));
			}else {
				$userData["additional"]["countryShipping"] =  $this->sSYSTEM->sDB_CONNECTION->GetRow("
				SELECT * FROM s_core_countries
				WHERE id=?
				",array($userData["shippingaddress"]["countryID"]));
			}
			
			
			$userData["additional"]["countryShipping"] = $this->sGetCountryTranslation($userData["additional"]["countryShipping"]);
			
			$userData["additional"]["payment"] = $this->sGetPaymentMeanById($userData["additional"]["user"]["paymentID"],$userData);
				
			
		}else {
			if ($this->sSYSTEM->_SESSION["sCountry"] && $this->sSYSTEM->_SESSION["sCountry"] != $this->sSYSTEM->_SESSION["sRegister"]["billing"]["country"]){
				$this->sSYSTEM->_SESSION["sRegister"]["billing"]["country"] = intval($this->sSYSTEM->_SESSION["sCountry"]);
			}
			// $this->sSYSTEM->_SESSION["sCountry"]
			$userData["additional"]["country"] =  $this->sSYSTEM->sDB_CONNECTION->GetRow("
			SELECT * FROM s_core_countries
			WHERE id=".intval($this->sSYSTEM->_SESSION["sRegister"]["billing"]["country"]));
			
			$userData["additional"]["countryShipping"] = $userData["additional"]["country"];
		}

		$userData = Enlight()->Events()->filter('Shopware_Modules_Admin_GetUserData_FilterResult', $userData, array('subject'=>$this,'id'=>$this->sSYSTEM->_SESSION["sUserId"]));
		return  $userData;
	}
	
	/**
	 * Get dispatch method by id
	 * @access public
	 * @return array
	 */
	public function sGetDispatch($dispatchID)
	{
		if (!empty($this->sSYSTEM->sCONFIG['sPREMIUMSHIPPIUNG']))
		{
			return $this->sGetPremiumDispatch($dispatchID);
		}
		$dispatchID = intval($dispatchID);
		
		$getDispatch = $this->sSYSTEM->sDB_CONNECTION->GetRow("
		SELECT id, name, description FROM s_shippingcosts_dispatch  WHERE 
			active = 1 
		AND
			s_shippingcosts_dispatch.id = $dispatchID
		");
		
		//print_r($getDispatch);
		
		return $this->sGetDispatchTranslation($getDispatch);
	}
	
	/**
	 * Get all dispatch methods, filtered by country
	 * @param int $countryID s_core_countries.id
	 * @access public
	 * @return array
	 */
	
	public function sGetDispatches($countryID)
	{
		if (!empty($this->sSYSTEM->sCONFIG['sPREMIUMSHIPPIUNG']))
		{
			return $this->sGetPremiumDispatches($countryID);
		}
		$countryID = intval($countryID);
		$getDispatches = $this->sSYSTEM->sDB_CONNECTION->GetAll("
		SELECT id, name, description FROM s_shippingcosts_dispatch,s_shippingcosts_dispatch_countries  WHERE 
			active = 1 
		AND
			s_shippingcosts_dispatch.id = s_shippingcosts_dispatch_countries.typeID
		AND
			s_shippingcosts_dispatch_countries.countryID = $countryID
		ORDER BY position ASC
		");
		if(empty($getDispatches)) return array();
		$object = $this->sGetDispatchTranslation();
		
		foreach ($getDispatches as $key => $v){
			if ($object[$v["id"]]["dispatch_name"]){
				$getDispatches[$key]["name"] = $object[$v["id"]]["dispatch_name"];
			}
			if ($object[$v["id"]]["dispatch_description"]){
				$getDispatches[$key]["description"] = $object[$v["id"]]["dispatch_description"];
			}
		}
		$dispatches = $getDispatches;
		
		

		return $dispatches;
	}
	
	/**
	 * Get shippingcosts
	 * @param array $countryInfo - data from shipping country
	 * @param double $surcharge - surcharge for current payment
	 * @access public
	 * @return array
	 */
	public function sGetShippingcosts($countryInfo,$surcharge=0,$surchargestring=""){

		if (!empty($this->sSYSTEM->sCONFIG['sPREMIUMSHIPPIUNG'])){
			return $this->sGetPremiumShippingcosts($countryInfo);
		}
		if(empty($countryInfo)) {
			return false;
		}
		if ($surchargestring){
			$surchargestring = explode(";",$surchargestring);
			foreach($surchargestring as $countrySurcharge){
				$countrySurcharge = explode(":",$countrySurcharge);
				$countrySurcharge[1] = floatval(str_replace(",",".",$countrySurcharge[1]));
				if ($countrySurcharge[1]){
					$countrySurcharges[$countrySurcharge[0]] = $countrySurcharge[1];
				}
			}
		}
		if ($countrySurcharges[$countryInfo["countryiso"]]) $surcharge+=$countrySurcharges[$countryInfo["countryiso"]];
		
		$shippingDebug = false;

		if ($this->sSYSTEM->sCurrency["factor"]){
			$currencyFactor = $this->sSYSTEM->sCurrency["factor"];
		}else {
			$currencyFactor = 1;
		}
		// CHECK CHOOSEN DISPATCH OR SELECT DEFAULT ONE @@ SHOPWARE 2.0.4
		// #################################################################################################
		if ($this->sSYSTEM->_SESSION["sDispatch"]){
			
			$getDispatch = $this->sSYSTEM->sDB_CONNECTION->GetRow("SELECT 
			id, name, description,shippingfree FROM s_shippingcosts_dispatch,s_shippingcosts_dispatch_countries  
			WHERE 
				active = 1 
			AND
				s_shippingcosts_dispatch.id = s_shippingcosts_dispatch_countries.typeID
			AND
				s_shippingcosts_dispatch_countries.countryID = {$countryInfo["id"]}
			AND s_shippingcosts_dispatch.id = ".intval($this->sSYSTEM->_SESSION["sDispatch"]));
		}
		if (!$getDispatch["id"]){
			// Read default dispatch
			$getDispatch = $this->sSYSTEM->sDB_CONNECTION->GetRow("SELECT 
			id, name, description,shippingfree FROM s_shippingcosts_dispatch,s_shippingcosts_dispatch_countries  
			WHERE 
				active = 1 
			AND
				s_shippingcosts_dispatch.id = s_shippingcosts_dispatch_countries.typeID
			AND
				s_shippingcosts_dispatch_countries.countryID = {$countryInfo["id"]}
			ORDER BY position ASC LIMIT 1");
			$this->sSYSTEM->_SESSION["sDispatch"] = $getDispatch["id"]; // Reset dispatch
		}
		

		// #################################################################################################
		
		// #################################################################################################
		// READ SHIPPINGFREE-FROM VALUE (ORDER: COUNTRY / AREA)
		// #################################################################################################
		
		if ($countryInfo["shippingfree"]){
			$shippingfreeFrom = $countryInfo["shippingfree"];
			
		}else {
			// Check for area - value
			$checkAreaShippingfree = $this->sSYSTEM->sDB_CONNECTION->GetRow("
			SELECT shippingfree FROM s_shippingcosts_areas WHERE name = '{$countryInfo["countryarea"]}'
			");
			
			if ($checkAreaShippingfree["shippingfree"]){
				$shippingfreeFrom = $checkAreaShippingfree["shippingfree"];
			}else {
				$shippingfreeFrom = 9999999999999999;
			}
		}
		
		

		if ($shippingDebug) echo "Read Dispatch ".$getDispatch["id"]." | Shippingfree: $shippingfreeFrom <br />";
		if ($shippingDebug) print_r($getDispatch);
		// #################################################################################################
		
		// #################################################################################################
		// CHECK IF ANY PHYSICAL ARTICLES ARE IN BASKET
		// #################################################################################################
		$sql = "
		SELECT id, esdarticle FROM s_order_basket WHERE sessionID=?
		AND modus=0
		ORDER BY esdarticle DESC
		";
		
		$checkForEsdOnly = $this->sSYSTEM->sDB_CONNECTION->GetAll($sql,array($userData["shippingaddress"]["countryID"]));
		
		foreach ($checkForEsdOnly as $esdCheck){
			if ($esdCheck["esdarticle"]){
				$esdOnly = true;
			}else {
				$esdOnly = false;
			}
		}
		
		if ($esdOnly){
			if ($shippingDebug) echo "##LEAVE AT ESD##<br />";
			if (!empty($this->sSYSTEM->sCONFIG["sIGNORESHIPPINGFREEFORSURCHARGES"]) && $surcharge>0){
				$return = array("brutto"=>($surcharge*$currencyFactor),"netto"=>$this->sSYSTEM->sMODULES['sArticles']->sRound($surcharge/(100+$this->sSYSTEM->sCONFIG['sTAXSHIPPING'])*100)*$currencyFactor);
				return $return;
			}else {
				return "0";
			}
		}
		// CHECK FOR ESD-ONLY 
		// #################################################################################################
		
		
		// #################################################################################################
		// CHECK FOR SHIPPINGFREE-ARTICLE (IF IS ALLOWED BY DISPATCH)
		// #################################################################################################
		$checkForShippingFree = $this->sSYSTEM->sDB_CONNECTION->GetRow("
		SELECT id FROM s_order_basket WHERE shippingfree=1 AND sessionID=?
		",array($this->sSYSTEM->sSESSION_ID));

		if ($checkForShippingFree["id"] && $getDispatch["shippingfree"]){
			if ($shippingDebug) echo "##LEAVE AT SHIPPINGFREE POSITION##<br />";
			if (!empty($this->sSYSTEM->sCONFIG["sIGNORESHIPPINGFREEFORSURCHARGES"]) && $surcharge>0){
				$return = array("brutto"=>($surcharge*$currencyFactor),"netto"=>$this->sSYSTEM->sMODULES['sArticles']->sRound($surcharge/(100+$this->sSYSTEM->sCONFIG['sTAXSHIPPING'])*100)*$currencyFactor);
				return $return;
			}else {
				return "0";
			}
		}
		
		// #################################################################################################
		// CHECK IF NEEDED TOTAL-AMOUNT FOR SHIPPING-FREE IS REACHED (AND ALLOWED BY DISPATCH)
		// #################################################################################################
		
		
		
		$this->sSYSTEM->sMODULES['sBasket']->sBASKET = $this->sSYSTEM->sMODULES['sBasket']->sGetBasket();
		
		
		$getBasket = $this->sSYSTEM->sMODULES['sBasket']->sBASKET;		
		
		if (($getBasket["AmountNumeric"]/$currencyFactor)>=$shippingfreeFrom && $getDispatch["shippingfree"]){
			if ($shippingDebug) echo "##LEAVE AT SHIPPINGFREE REACHED $shippingfreeFrom##<br />";
			if (!empty($this->sSYSTEM->sCONFIG["sIGNORESHIPPINGFREEFORSURCHARGES"]) && $surcharge>0){
				$return = array("brutto"=>($surcharge*$currencyFactor),"netto"=>$this->sSYSTEM->sMODULES['sArticles']->sRound($surcharge/(100+$this->sSYSTEM->sCONFIG['sTAXSHIPPING'])*100)*$currencyFactor);
				return $return;
			}else {
				return "0";
			}
		}else {
			// Calculate difference to shippingfree
			if (!empty($getDispatch["shippingfree"]) && $shippingfreeFrom && $shippingfreeFrom<=10000){
				$difference = $shippingfreeFrom - ($getBasket["AmountNumeric"]/$currencyFactor);
				
				$difference = $this->sSYSTEM->sMODULES['sArticles']->sRound($difference*$currencyFactor);
				$difference = array("float"=>$difference,"formated"=>$this->sSYSTEM->sMODULES['sArticles']->sFormatPrice($difference));
			}
		}

		// #################################################################################################
		// GET BASKET WEIGHT
		$getBasketWeight = $this->sSYSTEM->sMODULES['sBasket']->sGetBasketWeight();
		
		if ($shippingDebug) echo "Gewicht $getBasketWeight<br />";
		if (!$getBasketWeight) $getBasketWeight = "0";
		$calcWeight = $getBasketWeight;
		// #################################################################################################
		
		
		// #################################################################################################
		// CHECK FOR COUNTRY INDIVIDUAL SHIPPING-COSTS
		// #################################################################################################
		$sql = "
			SELECT id, shippingcosts, factor
			FROM s_shippingcosts
			WHERE countryID=?
			AND `from` <= ? AND (`to` >= ? OR `to`='0')  
			AND typeID=?
		";
		//echo $sql;
		$selectCountry = $this->sSYSTEM->sDB_CONNECTION->GetRow($sql,array(
			$countryInfo['id'],
			$getBasketWeight,
			$getBasketWeight,
			$getDispatch["id"]
		));
		
		
		if ($shippingDebug) print_r($selectCountry);
		
		if ($selectCountry["factor"]){
			//echo "#$calcWeight#";
			//echo ($selectCountry["factor"]*round($calcWeight,2));
			$selectCountry["shippingcosts"] += ($selectCountry["factor"]*round($calcWeight,2)); 
		}

		if ($selectCountry["id"]){
			if ($shippingDebug) echo "LEAVING #1";
			$return = array("difference"=>$difference,"brutto"=>(($selectCountry["shippingcosts"]+$surcharge)*$currencyFactor),"netto"=>$this->sSYSTEM->sMODULES['sArticles']->sRound((($selectCountry["shippingcosts"]+$surcharge)/(100+$this->sSYSTEM->sCONFIG['sTAXSHIPPING'])*100)*$currencyFactor));
			//print_r($return);
			return $return;
		}
		
		// #################################################################################################
		
		// #################################################################################################
		// CHECK FOR AREA  SHIPPING-COSTS
		// #################################################################################################
		$sql = "
		SELECT shippingcosts, factor
		FROM s_shippingcosts
		WHERE area=?
		AND `from` <= ? AND (`to` >= ? OR `to`='0')  
		AND typeID = ?
		";

		$selectCountry = $this->sSYSTEM->sDB_CONNECTION->GetRow($sql,array(
			$countryInfo['countryarea'],
			$getBasketWeight,
			$getBasketWeight,
			$getDispatch['id'],
		));
		if ($shippingDebug) print_r($selectCountry);
		if ($selectCountry["factor"]){
			$selectCountry["shippingcosts"] += ($selectCountry["factor"]*round($calcWeight,2)); 
		}
		
		if ($selectCountry["shippingcosts"] || $surcharge){
				$return = array("difference"=>$difference,"brutto"=>(($selectCountry["shippingcosts"]+$surcharge)*$currencyFactor),"netto"=>$this->sSYSTEM->sMODULES['sArticles']->sRound((($selectCountry["shippingcosts"]+$surcharge)/(100+$this->sSYSTEM->sCONFIG['sTAXSHIPPING'])*100)*$currencyFactor,2));
				//print_r($return);		
				return $return;
		}
		// #################################################################################################
		#echo "EOF SHIPPINGCOSTS";
		
		return false;
	
	}
	
	/**
	 * Shopware Risk-Management
	 * @param int $paymentID - payment id (s_core_paymentmeans.id)
	 * @param array $basket - current shoppingcart
	 * @param array $user -  user data
	 * @access public
	 * @return boolean
	 */
	public function sManageRisks($paymentID,$basket,$user){
		// Get all assigned rules
		$queryRules = $this->sSYSTEM->sDB_CONNECTION->GetAll("
		SELECT rule1, value1, rule2, value2
		FROM s_core_rulesets
		WHERE paymentID = ?
		ORDER BY id ASC
		",array($paymentID));
		
		if (!count($queryRules)) return false;
		
		// Get-User-Data
		
		// Get Basket
		
		
		foreach ($queryRules as $rule){
			if ($rule["rule1"] && !$rule["rule2"]){
				$rule["rule1"] = "sRisk".$rule["rule1"];
				if ($rule["rule2"]) $rule["rule2"] = "sRisk".$rule["rule2"];
				if ($this->$rule["rule1"]($user,$basket,$rule["value1"])){
					return true;
				}
			}elseif ($rule["rule1"] && $rule["rule2"]){
				$rule["rule1"] = "sRisk".$rule["rule1"];
				if ($rule["rule2"]) $rule["rule2"] = "sRisk".$rule["rule2"];
				// AND 
				if ($this->$rule["rule1"]($user,$basket,$rule["value1"]) && $this->$rule["rule2"]($user,$basket,$rule["value2"])){
					return true;
				}
			}
		}
	}
	
	/**
	 * Risk-Management Order value greater then
	 * @param  $user
	 * @param  $order
	 * @param  $value
	 * @return bool
	 */
	public function sRiskORDERVALUEMORE ($user, $order, $value){
		#print_r($user);
		#print_r($order);
		$basketValue = $order["AmountNumeric"];
		
		if ($this->sSYSTEM->sCurrency["factor"]){
			$factor = $this->sSYSTEM->sCurrency["factor"];
			$basketValue /= $factor;
		}else {
			$factor = 1;
		}
		
		if ($basketValue>=$value){
			return true;
		}else {
			return false;
		}
	}

	/**
	 * Risk-Management Order value less then
	 * @param  $user
	 * @param  $order
	 * @param  $value
	 * @return bool
	 */
	public function sRiskORDERVALUELESS ($user, $order, $value){
		$basketValue = $order["AmountNumeric"];
		
		if ($this->sSYSTEM->sCurrency["factor"]){
			$factor = $this->sSYSTEM->sCurrency["factor"];
			$basketValue /= $factor;
		}else {
			$factor = 1;
		}
		
		if ($basketValue<=$value){
			return true;
		}else {
			return false;
		}
	}

	/**
	 * Risk-Management customer group match x
	 * @param  $user
	 * @param  $order
	 * @param  $value
	 * @return bool
	 */
	public function sRiskCUSTOMERGROUPIS ($user, $order, $value){
		if ($user["additional"]["user"]["customergroup"]==$value){
			return true;
		}else {
			return false;
		}
	}

	/**
	 * Risk-Management customer group match not
	 * @param  $user
	 * @param  $order
	 * @param  $value
	 * @return bool
	 */
	public function sRiskCUSTOMERGROUPISNOT ($user, $order, $value){
		if ($user["additional"]["user"]["customergroup"]!=$value){
			return true;
		}else {
			return false;
		}
	}

	/**
	 * Risk-Management zip code is 
	 * @param  $user
	 * @param  $order
	 * @param  $value
	 * @return bool
	 */
	public function sRiskZIPCODE ($user, $order, $value){
		if ($value=="-1") $value = "";
		if ($user["shippingaddress"]["zipcode"]==$value || $user["billingaddress"]["zipcode"]==$value){
			return true;
		}else {
			return false;
		}
	}

	/**
	 * Risk-Management  country zone is
	 * @param  $user
	 * @param  $order
	 * @param  $value
	 * @return bool
	 */
	public function sRiskZONEIS ($user, $order, $value){
		if ($user["additional"]["countryShipping"]["countryarea"]==$value){
			return true;
		}else {
			return false;
		}
	}

	/**
	 * Risk-Management country zone is not
	 * @param  $user
	 * @param  $order
	 * @param  $value
	 * @return bool
	 */
	public function sRiskZONEISNOT ($user, $order, $value){
	
		if ($user["additional"]["countryShipping"]["countryarea"]!=$value){
			return true;
		}else {
			return false;
		}
	}

	/**
	 * Risk-Management country is
	 * @param  $user
	 * @param  $order
	 * @param  $value
	 * @return bool
	 */
	public function sRiskLANDIS ($user, $order, $value){
		if (preg_match("/$value/",$user["additional"]["countryShipping"]["countryiso"])){
			return true;
		}
		if ($user["additional"]["countryShipping"]["countryiso"]==$value){
			return true;
		}else {
			return false;
		}
	}

	/**
	 * Risk-Management country is not
	 * @param  $user
	 * @param  $order
	 * @param  $value
	 * @return bool
	 */
	public function sRiskLANDISNOT ($user, $order, $value){
		if (!preg_match("/$value/",$user["additional"]["countryShipping"]["countryiso"])){
			return true;
		}
		
		if ($user["additional"]["countryShipping"]["countryiso"]!=$value){
			return true;
		}else {
			return false;
		}
	}


	/**
	 * Risk-Management customer is new
	 * @param  $user
	 * @param  $order
	 * @param  $value
	 * @return bool
	 */
	public function sRiskNEWCUSTOMER ($user, $order, $value){
		if ($user["additional"]["user"]["firstlogin"]==date("Y-m-d") || !$user["additional"]["user"]["firstlogin"]){
			return true;
		}else {
			return false;
		}
	}

	/**
	 * Risk-Management order has more then x positions
	 * @param  $user
	 * @param  $order
	 * @param  $value
	 * @return bool
	 */
	public function sRiskORDERPOSITIONSMORE ($user, $order, $value){
		if (count($order["content"])>=$value){
			return true;
		}else {
			return false;
		}
	}

	/**
	 * Risk-Management Article attribute x from basket - positions is y
	 * @param  $user
	 * @param  $order
	 * @param  $value
	 * @return bool
	 */
	public function sRiskATTRIS ($user,$order,$value){
		if (!empty($order["content"])){
						
			$value = explode("|",$value);
			if (!empty($value[0]) && isset($value[1])){
			$sql = "
			SELECT s_articles_attributes.id
			FROM s_order_basket, s_articles_attributes, s_articles_details
			WHERE
			s_order_basket.sessionID=?
			AND s_order_basket.modus=0
			AND (
			s_order_basket.ordernumber = s_articles_details.ordernumber
			OR (s_order_basket.articleID = s_articles_details.articleID AND s_articles_details.kind = 1)
			)
			AND s_articles_details.id = s_articles_attributes.articledetailsID
			AND s_articles_attributes.{$value[0]} = '{$value[1]}'
			LIMIT 1
			";
			
			$checkArticle = $this->sSYSTEM->sDB_CONNECTION->GetOne($sql,array($this->sSYSTEM->sSESSION_ID));
			if ($checkArticle){
				return true;
			}else {
				return false;
			}
			
			}else {
				return false;
			}
		}
	}

	/**
	 * Risk-Management article attribute x from basket is not y
	 * @param  $user
	 * @param  $order
	 * @param  $value
	 * @return bool
	 */
	public function sRiskATTRISNOT ($user,$order,$value){
		if (!empty($order["content"])){
						
			$value = explode("|",$value);
			if (!empty($value[0]) && isset($value[1])){
			$sql = "
			SELECT s_articles_attributes.id
			FROM s_order_basket, s_articles_attributes, s_articles_details
			WHERE
			s_order_basket.sessionID=?
			AND s_order_basket.modus=0
			AND (
			s_order_basket.ordernumber = s_articles_details.ordernumber
			OR (s_order_basket.articleID = s_articles_details.articleID AND s_articles_details.kind = 1)
			)
			AND s_articles_details.id = s_articles_attributes.articledetailsID
			AND s_articles_attributes.{$value[0]}!= '{$value[1]}'
			LIMIT 1
			";
			$checkArticle = $this->sSYSTEM->sDB_CONNECTION->GetOne($sql,array($this->sSYSTEM->sSESSION_ID));
			
			
			if ($checkArticle){
				return true;
			}else {
				return false;
			}
			
			}else {
				return false;
			}
			
	}
	}

	/**
	 * Risk-Management customer had payment problems in past
	 * @param  $user
	 * @param  $order
	 * @param  $value
	 * @return bool
	 */
	public function sRiskINKASSO ($user, $order, $value){
		if ($this->sSYSTEM->_SESSION["sUserId"]){
			$checkOrder = $this->sSYSTEM->sDB_CONNECTION->GetRow("
			SELECT id FROM s_order WHERE cleared=16 AND userID=".$this->sSYSTEM->_SESSION["sUserId"]);
			if ($checkOrder["id"]){
				return true;
			}else {
				return false;
			}
		}else {
			return false;
		}
	}

	/**
	 * Risk-Management Last order less x days
	 * @param  $user
	 * @param  $order
	 * @param  $value
	 * @return bool
	 */
	public function sRiskLASTORDERLESS ($user, $order, $value){
		// A order from previous x days must exists
		if ($this->sSYSTEM->_SESSION["sUserId"]){
			$sql = "
			SELECT id FROM s_order WHERE userID=".$this->sSYSTEM->_SESSION["sUserId"]."
			AND TO_DAYS(ordertime) <= (TO_DAYS(now())-$value) LIMIT 1
			";		
			$checkOrder = $this->sSYSTEM->sDB_CONNECTION->GetRow($sql);
			if (!$checkOrder["id"]){
				return true;
			}else {
				return false;
			}
		}else {
			return true;
		}
	}

	/**
	 * Risk-Management articles from a certain category
	 * @param  $user
	 * @param  $order
	 * @param  $value
	 * @return bool
	 */
	public function sRiskARTICLESFROM ($user, $order, $value){
		$checkArticle = $this->sSYSTEM->sDB_CONNECTION->GetOne("
			SELECT s_articles_categories.id as id
			FROM s_order_basket, s_articles_categories
			WHERE s_order_basket.articleID = s_articles_categories.articleID
			AND s_articles_categories.categoryID = $value
			AND s_order_basket.sessionID='{$this->sSYSTEM->sSESSION_ID}'
			AND s_order_basket.modus=0
		");
		if (!empty($checkArticle))
			return true;
		else
			return false;
	}

	/**
	 * Risk-Management Order value greater then
	 * @param  $user
	 * @param  $order
	 * @param  $value
	 * @return bool
	 */
	public function sRiskLASTORDERSLESS($user,$order,$value){
		if ($this->sSYSTEM->_SESSION["sUserId"]){
			$checkOrder = $this->sSYSTEM->sDB_CONNECTION->GetAll("
			SELECT id FROM s_order WHERE status != -1 AND status != 4 AND userID=".$this->sSYSTEM->_SESSION["sUserId"]);
			if (count($checkOrder)<$value){
				return true;
			}
		}else {
			return true;
		}
		
		return false;
	}

	/**
	 * Risk management Block if street contains pattern
	 * @param  $user
	 * @param  $order
	 * @param  $value
	 * @return bool
	 */
	public function sRiskPREGSTREET ($user, $order, $value){
		$value = strtolower($value);
		if (preg_match("/$value/",strtolower($user["shippingaddress"]["street"]))){
			return true;
		}else {
			return false;
		}
	}

	/**
	 * Risk-Management block if billing address not eqal to shipping address
	 * @param  $user
	 * @param  $order
	 * @param  $value
	 * @return bool
	 */
	public function sRiskDIFFER ($user, $order, $value){
			//echo "test"; 
		if (strtolower($user["shippingaddress"]["street"]) != strtolower($user["billingaddress"]["street"])){
			return true;
		}else {
			return false;
		}
	}
	
	/**
	 * Risk-Management block if customernumber matches pattern
	 * @param  $user
	 * @param  $order
	 * @param  $value
	 * @return bool
	 */
	public function sRiskCUSTOMERNR ($user, $order, $value){
			//echo "test"; 
		if ($user["billingaddress"]["customernumber"]==$value && !empty($value)){
			return true;
		}else {
			return false;
		}
	}
	
	/**
	 * Risk-Management block if lastname matches pattern
	 * @param  $user
	 * @param  $order
	 * @param  $value
	 * @return bool
	 */
	public function sRiskLASTNAME ($user, $order, $value){
		$value = strtolower($value);
		if (preg_match("/$value/",strtolower($user["shippingaddress"]["lastname"])) || preg_match("/$value/",strtolower($user["billingaddress"]["lastname"]))){
			return true;
		}else {
			return false;
		}
	}
	
	/**
	 * Risk-Management  Block if subshop id is x
	 * @param  $user
	 * @param  $order
	 * @param  $value
	 * @return bool
	 */
	public function sRiskSUBSHOP ($user, $order, $value){
		
		if ($this->sSYSTEM->sSubShop["id"]==$value){
			return true;
		}else {
			return false;
		}
	}

	/**
	 * Risk-Management  Block if subshop id is not x
	 * @param  $user
	 * @param  $order
	 * @param  $value
	 * @return bool
	 */
	public function sRiskSUBSHOPNOT ($user, $order, $value){
		
		if ($this->sSYSTEM->sSubShop["id"]!=$value){
			return true;
		}else {
			return false;
		}
	}

	/**
	 * Risk-Management Block if currency id is not x
	 * @param  $user
	 * @param  $order
	 * @param  $value
	 * @return bool
	 */
	public function sRiskCURRENCIESISOIS ($user, $order, $value){
		if(strtolower($this->sSYSTEM->sCurrency['currency']) == strtolower($value))
		{
			return true;
		}else{
			return false;
		}
	}

	/**
	 * Risk-Management Block if currency id is x
	 * @param  $user
	 * @param  $order
	 * @param  $value
	 * @return bool
	 */
	public function sRiskCURRENCIESISOISNOT ($user, $order, $value){
		if(strtolower($this->sSYSTEM->sCurrency['currency']) != strtolower($value))
		{
			return true;
		}else{
			return false;
		}
	}
	

	/**
	 * Subscribe / unsubscribe to mailing list
	 * @param string $email - mail
	 * @param boolean $unsubscribe
	 * @param id $groupID id of the mailinglist group
	 * @access public
	 * @return boolean
	 */
	public function sNewsletterSubscription ($email, $unsubscribe=false, $groupID=null)
	{
		if(empty($unsubscribe))
		{
			$errorflag = array();

			/**
			 * Only the mail address needs to be a mandatory item
			 * @ticket #5781
			 * @author S.Pohl <stp@shopware.de>
			 * @date 2011-07-27
			 */
			$fields = array('newsletter');
			foreach ($fields as $field)
			{
				if(isset($this->sSYSTEM->_POST[$field])&&empty($this->sSYSTEM->_POST[$field]))
				{
					$errorflag[$field] = true;
				}
			}
			if(!empty($errorflag))
			{
				return array(
					'code' => 5,
					'message' => $this->sSYSTEM->sCONFIG['sErrors']['sErrorBillingAdress'],
					'sErrorFlag' => $errorflag
				);
			}
		}
		
		if(empty($groupID))
		{
			$groupID = $this->sSYSTEM->sCONFIG["sNEWSLETTERDEFAULTGROUP"];
			$sql = '
				INSERT IGNORE INTO s_campaigns_groups (id, name)
				VALUES (?, ?)
			';
			$this->sSYSTEM->sDB_CONNECTION->Execute($sql, array($groupID, 'Newsletter-Empf�nger'));
		}
		
		$email = trim(strtolower(stripslashes($email)));
		if(empty($email))
			return array("code"=>6, "message"=>$this->sSYSTEM->sCONFIG['sSnippets']["sErrorEnterEmail"]);
		$reg = "/^(([^<>()[\]\\\\.,;:\s@\"]+(\.[^<>()[\]\\\\.,;:\s@\"]+)*)|(\"([^\"\\\\\r]|(\\\\[\w\W]))*\"))@((\[([0-9]{1,3}\.){3}[0-9]{1,3}\])|(([a-z\-0-9����������������]+\.)+[a-z]{2,}))$/i";
		if(!preg_match($reg, $email))
			return array("code"=>1, "message"=>$this->sSYSTEM->sCONFIG['sSnippets']["sErrorValidEmail"]);
			
		if(!$unsubscribe)
		{
			$sql = "SELECT * FROM s_campaigns_mailaddresses WHERE email=?";
			$result = $this->sSYSTEM->sDB_CONNECTION->Execute($sql, array($email));
			if($result===false) {
				$result = array("code"=>10,"message"=>$this->sSYSTEM->sCONFIG['sSnippets']["sErrorUnknow"]);
			} elseif($result->RecordCount()) {
				$result = array("code"=>2,"message"=>$this->sSYSTEM->sCONFIG['sSnippets']["sInfoEmailAlreadyRegiested"]);
			} else {
				$sql = "INSERT INTO s_campaigns_mailaddresses (`groupID`,email) VALUES(?, ?)";
				$result = $this->sSYSTEM->sDB_CONNECTION->Execute($sql, array($groupID, $email));
				
				if($result===false)
					$result = array("code"=>10,"message"=>$this->sSYSTEM->sCONFIG['sSnippets']["sErrorUnknow"]);
				else 
					$result = array("code"=>3,"message"=>$this->sSYSTEM->sCONFIG['sSnippets']["sInfoEmailRegiested"]);
			}
		}
		else
		{
			$sql = "DELETE FROM s_campaigns_mailaddresses WHERE email=?";
			$result1 = $this->sSYSTEM->sDB_CONNECTION->Execute($sql, array($email));
			$result = $this->sSYSTEM->sDB_CONNECTION->Affected_Rows();
			$sql = "UPDATE s_user SET newsletter=0 WHERE email=?";
			$result12 =$this->sSYSTEM->sDB_CONNECTION->Execute($sql, array($email));
			$result += $this->sSYSTEM->sDB_CONNECTION->Affected_Rows();
			if($result1===false||$result2===false)
				$result = array("code"=>10,"message"=>$this->sSYSTEM->sCONFIG['sSnippets']["sErrorUnknow"]);
			elseif(empty($result))
				$result = array("code"=>4,"message"=>$this->sSYSTEM->sCONFIG['sSnippets']["sErrorEmailNotFound"]);
			else 
				$result = array("code"=>5,"message"=>$this->sSYSTEM->sCONFIG['sSnippets']["sInfoEmailDeleted"]);
		}
		if(!empty($result['code'])&&in_array($result['code'], array(2,3)))
		{
			$sql = '
				REPLACE INTO `s_campaigns_maildata` (`email`, `groupID`, `salutation`, `title`, `firstname`, `lastname`, `street`, `streetnumber`, `zipcode`, `city`, `added`)
				VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, '.$this->sSYSTEM->sDB_CONNECTION->sysTimeStamp.')
			';
			$this->sSYSTEM->sDB_CONNECTION->Execute($sql, array(
				$email,
				$groupID,
				$this->sSYSTEM->_POST['salutation'],
				$this->sSYSTEM->_POST['title'],
				$this->sSYSTEM->_POST['firstname'],
				$this->sSYSTEM->_POST['lastname'],
				$this->sSYSTEM->_POST['street'],
				$this->sSYSTEM->_POST['streetnumber'],
				$this->sSYSTEM->_POST['zipcode'],
				$this->sSYSTEM->_POST['city']
			));
		}
		elseif(!empty($unsubscribe))
		{
			$sql = 'DELETE FROM `s_campaigns_maildata` WHERE `email`=? AND `groupID`=?';
			//$sql = 'UPDATE `s_campaigns_maildata` SET `deleted`='.$this->sSYSTEM->sDB_CONNECTION->sysTimeStamp.' WHERE `email`=? AND `groupID`=?';
			$this->sSYSTEM->sDB_CONNECTION->Execute($sql, array($email, $groupID));
		}
		
		return $result;
	}
	
	/**
	 * Generate table with german holidays
	 * @access public
	 * @return boolean
	 */
	public function sCreateHolidaysTable ()
	{
		if(!function_exists('easter_days'))
		{
			function easter_days ($year)
			{
				$G = $year % 19;
				$C = (int)($year / 100);
				$H = (int)($C - (int)($C / 4) - (int)((8*$C+13) / 25) + 19*$G + 15) % 30;
				$I = (int)$H - (int)($H / 28)*(1 - (int)($H / 28)*(int)(29 / ($H + 1))*((int)(21 - $G) / 11));
				$J = ($year + (int)($year/4) + $I + 2 - $C + (int)($C/4)) % 7;
				$L = $I - $J;
				$m = 3 + (int)(($L + 40) / 44);
				$d = $L + 28 - 31 * ((int)($m / 4));
				$E = mktime(0,0,0, $m, $d, $year)-mktime(0,0,0,3,21,$year);
				return intval(round($E/(60*60*24),0));
			}
		}
		$sql = "
			SELECT id, calculation, `date`
			FROM `s_premium_holidays`
			WHERE `date`<CURDATE()
		";
		$holidays = $this->sSYSTEM->sDB_CONNECTION->CacheGetAssoc(60,$sql);
		if(empty($holidays)) return true;
		
		foreach ($holidays as $id => $holiday)
		{
			$calculation = $holiday['calculation'];
			$datestamp = strtotime($holiday['date']); 
			$date = date('Y-m-d',$datestamp);
			$year = date('Y',$datestamp)+1;
			$easter_date = date('Y-m-d',mktime(0,0,0,3,21+easter_days($year),$year));
			
			$calculation = preg_replace("#DATE\('(\d+)[\-/](\d+)'\)#i","DATE(CONCAT(YEAR(),'-','\$1-\$2'))",$calculation);
			$calculation = str_replace("EASTERDATE()","'$easter_date'",$calculation);
			$calculation = str_replace("YEAR()","'$year'",$calculation);
			$calculation = str_replace("DATE()","'$date'",$calculation);
			$sql = "UPDATE s_premium_holidays SET `date`= $calculation WHERE id = $id";
			$this->sSYSTEM->sDB_CONNECTION->Execute($sql);
		}
	}
	
	/**
	 * Get a specific country
	 * @param int $country - s_core_countries.id
	 * @access public
	 * @return array
	 */
	public function sGetCountry ($country)
	{
		static $cache = array();
		if(empty($country))
			return false;
		if(isset($cache[$country]))
			return $cache[$country];
		if(is_numeric($country))
			$sql = "c.id=".$country;
		elseif(is_string($country)) 
			$sql = "c.countryiso=".$this->sSYSTEM->sDB_CONNECTION->qstr($country);
		else 
			return false;
		$sql = "
			SELECT c.id, c.id as countryID, countryname, countryiso, countryarea, countryen, c.position, notice, IF(c.shippingfree=0,ca.shippingfree,c.shippingfree) as shippingfree, ca.name as areakey, ca.name as areadescription
			FROM s_core_countries c
			LEFT JOIN s_shippingcosts_areas AS ca
			ON ca.name=c.countryarea
			WHERE $sql
		";
		$currencyFactor = empty($this->sSYSTEM->sCurrency["factor"]) ? 1 : $this->sSYSTEM->sCurrency["factor"];
		$cache[$country]["shippingfree"] = round($cache[$country]["shippingfree"]*$currencyFactor,2);
		return $cache[$country] = $this->sSYSTEM->sDB_CONNECTION->GetRow($sql);
	}
	
	/**
	 * Get a specific payment
	 * @param int $payment - s_core_paymentmeans.id
	 * @access public
	 * @return array
	 */
	public function sGetPaymentmean ($payment)
	{
		static $cache = array();
		if(empty($payment))
			return false;
		if(isset($cache[$country]))
			return $cache[$country];
		if(is_numeric($payment))
			$sql = "id=".$payment;
		elseif(is_string($payment)) 
			$sql = "name=".$this->sSYSTEM->sDB_CONNECTION->qstr($payment);
		else 
			return false;
		$sql = "
			SELECT * FROM s_core_paymentmeans 
			WHERE $sql
		";
		$cache[$payment] = $this->sSYSTEM->sDB_CONNECTION->GetRow($sql);
		
		$cache[$payment]["country_surcharge"] = array();
		if (!empty($cache[$payment]["surchargestring"])){
			foreach(explode(";",$cache[$payment]["surchargestring"]) as $countrySurcharge){
				list($key,$value) = explode(":",$countrySurcharge);
				$value = floatval(str_replace(",",".",$value));
				if (!empty($value)){
					$cache[$payment]["country_surcharge"][$key] = $value;
				}
			}
		}
		return $cache[$payment];
	}
	
	/**
	 * Get dispatch methods
	 * @param int $countryID
	 * @param int $paymentID 
	 * @access public
	 * @return array
	 */
	public function sGetDispatchBasket ($countryID=null, $paymentID = null)
	{
		$sql_select = '';
		if(!empty($this->sSYSTEM->sCONFIG['sPREMIUMSHIPPIUNGASKETSELECT']))
		{
			$sql_select .= ', '.$this->sSYSTEM->sCONFIG['sPREMIUMSHIPPIUNGASKETSELECT'];
		}
		$sql = 'SELECT id, calculation_sql FROM s_premium_dispatch WHERE active=1 AND calculation=3';
		$calculations = $this->sSYSTEM->sDB_CONNECTION->GetAssoc($sql);
		if(!empty($calculations))
		foreach ($calculations as $dispatchID => $calculation)
		{
			if(empty($calculation)) $calculation = $this->sSYSTEM->sDB_CONNECTION->qstr($calculation);
			$sql_select .= ', ('.$calculation.') as calculation_value_'.$dispatchID;
		}
		if (empty($this->sSYSTEM->sUSERGROUPDATA["tax"]) && !empty($this->sSYSTEM->sUSERGROUPDATA["id"])){
			$amount = 'b.quantity*ROUND(CAST(b.price as DECIMAL(10,2))*(100+t.tax)/100,2)';
			$amount_net = 'b.quantity*CAST(b.price as DECIMAL(10,2))';
		} else {
			$amount = 'b.quantity*CAST(b.price as DECIMAL(10,2))';
			$amount_net = 'b.quantity*ROUND(CAST(b.price as DECIMAL(10,2))/(100+t.tax)*100,2)';
		}
		$sql = "
			SELECT
				MIN(IFNULL(g.instock,d.instock)>=b.quantity) as instock,
				MIN(IFNULL(g.instock,d.instock)>=(b.quantity+d.stockmin)) as stockmin,
				MIN(a.laststock) as laststock,
				SUM(d.weight*b.quantity) as weight,
				SUM(IF(a.id,b.quantity,0)) as count_article,
				MAX(b.shippingfree) as shippingfree,
				SUM(IF(b.modus=0,$amount/b.currencyFactor,0)) as amount,
				SUM(IF(b.modus=0,$amount_net/b.currencyFactor,0)) as amount_net,
				SUM(CAST(b.price as DECIMAL(10,2))*b.quantity) as amount_display,
				MAX(t.tax) as max_tax, u.id as userID
				$sql_select
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
			
			LEFT JOIN s_articles_attributes at
			ON at.articledetailsID=d.id
			
			LEFT JOIN s_core_tax t
			ON t.id=a.taxID
			
			LEFT JOIN s_user u
			ON u.id=?
			AND u.active=1
			
			LEFT JOIN s_user_billingaddress ub
			ON ub.userID=u.id
			
			LEFT JOIN s_user_shippingaddress us
			ON us.userID=u.id
			
			WHERE b.sessionID=?
			
			GROUP BY b.sessionID
		";
		$basket = $this->sSYSTEM->sDB_CONNECTION->GetRow($sql,array(
			$this->sSYSTEM->_SESSION["sUserId"],
			empty($this->sSYSTEM->sSESSION_ID) ? session_id() : $this->sSYSTEM->sSESSION_ID
		));
		if(empty($basket))
		{
			return false;
		}
		
		if(!empty($paymentID)) {
			$paymentID = (int) $paymentID;
		} elseif(!empty($this->sSYSTEM->_SESSION['sUserId'])) {
			$user = $this->sGetUserData();
			$paymentID = (int) $user['additional']['payment']['id'];
		} elseif(!empty($this->sSYSTEM->_POST['sPayment'])) {
			$paymentID = (int) $this->sSYSTEM->_POST['sPayment'];
		} elseif(!empty($this->sSYSTEM->_SESSION['sPaymentID'])) {
			$paymentID = (int) $this->sSYSTEM->_SESSION['sPaymentID'];
		}

		$paymentmeans = $this->sGetPaymentMeans();
		$paymentIDs = array();
		foreach ($paymentmeans as $paymentmean) {
			$paymentIDs[] = $paymentmean['id'];
		}
		if(!in_array($paymentID, $paymentIDs)) {
			$paymentID = reset($paymentIDs);
		}

		if(empty($countryID)&&!empty($user['additional']['countryShipping']['id']))
			$countryID = (int) $user['additional']['countryShipping']['id'];
		else
			$countryID = (int) $countryID;
			
		$basket['countryID'] = $countryID;
		$basket['paymentID'] = $paymentID;
		$basket['customergroupID'] = (int) $this->sSYSTEM->sUSERGROUPDATA['id'];
		$basket['multishopID'] = (int) $this->sSYSTEM->sSubShop['id'];
		$basket['sessionID'] = $this->sSYSTEM->sSESSION_ID;

		return $basket;
	}
	
	/**
	 * Get premium dispatch method
	 * @param int $dispatchID
	 * @access public
	 * @return array
	 */
	public function sGetPremiumDispatch ($dispatchID = null)
	{
		$sql = "
			SELECT d.id, name, d.description, calculation, status_link, surcharge_calculation, bind_shippingfree, shippingfree, tax_calculation, t.tax as tax_calculation_value
			FROM s_premium_dispatch d
			LEFT JOIN s_core_tax t
			ON t.id=d.tax_calculation
			WHERE active = 1 
			AND d.id = ?
		";
		$dispatch = $this->sSYSTEM->sDB_CONNECTION->GetRow($sql,array($dispatchID));
		if(empty($dispatch)) return false;
		return $this->sGetDispatchTranslation($dispatch);
	}
	
	/**
	 * Get premium dispatch methods
	 * @param int $countryID
	 * @param int $paymentID
	 * @access public
	 * @return array
	 */
	public function sGetPremiumDispatches ($countryID=null, $paymentID = null)
	{
		$this->sCreateHolidaysTable();
		
		$basket = $this->sGetDispatchBasket($countryID, $paymentID);
		if(empty($basket)) return array();
		
		$sql = "SELECT id, bind_sql FROM s_premium_dispatch WHERE active=1 AND type IN (0) AND bind_sql IS NOT NULL";
		$statements = $this->sSYSTEM->sDB_CONNECTION->GetAssoc($sql);
		$sql_where = "";
		foreach ($statements as $dispatchID => $statement)
		{
			$sql_where .= "
			AND ( d.id!=$dispatchID OR ($statement))
			";
		}
		
		$sql_basket = array();
		foreach ($basket as $key => $value)
		{
			$sql_basket[] = $this->sSYSTEM->sDB_CONNECTION->qstr($value)." as `$key`";
		}
		$sql_basket = implode(', ',$sql_basket);
		
		$sql = "
			SELECT	
				d.id as `key`,
				d.id, d.name,
				d.description,
				d.calculation,
				d.status_link,
				b.*
			FROM s_premium_dispatch d
			
			JOIN ( SELECT $sql_basket ) b
			JOIN s_premium_dispatch_countries dc
			ON d.id = dc.dispatchID
			AND dc.countryID=b.countryID
			JOIN s_premium_dispatch_paymentmeans dp
			ON d.id = dp.dispatchID
			AND dp.paymentID=b.paymentID
			LEFT JOIN s_premium_holidays h
			ON h.date = CURDATE()
			LEFT JOIN s_premium_dispatch_holidays dh
			ON d.id=dh.dispatchID
			AND h.id=dh.holidayID
						
			LEFT JOIN (
				SELECT dc.dispatchID
				FROM s_order_basket b
				JOIN s_articles_categories ac
				ON ac.articleID=b.articleID
				JOIN s_premium_dispatch_categories dc
				ON dc.categoryID=ac.categoryID
				WHERE b.modus=0
				AND b.sessionID='{$this->sSYSTEM->sSESSION_ID}'
				GROUP BY dc.dispatchID
			) as dk
			ON dk.dispatchID=d.id
			
			LEFT JOIN s_user u
			ON u.id=b.userID
			AND u.active=1
			
			LEFT JOIN s_user_billingaddress ub
			ON ub.userID=u.id
			
			LEFT JOIN s_user_shippingaddress us
			ON us.userID=u.id

			WHERE d.active=1 
			AND (
				(bind_time_from IS NULL AND bind_time_to IS NULL)
			OR
				(IFNULL(bind_time_from,0) <= IFNULL(bind_time_to,86400) AND TIME_TO_SEC(DATE_FORMAT(NOW(),'%H:%i:00')) BETWEEN IFNULL(bind_time_from,0) AND IFNULL(bind_time_to,86400))
			OR 
				(bind_time_from > bind_time_to AND TIME_TO_SEC(DATE_FORMAT(NOW(),'%H:%i:00')) NOT BETWEEN bind_time_to AND bind_time_from)
			)
			AND (
				(bind_weekday_from IS NULL AND bind_weekday_to IS NULL)
			OR
				(IFNULL(bind_weekday_from,1) <= IFNULL(bind_weekday_to,7) AND WEEKDAY(NOW())+1 BETWEEN IFNULL(bind_weekday_from,1) AND IFNULL(bind_weekday_to,7))
			OR 
				(bind_weekday_from > bind_weekday_to AND WEEKDAY(NOW())+1 NOT BETWEEN bind_weekday_to AND bind_weekday_from)
			)
			AND (bind_weight_from IS NULL OR bind_weight_from <= b.weight)
			AND (bind_weight_to IS NULL OR bind_weight_to >= b.weight)
			AND (bind_price_from IS NULL OR bind_price_from <= b.amount)
			AND (bind_price_to IS NULL OR bind_price_to >= b.amount)
			AND (bind_instock=0 OR bind_instock IS NULL OR (bind_instock=1 AND b.instock) OR (bind_instock=2 AND b.stockmin))
			AND (bind_laststock=0 OR (bind_laststock=1 AND b.laststock))
			AND (bind_shippingfree!=1 OR NOT b.shippingfree)
			AND dh.holidayID IS NULL
			AND (d.multishopID IS NULL OR d.multishopID=b.multishopID)
			AND (d.customergroupID IS NULL OR d.customergroupID=b.customergroupID)
			AND dk.dispatchID IS NULL
			AND d.type IN (0)
			$sql_where
			GROUP BY d.id
			ORDER BY d.position, d.name
		";
		$dispatches = $this->sSYSTEM->sDB_CONNECTION->GetAssoc($sql);			
		if(empty($dispatches))
		{
			$sql = "
				SELECT	
					d.id as `key`,
					d.id, d.name,
					d.description,
					d.calculation,
					d.status_link
				FROM s_premium_dispatch d
	
				WHERE d.active=1
				AND d.type=1
				GROUP BY d.id
				
				ORDER BY d.position, d.name
				LIMIT 1
			";
			$dispatches = $this->sSYSTEM->sDB_CONNECTION->GetAssoc($sql);
		}
		
		$names = array();
		foreach ($dispatches as $dispatchID => $dispatch)
		{
			if(in_array($dispatch['name'],$names)) unset($dispatches[$dispatchID]);
			else $names[] = $dispatch['name'];
		}
		unset($names);
		
		$object = $this->sGetDispatchTranslation();
		foreach ($dispatches as &$v)
		{
			if (!empty($object[$v['id']]['dispatch_name'])){
				$v['name'] = $object[$v['id']]['dispatch_name'];
			}
			if (!empty($object[$v['id']]['dispatch_description'])){
				$v['description'] = $object[$v['id']]['dispatch_description'];
			}
		}
		return $dispatches;
	}
	
	/**
	 * Get premium dispatch surcharges
	 * @param $basket
	 * @param $type
	 * @access public
	 * @return array
	 */
	public function sGetPremiumDispatchSurcharge ($basket, $type=2)
	{
		if(empty($basket)) return false;
		$type = (int) $type;
		
		$sql = 'SELECT id, bind_sql FROM s_premium_dispatch WHERE active=1 AND type=? AND bind_sql IS NOT NULL';
		$statements = $this->sSYSTEM->sDB_CONNECTION->GetAssoc($sql,array($type));
		$sql_where = '';
		foreach ($statements as $dispatchID => $statement)
		{
			$sql_where .= "
			AND ( d.id!=$dispatchID OR ($statement))
			";
		}
		$sql_basket = array();
		foreach ($basket as $key => $value)
		{
			$sql_basket[] = $this->sSYSTEM->sDB_CONNECTION->qstr($value)." as `$key`";
		}
		$sql_basket = implode(', ',$sql_basket);
		
		$sql = "
			SELECT d.id, d.calculation
			FROM s_premium_dispatch d
			
			JOIN ( SELECT $sql_basket ) b
			JOIN s_premium_dispatch_countries dc
			ON d.id = dc.dispatchID
			AND dc.countryID=b.countryID
			JOIN s_premium_dispatch_paymentmeans dp
			ON d.id = dp.dispatchID
			AND dp.paymentID=b.paymentID
			LEFT JOIN s_premium_holidays h
			ON h.date = CURDATE()
			LEFT JOIN s_premium_dispatch_holidays dh
			ON d.id=dh.dispatchID
			AND h.id=dh.holidayID
						
			LEFT JOIN (
				SELECT dc.dispatchID
				FROM s_order_basket b
				JOIN s_articles_categories ac
				ON ac.articleID=b.articleID
				JOIN s_premium_dispatch_categories dc
				ON dc.categoryID=ac.categoryID
				WHERE b.modus=0
				AND b.sessionID='{$this->sSYSTEM->sSESSION_ID}'
				GROUP BY dc.dispatchID
			) as dk
			ON dk.dispatchID=d.id
			
			LEFT JOIN s_user u
			ON u.id=b.userID
			AND u.active=1
			
			LEFT JOIN s_user_billingaddress ub
			ON ub.userID=u.id
			
			LEFT JOIN s_user_shippingaddress us
			ON us.userID=u.id

			WHERE d.active=1 
			AND (
				(bind_time_from IS NULL AND bind_time_to IS NULL)
			OR
				(IFNULL(bind_time_from,0) <= IFNULL(bind_time_to,86400) AND TIME_TO_SEC(DATE_FORMAT(NOW(),'%H:%i:00')) BETWEEN IFNULL(bind_time_from,0) AND IFNULL(bind_time_to,86400))
			OR 
				(bind_time_from > bind_time_to AND TIME_TO_SEC(DATE_FORMAT(NOW(),'%H:%i:00')) NOT BETWEEN bind_time_to AND bind_time_from)
			)
			AND (
				(bind_weekday_from IS NULL AND bind_weekday_to IS NULL)
			OR
				(IFNULL(bind_weekday_from,1) <= IFNULL(bind_weekday_to,7) AND REPLACE(WEEKDAY(NOW()),0,6)+1 BETWEEN IFNULL(bind_weekday_from,1) AND IFNULL(bind_weekday_to,7))
			OR 
				(bind_weekday_from > bind_weekday_to AND REPLACE(WEEKDAY(NOW()),0,6)+1 NOT BETWEEN bind_weekday_to AND bind_weekday_from)
			)
			AND (bind_weight_from IS NULL OR bind_weight_from <= b.weight)
			AND (bind_weight_to IS NULL OR bind_weight_to >= b.weight)
			AND (bind_price_from IS NULL OR bind_price_from <= b.amount)
			AND (bind_price_to IS NULL OR bind_price_to >= b.amount)
			AND (bind_instock=0 OR bind_instock IS NULL OR (bind_instock=1 AND b.instock) OR (bind_instock=2 AND b.stockmin))
			AND (bind_laststock=0 OR (bind_laststock=1 AND b.laststock))
			AND (bind_shippingfree=2 OR NOT b.shippingfree)
			AND dh.holidayID IS NULL
			AND (d.multishopID IS NULL OR d.multishopID=b.multishopID)
			AND (d.customergroupID IS NULL OR d.customergroupID=b.customergroupID)
			AND dk.dispatchID IS NULL
			AND d.type = $type
			AND (d.shippingfree IS NULL OR d.shippingfree > b.amount)
			$sql_where
			GROUP BY d.id
		";
		$dispatches = $this->sSYSTEM->sDB_CONNECTION->GetAll($sql);
		$surcharge = 0;
		if(!empty($dispatches))
		foreach ($dispatches as $dispatch)
		{
			if(empty($dispatch['calculation']))
				$from = round($basket['weight'],3);
			elseif($dispatch['calculation']==1)
				$from = round($basket['amount'],2);
			elseif($dispatch['calculation']==2)
				$from = round($basket['count_article']);
			elseif($dispatch['calculation']==3)
				$from = round($basket['calculation_value_'.$dispatch['id']]);
			else
				continue;
			$sql = "
				SELECT `value` , `factor`
				FROM `s_premium_shippingcosts`
				WHERE `from` <= $from
				AND `dispatchID` = {$dispatch['id']}
				ORDER BY `from` DESC
				LIMIT 1
			";
			$result = $this->sSYSTEM->sDB_CONNECTION->GetRow($sql);
			if(empty($result)) continue;
			$surcharge += $result['value'];
			if(!empty($result['factor']))
				$surcharge +=  $result['factor']/100*$from;
		}
		return $surcharge;
	}
	
	/**
	 * Get premium shippingcosts
	 * @param int $country - s_core_countries.id
	 * @access public
	 * @return array
	 */
	public function sGetPremiumShippingcosts ($country=null)
	{
		$currencyFactor = empty($this->sSYSTEM->sCurrency['factor']) ? 1 : $this->sSYSTEM->sCurrency['factor'];

		$discount_tax = empty($this->sSYSTEM->sCONFIG['sDISCOUNTTAX']) ? 0 : (float) str_replace(',','.',$this->sSYSTEM->sCONFIG['sDISCOUNTTAX']);
		// Determinate tax automaticly
		if (!empty($this->sSYSTEM->sCONFIG["sTAXAUTOMODE"])){
			$discount_tax = $this->sSYSTEM->sMODULES['sBASKET']->getMaxTax();
		}
		
		$surcharge_ordernumber = isset($this->sSYSTEM->sCONFIG['sPAYMENTSURCHARGEABSOLUTENUMBER']) ? $this->sSYSTEM->sCONFIG['sPAYMENTSURCHARGEABSOLUTENUMBER'] : 'PAYMENTSURCHARGEABSOLUTENUMBER';
		$surcharge_name = isset($this->sSYSTEM->sCONFIG["sPAYMENTSURCHARGEABSOLUTE"]) ? $this->sSYSTEM->sCONFIG["sPAYMENTSURCHARGEABSOLUTE"] : 'Zuschlag f�r Zahlungsart';
		$discount_ordernumber = isset($this->sSYSTEM->sCONFIG['sSHIPPINGDISCOUNTNUMBER']) ? $this->sSYSTEM->sCONFIG['sSHIPPINGDISCOUNTNUMBER'] : 'SHIPPINGDISCOUNT';
		$discount_name = isset($this->sSYSTEM->sCONFIG["sSHIPPINGDISCOUNTNAME"]) ? $this->sSYSTEM->sCONFIG["sSHIPPINGDISCOUNTNAME"] : 'Warenkorbrabatt';
		$percent_ordernumber = isset($this->sSYSTEM->sCONFIG['sPAYMENTSURCHARGENUMBER']) ? $this->sSYSTEM->sCONFIG['sPAYMENTSURCHARGENUMBER']: "PAYMENTSURCHARGE";
		$discount_basket_ordernumber = isset($this->sSYSTEM->sCONFIG['sDISCOUNTNUMBER']) ? $this->sSYSTEM->sCONFIG['sDISCOUNTNUMBER']: 'DISCOUNT';
		$discount_basket_name = isset($this->sSYSTEM->sCONFIG['sDISCOUNTNAME']) ? $this->sSYSTEM->sCONFIG['sDISCOUNTNAME']: 'Warenkorbrabatt';
		
		$sql = 'DELETE FROM s_order_basket WHERE sessionID=? AND modus IN (3, 4) AND ordernumber IN (?, ?, ?, ?)';
		$this->sSYSTEM->sDB_CONNECTION->Execute($sql, array(
			$this->sSYSTEM->sSESSION_ID,
			$surcharge_ordernumber,
			$discount_ordernumber,
			$percent_ordernumber,
			$discount_basket_ordernumber
		));

		$basket = $this->sGetDispatchBasket(empty($country['id']) ? null : $country['id']);
		if(empty($basket)) return false;
		$country = $this->sGetCountry($basket['countryID']);
		if(empty($country)) return false;
		$payment = $this->sGetPaymentmean($basket['paymentID']);
		if(empty($payment)) return false;
		
		$sql = 'SELECT SUM((CAST(price as DECIMAL(10,2))*quantity)/currencyFactor) as amount FROM s_order_basket WHERE sessionID=? GROUP BY sessionID';
		$amount = $this->sSYSTEM->sDB_CONNECTION->GetOne($sql, array($this->sSYSTEM->sSESSION_ID));
		
		$sql = '
			SELECT basketdiscount
			FROM s_core_customergroups_discounts
			WHERE groupID=?
			AND basketdiscountstart<=?
			ORDER BY basketdiscountstart DESC
		';
		$basket_discount = $this->sSYSTEM->sDB_CONNECTION->GetOne($sql,array($this->sSYSTEM->sUSERGROUPDATA['id'], $amount));
				
		if(!empty($basket_discount)){

			$percent = $basket_discount;
			$basket_discount = round($basket_discount/100*$amount, 2);
						
			if (empty($this->sSYSTEM->sUSERGROUPDATA["tax"]) && !empty($this->sSYSTEM->sUSERGROUPDATA["id"])){
				$basket_discount_net = $basket_discount;
			} else {
				$basket_discount_net = round($basket_discount/(100+$discount_tax)*100,2);
			}
			
			$basket_discount_net = $basket_discount_net *-1;
			$basket_discount = $basket_discount *-1;

			$sql = '
				INSERT INTO s_order_basket
					(sessionID, articlename, articleID, ordernumber, quantity, price, netprice, datum, modus, currencyFactor)
				VALUES
					(?, ?, 0, ?, 1, ?, ?, NOW(), 3, ?)
			';


			$this->sSYSTEM->sDB_CONNECTION->Execute($sql,array(
				$this->sSYSTEM->sSESSION_ID,
				'- '.$percent.' % '.$discount_basket_name,
				$discount_basket_ordernumber,
				$basket_discount,
				$basket_discount_net,
				$currencyFactor
			));
		}
		
		$discount = $this->sGetPremiumDispatchSurcharge($basket, 3);
		if(!empty($discount))
		{
			$discount *= -1;
			if (empty($this->sSYSTEM->sUSERGROUPDATA["tax"]) && !empty($this->sSYSTEM->sUSERGROUPDATA["id"])){
				$discount_net = $discount;
			} else {
				$discount_net = round($discount/(100+$discount_tax)*100,2);
			}
			$sql = '
				INSERT INTO s_order_basket
					(sessionID, articlename, articleID, ordernumber, quantity, price, netprice, datum, modus, currencyFactor)
				VALUES
					(?, ?, 0, ?, 1, ?, ?, NOW(), 4, ?)
			';
			$this->sSYSTEM->sDB_CONNECTION->Execute($sql,array(
				$this->sSYSTEM->sSESSION_ID,
				$discount_name,
				$discount_ordernumber,
				$discount,
				$discount_net,
				$currencyFactor
			));
		}
		
		$dispatch = $this->sGetPremiumDispatch((int)$this->sSYSTEM->_SESSION['sDispatch']);
		
		if (!empty($payment['country_surcharge'][$country['countryiso']]))
			$payment['surcharge'] += $payment['country_surcharge'][$country['countryiso']];
		$payment['surcharge'] = round($payment['surcharge']*$currencyFactor,2);
		
		if(!empty($payment['surcharge'])&&(empty($dispatch)||$dispatch['surcharge_calculation']==3))
		{
			$surcharge = round($payment['surcharge'], 2);
			$payment['surcharge'] = 0;
			if (empty($this->sSYSTEM->sUSERGROUPDATA["tax"]) && !empty($this->sSYSTEM->sUSERGROUPDATA["id"])){
				$surcharge_net = $surcharge;
			} else {
				$surcharge_net = round($surcharge/(100+$discount_tax)*100,2);
			}
			$sql = '
				INSERT INTO s_order_basket
					(sessionID, articlename, articleID, ordernumber, quantity, price, netprice, datum, modus, currencyFactor)
				VALUES
					(?, ?, 0, ?, 1, ?, ?, NOW(), 4, ?)
			';
			$this->sSYSTEM->sDB_CONNECTION->Execute($sql,array(
				$this->sSYSTEM->sSESSION_ID,
				$surcharge_name,
				$surcharge_ordernumber,
				$surcharge,
				$surcharge_net,
				$currencyFactor
			));
		}
		if(!empty($payment['debit_percent'])&&(empty($dispatch)||$dispatch['surcharge_calculation']!=2))
		{
			$sql = 'SELECT SUM(quantity*price) as amount FROM s_order_basket WHERE sessionID=? GROUP BY sessionID';
			$amount = $this->sSYSTEM->sDB_CONNECTION->GetOne($sql, array($this->sSYSTEM->sSESSION_ID));
			
			$percent = round($amount / 100 * $payment['debit_percent'], 2);
						
			if ($percent>0) {
				$percent_name = $this->sSYSTEM->sCONFIG["sPAYMENTSURCHARGEADD"];
			} else {
				$percent_name = $this->sSYSTEM->sCONFIG["sPAYMENTSURCHARGEDEV"];
			}
				
			if (empty($this->sSYSTEM->sUSERGROUPDATA["tax"]) && !empty($this->sSYSTEM->sUSERGROUPDATA["id"])){
				$percent_net = $percent;
			} else {
				$percent_net = round($percent/(100+$discount_tax)*100,2);
			}
			
			$sql = '
				INSERT INTO s_order_basket
					(sessionID, articlename, articleID, ordernumber, quantity, price, netprice, datum, modus, currencyFactor)
				VALUES
					(?, ?, 0, ?, 1, ?, ?, NOW(), 4, ?)
			';
			$this->sSYSTEM->sDB_CONNECTION->Execute($sql,array(
				$this->sSYSTEM->sSESSION_ID,
				$percent_name,
				$percent_ordernumber,
				$percent,
				$percent_net,
				$currencyFactor
			));
		}
		
		if(empty($dispatch)) return array('brutto'=>0, 'netto'=>0);
				
		if (empty($this->sSYSTEM->sUSERGROUPDATA["tax"]) && !empty($this->sSYSTEM->sUSERGROUPDATA["id"])){
			$dispatch['shippingfree'] = round($dispatch['shippingfree']/(100+$discount_tax)*100,2);
		} else {
			$dispatch['shippingfree'] = $dispatch['shippingfree'];
		}
		
		if ((!empty($dispatch['shippingfree'])&&$dispatch['shippingfree']<=$basket['amount_display'])
			||empty($basket['count_article'])
			||(!empty($basket['shippingfree'])&&empty($dispatch['bind_shippingfree']))
		)
		{
			if(empty($dispatch['surcharge_calculation'])&&!empty($payment['surcharge']))
				return array(
					'brutto'=>$payment['surcharge'],
					'netto'=>round($payment['surcharge']*100/(100+$this->sSYSTEM->sCONFIG['sTAXSHIPPING']),2)
				);
			else
				return array('brutto'=>0, 'netto'=>0);
		}
				
		if(empty($dispatch['calculation']))
			$from = round($basket['weight'],3);
		elseif($dispatch['calculation']==1)
			$from = round($basket['amount'],2);
		elseif($dispatch['calculation']==2)
			$from = round($basket['count_article']);
		elseif($dispatch['calculation']==3)
			$from = round($basket['calculation_value_'.$dispatch['id']],2);
		else
			return false;
		$sql = "
			SELECT `value` , `factor`
			FROM `s_premium_shippingcosts`
			WHERE `from` <= $from
			AND `dispatchID` = {$dispatch['id']}
			ORDER BY `from` DESC
			LIMIT 1
		";
		$result = $this->sSYSTEM->sDB_CONNECTION->GetRow($sql);
		if($result===false) return false;
		
		if(!empty($dispatch['shippingfree']))
		{
			$result['shippingfree'] = round($dispatch['shippingfree']*$currencyFactor,2);
			$difference = round(($dispatch['shippingfree']-$basket['amount_display'])*$currencyFactor,2);
			$result['difference'] = array("float"=>$difference,"formated"=>$this->sSYSTEM->sMODULES['sArticles']->sFormatPrice($difference));
		}
		$result['brutto'] = $result['value'];
		if(!empty($result['factor']))
			$result['brutto'] +=  $result['factor']/100*$from;
		$result['surcharge'] = $this->sGetPremiumDispatchSurcharge($basket);
		if(!empty($result['surcharge']))
			$result['brutto'] +=  $result['surcharge'];
		$result['brutto'] *= $currencyFactor;
		$result['brutto'] = round($result['brutto'],2);
		if(!empty($payment['surcharge'])&&$dispatch['surcharge_calculation']!=2&&(empty($basket['shippingfree'])||empty($dispatch['surcharge_calculation'])))
		{
			$result['surcharge'] = $payment['surcharge'];
			$result['brutto'] += $result['surcharge'];
		}
		if($result['brutto']<0)
		{
			return array('brutto'=>0, 'netto'=>0);
		}
		if(empty($dispatch['tax_calculation']))
			$result['tax'] = $basket['max_tax'];
		else
			$result['tax'] = $dispatch['tax_calculation_value'];
		$result['netto'] = round($result['brutto']*100/(100+$result['tax']),2);
		return $result;
	}
}
?>
