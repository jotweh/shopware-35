<?php
define('sAuthFile', 'sGUI');
define('sConfigPath',"../../../");
include("../../backend/php/check.php");
$result = new checkLogin();
$result = $result->checkUser();
if ($result!="SUCCESS"){
echo "FAIL";
	die();
}
// *****************
require_once("json.php");
$json = new Services_JSON();

$nodes = array();

$_POST["start"] = intval($_POST["start"]);
$_POST["limit"] = intval($_POST["limit"]);

$_POST["sort"] = mysql_escape_string($_POST["sort"]);
$_POST["dir"] = mysql_escape_string($_POST["dir"]);



 
if(isset($_REQUEST["orderNumber"])) {
	if(!$_POST["sort"]) {
		$_POST["sort"] = "date";
	}
	if(!$_POST["dir"]) {
		$_POST["dir"] = "DESC";
	}
	
	if ($_POST["search"]) {
		if (strlen($_POST["search"])>1){
			$search = "%".mysql_real_escape_string($_POST["search"])."%";
		}else {
			$search = mysql_real_escape_string($_POST["search"])."%";
		}
		$searchSQL = "
		 AND
		 	(mail LIKE '$search'
	 	 OR
		 	s_user_billingaddress.firstname LIKE '$search'
	 	 OR
		 	s_user_billingaddress.lastname LIKE '$search')
		";
	}
	
	$oNumber = mysql_real_escape_string($_REQUEST["orderNumber"]);
	$sql = "SELECT mail, concat(s_user_billingaddress.firstname,', ',s_user_billingaddress.lastname) as userName, s_user.id as userID, send, DATE_FORMAT(date, '%d.%m.%Y - %h:%i:%s') AS date, s_articles_notification.ordernumber as aOrdernumber, 
		send AS notificated
		FROM s_articles_notification
		LEFT JOIN  s_user ON (s_user.email  = s_articles_notification.mail)
		LEFT JOIN  s_user_billingaddress ON (s_user.id  = s_user_billingaddress.userID)

       WHERE ordernumber = '{$oNumber}'
		$searchSQL
       ORDER BY {$_POST["sort"]} {$_POST["dir"]}
        ";
	$getNotifcationCustomer = mysql_query($sql);
	if (@mysql_num_rows($getNotifcationCustomer)){
		while ($notifCustomer=mysql_fetch_assoc($getNotifcationCustomer)) {
			$notifCustomer["mail"] = stripslashes(utf8_encode($notifCustomer["mail"]));
			$notifCustomer["userID"] = intval($notifCustomer["userID"]);
			$notifCustomer["userName"] = stripslashes(utf8_encode($notifCustomer["userName"]));
			$notifCustomer["send"] = intval($notifCustomer["send"]);
			$nodes["notifications"][] = $notifCustomer;
		}
		$nodes["totalCount"] = mysql_num_rows($getNotifcationCustomer);
	}
	echo $json->encode($nodes);
	exit();

}

if ($_POST["search"]) {
	if (strlen($_POST["search"])>1){
		$search = "%".mysql_real_escape_string($_POST["search"])."%";
	}else {
		$search = mysql_real_escape_string($_POST["search"])."%";
	}
	$searchSQL = "
	 WHERE
	 	s_articles.name LIKE '$search'
	 OR
		s_articles_notification.ordernumber LIKE '$search'
	";
}

$sql = "
		SELECT s_articles_notification.ordernumber as aOrdernumber, s_articles.name as productName, count(s_articles.name) AS registered,
		(SELECT count(id) FROM s_articles_notification WHERE send = 0 AND ordernumber = aOrdernumber ) AS notNotificated
		FROM s_articles_notification
	        LEFT JOIN  s_articles_groups_value ON (s_articles_groups_value.ordernumber  = s_articles_notification.ordernumber)
	        LEFT JOIN  s_articles_details ON (s_articles_notification.ordernumber  = s_articles_details.ordernumber OR s_articles_groups_value.articleID  = s_articles_details.articleID)
	        LEFT JOIN  s_articles ON (s_articles_details.articleID  = s_articles.id)
		$searchSQL
		GROUP BY s_articles_notification.ordernumber
		ORDER BY {$_POST["sort"]} {$_POST["dir"]}
		";
$getAllNotifcations = mysql_query($sql);
$sql .= "LIMIT {$_POST["start"]}, {$_POST["limit"]}";
$getNotifcations = mysql_query($sql);

if (@mysql_num_rows($getNotifcations)){
	while ($notif=mysql_fetch_assoc($getNotifcations)) {
		$notif["notNotificated"] = intval($notif["notNotificated"]);
		$notif["aOrdernumber"] = stripslashes(utf8_encode($notif["aOrdernumber"]));
		$notif["registered"] = intval($notif["registered"]);
		$notif["productName"] = stripslashes(utf8_encode($notif["productName"]));
		$nodes["notifications"][] = $notif;
	}
	$nodes["totalCount"] = mysql_num_rows($getAllNotifcations);
}
else {
	$nodes["votes"] = array();
	$nodes["totalCount"] = 0;
}

echo $json->encode($nodes);

?>