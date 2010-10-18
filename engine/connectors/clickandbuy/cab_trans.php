<?php
/*
ClickandBuy-Schnittstelle
Version 1.0
(c)2008, PayIntelligent 
*/

$debug = ""; // E-Mailadresse für Debug-Informationen
$path = "../";

/*Initialisiere ClickandBuy Parameter*/ 
$cb_linknr			= $_SERVER["HTTP_X_CONTENTID"];		//ClickandBuy Linknummer
$cb_price			= $_SERVER["HTTP_X_PRICE"];			//ClickandBuy in Millicents !
$cb_uid				= $_SERVER["HTTP_X_USERID"];		//ClickandBuy Kundennummer
$cb_transaction_id	= $_SERVER["HTTP_X_TRANSACTION"];	//ClickandBuy TransaktionID 
$cb_ip				= $_SERVER["REMOTE_ADDR"];			//ClickandBuy ServerIP

$currency		= $_REQUEST["cb_currency"];	//Currency

$externalBDRID = $_REQUEST["externalBDRID"];	  	//ExternalBDRID
$custom_org	= $_REQUEST["custom"];
$custom = explode("|",$_REQUEST["custom"]);	//Custom Array
$coreID = $custom[0];

/*Initialisiere Shopware Parameter*/
$_REQUEST["sCoreId"] = $_REQUEST["coreID"] = $custom[0];				//CoreID / Session
$_REQUEST["uniqueID"] 	= $custom[1];					//ExternalBDRID
$_REQUEST["sLanguage"]	= $custom[2];					//Sprache
$_REQUEST["sCurrency"] 	= $custom[3];					//Währung
$_REQUEST["sSubShop"] 	= intval($custom[4]);			//Subshop-ID
$_REQUEST["dispatchID"] = intval($custom[5]);			//Dispatch
$_REQUEST["sCurrency"] 	= $currency;					//Währung
$_REQUEST["sComment"] = "";	

if (!$_REQUEST["trans_id"]) $_REQUEST["trans_id"] = $externalBDRID;
if (empty($_REQUEST["sCurrency"])) $_REQUEST["sCurrency"] = "EUR";

include("clickandbuy.class.php");
$payment = new clickandbuyPayment($debug,"../",true);

$payment->catchErrors();
$payment->initUser();

$userData = $payment->sUser;

$payment->initPayment();

/*Setze HOME_URL für Redirect*/		
$HOME_URL 	= 'http://'.$payment->config["sBASEPATH"].'/engine/connectors/clickandbuy/notify.php';

/*Shopware UserID*/
$shopware_uid= $userData["billingaddress"]["customernumber"];; 	

/*Setze Shopware Preis*/
$shopware_price = $payment->getAmount();
$shopware_price = $payment->formatAmountCent($shopware_price);

/*Korrigiere ClickandBuy Preis*/
$cb_price = $cb_price / 1000;  // Millicent in Cent

$result=true;
$reason="";

/*Prüfe ClickandBuy UserID*/
if(empty($cb_uid) || is_nan($cb_uid))
{
	$result = false;
	$reason.= "cb_uid&";
}

/*Prüfe ClickandBuy SERVER IP*/
if(substr($cb_ip,0,11) != "217.22.128.")
{
	$result = false;
	$reason.= "cb_ip+";
}

/*Prüfe ClickandBuy TranaktionID, cb_transaction_id=0 ist ein Testkauf ohne Umsatz*/
if ($cb_transaction_id == 0)
{
	//$result = false;
  	//$reason.= "cb_transaction_id+";
}

/*Prüfe ClickandBuy Preis*/
if(empty($cb_price) || is_nan($cb_price))   // is_nan: for PHP >= 4.2.0
{
	$result = false;
	$reason.= "cb_price1=".$cb_price."+";
}

/*Vergleiche die Übereinstimmung des ClickandBuy Preises mit dem Shopware Preis*/
if(($cb_price) != $shopware_price)
{
	$result = false;
	$reason.= "cb_price2=".$cb_price.";".$shopware_price."+";
}	

/*Prüfung der externalBDRID*/
if($result){
	
	$sql = "SELECT cb_transaction_id FROM cb_orders WHERE externalBDRID='$externalBDRID'";	
	$count = $payment->sDB_CONNECTION->GetRow($sql);

	/*Verhindert Doppeltbuchungen bei Mehrfachaufruf!*/
	if ($count){
			$result = true;   
			
	} elseif ($result) {
			
			/*Preis umrechnen in Dezimal*/
			$cb_price = $cb_price / 100;  // Millicent to Cent

			/*Einfügen in die cb_orders Tabelle*/
			$sql  = "INSERT INTO cb_orders (uid,cb_uid,cb_linknr,cb_transaction_id,cb_price,currency,externalBDRID,coreID, status, date_time) ";
			$sql .= "VALUES ";
			$sql .= "('$shopware_uid','$cb_uid','$cb_linknr','$cb_transaction_id','$cb_price','$currency','$externalBDRID','$coreID','0','".date("Y-m-d H:i:s")."')";
		
			$payment->sDB_CONNECTION->Execute($sql);	
			
			$ordernumber = $payment->submitOrder("sale","../../core/class/viewports/","sViewportSale",0);
				
	}
}

$shash = md5($coreID.$cb_transaction_id.$externalBDRID);

$success_url = $HOME_URL."?result=success&externalBDRID=".$externalBDRID."&shash=".$shash."&custom=".$custom_org;
$error_url   = $HOME_URL."?result=error&reason=".$reason."&externalBDRID=".$externalBDRID."&shash=".$shash."&custom=".$custom_org;


/*Weiterleitung zu success oder error*/ 
if($result)
{
	header("Location: ".$success_url);
} else {
	header("Location: ".$error_url);
}


?>