<?php
/**
 * Shopware Import
 *
 * @author      Heiner Lohaus (hl@shopware.ag>			
 * @package     Shopware 2.08.01
 * @subpackage  API
 */

ini_set('display_errors',0);
error_reporting(0);
@ini_set('memory_limit','2046M');
@ini_set('max_execution_time','9999999');

require_once('../../../connectors/api/api.php');
$api = new sAPI();
$import = &$api->import->shopware;


require_once($api->sPath.'/engine/backend/ajax/json.php');
$json = new Services_JSON();

function utf8_array_decode(&$input) 
{
	if(!is_array($input)) return;
    foreach ($input as &$val) 
    { 
        if(is_array($val))
        { 
            utf8_array_decode($val); 
        } 
        elseif (is_string($val))
        {
        	$val = utf8_decode($val); 
        }
    }
}

function sCopyFile($source, $destination, $timeout=5)
{
	$fp = fopen($destination, 'wb'); 
	if(!$fp) return false;
	
	if(function_exists('curl_init')&&strpos($source, '://')!==false)
	{
		$ch = curl_init();
		curl_setopt($ch,CURLOPT_URL,$source);
		curl_setopt($ch,CURLOPT_HEADER,0);
		curl_setopt($ch,CURLOPT_FILE,$fp); 
		curl_setopt($ch,CURLOPT_TIMEOUT,$timeout); 
		curl_exec($ch);
		curl_close($ch);
	}
	else
	{
		$options = array('http'=>array('method'=>'GET', 'timeout'=>$timeout)); 
		$context = stream_context_create($options); 
		$ch = fopen($source, 'rb', false, $context);
		stream_copy_to_stream($ch, $fp);
		fclose($ch);
	}
	
	fclose($fp);
	return true;
}
function sCreateTmpFile()
{
	global $api;
	static $tmpdir;
	
	if(!isset($tmpdir))
	{
		if(file_exists($api->sPath.'/engine/connectors/api/tmp')&&is_writeable($api->sPath.'/engine/connectors/api/tmp'))
			$tmpdir = $api->sPath.'/engine/connectors/api/tmp';
		elseif(file_exists($api->sPath.'/files/article_pdf')&&is_writeable($api->sPath.'/files/article_pdf'))
			$tmpdir = $api->sPath.'/files/article_pdf';
	}
	if(!$tmpdir) return false;
	
	$tmpfile = tempnam($tmpdir, 'import_');
	return $tmpfile;
}
function sImageExists($image)
{
	global $api;
	$path = realpath($api->sPath.$api->sSystem->sCONFIG['sARTICLEIMAGES']);
	$path .= '/'.$image.'.jpg';
	if(!file_exists($path))
	{
		return false;
	}
	$sql = 'SELECT id FROM s_articles_img WHERE img=?';
	$result = $api->sDB->GetOne($sql,array($image));
	if(empty($result))
	{
		return false;
	}
	return true;
}
$id = isset($_REQUEST[session_name()]) ? $_REQUEST[session_name()] : $_COOKIE[session_name()];
$sql = 'SELECT id FROM s_core_auth WHERE sessionID=? AND lastlogin>=DATE_SUB(NOW(),INTERVAL 60*90 SECOND)';
$result = $api->sDB->GetOne($sql,array($id));
if (empty($result))
	exit;
define('sAuthUser', $result);
	
$sConfig = array_merge($_POST, $_GET);
$sConfig['sRequestTime'] = !empty($_SERVER['REQUEST_TIME']) ? $_SERVER['REQUEST_TIME'] : time();
$sConfig['sImportPosition'] = 0;

switch ($sConfig['sTyp'])
{
	case 1:
		require_once('import/articles.php');
		break;
	case 2:
		require_once('import/categories.php');
		break;
	case 3:
		require_once('import/instock.php');
		break;
	case 4:
		require_once('import/customers.php');
		break;
	case 5:
		require_once('import/newsletter.php');
		break;
	case 6:
		require_once('import/prices.php');
		break;
	case 7:
		require_once('import/images.php');
		break;
	case 8:
		require_once('import/articles_images.php');
		break;
	default:
		die();
		break;
}
?>