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

if(isset($_REQUEST['newKey'])&&is_numeric($_REQUEST['portal']))
{
	$getPortals = mysql_query("
	UPDATE `s_export`
	SET hash = '".md5(time()+rand())."'
	WHERE id='{$_REQUEST['portal']}'
	");
	
}

if(isset($_REQUEST['active'])&&isset($_REQUEST['portal']))
{
	if($_REQUEST['active'] == 1)
	$getPortals = mysql_query("
	UPDATE `s_export`
	SET active = 1
	WHERE id='{$_REQUEST['portal']}'
	");
	else 
		$getPortals = mysql_query("
	UPDATE `s_export`
	SET active = 0
	WHERE id='{$_REQUEST['portal']}'
	");
	die();
}

require_once("json.php");
$json = new Services_JSON();

$getPortals = mysql_query("
SELECT `id` , `name` , `short_name` , `image` , `lastexport` , `active` , `hash` , `link`
FROM `s_export` WHERE `show` =1
");

if (!$getPortals){
echo "FAIL";
	die();
}
$retPortals = array();
while ($Portal = mysql_fetch_assoc($getPortals))
{
	$retPortal['logo'] = "<a href=\"{$Portal['link']}\" target=\"_blank\" class=\"ico cross\" style=\"cursor:pointer\"><img src=\"logos/{$Portal['image']}\" alt=\"{$Portal['name']}\" /></a>";
	$retPortal['portal'] = $Portal['name'];
	$retPortal['link'] = "<a href=\"../../connectors/sExport/csv/{$Portal['id']}/{$Portal['hash']}/export.{$Portal['short_name']}.csv\" target=\"_blank\" class=\"ico world_link\" style=\"cursor:pointer\"></a>";
	$retPortal['articles'] = "";
	if($Portal['active']==1)
		$retPortal['active'] = "<a onclick=\"toogleActive({$Portal['id']}, this)\" class=\"ico tick\" style=\"cursor:pointer\" onclick=\"window.location='#'\"></a>";
	else
		$retPortal['active'] = "<a onclick=\"toogleActive({$Portal['id']}, this)\" class=\"ico cross\" style=\"cursor:pointer\" onclick=\"window.location='#'\"></a>";
	$retPortal['option'] = "<a name=\"edit\" onclick=\"tooglePortal({$Portal['id']})\" class=\"ico pencil\" style=\"cursor: pointer;\"></a>";
	$retPortal['lastexport'] = "<a class=\"ico date toolTip\" style=\"cursor: pointer;\"/ title=\"Letzter zugriff :: {$Portal['lastexport']}\"></a>";//$Portal['lastexport'];
	$retPortal['key'] = "<a name=\"edit\" onclick=\"newKey({$Portal['id']})\" class=\"ico key\" style=\"cursor: pointer;\"></a>";
	$retPortals[] = $retPortal;
}
echo $json->encode($retPortals);
?>
