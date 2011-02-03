<?php
session_start();
$_SESSION["sUsername"] = addslashes(htmlspecialchars($_GET["sUsername"]));
$_SESSION["sPassword"] = addslashes(htmlspecialchars($_GET["sPassword"]));

define('sAuthFile', 'sGUI');
define('sConfigPath',"../../../../");
include("../../../backend/php/check.php");
$result = new checkLogin();
$result->sSession = addslashes(htmlspecialchars($_GET["sSession"]));
$result = $result->checkUser();

if ($result!="SUCCESS"){
	die();
}

$_GET["article"] = intval($_GET["article"]);
if (!$_GET["article"]) die("No article reference");


$uploaddir = '../../../../files/'.$sCore->sCONFIG['sESDKEY'].'/';

if (!is_dir($uploaddir)){
	die("$uploaddir it´s not a directory");
}

if (count($_FILES))
{
	$forbidden = array("php","php5","php4","phtml","cgi","pl");
	$filename = $_FILES['Filedata']['name'];
	$filename = strtolower($filename);
	
	// Datei-Endung
	$filename = explode(".", $filename);
	$filenameext = $filename[count($filename)-1];
	
	if (in_array($filenameext,$forbidden)) die("Not permitted");
	$filename = $filename[0]."-".rand(0,999999).".".$filenameext;
	
	$uploadfile = $uploaddir . basename($filename);
	
	if (move_uploaded_file($_FILES['Filedata']['tmp_name'], $uploadfile)) {
		// Refresh download
		$update = mysql_query("
		UPDATE s_articles_esd SET file='$filename' WHERE id={$_GET["esd"]}
		");
		chmod($uploadfile,0644);
		echo "SUCCESS";
	} else {
	   echo "SUCCESS";
	}
}
?>