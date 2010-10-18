<?php
class Shopware_Components_Snippet_SnippetManager extends Enlight_Class
{
	protected $locale;
	protected $shop;
	protected $namespace;
	protected $cache;
	
	protected $snippets;
	
	public function getSnippet($namespace=null)
	{
		$localeId = $this->locale->getId();
		$shopId = $this->shop->getId();
		$namespace = $namespace==null ? $this->namespace : $namespace;
			
		if(!isset($this->snippets[$localeId][$namespace])) {
			$this->snippets[$localeId][$namespace] = new Shopware_Models_Snippet(array(
				'section' => array($namespace, $localeId, $shopId),
				'extends' => array(array($namespace, 1, $shopId), array($namespace, 1, 1)),
				'cache' => $this->cache
			));
		}
		
		return $this->snippets[$localeId][$namespace];
	}
	
	public function setNamespace($namespace)
    {
    	$this->namespace = $namespace;
    	return $this;
    }
	    
    public function setShop(Shopware_Models_Shop $shop)
    {
    	$this->shop = $shop;
    	$this->locale = $shop->Locale();
        return $this;
    }
    
    public function setCache(Zend_Cache_Core $cache)
    {
    	$this->cache = $cache;
        return $this;
    }
}