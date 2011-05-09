<?php
/**
 * Cache controller
 * 
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Heiner Lohaus
 * @package Shopware
 * @subpackage Controllers
 */
class Shopware_Controllers_Backend_Cache extends Enlight_Controller_Action
{
	/**
	 * Pre dispatch controller method
	 */
	public function preDispatch()
	{
		if(!in_array($this->Request()->getActionName(), array('index', 'skeleton'))) {
			Shopware()->Plugins()->Controller()->ViewRenderer()->setNoRender();
		}
	}
	
	/**
	 * Index action method
	 */
	public function indexAction()
	{
		$this->View()->CacheInformation = $this->getCacheInformation();
	}
	
	/**
	 * Skeleton action method
	 */
	public function skeletonAction()
	{
	}
	
	/**
	 * Clear snippets and template cache action
	 */
	public function snippetsAction()
	{
		Shopware()->Cache()->clean(Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG, array(
			'Shopware_Config',
			'Shopware_Plugin'
		));
		$this->clearTemplateCache();
		$this->clearCompilerCache();
	}
	
	/**
	 * Clear articles cache action
	 */
	public function articlesAction()
	{
		Shopware()->Cache()->clean(Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG, array(
			'Shopware_Adodb',
			'Shopware_Plugin',
			'Shopware_RouterRewrite'
		));
		$this->clearRewriteCache();
	}
	
	/**
	 * Clear config cache action
	 */
	public function configAction()
	{
		$this->clearTemplateCache();
		$this->clearCompilerCache();
		
		Shopware()->Cache()->clean(Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG, array(
			'Shopware_Config',
			'Shopware_Plugin'
		));
	}
	
	/**
	 * Clear cache action
	 */
	public function clearCacheAction()
	{
		$cache = $this->Request()->getPost('cache');
		
		$tags = array();
		if ($cache['config'] == 'on') {
			$tags[] = 'Shopware_Config';
		}
		if ($cache['plugins'] == 'on') {
			$tags[] = 'Shopware_Plugin';
		}
		if ($cache['seo'] == 'on') {
			$tags[] = 'Shopware_RouterRewrite';
		}
		if ($cache['adodb'] == 'on') {
			$tags[] = 'Shopware_Adodb';
		}
		if ($cache['search'] == 'on') {
			$tags[] = 'Shopware_Modules_Search';
		}
		if(!empty($tags)) {
			Shopware()->Cache()->clean(Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG, $tags);
		}
		
		if ($cache['search'] == 'on') {
			$this->clearSearchCache();
		}
		if ($cache['template'] == 'on') {
			$this->clearTemplateCache();
		}
		if ($cache['seo'] == 'on') {
			$this->clearRewriteCache();
		}
	}
	
	/**
	 * Clear static cache
	 */
	public function clearStaticCacheAction()
	{
		$cache = $this->Request()->getPost('cache');
		
		if ($cache['compiler'] == 'on') {
			$this->clearCompilerCache();
		}
		if ($cache['locale'] == 'on') {
			Shopware()->Cache()->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array());
		}
	}
	
	/**
	 * Clear template cache
	 */
	protected function clearTemplateCache()
	{
		Shopware()->Template()->cache->clear(null, 'frontend');
	}
	
	/**
	 * Clear compiler cache
	 */
	protected function clearCompilerCache()
	{
		Shopware()->Template()->utility->clearCompiledTemplate();
	}
	
	/**
	 * Clear rewrite cache
	 */
	protected function clearRewriteCache()
	{
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
	}
	
	/**
	 * Clear search cache
	 */
	protected function clearSearchCache()
	{
		Shopware()->Db()->exec("UPDATE s_core_config SET value = '' WHERE name = 'sFUZZYSEARCHLASTUPDATE'");
	}
	
	/**
	 * Returns cache information
	 *
	 * @return array
	 */
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
			$info['cache_size'] = (float) 0;
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
	
	/**
	 * Format size method
	 *
	 * @param float $bytes
	 * @return string
	 */
	public static function encodeSize($bytes)
	{
	    $types = array( 'B', 'KB', 'MB', 'GB', 'TB' );
	    for( $i = 0; $bytes >= 1024 && $i < ( count( $types ) -1 ); $bytes /= 1024, $i++ );
	    return( round( $bytes, 2 ) . ' ' . $types[$i] );
	}
}