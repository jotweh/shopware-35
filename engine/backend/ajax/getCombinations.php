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
$id = intval($_POST["id"]);
$article = intval($_POST["article"]);

// Get saved relations
$getRelations = mysql_query("
SELECT relations FROM s_articles_img WHERE id = $id
");
$rel = @mysql_result($getRelations,0,"relations");

// Check if article has one dimensional variants or configurator groups / options
$getVariants = mysql_query("
SELECT additionaltext, ordernumber FROM s_articles_details WHERE articleID = $article
ORDER BY kind ASC
");
if (@mysql_num_rows($getVariants)>1){
	// Loop through
	while ($variant = mysql_fetch_assoc($getVariants)){
		$variant["additionaltext"] = utf8_encode($variant["additionaltext"]);
		if ($variant["ordernumber"]==$rel){
			$marked = true;
		}else {
			$marked = false;
		}
		$articles[] =  array("marked"=>$marked,"combination"=>$variant["additionaltext"],"id"=>$variant["ordernumber"],"mode"=>1);
	}
}else {
	// Check for configurators
	// Get all group / option pears
	$getGroups = mysql_query("
	SELECT groupID, groupname FROM s_articles_groups
	WHERE articleID = $article
	ORDER BY groupID ASC
	");
	$test = array();
	preg_match("/(.*)\{(.*)\}/",$rel,$test);
	
	//$rel = str_replace("{","",$rel);
	//$rel = str_replace("}","",$rel);
	$rel = $test[2];
	$rel = strtolower($rel);
//	$rel = str_replace(" ","",$rel);
	$rel = explode("/",$rel);
	
	
	while ($group = mysql_fetch_assoc($getGroups)){
		// Get all options
		$getOptions = mysql_query("
		SELECT optionID, optionname FROM s_articles_groups_option
		WHERE articleID = $article AND groupID = {$group["groupID"]}
		ORDER BY optionID ASC
		");
		while ($option = mysql_fetch_assoc($getOptions)){
			$option["optionname"] = str_replace("/","",$option["optionname"]);
			$group["groupname"] = str_replace("/","",$group["groupname"]);
			$combination = utf8_encode($group["groupname"].">".$option["optionname"]);
			$number = $group["groupname"].":".$option["optionname"];
			$number = str_replace(" ","",$number);
			$number = strtolower($number);
			if (in_array($number,$rel)){
				$marked = true;
			}else {
				$marked = false;
			}
			$number = utf8_encode($number);
			$articles[] =  array("marked"=>$marked,"combination"=>$combination,"id"=>$number,"mode"=>2);
		}
	}
}


$nodes = array("articles"=>$articles,"totalCount"=>count($articles));
echo $json->encode($nodes);
?>