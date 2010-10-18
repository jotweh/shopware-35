<?php
# http://www.shopware.vm/shopware.php/sViewport,moneybookers_success/?coreID=926078c023342825d075992a104f46fe&id=926078c023342825d075992a104f46fe&msid=07e3aa2bf1d3c2ab70ae1913cd3b85c8

include_once("s_login.php");

class sViewportMoneybookers_success{
  var $sSYSTEM;
	var $sViewportLogin;

	function sViewportMoneybookers_success(&$sSYSTEM,&$sViewportLogin){
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
    require("engine/connectors/moneybookers/moneybookers.class.php");											// Standard-Payment-Klasse laden
    /*
    Neue Instanz der Klasse erzeugen.
    Parameter - 1 : Hier können Sie eine Mailadresse angeben, an die mögliche Debug-Meldungen geschickt werden
    Parameter - 2 : Der relative Pfad zur Payment-Klasse
     */
    $payment = new moneybookersPayment("/dev/null",$path);									

    #mail('webmaster@web-dezign.de', 'Shopware Moneybookers Debug Mail2', print_r($_REQUEST, 1));

    # Lädt alle verfügaren User-Daten, diese stehen anschließend im array payment->sUser bereit
    $payment->initUser();

    if (!empty($_REQUEST['custom'])){
      // Übergebene Shop Params auswerten
      $custom_org = urldecode($_REQUEST['custom']);
      $custom = explode("-",$custom_org);	//Custom Array

      $currency = $payment->sSYSTEM->sCurrency["currency"] ? $payment->sSYSTEM->sCurrency["currency"] : "EUR";
      $skey     = $custom[6];
      
      $sVerify  = md5("deadbeef".$custom[0].$custom[1].$custom[7]."F3e5b9C6");

      /*Init Shopware param*/
      $coreID = $custom[0];
      $_REQUEST["trans_id"] = $custom[1];
      #$_REQUEST["coreID"] 	= $custom[0];	//CoreID / Session
      $_REQUEST["uniqueID"] 	= $custom[1];	//BookingID
      $_REQUEST["sUniqueID"] 	= $custom[1];	//BookingID
      $_REQUEST["sLanguage"]	= $custom[2];	//Language
      $_REQUEST["sCurrency"] 	= $currency;	//Currency
      $_REQUEST["sSubShop"] 	= intval($custom[4]);	//Subshop-ID
      $_REQUEST["dispatchID"] = intval($custom[5]);	//Dispatch
      $_REQUEST["sComment"] = "";	
    } else {
      $skey = $sVerify = true;
    }

    # Prüfsumme checken
    $transactionId = $_GET["transaction_id"];
    $check = $_GET["msid"];
    $checkSum = $payment->getSecureSum(sMONEYBOOKERS_MERCHANTID, $transactionId);
    #echo $check.' == '.$checkSum;
    if (/*$check == $checkSum &&*/ $skey == $sVerify){

      // Prüfen ob die Bestellung nicht ggf. schon durch die Notify.php abgeschlossen wurde
      $orderId = $payment->getOrderIdByTransactionId($transactionId);
      if ($orderId <= 0){
        $_REQUEST['param_sCoreId'] = $_GET['coreID'];
        $payment->initPayment();
        $_REQUEST["transaction"] = $transactionId;
        $ordernumber = $payment->submitOrder("sale","../../core/class/viewports/","sViewportSale",sMONEYBOOKERS_STATUS_ID);
      }

      $urlOK = 'http://'.$payment->config["sBASEPATH"].'/'.$payment->config["sBASEFILE"].'/sViewport,sale/sAction,doSale/sRefererAllowed,1/sCoreId,'.$coreID.'/sUniqueID,'.$_REQUEST["sUniqueID"].'/';	
      header('Location: '.$urlOK);    
      exit();
      /*
      $this->sSYSTEM->_GET["sViewport"] = "moneybookers_success";

      $templates = array(
        "sContainer"=>"/payment/moneybookers_success.tpl",
        "sContainerRight"=>""
      );
       */
    } else {
      $this->sSYSTEM->_GET["sViewport"] = "moneybookers_fail";

      $templates = array(
        "sContainer"=>"/payment/moneybookers_fail.tpl",
        "sContainerRight"=>""
      );
    }

    $variables = array();
		return array("templates"=>$templates,"variables"=>$variables);

	}
}
?>
