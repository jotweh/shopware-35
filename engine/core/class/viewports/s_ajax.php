<?php
include("engine/core/ajax/json.php");	// Add json-routines (PHP <= 5.1)
/*
Shopware 2.1 Viewport to manage ajax calls
*/
class sViewportAjax{
	var $sSYSTEM;
	
	function sRender(){
		$json = new Services_JSON();
		
		/*
		Overwrite routine for tests
		*/

		if ($_POST["sAjaxData"] && $_POST["sAjaxFunction"]){

			$_POST["json"] = $json->encode(
				array("sAjaxData"=>$_POST["sAjaxData"],"sAjaxFunction"=>$_POST["sAjaxFunction"])
			);
		}
		
		if ($_POST["json"]){
			$_POST["json"] = htmlspecialchars_decode($_POST["json"]);
			$_POST["json"] = stripslashes($_POST["json"]);
			
			$data["request"] = $json->decode($_POST["json"]);
			
		
			if ($data["request"]->sAjaxData && $data["request"]->sAjaxFunction){
				$job = $data["request"]->sAjaxFunction;
				$value = $data["request"]->sAjaxData;
				// Job-Data loaded
				
				// Search matching job
				switch ($job){
					/*
					Sample Job / You could do your own calls like ajax-based basket-routines here
					*/
					case "addCompare":			// Add article to comparison list
						/*
						Add Article to compare list
						*/
						
						$result = $this->sSYSTEM->sMODULES['sArticles']->sAddComparison($value);
						
						if ($result=="max_reached" && !intval($result)){
							die("max_reached");
						}
						
						$variables["sComparisons"] = $this->sSYSTEM->sMODULES['sArticles']->sGetComparisons();
						
						if (count($variables["sComparisons"])){
							
							// Assign variables to template, fetch template and return it to storefront
							$this->sSendHtml("index_top_comparisons.tpl",$variables);
						}
						break;
					case "deleteCompare":		// Delete article from comparison list
						$this->sSYSTEM->sMODULES['sArticles']->sDeleteComparison($value);
						$variables["sComparisons"] = $this->sSYSTEM->sMODULES['sArticles']->sGetComparisons();
						// Pass Variables to template and print-out compiled version
						$this->sSendHtml("index_top_comparisons.tpl",$variables);
						break;
					case "deleteComparisons":
						$this->sSYSTEM->sMODULES['sArticles']->sDeleteComparisons();
						
						exit;
						break;
					case "getComparisonList":	// Get all articles which were currently on comparison list
						$variables["sComparisons"] = $this->sSYSTEM->sMODULES['sArticles']->sGetComparisons();
						// Pass Variables to template and print-out compiled version
						$this->sSendHtml("index_top_comparisons.tpl",$variables);
						break;
					case "getComparisons":	// Get all articles which were currently on comparison list
						$variables["sComparisons"] = $this->sSYSTEM->sMODULES['sArticles']->sGetComparisonList();
						// Pass Variables to template and print-out compiled version
						$this->sSendHtml("index_overlay_comparisons.tpl",$variables);
						break;
					case "deleteCompareAll":	// Delete all article from comparison list
						break;
					default:
						exit;
				}
				
			}
		}
		
		exit;
	}
	
	function sSendHtml($template,$variables)
	{
		header("Content-Type: text/html; charset=ISO-8859-1");
		$variables['_POST'] = $this->sSYSTEM->_POST;
		$variables['_GET'] = $this->sSYSTEM->_GET;
		$variables['_SERVER'] = $_SERVER;
		$variables['sConfig'] = $this->sSYSTEM->sCONFIG;
		$variables["sTemplate"] = $this->sSYSTEM->sCONFIG["sTEMPLATEPATH"]."/".$this->sSYSTEM->sLanguageData[$this->sSYSTEM->sLanguage]["isocode"]."/";
		foreach ($variables as $key => $value) $this->sSYSTEM->sSMARTY->assign($key,$value);
		$file = dirname(__FILE__)."/../../../../".$this->sSYSTEM->sCONFIG["sTEMPLATEPATH"]."/".$this->sSYSTEM->sLanguageData[$this->sSYSTEM->sLanguage]["isocode"]."/html/ajax/$template";
		if (!is_file($file)){
			$file = dirname(__FILE__)."/../../../../templates/0/de/html/ajax/$template";
			if (!is_file($file)) die($file." not found");
		}
		$sViewport = $this->sSYSTEM->sSMARTY->fetch($file);
		die($sViewport);
	}
	
	function sSendJson($variables)
	{
		die($json->encode($variables));
	}
}
?>