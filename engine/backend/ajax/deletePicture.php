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
echo $_GET["id"];
$getPictureInformation = mysql_query("
SELECT img,extension FROM s_articles_img WHERE id={$_GET["id"]}
");
$uploaddir = '../../../images/articles/';
if (@mysql_num_rows($getPictureInformation)){
	$imgInfo = mysql_fetch_array($getPictureInformation);
	// Build Unlinks
	 $queryGetSizes = mysql_query("
	 SELECT value FROM s_core_config WHERE name='sIMAGESIZES'
	 ");	   
	 if (!@mysql_num_rows($queryGetSizes)){

	 	die("Could not load sIMAGESIZES");
	 
	 }
	 // Create thumbs
	 $queryGetSizes = mysql_result($queryGetSizes,0,"value");
	 $queryGetSizes = explode(";",$queryGetSizes);
	 // Delete main-picture
	 unlink($uploaddir.$imgInfo["img"].".".$imgInfo["extension"]);
	foreach ($queryGetSizes as $size){
		$imgSizes = explode(":",$size);
		$suffix  = $imgSizes[2];
		unlink($uploaddir.$imgInfo["img"]."_$suffix.".$imgInfo["extension"]);
	}
	$deletePicture = mysql_query("DELETE FROM s_articles_img WHERE id={$_GET["id"]}");

}
?>