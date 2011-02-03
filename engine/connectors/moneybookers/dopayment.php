<?php
$path = "../";													// Rel. Pfad zur Payment-Klasse
include("moneybookers.class.php");											// Standard-Payment-Klasse laden
/*
Neue Instanz der Klasse erzeugen.
Parameter - 1 : Hier können Sie eine Mailadresse angeben, an die mögliche Debug-Meldungen geschickt werden
Parameter - 2 : Der relative Pfad zur Payment-Klasse
*/
$payment = new moneybookersPayment("/dev/null","../");									

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
  echo $payment->sMODULES['sArticles']->sSYSTEM->sCONFIG['sSnippets']['sMoneybookersAcceptAGB']
    ."<br /><a href=\"javascript:history.back();\">"
    .$payment->sMODULES['sArticles']->sSYSTEM->sCONFIG['sSnippets']['sMoneybookersBack']."</a>";
	exit;
}

#$url = 'http://'.$payment->config["sBASEPATH"].'/'.$payment->sSYSTEM->sCONFIG["sBASEFILE"].'/sViewport,moneybookers_iframe/';
#echo '<script>top.location.href="'.$url.'"</script>';

    # Ermittelt den aktuellen Bestellwert
    $amount = $payment->getAmount();

    # Abfrage des Warenkorbs
    $basket = $payment->getBasket();

    # Falls Warenkorb leer oder Bestellwert = 0 => Abbruch der Zahlung
    if (!$basket["content"][0] || $amount<=0){
      echo "Die Bestellung wurde bereits abgeschickt<br /><a href=\"javascript:history.back();\">zurück</a>";
      exit;
    }

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

    $iframeCode = $payment->doPayment($bookingId, $amount, $userData, session_id(), $bookingId);
    echo $iframeCode;
#header('Location: '.$url);
exit;
?>
