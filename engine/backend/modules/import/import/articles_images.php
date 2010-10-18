<?php
if (!defined('sAuthUser')) die();
if($sConfig['sFormat']==1)
{
	require_once('csv.php');
	$articles = new CsvIterator($sConfig['sFilePath'],';');
}
elseif($sConfig['sFormat']==2)
{
	$xml = simplexml_load_file($sConfig['sFilePath'], 'SimpleXMLElement', LIBXML_NOCDATA);
	$articles = $xml->articles->article;
}
else
{
	exit();
}
foreach ($articles as $article)
{
	if($sConfig['sFormat']==2)
	{
		$article = (array) $article;
		utf8_array_decode($article);
	}
	if(empty($article['ordernumber'])&&empty($article['articleID'])&&empty($article['articledetailsID']))
		continue;
	$sConfig['sImportPosition']++;
	if($sConfig['sImportArticles']>=$sConfig['sImportPosition'])
	{
		continue;
	}
	$sConfig['sImportArticles']++;
	
	if(!empty($article['ordernumber']))
		$sql = 'ordernumber = '.$api->sDB->qstr((string)$article['ordernumber']);
	elseif(!empty($article['articledetailsID']))
		$sql = 'id = '. (int) $article['articledetailsID'];
	elseif(!empty($article['articleID']))
		$sql = 'articleID = '. (int) $article['articleID'];
		
	$sql = "SELECT articleID FROM s_articles_details WHERE kind=1 AND $sql";
	$article['articleID'] = $api->sDB->GetOne($sql);

	if(empty($article['articleID']))
	{
		continue;
	}

	if($sConfig['sFormat']==1)
	{
		if(isset($article['images'])&&$sConfig['sArticleImages'])
		{
			$article['images'] = explode('|', $article['images']);
		}
	}
	elseif($sConfig['sFormat']==2)
	{
		if(isset($article['images']))
		{
			$images = array();
			foreach ($article['images']->image as $image)
			{
				$image = (array) $image;
				utf8_array_decode($image);
				$images[] = $image;
			}
			$article['images'] = $images;
		}
	}
	$inserts = array();
	if(!empty($article['images']))
	{
		foreach ($article['images'] as $key=>&$image)
		{
			if(!is_array($image)) $image = array('image'=>$image);
			if(isset($image['link']))
			{
				$image['image'] = $image['link'];
				unset($image['link']);
			}
			$file = sCreateTmpFile();
			sCopyFile($image['image'], $file);
			$image['articleID'] = $article['articleID'];
			$image['name'] = $article['articleID'].'_'.md5_file($file);
			if(sImageExists($image['name']))
				unset($image['image']);
			else
				$image['image'] = $file;
			if(!isset($image['main'])&&empty($key))
				$image['main'] = 1;
			$inserts[] = $import->sArticleImage($image);
			@unlink($file);
		}
	}
	if(isset($article['images']))
	{
		$import->sDeleteOtherArticleImages((int) $article['articleID'], $inserts);
	}
	
	if(time()-$sConfig['sRequestTime'] >= $sConfig['sMaxExecutionTime'] || $sConfig['sImportArticles'] % $sConfig['sImportStep'] == 0)
	{
		break;
	}
}

if(empty($sConfig['sImportErrors']))
	$sConfig['sImportErrors'] = array();
else
	$sConfig['sImportErrors'] = unserialize($sConfig['sImportErrors']);

$errors = $api->sGetErrors();
foreach ($errors as $error)
{
	if(isset($sConfig['sImportErrors'][$error['code']]))
		$sConfig['sImportErrors'][$error['code']]++;
	else
		$sConfig['sImportErrors'][$error['code']] = 1;
}

$sConfig['sImportErrors'] = serialize($sConfig['sImportErrors']);

echo $json->encode(array(
	'sConfig' => $sConfig,
	'progress' => $sConfig['sImportArticles']/$sConfig['sCountArticles'],
	'text' => utf8_encode($sConfig['sImportArticles'].' von '.$sConfig['sCountArticles'].' Bild-Datensätze importiert'),
	'success' => true
));
?>