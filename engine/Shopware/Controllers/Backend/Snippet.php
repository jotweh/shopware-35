<?php
class Shopware_Controllers_Backend_Snippet extends Enlight_Controller_Action
{

	public function preDispatch()
	{
		if($this->Request()->getActionName()!='index' && $this->Request()->getActionName()!='skeleton') {
			$this->View()->setTemplate();
		}
	}
	
	public function skeletonAction ()
	{
		
	}
	
	public function indexAction(){
		$result = $this->getAllLocalesAndShopIDs();
		foreach ($result as $row) {
			$tempArray = array();
			$tempArray["name"] = $row["locale"]."-".$row["id"];
			$tempArray["label"] = $row["name"]." (".$tempArray["name"].")";
			$locales[] = $tempArray;
		}
		$this->View()->translations = $locales;
	}
	
	public function viewAction(){
		$this->forward('index');
	}

	public function getSnippetAction() {
		$limit = (int)$this->Request()->limit ? $this->Request()->limit : 25;
		$order = $this->Request()->sort ? $this->Request()->sort : "namespace,name,locale,shopID";
		$dir = $this->Request()->dir ? $this->Request()->dir : "ASC";
		$start = (int)$this->Request()->start ? $this->Request()->start : 0;
		$nameSpace = $this->Request()->nameSpace;
		$nameSpace = ($nameSpace == "_") ? "" :$nameSpace;
		
		if(!empty($this->Request()->locale)) {
			$tempLocale = $this->Request()->locale;
			$localeAndShopID = explode("-",$tempLocale);
			$localQuery = "AND l.locale = ".Shopware()->Db()->quote($localeAndShopID[0])."AND sn.shopID = ".Shopware()->Db()->quote($localeAndShopID[1]);
		}
		$showEmpty = ($this->Request()->showEmpty) ? " AND value = ''" : "";
		//make regex groups out of the namespace
		preg_match("/(.*)_([0-9]*)/", $nameSpace, $result);
		if (!empty($nameSpace) && $nameSpace !="_") {
			$nameSpace = "AND namespace like".Shopware()->Db()->quote($result[1]."%");
		}
		if ($this->Request()->search) {
			$search = $this->Request()->search;
			$search = $this->getFormatSnippetForSave($search);
			if (strlen($search)>1){
				$search = "%".$search."%";
			}else {
				$search = $search."%";
			}
			$htmlSearch = htmlentities($search);
			$searchSQL = "
			AND 
			(
				namespace LIKE '$search'
			OR
				locale LIKE '$search'
			OR
				name LIKE '$search'
			OR 
				shopID LIKE '$search'
			OR 
				value LIKE '$search'
			OR
				namespace LIKE '$htmlSearch'
			OR
				localeID LIKE '$htmlSearch'
			OR
				name LIKE '$htmlSearch'
			OR 
				shopID LIKE '$htmlSearch'
			OR 
				value LIKE '$htmlSearch'
			) 
			";
		}
		
		$sql = "SELECT SQL_CALC_FOUND_ROWS * ,sn.id as id, l.locale AS locale
			FROM s_core_snippets AS sn
			LEFT JOIN s_core_locales AS l ON ( sn.localeID = l.id )	
			WHERE 1
			$nameSpace 
			$showEmpty
			$localQuery
			$searchSQL
			ORDER BY $order $dir 
			LIMIT $start, $limit";
		$getSnippets = Shopware()->Db()->fetchAll($sql);
		foreach ($getSnippets as &$snippet) {
			$snippet["value"] = $this->getFormatSnippetForGrid($snippet["value"]);
			$snippet["name"] = $this->getFormatSnippetForGrid($snippet["name"]);
		}
		echo json_encode(array("count"=>Shopware()->Db()->fetchOne("SELECT FOUND_ROWS()"),"data"=>$getSnippets));
	}
	
	//deletes marked snippets
	public function deleteSnippetsAction() {
		$sSnippetIds = $this->Request()->snippetIds;
		$sql = "Delete FROM s_core_snippets WHERE id in ($sSnippetIds)";
		$stuff = Shopware()->Db()->query($sql);
	}
	
	public function getLanguageShopAction() {
		$locales = $this->getAllLocalesAndShopIDs();
		$data[] = array("id"=>0,"locale"=>"Alle anzeigen"); //Default val todo Snippet
		foreach ($locales as $locale) {
			$locale["id"] = $locale["locale"]."-".$locale["id"];
			$locale["locale"] = $locale["name"]." (".$locale["id"].")";
			$data[] = $locale;
		}
		echo json_encode(array("locales"=>$data));
	}
	
	public function getLocalesAction() {
		$locales = Shopware()->Db()->fetchCol("SELECT DISTINCT l.locale as locale
			FROM s_core_multilanguage AS ml, s_core_locales AS l 
			WHERE ml.locale = l.id");
		foreach ($locales as $locale) {
				$tempLocale["locale"] = $locale;
				$data[] = $tempLocale;
		}
		echo json_encode(array("locales"=>$data));
	}
	
	public function getshopIDsAction() {
		$ids = Shopware()->Db()->fetchCol("SELECT id
			FROM s_core_multilanguage");
		foreach ($ids as $id) {
				$tempShopID["shopID"] = $id;
				$data[] = $tempShopID;
		}
		echo json_encode(array("shopIDs"=>$data));
	}
	
	//change ns on marked snippets
	public function changeSnippetsAction() {
		$sSnippetIds = $this->Request()->snippetIds;
		$sNameSpace = Shopware()->Db()->quote($this->Request()->nameSpace);
		$sql = "UPDATE s_core_snippets SET namespace = $sNameSpace WHERE id in ($sSnippetIds)";
		Shopware()->Db()->query($sql);
	}
	
	//get whole snippet form data
	public function loadSnippetFormAction() {
		$sSnippetIds = $this->Request()->snippetIds;
		$sql = "SELECT sn.namespace, sn.name, sn.value AS value, l.locale AS locale, ml.id AS id
			FROM s_core_snippets AS sn
			LEFT JOIN s_core_locales AS l ON ( sn.localeID = l.id )
			LEFT JOIN s_core_multilanguage AS ml ON ( sn.shopID = ml.id )
			WHERE sn.namespace = ?
			AND sn.name = ?";
		$getSnippets = Shopware()->Db()->fetchAll($sql,array($this->Request()->nameSpace, $this->Request()->name));

		$namespace = $getSnippets[0]["namespace"];
		$name = $getSnippets[0]["name"];
		$data["name"] = $name;
		$data["namespace"] = $namespace;
		$data["oldName"] = $name;
		$data["oldNamespace"] = $namespace;
		foreach ($getSnippets as $snippet) {
			$data[$snippet["locale"]."-".$snippet["id"]] = $this->getFormatSnippetForGrid(($snippet["value"]));
		}
		echo json_encode(array("snippet"=>array($data)));
	}
	
	//save the updated snippetform data
	public function submitSnippetAction() {
		$req = (array)$this->Request()->getParams();
		$oldNamespace = $req["oldNamespace"];
		$oldName = $req["oldName"];
		$localesAndShopIds = $this->getAllLocalesAndShopIDs();
		foreach ($localesAndShopIds as $row) {
			$tempArray["both"] = $row["locale"]."-".$row["id"];
			$tempArray["locale"] = $row["locale"];
			$tempArray["shopID"] = $row["id"];
			$localesShopIds[] = $tempArray;
		}
		foreach ($localesShopIds as $localeAndShopID) {
			//check if locale was send
			if(!empty($req[$localeAndShopID["both"]])) {
				//getLocationID = $locale
				$localeID = $this->getLocaleID($localeAndShopID["locale"]);
				
				$sql = "SELECT id FROM s_core_snippets WHERE namespace = ? and name = ? and localeID = ? AND shopID = ?";
				$getSnippetID = Shopware()->Db()->fetchOne($sql,array($oldNamespace, $oldName, $localeID, $localeAndShopID["shopID"]));
				
				if(!empty($getSnippetID)) {
					//update
					$sql = "UPDATE s_core_snippets SET namespace = ?, name = ?, value = ?, updated = now() WHERE id = ?";
					Shopware()->Db()->query($sql,
						array(
							$this->getFormatSnippetForSave($this->Request()->namespace),
							$this->getFormatSnippetForSave($this->Request()->name),
							$this->getFormatSnippetForSave($req[$localeAndShopID["both"]]),
							$getSnippetID
						));
				}
				else {
					
					//if no translation is available insert one
					//Add a new Snippet
					$sql ="INSERT INTO `s_core_snippets` (
						`namespace` ,
						`name` ,
						`localeID`,
						`shopID`,
						`value` ,
						`created` ,
						`updated`
					)
					VALUES (
						?,?,?,?,?,NOW(),NOW()
					)
					";
					Shopware()->Db()->query($sql,
					array(
						$this->getFormatSnippetForSave($this->Request()->namespace),
						$this->getFormatSnippetForSave($this->Request()->name),
						$localeID,
						$localeAndShopID["shopID"],
						$this->getFormatSnippetForSave($req[$localeAndShopID["both"]])
					));
				}
			}
		}
	}
	
	//change or add only one snippet 
	public function changeSnippetAction() {
		//getLocationID = $locale
		$localeID = $this->getLocaleID($this->Request()->locale);
			
		if(!$this->Request()->id) {
			//Add new Snippet
			$sql ="INSERT INTO `s_core_snippets` (
			`namespace` ,
			`name` ,
			`localeID` ,
			`shopID` ,
			`value` ,
			`created` ,
			`updated`
			)
			VALUES (
			?,?,?,?,?,now(),now()
			)";
			Shopware()->Db()->query($sql,
				array(
					$this->getFormatSnippetForSave($this->Request()->namespace),
					$this->getFormatSnippetForSave($this->Request()->name),
					$localeID,
					$this->Request()->shopID,
					$this->getFormatSnippetForSave($this->Request()->value)
				));
			$data[] = Shopware()->Db()->lastInsertId();
			$data[] = Shopware()->Db()->fetchOne("SELECT created FROM s_core_snippets WHERE id =?",array(Shopware()->Db()->lastInsertId()));
			echo json_encode($data);
		}
		else {
			//update snippet
			$sql = "UPDATE s_core_snippets SET namespace = ?, name = ?, localeID = ?, shopID = ?, value = ?, updated = now() WHERE id = ?";
			Shopware()->Db()->query($sql,
				array(
					$this->getFormatSnippetForSave($this->Request()->namespace),
					$this->getFormatSnippetForSave($this->Request()->name),
					$localeID,
					$this->getFormatSnippetForSave($this->Request()->shopID),
					$this->getFormatSnippetForSave($this->Request()->value),
					$this->Request()->id
				));
		}
	}
	
	//dublicate the existing snippets with new namespaces
	public function dublicateSnippetsAction() {
		$sSnippetIds = $this->Request()->snippetIds;
		$sNameSpace = $this->Request()->nameSpace;
		$sql = "SELECT * FROM s_core_snippets WHERE id in ($sSnippetIds)";
		$stuff = Shopware()->Db()->fetchAll($sql);
		foreach ($stuff as $snippet) {
			$sql ="INSERT INTO `s_core_snippets` (
			`namespace` ,
			`name` ,
			`localeID` ,
			`shopID` ,
			`value` ,
			`created` ,
			`updated`
			)
			VALUES (
			?,?,?,?,?,?,?
			)";
			Shopware()->Db()->query($sql,array($sNameSpace,$snippet["name"],$snippet["localeID"],$snippet["shopID"],$snippet["value"],$snippet["created"],$snippet["updated"]));
		}
	}
	
	public function getNSAction() {
		$node = $this->Request()->node;

		if ($node!="_"){
			$result = array();
			preg_match("/(.*)_([0-9]*)/",$node,$result);
			$node = $result[1];
			$layer = $result[2];
			$node = Shopware()->Db()->quote($node);
			$where = "WHERE SUBSTRING_INDEX(namespace, '/', $layer) = $node";
			$layer += 1;
		}else {
			$where = "";
			$layer = "1";
		}
		
		$sql = "
		SELECT SUBSTRING_INDEX(s_core_snippets.namespace, '/', $layer) AS namespaceExploded,s_core_snippets.namespace AS namespaceOriginal FROM s_core_snippets 
		$where
		GROUP BY namespaceExploded
		";
		$getNamespaceTree = Shopware()->Db()->fetchAll($sql);
		foreach ($getNamespaceTree as &$namespace){
			$ns = explode("/",$namespace["namespaceExploded"]);
			$nsOrig = $namespace["namespaceOriginal"];
			if (!is_array($ns)) $ns = $namespace["namespaceExploded"];
			$ns = $ns[count($ns)-1];
			//echo $nsOrig."<br />"."/".$ns."\//"."<br />";
			if (!preg_match("/".$ns."$/",$nsOrig) || $layer == 1){
				$leaf = false;
			}else {
				$leaf = true;
			}
			$data[] = array("text"=>$ns,"id"=>$namespace["namespaceExploded"]."_".$layer,"leaf"=>$leaf,"layer"=>$layer);	
		}
		echo json_encode($data);
		
	}
	
	/////////////////////////////////////////
	// Export Import Functions
	/////////////////////////////////////////
	public function exportSnippetAction() {
		$format = $this->Request()->formatExport;
		if($format=="CSV")
		{
			//Attention group_concat_max_len is default 1024 the variable should be increased
			$sql = "SELECT namespace, name, GROUP_CONCAT( value
				ORDER BY shopID, localeId
				SEPARATOR '~' ) AS localeVals
				FROM s_core_snippets
				GROUP BY name, namespace
				ORDER BY namespace";
			$result = Shopware()->Db()->query($sql);
			
			$header = array_keys($result->fields);
			$this->Response()->setHeader('Content-Type', 'text/x-comma-separated-values;charset=utf-8');
			$this->Response()->setHeader('Content-Disposition', 'attachment; filename="export.csv"');
			$locales = Shopware()->Db()->fetchCol("SELECT concat(l.locale,'-',ml.id) as locale 
				FROM s_core_multilanguage as ml 
				LEFT JOIN s_core_locales as l ON (ml.locale = l.id) ORDER BY ml.id, l.id");
			$tempHeader = array();
			$tempHeader[] ="namespace";
			$tempHeader[] ="name";
			$countLocales = count($locales);
			foreach ($locales as $key => $locale) {
				$tempHeader[] = "value-".$locale;
				$headerLocaleFields = "value-".$locale;
			}
			$header = $tempHeader;
			echo implode($header,";");
			echo "\r\n";
			while ($row = $result->fetch()) {
				if(strpos($row["localeVals"],"~") !== false) {
					//Contains foreign Value
					$tempArr = explode("~",$row["localeVals"]);
					$countAvailableLocales = count($tempArr);
					foreach ($tempArr as $key => $value) {
						$row["value-".$locales[$key]] =$value;
						$values[] = $value;
					}
					//fill missing locale data
					for ($i = $countAvailableLocales; $i < $countLocales; $i++) {
						$row["value-".$locales[$i]] = "";
					}
				}
				else {
					$row["value-".$locales[0]] = $row["localeVals"];
					for ($i = 1; $i < $countLocales; $i++) {
						$row["value-".$locales[$i]] = "";
					}
				}
				unset($row["localeVals"]);
				echo $this->_encode_line($row, array_keys($row));
				
			}
			
		}
		else {
			$sql = "SELECT namespace,name,localeID,shopID,value,created,updated
			FROM `s_core_snippets`";
			$this->Response()->setHeader('Content-type: text/plain');
			$this->Response()->setHeader('Content-Disposition', 'attachment; filename="export.sql"');
			$result = Shopware()->Db()->fetchAll($sql);
			$countRows = count($result);
			echo  "REPLACE INTO `s_core_snippets` (`namespace`, `name`, `localeID`, `shopID`, `value`, `created`, `updated`) VALUES"."\r\n";
			foreach ($result as $key => $row) {
				$row['namespace'] = mysql_escape_string(utf8_encode($row['namespace']));
				$row['name'] = mysql_escape_string(utf8_encode($row['name']));
				$row['localeID'] = mysql_escape_string(utf8_encode($row['localeID']));
				$row['value'] = mysql_escape_string(utf8_encode($row['value']));
				if($countRows != $key+1){
					echo "('{$row['namespace']}', '{$row['name']}', '{$row['localeID']}', '{$row['shopID']}','{$row['value']}', '{$row['created']}', NOW()),"."\r\n";
				}
				else {
					//lastRow
					echo "('{$row['namespace']}', '{$row['name']}', '{$row['localeID']}', '{$row['shopID']}','{$row['value']}', '{$row['created']}', NOW());";
				}
			}
		}
		return true;
	}
	
	public function importSnippetAction() {
		$sConfig['sFileName'] = basename($_FILES['snippet_file']['name']);
		$sConfig['sFileExtension'] = pathinfo($sConfig['sFileName'],PATHINFO_EXTENSION);
		if($sConfig['sFileExtension'] == "csv"){
		
			if(file_exists(Shopware()->OldPath()."/engine/connectors/api/tmp")&&is_writeable(Shopware()->OldPath()."/engine/connectors/api/tmp")) {
				$tmpdir = Shopware()->OldPath()."/engine/connectors/api/tmp";
			}
			else {
				die(htmlentities($json->encode(array(
					'msg' => utf8_encode('Für den Ordner "/engine/connectors/api/tmp" sind keine Schreibrechte vorhanden.'),
					'success' => false
				))));	
			}
			
			$sConfig['sFilePath'] = tempnam($tmpdir, 'import_');
			if(is_readable($_FILES['snippet_file']['tmp_name'])) {
				copy($_FILES['snippet_file']['tmp_name'],$sConfig['sFilePath']);
			}
			$counter = 0;
			chmod($sConfig['sFilePath'], 0644);
			$snippets = new Shopware_Components_CsvIterator($sConfig['sFilePath'],';');
			unlink($sConfig['sFilePath']); // We don´t need the physical file anymore
			$sConfig['sHeader'] = $snippets->getHeader();
			
			if(!empty($sConfig['sHeader'])) {
				foreach ($sConfig['sHeader'] as $header) {
					$pos = strpos($header, "value-");
					if($pos !== false) {
						$row = explode("-",$header);

						$tempArray["both"] = $row[1]."-".$row[2];
						$tempArray["locale"] = $row[1];
						$tempArray["shopID"] = $row[2];
						$localesAndShopIDs[] = $tempArray;
					}
				}
				if(in_array('namespace',$sConfig['sHeader'])) {
					foreach ($snippets as $snippet) {
						$snippet = preg_replace("/^'?/","",$snippet); //Stripes the first ' 
						foreach ($localesAndShopIDs as $localeAndShopID) {
							if(!empty($snippet["value-".$localeAndShopID["both"]])) { 
								$localeID = $this->getLocaleID($localeAndShopID["locale"]);
								$sql = "REPLACE INTO `s_core_snippets` (`namespace`, `name`, `localeID`, `shopID`, `value`, `updated`,`created`) 
								VALUES
								(?, ?, ?, ?, ?, now(),now())";
								$stuff = Shopware()->Db()->query($sql,array($snippet["namespace"],$snippet["name"], $localeID, $localeAndShopID["shopID"], $snippet["value-".$localeAndShopID["both"]]));
								$counter++;
							}
						}
						$allSQL[] = $sql;
					}
				}
				//Returning
				echo json_encode(array(
				'msg' => utf8_encode('Es wurden '.$counter.' Textbausteine importiert.'),
				'success' => true));
			}
		}
		else {
				die(json_encode(array(
					'msg' => utf8_encode('Dieses Dateiformat wird nicht unterstützt.'),
					'success' => false
				)));
			}
	}
	/////////////////////////////////////////
	// Export Import Functions Ends
	/////////////////////////////////////////
	
	
	/////////////////////////////////////////
	// Helper
	/////////////////////////////////////////
	private function getFormatSnippetForSave($string) {
		if(function_exists('mb_convert_encoding')) {
			$string = mb_convert_encoding($string, 'HTML-ENTITIES', 'UTF-8');
		}
		$string = str_replace(array('&nbsp;', '&amp'), array('%%%SHOPWARE_NBSP%%%', '%%%SHOPWARE_AMP%%%'), $string);
		$string = html_entity_decode(utf8_decode($string), ENT_NOQUOTES);
		$string = str_replace(array('%%%SHOPWARE_NBSP%%%', '%%%SHOPWARE_AMP%%%'), array('&nbsp;', '&amp'), $string);
		return $string;
	}
	
	private function getFormatSnippetForGrid($string) {
		if(function_exists('mb_convert_encoding')) {
			$string = mb_convert_encoding($string, 'UTF-8', 'HTML-ENTITIES');
		} else {
			$string = utf8_encode(html_entity_decode($string, ENT_NOQUOTES));
		}
		return $string;
	}
	
	private function getAllLocalesAndShopIDs() {
		$result = Shopware()->Db()->fetchAll("SELECT l.locale as locale, ml.id as id ,ml.name as name
			FROM s_core_multilanguage AS ml, s_core_locales AS l 
			WHERE ml.locale = l.id");
		return $result;
	}

	private function getLocaleID($locale) {
		$localeID = Shopware()->Db()->fetchOne("SELECT *
			FROM `s_core_locales`
			WHERE `locale` = ?",
			array($locale));
		return $localeID;
	}
	
	function _encode_line($line, $keys)
	{
		$sSettings = array(
			"separator" => ";",
			"encoding"=>"ISO-8859-1",//UTF-8
			"escaped_separator" => "'",
			"escaped_fieldmark" => "\"\"",
			"newline" => "\r\n",
			"escaped_newline" => "",
		);
	
		if(isset($sSettings['fieldmark']))
			$fieldmark = $sSettings['fieldmark'];
		else
			$fieldmark = "";
		$lastkey = end($keys);
		foreach ($keys as $key)
		{
			if(!empty($line[$key]))
			{
				if(strpos($line[$key],"\r")!==false||strpos($line[$key],"\n")!==false||strpos($line[$key],$fieldmark)!==false||strpos($line[$key],$sSettings['separator'])!==false)
				{
					$csv .= "'";
					if($sSettings['encoding']=="UTF-8")
						$line[$key] = utf8_decode($line[$key]);
						$csv .= str_replace($sSettings['separator'],$sSettings['escaped_separator'],$line[$key]);
				}
				else 
					$csv .= "'".$line[$key];
			}
			if($lastkey!=$key){
				$csv .= $sSettings['separator'];
			}
			else{
				$csv .= $sSettings['newline'];
			}
		}
		return html_entity_decode($csv);
	}
	
}
