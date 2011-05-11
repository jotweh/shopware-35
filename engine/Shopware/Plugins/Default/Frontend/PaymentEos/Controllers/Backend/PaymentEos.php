<?php
/**
 * Eos payment controller
 * 
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Heiner Lohaus
 * @package Shopware
 * @subpackage Controllers
 */
class Shopware_Controllers_Frontend_PaymentEos extends Enlight_Controller_Action
{	
	/**
	 * Index action method
	 */
	public function indexAction()
	{
		switch ($this->getPaymentShortName()) {
			case 'eos_credit':
				return $this->forward('credit');
			default:
				return $this->forward('index', 'checkout');
		}
	}
	
	/**
	 * Credit action method
	 */
	public function creditAction()
	{
		if($this->getPaymentShortName() !== 'eos_credit') {
			return $this->forward('index');
		}
		
		$user = $this->getUser();
		$router = $this->Front()->Router();
		$request = $this->Request();
		
		$params = array();
		$params['haendlerid'] = Shopware()->Config()->get('sCLICKPAYMERCHANTID');
		$params['haendlercode'] = Shopware()->Config()->get('sCLICKPAYMERCHANTCODE');
		$params['text'] = Shopware()->Config()->get('sCLICKPAYTEXT');
		$params['referenz'] = $user['billingaddress']['customernumber']
			. '_' . $user['billingaddress']['firstname']
			. '_' . $user['billingaddress']['lastname'];
		$params['referenz'] = str_replace(
			array('Ä', 'Ü', 'Ö', 'ä', 'ü', 'ö', 'ß', ' '),
			array('Ae', 'Ue', 'Oe', 'ae', 'ue', 'oe', 'ss', '_'),
			$params['referenz']
		);
		$params['referenz'] = preg_replace('#[^A-Za-z0-9_]#', '', $params['referenz']);
		$params['bruttobetrag'] = $this->getAmount();
		$params['waehrung'] =  Shopware()->Currency()->getShortName();
		$params['_language'] = Shopware()->Locale()->getLanguage();
		$params['_buchen'] = (int) Shopware()->Config()->get('sCLICKPAYDIRECTBOOK');
		$params['_stylesheet'] = $request->getScheme() . '://' . $request->getHttpHost()
		                       . $request->getBasePath() . '/' . Shopware()->Config()->clickPayStyleSheet;
		
		//if($sClickPay->sGetSnippet('sClickPayButtonCancel'))
		//	$params['_ButtonTextCancel'] = $sClickPay->sGetSnippet('sClickPayButtonCancel');
		//if($sClickPay->sGetSnippet('sClickPayButtonOK'))
		//	$params['_ButtonTextOK'] = $sClickPay->sGetSnippet('sClickPayButtonOK');
			
		$params['karteninhaber'] = $user['billingaddress']['firstname'].' '.$user['billingaddress']['lastname'];
		
		$params['NotifyURL'] = $router->assemble(array(
			'action' => 'notify', 'appendSession' => true,
			'transactionId' => '<<KontaktID>>', 'status' => '<<statuscode>>'
		));
		$params['SuccessURL'] = $router->assemble(array(
			'action' => 'notify', 'appendSession' => true,
			'creditNumber' => '<<Zahlungsdatensatz.Kreditkartennummer_maskiert.>>'
		));
		$params['BackURL'] = $router->assemble(array(
			'action' => 'notify', 'appendSession' => true
		));
		$params['FailURL'] = $router->assemble(array(
			'action' => 'notify', 'appendSession' => true,
			'failMessage' => '<<SPAY_Reservierungen.AUTHRESULT.>>'
		));
		$params['ErrorURL'] = $router->assemble(array(
			'action' => 'notify', 'appendSession' => true
		));
		
		$params['EndURL'] = $router->assemble(array('action' => 'end'));
		
		$requestUrl = 'https://www.eos-payment.de/PaymentGatewayMini_CC.acgi';
		
		$respone = $this->doRequest($requestUrl, $params);
		
		if(!empty($respone['kontaktid'])) {
			$sql = '
				INSERT INTO `s_plugin_payment_eos` (`userID`, `werbecode`, `transactionID`, `reference`, `amount`, `currency`, `added`, `changed`)
				VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW());
			';
			Shopware()->Db()->query($sql, array(
				$user['additional']['user']['id'],
				$respone['werbecode'],
				$respone['kontaktid'],
				$params['referenz'],
				$params['bruttobetrag'],
				$params['waehrung'],
			));
			$sql = 'UPDATE s_order SET transactionID=? WHERE userID=? AND temporaryID=? AND status=-1';
			Shopware()->Db()->query($sql, array(
				$respone['kontaktid'],
				$user['additional']['user']['id'],
				Shopware()->SessionID()
			));
		}
		
		if(!empty($respone['URL'])) {
			$this->View()->loadTemplate('frontend/checkout/payment.tpl');
			$this->View()->sEmbedded = $respone['URL'];
		}
	}
	
	/**
	 * Returns payment short name
	 *
	 * @return string
	 */
	public function getPaymentShortName()
	{
		if(!empty(Shopware()->Session()->sOrderVariables->sPayment->name)) {
			return Shopware()->Session()->sOrderVariables->sPayment->name;
		} else {
			return null;
		}
	}
	
	/**
	 * Returns basket amount
	 *
	 * @return float
	 */
	public function getAmount()
	{
		$user = Shopware()->Session()->sOrderVariables->sUserData;
		$basket = Shopware()->Session()->sOrderVariables->sBasket;
		if (!empty($user['additional']['charge_vat'])){
			return empty($basket['AmountWithTaxNumeric']) ? $basket['AmountNumeric'] : $basket['AmountWithTaxNumeric'];
		} else {
			return $basket['AmountNetNumeric'];
		}
	}
	
	/**
	 * Returns user data
	 *
	 * @return unknown
	 */
	public function getUser()
	{
		if(!empty(Shopware()->Session()->sOrderVariables->sUserData)) {
			return Shopware()->Session()->sOrderVariables->sUserData;
		} else {
			return null;
		}
	}
	
	/**
	 * Notify action method
	 */
	public function notifyAction()
	{
		mail('hl@shopware.de', 'test', print_r($_REQUEST, true));
		
		$status = (int) $this->Request()->getParam('status');
		$transactionId = (int) $this->Request()->getParam('transactionId');
		
		if(!empty($status) && !empty($transactionId)) {
			$sql = 'UPDATE s_plugin_payment_eos SET status=?, `changed`=NOW() WHERE transactionID=?';
			Shopware()->Db()->query($sql, array($status, $transactionId));
			
			//$sClickPay->sSubmitOrder($status);
		}
	}
	
	/**
	 * End action method
	 */
	public function endAction()
	{
		for ($i=0; $i<20; $i++) {
			sleep(1);
			$sql = 'SELECT ordernumber FROM s_order WHERE status != -1 AND temporaryID=? AND userID=?';
			$orderNumber = Shopware()->Db()->fetchOne($sql, array($_REQUEST['sUniqueID'], $_REQUEST['sUserID']));
			if(!empty($orderNumber)) {
				break;
			}
		}
	}
	
	/**
	 * Do request method
	 *
	 * @param unknown_type $url
	 * @param unknown_type $params
	 * @return unknown
	 */
	public function doRequest($url, $params=array())
	{
		$client = new Zend_Http_Client($url);
		$client->setParameterGet($params);
		$respone = $client->request();
		$result = null;
		parse_str($respone->getBody(), $result);
		return $result;
	}
	
	/**
	 * Returns eos error message
	 *
	 * @param unknown_type $errorCode
	 * @param unknown_type $field
	 * @return unknown
	 */
	public function getEosErrorMessage($errorCode, $field)
	{
		$field = end(explode('_', $field));
		$fieldList = array(
			'blz' => 'Bankleitzahl',
			'plz' => 'Postleitzahl',
			'vname' => 'Vorname',
			'nname' => 'Nachname',
			'strasse' => 'Straße',
			'gebdatum' => 'Geburtstag',
		);
		if(isset($fieldList[$field])) {
			$field = $fieldList[$field];
		}
		$field = ucfirst($field);
		
		if($field=="Statuscode")
		{
			$errorList = array(
				1910 => 'Bank ist offline',
				1920 => 'Bankleitzahl wegen Fusion nicht mehr gültig',
				1930 => 'Bankkonto ist nicht für Giropay zugrelassen',
				1940 => 'Fehlerhafte Kontodaten',
				1900 => 'Wartungsmodus',
				2400 => 'Kontonummer nicht gültig für Online Banking',
				4900 => 'Transaktion nicht autorisiert',
				4500 => 'Status der Transaktion ist unbekannt'
			);
			if(isset($errorList[(int) $errorCode]))
				return $errorList[(int) $errorCode];
			else 
				return false;
		}
		
		$errorList = array(
			30001 => 'Feld "'.$field.'" wurde nicht übergeben',
			30002 => 'Feld "'.$field.'" ist leer',
			30011 => 'Eintrag im Feld "'.$field.'" in Negativliste gefunden (Wert nicht erlaubt)',
			30012 => 'Unzulässige Zeichenwiederholung im Feld "'.$field.'"',
			30013 => 'Inhalt vom Feld "'.$field.'" darf nicht mit einer Ziffer beginnen',
			30014 => 'Ziffern in der Eingabe vom im Feld "'.$field.'" nicht erlaubt',
			30072 => 'Keine gültige HändlerID',
			30073 => 'Händler hat keinen Kreditkartenaccount',
			30074 => 'Falscher URL Sicherheitscode des Händlers',
			30075 => 'Händler ist noch nicht freigeschaltet',
			30076 => 'Falsches Passwort des Händlers',
			30201 => $field.' ist ungültig',
			30202 => $field.' ist ungültig',
			30320 => 'Buchung bereits erfolgreich durchgeführt',
			30401 => 'Kein PDF Job gefunden',
			30402 => 'PDF noch nicht bearbeitet',
			30403 => 'PDF noch nicht auf Server',
			30411 => 'Ungültiger Werbecode',
		);
		if(isset($errorList[(int) $errorCode])) {
			return $errorList[(int) $errorCode];
		}
		
		$errorCodeSub = substr($errorCode,0,2);
		$errorValue = (int) substr($errorCode,2);

		$errorSubList = array(
			31 => 'Es müssen im Feld "'.$field.'" mindestens xxx Zeichen eingegeben werden',
			34 => 'Es dürfen im Feld "'.$field.'" höchstens xxx Zeichen eingegeben werden',
		);
		if (isset($errorSubList[(int) $errorCodeSub])) {
			return str_replace('xxx', $errorValue, $errorSubList[(int) $errorCodeSub]);
		}
	}
	
	/**
	 * Returns eos status message
	 *
	 * @param unknown_type $statusCode
	 * @return unknown
	 */
	public function getEosStatusMessage($statusCode)
	{
		$sClickPayStatus = array(
			0 => 'Offen',
			1 => 'Reserviert',
			2 => 'Gebucht',
			3 => 'Storniert',
			4 => 'Reservierung fehlgeschlagen',
			5 => 'Buchung abgebrochen',
			6 => 'Gutschrift',
			7 => 'Status unbekannt',
			8 => 'Geldeingang',
			9 => 'Geldauszahlung',
			10 => 'Buchung fehlgeschlagen',
			11 => '3-D Secure fehlgeschlagen',
			12 => 'User Accepted',
			13 => 'InitFolgezahlung',
			14 => 'Rückbuchung',
			15 => 'Warte auf Zahlungseingang'
		);
		if(isset($sClickPayStatus[$statusCode])) {
			return $sClickPayStatus[$statusCode];
		}
		return 'Status unbekannt';
	}
}