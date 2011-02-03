<?php
define('sAuthFile', 'sGUI');
define('sConfigPath',"../../../");
include("../../backend/php/check.php");
$result = new checkLogin();
$result = $result->checkUser();
if ($result!="SUCCESS"){
echo "FAIL";
	die();
}
// *****************
?>
<?php
if ($_REQUEST["artID"] && $_REQUEST["field"]){
	$_GET["id"] = intval($_REQUEST["artID"]);
	$_GET["state"] = intval($_REQUEST["value"]);
	
	
	switch ($_REQUEST["field"]){
		case "status":
			$updateOrder = mysql_query("
			UPDATE s_order SET status='{$_GET["state"]}'
			WHERE id={$_GET["id"]}
			");
			if (!$updateOrder){
				echo "UPDATE ERROR";
			}else {
				echo "OK";
			}
			break;
		case "cleared":
			$updateOrder = mysql_query("
			UPDATE s_order SET cleared='{$_GET["state"]}'
			WHERE id={$_GET["id"]}
			");
			if (!$updateOrder){
				echo "ERROR";
			}
			break;
	}
}else {
	echo "AUTH ERROR";
}
?>