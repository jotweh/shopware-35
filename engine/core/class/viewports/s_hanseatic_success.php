<?php
#print_r($_GET);
# $_GET['status'] == 'OK'
# $_GET['level'] == 'green'

include_once("s_login.php");

class sViewportHanseatic_success{
  var $sSYSTEM;
	var $sViewportLogin;

	function sViewportHanseatic_success(&$sSYSTEM,&$sViewportLogin){
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
    require("engine/connectors/hanseatic/hanseatic.class.php");											// Standard-Payment-Klasse laden
    /*
    Neue Instanz der Klasse erzeugen.
    Parameter - 1 : Hier können Sie eine Mailadresse angeben, an die mögliche Debug-Meldungen geschickt werden
    Parameter - 2 : Der relative Pfad zur Payment-Klasse
     */
    $payment = new hanseaticPayment("/dev/null",$path);									

    # Lädt alle verfügaren User-Daten, diese stehen anschließend im array payment->sUser bereit
    $payment->initUser();

    // Wenn nicht die Danke Seite von Shopware genutzt werden soll, dann diesen IF Block auskommentieren.
    if ($_GET['level'] == 'green' || $_GET['level'] == 'yellow'){
      $coreID = $_GET['coreId'];
      $orderId = $_GET['orderId'];
      $urlOK = 'http://'.$payment->config["sBASEPATH"].'/'.$payment->config["sBASEFILE"].'/sViewport,sale/sAction,doSale/sRefererAllowed,1/sCoreId,'.$coreID.'/sUniqueID,'.$orderId.'/';	
      header('Location: '.$urlOK);    
      exit();
    }

		$this->sSYSTEM->_GET["sViewport"] = "hanseatic_success";

    if ($_GET['level'] == 'yellow'){
      $templates = array(
        "sContainer"=>"/payment/hanseatic_ok.tpl",
        "sContainerRight"=>""
      );
    } else {
      $templates = array(
        "sContainer"=>"/payment/hanseatic_success.tpl",
        "sContainerRight"=>""
      );
    }

    $variables = array();
		return array("templates"=>$templates,"variables"=>$variables);

	}
}
?>