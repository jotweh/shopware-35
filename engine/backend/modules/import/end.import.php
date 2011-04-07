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

require_once($api->sPath.'/engine/backend/ajax/json.php');
$json = new Services_JSON();

$id = isset($_REQUEST[session_name()]) ? $_REQUEST[session_name()] : $_COOKIE[session_name()];
$sql = "SELECT id FROM s_core_auth WHERE sessionID=? AND lastlogin>=?";
$result = $api->sDB->GetOne($sql, array($id, date('Y-m-d H:i:s', mktime(date('H'), date('i'), date('s')-60*90))));
if (empty($result)) {
	exit;
}
	
$sConfig = $_REQUEST;
	
if ($sConfig['sDeleteArticles']==2&&in_array($sConfig['sTyp'],array(1,3)))
{
	if($sConfig['sFormat']==1)
	{
		require_once('csv.php');
		$articles = new CsvIterator($sConfig['sFilePath'],';');
	}
	elseif($sConfig['sFormat']==2)
	{
		$xml = simplexml_load_file($sConfig['sFilePath'], 'SimpleXMLElement', LIBXML_NOCDATA);
		$articles = $xml->articles->article;
	}
	$ordernumbers = array();
	foreach ($articles as $article)
	{
		if($sConfig['sFormat']==2)
		{
			$ordernumbers[] = (string) $article->ordernumber;
		}
		else
		{
			$ordernumbers[] = $article['ordernumber'];
		}
	}
	$sql = 'SELECT ordernumber FROM `s_articles_details`';
	$diff_ordernumbers = $api->sDB->GetCol($sql);
	$diff_ordernumbers = array_diff($diff_ordernumbers, $ordernumbers);
	unset($ordernumbers);
	if(!empty($diff_ordernumbers))
	foreach ($diff_ordernumbers as $ordernumber)
		$import->sDeleteArticle(array("ordernumber"=>$ordernumber));
}
if($sConfig['sDeleteCategories']==3)
{
	$import->sDeleteEmptyCategories();
}
if(!empty($sConfig['sDeleteArticleCache']))
{
	$import->sDeleteArticleCache();
}
if(file_exists($sConfig['sFilePath']))
{
	unlink($sConfig['sFilePath']);
}
if(!empty($sConfig['sImportErrors']))
{
	$error_messages = array();
	$sConfig['sImportErrors'] = unserialize($sConfig['sImportErrors']);
	foreach ($sConfig['sImportErrors'] as $code => $count)
	{
		switch ($code) {
			case 10201:
			case 10203:
			case 10204:
			case 10205:
			case 10206:
			case 10207:
				$msg = $count.' von '.$sConfig['sCountArticles'].' Artikel konnten nicht importiert werden, ';
				break;
			case 10400:
			case 10401:
			case 10402:
			case 10403:
				$msg = $count.' Bilder konnten nicht importiert werden, ';
				break;
			default:
				break;
		}
		
		switch ($code) {
			case 10201:
				$msg .= 'weil der angegebene Hauptartikel nicht gefunden werden konnte.';
				break;
			case 10203:
				$msg .= 'weil die angegebene ArtikelID nicht gefunden werden konnte.';
				break;
			case 10204:
				$msg .= 'weil die Bestellnummer schon für eine Konfigurator-Variante vergeben war.';
				break;
			case 10205:
				$msg .= 'weil der Konfigurator und die Varianten nicht zusammen genutzt werden können.';
				break;
			case 10206:
				$msg .= 'weil die Herstellerangabe fehlte.';
				break;
			case 10207:
				$msg .= 'weil der angegebene Steuersatz nicht gefunden werden konnte.';
				break;
			case 10400:
			case 10401:
				$msg .= 'weil das Bild nicht geöffnet werden konnte.';
				break;
			case 10402:
				$msg .= 'weil die angegebene Datei kein Bild war.';
				break;
			case 10403:
				$msg .= 'weil das Bildformat nicht unterstützt wurde.';
				break;
			default:
				break;
		}
		$error_messages[] = $msg;
	}
}
$message = "Der Import wurde beendet!";
if(!empty($error_messages))
{
	$message .= "<br><br>Es sind folgende Fehler aufgetreten:<br><br>";
	$message .= implode("<br>", $error_messages);
}
echo $json->encode(array(
	'message' => utf8_encode($message),
	'success' => true
));
?>