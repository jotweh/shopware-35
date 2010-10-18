<?php
if (!defined('sAuthUser')) die();

function sGetCategoryPath ($categoryID, $separator = " > ")
{
	global $api;
	$path = "";
	while (!empty($categoryID))
	{
		$sql = "
			SELECT parent, description
			FROM s_categories c
			WHERE id=$categoryID
		";
		$category = $api->sDB->GetRow($sql);
		if(empty($category))
			break;
		$category["description"] = str_replace($separator,'',htmlspecialchars_decode($category["description"]));
		$path = empty($path) ? $category["description"] : $category["description"].$separator.$path;
		$categoryID = $category["parent"];
	}
	return $path;
}

$sql_add_join = '';
$sql_add_select = '';
$sql_add_select_p = '';

if(!empty($_REQUEST['article_customergroup_prices']))
{
	$sql = "SELECT `id`, `groupkey`, `taxinput` FROM s_core_customergroups WHERE mode=0 ORDER BY id ASC";
}
else
{
	$sql = "SELECT `id`, `groupkey`, `taxinput` FROM s_core_customergroups WHERE `groupkey`='EK' AND mode=0";
}
$customergroups = $api->sDB->GetAll($sql);

if(!empty($customergroups))
foreach ($customergroups as $cg)
{
	if($cg['groupkey']=='EK')
		$cg['id'] = '';
	$sql_add_join .= "
		LEFT JOIN `s_articles_prices` p{$cg['id']}
		ON p{$cg['id']}.articledetailsID=d.id
		AND p{$cg['id']}.pricegroup='{$cg['groupkey']}'
		AND p{$cg['id']}.`from`=1
	";
	if(empty($cg['taxinput']))
		$sql_add_select_p .= "REPLACE(ROUND(p{$cg['id']}.price,2),'.',',')";
	else
		$sql_add_select_p .= "REPLACE(ROUND(p{$cg['id']}.price*(100+t.tax)/100,2),'.',',')";
		
	if($cg['groupkey']=='EK')
		$sql_add_select_p .= " as price, ";
	else
		$sql_add_select_p .= " as price_{$cg['groupkey']}, ";
}


if(!empty($_REQUEST['article_translations']))
{
	$sql = '
		SELECT DISTINCT isocode
		FROM s_core_multilanguage
		WHERE skipbackend=0
	';
	$languages = $api->sDB->GetCol($sql);
	
	$translation_fields = array("txtArtikel"=>"name","txtzusatztxt"=>"additionaltext","txtshortdescription"=>"description","txtlangbeschreibung"=>"description_long");
	for ($i=1;$i<=20;$i++)
	{
		$translation_fields["attr$i"] = "attr$i";
	}
	foreach ($languages as $language)
	{
		$sql_add_join .= "
			LEFT JOIN s_core_translations as ta_$language
			ON ta_$language.objectkey=a.id AND ta_$language.objecttype='article' AND ta_$language.objectlanguage='$language'
					
			LEFT JOIN s_core_translations as td_$language
			ON td_$language.objectkey=d.id AND td_$language.objecttype='variant' AND td_$language.objectlanguage='$language'
		";
		$sql_add_select .= ", ta_$language.objectdata as article_translation_$language";
		$sql_add_select .= ", td_$language.objectdata as detail_translation_$language";
		
		foreach ($translation_fields as $field)
		{
			$sql_add_select .= ", '' as {$field}_{$language}";
		}
	}
}
$sql = "
	SELECT
		a.id as `articleID`,
		d.ordernumber,
		d2.ordernumber as mainnumber,
		a.name,
		d.additionaltext,
		s.name as supplier,
		t.tax,
		$sql_add_select_p
		REPLACE(p.price,'.',',') as net_price,
		REPLACE(ROUND(p.pseudoprice*(100+t.tax)/100,2),'.',',') as pseudoprice,
		REPLACE(ROUND(p.pseudoprice,2),'.',',') as net_pseudoprice,
		REPLACE(ROUND(p.baseprice,2),'.',',') as baseprice,
		a.active,
		d.instock,
		d.stockmin,
		a.description,
		a.description_long,
		a.shippingtime,
		IF(a.datum='0000-00-00','',a.datum) as added,
		IF(a.changetime='0000-00-00 00:00:00','',a.changetime) as `changed`,
		IF(a.releasedate='0000-00-00','',a.releasedate) as releasedate,
		a.shippingfree,
		a.topseller,
		a.keywords,
		a.minpurchase,
		a.purchasesteps,
		a.maxpurchase,
		a.purchaseunit,
		a.referenceunit,
		a.packunit,
		a.unitID,
		a.pricegroupID,
		a.pricegroupActive,
		a.laststock,
		d.suppliernumber,
		d.impressions,
		d.sales,
		IF(file IS NULL,0,1) as esd,
		d.weight,
		u.unit,
		(SELECT GROUP_CONCAT(relatedarticle SEPARATOR '|') FROM s_articles_similar WHERE articleID=a.id) as similar,
		(SELECT GROUP_CONCAT(relatedarticle SEPARATOR '|') FROM s_articles_relationships WHERE articleID=a.id) as crosselling,
		(SELECT GROUP_CONCAT(categoryID SEPARATOR '|') FROM s_articles_categories WHERE categoryID=categoryparentID AND articleID=a.id) as categories,
		'' as categorypaths,
		(
			SELECT GROUP_CONCAT(CONCAT('http://{$api->sSystem->sCONFIG['sBASEPATH']}{$api->sSystem->sCONFIG['sARTICLEIMAGES']}/',img,'.',extension) ORDER BY `main`,  `position`  SEPARATOR '|')
			FROM `s_articles_img`
			WHERE articleID=a.id
		) as images,
		at.attr1, at.attr2, at.attr3, at.attr4, at.attr5, at.attr6, at.attr7, at.attr8, at.attr9, at.attr10, 
		at.attr11, at.attr12, at.attr13, at.attr14, at.attr15, at.attr16, at.attr17, at.attr18, at.attr19, at.attr20,
		a.filtergroupID as attributegroupID,
		'' as attributevalues,
		gs.type as configuratortype,
		IF(g.groupID,1,NULL) as configurator
		$sql_add_select
	FROM s_articles a
	
	INNER JOIN s_articles_details d
	ON d.articleID=a.id
	INNER JOIN s_articles_attributes at
	ON at.articledetailsID=d.id
	
	LEFT JOIN s_articles_details d2
	ON d.kind=2
	AND d2.articleID=d.articleID
	AND d2.kind=1
	
	LEFT JOIN `s_core_units` as u
	ON a.unitID = u.id
	LEFT JOIN s_core_tax as t
	ON a.taxID = t.id
	LEFT JOIN s_articles_supplier as s
	ON a.supplierID = s.id
	LEFT JOIN s_articles_esd e
	ON e.articledetailsID=d.id
	
	LEFT JOIN s_articles_groups g
	ON g.groupID=1
	AND g.articleID=a.id
	
	LEFT JOIN s_articles_groups_settings gs
	ON gs.articleID=a.id
	
	$sql_add_join
	WHERE a.mode = 0
	ORDER BY a.id, d.kind
";

$result = $api->sDB->Execute($sql);
$header = $result->fields; unset($header['articleID']);
$header = array_keys($header);

if(!empty($languages))
foreach ($languages as $language)
{
	unset($header['article_translation_'.$language]);
	unset($header['detail_translation_'.$language]);
}
if($_REQUEST["typID"]!=7)
{
	unset($header['categorypaths']);
}
if($_REQUEST["formatID"]==1)
{
	header("Content-Type: text/x-comma-separated-values;charset=iso-8859-1");
	header('Content-Disposition: attachment; filename="export.csv"');
	$api->convert->csv->sSettings['newline'] = "\r\n";
	echo $api->convert->csv->_encode_line($header, array_keys($header))."\r\n";
}
else
{
	header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
	header("Content-Disposition: attachment; filename=\"export.xls\"");
	require_once(dirname(dirname(__FILE__)).'/excel.php');
	$excel = new Excel();
	$excel->setTitle('Product Export');
	echo $excel->getHeader();
	echo $excel->encodeRow($header);
}

while (!$result->EOF)
{
	$result->fields['name'] = htmlspecialchars_decode($result->fields['name']);
    $result->fields['supplier'] = htmlspecialchars_decode($result->fields['supplier']);
    if(!empty($languages))
	foreach ($languages as $language)
	{
		if(!empty($result->fields['article_translation_'.$language]))
			$objectdata = unserialize($result->fields['article_translation_'.$language]);
		elseif(!empty($result->fields['detail_translation_'.$language]))
			$objectdata = unserialize($result->fields['detail_translation_'.$language]);
		else
			continue;
		
		if(!empty($objectdata))
		foreach ($objectdata as $key=>$value)
		{
			if(isset($translation_fields[$key]))
			{
				$result->fields[$translation_fields[$key].'_'.$language] = $value;
			}
		}
	}
	if(!empty($result->fields['attributegroupID']))
	{
		$sql = "
			SELECT REPLACE(GROUP_CONCAT(v.value ORDER BY r.position SEPARATOR '|'),',','.') as attributevalues
			FROM s_filter_relations r
			INNER JOIN s_filter_options o
			LEFT JOIN s_filter_values v
			ON v.groupID=r.groupID
			AND v.optionID=r.optionID
			AND v.articleID={$result->fields['articleID']}
			WHERE r.optionID = o.id
			AND r.groupID={$result->fields['attributegroupID']}
		";
		$result->fields['attributevalues'] = $api->sDB->GetOne($sql);
	}
	if(!empty($result->fields['categories'])&&$_REQUEST["typID"]==7)
	{
		$categorypaths = array();
		$categories = explode('|',$result->fields['categories']);
		foreach ($categories as $category)
		{
			$categorypath = sGetCategoryPath($category,"|");
			if(!empty($categorypath))
				$categorypaths[] = $categorypath;
		}
		$result->fields['categorypaths'] = implode("\r\n",$categorypaths);
	}
	if(!empty($result->fields['configurator']))
	{
		$sql_select = '';
		$sql_join = '';
		$sql_order = '';
		
		if(!empty($customergroups))
		foreach ($customergroups as $cg)
		{
			$sql_join .= "
				LEFT JOIN `s_articles_groups_prices` p{$cg['id']}
				ON p{$cg['id']}.valueID=v.valueID
				AND p{$cg['id']}.groupkey='{$cg['groupkey']}'
			";
			
			if(empty($cg['taxinput']))
				$sql_select .= ", REPLACE(ROUND(p{$cg['id']}.price,2),'.',',')";
			else
				$sql_select .= ", REPLACE(ROUND(p{$cg['id']}.price*(100+t.tax)/100,2),'.',',')";
				
			if($cg['groupkey']=='EK')
				$sql_select .= " as price";
			else
				$sql_select .= " as price_{$cg['groupkey']}";
		}
		
		for ($i=1;$i<=10;$i++)
		{
			$sql_select .= ", g$i.groupname AS group$i, o$i.optionname option$i";
			$sql_join .= "
				LEFT JOIN s_articles_groups_option o$i
				ON o$i.articleID=a.id
				AND o$i.optionID = v.attr$i
				LEFT JOIN s_articles_groups g$i
				ON g$i.articleID=a.id
				AND g$i.groupID =$i
			";
			
			if (!empty($sql_order)) $sql_order .= ', ';
            $sql_order .= "option$i";
		}
		
		$sql = "
			SELECT
				v.ordernumber, v.instock, v.active, v.standard
				$sql_select
			FROM `s_articles_groups_value` v
			
			INNER JOIN s_articles a
			ON a.id=v.articleID
						
			INNER JOIN s_core_tax t
			ON t.id=a.taxID
						
			$sql_join
		
			WHERE a.id={$result->fields['articleID']}
			
			ORDER BY $sql_order
		";
		$variants = $api->sDB->GetAll($sql);
		$result->fields['configurator'] = '';
		foreach ($variants as $variant)
		{
			
			$line = $variant['ordernumber'].'|'
				. $variant['instock'].','.$variant['active'].','.$variant['standard'].'|'
				. $variant['price'].'|';
			for ($i=1;$i<=10;$i++)
			{
				if(empty($variant['group'.$i])||empty($variant['option'.$i])) continue;
				if($i!=1) $line .= ', ';
				$line .= str_replace(array(':',','),'',$variant['group'.$i]).': ';
				$line .= str_replace(array(':',','),'',$variant['option'.$i]);
			}
			if(!empty($customergroups))
			foreach ($customergroups as $cg)
			{
				if(!empty($variant['price_'.$cg['groupkey']]))
				{
					$line .= '|'.$cg['groupkey'].':'.$variant['price_'.$cg['groupkey']];
				}
			}
			$line .= "\r\n";
			$result->fields['configurator'] .= $line;
		}
	}
	
	
	if($_REQUEST["formatID"]==1)
	{
		echo $api->convert->csv->_encode_line($result->fields, $header)."\r\n";
	}
	else
	{
		$row = array();
		foreach ($header as $key)
		{
			$row[] = isset($result->fields[$key]) ? $result->fields[$key] : '';
		}
		echo $excel->encodeRow($row);
	}
	
	$result->MoveNext();
}
$result->Close();

if($_REQUEST["formatID"]!=1)
{
	echo $excel->getFooter();
}

?>