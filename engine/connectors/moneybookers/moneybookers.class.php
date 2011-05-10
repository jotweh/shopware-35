<?php
if (!$path) $path = "../../";
include($path."payment.class.php");

class
	moneybookersPayment extends sPayment {
  /*{{{Variables*/
  var $version    = '1.0';
	
	// Benötigte Parameter
	var $neededArguments = array("param_sCoreId");
  var $arguments = array(
    "coreID"        => "param_sCoreId",
    "dispatchID"    => "param_dispatchID",
    "transactionID" => "transaction",
    "comment"       => "sComment",
    "uniqueID"      => "param_uniqueId",
  );

  /*}}}*/
	
  function __construct($debug,$path,$startSession=true)/*{{{*/
  {
		parent::__construct($debug,$path,$startSession);
		
    // Set configuration
    $settings = array(
      'sMONEYBOOKERS_MERCHANTID',
      'sMONEYBOOKERS_SECRET',
      'sMONEYBOOKERS_EMAIL',
      'sMONEYBOOKERS_STATUS_ID',
      'sMONEYBOOKERS_IFRAME_URL',
      'sMONEYBOOKERS_STYLE',
      'sMONEYBOOKERS_SWITCH_HIDE_LOGIN',
      'sMONEYBOOKERS_CONFIRMATION_NOTE',
      'sMONEYBOOKERS_STATUS_PROCESSED',
      'sMONEYBOOKERS_STATUS_PENDING',
      'sMONEYBOOKERS_STATUS_CANCELLED',
      'sMONEYBOOKERS_STATUS_FAILED',
      'sMONEYBOOKERS_STATUS_CHARGEBACK',
    );
    foreach($settings AS $k => $v){
      define($v, $this->config[$v]);
    }

  }/*}}}*/
  
  function sViewportSale()/*{{{*/
  {
  	// Doing something after / while submitting order
  }/*}}}*/
  
  function initPayment()/*{{{*/
  {
  	parent::initPayment();
  }/*}}}*/

  function doPayment($orderId, $amount, $userData, $coreID, $uniqueID)/*{{{*/
  {
    // Betrag formatieren
    $amount = sprintf('%1.2f', $amount);

    // OK und NOK URL definieren
    #$urlOK = 'http://'.$this->config["sBASEPATH"].'/'.$this->sSYSTEM->sCONFIG["sBASEFILE"].'/sViewport,moneybookers_success/status,OK/coreID,'.$coreID;
    //$urlOK = 'http://'.$this->config["sBASEPATH"].'/'.$this->sSYSTEM->sCONFIG["sBASEFILE"].'/sViewport,moneybookers_success/?coreID='.$coreID.'&sUniqueID='.$orderId;
     #$urlOK = 'http://'.$this->config["sBASEPATH"].'/'.$this->config["sBASEFILE"].'/sViewport,sale/sAction,doSale/sRefererAllowed,1/sCoreId,'.$coreID.'/sUniqueID,'.$orderId.'/';			
    //$urlCancel = 'http://'.$this->config["sBASEPATH"].'/'.$this->sSYSTEM->sCONFIG["sBASEFILE"].'/sViewport,moneybookers_fail/status,CANCEL';

    if (empty($this->config["sUSESSL"])){
    	$url= "http://".$this->config["sBASEPATH"]."/";
    }else {
    	$url= "https://".$this->config["sBASEPATH"]."/";
    }

    $urlOK = $url.'engine/connectors/moneybookers/success.php?coreID='.$coreID.'&sUniqueID='.$orderId;
    $urlCancel = $url.$this->config["sBASEFILE"].'/sViewport,sale/sRefererAllowed,1/sCoreId,'.$coreID.'/';
    $urlStatus = $url.'engine/connectors/moneybookers/notify.php?coreID='.$coreID;

    // Parameter durchschleifen für SubShops und Co...
    $sKey = md5("deadbeef".$coreID.$orderId.$amount."F3e5b9C6");
    $custom = $coreID."-".$orderId."-".$this->sSYSTEM->sLanguage."-".$this->sSYSTEM->sCurrency["id"]."-".$this->sSYSTEM->_SESSION["sSubShop"]["id"]."-".$this->sSYSTEM->_SESSION["sDispatch"]."-".$sKey."-".$amount;
    $urlOK.= '&custom='.$custom;

    $paytype = 'WLT';
    $actualPaymethod = strtoupper(str_replace('moneybookers_', '', $userData['additional']['payment']['name']));
    if (!empty($actualPaymethod)) $paytype = $actualPaymethod;

    // Kundendaten sammeln
    $moneybookers_data = array(
      'reference' 	    => $orderId,
      'coreid' 	        => $coreID,
      'uniqueid' 	      => $uniqueID,
      'amount' 			    => $amount,
      'title' 			    => $userData["billingaddress"]["salutation"] == "mr" ? "1" : "2", // 0 = unkown 1 = Mr 2 = Mrs
      'surname' 		    => $userData["billingaddress"]["firstname"],
      'name' 				    => $userData["billingaddress"]["lastname"],
      'birthname' 	    => $userData["billingaddress"]["lastname"],
      'email' 			    => $userData["additional"]["user"]["email"],
      'telephone' 	    => $userData["billingaddress"]["phone"],
      'birthdate' 	    => $userData["billingaddress"]["birthday"],
      'street' 			    => $userData["billingaddress"]["street"],
      'housenumber'     => $userData["billingaddress"]["streetnumber"],
      'postal' 			    => $userData["billingaddress"]["zipcode"],
      'city' 				    => $userData["billingaddress"]["city"],
      'country' 		    => $userData["additional"]["country"]["iso3"],
      'return_url'      => $urlOK,
      'return_text'     => $this->config['sSnippets']['sMoneybookersForward'],
      'cancel_url'      => $urlCancel,
      'status_url'      => $urlStatus,
      'hide_login'      => sMONEYBOOKERS_SWITCH_HIDE_LOGIN,
      'email_merchant'  => sMONEYBOOKERS_EMAIL,
      'language'        => strtoupper($userData["additional"]["user"]["language"]),
      'currency'        => $this->config['sCURRENCY'],
      'confirmation_note' => sMONEYBOOKERS_CONFIRMATION_NOTE,
      'custom'          => $custom,
      'payment_type'    => $paytype,
    );
    // Wenn Geburtstag leer dann auf 0 setzen
    if (empty($userData["billingaddress"]["birthday"])) {
      $moneybookers_data['birthdate'] = '00000000';
    } else {
      $dob_year   = substr($userData["billingaddress"]["birthday"], 0, 4);
      $dob_month  = substr($userData["billingaddress"]["birthday"], 5, 2);
      $dob_day    = substr($userData["billingaddress"]["birthday"], 8, 2);
      $moneybookers_data["birthdate"] = $dob_month.$dob_day.$dob_year;
    }
    // Leerzeichen entfernen und Umlaute umwandeln
    foreach($moneybookers_data AS $k => $v){
      #$moneybookers_data[$k] = $this->myEncode($v, true, false);
    }

    // XML erzeugen
		$xml = $this->getXML($moneybookers_data, true);

    // URL ermitteln
   	$moneybookers_url = sMONEYBOOKERS_IFRAME_URL;
    
    // Nur zu Testzwecken
    #$moneybookers_url = 'http://www.shopware.vm/showRequest.php?';
    #mail('webmaster@web-dezign.de', 'Shopware Moneybookers Debug Mail', print_r($xml, 1));

    // Prüfsumme berechnen
    #$chkSum = $this->getCheckSum($xml);
    #$moneybookers_data["CHECKSUM"] = $chkSum;
    $params = ''; #'sha1='.$chkSum.'&partnerID='.sMONEYBOOKERS_MERCHANTID;

    // IFrame Code erzeugen
    $output = '<html><head></head><body onload="document.getElementById(\'moneybookers_form\').submit();">';
   	//$output.= '<iframe name="moneybookers_frame" id="moneybookers_frame" frameborder="0" width="100%" height="1000" src="about:blank"></iframe>';
   	//target="moneybookers_frame"
   	$output.= '<form style="height:460px" id="moneybookers_form" name="moneybookers_form" method="post" action="'.$moneybookers_url.'?'.$params.'" enctype="multipart/form-data" acceptcharset="utf-8">';
    $output.= ''.$xml.'';
    $output.= '</form>';
    //$output.= '<script>document.getElementById(\'moneybookers_form\').submit();</script>';
    $output.= '</body></html>';
    return $output;
  }/*}}}*/

  function escapeRegExp($string)/*{{{*/
  {
    $repl = array(
      '-' => '\-',
      '+' => '\+',
      '*' => '\*',
      '\\' => '\\\\',
      '/' => '\/',
      '.' => '\.',
      '[' => '\[',
      ']' => '\]',
      '(' => '\(',
      ')' => '\)',
      '{' => '\{',
      '}' => '\}',
      '?' => '\?',
    );
    $string = strtr($string, $repl);
    return $string;
  }/*}}}*/

	function myEncode($value, $autoTrim = true, $autoEncode = true)/*{{{*/
  {
    # ä:ae,ö:oe,ü:ue,Ä:Ae,Ö:Oe,Ü:Ue,ß:ss,á:a,à:a,â:a,é:e,è:e,ê:e,í:i,ì:i,î:i,ó:o,ò:o,ô:o,ú:u,ù:u,û:u,ý:y,Á:A,À:A,Â:A,É:E,È:E,Ê:E,Í:I,Ì:I,Î:I,Ó:O,Ò:O,Ô:O,Ú:U,Ù:U,Û:U,Ý:Y,Ï:I,ï:i
    $tmp = array();
    $repl = explode(',', sMONEYBOOKERS_ENCODE_MASK);
    foreach($repl AS $k => $v){
      $parts = explode(':', $v);
      $tmp[$parts[0]] = $parts[1];
    }
    $repl = $tmp;

    $value = strtr($value, $repl);
    if ($autoTrim) {
      $value = trim($value);
    }
    // #[^A-Za-z0-9\-\+\\\/\._,:;\*\?&=\[\]\(\)\{\}]*#
    $reg = $this->escapeRegExp(sMONEYBOOKERS_CHAR_FILTER);
    $value = preg_replace('#[^A-Za-z0-9\\@'.$reg.']*#', '', $value);
	  if ($autoEncode && mb_detect_encoding($value) != 'UTF-8'){
	  	$value = utf8_encode($value);
    }
	  return $value;
	}/*}}}*/

  function getXML($data, $forceUTF8 = false)/*{{{*/
  {
    $replacement = array();
    if (!is_array($data)) $data = array();
    foreach($data AS $k => $v){
      $replacement['{VAR_'.strtoupper($k).'}'] = $v;
    }
    $xml = '
<input type="hidden" name="pay_to_email" value="{VAR_EMAIL_MERCHANT}">
<input type="hidden" name="transaction_id" value="{VAR_REFERENCE}">
<input type="hidden" name="return_url" value="{VAR_RETURN_URL}">
<input type="hidden" name="return_url_text" value="{VAR_RETURN_TEXT}">
<input type="hidden" name="return_url_target" value="1">
<input type="hidden" name="cancel_url" value="{VAR_CANCEL_URL}">
<input type="hidden" name="cancel_url_target" value="1">
<input type="hidden" name="status_url" value="{VAR_STATUS_URL}">
<input type="hidden" name="hide_login" value="{VAR_HIDE_LOGIN}">
<input type="hidden" name="language" value="{VAR_LANGUAGE}">
<input type="hidden" name="amount" value="{VAR_AMOUNT}">
<input type="hidden" name="currency" value="{VAR_CURRENCY}">
<input type="hidden" name="confirmation_note" value="{VAR_CONFIRMATION_NOTE}">

<input type="hidden" name="firstname" value="{VAR_SURNAME}">
<input type="hidden" name="lastname" value="{VAR_NAME}">
<input type="hidden" name="title" value="{VAR_TITLE}">
<input type="hidden" name="date_of_birth" value="{VAR_BIRTHDATE}">
<input type="hidden" name="address" value="{VAR_STREET} {VAR_HOUSENUMBER}">
<input type="hidden" name="postal_code" value="{VAR_POSTAL}">
<input type="hidden" name="city" value="{VAR_CITY}">
<input type="hidden" name="country" value="{VAR_COUNTRY}">
<input type="hidden" name="payment_type" value="{VAR_PAYMENT_TYPE}">
<input type="hidden" name="payment_methods" value="{VAR_PAYMENT_TYPE}">
<input type="hidden" name="merchant_fields" value="param_sCoreId, param_custom, param_uniqueId, platform">
<input type="hidden" name="platform" value="21477261">
<input type="hidden" name="param_sCoreId" value="{VAR_COREID}">
<input type="hidden" name="param_uniqueId" value="{VAR_UNIQUEID}">
<input type="hidden" name="param_custom" value="{VAR_CUSTOM}">
';
    $xml = strtr($xml, $replacement);
    $xml = str_replace(array("\r", "\n"), '', $xml);
    if ($forceUTF8){
      $xml = utf8_encode($xml);
    }
    return $xml;
  }/*}}}*/

  function getCheckSum($merchantId, $transactionId, $mbAmount, $mbCurrency, $status) /*{{{*/
  {
    return strtoupper(md5($merchantId.$transactionId.strtoupper(md5(sMONEYBOOKERS_SECRET)).$mbAmount.$mbCurrency.$status));
  }/*}}}*/

  function getSecureSum($merchantId, $transactionId) /*{{{*/
  {
    return strtoupper(md5($merchantId.$transactionId.strtoupper(md5(sMONEYBOOKERS_SECRET))));
  }/*}}}*/

  function decodeXML($xmlstg)/*{{{*/
  {
    preg_match_all("(<([a-z0-9\-]+)>([^<>]*?)</[a-z0-9\-]+>)i", $xmlstg, $out, PREG_SET_ORDER);
    $n = 0;
    $k = 0;
    while (isset($out[$n])){
      if ($out[$n][1] == 'reference') $k++;
      $retarr[$k][$out[$n][1]] = strip_tags($out[$n][2]);
      $n++;
    }
    return $retarr;
  }/*}}}*/

  function setStatus($reference, $status)/*{{{*/
  {
    $order_status = $this->getOrderStatus($status);
    $sql = "
      UPDATE `s_order`
      SET `cleared` = ".$this->sDB_CONNECTION->qstr($order_status)."
      WHERE `transactionID` = ".$this->sDB_CONNECTION->qstr($reference)."
      ";
    #echo $sql;
    #mail('webmaster@web-dezign.de', 'Shopware Moneybookers SQL Mail', $sql);
    #mail('webmaster@web-dezign.de', 'Shopware Moneybookers DBConn Mail', print_r($this->sDB_CONNECTION,1));
    $this->sDB_CONNECTION->Execute($sql);
  }/*}}}*/

  function getCoreIdByTransactionId($transId)/*{{{*/
  {
    $sql = "SELECT * FROM `s_order` WHERE `transactionID` = ".$this->sDB_CONNECTION->qstr($transId)." ";
    $data = $this->sDB_CONNECTION->GetRow($sql);
    return $data['temporaryID'];
  }/*}}}*/

  function getOrderIdByTransactionId($transId)/*{{{*/
  {
    $sql = "SELECT * FROM `s_order` WHERE `transactionID` = ".$this->sDB_CONNECTION->qstr($transId)." ";
    $data = $this->sDB_CONNECTION->GetRow($sql);
    return $data['ordernumber'];
  }/*}}}*/

  function getOrderStatus($status)/*{{{*/
  {
    $stat = '';
    switch($status){
      case '2': // Processed
        $stat = sMONEYBOOKERS_STATUS_PROCESSED;
        break;
      case '0': // Pending
        $stat = sMONEYBOOKERS_STATUS_PENDING;
        break;
      case '-1': // Cancelled
        $stat = sMONEYBOOKERS_STATUS_CANCELLED;
        break;
      case '-2': // Failed
        $stat = sMONEYBOOKERS_STATUS_FAILED;
        break;
      case '-3': // Chargeback
        $stat = sMONEYBOOKERS_STATUS_CHARGEBACK;
        break;
    }
    return $stat;
  }/*}}}*/

  function send_change_mail($orderId, $status, $toAdmin = false)/*{{{*/
  {
    // Send eMail
    $mail           = new PHPMailer;
    $selectedTemplate = 'sORDERSTATEMAIL'.$status;
    if (empty($this->sSYSTEM->sCONFIG['sTemplates'][$selectedTemplate]['frommail'])) return false;
    if (!$toAdmin){
      $email = $this->getCustomerEmailByOrdernumber($orderId);
    } else {
      $email = sMONEYBOOKERS_NOTIFY_EMAIL;
    }

    $mail->From     = $this->sSYSTEM->sCONFIG['sTemplates'][$selectedTemplate]['frommail'];
    $mail->FromName = $this->sSYSTEM->sCONFIG['sTemplates'][$selectedTemplate]['fromname'];
    $mail->Subject  = $this->sSYSTEM->sCONFIG['sTemplates'][$selectedTemplate]['subject'];

    if ($this->sSYSTEM->sCONFIG['sTemplates'][$selectedTemplate]['ishtml']){
      $mail->IsHTML(1);
      $mail->Body     = $this->sSYSTEM->sCONFIG['sTemplates'][$selectedTemplate]['contentHTML'];
      $mail->AltBody     = $this->sSYSTEM->sCONFIG['sTemplates'][$selectedTemplate]['content'];
    }else {
      $mail->IsHTML(0);
      $mail->Body     = $this->sSYSTEM->sCONFIG['sTemplates'][$selectedTemplate]['content'];
    }

    $mail->ClearAddresses();
    $mail->AddAddress($email, "");
    return $mail->Send();
  }/*}}}*/

  function getCustomerEmailByOrdernumber($orderId)/*{{{*/
  {
    $sql = "
      SELECT `userID` FROM `s_order`
      WHERE `ordernumber` = ".$this->sDB_CONNECTION->qstr($orderId)."
      ";
    #echo $sql;
    $res = $this->sDB_CONNECTION->GetRow($sql);
    $userId = $res['userID'];
    $sql = "
      SELECT `email` FROM `s_user`
      WHERE `id` = ".$this->sDB_CONNECTION->qstr($userId)."
      ";
    #echo $sql;
    $res = $this->sDB_CONNECTION->GetRow($sql);
    return $res['email'];
  }/*}}}*/

  function doCurlRequest($url, $send)/*{{{*/
  {
    // Wenn kein Curl vorhanden dann gleich abbrechen
    if (!$this->isCURL()) {
      $msg = urlencode('Curl Fehler');
      $this->response = 'status=FAIL&code_1=300&msg_1='.$msg;
      return $this->response;
    }

    // Erstellen des Strings für die Datenübermittlung
    $dataString="";
    foreach ($send as $k=>$v){
      $dataString.= $k.'='.urlencode($v).'&';
    }
    $dataString=substr($dataString,0,-1);

    // Proxydaten prüfen
    $prox = $this->proxyhost.$this->proxyport.$this->proxyuser.$this->proxypass;
    $needProxy = false;
    if (!empty($prox)) $needProxy = true;

    // Wenn Safe Mode aus dann Time Limit setzen
    if (strlen(ini_get("safe_mode"))< 1) set_time_limit(60);
    // prüfen ob CURL existiert
    if (function_exists('curl_init')) {
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_HEADER, 0);
      curl_setopt($ch, CURLOPT_FAILONERROR, 1);
      curl_setopt($ch, CURLOPT_TIMEOUT, 60);
      curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
      curl_setopt($ch, CURLOPT_POST, 1);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      #curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,0);
      curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,0);
      curl_setopt($ch, CURLOPT_USERAGENT, "Moneybookers Request");
      #curl_setopt($ch, CURLOPT_USERPWD,'preview:kredit2008');

      if ($needProxy){
        curl_setopt($ch, CURLOPT_PROXY, $this->proxyhost.':'.$this->proxyport);
        curl_setopt($ch, CURLOPT_PROXYUSERPWD, $this->proxyuser.':'.$this->proxypass);
      }

      $this->response     = curl_exec($ch);
      $this->error        = curl_error($ch);
      curl_close($ch);

      if (!$this->response && $this->error){
        $msg = urlencode('Curl Fehler');
        $this->response = 'status=FAIL&code_1=300&msg_1='.$msg.'&param_1='.$this->error;
      }
      $res = $this->response;

    } else {
      $urlArray = parse_url($url);
      $scheme = $urlArray['scheme'];
      $host = $urlArray['host'];
      $path = $urlArray['path'];
      if ($scheme == 'https'){
        $isHTTPS = true;
        $port = 443;
      } else {
        $isHTTPS = false;
        $port = 80;
      }
      $timeout = 60;

      if ($needProxy){
        $fp = fsockopen($this->proxyhost, $this->proxyport, $errno, $errstr, $timeout);
      } else {
        if ($isHTTPS){
          $fp = pfsockopen('ssl://'.$host, $port, $errno, $errstr, $timeout);
        } else {
          $fp = fsockopen($host, $port, $errno, $errstr, $timeout);
        }
      }
      if ($fp){
        if ($needProxy){
          fputs($fp, "POST $url HTTP/1.0\r\n");
          fputs($fp, "Host: $this->proxyhost\r\n");
        } else {
          fputs($fp, "POST $path HTTP/1.1\r\n");
          fputs($fp, "Host: $host\r\n");
        }
        fputs($fp, "Referer: $url\r\n");
        fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n");
        fputs($fp, "Content-length: ". strlen($dataString) ."\r\n");
        if ($needProxy){
          fputs($fp, "Proxy-Authorization: Basic " . base64_encode ("$this->proxyuser:$this->proxypass") . "\r\n\r\n");
        }
        if ($needProxy) fputs($fp, $dataString);
        fputs($fp, "Connection: close\r\n\r\n");
        if (!$needProxy) fputs($fp, $dataString);
        while(!feof($fp)) {
          $this->response .= fgets($fp, 128);
        }
        fclose($fp);

        $res2 = explode("\n",$this->response);
        foreach($res2 AS $k => $v){
          if (substr($v,0,7) == 'status='){
            $res = $v;
          }
        }
      } else {
        $this->error = $errstr.' ('.$errno.')';
        if (!$res && $this->error){
          $msg = urlencode('FSockOpen Fehler');
          $res = 'status=FAIL&code_1=301&msg_1='.$msg.'&param_1='.$this->error;
        }
      }
      $this->response = $res;
    }

    return $res;
  }/*}}}*/

  function isCURL()/*{{{*/
  {
    if (!function_exists('curl_init')
      && (ini_get('allow_url_fopen') == ''
        || ini_get('allow_url_fopen') == 0
        || strtolower(ini_get('allow_url_fopen')) == 'off')
          ) {
            return false;
          }
    return true;
  }/*}}}*/
}
?>
