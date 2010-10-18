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
require_once("json.php");
$json = new Services_JSON();
$nodes = array();

if ($_POST["startDate"] && $_POST["endDate"]){
	$filterDate = 
	"
	WHERE (TO_DAYS(datum) >= TO_DAYS('{$_POST["startDate"]}') AND TO_DAYS(datum) <= TO_DAYS('{$_POST["endDate"]}'))
	";
}


$_POST["start"] = intval($_POST["start"]);
$_POST["limit"] = intval($_POST["limit"]);
if (!$_POST["limit"]) $_POST["limit"] = 25;
if (!$_POST["sort"] || $_POST["sort"]=="lastpost") $_POST["sort"] = "ssvdate";
if (!$_POST["dir"]) $_POST["dir"] = "DESC";


if ($_REQUEST["return"]=="articles"){
	if ($_POST["startDate"] && $_POST["endDate"]){
		$filterDate = 
		"
		AND (TO_DAYS(datum) >= TO_DAYS('{$_POST["startDate"]}') AND TO_DAYS(datum) <= TO_DAYS('{$_POST["endDate"]}'))
		";
	}
	$sql = "SELECT COUNT(articlename) AS quantity, ordernumber, articlename
			FROM s_order_basket
			WHERE
				modus = 0
				$filterDate
			GROUP BY ordernumber
			ORDER BY quantity DESC
			LIMIT 25
			";
	$getArticles = mysql_query($sql);

	if (@mysql_num_rows($getArticles)){
		$nodes["totalCount"] = mysql_num_rows($getArticles);
		
		while ($article = mysql_fetch_assoc($getArticles)){
			$article["ordernumber"] = utf8_encode($article["ordernumber"]);
			$article["articlename"] = utf8_encode($article["articlename"]);
			$nodes["articles"][] = $article;
		}
	}else {
		$nodes["articles"] = array();
		$nodes["totalCount"] = 0;
	}
	echo $json->encode($nodes);
	exit;
}elseif ($_REQUEST["return"]=="pages"){
	if ($_POST["startDate"] && $_POST["endDate"]){
		$filterDate = 
		"
		AND (TO_DAYS(datum) >= TO_DAYS('{$_POST["startDate"]}') AND TO_DAYS(datum) <= TO_DAYS('{$_POST["endDate"]}'))
		";
	}
	$sql = "SELECT COUNT(lastviewport) AS countViewport, lastviewport, description
		FROM (
			SELECT DISTINCT lastviewport FROM s_order_basket
			WHERE
			modus=0
				$filterDate
		) AS b1,
		(
			SELECT sessionID, lastviewport AS test FROM s_order_basket
			WHERE
			modus=0
				$filterDate
			GROUP BY sessionID
		) AS b2, s_core_viewports
		WHERE lastviewport = test AND s_core_viewports.viewport = lastviewport
		GROUP BY lastviewport
		ORDER BY countViewport DESC";
   
	$getArticles = mysql_query($sql);

	if (@mysql_num_rows($getArticles)){
		$nodes["totalCount"] = mysql_num_rows($getArticles);
		
		while ($article=mysql_fetch_assoc($getArticles)){
			$abs+= $article["countViewport"];
		}
		mysql_data_seek($getArticles,0);
		while ($article = mysql_fetch_assoc($getArticles)){
			$article["absolute"] = utf8_encode($article["countViewport"]);
			
			$article["viewport"] = utf8_encode($article["description"]." ({$article["lastviewport"]})");
			$article["percent"]  = round($article["absolute"] / $abs*100,2);
			$nodes["pages"][] = $article;
		}
	}else {
		$nodes["pages"] = array();
		$nodes["totalCount"] = 0;
	}
	echo $json->encode($nodes);
	exit;
}


$queryLogs = "
SELECT s_statistics_visitors.datum AS ssvdate, pageimpressions AS hits, uniquevisits AS visits, 
(
	SELECT COUNT(DISTINCT sessionID) FROM s_order_basket WHERE TO_DAYS(datum) = TO_DAYS(ssvdate)
	AND modus=0
	GROUP BY TO_DAYS(datum)
) AS baskets,
(
	SELECT SUM(quantity*price/currencyFactor) FROM s_order_basket WHERE TO_DAYS(datum) = TO_DAYS(ssvdate)
	AND modus=0
	GROUP BY TO_DAYS(datum)
) AS summe
FROM s_statistics_visitors 
$filterDate
GROUP BY ssvdate
ORDER BY {$_POST["sort"]} {$_POST["dir"]}
LIMIT {$_POST["start"]},{$_POST["limit"]}
";

//echo $queryLogs;
$queryData = mysql_query($queryLogs);



if (@mysql_num_rows($queryData)){
	$nodes["totalCount"] = mysql_num_rows($queryData);
	$i = 0;
	while ($day=mysql_fetch_assoc($queryData)){
		$i += 1;
		$day["datum"] = explode("-",$day["ssvdate"]);
		$day["datum"] = $day["datum"][2].".".$day["datum"][1].".".$day["datum"][0];
		$day["basketavg"] = round($day["summe"] / $day["baskets"],2);
		
		$sumVisits += $day["visits"];
		$sumHits += $day["hits"];
		
		
		if ($day["baskets"]) $sumBaskets2 += 1;
		$sumBaskets += $day["baskets"];
		$sumAvg += $day["basketavg"];
		
		$nodes["baskets"][] = $day;
	}
	
	$ges[0] = array("datum"=>utf8_encode("<strong>Gesamt</strong>"),"visits"=>$sumVisits,"hits"=>$sumHits,"baskets"=>$sumBaskets,"basketavg"=>round($sumAvg/$sumBaskets2,2));
	$nodes["baskets"] = array_merge($ges,$nodes["baskets"]);
}else {
	$nodes["baskets"] = array();
	$nodes["totalCount"] = 0;
}
echo $json->encode($nodes);
?>