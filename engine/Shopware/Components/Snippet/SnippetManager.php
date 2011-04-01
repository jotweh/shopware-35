<?php
class Shopware_Components_Snippet_SnippetManager extends Enlight_Class
{
	protected $locale;
	protected $shop;
	protected $cache;
	protected $snippets;
	protected $snippetMerge;
	
	public function getSnippet($namespace)
	{
		if($this->shop !== null) {
			$shopId = $this->shop->getId();
		} else {
			$shopId = 1;
		}
		if($this->locale !== null) {
			$localeId = $this->locale->getId();
		} elseif($this->locale !== null) {
			$localeId = $this->shop->Locale()->getId();
		} else {
			$localeId = 1;
		}
		if(empty($namespace)) {
			if(!isset($this->snippetMerge)) {
				$this->snippetMerge = new Shopware_Models_Snippet(array(
					'sectionColum' => array('localeID', 'shopID'),
					'section' => array($localeId, $shopId),
					'extends' => array(array(1, $shopId), array(1, 1)),
					'cache' => $this->cache
				));
			}
			return $this->snippetMerge;
		} else {
			if(!isset($this->snippets[$localeId][$namespace])) {
				$this->snippets[$localeId][$namespace] = new Shopware_Models_Snippet(array(
					'section' => array($namespace, $localeId, $shopId),
					'extends' => array(array($namespace, 1, $shopId), array($namespace, 1, 1)),
					'cache' => $this->cache
				));
			}
			return $this->snippets[$localeId][$namespace];
		}
	}
	
	public function setLocale(Shopware_Models_Locale $locale)
    {
    	$this->locale = $locale;
        return $this;
    }
		    
    public function setShop(Shopware_Models_Shop $shop)
    {
    	$this->shop = $shop;
        return $this;
    }
    
    public function setCache(Zend_Cache_Core $cache)
    {
    	$this->cache = $cache;
        return $this;
    }
}