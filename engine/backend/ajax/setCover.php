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
SELECT articleID FROM s_articles_img WHERE id={$_GET["id"]}
");



if (@mysql_num_rows($getPictureInformation)){
	$imgInfo = mysql_fetch_array($getPictureInformation);
	// Reset all pictures
	$updateImages = mysql_query("
	UPDATE s_articles_img SET main=2 WHERE articleID={$imgInfo["articleID"]}
	");
	if ($updateImages){
		$updateNewCover = mysql_query("
		UPDATE s_articles_img SET main=1 WHERE id={$_GET["id"]}
		");
		echo "Change success";
	}else {
		echo "Change failure";
	}

}
?>