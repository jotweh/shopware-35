<?php
define('sAuthFile', 'sGUI');
define('sConfigPath',"../../../");
include("../../backend/php/check.php");
$result = new checkLogin();
$result = $result->checkUser();
if ($result!="SUCCESS"){
echo "<img src=\"backend/img/default/spinner.gif\">";
	die();
}
		$queryUsers = mysql_query("
		SELECT userID FROM s_statistics_currentusers 
		WHERE userID != 0
		GROUP BY remoteaddr
		ORDER BY userID DESC
		");
		while ($currentUser = mysql_fetch_array($queryUsers)){
			
				// Query User-Information
						
				$queryUserData = mysql_query("
				SELECT sessionID, company, firstname, lastname FROM s_user_billingaddress, s_user
				WHERE userID = {$currentUser["userID"]}
				AND s_user.id = userID 
				");
				
				$company = @mysql_result($queryUserData,0,"company");
				$firstname = @mysql_result($queryUserData,0,"firstname");
				$lastname = @mysql_result($queryUserData,0,"lastname");
				$session = @mysql_result($queryUserData,0,"sessionID");
				
				// Check if is something in basket
				$sql = "
				SELECT SUM(quantity * price) AS amount
				FROM s_order_basket
				WHERE userID = {$currentUser["userID"]}
				AND sessionID = '$session'
				GROUP BY sessionID
				";
				
				$getBasketAmount = mysql_query($sql);
				$basketAmount = 0;
				while ($basket = mysql_fetch_array($getBasketAmount)){
					$basketAmount+= $basket["amount"];
				}
				$dashboard[] = array("customer"=>htmlentities($firstname." ".$lastname),"amount"=>$basketAmount);
		}
	
		$queryGuests = mysql_query("
		SELECT userID FROM s_statistics_currentusers 
		WHERE userID = 0
		GROUP BY remoteaddr
		ORDER BY userID DESC
		");
		
		if (@mysql_num_rows($queryGuests)){
			$dashboard[] = array("customer"=>@mysql_num_rows($queryGuests)." Besucher","amount"=>0);
		}
		
		require_once("json.php");
		$json = new Services_JSON();
		$nodes = array();
		
		$nodes["dashboard"] = $dashboard;
		$nodes["totalCount"] = count($dashboard);
		
		echo $json->encode($nodes);
?>