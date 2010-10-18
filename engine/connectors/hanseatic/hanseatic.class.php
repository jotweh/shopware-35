<?php
if (!$path) $path = "../../";
include($path."payment.class.php");

// Randangaben für Finanzierungen
define('sHANSEATIC_MIN_CREDIT_VALUE', '15000');
define('sHANSEATIC_MAX_CREDIT_VALUE', '4000000');
define('sHANSEATIC_MIN_CREDIT_RATE', '1500');
define('sHANSEATIC_POSSIBLE_RATE_COUNT', '3,6,9,12,15,18,21,24,30,36,48,60,72,84');
define('sHANSEATIC_FORWARD', 'Weiter');

class hanseaticPayment extends sPayment {
  /*{{{Variables*/
  var $version    = '1.0';
	
	// Benötigte Parameter
	var $neededArguments = array("param_sCoreId");
  var $arguments = array(
    "coreID"        => "param_sCoreId",
    "dispatchID"    => "param_dispatchID",
    "transactionID" => "transaction",
    "comment"       => "sComment"
  );

  var $possibleRates = array();
  /*}}}*/
	
  function __construct($debug,$path,$startSession=true)/*{{{*/
  {
		parent::__construct($debug,$path,$startSession);
		
    // Set configuration
    $settings = array(
      'sHANSEATIC_PARTNERID',
      'sHANSEATIC_PRESHAREDKEY',
      'sHANSEATIC_NOTIFY_EMAIL',
      'sHANSEATIC_STATUS_ID',
      'sHANSEATIC_STATUS_0_ID',
      'sHANSEATIC_STATUS_1_ID',
      'sHANSEATIC_STATUS_2_ID',
      'sHANSEATIC_STATUS_3_ID',
      'sHANSEATIC_STATUS_4_ID',
      'sHANSEATIC_STATUS_5_ID',
      'sHANSEATIC_NOTIFY_CUSTOMER',
      'sHANSEATIC_NOTIFY_ADMIN',
      'sHANSEATIC_ORDER_TYPE',
      'sHANSEATIC_FORCE_UTF8',
      'sHANSEATIC_RATE_CALC_WIDTH',
      'sHANSEATIC_RATE_CALC_HEIGHT',
      'sHANSEATIC_CHAR_FILTER',
      'sHANSEATIC_ENCODE_MASK',
      'sHANSEATIC_IFRAME_URL',
      'sHANSEATIC_MICROSITE_URL',
      'sHANSEATIC_DELIVERY_URL',
      'sHANSEATIC_POLL_URL',
      'sHANSEATIC_CALCULATOR_URL',
      'sHANSEATIC_URL_TYPE',
      'sHANSEATIC_COMMENT',
      'sHANSEATIC_PERCENT',
      'sHANSEATIC_STYLE'
    );
    foreach($settings AS $k => $v){
      define($v, $this->config[$v]);
    }

    $this->possibleRates = explode(',', sHANSEATIC_POSSIBLE_RATE_COUNT);
  }/*}}}*/
  
  function sViewportSale()/*{{{*/
  {
  	// Doing something after / while submitting order
  }/*}}}*/
  
  function initPayment()/*{{{*/
  {
  	parent::initPayment();
  }/*}}}*/

  function doPayment($orderId, $amount, $userData, $coreId)/*{{{*/
  {
    // Betrag formatieren
    $amount = sprintf('%1.2f', $amount);

    // OK und NOK URL definieren
    $urlOK = 'http://'.$this->config["sBASEPATH"].'/'.$this->sSYSTEM->sCONFIG["sBASEFILE"].'/sViewport,hanseatic_success/status,OK/level,';
    $urlNOK = 'http://'.$this->config["sBASEPATH"].'/'.$this->sSYSTEM->sCONFIG["sBASEFILE"].'/sViewport,hanseatic_fail/status,OK/level,';

    // Kundendaten sammeln
    $hanseatic_data = array(
      'creditsum' 	    => $amount,
      'reference' 	    => $orderId,
      'model' 			    => '',
      'brand' 			    => '',
      'type' 				    => sHANSEATIC_ORDER_TYPE,
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
      'country' 		    => $userData["additional"]["country"]["countryiso"],
      'green_link'      => $urlOK.'green'.'/coreId,'.$coreId.'/orderId,'.$orderId,
      'green_text'      => sHANSEATIC_FORWARD,
      'green_target'    => '_top',
      'yellow_link'     => $urlOK.'yellow'.'/coreId,'.$coreId.'/orderId,'.$orderId,
      'yellow_text'     => sHANSEATIC_FORWARD,
      'yellow_target'   => '_top',
      'red_link'        => $urlNOK.'red',
      'red_text'        => sHANSEATIC_FORWARD,
      'red_target'      => '_top',
    );
    // Wenn Geburtstag leer dann auf 0 setzen
    if (empty($userData["billingaddress"]["birthday"])) $hanseatic_data['birthdate'] = '0000-00-00';
    if (isset($_POST['rates'])){
      $hanseatic_data['duration'] = $_POST['rates'];
    }
    // Leerzeichen entfernen und Umlaute umwandeln
    foreach($hanseatic_data AS $k => $v){
      $hanseatic_data[$k] = $this->myEncode($v, true, false);
    }

    // UFT8 Kodierung ermitteln
    $forceUTF8 = sHANSEATIC_FORCE_UTF8 == '1';

    // XML erzeugen
	$xml = $this->getXML($hanseatic_data, $forceUTF8);

    // URL ermitteln
    $useIFrame = false;
    if (sHANSEATIC_URL_TYPE == '1'){
    	$hanseatic_url = sHANSEATIC_IFRAME_URL;
    	$useIFrame = true;
    } else {
    	$hanseatic_url = sHANSEATIC_MICROSITE_URL;
    }
    // Nur zu Testzwecken
    #$hanseatic_url = 'http://www.shopware.vm/showRequest.php?';

    // Prüfsumme berechnen
    $chkSum = $this->getCheckSum($xml);
    $params = 'sha1='.$chkSum.'&partnerID='.sHANSEATIC_PARTNERID;

    // IFrame Code erzeugen
    $output = '<html><head></head><body>';
    if ($useIFrame){
    	$output.= '<iframe name="hanseatic_frame" id="hanseatic_frame" frameborder="0" width="100%" height="1000" src="about:blank"></iframe>';
    	$output.= '<form id="hanseatic_form" name="hanseatic_form" method="post" action="'.$hanseatic_url.'&'.$params.'" target="hanseatic_frame" enctype="multipart/form-data" acceptcharset="utf-8">';
    } else {
    	$output.= '<form id="hanseatic_form" name="hanseatic_form" method="post" action="'.$hanseatic_url.'&'.$params.'" target="_blank">';
    }
    $output.= '<textarea name="xml" style="display: none; width: 100%; height: 600px">'.$xml.'</textarea>';
    $output.= '</form>';
    $output.= '<script>document.getElementById(\'hanseatic_form\').submit();</script>';
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
    $repl = explode(',', sHANSEATIC_ENCODE_MASK);
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
    $reg = $this->escapeRegExp(sHANSEATIC_CHAR_FILTER);
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
    $xml = '<?xml version="1.0" encoding="UTF-8"?>
<request xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="hb-spec.xsd">
<backlink>
<gruen>
<link><![CDATA[{VAR_GREEN_LINK}]]></link>
<text><![CDATA[{VAR_GREEN_TEXT}]]></text>
<target><![CDATA[{VAR_GREEN_TARGET}]]></target>
</gruen>
<gelb>
<link><![CDATA[{VAR_YELLOW_LINK}]]></link>
<text><![CDATA[{VAR_YELLOW_TEXT}]]></text>
<target><![CDATA[{VAR_YELLOW_TARGET}]]></target>
</gelb>
<rot>
<link><![CDATA[{VAR_RED_LINK}]]></link>
<text><![CDATA[{VAR_RED_TEXT}]]></text>
<target><![CDATA[{VAR_RED_TARGET}]]></target>
</rot>
</backlink>
<financeplan>
<amount><![CDATA[{VAR_CREDITSUM}]]></amount>{VAR_DURATION_ELEMENT}
</financeplan>
<order>
<reference><![CDATA[{VAR_REFERENCE}]]></reference>
<model><![CDATA[{VAR_MODEL}]]></model>
<brand><![CDATA[{VAR_BRAND}]]></brand>
<type><![CDATA[{VAR_TYPE}]]></type>
<amount><![CDATA[{VAR_AMOUNT}]]></amount>
</order>
<customer>
<title><![CDATA[{VAR_TITLE}]]></title>
<surname><![CDATA[{VAR_SURNAME}]]></surname>
<name><![CDATA[{VAR_NAME}]]></name>
<birthname><![CDATA[{VAR_BIRTHNAME}]]></birthname>
<email><![CDATA[{VAR_EMAIL}]]></email>
<telephone><![CDATA[{VAR_TELEPHONE}]]></telephone>
<birthdate><![CDATA[{VAR_BIRTHDATE}]]></birthdate>
<address>
<street><![CDATA[{VAR_STREET}]]></street>
<housenumber><![CDATA[{VAR_HOUSENUMBER}]]></housenumber>
<postal><![CDATA[{VAR_POSTAL}]]></postal>
<city><![CDATA[{VAR_CITY}]]></city>
<country><![CDATA[{VAR_COUNTRY}]]></country>
</address>
</customer>
</request>';
    if (!empty($data['duration'])){
      $xml = str_replace('{VAR_DURATION_ELEMENT}', '<duration><![CDATA[{VAR_DURATION}]]></duration>', $xml);
    } else {
      $xml = str_replace('{VAR_DURATION_ELEMENT}', '', $xml);
    }
    $xml = strtr($xml, $replacement);
    $xml = str_replace(array("\r", "\n"), '', $xml);
    if ($forceUTF8){
      $xml = utf8_encode($xml);
    }
    return $xml;
  }/*}}}*/

  function getCheckSum($xml) /*{{{*/
  {
  	
  
    return sha1($xml . sHANSEATIC_PRESHAREDKEY);
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

	function markAsDelivered($reference)/*{{{*/
	{
		$url = $this->getDeliverURL($reference);
		$res = $this->doCurlRequest($url, '');
		$retArray = $this->decodeXML($res);
		return $retArray['status'] == 1;
	}/*}}}*/

  function getDeliverURL($reference)/*{{{*/
  {
    // Referenznummer+partnerID+Key
    $checkSum = sha1($reference.sHANSEATIC_PARTNERID.sHANSEATIC_PRESHAREDKEY);
    return sHANSEATIC_DELIVERY_URL.'?pageid=partnerInput&delivered=1&partnerID='.sHANSEATIC_PARTNERID.'&reference='.$reference.'&sha1='.$checkSum.'';
  }/*}}}*/

  function getPollURL()/*{{{*/
  {
    // Key+Datum (aktuell)
    $checkSum = sha1(sHANSEATIC_PRESHAREDKEY.date('Ymd'));
    return sHANSEATIC_POLL_URL.'?pageid=pollstatus&pos='.sHANSEATIC_PARTNERID.'&sha1='.$checkSum.'';
  }/*}}}*/

  function checkNextPoll()/*{{{*/
  {
    $state = false;
    $now = (int)date('Hi');
    $lastPoll = $firstPoll = '';

    $poll = (int)str_replace(':', '', sHANSEATIC_POLL_TIME_1);
    if ($poll != '' && $now >= $poll && sHANSEATIC_LAST_POLL < $poll){
      $state = true;
    }
    $lastPoll = $firstPoll = $poll;

    $poll = (int)str_replace(':', '', sHANSEATIC_POLL_TIME_2);
    if ($poll != '' && $now >= $poll && sHANSEATIC_LAST_POLL < $poll){
      $state = true;
    }
    if ($poll > $lastPoll) $lastPoll = $poll;
    if ($poll < $firstPoll) $firstPoll = $poll;

    $poll = (int)str_replace(':', '', sHANSEATIC_POLL_TIME_3);
    if ($poll != '' && $now >= $poll && sHANSEATIC_LAST_POLL < $poll){
      $state = true;
    }
    if ($poll > $lastPoll) $lastPoll = $poll;
    if ($poll < $firstPoll) $firstPoll = $poll;

    $poll = (int)str_replace(':', '', sHANSEATIC_POLL_TIME_4);
    if ($poll != '' && $now >= $poll && sHANSEATIC_LAST_POLL < $poll){
      $state = true;
    }
    if ($poll > $lastPoll) $lastPoll = $poll;
    if ($poll < $firstPoll) $firstPoll = $poll;

    $poll = (int)str_replace(':', '', sHANSEATIC_POLL_TIME_5);
    if ($poll != '' && $now >= $poll && sHANSEATIC_LAST_POLL < $poll){
      $state = true;
    }
    if ($poll > $lastPoll) $lastPoll = $poll;
    if ($poll < $firstPoll) $firstPoll = $poll;

    $poll = (int)str_replace(':', '', sHANSEATIC_POLL_TIME_6);
    if ($poll != '' && $now >= $poll && sHANSEATIC_LAST_POLL < $poll){
      $state = true;
    }
    if ($poll > $lastPoll) $lastPoll = $poll;
    if ($poll < $firstPoll) $firstPoll = $poll;

    #echo 'FirstPoll:'.$firstPoll.'<br>';
    #echo 'LastPoll:'.$lastPoll.'<br>';
    #echo 'SavedPoll:'.sHANSEATIC_LAST_POLL.'<br>';
    #echo 'Now:'.$now.'<br>';

    if ($firstPoll != '' && $lastPoll != '' && sHANSEATIC_LAST_POLL >= $lastPoll && $now >= $firstPoll){
      $state = true;
    }
    return $state;
  }/*}}}*/

  function rememberLastPoll()/*{{{*/
  {
    return $this->sDB_CONNECTION->Execute("
      UPDATE `s_core_config`
      SET `value` = ".$this->sDB_CONNECTION->qstr(date('Hi'))."
      WHERE `name` = ".$this->sDB_CONNECTION->qstr('sHANSEATIC_LAST_POLL')."
      ");
  }/*}}}*/

  function setStatus($reference, $statusBank, $amount)/*{{{*/
  {
    $map = array(
      '0' => 'Es wurde kein Kredit genehmigt.',
      '1' => 'Der Kredit wurde vorläufig akzeptiert.',
      '2' => 'Der Kredit wurde genehmigt.',
      '3' => 'Die Zahlung wurde von der Hanseatic Bank angewiesen.',
      '4' => 'Es wurde eine Zeitverlängerung eingetragen.',
      '5' => 'Vorgang wurde abgebrochen.',
    );
    $order_status = $this->getOrderStatus($statusBank);
    $comment = "\n".date('d.m.Y H:i').' '.sHANSEATIC_COMMENT.$map[$statusBank];
    $sql = "
      UPDATE `s_order`
      SET `cleared` = ".$this->sDB_CONNECTION->qstr($order_status).",
        `comment` = CONCAT(`comment`, ".$this->sDB_CONNECTION->qstr($comment).") 
      WHERE `ordernumber` = ".$this->sDB_CONNECTION->qstr($reference)."
      ";
    #echo $sql;
    $this->sDB_CONNECTION->Execute($sql);

    if (sHANSEATIC_NOTIFY_CUSTOMER == '1') $this->send_change_mail($reference, $order_status, false);
    if (sHANSEATIC_NOTIFY_ADMIN == '1')    $this->send_change_mail($reference, $order_status, true);
  }/*}}}*/

  function getOrderStatus($status)/*{{{*/
  {
    $stat = '';
    switch($status){
      case '0':
        $stat = sHANSEATIC_STATUS_0_ID;
        break;
      case '1':
        $stat = sHANSEATIC_STATUS_1_ID;
        break;
      case '2':
        $stat = sHANSEATIC_STATUS_2_ID;
        break;
      case '3':
        $stat = sHANSEATIC_STATUS_3_ID;
        break;
      case '4':
        $stat = sHANSEATIC_STATUS_4_ID;
        break;
      case '5':
        $stat = sHANSEATIC_STATUS_5_ID;
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
      $email = sHANSEATIC_NOTIFY_EMAIL;
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
      curl_setopt($ch, CURLOPT_USERAGENT, "Hanseatic Request");
      curl_setopt($ch, CURLOPT_USERPWD,'preview:kredit2008');

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

  function getCalculatorCode($amount)/*{{{*/
  {
    $amount = sprintf('%1.2f', $amount);
    $url =  sHANSEATIC_CALCULATOR_URL.'?id=71&partner='.sHANSEATIC_PARTNERID.'&wert='.$amount;
    $code = '
<div id="displayRatenborder" style="background-color:#FFFFFF;">
<iframe src="'.$url.'" scrolling="auto" frameborder="0"
style="width:'.sHANSEATIC_RATE_CALC_WIDTH.'px; height:'.sHANSEATIC_RATE_CALC_HEIGHT.'px;"></iframe>
</div>';
    return $code;
  }/*}}}*/

  function isGoodAmount($amount)/*{{{*/
  {
    $amount = sprintf('%1.2f', $amount);
    // Wenn Warenkorb zu niedrig dann ausblenden
    if ($amount < sHANSEATIC_MIN_AMOUNT) return false;
    // Wenn Warenkorb zu hoch dann ausblenden
    if ($amount > sHANSEATIC_MAX_AMOUNT) return false;

    return true;
  }/*}}}*/

  function checkRate($amount)/*{{{*/
  {
    $amount = sprintf('%1.2f', $amount * 100);
    // Wenn das Laufzeit Drop Down angezeigt werden soll.
    foreach($this->possibleRates AS $k => $v) {
      // Wenn der Betrag kleiner als die kleinste Rate dann fällt diese Laufzeit aus
      if (($amount / $v) < sHANSEATIC_MIN_CREDIT_RATE) continue;
      $rates[$v] = round($amount / $v);
    }
    // Wenn keine Laufzeiten möglich sind und keine Meldung angezeigt werden soll, dann Modul ausblenden
    if (empty($rates)) return array();
    return $rates;
  }/*}}}*/

  function getLowestRate($amount)/*{{{*/
  {
    $rates = $this->checkRate($amount);
    $t=0;
    foreach($rates AS $rates => $value){
      if ($value < $t || $t==0){
        $t = $value;
        $tmp = array();
        $tmp[$rates] = $value;
      }
    }
    return $tmp;
  }/*}}}*/

  function isActive()/*{{{*/
  {
    $sql = "
      SELECT `id`,`active` FROM `s_core_paymentmeans`
      WHERE `name` = 'hanseatic'
      ";
    $res = $this->sDB_CONNECTION->GetRow($sql);
    if (empty($res["active"])){
    	return false;
    }
   // print_r($this->sMODULES["sAdmin"]->sGetUserData()); exit;
    $test = $this->sMODULES["sAdmin"]->sGetPaymentMeanById($res["id"],$this->sMODULES["sAdmin"]->sGetUserData());
    
    return $test["id"]==$res["id"];
    return $res['active']==1;
  }/*}}}*/
}
?>