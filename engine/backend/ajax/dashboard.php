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

	if (empty($_SESSION["sSidebar"])) die("Auth failure");
	// Amounts
	$queryAmountToday = mysql_query("
	SELECT sum(invoice_amount/currencyFactor) AS amount FROM s_order
	WHERE TO_DAYS(ordertime) = TO_DAYS(now())
	AND status != 4 AND status != -1
	");
	
	$queryAmountYesterday = mysql_query("
	SELECT sum(invoice_amount/currencyFactor) AS amount FROM s_order
	WHERE  TO_DAYS( ordertime ) = (TO_DAYS( NOW( ) )-1) 
	AND status != 4 AND status != -1
	");
	
	
	$amountToday = @mysql_result($queryAmountToday,0,"amount") ? @mysql_result($queryAmountToday,0,"amount") : 0;
	$amountYesterday = @mysql_result($queryAmountYesterday,0,"amount") ? @mysql_result($queryAmountYesterday,0,"amount") : 0;
	$amountToday = round($amountToday,2);
	
	$amountYesterday = round($amountYesterday,2);
	
	// Count orders
	$queryOrdersToday = mysql_query("
	SELECT COUNT(*) AS orders FROM s_order
	WHERE TO_DAYS( ordertime ) = TO_DAYS( NOW( ) ) 
	AND status != 4 AND status != -1
	");
	$queryOrdersYesterday = mysql_query("
	SELECT COUNT(*) AS orders FROM s_order
	WHERE TO_DAYS( ordertime ) = (TO_DAYS( NOW( ) )-1) 
	AND status != 4 AND status != -1
	");
	
	$ordersToday = @mysql_result($queryOrdersToday,0,"orders") ? @mysql_result($queryOrdersToday,0,"orders") : 0;
	$ordersYesterday = @mysql_result($queryOrdersYesterday,0,"orders") ? @mysql_result($queryOrdersYesterday,0,"orders") : 0;

	
	// Count new clients
	$newClientsToday = mysql_query("
	SELECT count(id) AS newUsers FROM s_user 
	 WHERE TO_DAYS( firstlogin ) = TO_DAYS( NOW( ) ) 
 	 GROUP BY TO_DAYS( NOW( ) )  
	");
	
	$newClientsYesterday = mysql_query("
	SELECT count(id) AS newUsers FROM s_user 
	 WHERE TO_DAYS( firstlogin ) = (TO_DAYS( NOW( ) )-1) 
 	 GROUP BY TO_DAYS( NOW( ) )  
	");
		
	$newUsersToday = @mysql_result($newClientsToday,0,"newUsers") ? @mysql_result($newClientsToday,0,"newUsers") : 0;
	$newUsersYesterday = @mysql_result($newClientsYesterday,0,"newUsers") ? @mysql_result($newClientsYesterday,0,"newUsers") : 0;
	

	// Visitors and Hits Today/Yesterday
	$queryHitsToday = mysql_query("
	SELECT pageimpressions, uniquevisits FROM s_statistics_visitors WHERE datum = CURDATE()
	");
	
	$visitsToday =  @mysql_result($queryHitsToday,0,"uniquevisits") ? @mysql_result($queryHitsToday,0,"uniquevisits") : 0;
	$hitsToday = @mysql_result($queryHitsToday,0,"pageimpressions") ? @mysql_result($queryHitsToday,0,"pageimpressions") : 0;
	
	$queryHitsYesterday = mysql_query("
	SELECT pageimpressions, uniquevisits FROM s_statistics_visitors WHERE datum = DATE_SUB(CURDATE(),INTERVAL 1 DAY)
	");
	
	$hitsYesterday =  @mysql_result($queryHitsYesterday,0,"pageimpressions");
	$visitsYesterday = @mysql_result($queryHitsYesterday,0,"uniquevisits");
	
	// Umsatz
	$dashboard[] = array("desc"=>"Umsatz",
	"today"=>$sCore->sFormatPrice($amountToday),
	"yesterday"=>$sCore->sFormatPrice($amountYesterday)
	);
	
	// Bestellungen
	$dashboard[] = array("desc"=>"Bestellungen",
	"today"=>$ordersToday,
	"yesterday"=>$ordersYesterday
	);
	
	// Neukunden
	$dashboard[] = array("desc"=>"Neukunden",
	"today"=>$newUsersToday,
	"yesterday"=>$newUsersYesterday
	);
	
	// Besucher
	$dashboard[] = array("desc"=>"Besucher",
	"today"=>$visitsToday,
	"yesterday"=>$visitsYesterday
	);
	
	// Besucher
	$dashboard[] = array("desc"=>"Seitenaufrufe",
	"today"=>$hitsToday,
	"yesterday"=>$hitsYesterday
	);
	
	$queryUsersUnlock = mysql_query("
	SELECT id FROM s_user 
	WHERE
		validation != ''
	");
	if (@mysql_num_rows($queryUsersUnlock)){
		$dashboard[] = array("desc"=>"Freizuschalten",
		"today"=>mysql_num_rows($queryUsersUnlock),
		"yesterday"=>0,
		"merchants"=>true,
		"link"=>"loadSkeleton('userunlock')"
		);
	}
	
	require_once("json.php");
	$json = new Services_JSON();
	$nodes = array();
	
	$nodes["dashboard"] = $dashboard;
	$nodes["totalCount"] = count($dashboard);
	
	echo $json->encode($nodes);
?>