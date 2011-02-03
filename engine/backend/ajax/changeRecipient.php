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
$_POST["id"] = intval($_POST["id"]);

if ($_POST["id"]){
	
	switch ($_POST["field"]){
		case "groupname":
			if ($_POST["value"]){
				$v = intval($_POST["value"]);
				$id = intval($_POST["id"]);
				$update = mysql_query("
				UPDATE s_campaigns_mailaddresses SET groupID = $v WHERE id = $id
				");
			}
			break;
		case "email":
			$v = mysql_real_escape_string($_POST["value"]);
			$id = intval($_POST["id"]);
			$update = mysql_query("
			UPDATE s_campaigns_mailaddresses SET email = '$v' WHERE id = $id
			");
			break;
	}
}else {
	
	// Insert entry
	if ($_POST["field"]=="email" && $_POST["value"]){
		$email = mysql_real_escape_string($_POST["value"]);
		$sql = "
		INSERT INTO s_campaigns_mailaddresses (email, groupID) VALUES ( '$email',1 )
		";
		
		$insert = mysql_query($sql);
		
		echo mysql_insert_id();
	}
	
	
}
?>