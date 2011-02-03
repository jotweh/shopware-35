<?php
if (!defined('sAuthUser')) die();

if($sConfig['sFormat']==1)
{
	require_once('csv.php');
	$categories = new CsvIterator($sConfig['sFilePath'],';');
}
elseif($sConfig['sFormat']==2)
{
	$xml = simplexml_load_file($sConfig['sFilePath'], 'SimpleXMLElement', LIBXML_NOCDATA);
	$categories = $xml->categories->category;
}

$categoryIDs = array();
foreach ($categories as $category)
{
	if($sConfig['sFormat']==2)
	{
		$category = (array) $category;
		utf8_array_decode($category);
	}
	$category['id'] = $category['categoryID'];
	$category['parent'] = $category['parentID'];
	$categoryID = $import->sCategory($category);
	if(!empty($categoryID)) $categoryIDs[] = $categoryID;
}

if($sConfig['sDeleteCategories']==2)
{
	$import->sDeleteOtherCategories($categoryIDs);
}
echo $json->encode(array(
	'sConfig' => $sConfig,
	'progress' => $sConfig['sTyp']==1 ? 0 : 1,
	'text' => utf8_encode(count($categoryIDs).' Kategorien wurden importiert'),
	'success' => true
));
?>