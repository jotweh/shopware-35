<?php
define('sAuthFile', 'sSUMMARY');
define('sConfigPath',"../../../../");
include("../../../backend/php/check.php");
$result = new checkLogin();
$result = $result->checkUser();
if ($result!="SUCCESS"){
	//echo $result;
	//header("location: auth.php");
	die();
}

//Editierung der Bestelldaten
if(!empty($_REQUEST['orderId']))
{
	$orderId = intval($_REQUEST['orderId']);
	
	$queryUserDetails = mysql_query("
	SELECT firstname, lastname FROM s_order_billingaddress WHERE orderID='{$orderId}'
	");
	$fn = mysql_result($queryUserDetails,0,"firstname");
	$ln = mysql_result($queryUserDetails,0,"lastname");
	
	$title = sprintf("%s %s (Bearbeitung der Bestelldaten)", $fn, $ln);
	?>
	
{
	"init": {
		"title": "<?php echo $title ?>",
		"minwidth": "800",
		"minheight": "600",
		"content": "",
		"loader": "iframe",
		"url": "main_order.php?orderId=<?php echo $orderId ?>",
		"help":"51"
	}
}

<?php	
}else{
	
	if (!empty($_REQUEST["id"])) $_REQUEST["user"] = $_REQUEST["id"];
	$userId =  intval($_REQUEST["user"]);
	
	// Query for user details
	$queryUserDetails = mysql_query("
	SELECT firstname, lastname FROM s_user_billingaddress WHERE userID=$userId
	");
	
	if (!mysql_num_rows($queryUserDetails) || empty($userId)){
		$title = "Benutzer nicht gefunden";
	}else {
		$title = $sLang["userdetails"]["skeleton_customer_login"]." ".mysql_result($queryUserDetails,0,"firstname")." ".mysql_result($queryUserDetails,0,"lastname");
	}
	
	?>
	{
		"init": {
			"title": "<?php echo $title ?>",
			"minwidth": "800",
			"minheight": "600",
			"content": "",
			"loader": "iframe",
			"url": "details.php",
			"help":"51"
		},
		"tabs": 
		[
			{
				"id": "step1",
				"title": "<?php echo $sLang["userdetails"]["skeleton_KeyData"] ?>",
				"active": 1,
				"content": "<div id='contentFrame' class='contentFrame' src='http://<?php echo $sCore->sCONFIG['sBASEPATH']?>/engine/backend/modules/userdetails/main.php?id=<?php echo $userId ?>'>",
				"show":"save",
				"help":"51"
			}
			,
			{
				"id": "step2",
				"title": "<?php echo $sLang["userdetails"]["skeleton_orders"] ?>",
				"active": 1,
				"content": "<div id='contentFrame' class='contentFrame' src='http://<?php echo $sCore->sCONFIG['sBASEPATH']?>/engine/backend/modules/userdetails/orders.php?id=<?php echo $userId ?>'>",
				"hide":"save",
				"help":"51"
			}
			,
			{
				"id": "step3",
				"title": "<?php echo $sLang["userdetails"]["skeleton_Turnover"] ?>",
				"active": 1,
				"content": "<div id='contentFrame' class='contentFrame' src='http://<?php echo $sCore->sCONFIG['sBASEPATH']?>/engine/backend/modules/userdetails/statistics.php?id=<?php echo $userId ?>'>",
				"hide":"save",
				"help":"51"
			}
		],
		"buttons": 
		[
			{
				"id": "save",
				"bind": "step1",
				"title": "<?php echo $sLang["userdetails"]["skeleton_save_changes"] ?>",
				"active": 1,
				"remotecall": "save",
				"remoteattribute": ""
			}
		]
		
	}
<?php	
}
?>
