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

error_reporting(0);
require_once("json.php");
$json = new Services_JSON();




if (empty($_REQUEST["node"]) || $_REQUEST["node"]==-1)
{
	$_REQUEST["node"] = 0;
}
else 
{
	$_REQUEST["node"] = intval($_REQUEST["node"]);
}

$nodes = array();

if (empty($_REQUEST["node"])){
	$getCategories = mysql_query("
	SELECT id, `name` AS description, active, DATE_FORMAT(lastexport,'%d.%m.%Y %H:%i') AS lastexport FROM s_export WHERE `show`=1 ORDER BY `name`
	");
	
	if (@mysql_num_rows($getCategories)){
		while ($category=mysql_fetch_array($getCategories)){
			$category["description"] = ucfirst(utf8_encode($category["description"]));
			if (!$_REQUEST["node"]){
				if ($category["active"]){
					$nodes[] = array('text'=>$category["description"], id=>$category["id"], parentId=>$category["parent"], cls=>'folder',icon=>'../../backend/img/default/icons/tick.png');
				}else {
					$nodes[] = array('text'=>$category["description"], id=>$category["id"], parentId=>$category["parent"], cls=>'folder',icon=>'../../backend/img/default/icons/cross.png');
				}
			}
		}
	}
}else {
	$getEngine = mysql_query("
	SELECT id, `name` AS description, active, DATE_FORMAT(lastexport,'%d.%m.%Y %H:%i') AS lastexport, hash, short_name, countarticle FROM s_export WHERE `show`=1 AND id={$_REQUEST["node"]}
	");
	$getEngine = mysql_fetch_assoc($getEngine);
	if(empty($getEngine["countarticle"]))
		$getEngine["countarticle"] = "?";
	//$url = "http://".$sCore->sCONFIG["sBASEPATH"]."/engine/connectors/sExport/csv/{$getEngine['id']}/{$getEngine['hash']}/export.{$getEngine['short_name']}.csv";

	//$fp = fopen($url,"r");
	//while ($buffer=fread($fp,1024)) $data.=$buffer;
	//fclose($fp);
	
	//$data = explode("\n",$data);
	//$data =  count($data);
	// Child-Options for Search-Engines
	$nodes[] = array('text'=>"Letzer Export: {$getEngine["lastexport"]}",cls=>'folder',leaf=>true,icon=>'../../backend/img/default/icons/information.png','disabled'=>true);
	$nodes[] = array('text'=>"Anzahl Artikel: {$getEngine["countarticle"]}",cls=>'folder',leaf=>true,icon=>'../../backend/img/default/icons/information.png','disabled'=>true);
	$nodes[] = array('text'=>"Link generieren",cls=>'folder',leaf=>true,'file'=>"dumper.php?id={$_REQUEST["node"]}&mask=1");
	$nodes[] = array('text'=>"Einstellungen",cls=>'folder',leaf=>true,'file'=>"config.php?id={$_REQUEST["node"]}&mask=1");
	$nodes[] = array('text'=>"Kategorien sperren", cls=>'folder',leaf=>true,'file'=>"config.php?id={$_REQUEST["node"]}&mask=2");
	$nodes[] = array('text'=>"Artikel sperren", cls=>'folder',leaf=>true,'file'=>"config.php?id={$_REQUEST["node"]}&mask=3");
}
echo $json->encode($nodes);
?>