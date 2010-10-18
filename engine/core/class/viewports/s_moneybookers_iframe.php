<?php
include_once("s_login.php");

class sViewportMoneybookers_iframe{
  var $sSYSTEM;
	var $sViewportLogin;

	function sViewportMoneybookers_iframe(&$sSYSTEM,&$sViewportLogin){
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

    $this->sSYSTEM->_GET["sViewport"] = "moneybookers_iframe";

    $templates = array(
      "sContainer"=>"/payment/moneybookers_iframe.tpl",
      "sContainerRight"=>""
    );

    $path = "engine/connectors/";													// Rel. Pfad zur Payment-Klasse
    require("engine/connectors/moneybookers/moneybookers.class.php");											// Standard-Payment-Klasse laden
    /*
    Neue Instanz der Klasse erzeugen.
    Parameter - 1 : Hier können Sie eine Mailadresse angeben, an die mögliche Debug-Meldungen geschickt werden
    Parameter - 2 : Der relative Pfad zur Payment-Klasse
     */
    $payment = new moneybookersPayment("/dev/null",$path);									

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

    # Userdaten laden
    $userData = $payment->sUser;

    $dispatchID = $payment->sSYSTEM->_SESSION["sDispatch"];	// SHOPWARE 2.0.4

    $_REQUEST['param_sCoreId'] = session_id();
    $bookingId = md5(uniqid(rand()));  
    $payment->sSYSTEM->_SESSION["bookingId"] = $bookingId;
    $_REQUEST["param_uniqueId"] = $bookingId;

    /*
    $sql = "
      UPDATE s_order
      SET transactionID = ".$payment->sDB_CONNECTION->qstr($bookingId)."
      WHERE temporaryID = ".$payment->sDB_CONNECTION->qstr($_REQUEST['SHOPWARESID'])."
      ";
    $payment->sDB_CONNECTION->Execute($sql);
     */

    $payment->initPayment();

    $iframeCode = $payment->doPayment($bookingId, $amount, $userData, session_id());

    $isIFrame = true;
    $variables = array(
      'sIframe' => $iframeCode,
      'isIFrame' => $isIFrame
    );
    return array("templates"=>$templates,"variables"=>$variables);

  }
}
?>
