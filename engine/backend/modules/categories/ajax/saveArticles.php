<?php
define('sAuthFile', 'sGUI');
define('sConfigPath',"../../../../../");
include("../../../../backend/php/check.php");
$result = new checkLogin();
$result = $result->checkUser();
if ($result!="SUCCESS"){
	die();
}
$categoryID = (int)$_REQUEST['categoryID'];
if(!empty($_REQUEST['articleIDs']))
{
	foreach ($_REQUEST['articleIDs'] as &$value)
	{
		$value = (int) $value;
	}
	$articleIDs = $_REQUEST['articleIDs'];
}
if(!empty($_REQUEST['delete_articleIDs']))
{
	foreach ($_REQUEST['delete_articleIDs'] as &$value)
	{
		$value = (int) $value;
	}
	$delete_articleIDs = $_REQUEST['delete_articleIDs'];
}

function sGetDeepCategories($categoryIDs)
{
	if(!is_array($categoryIDs)) $categoryIDs = array($categoryIDs);
	$categories = array();
	foreach ($categoryIDs as $categoryID)
	{
		$categoryID = (int) $categoryID;
		$parentID = $categoryID;
		while ($categoryID!=1 && !empty($categoryID))
		{
			$categories[] = $categoryID;
			$sql = 'SELECT parent FROM s_categories WHERE id='.$categoryID;
			$result = mysql_query($sql);
			if($result && mysql_num_rows($result))
				$tmp = mysql_result($result, 0, 0);
			else
				break;
			$parentID = $categoryID;
			$categoryID = (int) $tmp;
		}
	}
	$categories = array_unique($categories);
	return $categories;
}

function sDeleteArticleCategories($articleIDs, $categoryIDs)
{
	if(is_array($categoryIDs))
	{
		foreach ($categoryIDs as $categoryID)
		{
			sDeleteArticleCategories($articleIDs, $categoryID);
		}
		return true;
	}
	$categoryID = (int) $categoryIDs;
	while ($categoryID)
	{
		$sql = "
			SELECT a.id
			FROM s_articles a
			LEFT JOIN s_articles_categories ac
			ON ac.categoryID IN (SELECT id FROM s_categories WHERE parent=$categoryID)
			AND ac.articleID=a.id
			WHERE a.id IN (".implode($articleIDs, ',').")
			AND ac.id IS NULL
		";
		$result = mysql_query($sql);
		if(!$result||!mysql_num_rows($result))
			break;
		$articleIDs = array();
		while ($row = mysql_fetch_row($result)) {
			$articleIDs[] = $row[0];
		}
		$sql = "
			DELETE FROM s_articles_categories
			WHERE categoryID=$categoryID
			AND articleID IN (".implode($articleIDs, ',').")
		";
		$result = mysql_query($sql);
		if(!$result)
			break;
		$sql = "
			SELECT parent
			FROM s_categories
			WHERE parent!=1
			AND parent!=id
			AND id=$categoryID
		";
		$result = mysql_query($sql);
		if(!$result||!mysql_num_rows($result))
			break;
		$categoryID = (int) mysql_result($result,0,0);
	}
}

function sInsertArticleCategories($articleIDs, $categoryIDs)
{
	$categoryIDs = sGetDeepCategories($categoryIDs);
	if(empty($categoryIDs)) return false;
	$sql = '
		INSERT INTO s_articles_categories (categoryID, articleID, categoryparentID)
		SELECT c.id as categoryID, a.id as articleID,
			IF((SELECT 1 FROM `s_categories` WHERE parent=c.id LIMIT 1),c.parent, c.id) as categoryparentID
		FROM s_articles a, s_categories c
		WHERE a.id IN ('.implode($articleIDs, ',').')
		AND c.id IN ('.implode($categoryIDs, ',').')
		ORDER BY articleID, categoryID=categoryparentID, categoryID
		ON DUPLICATE KEY UPDATE categoryparentID=VALUES(categoryparentID)
	';
	mysql_query($sql);
}

$categoryIDs = sGetDeepCategories($categoryID);
foreach ($categoryIDs as $id)
{
	$sCore->sDeletePartialCache("category",$id);
}

if(!empty($delete_articleIDs))
{
	sDeleteArticleCategories($delete_articleIDs, $categoryID);
}
if(!empty($articleIDs))
{
	sInsertArticleCategories($articleIDs, $categoryID);
}
?>