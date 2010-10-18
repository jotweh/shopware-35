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
require_once("json.php");
$json = new Services_JSON();
?>
<?php

// Fetch-Data from categories
$_REQUEST["node"] = intval($_REQUEST["node"]);

if (!$_REQUEST["node"]){
	$_REQUEST["node"] = 1;
}

$nodes = array();
$_POST["start"] = intval($_POST["start"]);
$_POST["limit"] = intval($_POST["limit"]);
$_POST["id"] = intval($_POST["id"]);

if ($_POST["pagingID"]) $_POST["id"] = $_POST["pagingID"];
if (!$_POST["id"]) $_POST["id"] = 3;
if (!$_POST["limit"]) $_POST["limit"] = 25;

if ($_POST["sort"]=="supplier") $_POST["sort"] = "supplierName";

if (!$_POST["sort"] || $_POST["sort"]=="lastpost") $_POST["sort"] = "id";
if (!$_POST["dir"]) $_POST["dir"] = "ASC";

if ($_POST["search"]){
	if (strlen($_POST["search"])>1){
		$search = "%".mysql_real_escape_string($_POST["search"])."%";
	}else {
		$search = mysql_real_escape_string($_POST["search"])."%";
	}
	$searchSQL = "
	 AND 
	(
		s_articles.name LIKE '$search'
	OR 
		s_articles_vote.headline LIKE '$search'
	OR
		s_articles_vote.name LIKE '$search'
	) 
	";
}


$sql = "
SELECT s_articles_vote.id AS id,s_articles_vote.active AS active, s_articles.name AS articleName, s_articles_vote.name AS name, headline, comment, s_articles_vote.datum AS datum, points 
FROM s_articles_vote, s_articles
WHERE
s_articles_vote.articleID=s_articles.id
$searchSQL
ORDER BY {$_POST["sort"]} {$_POST["dir"]}
";

//echo $sql;


//echo $sql;

$resultCountArticles = mysql_query($sql);
$nodes["totalCount"] = @mysql_num_rows($resultCountArticles);

$sql = "
SELECT s_articles_vote.id AS id,s_articles_vote.active AS active, s_articles.name AS articleName, s_articles_vote.name AS name, headline, comment, s_articles_vote.datum AS datum, points 
FROM s_articles_vote, s_articles
WHERE
s_articles_vote.articleID=s_articles.id
$searchSQL
ORDER BY {$_POST["sort"]} {$_POST["dir"]} LIMIT {$_POST["start"]},{$_POST["limit"]}";
$getVotes = mysql_query($sql);

//echo $sql;

if (@mysql_num_rows($getVotes)){
	while ($vote=mysql_fetch_assoc($getVotes)){
		$vote["articleName"] = utf8_encode($vote["articleName"]);
		$vote["name"] = utf8_encode($vote["name"]);
		$vote["headline"] = utf8_encode($vote["headline"]);
		$vote["comment"] = nl2br(stripslashes(utf8_encode($vote["comment"])));
		
		$nodes["votes"][] = $vote;
	}
}else {
	$nodes["votes"] = array();
	$nodes["totalCount"] = 0;
}
echo $json->encode($nodes);

?>