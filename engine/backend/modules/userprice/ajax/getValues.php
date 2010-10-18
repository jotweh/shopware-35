<?php
define('sAuthFile', 'sGUI');
define('sConfigPath',"../../../../../");
include("../../../../backend/php/check.php");
$result = new checkLogin();
$result = $result->checkUser();
if ($result!="SUCCESS"){
	die();
}

if(!empty($_REQUEST["delete"]))
{
	$delete = intval($_REQUEST['delete']);
	switch ($_REQUEST["name"])
	{
		case "customerpricegroups":
			$sql = "DELETE FROM s_articles_prices WHERE pricegroup='PG$delete'";
			mysql_query($sql);
			$sql = "DELETE FROM s_articles_groups_prices WHERE groupkey='PG$delete'";
			mysql_query($sql);
			$sql = "UPDATE s_user SET pricegroupID=NULL WHERE pricegroupID=$delete";
			mysql_query($sql);
			$sql = "DELETE FROM s_core_customerpricegroups WHERE id=$delete";
			mysql_query($sql);
			break;
		default:
			exit();
	}
}

$limit = empty($_REQUEST['limit']) ? 25 : (int)$_REQUEST['limit'];
$start = empty($_REQUEST['start']) ? 0 : (int)$_REQUEST['start'];

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
		break;
	case "premium_dispatch":
		$sql = "
			SELECT id, name 
			FROM s_premium_dispatch
			ORDER BY position, name
		";
		break;
	case "users":
		if(!empty($_REQUEST['pricegroupID']))
			$sql_where = 'AND u.pricegroupID='.intval($_REQUEST['pricegroupID']);
		else
			$sql_where = 'AND u.pricegroupID IS NULL';
		$dir = (empty($_REQUEST['dir'])||$_REQUEST['dir']=='ASC') ? 'ASC' : 'DESC';
		$sort = (empty($_REQUEST['sort'])||is_array($_REQUEST['sort'])) ? 'customernumber' : preg_replace('#[^\w]#','',$_REQUEST['sort']);
		if(!empty($_REQUEST["search"]))
		{
			$search = "'".mysql_real_escape_string(trim(utf8_decode($_REQUEST["search"])))."%'";
			$search2 = "'%".mysql_real_escape_string(trim(utf8_decode($_REQUEST["search"])))."%'";
			$sql_where .= " AND ( ub.customernumber LIKE  $search ";
			$sql_where .= "OR u.email LIKE $search2 ";
			$sql_where .= "OR ub.company LIKE $search ";
			$sql_where .= "OR ub.firstname LIKE $search ";
			$sql_where .= "OR ub.lastname LIKE $search )";
		}
		$sql = "
			SELECT SQL_CALC_FOUND_ROWS u.id, ub.customernumber, u.email, u.customergroup, ub.company, ub.firstname ,ub.lastname
			FROM s_user u, s_user_billingaddress ub
			WHERE u.id=ub.userID
			$sql_where
			ORDER BY $sort $dir
			LIMIT $start, $limit
		";
		break;
	case "customerpricegroups":
		$sql = "
			SELECT *
			FROM s_core_customerpricegroups
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
	if(isset($row["netto"]))
		$row["netto"] = empty($row["netto"]) ? 0 : 1;
	if(isset($row["active"]))
		$row["active"] =empty($row["active"]) ? 0 : 1;
	if(isset($row["text"]))
		$row["text"] = utf8_encode($row["text"]);
	if(isset($row["firstname"]))
		$row["firstname"] = utf8_encode($row["firstname"]);
	if(isset($row["lastname"]))
		$row["lastname"] = utf8_encode($row["lastname"]);
	$row["name"] = utf8_encode($row["name"]);
	if(!empty($row["count"]))
		$row["name"] .= " (".$row["count"].")";
	$nodes[] = $row;
}
}

if(!empty($limit))
{
	$sql = 'SELECT FOUND_ROWS() as count';
	$result = mysql_query($sql);
	if($result&&mysql_num_rows($result))
		$count = (int) mysql_result($result, 0, 'count');
	else
		$count = 0;
}
else
{
	$count = count($nodes);
}

require_once("json.php");
$json = new Services_JSON();

switch ($_REQUEST["name"]) {
	case "category":
		echo  $json->encode($nodes);
		break;
	default:
		echo  $json->encode(array("articles"=>$nodes,"count"=>$count));
		break;
}


?>