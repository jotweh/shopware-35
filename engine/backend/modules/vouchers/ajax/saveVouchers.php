<?php
define('sAuthFile', 'sGUI');
define('sConfigPath',"../../../../../");
include("../../../../backend/php/check.php");
$result = new checkLogin();
$result = $result->checkUser();
if ($result!="SUCCESS"){
	die();
}

function formatdate ($date)
{
	if (!$date) return "";
	$date = explode(".",$date);
	return $date[2]."-".$date[1]."-".$date[0];
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


//Einlösbar je Kunde mindestens 1
if($_REQUEST["modus"] == 0 && empty($_REQUEST["numorder"]))
{
	$_REQUEST["numorder"] = 1;
}


$upset = array();
//************************* Fill Upset ****************************
if(!empty($_REQUEST["feedID"])&&is_numeric($_REQUEST["feedID"]))
{
	$feedID = (int)$_REQUEST["feedID"];
	//*********************** Validate the values for the updatestatement
	$upset[] = "description=".((empty($_REQUEST["description"])) ? "''" : "'".utf8_decode(mysql_real_escape_string(trim($_REQUEST["description"])))."'");
	$upset[] = "vouchercode=".((empty($_REQUEST["vouchercode"])) ? "''" : "'".utf8_decode(mysql_real_escape_string(trim($_REQUEST["vouchercode"])))."'");
	$upset[] = "numberofunits=".((empty($_REQUEST["numberofunits"])||!is_numeric($_REQUEST["numberofunits"])) ? 0 : (int) $_REQUEST["numberofunits"]);
//	$upset[] = "value=".((empty($_REQUEST["value"])) ? "'NULL'" :  "'".(int) $_REQUEST["value"]."'"); 
	$upset[] = "value=".((empty($_REQUEST["value"])||!is_numeric($_REQUEST["value"])) ? 0 : (double) $_REQUEST["value"]);
//	$upset[] = "minimumcharge=".((empty($_REQUEST["minimumcharge"])) ? "'NULL'" :  "'".(int) $_REQUEST["minimumcharge"]."'");
	$upset[] = "minimumcharge=".((empty($_REQUEST["minimumcharge"])||!is_numeric($_REQUEST["minimumcharge"])) ? 0 : (double) $_REQUEST["minimumcharge"]);
	$upset[] = "shippingfree=".((empty($_REQUEST["shippingfree"])) ? "'0'" : "'1'");
//	$upset[] = "bindtosupplier=".((empty($_REQUEST["bindtosupplier"])) ? "''" :  "'".(int) $_REQUEST["bindtosupplier"]."'");
	$upset[] = "bindtosupplier=".((empty($_REQUEST["bindtosupplier"])||!is_numeric($_REQUEST["bindtosupplier"])) ? 0 : (int) $_REQUEST["bindtosupplier"]);
	$upset[] = "valid_from=".((empty($_REQUEST["valid_from"])) ? "''" :  "'".formatdate($_REQUEST['valid_from'])."'");
	$upset[] = "valid_to=".((empty($_REQUEST["valid_to"])) ? "''" :  "'".formatdate($_REQUEST['valid_to'])."'");
//	$upset[] = "ordercode=".((empty($_REQUEST["ordercode"])) ? "'NULL'" :  "'".utf8_decode(mysql_real_escape_string(trim($_REQUEST["ordercode"])))."'");
	$upset[] = "ordercode=".((empty($_REQUEST["ordercode"])) ? "''" : "'".utf8_decode(mysql_real_escape_string(trim($_REQUEST["ordercode"])))."'");
	$upset[] = "restrictarticles=".((empty($_REQUEST["restrictarticles"])) ? "''" :  "'".utf8_decode(mysql_real_escape_string(trim($_REQUEST["restrictarticles"])))."'");
//	$upset[] = "modus=".((empty($_REQUEST["modus"])) ? 0 : "'".$_REQUEST['modus']."'");
	$upset[] = "modus=".((empty($_REQUEST["modus"])||!is_numeric($_REQUEST["modus"])) ? 0 : (int) $_REQUEST["modus"]);
//	$upset[] = "percental=".((empty($_REQUEST["percental"])) ? 0 : "'".$_REQUEST['percental']."'");
	$upset[] = "percental=".((empty($_REQUEST["percental"])||!is_numeric($_REQUEST["percental"])) ? 0 : (int) $_REQUEST["percental"]);
//	$upset[] = "numorder=".((empty($_REQUEST["numorder"])) ? 0 :  "'".(int) $_REQUEST["numorder"]."'");
	$upset[] = "numorder=".((empty($_REQUEST["numorder"])||!is_numeric($_REQUEST["numorder"])) ? 0 : (int) $_REQUEST["numorder"]);
//	$upset[] = "customergroup=".((empty($_REQUEST["customergroup"])) ? "''" :  "'".$_REQUEST["customergroup"]."'");
	$upset[] = "customergroup=".((empty($_REQUEST["customergroup"])) ? "''" : "'".utf8_decode(mysql_real_escape_string(trim($_REQUEST["customergroup"])))."'");
	$upset[] = "strict=".((empty($_REQUEST["strict"])) ? "'0'" : "'1'");
	$upset[] = "subshopID=".((empty($_REQUEST["subshop"])) ? "''" : "'".utf8_decode(mysql_real_escape_string(trim($_REQUEST["subshop"])))."'");
	$upset[] = "taxconfig=".((empty($_REQUEST["taxConfiguration"])) ? "''" : "'".utf8_decode(mysql_real_escape_string(trim($_REQUEST["taxConfiguration"])))."'");
		
//	
	$upset = implode(",",$upset);
	$sql = "UPDATE s_emarketing_vouchers SET $upset WHERE id=$feedID";
	
	mysql_query($sql);

}

//String $upset[] = (empty($_REQUEST["description"])) ? "''" : "'".utf8_decode(mysql_real_escape_string(trim($_REQUEST["description"])))."'";
//Double $upset[] = (empty($_REQUEST["minimumcharge"])||!is_numeric($_REQUEST["minimumcharge"])) ? 0 : (double) $_REQUEST["minimumcharge"];
else {
	$upset[] = (empty($_REQUEST["description"])) ? "''" : "'".utf8_decode(mysql_real_escape_string(trim($_REQUEST["description"])))."'";
	$upset[] = (empty($_REQUEST["vouchercode"])) ? "''" : "'".utf8_decode(mysql_real_escape_string(trim($_REQUEST["vouchercode"])))."'";
//	$upset[] = (empty($_REQUEST["numberofunits"])) ? "'NULL'" :  "'".(int) $_REQUEST["numberofunits"]."'";  
	$upset[] = (empty($_REQUEST["numberofunits"])||!is_numeric($_REQUEST["numberofunits"])) ? 0 : (int) $_REQUEST["numberofunits"];
//	$upset[] = (empty($_REQUEST["value"])) ? "'NULL'" :  "'".(int) $_REQUEST["value"]."'"; 
	$upset[] = (empty($_REQUEST["value"])||!is_numeric($_REQUEST["value"])) ? 0 : (double) $_REQUEST["value"];
//	$upset[] = (empty($_REQUEST["minimumcharge"])) ? "'NULL'" :  "'".(int) $_REQUEST["minimumcharge"]."'";
	$upset[] = (empty($_REQUEST["minimumcharge"])||!is_numeric($_REQUEST["minimumcharge"])) ? 0 : (double) $_REQUEST["minimumcharge"];
	$upset[] = (empty($_REQUEST["shippingfree"])) ? 0 : 1;
	
//	$upset[] = (empty($_REQUEST["bindtosupplier"])) ? "''" :  "'".(int) $_REQUEST["bindtosupplier"]."'";
	$upset[] = (empty($_REQUEST["bindtosupplier"])||!is_numeric($_REQUEST["bindtosupplier"])) ? 0 : (int) $_REQUEST["bindtosupplier"];
	$upset[] = (empty($_REQUEST["valid_from"])) ? "''" :  "'".formatdate($_REQUEST['valid_from'])."'";
	$upset[] = (empty($_REQUEST["valid_to"])) ? "''" :  "'".formatdate($_REQUEST['valid_to'])."'";
//	$upset[] = (empty($_REQUEST["ordercode"])) ? "'NULL'" :  "'".utf8_decode(mysql_real_escape_string(trim($_REQUEST["ordercode"])))."'";
	$upset[] = (empty($_REQUEST["ordercode"])) ? "''" : "'".utf8_decode(mysql_real_escape_string(trim($_REQUEST["ordercode"])))."'";
	$upset[] = (empty($_REQUEST["restrictarticles"])) ? "''" :  "'".utf8_decode(mysql_real_escape_string(trim($_REQUEST["restrictarticles"])))."'";
//	$upset[] = (empty($_REQUEST["modus"])) ? 0 : "'".$_REQUEST['modus']."'";
	$upset[] = (empty($_REQUEST["modus"])||!is_numeric($_REQUEST["modus"])) ? 0 : (int) $_REQUEST["modus"];
//	$upset[] = (empty($_REQUEST["percental"])) ? 0 : "'".$_REQUEST['percental']."'";
	$upset[] = (empty($_REQUEST["percental"])||!is_numeric($_REQUEST["percental"])) ? 0 : (int) $_REQUEST["percental"];
//	$upset[] = (empty($_REQUEST["numorder"])) ? 0 :  "'".(int) $_REQUEST["numorder"]."'";
	$upset[] = (empty($_REQUEST["numorder"])||!is_numeric($_REQUEST["numorder"])) ? 0 : (int) $_REQUEST["numorder"];
//	$upset[] = (empty($_REQUEST["customergroup"])) ? "''" :  "'".$_REQUEST["customergroup"]."'";
	$upset[] = (empty($_REQUEST["customergroup"])) ? "''" : "'".utf8_decode(mysql_real_escape_string(trim($_REQUEST["customergroup"])))."'";
	$upset[] = (empty($_REQUEST["strict"])) ? 0 : 1;
	$upset[] = (empty($_REQUEST["subshop"])) ? "''" : "'".utf8_decode(mysql_real_escape_string(trim($_REQUEST["subshop"])))."'";
	$upset[] = (empty($_REQUEST["taxConfiguration"])) ? "''" : "'".utf8_decode(mysql_real_escape_string(trim($_REQUEST["taxConfiguration"])))."'";
		
	$upset = implode(",",$upset);
	$sql = "INSERT INTO `s_emarketing_vouchers` (
			`description` ,
			`vouchercode` ,
			`numberofunits` ,
			`value` ,
			`minimumcharge` ,
			`shippingfree` ,
			`bindtosupplier` ,
			`valid_from` ,
			`valid_to` ,
			`ordercode` ,
			`restrictarticles` ,
			`modus` ,
			`percental` ,
			`numorder` ,
			`customergroup`,
			`strict`,
			`subshopID`,
			`taxconfig`
			)
			VALUES (
			$upset
			)";
	mysql_query($sql);
	$feedID = mysql_insert_id();
}


		require_once("json.php");
		$json = new Services_JSON();
		$data = array(
		'feedID' => $feedID,
		'success'=>true);
		echo $json->encode($data);// return the new $feedID 


				

?>