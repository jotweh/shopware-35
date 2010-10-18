<?php
define('sAuthFile', 'sGUI');
define('sConfigPath',"../../../../");
include("../../../backend/php/check.php");
$result = new checkLogin();
$result = $result->checkUser();
if ($result!="SUCCESS"){
	die("FAIL");
}
if(isset($_REQUEST['pdf']))
	$_REQUEST['id'] = $_REQUEST['pdf'];
if (empty($_REQUEST['id']))
{
	die("FAIL");
}
$_REQUEST['id'] = basename($_REQUEST["id"]);
if (!is_file("../../../../files/documents/{$_REQUEST['id']}.pdf")){
	die("FAIL");
}
// Updated 29.10.2008 sth
header("Cache-Control: public");
header("Content-Description: File Transfer");
header('Content-disposition: attachment; filename='.basename("../../../../files/documents/{$_REQUEST['id']}.pdf"));
header("Content-Type: application/pdf");
header("Content-Transfer-Encoding: binary");
header('Content-Length: '. filesize("../../../../files/documents/{$_REQUEST['id']}.pdf")); 
echo readfile("../../../../files/documents/{$_REQUEST['id']}.pdf");
?>