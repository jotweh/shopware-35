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
$_REQUEST["node"] = addslashes($_REQUEST["node"]);
if (!$_REQUEST["node"]){
	$_REQUEST["node"] = "CAMPAIGNS:1";	// Get campaigns from start
}


$requestElement = explode(":",$_REQUEST["node"]);

$requestType = $requestElement[0];
$requestElement = $requestElement[1];


$nodes = array();

switch ($requestType){
	case "CAMPAIGNS":
		$getElements = mysql_query("
		SELECT id, description FROM s_emarketing_promotion_main WHERE parentID=$requestElement ORDER BY position ASC
		");
		break;
	case "CAMPAIGN":
		$getElements = mysql_query("
		SELECT id, type AS description, description AS fallback FROM s_emarketing_promotion_containers WHERE promotionID=$requestElement ORDER BY position ASC
		");
		break;
	case "ARTICLES":
		$getElements = mysql_query("
		SELECT id, name AS description FROM s_emarketing_promotion_articles WHERE parentID=$requestElement ORDER BY position ASC
		");
		break;
	case "LINKS":
		$getElements = mysql_query("
		SELECT id, description FROM s_emarketing_promotion_links WHERE parentID=$requestElement ORDER BY position ASC
		");
		break;
}




if (@mysql_num_rows($getElements)){
	$parentID = rand(0,100000);
	while ($element=mysql_fetch_array($getElements)){
		unset($getCategoryLeafs);
		// Query for Sub-Elements
		switch ($requestType){
			case "CAMPAIGNS":
				$type = "CAMPAIGN";
				$getCategoryLeafs = mysql_query("
				SELECT id FROM s_emarketing_promotion_containers WHERE promotionID={$element["id"]} LIMIT 1
				");
				$type = "CAMPAIGN";
				$element["parent"] = "CAMPAIGNS:".$requestElement;
				break;
			case "CAMPAIGN":
				
					if ($element["description"]=="ctBanner"){
						$element["description"] = "Banner";
						$type = "BANNER";
					}elseif ($element["description"]=="ctLinks"){
						$element["description"] = "Linkgruppe";
						
						$getCategoryLeafs = mysql_query("
						SELECT id FROM s_emarketing_promotion_links WHERE parentID={$element["id"]} LIMIT 1
						");
						
						$type = "LINKS";
					}elseif ($element["description"]=="ctArticles"){
						$element["description"] = "Artikelgruppe";
						$getCategoryLeafs = mysql_query("
						SELECT id FROM s_emarketing_promotion_articles WHERE parentID={$element["id"]} LIMIT 1
						");
						$type = "ARTICLES";
					}elseif ($element["description"]=="ctText"){
						$element["description"] = "Text";
						$type = "TEXT";
					}
					
					if ($element["fallback"]) $element["description"] = $element["fallback"];
					$element["parent"] = "CAMPAIGN:".$requestElement;
				break;
			case "ARTICLES":
				$type = "ARTICLE";
				$element["parent"] = "ARTICLES:".$requestElement;
				break;
			case "LINKS":
				$type = "LINK";
				$element["parent"] = "LINKS:".$requestElement;
			break;
		}
		
		
		
		
		$element["description"] = utf8_encode($element["description"]);
		if (@mysql_num_rows($getCategoryLeafs)){
			$nodes[] = array('text'=>$element["description"], id=>$type.":".$element["id"], dbId=>$element["id"], parentId=>$element["parent"], type=>$type, cls=>'folder');
		}else {
			$nodes[] = array('text'=>$element["description"], id=>$element["id"]."#".$element["parent"], dbId=>$element["id"],parentId=>$element["parent"], type=>$type, leaf=>true, cls=>'folder');
		}
	}
}
echo $json->encode($nodes);
?>