<?php
if (!$path) $path = "../../";
include($path."payment.class.php");

class heidelpayPayment extends sPayment {
  /*{{{Variables*/
  var $version    = '1.1';
	
	// Benötigte Parameter
	var $neededArguments = array("param_sCoreId");
  var $arguments = array(
    "coreID"        => "param_sCoreId",
    "dispatchID"    => "param_dispatchID",
    "transactionID" => "transaction",
    "comment"       => "sComment",
    "uniqueID"      => "param_uniqueId",
  );
  // Zahlverfahren die immer eine PreAuth benötigen
  var $alwaysPA = array(
    'PP',
    'IV',
    'OTGP',
    'OTSUE',
    'OTID',
    'OTEPS',
    'VAMB',
  );

  var $response   = '';
  var $error      = '';

  var $availablePayments = array('CC','DD','DC','VA','OT','IV','PP','UA');
  var $pageURL = '';
  var $urlOK = '';
  var $urlCancel = '';
  var $urlFail = '';
  var $actualPaymethod = 'CC';
  var $mailDebug = false;
  var $debugEmail = 'webmaster@web-dezign.de';

  /*}}}*/
	
  function __construct($debug,$path,$startSession=true)/*{{{*/
  {
		parent::__construct($debug,$path,$startSession);

    // Set configuration
    $settings = array(
      'sHEIDELPAY_DEMO_URL',
      'sHEIDELPAY_LIVE_URL',
      'sHEIDELPAY_SECURITY_SENDER',
      'sHEIDELPAY_USER_LOGIN',
      'sHEIDELPAY_USER_PASSWORD',
      'sHEIDELPAY_TRANSACTION_CHANNEL',
      'sHEIDELPAY_TRANSACTION_MODE',
      'sHEIDELPAY_STATUS_SUCCESS',
      #'sHEIDELPAY_STATUS_CANCEL',
      #'sHEIDELPAY_STATUS_FAILED',
      'sHEIDELPAY_PAYMENT_TYPE_CC',
      'sHEIDELPAY_PAYMENT_TYPE_DD',
      'sHEIDELPAY_PAYMENT_TYPE_DC',
      'sHEIDELPAY_PAYMENT_TYPE_VAPP',
      'sHEIDELPAY_NOTIFY_CUSTOMER',
      'sHEIDELPAY_NOTIFY_ADMIN',
      'sHEIDELPAY_NOTIFY_EMAIL',
      'sHEIDELPAY_TRANSACTION_CHANNEL_PAYPAL',
      'sHEIDELPAY_TRANSACTION_CHANNEL_MONEYBOOKERS',
      'sHEIDELPAY_TRANSACTION_CHANNEL_GIROPAY',
      'sHEIDELPAY_TRANSACTION_CHANNEL_SOFORT',
      'sHEIDELPAY_TRANSACTION_CHANNEL_IDEAL',
      'sHEIDELPAY_TRANSACTION_CHANNEL_EPS',
      'sHEIDELPAY_STYLE',
    );
    foreach($settings AS $k => $v){
      $val = '';
      if (isset($this->config[$v])) $val = $this->config[$v];
      define($v, $val);
    }
    
    $this->pageURL    = 'http://'.$this->config["sBASEPATH"].'/engine/connectors/heidelpay/';
    $this->urlOK      = 'http://'.$this->config["sBASEPATH"].'/'.$this->sSYSTEM->sCONFIG["sBASEFILE"].'/sViewport,heidelpay_success/';
    $this->urlCancel  = 'http://'.$this->config["sBASEPATH"].'/'.$this->sSYSTEM->sCONFIG["sBASEFILE"].'/sViewport,heidelpay_cancel/';
    $this->urlFail    = 'http://'.$this->config["sBASEPATH"].'/'.$this->sSYSTEM->sCONFIG["sBASEFILE"].'/sViewport,heidelpay_fail/';

  }/*}}}*/
  
  function sViewportSale()/*{{{*/
  {
  	// Doing something after / while submitting order
  }/*}}}*/
  
  function initPayment()/*{{{*/
  {
  	parent::initPayment();
  }/*}}}*/

  function doPayment($orderId, $amount, $userData, $coreID, $uniqueId)/*{{{*/
  {
    // OK und NOK URL definieren
    $this->urlOK = 'http://'.$this->config["sBASEPATH"].'/'.$this->sSYSTEM->sCONFIG["sBASEFILE"].'/sViewport,heidelpay_success/?coreID='.$coreID;

    $currency = $this->config['sCURRENCY'];
    // Aktuelles Zahlverfahren ermitteln
    $this->actualPaymethod = strtoupper(str_replace(array('heidelpay_', '.tpl'), '', $userData['additional']['payment']['template']));
    $payCode = $this->actualPaymethod;
    $uData = array(
      'firstname'   => $userData["billingaddress"]["firstname"],
      'lastname'    => $userData["billingaddress"]["lastname"],
      'street'      => $userData["billingaddress"]["street"],
      'zip'         => $userData["billingaddress"]["zipcode"],
      'city'        => $userData["billingaddress"]["city"],
      'country'     => $userData["additional"]["country"]["countryiso"],
      'email'       => $userData["additional"]["user"]["email"],
    );
    $lang = strtoupper($userData["additional"]["user"]["language"]);

    // Debugging für Heidelpay Request
    $debug = false;
    $data = $this->prepareData($orderId, $amount, $currency, $payCode, $uData, $lang, $coreID, $uniqueId);
    if ($debug) echo '<pre>'.print_r($data, 1).'</pre>';
    if ($this->mailDebug) mail($this->debugEmail, 'prepareData', print_r($data,1));

    $res = $this->doRequest($data);

    if ($this->mailDebug) mail($this->debugEmail, 'doRequest', print_r($res,1));

    if ($debug) echo '<pre>resp('.print_r($this->response, 1).')</pre>';
    if ($debug) echo '<pre>'.print_r($res, 1).'</pre>';

    $res = $this->parseResult($res);

    if ($debug) echo '<pre>'.print_r($res, 1).'</pre>';

    $processingresult = $res['result'];
    $redirectURL      = $res['url'];
    $src = $this->urlCancel;
    if ($processingresult == "ACK" && strstr($redirectURL,"http")) {
      $src = $redirectURL;
    }
    
    // IFrame Code erzeugen
    $output = '';
   	$output.= '<iframe src="'.$src.'" frameborder="0" width="400" height="600"></iframe>';
    return $output;
  }/*}}}*/

  function setStatus($reference, $status)/*{{{*/
  {
    $order_status = $this->getOrderStatus($status);
    $sql = "
      UPDATE `s_order`
      SET `cleared` = ".$this->sDB_CONNECTION->qstr($order_status)."
      WHERE `transactionID` = ".$this->sDB_CONNECTION->qstr($reference)."
      ";
    $this->sDB_CONNECTION->Execute($sql);
  }/*}}}*/

  function saveUniqueID($orderId, $uniqueId)/*{{{*/
  {
    $sql = "
      UPDATE `s_order`
      SET `partnerID` = ".$this->sDB_CONNECTION->qstr($uniqueId)."
      WHERE `ordernumber` = ".$this->sDB_CONNECTION->qstr($orderId)."
      ";
    return $this->sDB_CONNECTION->Execute($sql);
  }/*}}}*/

  function saveShortID($orderId, $shortId)/*{{{*/
  {
    $comment = 'Heidelpay ShortId: '.$shortId;
    return $this->addComment($orderId, $comment);
  }/*}}}*/

  function addComment($orderId, $comment)/*{{{*/
  {
    $comment = "\n".$comment;
    $sql = "
      UPDATE `s_order`
      SET `comment` = CONCAT(`comment`, ".$this->sDB_CONNECTION->qstr($comment).") 
      WHERE `ordernumber` = ".$this->sDB_CONNECTION->qstr($orderId)."
      ";
    return $this->sDB_CONNECTION->Execute($sql);
  }/*}}}*/

  function sendNotifyMails($orderId, $order_status)/*{{{*/
  {
    if (!constant('sHEIDELPAY_NOTIFY_EMAIL')) return;
    if (sHEIDELPAY_NOTIFY_CUSTOMER == '1') $this->send_change_mail($orderId, $order_status, false);
    if (sHEIDELPAY_NOTIFY_ADMIN == '1')    $this->send_change_mail($orderId, $order_status, true);
    return true;
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
      $email = sHEIDELPAY_NOTIFY_EMAIL;
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

  function prepareData($orderId, $amount, $currency, $payCode, $userData, $lang, $coreID, $uniqueId)/*{{{*/
  {
    $subCode = substr(strtoupper($payCode),2);
    $payCode = substr(strtoupper($payCode),0,2);
    $amount = sprintf('%1.2f', $amount);
    $currency = strtoupper($currency);

    $channel = sHEIDELPAY_TRANSACTION_CHANNEL;
    $channelX = '';
    switch($subCode){
    case 'PP':
      $channelX = sHEIDELPAY_TRANSACTION_CHANNEL_PAYPAL;
      break;
    case 'MB':
      $channelX = sHEIDELPAY_TRANSACTION_CHANNEL_MONEYBOOKERS;
      break;
    case 'GP':
      $channelX = sHEIDELPAY_TRANSACTION_CHANNEL_GIROPAY;
      break;
    case 'SUE':
      $channelX = sHEIDELPAY_TRANSACTION_CHANNEL_SOFORT;
      break;
    case 'ID':
      $channelX = sHEIDELPAY_TRANSACTION_CHANNEL_IDEAL;
      break;
    case 'EPS':
      $channelX = sHEIDELPAY_TRANSACTION_CHANNEL_EPS;
      break;
    }
    // Wenn der Zusatzchannel gefüllt dann benutzen
    if (!empty($channelX)) $channel = $channelX;

    $parameters['SECURITY.SENDER']        = sHEIDELPAY_SECURITY_SENDER;
    $parameters['USER.LOGIN']             = sHEIDELPAY_USER_LOGIN;
    $parameters['USER.PWD']               = sHEIDELPAY_USER_PASSWORD;
    $parameters['TRANSACTION.CHANNEL']    = $channel;
    $parameters['TRANSACTION.MODE']       = sHEIDELPAY_TRANSACTION_MODE;
    $parameters['REQUEST.VERSION']        = "1.0";
    $parameters['IDENTIFICATION.TRANSACTIONID'] = $orderId;
    $parameters['FRONTEND.ENABLED']       = "true";
		$parameters['FRONTEND.REDIRECT_TIME'] = "0";
    $parameters['FRONTEND.POPUP']         = "false";
    $parameters['FRONTEND.MODE']          = "DEFAULT";
    $parameters['FRONTEND.LANGUAGE']      = $lang;
    $parameters['FRONTEND.LANGUAGE_SELECTOR'] = "true";
    $parameters['FRONTEND.ONEPAGE']       = "true";
    $parameters['FRONTEND.NEXTTARGET']    = "top.location.href";
    $parameters['FRONTEND.CSS_PATH']      = $this->pageURL."heidelpay_style.css";

    foreach($this->availablePayments as $key=>$value) {
      if ($value != $payCode) { 
        $parameters["FRONTEND.PM." . (string)($key + 1) . ".METHOD"] = $value;
        $parameters["FRONTEND.PM." . (string)($key + 1) . ".ENABLED"] = "false";
      }
    }

    // Payment Type ermitteln
    $paymentType = @constant('sHEIDELPAY_PAYMENT_TYPE_'.$payCode.$subCode);
    if (!$paymentType) $paymentType = 'DB';
    // Zahlverfahren die immer PreAuth benötigen auf PA setzen
    if (in_array($payCode.$subCode, $this->alwaysPA)) $paymentType = 'PA';

    // Parameter durchschleifen für SubShops und Co...
    $sKey = md5("deadbeef".$coreID.$orderId.$amount."F3BaAC6");
    $custom = $coreID."-".$orderId."-".$this->sSYSTEM->sLanguage."-".$this->sSYSTEM->sCurrency["id"]."-".$this->sSYSTEM->_SESSION["sSubShop"]["id"]."-".$this->sSYSTEM->_SESSION["sDispatch"]."-".$sKey."-".$amount."-".$payCode.".".$paymentType;

    // Nur zu Testzwecken
    #$this->pageURL = 'http://www.web-dezign.de/shopware/';

    $parameters['PAYMENT.CODE']           = $payCode.".".$paymentType;
    $parameters['FRONTEND.RESPONSE_URL']  = $this->pageURL."heidelpay_response.php?coreID=".$coreID.'&custom='.$custom;
    $parameters['NAME.GIVEN']             = $userData['firstname'];
    $parameters['NAME.FAMILY']            = $userData['lastname'];
    $parameters['ADDRESS.STREET']         = $userData['street'];
    $parameters['ADDRESS.ZIP']            = $userData['zip'];
    $parameters['ADDRESS.CITY']           = $userData['city'];
    $parameters['ADDRESS.COUNTRY']        = $userData['country'];
    $parameters['CONTACT.EMAIL']          = $userData['email'];
    $parameters['PRESENTATION.AMOUNT']    = $amount; // 99.00
    $parameters['PRESENTATION.CURRENCY']  = $currency; // EUR
    $parameters['ACCOUNT.COUNTRY']        = $userData['country'];
    return $parameters;
  }/*}}}*/

  function isUTF8($string)/*{{{*/
  {
	  if (is_array($string)) {
      $enc = implode('', $string);
	    return @!((ord($enc[0]) != 239) && (ord($enc[1]) != 187) && (ord($enc[2]) != 191));
	  } else {
	    return (utf8_encode(utf8_decode($string)) == $string);
	  }
  }/*}}}*/

  function doRequest($data)/*{{{*/
  {
    $url = sHEIDELPAY_DEMO_URL;
    if (sHEIDELPAY_TRANSACTION_MODE == 'LIVE'){
      $url = sHEIDELPAY_LIVE_URL;
    }

    // Erstellen des Strings für die Datenübermittlung
    foreach (array_keys($data) AS $key) {
      if ($this->isUTF8($data[$key])) $data[$key] = utf8_decode($data[$key]);
      $$key .= $data[$key];
      $$key = urlencode($$key);
      $$key .= "&";
      $var = strtoupper($key);
      $value = $$key;
      $result .= "$var=$value";
    }
    $strPOST = stripslashes($result);
    
    // prüfen ob CURL existiert
    if (function_exists('curl_init')) {
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_HEADER, 0);
      curl_setopt($ch, CURLOPT_FAILONERROR, 1);
      curl_setopt($ch, CURLOPT_TIMEOUT, 60);
      curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
      curl_setopt($ch, CURLOPT_POST, 1);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $strPOST);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,0);
      curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,0);
      curl_setopt($ch, CURLOPT_USERAGENT, "php ctpepost");

      $this->response     = curl_exec($ch);
      $this->error        = curl_error($ch);
      curl_close($ch);

      $res = $this->response;
      if (!$this->response && $this->error){
        $msg = urlencode('Curl Fehler');
        $res = 'status=FAIL&msg='.$this->error;
      }

    } else {
      $msg = urlencode('Curl Fehler');
      $res = 'status=FAIL&&msg='.$msg;
    }

    return $res;
  }/*}}}*/

  function parseResult($curlresultURL)/*{{{*/
  {
    $r_arr=explode("&",$curlresultURL);
    foreach($r_arr AS $buf) {
      $temp=urldecode($buf);
      $temp=split("=",$temp,2);
      $postatt=$temp[0];
      $postvar=$temp[1];
      $returnvalue[$postatt]=$postvar;
    }
    $processingresult = $returnvalue['POST.VALIDATION'];
    $redirectURL = $returnvalue['FRONTEND.REDIRECT_URL'];

    return array('result' => $processingresult, 'url' => $redirectURL);
  }/*}}}*/

}
?>