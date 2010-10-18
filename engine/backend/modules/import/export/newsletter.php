<?php
if (!defined('sAuthUser')) die();
$sql = "
	SELECT
		cm.email,
		cg.name as `group`,
		IFNULL(ub.salutation, nd.salutation) as salutation,
		IFNULL(ub.firstname, nd.firstname) as firstname,
		IFNULL(ub.lastname, nd.lastname) as lastname,
		IFNULL(ub.street, nd.street) as street,
		IFNULL(ub.streetnumber, nd.streetnumber) as streetnumber,
		IFNULL(ub.zipcode, nd.zipcode) as zipcode,
		IFNULL(ub.city, nd.city) as city,
		lastmailing,
		lastread,
		u.id as userID
	FROM s_campaigns_mailaddresses cm
	LEFT JOIN s_campaigns_groups cg
	ON cg.id=cm.groupID
	LEFT JOIN s_campaigns_maildata nd
	ON nd.email=cm.email
	LEFT JOIN s_user u
	ON u.email=cm.email
	AND u.accountmode=0
	LEFT JOIN s_user_billingaddress ub
	ON ub.userID=u.id
";
if(!empty($_REQUEST["formatID"]))
if($_REQUEST["formatID"]==1)
{
	header("Content-Type: text/x-comma-separated-values;charset=iso-8859-1");
	header('Content-Disposition: attachment; filename="newsletter.csv"');
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
?>