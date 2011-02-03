<?php
define('sAuthFile', 'sGUI');
define('sConfigPath',"../../../../../");
include("../../../../backend/php/check.php");
$result = new checkLogin();
$result = $result->checkUser();
if ($result!="SUCCESS"){
	die();
}

require_once("json.php");
$json = new Services_JSON();

$feedID = (int)$_REQUEST["feedID"];
switch ($_REQUEST["name"])
{
	case "customergroup":
		$sql = "
			SELECT 0 as id, 'allgemein gültig' as name
			UNION (
				SELECT id, description as name
				FROM s_core_customergroups
				ORDER BY name
			)
		";
		break;
	case "multishop":
		$sql = "
			SELECT 0 as id, 'allgemein gültig' as name
			UNION (
				SELECT id, name
				FROM s_core_multilanguage
				ORDER BY `default` DESC, name
			)
		";
		break;
	case "language":
		$sql = "
			SELECT 0 as id, 'Standard' as name
			UNION
			SELECT id, isocode as name
			FROM s_core_multilanguage
			WHERE skipbackend=0
			GROUP BY isocode
		";
		break;
	case "tax":
		$sql = "
			SELECT 0 as id, 'höchster Steuersatz aus dem Warenkorb nehmen' as name
			UNION
			SELECT id, description as name
			FROM `s_core_tax`
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
	case "currency":
		$sql = "
			SELECT id, name
			FROM s_core_currencies
		";
		break;
	case "category";
		$node = (empty($_REQUEST["node"])||!is_numeric($_REQUEST["node"])) ? 1 : (int) $_REQUEST["node"];
		$sql = "
			SELECT c.id, c.description as text, c.parent as parentId, IF(COUNT(c2.id)>0,0,1) as leaf FROM s_categories c LEFT JOIN s_categories c2 ON c2.parent=c.id  WHERE c.parent=$node GROUP BY c.id ORDER BY c.position, c.description
		";
		break;
	case "holiday";
		$sql = "
			SELECT id, CONCAT(name,' (',DATE_FORMAT(`date`,'%d.%m.%Y'),')') as name
			FROM s_premium_holidays
			ORDER BY `date`, name
		";
		break;
	case "article":
		if(empty($_REQUEST["active"]))
		{
			$sql = "
				SELECT a.id, a.name
				FROM s_articles a, s_export_articles ea
				WHERE a.id=ea.articleID
				AND ea.feedID=$feedID
			";
		}
		elseif(!empty($_REQUEST["filter"]))
		{
			$sql_filter = mysql_real_escape_string(trim($_REQUEST["filter"]));
			$sql = "
				SELECT 
					a.id, a.name
				FROM 
					s_articles as a,
				(
					SELECT DISTINCT articleID
					FROM
					(
							SELECT DISTINCT articleID
							FROM s_articles_details
							WHERE ordernumber LIKE '$sql_filter%'
							LIMIT 10
						UNION
							SELECT DISTINCT articleID
							FROM s_articles_translations
							WHERE name LIKE '%$sql_filter%'
							LIMIT 10
						UNION
							SELECT DISTINCT articleID
							FROM s_articles_translations
							WHERE name LIKE '%$sql_filter%'
							LIMIT 10
						UNION	
							SELECT id as articleID
							FROM s_articles
							WHERE name LIKE '%$sql_filter%'
							LIMIT 10
					) as amu
				) as am
				WHERE am.articleID=a.id
				ORDER BY a.name ASC
				LIMIT 20
			";
		}
		break;
	case "paymentmean":
		if(empty($_REQUEST["active"]))
		{
			$sql = "
				SELECT p.id, p.description as name
				FROM s_core_paymentmeans p
				LEFT JOIN s_premium_dispatch_paymentmeans AS dp ON dp.paymentID = p.id AND dispatchID = $feedID
				WHERE dispatchID IS NULL
				ORDER BY name
			";
		}
		else
		{
			$sql = "
				SELECT p.id, p.description as name
				FROM s_core_paymentmeans p
				JOIN s_premium_dispatch_paymentmeans AS dp ON dp.paymentID = p.id AND dispatchID = $feedID
				ORDER BY name
			";
		}
		break;
	case "countries":
		if(empty($_REQUEST["active"]))
		{
			$sql = "
				SELECT c.id, c.countryname as name
				FROM s_core_countries c
				LEFT JOIN s_premium_dispatch_countries AS dc ON dc.countryID = c.id AND dispatchID = $feedID
				WHERE dispatchID IS NULL
				ORDER BY name
			";
		}
		else
		{
			$sql = "
				SELECT c.id, c.countryname as name
				FROM s_core_countries c
				JOIN s_premium_dispatch_countries AS dc ON dc.countryID = c.id AND dispatchID = $feedID
				ORDER BY name
			";
		}
		break;
	case "dispatch":
		$sql = "
			SELECT id, name
			FROM s_shippingcosts_dispatch d
		";
		//WHERE (SELECT id FROM s_order WHERE dispatchID = d.id LIMIT 1)
		break;
	case "premium_dispatch":
		$sql = "
			SELECT id, name 
			FROM s_premium_dispatch
			ORDER BY position, name
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
	if(isset($row["id"]))
		$row["id"] = intval($row["id"]);
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