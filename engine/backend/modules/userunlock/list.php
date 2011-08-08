<?php
define('sAuthFile', 'sGUI');
define('sConfigPath',"../../../../");
include("../../../backend/php/check.php");
$result = new checkLogin();
$result = $result->checkUser();
if ($result!="SUCCESS"){
echo "
	<html><title>Time-Out</title><head></head>
	<script language=\"javascript\">
	parent.location.reload();
	</script>
	<body>Bitte loggen Sie sich neu ein!</body></html>
	";
	die();
}

if ($_GET["id"]){
	$sql = "SELECT email, validation FROM s_user WHERE id=".$_GET["id"];
	//echo $sql;
	$queryUser = mysql_query($sql);
	if (@mysql_num_rows($queryUser)){
		//echo "test";
		$GROUP = mysql_result($queryUser,0,"validation");
		$email = mysql_result($queryUser,0,"email");
		// ACCEPTED // REJECTED
		$sql =  "SELECT	* FROM `s_core_config_mails` WHERE `name` = 'sCUSTOMERGROUP".$GROUP."ACCEPTED'";
		$result = mysql_query($sql);
		$emailData = mysql_fetch_assoc($result);
		
		if (!$emailData["subject"]){
	
			$sError = $sLang["userunlock"]["list_sError"];
			
		}else {
			// UPDATE
			$updateUser = mysql_query("UPDATE s_user SET validation = '', customergroup='$GROUP' WHERE id=".$_GET["id"]);
			
			// MAIL
				
			$mail = clone Shopware()->Mail();
			$mail->From     = $emailData['frommail'];
			$mail->FromName = $emailData['fromname'];
			$mail->Subject  = $emailData['subject'];
			$mail->Body     = $emailData['content'];
			$mail->ClearAddresses();
			$mail->AddAddress($email, "");
			if (!$mail->Send()){
				
			}
			$sInform = $sLang["userunlock"]["list_sInform"];
		}
	}
}

if ($_GET["rejectid"]){

	
	$queryUser = mysql_query("SELECT email, validation FROM s_user WHERE id=".$_GET["rejectid"]);
	if (@mysql_num_rows($queryUser)){
		$GROUP = mysql_result($queryUser,0,"validation");
		$email = mysql_result($queryUser,0,"email");
		// ACCEPTED // REJECTED
		$sql =  "SELECT	* FROM `s_core_config_mails` WHERE `name` = 'sCUSTOMERGROUP".$GROUP."REJECTED'";
		$result = mysql_query($sql);
		$emailData = mysql_fetch_assoc($result);
		
		if (!$emailData["subject"]){
			$sError = "eMail-Template nicht gefunden";
			
		}else {
			// UPDATE
			$updateUser = mysql_query("
			UPDATE s_user SET validation = '' WHERE id=".$_GET["rejectid"]);
			
			// MAIL
				
			$mail = clone Shopware()->Mail();
			$mail->From     = $emailData['frommail'];
			$mail->FromName = $emailData['fromname'];
			$mail->Subject  = $emailData['subject'];
			$mail->Body     = $emailData['content'];
			$mail->ClearAddresses();
			$mail->AddAddress($email, "");
			if (!$mail->Send()){
				
			}
			$sInform = $sLang["userunlock"]["list_customer_not_assigned"];
		}
	}
}
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title><?php echo $sLang["userunlock"]["list_customerlist"] ?></title>
<!-- Common Styles for the examples -->
</head>
<script type="text/javascript" src="../../../backend/js/mootools.js"></script>
<link href="../../../backend/css/modules.css" rel="stylesheet" type="text/css" />
<link href="../../../backend/plugins/moo.table/mootable.css" rel="stylesheet" type="text/css" />
<link href="../../../backend/css/icons.css" rel="stylesheet" type="text/css" />
<script>
// Shopware - 2  JS-Wrapper-Code -
function sWrapper(sFunction, sId){
	switch (sFunction){
		case "unlockClientRedirect":
			// Redirect
			window.location.href = "<?php echo $_SERVER["PHP_SELF"] ?>?id="+sId;
			break;
		case "rejectClientRedirect":
			// Redirect
			window.location.href = "<?php echo $_SERVER["PHP_SELF"] ?>?rejectid="+sId;
			break;
		case "newSupplier":
		window.location.href = "<?php echo $_SERVER["PHP_SELF"] ?>?new=1";
			break;
		case "saveSupplier":
		try {
			$('save').submit();
		}catch (e) {} 
		
		break;
	}
}

function unlockClient(ev,text,text2,number){
		
	<?php global $sLang; ?>

	parent.parent.sConfirmationObj.show('<?php echo $sLang["userunlock"]["list_the_customer"] ?> "'+text+'" <?php echo $sLang["userunlock"]["list_customer_team"] ?>"'+ text2 +'" <?php echo $sLang["userunlock"]["list_assigned"] ?>',window,'unlockClientRedirect',ev);
	
}

function rejectClient(ev,text,text2,number){
	
	parent.parent.sConfirmationObj.show('<?php echo $sLang["userunlock"]["list_the_customer"] ?>  "'+text+'" <?php echo $sLang["userunlock"]["list_rejected"] ?>',window,'rejectClientRedirect',ev);

}
window.onload = function(){
	<?php
		if ($sInform){
			echo "parent.parent.Growl('$sInform');";
		}
		if ($sError){
			echo "parent.parent.Growl('$sError');";
			// Das Fenster shaken
			echo "parent.parent.sWindows.focus.shake(50);";
		}
	?>
	
};
</script>
<script type="text/javascript" src="../../../backend/plugins/moo.table/mootable.js"></script>
<?php
// TEMPLATE FÜR NICHT LIZENZIERTE MODULE //
if (!$sCore->sCheckLicense("","",$sCore->sLicenseData["sGROUPS"])){
echo $sCore->sDumpLicenceInfo("../../../","Modul Kundengruppen","Wenn Sie mehr als eine Käuferschicht bedienen wollen, ist dieses Modul genau das Richtige! Definieren Sie beliebig viele Kundengruppen und statten diese mit eigenen Preisen, Staffeln oder Rabatten aus. Ihre Shopware ist somit für B2B und B2C optimal aufgestellt.","http://www.shopware-ag.de/Haendlerbereich-Kunden.-_detail_67_196.html","sGROUPS");
$licenceFailed = true;
	exit;
}
if (!$licenceFailed){ 
?>
	
		

<?php
}
?>
<?php
// Realy simple search by Char
$epp = 10;
//if (!$_GET["searchByChar"]) $_GET["searchByChar"] = "H";

$_GET["searchByChar"] = urldecode($_GET["searchByChar"]);


	$sql = "
	SELECT DISTINCT s_user.active AS active, customergroup,validation, s_user.id AS id, DATE_FORMAT(firstlogin,'%d.%m.%Y') AS regdate, company, customernumber, firstname, lastname, zipcode, city
	FROM s_user, s_user_billingaddress WHERE 
	s_user.id = s_user_billingaddress.userID 
	AND validation != ''
	ORDER BY s_user.id DESC
	";
	$queryUsers = mysql_query($sql);


?>


<script type='text/javascript'>	


<?php
$entrys =@mysql_num_rows($queryUsers);
if (!$entrys){
	// No search-results
	$entrys = 0;
?>

var headers = [
{
"text":"<?php echo $sLang["userunlock"]["list_status"] ?>",
"key":"kdnr","sortable":true,
"fixedWidth":true,"defaultWidth":"500px"}
];
var data = [{"kdnr":"<?php echo $sLang["userunlock"]["list_no_customer"] ?>"}
];

<?php
} else {
	
	if(empty($_GET["site"])){
		$_GET["site"] = 0;
	}
	
	$limit = $epp*$_GET["site"];


	$sql = "
	SELECT DISTINCT s_user.active AS active, customergroup,validation, s_user.id AS id, DATE_FORMAT(firstlogin,'%d.%m.%Y') AS regdate, company, customernumber, firstname, lastname, zipcode, city
	FROM s_user, s_user_billingaddress WHERE 
	s_user.id = s_user_billingaddress.userID 
	AND validation != ''
	ORDER BY s_user.id DESC
	";


$queryUsers = mysql_query($sql);
?>

var headers = [
{

"text":"<?php echo $sLang["userunlock"]["list_reg_date"] ?>",
"key":"regdate","sortable":true,
"fixedWidth":true,"defaultWidth":"70px","date":true},
{
"text":"<?php echo $sLang["userunlock"]["list_firm"] ?>",
"key":"company","sortable":true,
"fixedWidth":true,"defaultWidth":"150px"},
{
"text":"<?php echo $sLang["userunlock"]["list_customer"] ?>",
"key":"name","sortable":true,
"fixedWidth":true,"defaultWidth":"200px"},
{
"text":"<?php echo $sLang["userunlock"]["list_postcode"] ?>",
"key":"zip","sortable":true,
"fixedWidth":true,"defaultWidth":"40px"},
{
"text":"<?php echo $sLang["userunlock"]["list_city"] ?>",
"key":"city","sortable":true,
"fixedWidth":true,"defaultWidth":"100px"},
{
"text":"<?php echo $sLang["userunlock"]["list_customer_group"] ?>",
"key":"options","sortable":true,
"fixedWidth":false,"defaultWidth":"125px"},
{
"text":"<?php echo $sLang["userunlock"]["list_status"] = "Status"; ?>",
"key":"status","sortable":true,
"fixedWidth":true,"defaultWidth":"60px"}
];
<?php
	// Display search-results
	echo "var data = [";
	$countUsers = mysql_num_rows($queryUsers);
	$i = 0;
	while ($user=mysql_fetch_array($queryUsers)){
		$i++;
		$user["idStr"] = sprintf("%06d", $user["id"]);
		$comma = $i == $countUsers ? "" : ",";
		// Kundengruppe auslesen
		$getCustomerGroup = mysql_query("
		SELECT description FROM s_core_customergroups
		WHERE groupkey='{$user["validation"]}'
		");
		$groupname = @mysql_result($getCustomerGroup,0,"description");
		if ($groupname && strlen($groupname)>15){
			$groupname = substr($groupname,0,15)."..";
		}
		
		// 
?>//customernumber
	{
	
	"regdate":"<?php echo $user["regdate"] ?>",
	"company":"<?php echo $user["company"] ?>",
	"name":"<a onclick=\"parent.parent.loadSkeleton('userdetails',false, {'id':<?php echo $user["id"] ?>})\" class=\"ico information\" style=\"cursor:pointer\"></a><?php echo $user["lastname"].", ".$user["firstname"]?>",
	"zip":"<?php echo $user["zipcode"]?>",
	"city":"<?php echo $user["city"]?>",
	"options":"<?php echo $groupname ?>",
	"status":"<a style=\"cursor:pointer\" class=\"ico accept\" onclick=\"unlockClient('<?php echo $user["id"] ?>','<?php echo $user["firstname"]." ".$user["lastname"]?>','<?php echo $groupname ?>',<?php echo $user["id"] ?>)\"></a><a class=\"ico delete\" style=\"cursor:pointer\" onclick=\"rejectClient('<?php echo $user["id"] ?>','<?php echo $user["firstname"]." ".$user["lastname"]?>','<?php echo $groupname ?>',<?php echo $user["id"] ?>)\"></a>"
	}<?php echo $comma ?>
<?php
	} // for each user
	echo "];";
} // In case of any result

?>

window.addEvent('load',function(){


	
				
				mootable = new MooTable( 'test', {debug: false, height: '350px', headers: headers, sortable: true, useloading: false, resizable: false});
			
				mootable.loadData( data );
				});
		</script>
<?php
// If category is choosen

?>

<body id="case1">
<?php
if ($countUsers){
	if (!$_GET["userFrom"])  $_GET["userFrom"] = 1;
	if (!$userTo) $userTo = $countUsers;
?>

<?php
}
?>
<div id='test'></div>
</body>
</html>
