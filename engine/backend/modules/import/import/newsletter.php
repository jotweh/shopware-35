<?php
if (!defined('sAuthUser')) die();

if($sConfig['sFormat']==1)
{
	require_once('csv.php');
	$customers = new CsvIterator($sConfig['sFilePath'],';');
}

$count = 0;
foreach ($customers as $customer)
{
	if(!isset($customer['groupID']))
	{
		$customer['groupID'] = $sConfig['sValueGroup'];
	}
	if(sNewsletter($customer))
	{
		$count++;
	}
}

function sNewsletter($newsletter)
{
	global $api;
	
	$newsletter['email'] = trim($newsletter['email']);
	if(empty($newsletter['email'])) return false;
	
	$sql = 'SELECT id FROM s_user WHERE accountmode=0 AND email=?';
	$newsletter['userID'] = $api->sDB->GetOne($sql, array($newsletter['email']));
	
	if(!empty($newsletter['group']))
	{
		$sql = 'SELECT id FROM s_campaigns_groups WHERE name=?';
		$newsletter['groupID'] = $api->sDB->GetOne($sql, array($newsletter['group']));
		
		if(empty($newsletter['groupID']))
		{
			$sql = 'INSERT INTO s_campaigns_groups (`name`) VALUES (?);';
			$api->sDB->Execute($sql, array($newsletter['group']));
			$newsletter['groupID'] = $api->sDB->Insert_ID();
		}
	}
	if(empty($newsletter['groupID']))
	{
		return false;
	}
	
	$sql = 'SELECT `id` FROM `s_campaigns_mailaddresses` WHERE `groupID`=? AND `email`=?';
	$newsletter['mailaddressID'] = $api->sDB->GetOne($sql, array($newsletter['groupID'], $newsletter['email']));
	if(empty($newsletter['mailaddressID']))
	{
		$sql = '
			INSERT INTO s_campaigns_mailaddresses (
				`customer`,
				`groupID`,
				`email`
			)
			VALUES (
				?, ?, ?
			);
		';
		$api->sDB->Execute($sql,array(!empty($newsletter['userID']), $newsletter['groupID'], $newsletter['email']));
		$newsletter['mailaddressID'] = $api->sDB->Insert_ID();
	}
	if(!empty($newsletter['mailaddressID']))
	{
		$fields = array('firstname', 'lastname', 'salutation', 'title', 'street', 'streetnumber', 'zipcode', 'city');
		foreach ($fields as $field)
		{
			if(isset($newsletter[$field]))
			{
				$upset[] = $field."=".$api->sDB->qstr($newsletter[$field]);
			}
		}
		if(!empty($upset))
		{
			$sql = '
				INSERT IGNORE INTO s_campaigns_maildata (
					`email`,
					`groupID`,
					`added`
				)
				VALUES (
					?, ?, '.$api->sDB->sysTimeStamp.'
				);
			';
			$api->sDB->Execute($sql,array($newsletter['email'], $newsletter['groupID']));
			
			$upset = implode(", ",$upset);
			$sql = "
				UPDATE s_campaigns_maildata 
				SET $upset
				WHERE email=".$api->sDB->qstr($newsletter["email"])."
				AND groupID=".$api->sDB->qstr($newsletter["groupID"])."
			";
			$api->sDB->Execute($sql);
		}
	}
	return true;
}

$sConfig['sImportArticles'] = $sConfig['sCountArticles'];
echo $json->encode(array(
	'sConfig' => $sConfig,
	'progress' => 1,
	'text' => utf8_encode($count.' Newsletter-Empfänger wurden importiert'),
	'success' => true
));
?>