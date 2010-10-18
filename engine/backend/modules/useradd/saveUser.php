<?php
define('sAuthFile', 'sGUI');
define('sConfigPath',"../../../../");
include("../../../backend/php/check.php");
$result = new checkLogin();
$result = $result->checkUser();
if ($result!="SUCCESS"){
	die("FAIL");
}

require_once("../../../backend/ajax/json.php");
$json = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);

$forms 				= $_POST["forms"];
$tbls 				= $_POST["tbls"];

$sPOST["payment"] = intval($_POST["payment"]);
$forms = $json->decode($forms);
foreach ($forms as $key=>$form)
{
	$sPOST[$key] = mysql_real_escape_string(htmlspecialchars(utf8_decode($form)));
}
$pw 				= md5($sPOST["sBillingPassword"]);
$sql_check = sprintf("SELECT *
				FROM `s_user`
				WHERE `email` = CONVERT( _utf8 '%s'
				USING latin1 )
				COLLATE latin1_swedish_ci",
				$sPOST["sBillingEmail"]);
$query_check = mysql_query($sql_check);
if(mysql_num_rows($query_check) != 0)
{
	echo "email_exist";
}else{
	
	$sql = "INSERT INTO `s_user` 
			(`password` , 
			`email` , 
			`active` , 
			`firstlogin` , 
			`paymentID` , 
			`customergroup` ,
			`validation`, 
			`language`,
			`subshopID` )
			VALUES (
			'{$pw}', 
			'{$sPOST["sBillingEmail"]}', 
			'1', 
			now(), 
			'{$sPOST["payment"]}', 
			'{$sPOST["sCustomerGroup"]}', 
			'',
			'de',
			'{$sPOST["sMultiShop"]}')";
	mysql_query($sql);
	$user_id = mysql_insert_id();
	echo $user_id;
	
	if($tbls)
	{
		$tbls = json_decode($tbls);
		foreach ($tbls as $key=>$tbl)
		{
			$sqlFields = "";
			$sqlValues = "";
			$sqlTbl = $key;
			
			$sqlFields = "`userID`";
			$sqlValues = "'".$user_id."'";
			
			foreach($tbls->$key as $field => $value)
			{
				$sqlVal = $tbls->$key->$field->value;
				$sqlFields .= " ,`".$field."`";
				$sqlValues .= " ,'".$sqlVal."'";
			}
			$sql = sprintf("INSERT INTO `%s` 
			(%s) VALUES (%s)",
			$sqlTbl,
			mysql_real_escape_string(htmlspecialchars(utf8_decode($sqlFields))),
			mysql_real_escape_string(htmlspecialchars(utf8_decode($sqlValues)))
			);
			
			mysql_query($sql);
		}
	}
	
	$sql2 = "INSERT INTO `s_user_billingaddress` 
			(`userID` , `company` , `department` , `salutation` , `firstname` , 
			`lastname` , `street` , `streetnumber` , `zipcode` , `city` , `phone` , `fax` , `countryID` , 
			`ustid`)
			VALUES (
			'{$user_id}', 
			'{$sPOST["sBillingCompany"]}', 
			'{$sPOST["sBillingDepartment"]}', 
			'{$sPOST["sBillingTitle"]}', 
			'{$sPOST["sBillingFirstname"]}', 
			'{$sPOST["sBillingLastname"]}', 
			'{$sPOST["sBillingStreet"]}', 
			'{$sPOST["sBillingHouseN"]}', 
			'{$sPOST["sBillingZipcode"]}', 
			'{$sPOST["sBillingCity"]}', 
			'{$sPOST["sBillingPhone"]}', 
			'{$sPOST["sBillingFax"]}', 
			'{$sPOST["sBillingCountry"]}', 
			'{$sPOST["sBillingTax"]}'
			)";
	mysql_query($sql2);
	
	$sql3 = "INSERT INTO `s_user_shippingaddress` ( `userID` , `company` , `department` , `salutation` , 
			`firstname` , `lastname` , `street` , `streetnumber` , `zipcode` , `city` , `countryID`)
			VALUES (
			'{$user_id}',
			'{$sPOST["sDeliveryCompany"]}', 
			'{$sPOST["sDeliveryDepartment"]}', 
			'{$sPOST["sDeliveryTitle"]}', 
			'{$sPOST["sDeliveryFirstname"]}', 
			'{$sPOST["sDeliveryLastname"]}', 
			'{$sPOST["sDeliveryStreet"]}', 
			'{$sPOST["sDeliveryHouseN"]}', 
			'{$sPOST["sDeliveryZipcode"]}', 
			'{$sPOST["sDeliveryCity"]}', 
			'{$sPOST["sDeliveryCountry"]}'
			)";
	mysql_query($sql3);
}
?>