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
/*
sTranslationsId=162&sTranslationsKey=txtArtikel&sTranslationsObject=article&sTranslationsLanguage=en

&sTransationsValue=Hallo%20Welt
*/

if ($_POST["sTranslationsId"] 
&& $_POST["sTranslationsKey"] 
&& $_POST["sTranslationsObject"] 
&& $_POST["sTranslationsLanguage"]
){
	if (!$_POST["sTransationsValue"]){
		// No translation given ;(
	}
	$key = intval($_POST["sTranslationsId"]);
	
	$_POST["sTranslationsKey"] = mysql_real_escape_string(utf8_decode($_POST["sTranslationsKey"]));
	$_POST["sTranslationsObject"] = mysql_real_escape_string(utf8_decode($_POST["sTranslationsObject"])); 
	$_POST["sTranslationsLanguage"] = mysql_real_escape_string(utf8_decode($_POST["sTranslationsLanguage"]));
	$_POST["sTransationsValue"] = utf8_decode($_POST["sTransationsValue"]);
	// Get available data
	$getObject = mysql_query("
		SELECT * FROM s_core_translations
		WHERE
			objecttype = '{$_POST["sTranslationsObject"]}'
		AND
			objectkey = $key
		AND
			objectlanguage  = '{$_POST["sTranslationsLanguage"]}'
	");
	
	if (@mysql_num_rows($getObject)){
		// Update
		$object = unserialize(mysql_result($getObject,0,"objectdata"));
		
		if ($_POST["sTranslationsKey2"]){
			$object[$_POST["sTranslationsKey2"]][$_POST["sTranslationsKey"]] = $_POST["sTransationsValue"];
		}else {
			$object[$_POST["sTranslationsKey"]] = $_POST["sTransationsValue"];
		}
		$object = mysql_real_escape_string(serialize($object));
		
		$insertObject = mysql_query("
		UPDATE s_core_translations
		SET
			objectdata = '".$object."'
		WHERE
			objecttype = '{$_POST["sTranslationsObject"]}'
		AND
			objectkey = '$key'
		AND
			objectlanguage  = '{$_POST["sTranslationsLanguage"]}'
		");		
	}else {
		// First time insert
		if ($_POST["sTranslationsKey2"]){
			$object[$_POST["sTranslationsKey2"]] = array($_POST["sTranslationsKey"]=>$_POST["sTransationsValue"]);
		}else {
			$object = array($_POST["sTranslationsKey"]=>$_POST["sTransationsValue"]);
		}
		$object = mysql_real_escape_string(serialize($object));
		$insertObject = mysql_query("
		INSERT INTO s_core_translations
		(objecttype,
		objectdata,
		objectkey,
		objectlanguage)
		VALUES (
		'{$_POST["sTranslationsObject"]}',
		'".$object."',
		'$key',
		'{$_POST["sTranslationsLanguage"]}'
		)
		");
	}
	
	if ($insertObject){
		echo "SUCCESS";
	}else {
		//echo mysql_error();
		echo "FAILURE";
	}
}
?>