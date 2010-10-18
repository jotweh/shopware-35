<?php
define('sAuthFile', 'sGUI');
define('sConfigPath',"../../../../");
include("../../../backend/php/check.php");
$result = new checkLogin();
$result = $result->checkUser();
if ($result!="SUCCESS"){
echo "
	<html><title>Time-Out</title><head></head>
	<script language=\"javascript\">
	parent.location.reload();
	</script>
	<body>Bitte loggen Sie sich neu ein!</body></html>
	";
	die();
}

if (!empty($_REQUEST["article"])){
	/*
	$_POST["json"] = stripslashes($_POST["json"]);
	$_POST["json"] = explode(",",$_POST["json"]);
	$_POST["json"] = str_replace("{","",$_POST["json"]);
	$_POST["json"] = str_replace("}","",$_POST["json"]);
	
	foreach ($_POST["json"] as $variable){
		$variable = explode(":",$variable);
		$variable[0] = str_replace("\"","",$variable[0]);
		$variable[1] = htmlspecialchars(str_replace("\"","",$variable[1]));
		$_GET[$variable[0]] = $variable[1];
	}
	*/
	$_GET["article"] = (int) $_REQUEST["article"];
	
	if ($_GET["article"]){
		$getProduct = mysql_query("
		SELECT name FROM s_articles, s_articles_esd WHERE s_articles_esd.id={$_GET["article"]}
		AND s_articles_esd.articleID=s_articles.id
		");
		if (!@mysql_num_rows($getProduct)){
			die("No article relation");
		}else {
			$title = "Artikel ".mysql_result($getProduct,0,"name")."";
		}
	}else {
		die("No article relation");
	}
	
	$title = utf8_encode($title);
	
}
?>
{
	"init": {
		"title": "<?php echo $title ?> - Seriennummern",
		"minwidth": "640",
		"minheight": "480",
		"content": "",
		"loader": "iframe",
		"url": "search_esd.php?id=<?php echo $_GET["article"] ?>"
	},
	"buttons": 
	[
		{
			"id": "step1",
			"title": "Suchen",
			"active": 1,
			"remotecall": "startSearch",
			"remoteattribute": ""
		}
	]
}
