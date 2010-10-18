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

if ($_REQUEST['node']=="0") unset($_REQUEST['node']);

$dir = "../../../";
$node = $_REQUEST['node'];

$nodes = array();

$d = dir($dir.$node);



while(false !== ($f = $d->read())){
	if (preg_match("/engine/",$f)) continue;
	if (preg_match("/cache/",$f)) continue;
	if (preg_match("/\.dll/",$f)) continue;
	if (preg_match("/\.php/",$f)) continue;
	
    if($f == '.' || $f == '..' || substr($f, 0, 1) == '.')continue;
    $lastmod = date('M j, Y, g:i a',filemtime($dir.$node.'/'.$f));
	$text = $f;
  
    if(is_dir($dir.$node.'/'.$f)){
    	$qtip = 'Type: Folder<br />Last Modified: '.$lastmod;
    	
    	$nodes[] = array('text'=>$text, 'direct'=>$f,id=>$node.'/'.$f,leaf=>false, cls=>'folder');
    }else{
    	$size = formatBytes(filesize($dir.$node.'/'.$f), 2);
    	$qtip = 'Type: JavaScript File<br />Last Modified: '.$lastmod.'<br />Size: '.$size;
    	
    	$nodes[] = array('text'=>$text, 'direct'=>$f,id=>$node.'/'.$f, leaf=>true/*, qtip=>$qtip, qtipTitle=>$f */, cls=>'file');
    }
  	
}

masort($nodes,"text");
$i=0;
foreach ($nodes as $nodeKey => $nodeValue){
	$nodes2[$i] = $nodeValue;
	$i++;
}

// Mehrdimensionale Arrays sortieren
	function masort(&$data, $sortby)
	{
	   static $sort_funcs = array();

	   if (empty($sort_funcs[$sortby])) {
	       $code = "\$c=0;";
	       foreach (split(',', $sortby) as $key) {
	         $array = array_pop($data);
	         array_push($data, $array);
	         if(is_numeric($array[$key]))
	           $code .= "if ( \$c = ((\$a['$key'] == \$b['$key']) ? 0:((\$a['$key'] < \$b['$key']) ? -1 : 1 )) );";
	         else
	           $code .= "if ( (\$c = strcasecmp(\$a['$key'],\$b['$key'])) != 0 ) return \$c;\n";
	       }
	       $code .= 'return $c;';
	       $sort_func = $sort_funcs[$sortby] = create_function('$a, $b', $code);
	   } else {
	       $sort_func = $sort_funcs[$sortby];
	   }

	  $sort_func = $sort_funcs[$sortby];
	   uasort($data, $sort_func);
	}
$d->close();
echo $json->encode($nodes2);

?>