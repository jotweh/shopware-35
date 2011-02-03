<?php
	define('sAuthFile', 'sGUI');
	define('sConfigPath',"../../../../../");
	include("../../../../backend/php/check.php");
	$result = new checkLogin();
	$result = $result->checkUser();
	if ($result!="SUCCESS"){
		die();
	}
	
	
	$feedID = (empty($_REQUEST["feedID"])||!is_numeric($_REQUEST["feedID"])) ? 0 : (int) $_REQUEST["feedID"];
	//To get all Vouchers with the right ID
	$getVoucher = mysql_query("
	SELECT description FROM s_emarketing_vouchers
	WHERE id = $feedID
	");
	$description = mysql_result($getVoucher,0,"description");
	require_once("csvdump.php");// Used for CVS Export
	$dumpfile = new iam_csvdump();
	
	//CVS Export SQL Statement
	$sql = "
		SELECT code, customernumber, firstname, lastname, cashed 
		FROM s_emarketing_voucher_codes
		LEFT JOIN s_user_billingaddress ON (s_user_billingaddress.userID = s_emarketing_voucher_codes.userID)
		WHERE s_emarketing_voucher_codes.voucherID = $feedID
		ORDER BY cashed ASC
	";
	
	$dumpfile->dump($sql, "Gutschein-Codes-$description-".date("d.m.Y"), "csv", "", "", "", "" ); 
	
	exit();
	
?>