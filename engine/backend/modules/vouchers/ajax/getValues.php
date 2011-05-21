<?php
define('sAuthFile', 'sGUI');
define('sConfigPath',"../../../../../");
include("../../../../backend/php/check.php");
$result = new checkLogin();
$result = $result->checkUser();
if ($result!="SUCCESS"){
	die();
}

require_once("json.php");
$json = new Services_JSON();



$feedID = (int)$_REQUEST["feedID"];
switch ($_REQUEST["name"])
{
	//For the Customergroupdropdown
	case "customergroup":
		$sql = "
			SELECT id, description as name
			FROM s_core_customergroups
			ORDER BY name
		";
		break;
	case "subshop":
		$sql = "
			SELECT id, name
			FROM s_core_multilanguage
			ORDER BY name
		";
		break;
		
	//For the Supplierdropdown	
	case "supplier":
		$sql = "
			SELECT id, name FROM s_articles_supplier ORDER BY name ASC
		";
		break;
		
	//To check out the ordercode on the fly	
	case "ordercode":
		$value = (empty($_REQUEST["value"])) ? "" :  utf8_decode(mysql_real_escape_string(trim($_REQUEST["value"])));
		$id = (int)$_REQUEST["id"];
		
		if(isset($value)) {
			if($id != 0) {
					$sql = "SELECT ordercode FROM s_emarketing_vouchers	WHERE ordercode = '$value' AND ID != '$id'";
			}
			else{
					$sql = "SELECT ordercode FROM s_emarketing_vouchers	WHERE ordercode = '$value'";
			}
		}
		break;
		
	//To check out the vouchercode on the fly	
	case "vouchercode":
		$value = (empty($_REQUEST["value"])) ? "" :  utf8_decode(mysql_real_escape_string(trim($_REQUEST["value"])));
		$id = (int)$_REQUEST["id"];
		
		if(isset($value)) {
			if($id != 0) {
					$sql = "SELECT vouchercode FROM s_emarketing_vouchers	WHERE vouchercode = '$value' AND ID != '$id'";
			}
			else{
					$sql = "SELECT vouchercode FROM s_emarketing_vouchers	WHERE vouchercode = '$value'";
			}
		}
		break;
		
	//The Vouchercodelist to show in the grid below	
	case "voucherList":
		if (!$_POST["sort"]) $_POST["sort"] = "cashed";
		if (!$_POST["dir"]) $_POST["dir"] = "DESC";
		$sort = mysql_real_escape_string($_POST["sort"]);
		$dir = mysql_real_escape_string($_POST["dir"]);
	
		$start = intval($_REQUEST['start']);
		$limit = intval($_REQUEST['limit']);
		
		$searchSQL = "";
		if ($_POST["search"]) {
			if (strlen($_POST["search"])>1){
				$search = "%".mysql_real_escape_string(utf8_decode($_POST["search"]))."%";
			}else {
				$search = mysql_real_escape_string(utf8_decode($_POST["search"]))."%";
			}
			$searchSQL = "
			 AND (
				code LIKE '$search'
			OR
				customernumber LIKE '$search'
			OR
				firstname LIKE '$search'
			OR
				lastname LIKE '$search'
			)
			";
		}
		
		
		
		if(isset($feedID) && $feedID != 0 ) {
			$sql = "
			SELECT code, customernumber, firstname, lastname, cashed 
				FROM s_emarketing_voucher_codes
				LEFT JOIN s_user_billingaddress ON (s_user_billingaddress.userID = s_emarketing_voucher_codes.userID)
				WHERE s_emarketing_voucher_codes.voucherID = $feedID
				{$searchSQL}
			    ORDER BY {$sort} {$dir}
				LIMIT {$start}, {$limit}
				";
		}
		break;
	case "tax":
		$tax[] = array("id"=>"default","name"=>"Standard");
		$tax[] = array("id"=>"auto","name"=>"Auto-Ermittlung");
		$query = mysql_query("
		SELECT * FROM s_core_tax ORDER BY ID ASC
		");
		while ($result = mysql_fetch_assoc($query)){
			$tax[] = array("id"=>"fix_"+$result["id"],"name"=>$result["description"]);
		}
		$tax[] = array("id"=>"none","name"=>"Steuerfrei");
		echo  $json->encode(array("articles"=>$tax,"count"=>count($tax)));
		exit;
	default:
		exit();
}

$result = mysql_query($sql);
$nodes = array();
if ($result&&mysql_num_rows($result)){
	while ($row = mysql_fetch_assoc($result))
	{
		$row["name"] = utf8_encode($row["name"]);
		$row["firstname"] = utf8_encode($row["firstname"]);
		$row["lastname"] = utf8_encode($row["lastname"]);
		if(!empty($row["count"]))
			$row["name"] .= " (".$row["count"].")";
		$nodes[] = $row;
	}
}

switch ($_REQUEST["name"]) {
	case "voucherList":
		if(isset($feedID) && $feedID != 0 ) {
			$sql = "SELECT code FROM s_emarketing_voucher_codes
				WHERE voucherID = $feedID
				";
		}
		echo  $json->encode(array("articles"=>$nodes,"count"=>mysql_num_rows(mysql_query($sql))));
		break;
	default:
		echo  $json->encode(array("articles"=>$nodes,"count"=>count($nodes)));
		break;
}


?>