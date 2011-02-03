<?php
include_once("s_login.php");

class sViewportHeidelpay_success{
  var $sSYSTEM;
	var $sViewportLogin;

	function sViewportHeidelpay_success(&$sSYSTEM,&$sViewportLogin){
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

    $path = "engine/connectors/";													// Rel. Pfad zur Payment-Klasse
    require("engine/connectors/heidelpay/heidelpay.class.php");											// Standard-Payment-Klasse laden
    /*
    Neue Instanz der Klasse erzeugen.
    Parameter - 1 : Hier können Sie eine Mailadresse angeben, an die mögliche Debug-Meldungen geschickt werden
    Parameter - 2 : Der relative Pfad zur Payment-Klasse
     */
    $payment = new heidelpayPayment("/dev/null",$path);									

    # Lädt alle verfügaren User-Daten, diese stehen anschließend im array payment->sUser bereit
    $payment->initUser();

    if ($payment->mailDebug) mail($payment->debugEmail, 'heidelpay_success.php', print_r($_POST,1));

    if (!empty($_REQUEST['custom'])){
      // Übergebene Shop Params auswerten
      $custom_org = urldecode($_REQUEST['custom']);
      $custom = explode("-",$custom_org);	//Custom Array

      $currency = $payment->sSYSTEM->sCurrency["currency"] ? $payment->sSYSTEM->sCurrency["currency"] : "EUR";
      $skey     = $custom[6];
      $sVerify  = md5("deadbeef".$custom[0].$custom[1].$custom[7]."F3BaAC6");

      /*Init Shopware param*/
      $coreID = $custom[0];
      $_REQUEST["trans_id"] = $custom[1];
      $_REQUEST["coreID"] 	= $custom[0];	//CoreID / Session
      $_REQUEST['param_sCoreId'] = $custom[0];	//CoreID / Session
      $_REQUEST["uniqueID"] 	= $custom[1];	//BookingID
      $_REQUEST["sUniqueID"] 	= $custom[1];	//BookingID
      $_REQUEST["param_uniqueId"] 	= $custom[1];	//BookingID
      $_REQUEST["sLanguage"]	= $custom[2];	//Language
      $_REQUEST["sCurrency"] 	= $currency;	//Currency
      $_REQUEST["sSubShop"] 	= intval($custom[4]);	//Subshop-ID
      $_REQUEST["dispatchID"] = intval($custom[5]);	//Dispatch
      $_REQUEST["param_dispatchID"] = intval($custom[5]);	//Dispatch
      $payMethod = $custom[8];	//Paymethod
      $_REQUEST["sComment"] = "";	
    } else {
      $skey = $sVerify = true;
    }

    if ($skey == $sVerify){
      // Bestellung abschliessen
      $transactionId = $_GET["order_id"];
      $payment->initPayment();
      $_REQUEST["transaction"] = $transactionId;
      $ordernumber = $payment->submitOrder("sale","../../core/class/viewports/","sViewportSale",sHEIDELPAY_STATUS_SUCCESS);
      // Heidelpay Unique ID für WaWi speichern
      $uniqueId = $_GET['uid'];
      $payment->saveUniqueID($ordernumber, $uniqueId);
      // Heidelpay Short ID als Kommentar in Bestellung speichern
      $shortId = $_GET['shortid'];
      $payment->saveShortID($ordernumber, $shortId);
      // Benachrichtigungs EMails verschicken
      $payment->sendNotifyMails($ordernumber, sHEIDELPAY_STATUS_SUCCESS);

      $urlOK = 'http://'.$payment->config["sBASEPATH"].'/'.$payment->config["sBASEFILE"].'/sViewport,sale/sAction,doSale/sRefererAllowed,1/sCoreId,'.$coreID.'/sUniqueID,'.$_REQUEST["sUniqueID"].'/';	
      header('Location: '.$urlOK);
      #echo $urlOK;
      exit();
      /*
      $this->sSYSTEM->_GET["sViewport"] = "heidelpay_success";

      $templates = array(
        "sContainer"=>"/payment/heidelpay_success.tpl",
        "sContainerRight"=>""
      );

      $variables = array();
      return array("templates"=>$templates,"variables"=>$variables);
       */
    } else {
      $this->sSYSTEM->_GET["sViewport"] = "heidelpay_fail";

      $templates = array(
        "sContainer"=>"/payment/heidelpay_fail.tpl",
        "sContainerRight"=>""
      );

      $variables = array();
      return array("templates"=>$templates,"variables"=>$variables);
    }
  }
}
?>
