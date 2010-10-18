<?php
$path = "../";													// Rel. Pfad zur Payment-Klasse
include("heidelpay.class.php");											// Standard-Payment-Klasse laden
/*
Neue Instanz der Klasse erzeugen.
Parameter - 1 : Hier können Sie eine Mailadresse angeben, an die mögliche Debug-Meldungen geschickt werden
Parameter - 2 : Der relative Pfad zur Payment-Klasse
*/
$payment = new heidelpayPayment("/dev/null","../");									

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
  echo $payment->sMODULES['sArticles']->sSYSTEM->sCONFIG['sSnippets']['sHeidelpayAcceptAGB']
    ."<br /><a href=\"javascript:history.back();\">"
    .$payment->sMODULES['sArticles']->sSYSTEM->sCONFIG['sSnippets']['sHeidelpayBack']."</a>";
	exit;
}

$url = 'http://'.$payment->config["sBASEPATH"].'/'.$payment->sSYSTEM->sCONFIG["sBASEFILE"].'/sViewport,heidelpay_iframe/';
echo '<script>top.location.href="'.$url.'"</script>';
exit;
?>