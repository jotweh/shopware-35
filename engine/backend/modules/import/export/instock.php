<?php
if (!defined('sAuthUser')) die();

$sql = "
	SELECT
		IFNULL(v.ordernumber,d.ordernumber) as ordernumber,
		IFNULL(v.instock,d.instock) as instock,
		a.name as `_name`,
		d.additionaltext as `_additionaltext`,
		s.name as `_supplier`,
		REPLACE(ROUND(IFNULL(gp.price,p.price)*(100+t.tax)/100,2),'.',',') as `_price`
	FROM s_articles a

	INNER JOIN s_articles_details d
	ON d.articleID=a.id
	
	INNER JOIN s_core_tax t
	ON t.id=a.taxID

	LEFT JOIN s_articles_supplier as s
	ON a.supplierID = s.id
	
	LEFT JOIN `s_articles_groups_value` v
	ON v.articleID=a.id
	
	LEFT JOIN s_articles_groups_prices gp
	ON gp.valueID=v.valueID
	AND gp.groupkey='EK'

	LEFT JOIN s_articles_prices p
	ON p.articledetailsID=d.id
	AND p.`from`=1
	AND p.pricegroup='EK'
	
	ORDER BY a.id, d.kind, ordernumber
";

if(!empty($_REQUEST["formatID"]))
if($_REQUEST["formatID"]==1)
{
	header("Content-Type: text/x-comma-separated-values;charset=iso-8859-1");
	header('Content-Disposition: attachment; filename="instock.csv"');
	$result = $api->sDB->Execute($sql);
	$header = array_keys($result->fields);
	echo $api->convert->csv->_encode_line($header, array_keys($header));
	echo "\r\n";
	while (!$result->EOF)
	{
		echo $api->convert->csv->_encode_line($result->fields, array_keys($result->fields));
		echo "\r\n";
		$result->MoveNext();
	}
	$result->Close();
}
elseif($_REQUEST["formatID"]==2)
{
	header('Content-type: text/xml;charset=iso-8859-1');
	header('Content-Disposition: attachment; filename="instock.xml"');
	$articles = $api->sDB->GetAll($sql);
	$xmlmap = array("shopware"=>array("articles"=>array("article"=>&$articles)));
	$api->convert->xml->sSettings['encoding'] = "ISO-8859-1";
	echo $api->convert->xml->encode($xmlmap);
}
?>