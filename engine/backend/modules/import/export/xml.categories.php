<?php
if (!defined('sAuthUser')) die();

$xml =& $api->convert->xml;
$xml->sSettings['encoding'] = "ISO-8859-1";

$categories = $export->sCategories();
$xmlmap = array("shopware"=>array("categories"=>array("category"=>&$categories)));

header('Content-type: text/xml');
header('Content-Disposition: attachment; filename="export.xml"');
echo $xml->encode($xmlmap);
?>