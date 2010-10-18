<?php
include_once("s_login.php");

class sViewportHanseatic_iframe{
  var $sSYSTEM;
	var $sViewportLogin;

	function sViewportHanseatic_iframe(&$sSYSTEM,&$sViewportLogin){
		if (!is_object($sViewportLogin)){
			$this->sViewportLogin = new sViewportLogin($sSYSTEM,$this);
			$this->sViewportLogin->sSYSTEM = $sSYSTEM;
		}else {
			$this->sViewportLogin = $sViewportLogin;
		}
	}

  function sRender(){

    // Check users permission
    if (!$this->sSYSTEM->sMODULES['sAdmin']->sCheckUser()){
      $this->sSYSTEM->_GET["sViewport"] = "login";
      $this->sSYSTEM->_POST["sTarget"] = "sale";
      return $this->sViewportLogin->sRender();
    }else {
      $userData = $this->sSYSTEM->sMODULES['sAdmin']->sGetUserData();
      $variables["sUserData"] = $userData;
    }

    $this->sSYSTEM->_GET["sViewport"] = "hanseatic_iframe";

    $templates = array(
      "sContainer"=>"/payment/hanseatic_iframe.tpl",
      "sContainerRight"=>""
    );

    $path = "engine/connectors/";													// Rel. Pfad zur Payment-Klasse
    require("engine/connectors/hanseatic/hanseatic.class.php");											// Standard-Payment-Klasse laden
    /*
    Neue Instanz der Klasse erzeugen.
    Parameter - 1 : Hier können Sie eine Mailadresse angeben, an die mögliche Debug-Meldungen geschickt werden
    Parameter - 2 : Der relative Pfad zur Payment-Klasse
     */
    $payment = new hanseaticPayment("/dev/null",$path);									

    # Lädt alle verfügaren User-Daten, diese stehen anschließend im array payment->sUser bereit
    $payment->initUser();

    # Enthält den Namen der Zahlungsart, die der Kunde aktuell gewählt hat
    $choosenPaymentMean = $payment->sUser["additional"]["payment"]["name"];
    $userData = $payment->sUser;

    # Ermittelt den aktuellen Bestellwert
    $amount = $payment->getAmount();

    # Abfrage des Warenkorbs
    $basket = $payment->getBasket();

    # Falls Warenkorb leer oder Bestellwert = 0 => Abbruch der Zahlung
    if (!$basket["content"][0] || $amount<=0){
      echo "Die Bestellung wurde bereits abgeschickt<br /><a href=\"javascript:history.back();\">zurück</a>";
      exit;
    }

	$bookingId = md5(uniqid(rand()));  
	$currency = $payment->sSYSTEM->sCurrency["currency"] ? $payment->sSYSTEM->sCurrency["currency"] : "EUR";
	/*Init Shopware param*/
	$_REQUEST["trans_id"] = $bookingId;
	$_REQUEST["uniqueID"] 	= $bookingId;	//BookingID
	$_REQUEST["sLanguage"]	= $this->sSYSTEM->sLanguage;	//Language
	$_REQUEST["sCurrency"] 	= $currency;	//Currency
	$_REQUEST["sSubShop"] 	= $this->sSYSTEM->_SESSION["sSubShop"]["id"];	//Subshop-ID
	$_REQUEST["dispatchID"] = $this->sSYSTEM->_SESSION["sDispatch"];	//Dispatch
	$_REQUEST["param_dispatchID"] = $_REQUEST["dispatchID"];
	$_REQUEST["sComment"] = "";	

    # Userdaten laden
    $userData = $payment->sUser;

    $_REQUEST['param_sCoreId'] = session_id();
    $payment->sSYSTEM->_SESSION["bookingId"] = $bookingId;
    $_REQUEST["param_uniqueId"] = $bookingId;
    $payment->sDB_CONNECTION->Execute("
      UPDATE s_order
      SET transactionID = '".$payment->sDB_CONNECTION->qstr($bookingId)."'
      WHERE temporaryID = ".$payment->sDB_CONNECTION->qstr($bookingId)."
      ");
    $payment->initPayment();
    $_REQUEST["transaction"] = $bookingId;
    $ordernumber = $payment->submitOrder("sale","../../core/class/viewports/","sViewportSale",sHANSEATIC_STATUS_ID);

    $iframeCode = $payment->doPayment($ordernumber, $amount, $userData, session_id());

    $isIFrame = true;
    if (sHANSEATIC_URL_TYPE == 2) $isIFrame = false;
    $variables = array(
      'sIframe' => $iframeCode,
      'isIFrame' => $isIFrame
    );
    return array("templates"=>$templates,"variables"=>$variables);

  }
}
?>