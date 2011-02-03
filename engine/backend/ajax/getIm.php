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


if (empty($_REQUEST["limit"])) $limit = 25; else $limit = $_REQUEST["limit"];
if (empty($_REQUEST["start"])) $start = 0; else $limit = $_REQUEST["start"];
/*
	 	Feld  	Typ  	Kollation  	Attribute  	Null  	Standard  	Extra  	Aktion
	id 	int(11) 			Nein 	keine 	auto_increment 	Zeige nur unterschiedliche Werte 	Ändern 	Löschen 	Primärschlüssel 	Unique 	Index 	Volltext
	client 	varchar(255) 	latin1_swedish_ci 		Nein 			Zeige nur unterschiedliche Werte 	Ändern 	Löschen 	Primärschlüssel 	Unique 	Index 	Volltext
	subject 	varchar(255) 	latin1_swedish_ci 		Nein 			Zeige nur unterschiedliche Werte 	Ändern 	Löschen 	Primärschlüssel 	Unique 	Index 	Volltext
	text 	text 	latin1_swedish_ci 		Nein 			Zeige nur unterschiedliche Werte 	Ändern 	Löschen 	Primärschlüssel 	Unique 	Index 	Volltext
	datum 	d
	*/
if (!empty($_REQUEST["search"])){
	$search = mysql_real_escape_string($_REQUEST["search"]);
	$sql = "AND (subject LIKE '%$search%' OR text LIKE '%$search%' OR client LIKE '%$search%')";
}
$sql = "
SELECT id,client,datum,text,subject,DATE_FORMAT(datum,'%d.%m.%Y %H:%i') AS dateFormated, status FROM s_core_im 
WHERE (receiver = -1 OR receiver = {$_SESSION["sID"]})
$sql
ORDER BY datum DESC LIMIT $start,$limit
";

$query = mysql_query($sql);

function rewriteTags($src){
	if ($src[1] && $src[2]){
		switch ($src[1]){
			case "article":
				$sql = "
				SELECT articleID AS id FROM s_articles_details WHERE ordernumber='{$src[2]}' 
				";
				
				$checkForArticle = mysql_query($sql);
				
				if (mysql_num_rows($checkForArticle)){
					$id = mysql_result($checkForArticle,0,"id");
					
					
				}else {
					// Check if is article-configurator-article
					$checkForArticle =mysql_query("
					SELECT articleID AS id FROM s_articles_groups_value WHERE ordernumber='{$src[2]}' 
					");
					if (mysql_num_rows($checkForArticle)){
						$id = mysql_result($checkForArticle,0,"id");
					}else {
						return false;
					}
				}
				if (!empty($id)){
					//parent.loadSkeleton('articles',false, {'article':37})
					return "<a style=\"cursor:pointer\" onclick=\"parent.loadSkeleton('articles',false,{'article':$id})\" class=\"ico3 package\">Artikel aufrufen</a>";
				}else {
					return false;
				}
				break;
			case "user":
				$queryId = mysql_query("
				SELECT userID AS id FROM s_user_billingaddress WHERE customernumber = '{$src[2]}'
				");
				if (@mysql_num_rows($queryId)){
					// parent.loadSkeleton('orders',false,{'id':71})
					$orderID = mysql_result($queryId,0,"id");
					return "<a style=\"cursor:pointer\" onclick=\"parent.loadSkeleton('userdetails',false,{'user':$orderID})\" class=\"ico3 customer\">Kunden aufrufen</a>";
			
				}else {
					return "";
				}
				break;
			case "order":
				$queryId = mysql_query("
				SELECT id FROM s_order WHERE ordernumber = '{$src[2]}'
				");
				if (@mysql_num_rows($queryId)){
					$orderID = mysql_result($queryId,0,"id");
					return "<a style=\"cursor:pointer\" onclick=\"parent.loadSkeleton('orders',false,{'id':$orderID})\" class=\"ico3 sticky_note_pin\">Bestellung aufrufen</a>";
				}else {
					return "";
				}
				break;
		}
	}
}
while ($msg = mysql_fetch_assoc($query)){
	$msg["subject"] = str_replace("'","",$msg["subject"]);
	$msg["subject"] = str_replace("\"","",$msg["subject"]);
	$msg["text"] = preg_replace_callback("/\{(.*)\:(.*)\}/U",rewriteTags,$msg["text"] );
	$msg["status"] = unserialize($msg["status"]);
	$msg["status"] = $msg["status"][$_SESSION["sID"]];
	if (!$msg["status"]) $notYetRead = true;
	/*$msg["text"] = "{article:1234}";$msg["text"] 
	$msg["text"] = "{order:1234}";
	$msg["text"] = "{user:1234}";
	*/
	$ims[] = array("from"=>utf8_encode($msg["client"]),"date"=>$msg["dateFormated"],"subject"=>$msg["subject"],"text"=>$msg["text"],"id"=>$msg["id"],"status"=>$msg["status"]);
}

$queryAll = mysql_query("
SELECT * FROM s_core_im 
WHERE (receiver = -1 OR receiver = {$_SESSION["sID"]})
$sql
ORDER BY datum DESC
");
require_once("json.php");
$json = new Services_JSON();
$nodes = array();

$nodes["dashboard"] = $ims;

$nodes["totalCount"] = @mysql_num_rows($queryAll) ?  @mysql_num_rows($queryAll) : 0;
if (@count($nodes["dashboard"])){
if (($notYetRead)) $nodes["dashboard"][0]["notRead"] = true; else $nodes["dashboard"][0]["notRead"] = false;
}
echo $json->encode($nodes);
	
?>