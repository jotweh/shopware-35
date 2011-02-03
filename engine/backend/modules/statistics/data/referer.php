<?php
if (!defined('sAuthFile')) die();

if ($_REQUEST["node"]=="referer") unset($_REQUEST["node"]);
if(empty($_REQUEST['range']))
	$range = 14;
else 
	$range = $_REQUEST['range'];

if(empty($_REQUEST['date']))
{
	$lastday = time();
}
else 
{
	list($td, $tm, $tj) = explode('.',$_REQUEST['date']);
	$lastday = mktime(0,0,0,$tm,$td,$tj);
}
list($day, $mounth, $jear) = explode ('-',date("d-m-Y",$lastday));
if(empty($_REQUEST['date2']))
{
	$firstday = mktime(0,0,0,$mounth,$day-$range,$jear);
}
else 
{
	list($td, $tm, $tj) = explode ('.',$_REQUEST['date2']);
	$firstday = mktime(0,0,0,$tm,$td,$tj);
}
list($day2, $mounth2, $jear2) = explode ('-',date("d-m-Y",$firstday));

$monate = $sLang["statistics"]["referer_month"];

if(empty($_REQUEST['node'])&&empty($_REQUEST['keywords'])){
	$sql = "
		SELECT 
			SUM(uniquevisits) AS `Besucher`
		FROM `s_statistics_visitors`
		WHERE 
			datum >= '$jear-$mounth-$day'
		AND 
			datum <= '$jear2-$mounth2-$day2'";
	$result = mysql_query($sql);
	if (!$result||!mysql_num_rows($result))
		$arrays['all']['Count'] = 0;
	else 
		$arrays['all']['Count'] = mysql_result($result,0,0);
	$arrays['all']['Name'] = "all";
}
if(empty($_REQUEST['node']))
	$sql_referer = "IF(referer LIKE '%\$%',CONCAT(SUBSTRING_INDEX(referer, '/', 3),'\$',SUBSTRING_INDEX(referer, '\$', -1)),SUBSTRING_INDEX(referer, '/', 3))";
else 
	$sql_referer = "referer";


$sql = "
	SELECT 
		COUNT(referer) AS `Count`,
		$sql_referer AS `Referer`
	FROM `s_statistics_referer`
	WHERE 
		datum >= '$jear-$mounth-$day'
	AND 
		datum <= '$jear2-$mounth2-$day2'
	AND 
		referer NOT LIKE 'http%//{$sCore->sCONFIG['sHOST']}%'
	AND 
		referer NOT LIKE 'http%//www.{$sCore->sCONFIG['sHOST']}%'
	AND
		referer NOT LIKE '%uos-test.com%'
	AND 
		referer NOT LIKE '%127.0.0.1%'
	AND
		referer LIKE 'http%//%'";
if(!empty($_REQUEST['node']))
{
$sql .= 
"AND
(
		referer LIKE 'http%//www.{$_REQUEST['node']}%'
	OR
		referer LIKE 'http%//{$_REQUEST['node']}%'
)";
}
$sql .= "GROUP BY ($sql_referer)
	";

$result = mysql_query($sql);

if (!$result)
	die('FAIL');
if(mysql_num_rows($result)==0){
		include("json.php");
		$json = new Services_JSON();
		$result = array("metaData"=>array("id"=>0,"root"=>"rows","fields"=>array(),"totalProperty"=>"totalCount"),"rows"=>array(),"totalCount"=>0);
		
		echo $json->encode($result);
		exit;
}
	

$sBadwords = $sLang["statistics"]["referer_badword"];


while ($entry = mysql_fetch_assoc($result))
{
	if(empty($_REQUEST['node'])&&empty($_REQUEST['keywords']))
	{
		
		$ref = parse_url($entry['Referer']);
		$ref = str_replace("www.",'',$ref['host']);
		if(!empty($ref))
		{
			$arrays[$ref]['Count'] += $entry['Count'];
			$arrays[$ref]['Name'] = $ref;
			if(preg_match("#google\.[a-z]+#","{$entry['Referer']}",$match))
			{
				if($match[1]=="products")
				{
					$arrays['google_products']['Count'] += $entry['Count'];
					$arrays['google_products']['Name'] = "google_products";
				}
				else 
				{
					$arrays['google']['Count'] += $entry['Count'];
					$arrays['google']['Name'] = "google";
				}
			}
			if(preg_match("#\\$(.+?)$#",$entry['Referer'],$match))
			{
				if($match[1]=="adwords")
				{
					$arrays['google_adwords']['Count'] += $entry['Count'];
					$arrays['google_adwords']['Name'] = "google_adwords";
				}
				else 
				{
					$arrays["partner_{$match[1]}"]['Count'] += $entry['Count'];
					$arrays["partner_{$match[1]}"]['Name'] = "partner_{$match[1]}";
				}
				$arrays['partner']['Count'] += $entry['Count'];
				$arrays["partner"]['Name'] = "partner";
			}
			$arrays[$ref]['Name'] = preg_replace("#\\$.+$#",'',$arrays[$ref]['Name']);
			$arrays['referer_all']['Count'] += $entry['Count'];
			$arrays['referer_all']['Name'] = "referer_all";
		}
	}
	elseif(!empty($_REQUEST['keywords']))
	{
		
		preg_match_all("#[?&]([qp]|query|highlight|encquery|url|field-keywords|as_q|sucheall|satitle|KW)=([^&\\$]+)#",utf8_encode($entry['Referer'])."&",$matchs);
		if(!empty($matchs))
		{
			foreach ($matchs[1] as $key=>$match)
				$all[$match] = $matchs[2][$key];
			if(!empty($all['field-keywords']))
				$ref = $all['field-keywords'];
			else 
				$ref = $matchs[2][0];
			$ref = html_entity_decode(rawurldecode(strtolower($ref)));
			$ref = str_replace("+", " ",$ref);
			$ref = trim(preg_replace('/\s\s+/', ' ', $ref));	
		}
		if(!empty($ref))
		{
			//preg_match("#google\..+(search|products)#","{$entry['Referer']}",$match);
			if($_REQUEST['keywords']==1)
			{
				$arrays[$ref]['Count'] += $entry['Count'];
				$arrays[$ref]['Name'] = $ref;
			}
			else 
			{
				//$ref = preg_replace("/[-.,]/", "",$ref);
				$ref = preg_replace("/[^a-z0-9äöüß\\-]/", " ", $ref);
				$keywords = preg_split('/ /', $ref, -1, PREG_SPLIT_NO_EMPTY);
				if(count($keywords))
					$keywords = array_unique($keywords);
				else 
					$keywords = array($ref);
				foreach ($keywords as $keyword)
				{
					if(!in_array($keyword,$sBadwords)&&strlen($keyword)>1)
					{
						$arrays[$keyword]['Count'] += $entry['Count'];
						$arrays[$keyword]['Name'] = $keyword;
					}
				}
			}
		}
		unset($matchs,$match,$ref,$all);
	}
	else
	{
		$entry['Referer']=preg_replace("#\\$.+$#",'',$entry['Referer']);
		if (!preg_match("#[%_]#","{$_REQUEST['node']}"))
		{
			$entry['name'] = str_replace(array("http://www.{$_REQUEST['node']}","http://{$_REQUEST['node']}","www.{$_REQUEST['node']}"),'',$entry['Referer']);
			if(empty($entry['name']))
				$entry['name'] = "/";
		}
		else 
		{
			$entry['name'] = $entry['Referer'];
		}
		$arrays[$entry['name']]['Count'] += $entry['Count'];
		$arrays[$entry['name']]['URL'] = $entry['Referer'];
		$arrays[$entry['name']]['Name'] = $entry['name'];
	}
	
}





if(!empty($_REQUEST['table']))
{
	
	//if(!empty($arrays['all']['Count'])&&!empty($arrays['referer_all']['Count']))
	if(empty($_REQUEST['node'])&&empty($_REQUEST['keywords'])){
		$arrays['direct']['Count'] = $arrays['all']['Count']-$arrays['referer_all']['Count'];
		$arrays['direct']['Name'] = 'direct';
	}

	arsort($arrays);
		
	if(!isset($csv))
	{
		$data = array_slice($arrays, 0, 1000);
		if(empty($_REQUEST['node'])&&empty($_REQUEST['keywords'])){
			foreach ($data as $key=>$dat)
			{
				if($key=="all")
				{
					$data[$key]['Name'] = $sLang["statistics"]["referer_Total_visitors"];
					$data[$key]['Options'] = "&nbsp;";
				}
				elseif($key=="direct")
				{
					$data[$key]['Name'] = utf8_encode($sLang["statistics"]["referer_Direct_calls"]);
					$data[$key]['Options'] = "&nbsp;";
				}
				else
				{
					$search = "";
					if($key=="referer_all")
					{
						$data[$key]['Name'] = "<strong>".utf8_encode($sLang["statistics"]["referer_Views_on_referer"])."</strong>";
						$search = "_";
					}
					elseif($key=="google_products")
					{
						$data[$key]['Name'] = "<strong>".utf8_encode($sLang["statistics"]["referer_goog1e_Product_Search"])."</strong>";
						$search = "google%/products";
					}
					elseif($key=="google")
					{
						$data[$key]['Name'] = "<strong>".utf8_encode($sLang["statistics"]["referer_goog1e_search"])."</strong>";
						$search = "google%/search";
					}
					elseif($key=="google_adwords")
					{
						$data[$key]['Name'] = "<strong>".utf8_encode($sLang["statistics"]["referer_goog1e_adwords"])."</strong>";
						$search = "%\$adwords";
					}
					elseif($key=="partner")
					{
						$data[$key]['Name'] = "<strong>".utf8_encode($sLang["statistics"]["referer_Partner_1"])."</strong>";
						$search = "%\$";
					}
					elseif (preg_match("#partner_(.+?)\$#",$key,$match))
					{
						$search = "%\${$match[1]}";
						$sql = "SELECT company FROM s_emarketing_partner WHERE idcode='".mysql_real_escape_string($match[1])."'";
						$result = mysql_query($sql);
						if($result&&mysql_num_rows($result)) $match[1] = mysql_result($result,0,0);
						$data[$key]['Name'] = "<strong>".utf8_encode($sLang["statistics"]["referer_Partner"]." {$match[1]}")."</strong>";
						
					}
					else 
					{
						$search = $dat['Name'];
					}
					
					//$search = rawurlencode($search);

					$data[$key]['Options']   = "<a onclick=\"myExt.loadReferer('$search');\" style=\"cursor: pointer; margin:0px;\" class=\"ico application_view_columns\"</a> ";
					$data[$key]['Options']   .= "<a onclick=\"myExt.loadRefererKeywords('$search');\" style=\"cursor: pointer; margin:0px;\" class=\"ico zoom\"</a> ";
					//$data[$key]['Options']  .= "<a href=\"#\" onclick=\"window.location.href = 'charts.php?chart=referer&table=1&show=table&date={$_REQUEST['date']}&date2={$_REQUEST['date2']}&dtyp=1&keywords=1&node=$search';\" style=\"cursor: pointer; margin:0px;\" class=\"ico find\"</a> ";
					//$data[$key]['Options']  .= "<a href=\"#\" onclick=\"window.location.href = 'charts.php?chart=referer&table=1&show=table&date={$_REQUEST['date']}&date2={$_REQUEST['date2']}&dtyp=1&keywords=2&node=$search';\" style=\"cursor: pointer; margin:0px;\" class=\"ico zoom\"</a> ";
				}
			}
		}
		elseif (!empty($_REQUEST['keywords']))
		{
			foreach ($data as $key=>$dat)
			{
				$data[$key]['Name'] = $dat['Name'];
				$data[$key]['Options'] = "&nbsp;";
			}
		}
		else
		{
			
			foreach ($data as $key=>$dat)
			{
				//$data[$key]['Options']  = $dat['URL'];
				//$data[$key]['URL'] = rawurlencode($dat['URL']);
				//$data[$key]['Options'] = "<a href=\"#popup\" onclick=\"parent.parent.loadSkeleton('statistics/window', 1, '{$data[$key]['URL']}');\" style=\"cursor: pointer\;\" class=\"ico application\"></a>";
				$data[$key]['Options'] = "<a href=\"{$dat['URL']}\" target=\"_blank\" style=\"cursor: pointer\;\" class=\"ico application\"></a>";
				$data[$key]['Name'] = utf8_encode($data[$key]['Name']);
			
			}
		}
		
$i = 0;
foreach ($data as $key=>$dat)
			{
				
				$data2[$i]["Options"] = $data[$key]["Options"];
				$data2[$i]["referer"] = $data[$key]["Name"];
				$data2[$i]["Anzahl"] = $data[$key]["Count"];
				unset($data[$key]["Count"]);
				unset($data[$key]["Name"]);
				$i++;
			}
 $getHeader = mysql_query("
 SELECT header FROM s_core_statistics WHERE file='referer'
 ");
 $getHeader = mysql_result($getHeader,0,"header");
 $getHeader = explode("#",$getHeader);
 $i=0;
 foreach ($getHeader as $header){
 	$columns = explode(";",$header);
 	unset($tempColumns);
 	foreach ($columns as $column){
 		$column = explode(":",$column);
 		if (intval($column[1])){
 			$tempColumns[$column[0]] = intval($column[1]);
 		}else {
 			$tempColumns[$column[0]] = $column[1];
 		}
 	}
 	$tempHeader[$i] = $tempColumns;
 	$i++;
 }
 
 $headers = $tempHeader;
		include("json.php");
		$json = new Services_JSON();
		foreach($headers as $head) $keys[] = $head["dataIndex"];
		$result = array("metaData"=>array("id"=>0,"root"=>"rows","fields"=>$headers,"totalProperty"=>"totalCount"),"rows"=>$data2,"totalCount"=>count($data2));
		
		echo $json->encode($result);
	}
	else
	{
		$data = array_values($arrays);
	}
}

/*
$script = 
"Table.addEvent( 'afterRow', function(data, row){					
	row.cols[0].element.addEvent('click',function(e){
		window.location.href = 'charts.php?chart=referer&table=1&show=table&date={$_REQUEST['date']}&date2={$_REQUEST['date2']}&node='+row.data.Name;
	});
});";

$script = "Table.addEvent( 'afterRow', function(data, row){					
	row.cols[0].element.setStyle('cursor', 'pointer');
	row.cols[0].element.addEvent('click',function(){
		//window.open(\"http://{$_REQUEST['node']}\"+row.cols[0].value, \"_blank\", \"\");
		parent.parent.loadSkeleton('statistics/window',1, row.data.URL);
		//console.log(row);
	});
});";

{
	$arrays = array_slice($arrays, 0, 10);
	header('Content-type: text/plain');
?>
<?php echo"<?phpxml version=\"1.0\" encoding=\"iso-8859-1\"?>"?>
<chart caption="Referer" showValues="0" decimals="0" formatNumberScale="0" chartRightMargin="30">
<?php
foreach ($arrays as $array)
{
if(empty($_REQUEST['node'])){
?>	<set label='<?php echo$array['Name']?>' value='<?php echo$array['Count']?>' link='charts.php?chart=referer&swf=Bar2D&node=<?php echo$ref?>&table=1&dtyp=1&range=<?php echo$range?>&date=<?php echo$_REQUEST['date']?>&date2=<?php echo$_REQUEST['date2']?>'/>
<?php
}
else {
?>	<set label='<?php echo$array['Name']?> value='<?php echo$array['Count']?>' link="JavaScript:openW('<?php echorawurlencode($array['URL'])?>');"/>
<?php
}
}
?>
</chart>
<?php
}
else 
*/
?>