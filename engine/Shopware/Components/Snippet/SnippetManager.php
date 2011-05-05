<?php
/**
 * Shopware Snippet Manager
 * 
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Heiner Lohaus
 */
class Shopware_Components_Snippet_SnippetManager extends Enlight_Class
{
	protected $locale;
	protected $shop;
	protected $cache;
	protected $snippets;
	protected $snippetMerge;
	
	/**
	 * Returns snippet model instance
	 *
	 * @param unknown_type $namespace
	 * @return Shopware_Models_Snippet
	 */
	public function getSnippet($namespace)
	{
		if($this->shop !== null) {
			$shopId = $this->shop->getId();
		} else {
			$shopId = 1;
		}
		if($this->locale !== null) {
			$localeId = $this->locale->getId();
		} else {
			$localeId = 1;
		}
		if(empty($namespace)) {
			if(!isset($this->snippetMerge)) {
				$this->snippetMerge = new Shopware_Models_Snippet(array(
					'sectionColum' => array('localeID', 'shopID'),
					'section' => array($localeId, $shopId),
					'extends' => array(array(1, 1), array(1, $shopId)),
					'cache' => $this->cache
				));
			}
			return $this->snippetMerge;
		} else {
			if(!isset($this->snippets[$localeId][$namespace])) {
				$this->snippets[$localeId][$namespace] = new Shopware_Models_Snippet(array(
					'section' => array($namespace, $localeId, $shopId),
					'extends' => array(array($namespace, 1, 1), array($namespace, $localeId, 1)),
					'cache' => $this->cache
				));
			}
			return $this->snippets[$localeId][$namespace];
		}
	}
	
	/**
	 * Set locale instance
	 *
	 * @param Shopware_Models_Shop $shop
	 * @return Shopware_Components_Snippet_SnippetManager
	 */
	public function setLocale(Shopware_Models_Locale $locale)
    {
    	$this->locale = $locale;
        return $this;
    }
	
	/**
	 * Set shop instance
	 *
	 * @param Shopware_Models_Shop $shop
	 * @return Shopware_Components_Snippet_SnippetManager
	 */
    public function setShop(Shopware_Models_Shop $shop)
    {
    	$this->shop = $shop;
    	$this->locale = $shop->Locale();
        return $this;
    }
    
    /**
     * Set cache instance
     *
     * @param Zend_Cache_Core $cache
     * @return Shopware_Components_Snippet_SnippetManager
     */
    public function setCache(Zend_Cache_Core $cache)
    {
    	$this->cache = $cache;
        return $this;
    }
}