<?php
require_once(dirname(dirname(__FILE__)).'/payment.class.php');
final class sClickPay extends sPayment
{
	private $_path;
	private $_shopurl;
	/**
	 * Constructor of the payment class.
	 *
	 * @param $debug
	 * @param $path
	 * @param $startSession
	 */
	public function __construct($startSession=true)
	{
		$this->arguments = array(	'coreID'		=> 'sCoreID',
									'transactionID'	=> 'sTransactionID',
									'uniqueID'		=> 'sUniqueID',
									'comment'		=> 'sComment',
									'dispatchID'	=> 'dispatchID');
									
		$this->_path = dirname(dirname(__FILE__)).'/';
		if(!empty($_REQUEST[$this->arguments["coreID"]]))
			$_REQUEST["sCoreId"] = $_REQUEST[$this->arguments["coreID"]];
		if(!empty($_GET[$this->arguments["uniqueID"]]))
			$_REQUEST[$this->arguments["uniqueID"]] = $_GET[$this->arguments["uniqueID"]];
		parent::__construct("",$this->_path,$startSession);

		if(empty($this->sSYSTEM->sCONFIG["sHOSTORIGINAL"])&&$this->sSYSTEM->sCONFIG["sHOST"] != $_SERVER["HTTP_HOST"])
		{
			$this->sSYSTEM->sCONFIG["sHOSTORIGINAL"] = trim($this->sSYSTEM->sCONFIG["sHOST"]);
			$this->sSYSTEM->sCONFIG["sHOST"] = $_SERVER["HTTP_HOST"];
			$this->sSYSTEM->sCONFIG['sBASEPATH'] = str_replace($this->sSYSTEM->sCONFIG["sHOSTORIGINAL"],$this->sSYSTEM->sCONFIG["sHOST"],$this->sSYSTEM->sCONFIG['sBASEPATH']);
		}
	}
	
	public function sGetConfig($name)
	{
		if(isset($this->sSYSTEM->sCONFIG[$name]))
			return $this->sSYSTEM->sCONFIG[$name];
		else
			return false;
	}
	
	public function sGetSnippet($name)
	{
		if(isset($this->sSYSTEM->sCONFIG['sSnippets'][$name]))
			return $this->sSYSTEM->sCONFIG['sSnippets'][$name];
		else
			return false;
	}
	
	public function sGetSessionID()
	{
		
		if(!empty($this->sSYSTEM->sSESSION_ID))
			$result = $this->sSYSTEM->sSESSION_ID;
		elseif(!empty($this->arguments["coreID"])&&!empty($_REQUEST[$this->arguments["coreID"]]))
			$result = $_REQUEST[$this->arguments["coreID"]];
		elseif (!empty($_REQUEST["sCoreId"]))
			$result = $_REQUEST["sCoreId"];
		elseif (!empty($_REQUEST["SHOPWARESID"]))
			$result = $_REQUEST["SHOPWARESID"];
		else
			return false;
		return preg_match('/[^0-9a-zA-Z]/',$result) ? false : $result;
	}
	
	public function sGetTransactionID()
	{
		if(!empty($_REQUEST[$this->arguments["transactionID"]]))
			$result = $_REQUEST[$this->arguments["transactionID"]];
		else
			return false;
		return preg_match('/[^\w\d]/',$result) ? false : $result;
	}
	
	public function sGetLanguage()
	{
		if(!empty($this->sSYSTEM->sLanguage)&&!empty($this->sSYSTEM->sLanguageData[$this->sSYSTEM->sLanguage]["isocode"]))
		{
			$result = $this->sSYSTEM->sLanguageData[$this->sSYSTEM->sLanguage]["isocode"];
			$result = strtoupper($result);
			$result = preg_replace('/[^A-Z]/','',$result);
			$result = substr($result,0,2);
			if(in_array(array('EU','US'),$result))
				$result = 'EN';
			return strlen($result)==2 ? $result : "DE";
		}
		return "DE";
	}
	
	public function sGetShopURL()
	{
		if(!empty($this->_shopurl)) return $this->_shopurl;
		
		if($this->sGetConfig('sUSESSL'))
			$this->_shopurl = 'https://';
		else 
			$this->_shopurl = 'http://';
		$this->_shopurl .= $this->sGetConfig('sBASEPATH');
		return $this->_shopurl;
	}
	
	public function sInitModule()
	{
		$this->catchErrors();
		$this->initUser();
		$this->initPayment();
	}
	
	public function sDoRequest($url, $parms=array())
	{
		if(!empty($parms))
		{
			$query = http_build_query($parms, '', '&');
			if(strpos($url,'?')===false)
				$url .= '?';
			else
				$url .= '&';
			$url .= $query;
		}
		
		$ch = curl_init();
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_HEADER,0);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
		curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,0);
		curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,0);
		$response = curl_exec($ch);
		curl_close($ch);
		if(empty($response)) return false;
		parse_str($response, $result);
		
		return $result;
	}
	
	public function sSubmitOrder($paymentStatus=0)
	{
		$sql = 'SELECT id FROM s_order WHERE transactionID=? AND status != -1';
		$orderID = $this->sDB_CONNECTION->GetOne($sql,array($this->sGetTransactionID()));
		if(!empty($orderID))
		{
			$sql = "
				UPDATE s_order
				SET	cleared = ?
				WHERE transactionID = ?
				AND cleared != ?
			";	
			$this->sDB_CONNECTION->Execute($sql,array($paymentStatus,$this->sGetTransactionID(),$paymentStatus));
		}
		else
		{
			$this->sInitModule();
			parent::submitOrder('sale','../../core/class/viewports/','sViewportSale',$paymentStatus);
		}
	}
	
	public function sFormatPrice($price)
	{
		return number_format(str_replace(',','.',$price),2,',','');
	}
	
	public function sGetAmount()
	{
		return $this->getAmount();
	}
	
	public function sGetClickPayErrorMessage($errorCode,$field)
	{
		$field = end(explode('_',$field));
		$fieldList = array(
			'blz' => 'Bankleitzahl',
			'plz' => 'Postleitzahl',
			'vname' => 'Vorname',
			'nname' => 'Nachname',
			'strasse' => 'Straße',
			'gebdatum' => 'Geburtstag',
		);
		if(isset($fieldList[$field]))
			$field = $fieldList[$field];
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
			if(isset($errorList[(int)$errorCode]))
				return $errorList[(int)$errorCode];
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
		if(isset($errorList[(int)$errorCode]))
			return $errorList[(int)$errorCode];
		
		$errorCodeSub = substr($errorCode,0,2);
		$errorValue = (int) substr($errorCode,2);

		$errorSubList = array(
			31 => 'Es müssen im Feld "'.$field.'" mindestens xxx Zeichen eingegeben werden',
			34 => 'Es dürfen im Feld "'.$field.'" höchstens xxx Zeichen eingegeben werden',
		);
		if (isset($errorSubList[(int)$errorCodeSub]))
			return str_replace('xxx',$errorValue,$errorSubList[(int)$errorCodeSub]);
	}
	
	public function sGetClickPayStatusMessage($statusCode)
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
		if(isset($sClickPayStatus[$statusCode]))
			return $sClickPayStatus[$statusCode];
		return 'Status unbekannt';
	}
}
?>