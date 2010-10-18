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



$nodes = array();

$nodes[] = array('text'=>"Zubeh&ouml;r - Gruppen",cls=>'folder',leaf=>true,'file'=>"config/config.accessories.group.php");
$nodes[] = array('text'=>"Zubeh&ouml;r - Optionen",cls=>'folder',leaf=>true,'file'=>"config/config.accessories.option.php");
$nodes[] = array('text'=>"Templates",cls=>'folder',leaf=>true,'file'=>"config/config.template.php");
$nodes[] = array('text'=>"Einstellungen",cls=>'folder',leaf=>true,'file'=>"config/config.settings.php");
$nodes[] = array('text'=>"Gruppen", cls=>'folder',leaf=>true,'file'=>"config/config.group.php");
$nodes[] = array('text'=>"Optionen", cls=>'folder',leaf=>true,'file'=>"config/config.option.php");
$nodes[] = array('text'=>"Preiseingabe", cls=>'folder',leaf=>true,'file'=>"config/config.price.php");

echo $json->encode($nodes);
?>