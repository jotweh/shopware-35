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
?>
<?php
require_once("json.php");
$json = new Services_JSON();
?>
<?php
// from php manual page
function formatBytes($val, $digits = 3, $mode = "SI", $bB = "B"){ //$mode == "SI"|"IEC", $bB == "b"|"B"
   $si = array("", "K", "M", "G", "T", "P", "E", "Z", "Y");
   $iec = array("", "Ki", "Mi", "Gi", "Ti", "Pi", "Ei", "Zi", "Yi");
   switch(strtoupper($mode)) {
       case "SI" : $factor = 1000; $symbols = $si; break;
       case "IEC" : $factor = 1024; $symbols = $iec; break;
       default : $factor = 1000; $symbols = $si; break;
   }
   switch($bB) {
       case "b" : $val *= 8; break;
       default : $bB = "B"; break;
   }
   for($i=0;$i<count($symbols)-1 && $val>=$factor;$i++)
       $val /= $factor;
   $p = strpos($val, ".");
   if($p !== false && $p > $digits) $val = round($val);
   elseif($p !== false) $val = round($val, $digits-$p);
   return round($val, $digits) . " " . $symbols[$i] . $bB;
}

// Fetch-Data from categories
$_REQUEST["node"] = intval($_REQUEST["node"]);
if (!$_REQUEST["node"]){
	$_REQUEST["node"] = 1;
}else {
	//echo $_REQUEST["node"];
}

$nodes = array();

$getCategories = mysql_query("
SELECT id, description, position, parent FROM s_help WHERE parent={$_REQUEST["node"]} ORDER BY position, description
");

if (@mysql_num_rows($getCategories)){
	while ($category=mysql_fetch_array($getCategories)){
		// Check for leafs
		$getCategoryLeafs = mysql_query("
		SELECT id FROM s_help WHERE parent={$category["id"]} LIMIT 1
		");
		$category["description"] = utf8_encode($category["description"]);
		if (@mysql_num_rows($getCategoryLeafs)){
			$nodes[] = array('text'=>$category["description"], id=>$category["id"], parentId=>$category["parent"], cls=>'folder');
		}else {
			$nodes[] = array('text'=>$category["description"], id=>$category["id"], parentId=>$category["parent"],leaf=>true, cls=>'folder');
		}
	}
}
echo $json->encode($nodes);
exit;
return;
$dir = $_REQUEST['lib'] == 'yui' ? '../../../' : '../../';
$node = $_REQUEST['node'];
if(strpos($node, '..') !== false){
	die('Nice try buddy.');
}
$nodes = array();

$d = dir($dir.$node);
while($f = $d->read()){
    if($f == '.' || $f == '..' || substr($f, 0, 1) == '.')continue;
    $lastmod = date('M j, Y, g:i a',filemtime($dir.$node.'/'.$f));
    $f = "test";
    if(is_dir($dir.$node.'/'.$f)){
    	$qtip = 'Type: Folder<br />Last Modified: '.$lastmod;
    	$nodes[] = array('text'=>$f, id=>$node.'/'.$f/*, qtip=>$qtip*/, cls=>'folder');
    }else{
    	$size = formatBytes(filesize($dir.$node.'/'.$f), 2);
    	$qtip = 'Type: JavaScript File<br />Last Modified: '.$lastmod.'<br />Size: '.$size;
    	$nodes[] = array('text'=>$f, id=>$node.'/'.$f, leaf=>true/*, qtip=>$qtip, qtipTitle=>$f */, cls=>'file');
    }
}
$d->close();
echo $json->encode($nodes);

?>