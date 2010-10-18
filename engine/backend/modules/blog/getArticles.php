<?php
define('sAuthFile', 'sGUI');
define('sConfigPath',"../../../../");
include("../../../backend/php/check.php");
$result = new checkLogin();
$result = $result->checkUser();
if ($result!="SUCCESS"){
	die();
}
// *****************
?>
<?php
require_once("../../../backend/ajax/json.php");
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



if (!$_POST["id"] && !isset($_REQUEST['filter'])){
	
	$nodes["articles"] = array();
	$nodes["totalCount"] = 0;
	echo $json->encode($nodes);
	exit;
}

if (!$_POST["limit"]) $_POST["limit"] = 25;

if ($_POST["sort"]=="supplier") $_POST["sort"] = "supplierName";

if (!$_POST["sort"] || $_POST["sort"]=="lastpost" || $_POST["sort"]=="info") $_POST["sort"] = "a.name";
if (!$_POST["dir"]) $_POST["dir"] = "ASC";






if(empty($_REQUEST['filter'])){
	if ($_REQUEST["showDefect"]){
		$sql = "SELECT COUNT(DISTINCT a.id) as count
		FROM s_articles AS a,
		s_articles_supplier AS aSupplier, s_articles_details AS aDetails,
		s_articles_attributes AS aAttributes
		WHERE 
		a.mode = 1
		AND aAttributes.articledetailsID=aDetails.id
		AND aSupplier.id=a.supplierID AND aDetails.articleID=a.id AND aDetails.kind=1
		";
		
		
		$resultCountArticles = mysql_query($sql);
		$nodes["totalCount"] = @mysql_result($resultCountArticles,0,'count');
		
		$sql = "SELECT a.id as articleID,a.active AS active, ordernumber,datum,additionaltext,instock, shippingfree, description_long, aSupplier.name AS supplierName, aSupplier.id AS supplierID,aSupplier.img AS supplierImg, a.name AS articleName,  sales, 
		attr1,attr2,attr3,attr4,attr5,attr6,attr7,attr8,attr9,attr10,
		attr11,attr12,attr13,attr14,attr15,attr16,attr17,attr18,attr19,attr20,DATE_FORMAT(changetime,'%d.%m.%Y %H:%i:%s') AS datumF
		FROM s_articles AS a,
		s_articles_supplier AS aSupplier, s_articles_details AS aDetails,
		s_articles_attributes AS aAttributes
		WHERE 
		aAttributes.articledetailsID=aDetails.id
		AND a.mode = 1
		AND aSupplier.id=a.supplierID AND aDetails.articleID=a.id AND aDetails.kind=1
		GROUP BY a.id ORDER BY {$_POST["sort"]} {$_POST["dir"]} LIMIT {$_POST["start"]},{$_POST["limit"]}";
	}else {
		$sql = "SELECT COUNT(DISTINCT a.id) as count
		FROM s_articles_categories AS aCategories,s_articles AS a,
		s_articles_supplier AS aSupplier, s_articles_details AS aDetails,
		s_articles_attributes AS aAttributes
		WHERE 
		aCategories.articleID=a.id
		AND a.mode = 1
		AND aAttributes.articledetailsID=aDetails.id
		AND aCategories.categoryID=".$_POST["id"]."
		AND aSupplier.id=a.supplierID AND aDetails.articleID=a.id AND aDetails.kind=1
		";
		
		
		$resultCountArticles = mysql_query($sql);
		$nodes["totalCount"] = @mysql_result($resultCountArticles,0,'count');
		
		$sql = "SELECT a.id as articleID,a.active AS active, ordernumber,datum,additionaltext,instock, shippingfree, description_long, aSupplier.name AS supplierName, aSupplier.id AS supplierID,aSupplier.img AS supplierImg, a.name AS articleName,  sales, 
		attr1,attr2,attr3,attr4,attr5,attr6,attr7,attr8,attr9,attr10,
		attr11,attr12,attr13,attr14,attr15,attr16,attr17,attr18,attr19,attr20,DATE_FORMAT(changetime,'%d.%m.%Y %H:%i:%s') AS datumF
		FROM s_articles_categories AS aCategories,s_articles AS a,
		s_articles_supplier AS aSupplier, s_articles_details AS aDetails,
		s_articles_attributes AS aAttributes
		WHERE 
		aCategories.articleID=a.id
		AND aAttributes.articledetailsID=aDetails.id
		AND aCategories.categoryID=".$_POST["id"]."
		AND a.mode = 1
		AND aSupplier.id=a.supplierID AND aDetails.articleID=a.id AND aDetails.kind=1
		GROUP BY a.id ORDER BY {$_POST["sort"]} {$_POST["dir"]} LIMIT {$_POST["start"]},{$_POST["limit"]}";
	}
	
	$getArticles = mysql_query($sql);
	
	if (@mysql_num_rows($getArticles)){
		while ($article=mysql_fetch_array($getArticles)){
			// Grep one picture to show in list
			$queryPicture = mysql_query("
			SELECT img FROM s_articles_img WHERE articleID={$article["articleID"]} AND main=1
			");
			if (@mysql_num_rows($queryPicture)){
				$article["image"] = $_SERVER["SERVER_PORT"] == "80" ? "http" : "https";
				$article["image"] .= "://".$sCore->sCONFIG['sBASEPATH']."/images/articles/".mysql_result($queryPicture,0,"img")."_2.jpg";
			}else {
				$article["image"] = "";
				$article["info"] .= "<a class='ico picture_exclamation' title='Kein Bild hinterlegt'></a>";
			}			
			if (!$article["info"]) $article["info"] = "";
			if (empty($article["payments"])) $article["payments"] = "0";
			$nodes["articles"][] = array('articleName'=>utf8_encode(str_replace("'","",$article["articleName"])), 'articleID'=>$article["articleID"],"ordernumber"=>utf8_encode($article["ordernumber"]),"supplier"=>utf8_encode($article["supplierName"]),"image"=>$article["image"],"price"=>$price,"instock"=>$article["instock"],"tax"=>$article["taxID"],"active"=>$article["active"],"info"=>$article["info"],"datum"=>$article["datumF"]);
		}
	}else {
		$nodes["articles"] = array();
		$nodes["totalCount"] = 0;
	}
}
echo $json->encode($nodes);
?>