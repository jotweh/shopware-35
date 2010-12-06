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


$id = intval($_REQUEST["id"]);
$key = mysql_real_escape_string(utf8_decode($_REQUEST["key"]));
$language = mysql_real_escape_string(utf8_decode($_REQUEST["language"]));
$object = mysql_real_escape_string(utf8_decode($_REQUEST["object"]));
$secondkey = mysql_real_escape_string(utf8_decode($_REQUEST["secondkey"]));

$getObject = mysql_query("
	SELECT * FROM s_core_translations
	WHERE
		objecttype = '$object'
	AND
		objectkey = $id
	AND
		objectlanguage  = '$language'
	");

if (@mysql_num_rows($getObject)){
	$object = @mysql_fetch_array($getObject);
	$object = unserialize($object["objectdata"]);
	
	if (!$secondkey){
		$value = $object[$key];
	}else {
		$value = $object[$secondkey][$key];
	}
	/*$value = nl2br($object[$key]);
	$value = str_replace("\n","",$value);
	$value = str_replace("\r","",$value);
	*/
	if(function_exists('mb_convert_encoding')) {
		$value = mb_convert_encoding($value, 'UTF-8', 'HTML-ENTITIES');
	}
} else {
	$value = "";
}
echo $value;
?>
