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
	}
	
	$sql = 'UPDATE s_articles_groups_value SET instock=? WHERE ordernumber=?';
	$api->sDB->Execute($sql,array($article['instock'],$article['ordernumber']));
	$sql = 'UPDATE s_articles_details SET instock=? WHERE ordernumber=?';
	$api->sDB->Execute($sql,array($article['instock'],$article['ordernumber']));
	
	if(time()-$sConfig['sRequestTime'] >= $sConfig['sMaxExecutionTime'] || $sConfig['sImportArticles'] % $sConfig['sImportStep'] == 0)
	{
		break;
	}
}

echo $json->encode(array(
	'sConfig' => $sConfig,
	'progress' => $sConfig['sImportArticles']/$sConfig['sCountArticles'],
	'text' => utf8_encode($sConfig['sImportArticles'].' von '.$sConfig['sCountArticles'].' Datensätze importiert'),
	'success' => true
));
?>