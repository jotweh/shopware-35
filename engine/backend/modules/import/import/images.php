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

foreach ($articles as $article)
{
	$sConfig['sImportPosition']++;
	if($sConfig['sImportArticles']>=$sConfig['sImportPosition'])
	{
		continue;
	}
	$sConfig['sImportArticles']++;
	
	if($sConfig['sFormat']==2)
	{
		$article = (array) $article;
		utf8_array_decode($article);
	}
	
	$sql = '
		SELECT d.articleID, d.kind
		FROM s_articles_details d
		WHERE d.ordernumber LIKE ?
	';
	$config = $api->sDB->GetRow($sql,array($article['ordernumber']));
	
	$inserts = array();
	if(!empty($config['articleID'])&&!empty($article['image']))
	{
		$file = sCreateTmpFile();
		sCopyFile($article['image'], $file);
		$name = $config['articleID'].'_'.md5_file($file);
		
		$image = array(
			'articleID'=>$config['articleID'],
			'relations'=>$config['kind']==2?$article['ordernumber']:$article['relations'],
			'image'=>!sImageExists($name)?$article['image']:false,
			'name'=>$name
		);
		if(isset($article['description']))
			$image['description'] = $article['description'];
		if(isset($article['position']))
			$image['position'] = $article['position'];
		if(isset($article['main']))
			$image['main'] = $article['main'];
		elseif($config['kind']==1)
			$image['main'] = 1;
		
		$inserts[] = $import->sArticleImage($image);
		
		unlink($file);
	}
	if(!empty($config['articleID'])&&$config['kind']==1&&(empty($article['image'])||$article['main']==1))
	{
		$import->sDeleteOtherArticleImages($config['articleID'], $inserts);
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
	'text' => utf8_encode($sConfig['sImportArticles'].' von '.$sConfig['sCountArticles'].' Datensätze importiert'),
	'success' => true
));
?>