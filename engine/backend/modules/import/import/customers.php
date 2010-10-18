<?php
if (!defined('sAuthUser')) die();

if($sConfig['sFormat']==1)
{
	require_once('csv.php');
	$customers = new CsvIterator($sConfig['sFilePath'],';');
}
elseif($sConfig['sFormat']==2)
{
	$xml = simplexml_load_file($sConfig['sFilePath'], 'SimpleXMLElement', LIBXML_NOCDATA);
	$customers = $xml->customers->customer;
}

$customerIDs = array();
foreach ($customers as $customer)
{
	if($sConfig['sFormat']==2)
	{
		$customer = (array) $customer;
	}
	$result = $import->sCustomer($customer);
	if(!empty($result['userID'])) $customerIDs[] = $result['userID'];
}

$sConfig['sImportArticles'] = $sConfig['sCountArticles'];
echo $json->encode(array(
	'sConfig' => $sConfig,
	'progress' => 1,
	'text' => utf8_encode(count($customerIDs).' Kunden wurden importiert'),
	'success' => true
));
?>