<?php
class Shopware_Controllers_Backend_Cache extends Enlight_Controller_Action
{
	public function indexAction()
	{
	}
	
	public function skeletonAction()
	{
	}
	
	public function snippetsAction(){
		$this->View()->setTemplate();
		$this->clearTemplateCache();
		$this->clearConfigCache();
		$this->clearCompilerCache();
	}
	
	public function articlesAction(){
		$this->View()->setTemplate();
		$this->clearAdodbCache();
		$this->clearPluginCache();
	}
	
	public function configAction(){
		$this->View()->setTemplate();
		$this->clearTemplateCache();
		$this->clearConfigCache();
		$this->clearCompilerCache();
		$this->clearPluginCache();
	}
	
	public function clearCacheAction()
	{
		$this->View()->setTemplate();
		$cache = $this->Request()->getPost('cache');
		
		if ($cache["template"] == "on") $this->clearTemplateCache();
		if ($cache["config"] == "on") $this->clearConfigCache();
		if ($cache["seo"] == "on") $this->clearRewriteCache();
		if ($cache["plugins"] == "on") $this->clearPluginCache();
		if ($cache["search"] == "on") $this->clearSearchCache();
		if ($cache["adodb"] == "on") $this->clearAdodbCache();
	}
	
	public function clearStaticCacheAction()
	{
		$this->View()->setTemplate();
		$cache = $this->Request()->getPost('cache');
		
		if ($cache["compiler"] == "on") $this->clearCompilerCache();
		if ($cache["locale"] == "on") $this->clearLocaleCache();
	}
	
	protected function clearConfigCache()
	{
		Shopware()->Cache()->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array('Shopware_Config'));
	} 
	
	protected function clearTemplateCache()
	{
		Shopware()->Template()->cache->clear(null, 'frontend');
	}
	
	protected function clearCompilerCache()
	{
		Shopware()->Template()->utility->clearCompiledTemplate();
	}
	
	protected function clearLocaleCache()
	{
		Shopware()->Cache()->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array());
	}
	
	protected function clearRewriteCache(){
		Shopware()->Db()->query("UPDATE s_core_config SET value = '' WHERE name = 'sROUTERLASTUPDATE'");
		Shopware()->Cache()->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array('Shopware_RouterRewrite'));	
	}
	
	protected function clearPluginCache()
	{
		Shopware()->Cache()->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array('Shopware_Plugin'));
	}
	
	protected function clearSearchCache()
	{
		Shopware()->Db()->query("UPDATE s_core_config SET value = '' WHERE name = 'sFUZZYSEARCHLASTUPDATE'");
		Shopware()->Cache()->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array('Shopware_Modules_Search'));
	}
	
	protected function clearAdodbCache()
	{
		Shopware()->Cache()->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array('Shopware_Adodb'));
	}
}