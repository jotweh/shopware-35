<?php
define('sAuthFile', 'sGUI');
define('sConfigPath',"../../../../");
include("../../../backend/php/check.php");
$result = new checkLogin();
$result = $result->checkUser();
if ($result!="SUCCESS"){
	die();
}

require_once('../../../backend/ajax/json.php');
$json = new Services_JSON();


if(!empty($_REQUEST["search"]))
{
	$search = mysql_real_escape_string(trim(utf8_decode($_REQUEST["search"])));
	$sql_where = "WHERE (
			b.customernumber LIKE '".$search."%'
		OR
			u.email LIKE '".$search."%'
		OR
			b.lastname LIKE '".$search."%'
		OR
			s.lastname LIKE '".$search."%'
		OR
			b.zipcode LIKE '".$search."%'
		OR
			s.zipcode LIKE '".$search."%'
	)";
}
if(!empty($_REQUEST["sort"]))
{
	$sort = mysql_real_escape_string(trim(utf8_decode($_REQUEST["sort"])));
	$sql_sort = "ORDER BY $sort";
}
else
{
	$sql_sort = "ORDER BY name";
}
if(!empty($_REQUEST["dir"])&&$_REQUEST["dir"]=='DESC')
{
	$sql_sort .= " DESC" ;
}

	$sql = "
		SELECT SQL_CALC_FOUND_ROWS
			u.id as userID,
			b.customernumber,
			u.email as email,
			IF(IFNULL(s.salutation,b.salutation)='ms','Frau','Herr') as salutation,
			IFNULL(s.lastname,b.lastname) as lastname,
			CONCAT(IFNULL(s.firstname,b.firstname),' ',IFNULL(s.lastname,b.lastname)) as name,
			CONCAT(IFNULL(s.street,b.street),' ',IFNULL(s.streetnumber,b.streetnumber)) as adress,
			CONCAT(IFNULL(s.zipcode,b.zipcode),' ',IFNULL(s.city,b.city)) as adress2,
			UNIX_TIMESTAMP(MAX(e.added)) as risk_date,
			(SELECT result FROM eos_risk_results WHERE added=MAX(e.added) LIMIT 1) as risk_result
		FROM s_user u
		INNER JOIN s_user_billingaddress b
		ON b.userID=u.id
		LEFT JOIN s_user_shippingaddress s
		ON s.userID=u.id
		AND 1=2
		LEFT JOIN eos_risk_results e
		ON e.reference=REPLACE(u.email,'@','[at]')
		$sql_where
		GROUP BY u.id
		$sql_sort
	";
	
	$rows = array();
	$result = mysql_query($sql);
	if($result&&mysql_num_rows($result))
	while($row = mysql_fetch_assoc($result))
	{
		$row['adress'] = trim(utf8_encode($row['adress'])).', '.trim(utf8_encode($row['adress2']));
		$row['name'] = $row['salutation'].' '.trim(utf8_encode($row['name']));
		$row['lastname'] = trim(utf8_encode($row['lastname']));
		$row['city'] = trim(utf8_encode($row['city']));		
		$rows[] = $row;
	}
	$sql = 'SELECT FOUND_ROWS() as count';
	$result = mysql_query($sql);
	if($rows&&$result&&mysql_num_rows($result))
		$count = (int) mysql_result($result,0,"count");
	else 
		$count = 0;
	echo  $json->encode(array("users"=>$rows,"count"=>$count));
?>