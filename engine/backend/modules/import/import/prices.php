<?php
if (!defined('sAuthUser')) die();

$sql = "SELECT `groupkey` as `key`, `id`, `groupkey`, `taxinput` FROM s_core_customergroups WHERE mode=0 ORDER BY id ASC";
$customergroups = $api->sDB->GetAssoc($sql);

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
	
	$sql = '
		SELECT articleID, t.tax
		FROM s_articles_details ad
		JOIN s_articles a
		ON a.id=ad.articleID
		JOIN s_core_tax t
		ON t.id=a.taxID
		WHERE ordernumber LIKE ?
	';
	$config = $api->sDB->GetRow($sql,array($article['ordernumber']));
	if(empty($config))
	{
		$sql = '
			SELECT valueID, articleID, t.tax
			FROM s_articles_groups_value gv
			JOIN s_articles a
			ON a.id=gv.articleID
			JOIN s_core_tax t
			ON t.id=a.taxID
			WHERE ordernumber LIKE ?
		';
		$config = $api->sDB->GetRow($sql,array($article['ordernumber']));
	}
	
	if(empty($article['pricegroup'])) $article['pricegroup'] = 'EK';
	$article['price'] = $import->sValFloat($article['price']);	
	
	if(!empty($customergroups[$article['pricegroup']]['taxinput']))
	{
		$article['price'] = $article['price']/(100+$config['tax'])*100;
		if(isset($article['pseudoprice']))
		{
			$article['pseudoprice'] = $article['pseudoprice']/(100+$config['tax'])*100;
		}
	}
	
	if(!empty($config['valueID']))
	{
		if(!empty($article['price']))
		{
			$sql = '
				INSERT INTO s_articles_groups_prices (articleID, valueID, groupkey, price)
				VALUES (?, ?, ?, ?)
				ON DUPLICATE KEY UPDATE price=VALUES(price)
			';
			$api->sDB->Execute($sql,array($config['articleID'], $config['valueID'], $article['pricegroup'], $article['price']));
		}
		else
		{
			$sql = 'DELETE FROM s_articles_groups_prices WHERE articleID=? AND valueID=? AND groupkey=?';
			$api->sDB->Execute($sql,array($config['articleID'], $config['valueID'], $article['pricegroup']));
		}
	}
	else
	{
		$import->sArticlePrice($article);
	}
	
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