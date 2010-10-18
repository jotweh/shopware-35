<?php
define('sAuthFile', 'sGUI');
define('sConfigPath',"../../../../");
include("../../../backend/php/check.php");
$result = new checkLogin();
$result = $result->checkUser();
if ($result!="SUCCESS"){

	die();
}

require_once('../../../../engine/backend/ajax/json.php');
$json = new Services_JSON();

$feedID = (int)$_REQUEST["feedID"];
switch ($_REQUEST["name"])
{
	case "paymentstate":
		$sql = "
			SELECT 0 as id, 'Bitte auswählen' as name, -1 as position
			UNION
			SELECT id+1 as id, description as name, position FROM s_core_states WHERE `group`='payment' AND id>=0 ORDER BY IF(position,position,0) ASC
		";
		break;
	case "orderstate":
		$sql = "
			SELECT 0 as id, 'Bitte auswählen' as name, -1 as position
			UNION
			SELECT id+1 as id, description as name, position FROM s_core_states WHERE `group`='state' AND id>=0 ORDER BY position ASC
		";		
		break;
	case "supplier":
		if(empty($_REQUEST["active"]))
		{
			$sql = "
				SELECT s.id, s.name, s.img
				FROM s_articles_supplier s
				LEFT JOIN s_articles AS a ON a.supplierID = s.id
				LEFT JOIN s_export_suppliers AS es ON es.supplierID = s.id AND feedID = $feedID
				WHERE es.supplierID IS NULL
				GROUP BY s.id ORDER BY name
			";
		}
		else
		{
			$sql = "
				SELECT s.id, s.name, s.img
				FROM s_articles_supplier s
				LEFT JOIN s_articles AS a ON a.supplierID = s.id
				JOIN s_export_suppliers AS es ON es.supplierID = s.id AND feedID = $feedID
				GROUP BY s.id ORDER BY name
			";
		}
		break;
	case "category";
		$node = (empty($_REQUEST["node"])||!is_numeric($_REQUEST["node"])) ? 1 : (int) $_REQUEST["node"];
		$sql = "
			SELECT c.id, c.description as text, c.parent as parentId, IF(COUNT(c2.id)>0,0,1) as leaf FROM s_categories c LEFT JOIN s_categories c2 ON c2.parent=c.id  WHERE c.parent=$node GROUP BY c.id ORDER BY c.position, c.description
		";
		break;
	case "newsletter_groups":
		$sql = "
			SELECT id, name FROM s_campaigns_groups ORDER BY id ASC
		";
		break;
	default:
		exit();
}

$result = mysql_query($sql);
$nodes = array();
if ($result&&mysql_num_rows($result)){
while ($row = mysql_fetch_assoc($result))
{
	if(isset($row["leaf"]))
		$row["leaf"] = !empty($row["leaf"]);
	if(isset($row["text"]))
		$row["text"] = utf8_encode($row["text"]);
	$row["name"] = utf8_encode($row["name"]);
	if(!empty($row["count"]))
		$row["name"] .= " (".$row["count"].")";
	$nodes[] = $row;
}
}

switch ($_REQUEST["name"]) {
	case "category":
		echo  $json->encode($nodes);
		break;
	default:
		echo  $json->encode(array("articles"=>$nodes,"count"=>count($nodes)));
		break;
}


?>