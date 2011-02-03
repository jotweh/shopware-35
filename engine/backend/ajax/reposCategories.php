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


require_once("json.php");
$json = new Services_JSON();
$_REQUEST["nodes"] = stripslashes($_REQUEST["nodes"]);
$data = $json->decode($_REQUEST["nodes"]);

if (!empty($data))
{
	//$log = "";
	foreach ($data as $row)
	{
		if (empty($row->position) || empty($row->id))
			continue;
			
		$row->position = intval($row->position);
		$row->id = intval($row->id);
		$sql = "
			UPDATE s_categories SET position=$row->position WHERE id=$row->id 
		";
		$result = mysql_query($sql);

		if(!$result)
			continue;
			
		
		
		if (empty($row->oldParentID)||empty($row->parentID))
			continue;

			
		$new = intval($row->parentID);
		$old = intval($row->oldParentID);

		$sql = "
			UPDATE s_categories SET parent=$new WHERE parent=$old AND id=$row->id 
		";
		//$log .=  $sql."\n";
		$result = mysql_query($sql);
		if(!$result||!mysql_affected_rows())
			continue;
		
		$newpath = test($new,$old);
		$newpath[] = $new;
		$oldpath = test($old,$new);
		$oldpath[] = $old;
		$addpath = array_values(array_diff($newpath,$oldpath));
		$delpath = array_values(array_diff($oldpath,$newpath));
		
		//$log .= "Neu: ".implode(" > ",$newpath)."\n";
		//$log .= "Alt: ".implode(" > ",$oldpath)."\n";
		
		//$log .= "Anlegen ". implode(" > ",$addpath)."\n";
		//$log .= "Löschen ". implode(" > ",$delpath)."\n";
				
		$sql = "
			INSERT IGNORE INTO s_articles_categories
			SELECT NULL, ac.articleID, c.id, c.parent
			FROM s_articles_categories ac, s_categories c
			WHERE ac.categoryID=$row->id 
			AND c.id IN (".implode(",",$addpath).")
		";
		//$log .=  $sql."\n";
		mysql_unbuffered_query($sql);
		
		for ($i=count($delpath)-1;$i>=0;$i--)
		{
			if($i==count($delpath)-1)
			{
				$sql = "AND c2.id !=$row->id ";
				$sql2 = "";
			}	
			else
			{
				$sql = "";
				$sql2 = "";
			}
			$sql = "
				SELECT ac.articleID
				FROM `s_articles_categories` ac
				JOIN s_articles_categories ac3 ON ac.articleID = ac3.articleID AND ac3.categoryID=$row->id 
				LEFT JOIN s_categories c2 ON c2.parent = ac.categoryID $sql
				LEFT JOIN s_articles_categories ac2 ON ac.articleID = ac2.articleID AND ac2.categoryID=c2.id
				AND c2.id = ac2.categoryID
				WHERE ac.categoryID={$delpath[$i]}
				GROUP BY ac.articleID
				HAVING COUNT(ac2.categoryID) < 1
			";
			echo $sql;
			//$log .=  $sql."\n";
			$articleIDs = array();
			$result = mysql_query($sql);
			if($result&&mysql_num_rows($result)) {
				while ($row = mysql_fetch_row($result)) {
					$articleIDs[] = $row[0];
				}
			}
			if(!empty($articleIDs)) {
				$sql = "DELETE FROM s_articles_categories WHERE categoryID={$delpath[$i]} AND articleID IN (".implode(",",$articleIDs).")";
				//$log .=  $sql."\n";
				mysql_query($sql);
			}
		}
		//$result = mysql_query($sql);
		//if(!$result||!mysql_affected_rows())
		//	continue;
		
		//if($results == test(22150,1) //
		//elseif 
		//Hole löschbare Artikel
		/*$sql = implode(", ",$delpath);
		$sql = "
			SELECT ac.articleID, ac.categoryID, c2.id, c2.description
			FROM `s_articles_categories` ac
			LEFT JOIN s_categories c2 ON c2.parent = ac.categoryID AND c2.id NOT IN ($sql)
			LEFT JOIN s_articles_categories ac2 ON ac.articleID = ac2.articleID AND ac2.categoryID=c2.id
			AND c2.id = ac2.categoryID
			WHERE ac.categoryID IN ($sql)
			AND c2.id IS NULL
		";
		echo $sql;
		*/
		//$sql = "SELECT DISTINCT articleID FROM s_articles_categories WHERE categoryID=$row->id";
	}
	//echo $log;
	//file_put_contents("../../../cache/vars/test.txt",$log);
}
function test ($start, $ziel)
{
	$sql = "SELECT parent FROM s_categories WHERE id=".intval($start);
	$result = mysql_query($sql);
	if($result&&mysql_num_rows($result))
		$tmp = (int) mysql_result($result,0,0);
	if(empty($tmp))//||(is_int($ziel)&&$tmp===$ziel)||(is_array($ziel)&&in_array($tmp,$ziel))) 
		return array();//empty($tmp) ?  array() : array($tmp);
	return array_merge(test($tmp, $ziel),array($tmp));
}
?>