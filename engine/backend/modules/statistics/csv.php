<?php
define('sAuthFile', 'sGUI');
define('sConfigPath',"../../../../");
include("../../../backend/php/check.php");
$result = new checkLogin();
$result = $result->checkUser();
if ($result!="SUCCESS"){
echo "FAIL";
	die();
}
$_REQUEST["table"] = 1;
$_REQUEST["csv"] = 1;

	$fieldmark = "";
	$separator = ";";
	function sConArray ($array, $strip)
	{
		$rep = array ("\r\n", "\n", "\r");
		$rep = array_merge($rep,$strip);
		foreach ($array as $key => $value)
			$ret[$key] = str_replace($rep," ",html_entity_decode(strip_tags(trim($value))));
		return $ret;
	}
	function sCreateCSV ($products, &$csv, $fieldmark, $separator)
	{	
		$keys = array_keys(current($products));
		$lastkey = end($keys);
		$csv .= $fieldmark;
		$csv .= implode($fieldmark.$separator.$fieldmark,sConArray($keys, array($fieldmark, $separator)));
		$csv .= $fieldmark;
		$csv .= "\r\n";
		foreach ($products as $product)
		{
			$csv .= $fieldmark;
			//$csv .= implode($fieldmark.$separator.$fieldmark,sConArray($product, array($fieldmark, $separator)));
			$product = sConArray($product, array($fieldmark, $separator));
			foreach ($keys as $key)
			{
				$csv .= $fieldmark;
				if(!empty($product[$key]))
					$csv .= $product[$key];
				$csv .= $fieldmark;
				if($lastkey!=$key)
					$csv .= $separator.$fieldmark;
			}
			$csv .= "\r\n";
		}
	}
	header('Content-type: text/x-comma-separated-values');
	header("Content-Disposition: attachment; filename=\"{$_REQUEST['chart']}.csv\"");
	
	$csv = true;
	$url = "data/{$_REQUEST['chart']}.php";
	include($url);

	if (!empty($data))
	{
		$csv = "";
		sCreateCSV($data,&$csv, $fieldmark, $separator);
		echo $csv;
	}
?>