<?php
interface Enlight_View_ViewCache
{
	public function setTemplateDir($path);
	public function addTemplateDir($path);
	public function setTemplate($template=null);
	public function hasTemplate();
	
	public function clearAssign($spec = null);
	public function getAssign($spec = null);
	
	public function render();
	
	public function setCaching($value=true);
	public function isCached();
	public function setCacheID($cache_id = null);
	public function clearCache($template = null, $cache_id = null, $compile_id = null, $exp_time = null, $type = null);
	public function clearAllCache($exp_time = null);
	
	public function assign($spec, $value = null, $nocache = false, $scope = null);
}