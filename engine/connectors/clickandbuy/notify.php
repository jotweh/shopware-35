<?php
/*
ClickandBuy-Schnittstelle
Version 1.0
(c)2008, PayIntelligent 
*/

$debug = ""; // E-Mailadresse für Debug-Informationen
$path = "../";

/*Initialisiere ClickandBuy Parameter*/
$shash	= $_REQUEST['shash'];
$custom_org = $_REQUEST['custom'];
$externalBDRID	= $_REQUEST['externalBDRID'];

if (!$_REQUEST["trans_id"]) $_REQUEST["trans_id"] = $externalBDRID;

/*Initialisiere Shopware Parameter*/
$custom = explode("|",$_REQUEST["custom"]);
$_REQUEST["coreID"] = $custom[0];
$_REQUEST["uniqueID"] = $custom[1];
$_REQUEST["sLanguage"] = $custom[2];	// Language
$_REQUEST["sCurrency"] = $custom[3]; 	// Currency
if (empty($_REQUEST["sCurrency"])) $_REQUEST["sCurrency"] = "EUR";
$_REQUEST["sSubShop"] = intval($custom[4]);		// Subshop-ID
$_REQUEST["dispatchID"] = intval($custom[5]);	// Dispatch
$_REQUEST["sComment"] = "";	

include("clickandbuy.class.php");
$payment = new clickandbuyPayment($debug,"../",false);

$payment->catchErrors();
$payment->initUser();

$userData = $payment->sUser;

$payment->initPayment();

/*Initialisiere ClickandBuy Parameter für Second Confirmation*/
$cabLink = $payment->cabLink;
$sellerID = $payment->sellerID;
$tmiPwd = $payment->tmiPwd;
$secondConfirmationStatus =  $payment->secondConfirmationStatus;

/*
 * Da hier keine IP Adresse von ClickandBuy geprüft werden kann, kommt hier eine Sicherheitsprüfung rein.
 * 1. Vergleiche $coreID REQUEST mit coreID aus cb_orders
 * 2. Vergleiche $shash mit $verify_shash
 */
$sql = "SELECT cb_transaction_id, coreID, cb_price, status, cb_uid FROM cb_orders WHERE externalBDRID='$externalBDRID'";	
$result = $payment->sDB_CONNECTION->GetRow($sql);

$db_cb_transaction_id = $result["cb_transaction_id"];
$db_coreID = $result["coreID"];
$db_cb_price = $result["cb_price"];
$db_status = $result["status"];
$db_cb_uid = $result["cb_uid"];

$s_orders_comment = "ClickandBuy UserID: ".$db_cb_uid." ClickandBuy TransaktionsID: ".$db_cb_transaction_id." externalBDRID: ".$externalBDRID;

$verify_shash = md5($db_coreID.$db_cb_transaction_id.$externalBDRID);

$error_url = 'http://'.$payment->config["sBASEPATH"].'/'.$payment->config["sBASEFILE"].'/sViewport,sale/sRefererAllowed,1/sCoreId,'.$custom[0].'/sUniqueID,'.$externalBDRID.'/';

	/*Sicherheitsprüfung*/
	
	
if ($secondConfirmationStatus == 1) {
	$result = $payment->secondConfirmation($debug, $sellerID,$tmiPwd,$externalBDRID, $cabLink);

	if ($result == 1) {
		/*Sicherheitsprüfung*/
		if (($db_status == 0) && ($db_coreID == $_REQUEST["coreID"]) && ($shash == $verify_shash)) {

			/*Update Status*/
			$sql  = "UPDATE cb_orders SET status = '1' WHERE externalBDRID = '$externalBDRID' LIMIT 1";
			$payment->sDB_CONNECTION->Execute($sql);		
			
			$status = 18;
			$success_url = 'http://'.$payment->config["sBASEPATH"].'/'.$payment->config["sBASEFILE"].'/sViewport,sale/sAction,doSale/sRefererAllowed,1/sCoreId,'.$custom[0].'/sUniqueID,'.$externalBDRID.'/';		
			
			/*Füge in s_orders die ClickandBuy Werte in die comment Spalte hinzu und Update den Status von Offen auf Reserviert*/ 
			$sql  = "UPDATE s_order SET cleared = '$status', comment = '$s_orders_comment' WHERE transactionID = '$externalBDRID' LIMIT 1";
			$payment->sDB_CONNECTION->Execute($sql);		
						
			header("Location: ".$success_url);
				
		} else {

			/*Security Check NOK!*/			
			$payment->throughError("Ein Fehler ist aufgetreten (coreID/shash)",false);
			echo "Error / Fehler (coreID/shash)";
			echo "<br><a href=\"$error_url\">Back</a> / <a href=\"$error_url\">Zur&uuml;ck</a>";	
		}
				
	} else {

		/*Fehler beim Second Confirmation!*/				
		$error_url = 'http://'.$payment->config["sBASEPATH"].'/'.$payment->config["sBASEFILE"].'/sViewport,sale/sRefererAllowed,1/sCoreId,'.$custom[0].'/sUniqueID,'.$externalBDRID.'/';

		echo "Error / Fehler (ClickandBuy Second-Confirmation)";
		echo "<br><a href=\"$error_url\">Back</a> / <a href=\"$error_url\">Zur&uuml;ck</a>";	
	}	
} else {


	
	if (($db_status == 0) && ($db_coreID == $_REQUEST["coreID"]) && ($shash == $verify_shash)) {

		/*Security Check OK, Update Status*/
		$sql  = "UPDATE cb_orders SET status = '1' WHERE externalBDRID = '$externalBDRID' LIMIT 1";
		$payment->sDB_CONNECTION->Execute($sql);		
		
		$status = 18;
		if (empty($payment->config["sUSESSL"])){
			$success_url = 'http://'.$payment->config["sBASEPATH"].'/'.$payment->config["sBASEFILE"].'/sViewport,sale/sAction,doSale/sRefererAllowed,1/sCoreId,'.$custom[0].'/sUniqueID,'.$externalBDRID.'/';		
		}else {
			$success_url = 'https://'.$payment->config["sBASEPATH"].'/'.$payment->config["sBASEFILE"].'/sViewport,sale/sAction,doSale/sRefererAllowed,1/sCoreId,'.$custom[0].'/sUniqueID,'.$externalBDRID.'/';	
		}
		/*Füge in s_orders die ClickandBuy Werte in die comment Spalte hinzu und Update den Status von Offen auf Reserviert*/ 
		$sql  = "UPDATE s_order SET cleared = '$status', comment = '$s_orders_comment' WHERE transactionID = '$externalBDRID' LIMIT 1";
		$payment->sDB_CONNECTION->Execute($sql);		

		header("Location: ".$success_url);
			
	} else {

		/*Security Check NOK*/	
		$payment->throughError("Ein Fehler ist aufgetreten (coreID/shash Fehler)",false);
		echo "Error / Fehler (coreID/shash)";
		echo "<br><a href=\"$error_url\">Back</a> / <a href=\"$error_url\">Zur&uuml;ck</a>";	
	}	
	
}
?>