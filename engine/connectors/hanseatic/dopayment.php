<?php
$path = "../";													// Rel. Pfad zur Payment-Klasse
include("hanseatic.class.php");											// Standard-Payment-Klasse laden
/*
Neue Instanz der Klasse erzeugen.
Parameter - 1 : Hier können Sie eine Mailadresse angeben, an die mögliche Debug-Meldungen geschickt werden
Parameter - 2 : Der relative Pfad zur Payment-Klasse
*/
$payment = new hanseaticPayment("/dev/null","../");									

/*
Lädt alle verfügaren User-Daten, diese stehen anschließend im array payment->sUser bereit
*/
$payment->initUser();

/*
Enthält den Namen der Zahlungsart, die der Kunde aktuell gewählt hat
*/
$choosenPaymentMean = $payment->sUser["additional"]["payment"]["name"];
$userData = $payment->sUser;

// Prüfen ob AGBs akzeptiert wurden
if (!$_POST["sAGB"] && $payment->config['sIGNOREAGB']!="1"){
  echo $payment->sMODULES['sArticles']->sSYSTEM->sCONFIG['sSnippets']['sHanseaticAcceptAGB']
    ."<br /><a href=\"javascript:history.back();\">"
    .$payment->sMODULES['sArticles']->sSYSTEM->sCONFIG['sSnippets']['sHanseaticBack']."</a>";
	exit;
}
/*
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
$uos_param["param_dispatchID"] = $dispatchID;
$payment->sDB_CONNECTION->Execute("
	    		UPDATE s_order
          SET transactionID = '".$payment->sDB_CONNECTION->qstr($bookingId)."'
	    		WHERE temporaryID = ".$payment->sDB_CONNECTION->qstr($bookingId)."
	  		");
$payment->initPayment();
$_REQUEST["transaction"] = $bookingId;
$ordernumber = $payment->submitOrder("sale","../../core/class/viewports/","sViewportSale",sHANSEATIC_STATUS_ID);
#print_r($payment);
print_r($ordernumber);

$payment->doPayment($ordernumber, $amount, $userData);

if (isset($_GET['status']) && $_GET['status'] == 'OK'){
  if (isset($_GET['level'])){
    switch($_GET['level']){
      case 'green':
        $_GET['msg'] = urlencode(MODULE_PAYMENT_HANSEATIC_MSG_GREEN);
      break;
      case 'yellow':
        $_GET['msg'] = urlencode(MODULE_PAYMENT_HANSEATIC_MSG_YELLOW);
      break;
      case 'red':
        $_GET['msg'] = urlencode(MODULE_PAYMENT_HANSEATIC_MSG_RED);
        #$url = xtc_href_link('checkout_payment.php', 'payment_error='.$module->code.'&error='.$_GET['msg'], 'SSL', true, false);
        #xtc_redirect($url);
      break;
    }
  }

}
 */
$url = 'http://'.$payment->config["sBASEPATH"].'/'.$payment->sSYSTEM->sCONFIG["sBASEFILE"].'/sViewport,hanseatic_iframe/';
echo '<script>top.location.href="'.$url.'"</script>';
exit;
?>