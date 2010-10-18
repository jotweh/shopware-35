<?php
if (!defined('sAuthUser')) die();

$sql = "
	SELECT
		IFNULL(v.ordernumber,d.ordernumber) as ordernumber,
		REPLACE(ROUND(IFNULL(gp.price,p.price)*IF(cg.taxinput=1,(100+t.tax)/100,1),2),'.',',') as price,
		IFNULL(gp.groupkey, p.pricegroup) as pricegroup,
		IF(p.`from`=1,NULL,p.`from`) as `from`,
		REPLACE(ROUND(p.pseudoprice*IF(cg.taxinput=1,(100+t.tax)/100,1),2),'.',',') as pseudoprice,
		REPLACE(ROUND(p.baseprice,2),'.',',') as baseprice,
		a.name as `_name`,
		d.additionaltext as `_additionaltext`,
		s.name as `_supplier`
	FROM s_articles a

	INNER JOIN s_articles_details d
	ON d.articleID=a.id

	INNER JOIN (SELECT 1 as k UNION ALL SELECT 2 as k) m
	ON m.k=1 OR (SELECT 1 FROM s_articles_groups WHERE articleID=a.id LIMIT 1)
	
	INNER JOIN s_core_tax t
	ON t.id=a.taxID
	
	LEFT JOIN s_articles_supplier as s
	ON a.supplierID = s.id
	
	LEFT JOIN `s_articles_groups_value` v
	ON v.articleID=a.id
	AND m.k=2
	
	LEFT JOIN s_articles_groups_prices gp
	ON gp.valueID=v.valueID

	LEFT JOIN s_articles_prices p
	ON p.articledetailsID=d.id
	AND v.valueID IS NULL
	
	INNER JOIN s_core_customergroups cg
	ON IFNULL(p.pricegroup, gp.groupkey)=cg.groupkey
	
	ORDER BY a.id, d.kind, ordernumber, `from`
";

if(!empty($_REQUEST["formatID"]))
if($_REQUEST["formatID"]==1)
{
	header("Content-Type: text/x-comma-separated-values;charset=iso-8859-1");
	header('Content-Disposition: attachment; filename="prices.csv"');
	$result = $api->sDB->Execute($sql);
	$header = array_keys($result->fields);
	echo $api->convert->csv->_encode_line($header, array_keys($header))."\r\n";
	while (!$result->EOF)
	{
		$result->fields['_name'] = htmlspecialchars_decode($result->fields['_name']);
		$result->fields['_additionaltext'] = htmlspecialchars_decode($result->fields['_additionaltext']);
		$result->fields['_supplier'] = htmlspecialchars_decode($result->fields['_supplier']);
		echo $api->convert->csv->_encode_line($result->fields, array_keys($result->fields))."\r\n";
		$result->MoveNext();
	}
	$result->Close();
}
?>