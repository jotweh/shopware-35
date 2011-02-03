<?php
if (!defined('sAuthUser')) die();

$sql = "
	SELECT
		d.ordernumber,
		CONCAT('http://{$api->sSystem->sCONFIG['sBASEPATH']}{$api->sSystem->sCONFIG['sARTICLEIMAGES']}/',img,'.',extension) as image,
		ai.main, ai.description, ai.position, ai.width, ai.height, IF(d.kind=1,ai.relations,'') as relations
	FROM s_articles a
	INNER JOIN s_articles_details d
	ON d.articleID=a.id
		
	LEFT JOIN s_articles_img ai
	ON ai.articleID=a.id
	AND (
			ai.relations=d.ordernumber
		OR (
				d.kind=1 
			AND
				(relations='' OR relations LIKE '&{%}' OR relations LIKE '||{%}')
		)
	)
	ORDER BY a.id, d.kind, d.ordernumber, ai.main, ai.position
";
if(!empty($_REQUEST["formatID"]))
if($_REQUEST["formatID"]==1)
{
	header("Content-Type: text/x-comma-separated-values;charset=iso-8859-1");
	header('Content-Disposition: attachment; filename="images.csv"');
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
	header('Content-Disposition: attachment; filename="images.xml"');
	$articles = $api->sDB->GetAll($sql);
	$xmlmap = array("shopware"=>array("articles"=>array("article"=>&$articles)));
	$api->convert->xml->sSettings['encoding'] = "ISO-8859-1";
	echo $api->convert->xml->encode($xmlmap);
}
?>