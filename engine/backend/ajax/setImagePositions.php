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

$positions = mysql_real_escape_string($_POST["positions"]);

$getPictureInformation = mysql_query("
SELECT articleID FROM s_articles_img WHERE articleID={$_GET["id"]}
");



if (@mysql_num_rows($getPictureInformation) && $positions){
	$positions = explode("#",$positions);
	$pos = 1;
	foreach ($positions as $position){
		if (intval($position)){
			$sql = "
			UPDATE s_articles_img SET position = $pos
			WHERE id = $position
			";
			
			$updateQuery = mysql_query($sql);
			$pos++;
		}
	}
}else {
	
	//echo "{$_GET["id"]} not found";
}
?>