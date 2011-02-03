<?php
if (!defined('sAuthUser')) die();

$export =& $api->export->shopware;
$xml =& $api->convert->xml;
$mapping =& $api->convert->mapping;
$xml->sSettings['encoding'] = "ISO-8859-1";

$translation_fields = array("txtArtikel"=>"name","txtzusatztxt"=>"additionaltext","txtshortdescription"=>"description","txtlangbeschreibung"=>"description_long");
for ($i=1;$i<=20;$i++)
{
	$translation_fields["attr$i"] = "attr$i";
}
$sql = '
	SELECT DISTINCT isocode
	FROM s_core_multilanguage
	WHERE skipbackend=0
';
$languages = $api->sDB->GetCol($sql);

$pricemask = array(
		"pricegroup",
		"from",
		"baseprice",
		"percent",
		"net_pseudoprice",
		"pseudoprice",
		"net_price",
		"price"
);
$pricemask = $mapping->prepare_mask ($pricemask);
	
$imagemask = array(
	"main",
	"description",
	"position",
	"width",
	"height",
	"link",
	"relations"
);
$imagemask = $mapping->prepare_mask ($imagemask);

$articlemask = array (
	//"articledetailsID",
	//"articleID",
	"ordernumber",
	"mainnumber" => array("isset"),
	"name",
	"description",
	"description_long",
	"added",
	"changed",
	"releasedate",
	"shippingtime",
	"shippingfree",
	"topseller",
	//"free",
	"keywords",
	"minpurchase",
	"purchasesteps",
	"maxpurchase",
	"purchaseunit",
	"referenceunit",
	"packunit",
	"suppliernumber",
	//"kind",
	"additionaltext",
	"active",
	"instock",
	"stockmin",
	"esd",
	"weight",
	//"taxID",
	//"supplierID",
	"unitID",
	"tax",
	"supplier",
	"unit",
	"attributegroupID",
	"pricegroupID",
	"pricegroupActive",
	//"mainID" => array("isset"),
	//"maindetailsID" => array("isset"),
	"attributes" => array(
		"isset",
		"field" => "attr",
		"convert" => array(
			"key_as_atr"=>"id",
			"put_value" => "attribute"
		)
	),
	"downloads" => array(
		"isset",
		"convert" => array(
			"put_value" => "download"
		)
	),
	"links" => array(
		"isset",
		"field" => "information",
		"convert" => array(
			"put_value" => "link"
		)
	),
	"images" => array(
		"isset",
		"convert" => array(
			"put_value" => "image"
		)
	),
	"prices" => array(
		"isset",
		"convert" => array(
			"put_value" => "price"
		)
	),
	"crossellings" => array(
		"isset",
		"convert" => array(
			"put_value" => "crosselling"
		)
	),
	"similars" => array(
		"isset",
		"convert" => array(
			"put_value" => "similar"
		)
	),
	"categories" => array(
		"isset",
		"convert" => array(
			"put_value" => "category"
		)
	),
	"configurator" => array(
		"empty"
	),
	"translations" => array(
		"empty",
		"field" => "translations",
		"convert" => array(
			"put_value" => "translation"
		)
	),
	"attributevalues" => array(
		"isset",
		"field" => "attributevalues",
		"convert" => array(
			"key_as_atr"=>"id",
			"put_value" => "attributevalue"
		)
	),
);
$articlemask = $mapping->prepare_mask ($articlemask);

$articles = $export->sFullArticles();


header('Content-type: text/xml;charset=iso-8859-1');
header('Content-Disposition: attachment; filename="export.xml"');
echo "<?xml version=\"1.0\" encoding=\"ISO-8859-1\" standalone=\"yes\"?>\r\n<shopware>\r\n";
if($_REQUEST["typID"]==7)
{
	$categories = $export->sCategories();
	echo $xml->_encode(array("categories"=>array("category"=>&$categories)));
}
echo "<articles>\r\n";
foreach ($articles as &$article)
{
	if(empty($article['mainnumber']))
	{
		$sql = "";
		for ($i=1;$i<=10;$i++)
		{
			$sql .= ", g$i.groupname AS group$i, o$i.optionname option$i";
		}
		$sql = "
			SELECT
				v.valueID, v.standard, v.active,
				v.ordernumber, v.instock, p.price as net_price,
				ROUND(p.price*(100+{$article["tax"]})/100,2) as price
				$sql
			FROM `s_articles_groups_value` v
			
			LEFT JOIN s_articles_groups_prices p
			ON p.valueID=v.valueID
			AND groupkey='EK'
		";
		for ($i=1;$i<=10;$i++)
		{
			$sql .= "
				LEFT JOIN s_articles_groups_option o$i ON o$i.articleID = v.articleID
				AND o$i.optionID = v.attr$i
				LEFT JOIN s_articles_groups g$i ON g$i.articleID = v.articleID
				AND g$i.groupID =$i
			";
		}
		$sql .= "
			WHERE v.articleID={$article['articleID']}
		";
		$result = $api->sDB->Execute($sql);
		
		if($result)
		while ($row = $result->FetchRow())
		{
			for($i=1;$i<=10;$i++)
			{
				if (empty($row["option$i"])||empty($row["group$i"]))
				{
					unset($row["option$i"], $row["group$i"]);
				}
			}
			$sql = "
				SELECT
					groupkey as pricegroup, price as net_price,
					ROUND(price*(100+{$article["tax"]})/100,2) as price
				FROM s_articles_groups_prices
				WHERE valueID={$row["valueID"]}
				AND groupkey!='EK'
			";
			$prices = $api->sDB->GetAll($sql);
			if(!empty($prices))
				$row["prices"]["price"] = $prices;
				
			if(empty($article["configurator"]["values"]["value"]))
				$article["configurator"] = array("values"=>array("value"=>array($row)));
			else
				$article["configurator"]["values"]["value"][] = $row;
		}
	}
	
	if(!empty($_REQUEST['article_translations'])&&!empty($languages))
	{
		if(empty($article['mainnumber']))
			$where = " objecttype='article' AND objectkey=".$article['articleID'];
		else
			$where = " objecttype='variant' AND objectkey=".$article['articledetailsID'];
		$sql = "
			SELECT objectlanguage as language, objectdata as data
			FROM s_core_translations
			WHERE $where
		";
		$translations = $api->sDB->GetAssoc($sql);
	
		$article['translations'] = array();
		foreach ($languages as $language)
		{
			$article['translations'][$language] = array('language'=>$language);
			foreach ($translation_fields as $field)
			{
				$article['translations'][$language][$field] = null;
			}
			if(!empty($article['mainnumber']))
			{
				unset($article['translations'][$language]['name']);
				unset($article['translations'][$language]['description']);
				unset($article['translations'][$language]['description_long']);
			}
			if(empty($translations[$language])) continue;
			$translation = unserialize($translations[$language]);
			if(empty($translation)) continue;
			foreach ($translation as $key=>$value)
			{
				if(empty($translation_fields[$key])) continue;
				$article['translations'][$language][$translation_fields[$key]] = $value;
			}
		}
		$article['translations'] = array_values($article['translations']);
	}
	if(!empty($article['attributegroupID']))
	{
		$sql = "
			SELECT v.value
			FROM s_filter_relations r
			INNER JOIN s_filter_options o
			LEFT JOIN s_filter_values v
			ON v.groupID=r.groupID
			AND v.optionID=r.optionID
			AND v.articleID={$article['articleID']}
			WHERE r.optionID = o.id
			AND r.groupID={$article['attributegroupID']}
			ORDER BY r.position
		";
		$article['attributevalues'] = $api->sDB->GetCol($sql);
		$article['attributevalues'] = array_combine(range(1,count($article['attributevalues'])),$article['attributevalues']);
	}
	
	if(!empty($article['configurator']))
	{
		$sql = 'SELECT type FROM s_articles_groups_settings	WHERE articleID=?';
		$article['configurator']['type'] = (int) $api->sDB->GetOne($sql,array($article['articleID']));
	}

	$article = $mapping->convert_line($articlemask, $article, true);

	if(!empty($article["prices"]["price"]))
	foreach ($article["prices"]["price"] as &$price)
	{
		$price = $mapping->convert_line($pricemask, $price, true);
	}
	if(!empty($article["images"]["image"]))
	foreach ($article["images"]["image"] as &$image)
	{
		$image = $mapping->convert_line($imagemask, $image, true);
	}
	
	echo $xml->_encode(array("article"=>&$article));
}
echo "</articles>\r\n";
echo "</shopware>";
/*
$xmlmap = array("shopware"=>array("articles"=>array("article"=>&$articles)));
header('Content-type: text/xml');
header('Content-Disposition: attachment; filename="export.xml"');
echo $xml->encode($xmlmap);
*/
?>