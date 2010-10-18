<?php
define('sAuthFile', 'sGUI');
define('sConfigPath',"../../../../../");
include("../../../../backend/php/check.php");
$result = new checkLogin();
$result = $result->checkUser();
if ($result!="SUCCESS"){
	die();
}

require_once('../../../../backend/ajax/json.php');
$json = new Services_JSON();

function sGetCategoryPath($categoryID, $separator = ">")
{
	$sql = 'SELECT description as name, parent FROM s_categories WHERE id='.$categoryID;
	$result = mysql_query($sql);
	if(!empty($result)&&mysql_num_rows($result))
	{
		$row = mysql_fetch_assoc($result);
		$parent = sGetCategoryPath($row['parent']);
		return empty($parent) ? $row['name'] : $parent.$separator.$row['name'];
	}
	else
	{
		return '';
	}
}
		
function sGetCategoriesPath($categoryID=null, $separator = '>')
{
	if(empty($categoryID))
		$where = 'parent=1';
	elseif(is_numeric($categoryID))
		$where = 'parent='.(int) $categoryID;
	else
		$where = "description LIKE '%".mysql_real_escape_string(trim(htmlspecialchars($categoryID)))."%'";
	
	$paths = array();
	
	$sql = 'SELECT id, description as name FROM s_categories WHERE '.$where.' ORDER BY parent DESC, name';
	$result = mysql_query($sql);
	if(!empty($result)&&mysql_num_rows($result))
	while ($category = mysql_fetch_assoc($result))
	{
		if(!is_numeric($categoryID))
		{
			$category['name'] = sGetCategoryPath($category['id'] , $separator);
		}
		$childs = sGetCategoriesPath($category['id'], $separator);
		if(empty($childs))
		{
			$paths[$category['id']] = $category['name'];
		}
		else 
		{
			foreach ($childs as $key=>$child)
			{
				$paths[$key] = $category['name'].$separator.$child;
			}
		}
	}
	return $paths;
}

$rows = array();

if(!empty($_REQUEST['query']))
{
	$_REQUEST['query'] = trim(utf8_decode($_REQUEST['query']));
}

if(!empty($_REQUEST['valuesqry'])&&!empty($_REQUEST['query']))
{
	$categories = explode('|',$_REQUEST['query']);
	foreach ($categories as $categoryID)
	{
		$rows[] = array(
			'id'=>$categoryID,
			'name'=>utf8_encode(htmlspecialchars_decode(sGetCategoryPath($categoryID)))
		);
	}
}
elseif(!empty($_REQUEST['query']))
{
	$categories = sGetCategoriesPath($_REQUEST['query']);
	foreach ($categories as $categoryID => $category)
	{
		$category = utf8_encode(htmlspecialchars_decode($category));
		$rows[] = array('id'=>$categoryID, 'name'=>$category);
	}
}

echo $json->encode(array('success'=>true, 'rows'=>$rows));
?>