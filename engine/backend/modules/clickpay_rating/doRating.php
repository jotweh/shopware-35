<?php
define('sAuthFile', 'sGUI');
define('sConfigPath',"../../../../");
include("../../../backend/php/check.php");
$result = new checkLogin();
$result = $result->checkUser();
if ($result!="SUCCESS"){
echo "
	<html><title>Time-Out</title><head></head>
	<script language=\"javascript\">
	parent.location.reload();
	</script>
	<body>Bitte loggen Sie sich neu ein!</body></html>
	";
	die();
}
session_write_close();
mysql_close();

require_once(dirname(dirname(dirname(dirname(__FILE__)))).'/connectors/clickpay/clickpay.class.php');

$sClickPay = new sClickPay(false);
$sErrorMessages = array();

if(!empty($_REQUEST['sUserID']))
{
	$sRequestURL = 'https://www.eos-payment.de/bonitaetprivat.acgi';
	
	$sql = '
		SELECT
			REPLACE(u.email,\'@\',\'[at]\') as bo_referenz,
			IF(IFNULL(s.salutation,b.salutation)=\'ms\',\'Frau\',\'Herr\') as bo_anrede,
			IFNULL(s.firstname,b.firstname) as bo_vname,
			IFNULL(s.lastname,b.lastname) as bo_nname,
			IF(b.birthday AND b.birthday!=\'0000-00-00\',DATE_FORMAT(b.birthday,\'%d.%m.%Y\'),NULL) as bo_gebdatum,
			IFNULL(s.street,b.street) as bo_strasse,
			IFNULL(s.streetnumber,b.streetnumber) as bo_hausnummer,
			IFNULL(s.zipcode,b.zipcode) as bo_plz,
			IFNULL(s.city,b.city) as bo_ort
		FROM s_user u
		INNER JOIN s_user_billingaddress b
		ON b.userID=u.id
		LEFT JOIN s_user_shippingaddress s
		ON s.userID=u.id
		AND 1=2
		WHERE u.id=?
	';
	$sAddress = $sClickPay->sDB_CONNECTION->GetRow($sql,array($_REQUEST['sUserID']));
	
	$sParams = array();
	$sParams["bo_haendlerid"] = $sClickPay->sGetConfig('sCLICKPAYMERCHANTID');
	$sParams["bo_haendlercode"] = $sClickPay->sGetConfig('sCLICKPAYMERCHANTCODE');
	$sParams["bo_anbieter"] = $sClickPay->sGetConfig('sCLICKPAYRISKPROVIDER');
	$sParams["bo_referenz"] = $sAddress["bo_referenz"];
	$sParams["bo_anrede"] = $sAddress["bo_anrede"];
	$sParams["bo_vname"] = $sAddress["bo_vname"];
	$sParams["bo_nname"] = $sAddress["bo_nname"];
	if($sAddress["bo_gebdatum"])
		$sParams["bo_gebdatum"] = $sAddress["bo_gebdatum"];
	$sParams["bo_strasse"] = $sAddress["bo_strasse"];
	$sParams["bo_hausnummer"] = $sAddress["bo_hausnummer"];
	$sParams["bo_plz"] = $sAddress["bo_plz"];
	$sParams["bo_ort"] = $sAddress["bo_ort"];
	$sParams["bo_warten"] = 25;
	
	$sParamsHash = md5(implode('|',$sParams));
	
	$sRespone = $sClickPay->sDoRequest($sRequestURL,$sParams);
	
	$sql = '
		INSERT INTO `eos_risk_results` (`reference`, `hash`, `result`, `added`)
		VALUES (?,?,?,NOW());
	';
	$sClickPay->sDB_CONNECTION->Execute($sql,array(
		$sRespone["bo_referenz"],
		$sParamsHash,
		$sRespone['azdindex']
	));
	
	if(!empty($sRespone['status'])&&$sRespone['status']=='ERROR')
	{
		foreach (array_keys($sParams) as $field)
		{
			if(!empty($sRespone[$field]))
			{
				$sErrorMessage = $sClickPay->sGetClickPayErrorMessage($sRespone[$field],$field);
				if(!empty($sErrorMessage))
					$sErrorMessages[] = $sErrorMessage;
			}
		}
		if(empty($sErrorMessages))
		{
			$sErrorMessages[] = 'Es ist ein unbekannter Fehler aufgetreten';
		}
	}
	sleep(1);
}

require_once('../../../backend/ajax/json.php');
$json = new Services_JSON();
echo $json->encode(array(
	'sErrorMessage'=>implode('<br />',$sErrorMessages)
));
?>