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
$_GET["id"] = str_replace("thumb","",$_GET["id"]);
$_GET["id"] = intval($_GET["id"]);


$getPictureInformation = mysql_query("
SELECT articleID FROM s_articles_img WHERE id={$_GET["id"]}
");


if (@mysql_num_rows($getPictureInformation)){

	$article = mysql_result($getPictureInformation,0,"articleID");
	$description = mysql_real_escape_string(urldecode(utf8_decode($_REQUEST["title"])));
	$relations = mysql_real_escape_string(urldecode(utf8_decode($_REQUEST["relations"])));
	
	
	$sql = "
	UPDATE s_articles_img SET 
	description = '$description',
	relations = '$relations'
	WHERE id={$_GET["id"]}
	";
	$sCore->sDeletePartialCache("article",$article);
	$updateNewCover = mysql_query($sql);
		
	
	if ($updateNewCover){
		echo "ok";
	}else {
		echo "err";
	}
}
?>