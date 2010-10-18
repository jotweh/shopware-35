<?php
define('sAuthFile', 'sGUI');
define('sConfigPath',"../../../");
include("../../backend/php/check.php");
$result = new checkLogin();
$result = $result->checkUser();
if ($result!="SUCCESS"){
	die("FAIL");
}

require_once("../../backend/ajax/json.php");
$json = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);

if (!empty($_REQUEST["id"])&&!empty($_REQUEST["field"])){
	$orderdetailsID = intval($_REQUEST["id"]);
	$value = mysql_real_escape_string(trim(utf8_decode($_REQUEST["value"])));
	$field = utf8_decode($_REQUEST["field"]);
	
	if(strpos($field,"attr")===0)
	{
		$sql = "SELECT orderdetailsID FROM s_order_attributes WHERE orderdetailsID=$orderdetailsID";
		$result = mysql_query($sql);
		if($result&&mysql_num_rows($result))
		{
			$sql = "
				UPDATE s_order_attributes SET `$field`='$value'
				WHERE orderdetailsID=$orderdetailsID
			";
		}
		else 
		{
			$sql = "
				INSERT INTO s_order_attributes SET `$field`='$value', orderdetailsID=$orderdetailsID
			";
		}
	}
	else 
	{
		$sql = "
			UPDATE s_order_details SET `$field`='$value'
			WHERE id=$orderdetailsID
		";
	}
	$result = mysql_query($sql);
	if (!$result){
		echo "UPDATE ERROR";
	}else {
		echo "OK";
	}
}
elseif(!empty($_REQUEST["rec"]))
{
	$data = $json->decode($_REQUEST["rec"]);
	$orderdetailsID = intval($data["id"]);
	$value1 = mysql_real_escape_string(trim(utf8_decode($data["attr1"])));
	$value2 = mysql_real_escape_string(trim(utf8_decode($data["attr2"])));
	
	$sql = "
		REPLACE INTO s_order_attributes SET `attr1`='$value1', `attr2`='$value2', orderdetailsID=$orderdetailsID
	";
	$result = mysql_query($sql);
	if (!$result){
		echo "UPDATE ERROR";
	}else {
		echo "OK";
	}
}else {
	echo "AUTH ERROR";
}
?>