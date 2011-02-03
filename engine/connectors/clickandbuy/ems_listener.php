<?php
/*
ClickandBuy-Schnittstelle
Version 1.0
(c)2008, PayIntelligent 
*/

$ip_check = $_SERVER["REMOTE_ADDR"];

/*Prüfe ClickandBuy SERVER IP*/
if(substr($ip_check,0,11) != "217.22.128.")
{
	$result = "error";
	exit();
}

$path = "../../../";
include($path."config.php"); 	
include("lib/xml.php");

$debug = ""; // E-Mailadresse für Debug-Informationen
 	
$link = mysql_connect($DB_HOST ,$DB_USER ,$DB_PASSWORD );
 
/*Prüfe MySQL Verbindung*/	
if ( ! $link ) die( "Keine Verbindung zu MySQL" );

/*Verbindung zur Datenbank*/
MYSQL_SELECT_DB($DB_DATABASE, $link ) or die ( "Konnte Datenbank \"$db\" nicht öffnen: ".MYSQL_ERROR() );

//einlesen des xml Parameters und entfernen von Quotes
$data = stripslashes($_POST['xml']);
	
/*Setze Array aus String*/ 
$xml = XML_unserialize($data);

/*Initialisiere Daten*/	
$eventID = $xml['EVENT-DATA']['GLOBAL']['event-id'];
$action = $xml['EVENT-DATA']['BDR']['bdr-data']['action'];
$externalBDRID = $xml['EVENT-DATA']['BDR']['bdr-data']['externalBDRID'];
$bdrID = $xml['EVENT-DATA']['BDR']['bdr-data']['bdr-id'];
$price = $xml['EVENT-DATA']['BDR']['bdr-data']['price'];
$currency = $xml['EVENT-DATA']['BDR']['bdr-data']['currency'];
$crn = $xml['EVENT-DATA']['GLOBAL']['crn'];
$systemID = $xml['EVENT-DATA']['GLOBAL']['systemID'];
$linkID = $xml['EVENT-DATA']['BDR']['bdr-data']['link-nr'];
$cb_datetime = $xml['EVENT-DATA']['GLOBAL']['datetime'];
$shopware_datetime = date("Y-m-d H:i:s");

$price = $price / 100;
$status = 0;
	
if(empty($eventID) || empty($action) || empty($externalBDRID)){

	//mail($debug,'Errror in XML PUSH STRUCTURE',$data);
	echo "NOK";
	 		
} else {
	
	$sql = "SELECT eventID FROM cb_events WHERE eventID = '$eventID'";
	$result = mysql_fetch_object(mysql_query($sql, $link));

	/*EventID ist nicht in DB, füge Resultate ein!*/
	if (empty($result)) {
	
		$sql = "insert into cb_events (eventID, action, externalBDRID, bdrID, price, currency, crn, systemID, linkID, xml, cb_datetime, shopware_datetime) values ('$eventID', '$action', '$externalBDRID', '$bdrID','$price','$currency','$crn','$systemID','$linkID','$data','$cb_datetime','$shopware_datetime')";
		$query = mysql_query($sql) or die(mysql_error());		
				
		if ($action=='payment_successful') $status= 12;
		if ($action=='cancelled') $status= 4;
		if ($action=='charge back') $status= 13;
		if ($action=='charge back lifted') $status= 12;
		if ($action=='BDR to collection agency') $status= 16;
		if ($action=='BDR successfully collected from collection agency') $status= 12;
		if ($action=='BDR not collected from collection agency') $status= 8;
		if ($action=='booked-out') $status= 21;
		if ($action=='booked-in') $status= 12;
						
		/*Falls Charge Back setze evtl. die 2.Mahnung und 3.Mahnung*/
		if ($status == 13) {
			$sql = "SELECT cleared FROM s_order WHERE transactionID='$externalBDRID'";	
			$result = mysql_fetch_object(mysql_query($sql, $link));
			if ($result->cleared == 13) $status = 14;
			elseif ($result->cleared == 14) $status = 15;
			elseif ($result->cleared == 15) $status = 15;								
		}
				
		if ($status > 0) {
			/*Update Status*/
			$sql  = "UPDATE s_order SET cleared = $status WHERE transactionID = '$externalBDRID' LIMIT 1";
			$query = mysql_query($sql) or die(mysql_error());				
		}		
	}	
	echo "ok";
}
?>