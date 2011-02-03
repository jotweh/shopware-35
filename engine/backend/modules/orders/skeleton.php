<?php
define('sAuthFile', 'sSUMMARY');
define('sConfigPath',"../../../../");
include("../../../backend/php/check.php");
$result = new checkLogin();
$result = $result->checkUser();
if ($result!="SUCCESS"){
	die();
}

$orderId =  intval($_REQUEST["id"]);
// Query for user details
$queryOrderDetails = mysql_query("
SELECT * FROM s_order WHERE id=$orderId
");

if (!mysql_num_rows($queryOrderDetails)){
	$title = $sLang["orders"]["skeleton_error_order_not_found"];
}else {
	$title = utf8_encode($sLang["orders"]["skeleton_order"]." ".sprintf("%08d", mysql_result($queryOrderDetails,0,"ordernumber")));
}

?>
{
	"init": {
		"title": "<?php echo $title ?>",
		"minwidth": "800",
		"minheight": "610",
		"width": 800,
		"height": 610,
		"content": "",
		"loader": "iframe",
		"url": "index.php?id=<?php echo $orderId ?>",
		"help":"52"
	}
}