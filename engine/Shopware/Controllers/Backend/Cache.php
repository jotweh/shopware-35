<?php
class Shopware_Controllers_Backend_Cache extends Enlight_Controller_Action
{
	public function preDispatch()
	{
		if(!in_array($this->Request()->getActionName(), array('index', 'skeleton'))) {
			Shopware()->Plugins()->Controller()->ViewRenderer()->setNoRender();
		}
	}
	
	public function indexAction()
	{
		$this->View()->CacheInformation = $this->getCacheInformation();
	}
	
	public function skeletonAction()
	{
	}
	
	public function snippetsAction()
	{
		$this->clearTemplateCache();
		$this->clearConfigCache();
		$this->clearCompilerCache();
	}
	
	public function articlesAction()
	{
		$this->clearAdodbCache();
		$this->clearPluginCache();
	}
	
	public function configAction()
	{
		$this->clearTemplateCache();
		$this->clearConfigCache();
		$this->clearCompilerCache();
		$this->clearPluginCache();
	}
	
	public function clearCacheAction()
	{
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
	
	protected function clearRewriteCache()
	{
		//Shopware()->Db()->query("UPDATE s_core_config SET value = '' WHERE name = 'sROUTERLASTUPDATE'");
		
		$cache = (empty(Shopware()->Config()->RouterCache)||Shopware()->Config()->RouterCache<360) ? 86400 : (int) Shopware()->Config()->RouterCache;
		$sql = 'SELECT value FROM s_core_config WHERE name=?';
		$last_update = Shopware()->Db()->fetchOne($sql, array('sROUTERLASTUPDATE'));
		if(!empty($last_update)) {
			$last_update = unserialize($last_update);
		}
		if(empty($last_update)||!is_array($last_update)) {
			$last_update = array();
		}
		foreach ($last_update as $shopId=>$time) {
			$last_update[$shopId] = date('Y-m-d H:i:s', min(strtotime($time), time()-$cache));
		}
		$sql = 'UPDATE `s_core_config` SET `value`=? WHERE `name`=?';
	    Shopware()->Db()->query($sql, array(serialize($last_update), 'sROUTERLASTUPDATE'));
			
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
	
	public function getCacheInformation()
	{
		$cache = Shopware()->Cache();
		$cache_config = Shopware()->getOption('cache');
		$info['backend'] = empty($cache_config['backend']) ? 'File' : $cache_config['backend'];
		if(!empty($cache_config['backendOptions']['cache_dir'])){
			$info['cache_dir'] = $cache_config['backendOptions']['cache_dir'];
		} elseif(!empty($cache_config['backendOptions']['slow_backend_options']['cache_dir'])){
			$info['cache_dir'] = $cache_config['backendOptions']['slow_backend_options']['cache_dir'];
		}
		if(!empty($info['cache_dir'])) {
			$info['cache_size'] = 0;
			$info['cache_files'] = 0;
			$dir_iterator = new RecursiveDirectoryIterator($info['cache_dir']);
			$iterator = new RecursiveIteratorIterator($dir_iterator, RecursiveIteratorIterator::SELF_FIRST);
			foreach ($iterator as $entry) {
				if(!$entry->isFile()) {
					continue;
				}
				$info['cache_size'] += $entry->getSize();
				$info['cache_files']++;
			}
			$info['cache_size'] = $this->encodeSize($info['cache_size']);
			$info['free_space'] = disk_free_space($info['cache_dir']);
			$info['free_space'] = $this->encodeSize($info['free_space']);
			$info['cache_dir'] = str_replace(Shopware()->DocPath(), '', $info['cache_dir']);
		}
		return $info;
	}
	
	public static function encodeSize($bytes)
	{
	    $types = array( 'B', 'KB', 'MB', 'GB', 'TB' );
	    for( $i = 0; $bytes >= 1024 && $i < ( count( $types ) -1 ); $bytes /= 1024, $i++ );
	    return( round( $bytes, 2 ) . ' ' . $types[$i] );
	}
}