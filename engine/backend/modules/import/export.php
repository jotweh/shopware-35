<?php
/**
 * Shopware Import
 *
 * @author      Heiner Lohaus (hl@shopware.ag>			
 * @package     Shopware 2.08.01
 * @subpackage  API
 */

//set_time_limit(0);
ini_set('memory_limit','2046M');
ini_set('max_execution_time','9999999');
ini_set('display_errors',0);
error_reporting(0);

require_once('../../../connectors/api/api.php');
$api = new sAPI();
$export =& $api->export->shopware;
$id = isset($_REQUEST[session_name()]) ? $_REQUEST[session_name()] : $_COOKIE[session_name()];

$sql = 'SELECT id FROM s_core_auth WHERE sessionID=? AND lastlogin>=DATE_SUB(NOW(),INTERVAL 60*90 SECOND)';
$result = $api->sDB->GetOne($sql,array($id));
if (empty($result))
{
	exit;
}
define('sAuthUser', $result);

if(empty($_REQUEST["formatID"])) $_REQUEST["formatID"] = 1;

if($_REQUEST["formatID"]==1)
{
	if(!empty($_REQUEST["typID"]))
	switch ($_REQUEST["typID"]) {
		case 1:
			require('export/csv.articles.php');
			break;
		case 2:
			require('export/csv.categories.php');
			break;
		case 3:
			require('export/instock.php');
			break;
		case 4:
			require('export/customers.php');
			break;
		case 5:
			require('export/newsletter.php');
			break;
		case 6:
			require('export/noinstock.php');
			break;
		case 7:
			require('export/csv.articles.php');
			break;
		case 8:
			require('export/prices.php');
			break;
		case 9:
			require('export/images.php');
			break;
		default:
			break;
	}
}
elseif($_REQUEST["formatID"]==2)
{
	if(!empty($_REQUEST["typID"]))
	switch ($_REQUEST["typID"]) {
		case 1:
			require('export/xml.articles.php');
			break;
		case 2:
			require('export/xml.categories.php');
			break;
		case 3:
			require('export/instock.php');
			break;
		case 4:
			require('export/customers.php');
			break;
		case 7:
			require('export/xml.articles.php');
			break;
		default:
			break;
	}
}
elseif($_REQUEST["formatID"]==3)
{
	if(!empty($_REQUEST["typID"]))
	switch ($_REQUEST["typID"]) {
		case 1:
			require('export/csv.articles.php');
			break;
		case 2:
			require('export/csv.categories.php');
			break;
		case 7:
			require('export/csv.articles.php');
			break;
		default:
			break;
	}
}
?>