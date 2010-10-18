<?php
	define('sAuthFile', 'sGUI');
	define('sConfigPath',"../../../../../");
	include("../../../../backend/php/check.php");
	$result = new checkLogin();
	$result = $result->checkUser();
	if ($result!="SUCCESS"){
		die();
	}
	
	function generateVoucherCode()
	{
	   mt_srand ((double) microtime() * 1000000);
	   $voucherCode = "";
	   $chars = "123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
	   for ($k = 0; $k < 8; $k += 1)
	   {
	     $num = mt_rand(0, strlen($chars)-1);
	     $voucherCode .= $chars[$num];
	   }
	   return $voucherCode;
	}

	
		
	$numberofunits = (empty($_REQUEST["numberofunits"])) ? 0 :  (int) $_REQUEST["numberofunits"]; 
	$id = (empty($_REQUEST["id"])) ? 0 :  (int) $_REQUEST["id"]; 
	// Delete All Voucher Codes with VoucherID 
	$sql = "DELETE FROM s_emarketing_voucher_codes WHERE voucherID = $id";
	mysql_query($sql); // It´s fine cause if the numberofunits is 0 the user wanted to do that
	
	for ($i=0; $i < $numberofunits; $i++) {
		$ticketCode = generateVoucherCode();
		// Fast check if code is already determinated
		$checkCode = mysql_query("
		SELECT id FROM s_emarketing_voucher_codes WHERE voucherID=$id AND
		code='$ticketCode'
		");
		
		if (!mysql_num_rows($checkCode)) {
			$insertCode = mysql_query("
			INSERT INTO s_emarketing_voucher_codes (voucherID, code)
			VALUES ($id,'$ticketCode')
			");
		}
		else {
			$i--; // try this stuff again
		}
	}
?>