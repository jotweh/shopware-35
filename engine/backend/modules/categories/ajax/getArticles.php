<?php
define('sAuthFile', 'sGUI');
define('sConfigPath','../../../../../');
include('../../../../backend/php/check.php');
$result = new checkLogin();
$result = $result->checkUser();
if ($result!='SUCCESS'){
	die();
}


require_once('../../../../backend/ajax/json.php');



$limit = empty($_REQUEST['limit']) ? 25 : (int)$_REQUEST['limit'];
$start = empty($_REQUEST['start']) ? 0 : (int)$_REQUEST['start'];
$dir = (empty($_REQUEST['dir'])||$_REQUEST['dir']=='ASC') ? 'ASC' : 'DESC';
$sort = (empty($_REQUEST['sort'])||is_array($_REQUEST['sort'])) ? 'name' : preg_replace('#[^\w]#','',$_REQUEST['sort']);

if(!empty($_REQUEST["search"]))
{
	$search = "'%".mysql_real_escape_string(trim(utf8_decode($_REQUEST["search"])))."%'";
	$sql_where .= " AND ( d.ordernumber LIKE  $search ";
	$sql_where .= "OR a.name LIKE $search ";
	$sql_where .= "OR s.name LIKE $search  ) ";
}
if(!empty($_REQUEST['categoryID'])&&is_numeric($_REQUEST['categoryID']))
{
	$categoryID = (int)$_REQUEST['categoryID'];
	$sql_join = 'JOIN s_articles_categories ac ON a.id=ac.articleID AND ac.categoryID='.$categoryID;
	if(empty($_REQUEST['invert']))
	{
		$sql_join = 'LEFT '.$sql_join;
		$sql_where .= "AND ac.id IS NULL";
	}
}

$sql = "
	SELECT SQL_CALC_FOUND_ROWS
		a.id, a.name, d.ordernumber, s.name as supplier
	FROM s_articles a
	INNER JOIN s_articles_details d
	ON a.id=d.articleID
	AND d.kind=1
	LEFT JOIN s_articles_supplier s
	ON s.id=a.supplierID
	$sql_join
	WHERE a.mode = 0
	$sql_where
	ORDER BY $sort $dir
	LIMIT $start, $limit
";

$result = mysql_query($sql);
$nodes = array();
if ($result&&mysql_num_rows($result))
while ($row = mysql_fetch_assoc($result))
{
	$row["name"] = utf8_encode($row["name"]);
	$row["supplier"] = utf8_encode($row["supplier"]);
	$nodes[] = $row;
}

$sql = 'SELECT FOUND_ROWS() as count';
$result = mysql_query($sql);
if($result&&mysql_num_rows($result))
	$count = (int) mysql_result($result, 0, 'count');
else
	$count = 0;


$json = new Services_JSON();
echo $json->encode(array("articles"=>$nodes,"count"=>$count));
?>