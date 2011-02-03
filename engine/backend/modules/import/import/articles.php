<?php
if (!defined('sAuthUser')) die();

if($sConfig['sImportArticles']==0&&$sConfig['sFormat']==2&&!empty($xml->categories->category))
{
	require('categories.php');
	exit();
}

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

if(empty($sConfig['sImportCategories'])&&$sConfig['sImportArticles']==0&&$sConfig['sFormat']==2&&!empty($xml->categories->category))
{
	$sConfig['sImportCategories'] = 1;
	require('categories.php');
	exit();
}

$sql = "SELECT `groupkey` as `key`, `id`, `groupkey`, `taxinput` FROM s_core_customergroups WHERE mode=0 ORDER BY id ASC";
$customergroups = $api->sDB->GetAssoc($sql);

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
	
	if(!empty($article['ordernumber'])) unset($article['articleID'],$article['articledetailsID']);
	if(!empty($article['supplier'])) unset($article['supplierID']);
	if($sConfig['sFormat']==1)
	{
		if(isset($article['images'])&&$sConfig['sArticleImages'])
			$article['images'] = explode('|', $article['images']);
		if(isset($article['categories']))
			$article['categories'] = explode('|', $article['categories']);
		if(isset($article['crosselling']))
			$article['crosselling'] = explode('|', $article['crosselling']);
		if(isset($article['similar']))
			$article['similar'] = explode('|', $article['similar']);
		if(isset($article['attributevalues']))
			$article['attributevalues'] = empty($article['attributevalues']) ? array() : explode('|', '|'.$article['attributevalues']);
		if(isset($article['configurator']))
		{
			$variants = array();
			$values = explode("\n",$article['configurator']);
			foreach ($values as $value)
			{
				$value = explode('|',trim($value));
				if(count($value)<4) continue;
				$value[1] = explode(',',$value[1]);
				$value[3] = explode(',',$value[3]);
				$variant = array(
					'ordernumber' => $value[0],
					'instock' => $value[1][0]
				);
				if(isset($value[1][1]))
					$variant['active'] = $value[1][1];
				if(isset($value[1][2]))
					$variant['standard'] = $value[1][2];
				for ($i=0,$c=count($value[3]);$i<$c;$i++)
				{
					$value[3][$i] = explode(':',$value[3][$i]);
					$variant['group'.($i+1)] = $value[3][$i][0];
					$variant['option'.($i+1)] = $value[3][$i][1];
				}
				$variant['prices'] = array();
				$variant['prices']['EK'] = $value[2];
				for ($i=4;isset($value[$i]);$i++)
				{
					if(empty($value[$i])) continue;
					$value[$i] = explode(':',$value[$i]);
					if(!isset($value[$i][1])) continue;
					$variant['prices'][$value[$i][0]] = $value[$i][1];
				}
				foreach ($variant['prices'] as $key => $price)
				{
					$price = $import->sValFloat($price);
					if(!isset($customergroups[$key]))
					{
						unset($variant['prices'][$key]);
					}
					elseif(!empty($customergroups[$key]['taxinput']))
					{
						$variant['prices'][$key] = $price/(100+$article['tax'])*100;
					}
				}
				$variants[] = $variant;
			}
			$article['configurator'] = $variants;
		}
		if(isset($article['categorypaths']))
		{
			$article['categories'] = array();
			$article['categorypaths'] = explode("\n",$article['categorypaths']);
			foreach ($article['categorypaths'] as $categorypath)
			{
				$categorypath = trim($categorypath);
				if(empty($categorypath)) continue;
				$categories = explode("|",$categorypath);
				$categoryID = 0;
				foreach ($categories as $category)
				{
					if(empty($category)) break;
					$categoryID = $import->sCategory(array(
						"description" => $category,
						"parent" => $categoryID
					));
				}
				if(empty($categoryID)) continue;
				$article['categories'][] = $categoryID;
			}
			unset($article['categorypaths']);
		}
	}
	elseif($sConfig['sFormat']==2)
	{
		if(isset($article['attributes']))
		{
			$article['attr'] = array();
			foreach ($article['attributes']->attribute as $attr)
			{
				$article['attr'][(int) $attr['id']] = utf8_decode((string) $attr);
			}
			unset($article['attributes']);
		}
		if(isset($article['crossellings']))
		{
			$article['crosselling'] = array();
			foreach ($article['crossellings']->crosselling as $value)
			{
				$article['crosselling'][] = utf8_decode((string) $value);
			}
			unset($article['crossellings']);
		}
		if(isset($article['similars']))
		{
			$article['similar'] = array();
			foreach ($article['similars']->similar as $value)
			{
				$article['similar'][] = utf8_decode((string) $value);
			}
			unset($article['similars']);
		}
		if(isset($article['prices']))
		{
			$prices = array();
			foreach ($article['prices']->price as $price)
			{
				$price = (array) $price;
				utf8_array_decode($price);
				$prices[] = $price;
			}
			$article['prices'] = $prices;
		}
		if(isset($article['links']))
		{
			$links = array();
			foreach ($article['links']->link as $link)
			{
				$link = (array) $link;
				utf8_array_decode($link);
				$links[] = $link;
			}
			$article['links'] = $links;
		}
		if(isset($article['downloads']))
		{
			$downloads = array();
			foreach ($article['downloads']->download as $download)
			{
				$download = (array) $download;
				utf8_array_decode($download);
				$downloads[] = $download;
			}
			$article['downloads'] = $downloads;
		}
		if(isset($article['categories']))
		{
			$categories = array();
			foreach ($article['categories']->category as $category)
			{
				$categories[] = (int) $category;
			}
			$article['categories'] = $categories;
		}
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
		if(isset($article['configurator']->type))
		{
			$article['configuratortype'] = (int) $article['configurator']->type;
		}
		if(isset($article['configurator']))
		{
			$values = array();
			foreach ($article['configurator']->values->value as $value)
			{
				$prices = array();
				foreach ($value->prices->price as $price)
				{
					$price = (array) $price;
					utf8_array_decode($price);
					$prices[] = $price;
				}
				$value = (array) $value;
				utf8_array_decode($value);
				$value['prices'] = $prices;
				$values[] = $value;
			}
			$article['configurator'] = $values;
		}
		if(isset($article['attributevalues']))
		{
			$values = array();
			foreach ($article['attributevalues']->attributevalue as $value)
			{
				$values[(int) $value['id']] = utf8_decode((string) $value);
			}
			$article['attributevalues'] = $values;
		}
	}
	
	$article_insert = $import->sArticle($article);
	
	
	if(empty($article_insert))
	{
		continue;
	}
	$article = array_merge($article, $article_insert);
	
	if($sConfig['sFormat']==1)
	{
		$import->sArticleTranslation($article);
	}
	elseif($sConfig['sFormat']==2&&isset($article['translations']))
	{
		if($article['kind']==1)
		{
			$translate_type = 'article';
			$translate_key = $article['articleID'];
		}
		else
		{
			$translate_type = 'variant';
			$translate_key = $article['articledetailsID'];
		}
		foreach ($article['translations']->translation as $translation)
		{
			$translation = (array) $translation;
			utf8_array_decode($translation);
			$import->sTranslation($translate_type, $translate_key, $translation['language'], $translation);
		}
	}
		
	if(!empty($customergroups))
	foreach ($customergroups as $cg)
	{
		if($cg['groupkey']=='EK'&&isset($article['net_price']))
		{
			$import->sArticlePrice(array(
				'price' => $article['net_price'],
				'articledetailsID' => $article['articledetailsID'],
				'pricegroup' => $cg['groupkey'],
				'baseprice' => isset($article['baseprice']) ? $article['baseprice'] : 0,
				'pseudoprice' => isset($article['net_pseudoprice']) ? $article['net_pseudoprice'] : 0
			));
		}
		elseif ($cg['groupkey']=='EK'&&isset($article['price']))
		{
			$import->sArticlePrice(array(
				'price' => $article['price'],
				'articledetailsID' => $article['articledetailsID'],
				'pricegroup' => $cg['groupkey'],
				'baseprice' => isset($article['baseprice']) ? $article['baseprice'] : 0,
				'pseudoprice' => isset($article['pseudoprice']) ? $article['pseudoprice'] : 0,
				'tax' =>  empty($cg['taxinput']) ? 0 : $article['tax']
			));
		}
		elseif(isset($article['price_'.$cg['groupkey']]))
		{
			$import->sArticlePrice(array(
				'price' => $article['price_'.$cg['groupkey']],
				'articledetailsID' => $article['articledetailsID'],
				'pricegroup' => $cg['groupkey'],
				'tax' =>  empty($cg['taxinput']) ? 0 : $article['tax']
			));
		}
	}
	
	if(isset($article['prices']))
	{
		foreach ($article['prices'] as &$price)
		{
			if(!empty($customergroups[$price['pricegroup']]['taxinput']))
			{
				if(isset($price['net_price'])&&!isset($price['price']))
				{
					$price['price'] = $price['net_price']*(100+$article['tax'])/100;
				}
				if(isset($price['net_pseudoprice'])&&!isset($price['pseudoprice']))
				{
					$price['pseudoprice'] = $price['net_pseudoprice']*(100+$article['tax'])/100;
				}
				$price['tax'] = $article['tax'];
			}
			else
			{
				if(isset($price['net_price'])&&!isset($price['price']))
				{
					$price['price'] = $price['net_price'];
				}
				if(isset($price['net_pseudoprice'])&&!isset($price['pseudoprice']))
				{
					$price['pseudoprice'] = $price['net_pseudoprice'];
				}
			}
			
			$price['articledetailsID'] = $article['articledetailsID'];
			$import->sArticlePrice($price);
		}
	}
	if($article['kind']==1)
	{
		//if(isset($article['images'])&&$sConfig['sArticleImages'])
		//	$import->sArticleImages((int) $article['articleID'], $article['images']);
		if(isset($article['categories']))
			$import->sArticleCategories((int) $article['articleID'], $article['categories']);
		if(isset($article['crosselling']))
			$import->sArticleCrossSelling((int) $article['articleID'], $article['crosselling']);
		if(isset($article['similar']))
			$import->sArticleSimilar((int) $article['articleID'], $article['similar']);
		if(isset($article['links']))
		{
			$import->sDeleteArticleLinks((int) $article['articleID']);
			foreach ($article['links'] as $link)
			{
				$link['articleID'] = $article['articleID'];
				$import->sArticleLink($link);
			}
		}
		if(isset($article['downloads']))
		{
			$import->sDeleteArticleDownloads((int) $article['articleID']);
			foreach ($article['downloads'] as $download)
			{
				$download['articleID'] = $article['articleID'];
				$import->sArticleDownload($download);
			}
		}
		if(isset($article['attributegroupID']))
		{
			$import->sArticleAttributeGroup(array(
				'articleID' => (int) $article['articleID'],
				'attributegroupID' => (int) $article['attributegroupID'],
				'values' => $article['attributevalues']
			));
		}
		if(isset($article['configurator']))
		{
			$import->sArticleConfigurator(array(
				'articleID' => (int) $article['articleID'],
				'values' => $article['configurator'],
				'type' => $article['configuratortype'],
				'tax' => $article['tax'],
			));
		}
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

if($sConfig['sImportArticles']==$sConfig['sCountArticles']&&$sConfig['sArticleImages'])
{
	$sConfig['sImportArticles'] = 0;
	$sConfig['sTyp'] = 8;
	echo $json->encode(array(
		'sConfig' => $sConfig,
		'progress' => 0,
		'text' => utf8_encode('Bilder-Datensätze werden importiert'),
		'success' => true
	));
}
else
{
	echo $json->encode(array(
		'sConfig' => $sConfig,
		'progress' => $sConfig['sImportArticles']/$sConfig['sCountArticles'],
		'text' => utf8_encode($sConfig['sImportArticles'].' von '.$sConfig['sCountArticles'].' Artikel-Datensätze importiert'),
		'success' => true
	));
}
?>