<?php
/**
 * Deprecated core functions 
 * Will be removed in Shopware 4.0
 * @link http://www.shopware.de
 * @package core
 * @subpackage class
 * @copyright (C) Shopware AG 2002-2010
 * @version Shopware 3.5.0
 */
class sCore
{
	/**
	* Pointer to Shopware-Core-Functions
	*
	* @var    object
	* @access private
	*/
	var $sSYSTEM;
	
	function sStripSlahes($variable){
		if (is_array($variable)){
			foreach ($variable as $key => $value){
				if (!is_array($variable[$key])){
					$variable[$key] = stripslashes($value);
				}
			}
		}else {
			$variable = stripslashes($variable);
		}
		eval($this->sSYSTEM->sCallHookPoint("sSystem.php_sStripSlashes_End"));
		return $variable;
	}
			
	public function sBuildLink($sVariables, $sUsePost=false)
	{
		$cat = array("sCategory","sPage");
		
		$tempGET = $this->sSYSTEM->_GET;	

		// If viewport is available, this will be the first variable
		if (!empty($tempGET["sViewport"])){
			$url['sViewport'] = $tempGET["sViewport"];
			if ($url["sViewport"]=="cat"
			){
				foreach ($cat as $catAllowedVariable){
					if (!empty($tempGET[$catAllowedVariable])){
						$url[$catAllowedVariable] = $tempGET[$catAllowedVariable];
						unset($tempGET[$catAllowedVariable]);
					}
				}
				$tempGET = array();
			}
			unset ($tempGET["sViewport"]);
		}
		
		// Strip new variables from _GET
		foreach ($sVariables as $getKey => $getValue)
		{
			$tempGET[$getKey] = $getValue;
		}
		
		// Strip session from array
		unset($tempGET['coreID']);
		unset($tempGET['sPartner']);
		
		
		if(!empty($tempGET))
		foreach ($tempGET as $getKey => $getValue){
			if ($getValue) $url[$getKey] = $getValue;
		}
		
		if(!empty($url))
			$queryString = '?'.http_build_query($url,"","&");
		else 
			$queryString = '';
		
		return $queryString;
	}
	
	public function sRewriteLink($link=null, $title=null)
	{
		$url = str_replace(',', '=', $link);
		$url = html_entity_decode($url);
		$query = parse_url($url, PHP_URL_QUERY);
		parse_str($query, $query);
		
		if(!empty($title))
		{
			$query['title'] = $title;
		}
		return Shopware()->Front()->Router()->assemble($query);
	}
	
	public function __call($name, $params=null)
	{
		switch ($name)
		{
			case 'rewriteLink':
				return call_user_func(array($this, 'sRewriteLink'), $params[0][2], empty($params[0][3]) ? null : $params[0][3]);
			default:
				return null;
		}
		return null;
	}
	
	public function sCustomRenderer($sRender,$sPath,$sLanguage)
	{
		return $sRender;
	}
}