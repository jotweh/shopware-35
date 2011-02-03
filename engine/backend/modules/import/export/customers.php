<?php
if (!defined('sAuthUser')) die();

$sql = "
	SELECT
		`b`.`customernumber`,
		`u`.`email`,
		'' as `password`,
		`u`.`password` as `md5_password`,
		`b`.`company` as `billing_company`,
		`b`.`department` as `billing_department`,
		`b`.`salutation` as `billing_salutation`,
		`b`.`firstname` as `billing_firstname`,
		`b`.`lastname` as `billing_lastname`,
		`b`.`street` as `billing_street`,
		`b`.`streetnumber` as `billing_streetnumber`,
		`b`.`zipcode` as `billing_zipcode`,
		`b`.`city` as `billing_city`,
		`b`.`phone`, 
		`b`.`fax`, 
		`b`.`countryID` as `billing_countryID`,
		bc.countryname as billing_country,
		bc.countryiso as billing_countryiso, 
		`b`.`ustid`,
		`b`.`text1` as `billing_text1`, 
		`b`.`text2` as `billing_text2`, 
		`b`.`text3` as `billing_text3`, 
		`b`.`text4` as `billing_text4`, 
		`b`.`text5` as `billing_text5`,
		`b`.`text6` as `billing_text6`,
		`s`.`company` as `shipping_company`,
		`s`.`department` as `shipping_department`,
		`s`.`salutation` as `shipping_salutation`,
		`s`.`firstname` as `shipping_firstname`,
		`s`.`lastname` as `shipping_lastname`,
		`s`.`street` as `shipping_street`,
		`s`.`streetnumber` as `shipping_streetnumber`,
		`s`.`zipcode` as `shipping_zipcode`,
		`s`.`city` as `shipping_city`,
		`s`.`countryID` as `shipping_countryID`,
		sc.countryname as shipping_country,
		sc.countryiso as shipping_countryiso,
		`s`.`text1` as `shipping_text1`, 
		`s`.`text2` as `shipping_text2`, 
		`s`.`text3` as `shipping_text3`, 
		`s`.`text4` as `shipping_text4`, 
		`s`.`text5` as `shipping_text5`,
		`s`.`text6` as `shipping_text6`,
		`u`.`paymentID`,
		`u`.`newsletter` ,
		`u`.`affiliate` ,
		`u`.`customergroup`,
		`u`.`language`,
		`u`.`subshopID`
	
	FROM 
		`s_user` as `u`
	LEFT JOIN `s_user_billingaddress` as `b` ON (`b`.`userID`=`u`.`id`) 
	LEFT JOIN `s_user_shippingaddress` as `s` ON (`s`.`userID`=`u`.`id`) 
	LEFT JOIN s_core_countries bc ON bc.id = b.countryID
	LEFT JOIN s_core_countries sc ON sc.id = s.countryID
";
if(!empty($_REQUEST["formatID"]))
if($_REQUEST["formatID"]==1)
{
	header("Content-Type: text/x-comma-separated-values;charset=iso-8859-1");
	header('Content-Disposition: attachment; filename="customers.csv"');
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
	header('Content-Disposition: attachment; filename="customers.xml"');
	$customers = $api->sDB->GetAll($sql);
	$xmlmap = array("shopware"=>array("customers"=>array("customer"=>&$customers)));
	$api->convert->xml->sSettings['encoding'] = "ISO-8859-1";
	echo $api->convert->xml->encode($xmlmap);
}
?>