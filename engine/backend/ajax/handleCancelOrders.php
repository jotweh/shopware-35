<?php
define('sAuthFile', 'sGUI');
define('sConfigPath',"../../../");
include("../../backend/php/check.php");
include("../../vendor/phpmailer/class.phpmailer.php");
$result = new checkLogin();
$result = $result->checkUser();
if ($result!="SUCCESS"){
	echo "FAIL";
	die();
}
// *****************
?>
<?php
if ($_GET["sendVoucher"])
{

$_GET["sendVoucher"] = explode("|",$_GET["sendVoucher"]);

	$sql =  "SELECT	* FROM `s_core_config_mails` WHERE `name` = 'sCANCELEDVOUCHER'";
	$result = mysql_query($sql);
	$email = mysql_fetch_assoc($result);
	
	$orderID = (int) $_GET["sendVoucher"][0];
	
	// Query language
	$getOrderLanguage = mysql_query("
	SELECT isocode FROM s_core_multilanguage, s_order WHERE s_order.id = $orderID AND
	s_order.subshopID = s_core_multilanguage.id
	");
	
	
	$language = mysql_result($getOrderLanguage,0,"isocode");

	
	if ($language!="de"){
		// Get Mail-Template
		$getMailTemplate = mysql_query("
		SELECT objectdata FROM s_core_translations WHERE objectkey = 1 AND objecttype = 'config_mails' AND objectlanguage = '$language'
		");
		$getMailTemplate = unserialize(mysql_result($getMailTemplate,0,"objectdata"));
		$getMailTemplate = $getMailTemplate["sCANCELEDVOUCHER"];
		foreach ($email as $key => $value){
			if (!empty($getMailTemplate[$key])){
				$email[$key] = $getMailTemplate[$key];
			}
		}
	}
	
	
	$voucherID = $_GET["sendVoucher"][1];
	
	$voucherID = str_replace("V","",$voucherID);
	$voucherID = intval($voucherID);
	
	$sql = "
		SELECT evc.id as vouchercodeID, code as vouchercode
		FROM s_emarketing_vouchers ev, s_emarketing_voucher_codes evc
		WHERE  modus = 1 AND (valid_to >= now() OR valid_to='0000-00-00')
		AND evc.voucherID = ev.id
		AND evc.userID = 0
		AND evc.cashed = 0
		AND ev.id=$voucherID
	";
	$result = mysql_query($sql);
	if($result&&mysql_num_rows($result))
	{
		$email["vouchercodeID"] = mysql_result($result,0,0);
		$email["vouchercode"] = mysql_result($result,0,1);
	}else {
		die("Keine freien Codes mehr verfügbar");
	}
	
	$email['content'] = str_replace("{\$sVouchercode}",$email["vouchercode"],$email['content']);
		
	$result = mysql_query("
		SELECT s_user.id,email FROM s_user, s_order 
		WHERE s_order.id = $orderID
		AND s_order.userID = s_user.id
	");
	$email["userID"] = mysql_result($result,0,0);
	$email["tomail"] = mysql_result($result,0,1);
	
	$sql = "
		UPDATE s_emarketing_voucher_codes evc
		SET
			userID={$email["userID"]}
		WHERE
			id={$email["vouchercodeID"]}
		AND
			userID=0
	";
	$result = mysql_query($sql);
		
	$result = mysql_query("
		UPDATE s_order SET comment = CONCAT(comment,'\n',DATE(NOW()),' Gutschein gesendet')
		WHERE id = $orderID
	");
	
	$mail = new PHPMailer;
	
	$mail->Subject = $sCore->sCONFIG['sMAILER_Subject'];
	$mail->defaultFromMail = $sCore->sCONFIG['sMAIL'];
	$mail->defaultFrom = $sCore->sCONFIG['sSHOPNAME'];
	$mail->Sender = $sCore->sCONFIG['sMAILER_Sender'];
	$mail->Subject = $sCore->sCONFIG['sMAILER_Subject'];
	$mail->Body = $sCore->sCONFIG['sMAILER_Body'];
	$mail->AltBody = $sCore->sCONFIG['sMAILER_AltBody'];
	$mail->ConfirmReadingTo = $sCore->sCONFIG['sMAILER_ConfirmReadingTo'];
	$mail->Hostname = $sCore->sCONFIG['sMAILER_Hostname'];
	$mail->Host = $sCore->sCONFIG['sMAILER_Host'];
	$mail->Port = $sCore->sCONFIG['sMAILER_Port'];
	
	$mail->SMTPSecure = $sCore->sCONFIG['sMAILER_SMTPSecure'];

	$mail->SMTPAuth = $sCore->sCONFIG['sMAILER_SMTPAuth'];
	$mail->Username = $sCore->sCONFIG['sMAILER_Username'];
	$mail->Password = $sCore->sCONFIG['sMAILER_Password'];

	$mail->From     = $email['frommail'];
	$mail->FromName = $email['fromname'];
	$mail->Subject  = $email['subject'];
	$mail->Body     = $email['content'];
	$mail->ClearAddresses();
	$mail->AddAddress($email["tomail"], "");
	
	if ($mail->Send()){
		echo "Dem Kunden wurde ein Gutschein geschickt";
	}
}
if ($_GET["sendQuestion"]){
	$_GET["sendQuestion"] = intval($_GET["sendQuestion"]);
	$sql =  "SELECT	* FROM `s_core_config_mails` WHERE `name` = 'sCANCELEDQUESTION'";
	$result = mysql_query($sql);
	$email = mysql_fetch_assoc($result);
	// Query language
	$getOrderLanguage = mysql_query("
	SELECT isocode FROM s_core_multilanguage, s_order WHERE s_order.id = {$_GET["sendQuestion"]} AND
	s_order.subshopID = s_core_multilanguage.id
	");
	
	
	$language = mysql_result($getOrderLanguage,0,"isocode");

	
	if ($language!="de"){
		// Get Mail-Template
		$getMailTemplate = mysql_query("
		SELECT objectdata FROM s_core_translations WHERE objectkey = 1 AND objecttype = 'config_mails' AND objectlanguage = '$language'
		");
		$getMailTemplate = unserialize(mysql_result($getMailTemplate,0,"objectdata"));
		$getMailTemplate = $getMailTemplate["sCANCELEDQUESTION"];
		foreach ($email as $key => $value){
			if (!empty($getMailTemplate[$key])){
				$email[$key] = $getMailTemplate[$key];
			}
		}
	}
	$mail = new PHPMailer;	

	
	$mail->Sender = $sCore->sCONFIG['sMAILER_Sender'];
	$mail->Subject = $sCore->sCONFIG['sMAILER_Subject'];
	
	$mail->defaultFromMail = $sCore->sCONFIG['sMAIL'];
	$mail->defaultFrom = $sCore->sCONFIG['sSHOPNAME'];
	$mail->Sender = $sCore->sCONFIG['sMAILER_Sender'];
	$mail->Subject = $sCore->sCONFIG['sMAILER_Subject'];
	$mail->Body = $sCore->sCONFIG['sMAILER_Body'];
	$mail->AltBody = $sCore->sCONFIG['sMAILER_AltBody'];
	$mail->Hostname = $sCore->sCONFIG['sMAILER_Hostname'];
	$mail->Host = $sCore->sCONFIG['sMAILER_Host'];
	$mail->Port = $sCore->sCONFIG['sMAILER_Port'];
	$mail->SMTPSecure = $sCore->sCONFIG['sMAILER_SMTPSecure'];
	$mail->SMTPAuth = $sCore->sCONFIG['sMAILER_SMTPAuth'];
	$mail->Username = $sCore->sCONFIG['sMAILER_Username'];
	$mail->Password = $sCore->sCONFIG['sMAILER_Password'];


	$mail->From     = ($email['frommail']);
	$mail->FromName = ($email['fromname']);
	$mail->Subject  = ($email['subject']);
	$mail->Body     = $email['content'];
	$mail->ClearAddresses();
	
	// Query mail-address
	$getMailAddress = mysql_query("
	SELECT email FROM s_user, s_order 
	WHERE s_order.id = {$_GET["sendQuestion"]}
	AND s_order.userID = s_user.id
	");
	
	$updateComment = mysql_query("
	UPDATE s_order SET comment = 'Frage gesendet'
	WHERE id = {$_GET["sendQuestion"]}
	");
	
	
	$getMailAddress = mysql_fetch_assoc($getMailAddress);
	$getMailAddress = $getMailAddress["email"];
	
	$mail->AddAddress($getMailAddress, "");
	echo "Frage wurde verschickt";
	
	if (!$mail->Send()){
		echo "Frage konnte nicht verschickt werden";
	}
}
if ($_GET["delete"]){
	$deleteOrder = mysql_query("
	DELETE FROM s_order 
	WHERE id = {$_GET["delete"]}
	");
	$deleteOrder = mysql_query("
	DELETE FROM s_order_details 
	WHERE orderID = {$_GET["delete"]}
	");
	$sInform = "Bestellung wurde aus Liste entfernt";
}
?>