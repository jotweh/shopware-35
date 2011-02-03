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
//ini_set('display_errors',1);
//error_reporting(E_ALL);

require_once('../../../connectors/api/api.php');
$api = new sAPI();
$import = &$api->import->shopware;
error_reporting(E_ALL);
ini_set("display_errors",1);
require_once($api->sPath.'/engine/backend/ajax/json.php');
$json = new Services_JSON();

$sql = 'SELECT id FROM s_core_auth WHERE sessionID=? AND lastlogin>=DATE_SUB(NOW(),INTERVAL 60*90 SECOND)';
$id = isset($_REQUEST[session_name()]) ? $_REQUEST[session_name()] : $_COOKIE[session_name()];

$result = $api->sDB->GetOne($sql,array($id));
if (empty($result)){
	die("Login failure".$api->sDB->ErrorMsg());
}
$sConfig = array();

$sConfig['sMaxExecutionTime'] = @ini_get('max_execution_time') ? ini_get('max_execution_time') : 30;
$sConfig['sMaxExecutionTime'] = min(10,$sConfig['sMaxExecutionTime']);
$sConfig['sRequestTime'] = !empty($_SERVER['REQUEST_TIME']) ? $_SERVER['REQUEST_TIME'] : time();
$sConfig['sDeleteCategories'] = empty($_REQUEST["delete_old_categories"]) ? 0 : intval($_REQUEST["delete_old_categories"]);
$sConfig['sDeleteArticles'] = empty($_REQUEST["delete_old_articles"]) ? 0 : intval($_REQUEST["delete_old_articles"]);
$sConfig['sArticleImages'] = empty($_REQUEST['article_images']) ? 0 : 1;
$sConfig['sDeleteArticleCache'] = empty($_REQUEST['delete_article_cache']) ? 0 : 1;
$sConfig['sTyp'] = empty($_REQUEST['typ']) ? 0 : (int) $_REQUEST['typ'];
if(isset($_REQUEST['group']))
{
	$sConfig['sValueGroup'] = (int) $_REQUEST['group'];
}

//$sConfig['sStartImportTime'] = strtotime($api->sDB->GetOne('SELECT NOW()'));

$sConfig['sImportArticles'] = 0;
$sConfig['sCountArticles'] = 0;
$sConfig['sImportStep'] = empty($_REQUEST['import_step']) ? 100 : (int) $_REQUEST['import_step'];
if($sConfig['sImportStep']<1) $sConfig['sImportStep'] = 1;

//$_FILES['articles_file']['tmp_name'] = 'test.csv';

if(empty($_FILES['articles_file']['tmp_name'])||!empty($_FILES['articles_file']['error']))
{
	$data = array(
		'msg' => 'Der Dateiupload ist fehlgeschlagen.',
		'success' => false
	);
	die(htmlentities($json->encode($data)));
}

if(file_exists($api->sPath."/engine/connectors/api/tmp")&&is_writeable($api->sPath."/engine/connectors/api/tmp"))
	$tmpdir = $api->sPath."/engine/connectors/api/tmp";
elseif(file_exists($api->sPath."/files/article_pdf")&&is_writeable($api->sPath."/files/article_pdf"))
	$tmpdir = $api->sPath."/files/article_pdf";
else
	die(htmlentities($json->encode(array(
		'msg' => utf8_encode('Für den Ordner "/engine/connectors/api/tmp" sind keine Schreibrechte vorhanden.'),
		'success' => false
	))));
	
$sConfig['sFileName'] = basename($_FILES['articles_file']['name']);
$sConfig['sFileExtension'] = pathinfo($sConfig['sFileName'],PATHINFO_EXTENSION);
switch ($sConfig['sFileExtension'])
{
	case 'csv':
	case 'txt':
		$sConfig['sFormat'] = 1;
		break;
	case 'xml':
		$sConfig['sFormat'] = 2;
		break;
	default:
		die(htmlentities($json->encode(array(
			'msg' => utf8_encode('Dieses Dateiformat wird nicht unterstützt.'),
			'success' => false
		))));
		break;
}

$sConfig['sFilePath'] = tempnam($tmpdir, 'import_');
if(is_readable($_FILES['articles_file']['tmp_name']))
	copy($_FILES['articles_file']['tmp_name'],$sConfig['sFilePath']);
else
	move_uploaded_file($_FILES['articles_file']['tmp_name'],$sConfig['sFilePath']);
chmod($sConfig['sFilePath'], 0644);

if($sConfig['sFormat']==1)
{
	require_once('csv.php');
	$articles = new CsvIterator($sConfig['sFilePath'],';');
	$sConfig['sHeader'] = $articles->GetHeader();
	if(in_array('ordernumber',$sConfig['sHeader']))
	{
		foreach ($articles as $article)
		{
			if(!empty($article['ordernumber'])||!empty($article['articleID'])||!empty($article['articledetailsID']))
				$sConfig['sCountArticles']++;
		}
		$sConfig['sTyp'] = 1;
	}
	elseif (in_array('categoryID',$sConfig['sHeader']))
	{
		foreach ($articles as $article)
		{
			if(!empty($article['categoryID'])&&!empty($article['description']))
				$sConfig['sCountArticles']++;
		}
		$sConfig['sTyp'] = 2;
	}
	elseif (in_array('email',$sConfig['sHeader']))
	{
		foreach ($articles as $article)
		{
			if(!empty($article['email']))
				$sConfig['sCountArticles']++;
		}
		if(empty($sConfig['sTyp']))
			$sConfig['sTyp'] = 4;
	}
}
elseif($sConfig['sFormat']==2)
{
	$xml = simplexml_load_file($sConfig['sFilePath'], 'SimpleXMLElement', LIBXML_NOCDATA);
	if(!empty($xml->articles->article))
	{
		foreach ($xml->articles->article as $article)
		{
			if(!empty($article->ordernumber)||!empty($article->articleID)||!empty($article->articledetailsID))
				$sConfig['sCountArticles']++;
		}
		$sConfig['sHeader'] = array_keys((array)$xml->articles->article);
		$sConfig['sTyp'] = 1;
	}
	elseif(!empty($xml->categories->category))
	{
		foreach ($xml->categories->category as $article)
		{
			if(!empty($article->categoryID)&&!empty($article->description))
				$sConfig['sCountArticles']++;
		}
		$sConfig['sHeader'] = array_keys((array)$xml->categories->category);
		$sConfig['sTyp'] = 2;
	}
	elseif(!empty($xml->customers->customer))
	{
		foreach ($xml->customers->customer as $article)
		{
			if(!empty($article->email))
				$sConfig['sCountArticles']++;
		}
		$sConfig['sHeader'] = array_keys((array)$xml->customers->customer);
		$sConfig['sTyp'] = 4;
	}
}
foreach ($sConfig['sHeader'] as $key => $value)
{
	if(strpos($value,'_')===0)
	{
		unset($sConfig['sHeader'][$key]);
	}
}
if(count($sConfig['sHeader'])==2&&in_array('ordernumber',$sConfig['sHeader'])&&in_array('instock',$sConfig['sHeader']))
{
	$sConfig['sTyp'] = 3;
}
if(count($sConfig['sHeader'])<7&&in_array('ordernumber',$sConfig['sHeader'])&&in_array('price',$sConfig['sHeader'])&&in_array('pricegroup',$sConfig['sHeader']))
{
	$sConfig['sTyp'] = 6;
}
if(count($sConfig['sHeader'])<10&&in_array('ordernumber',$sConfig['sHeader'])&&in_array('image',$sConfig['sHeader']))
{
	$sConfig['sTyp'] = 7;
}
if(empty($sConfig['sTyp'])||empty($sConfig['sCountArticles']))
{
	die(htmlentities($json->encode(array(
		'msg' => utf8_encode('Es konnten keine Daten zum Importieren ausgelesen werden.'),
		'success' => false
	))));
}

if($sConfig['sDeleteCategories']==1)
	$import->sDeleteAllCategories();
if($sConfig['sDeleteArticles']==1)
	$import->sDeleteAllArticles();

$data = array(
	'sConfig' => $sConfig,
	'success' => true
);
echo htmlentities($json->encode($data));
?>