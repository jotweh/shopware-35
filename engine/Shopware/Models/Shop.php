<?php
/**
 * Shopware Shop Model
 * 
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Heiner Lohaus
 */
class Shopware_Models_Shop extends Enlight_Class implements Enlight_Hook
{
    protected static $db;
	protected static $cache;
	
	protected $id;
	protected $name;
	protected $host;
	protected $locale;
	protected $currency;
	protected $config;
	protected $template;
	protected $properties = array();
	
	protected $switchShops = array();
	protected $switchCurrencies = array();
	protected $switchLocales = array();
	
	/**
	 * Constructor method
	 *
	 * @param int|string $shop
	 * @param int|string $locale
	 * @param int|string $currency
	 */
	public function __construct($shop=null, $locale=null, $currency=null)
	{
		//parent::__construct();
		
		if($shop!==null && is_array($shop)) {
			$this->setOptions($shop);
		} else {
			$this->setShop($shop);
		}
		if($locale!==null) {
			$this->setLocale($locale);
		}
		if($currency!==null) {
			$this->setCurrency($currency);
		}
	}
	
	/**
	 * Set shop and read options
	 *
	 * @param int|string $shop
	 * @return Shopware_Models_Shop
	 */
	public function setShop($shop=null)
	{
		if (is_numeric($shop)) {
			$sql = 'SELECT * FROM s_core_multilanguage WHERE id=?';
			$options = self::$db->fetchRow($sql, array($shop));
		} elseif (is_string($shop) && !empty($shop)) {
			$sql = 'SELECT * FROM s_core_multilanguage WHERE domainaliase LIKE ?';
			$options = self::$db->fetchRow($sql, array($shop.'%'));
		} elseif ($shop===null) {
			$sql = 'SELECT * FROM s_core_multilanguage WHERE `default`=1';
			$options = self::$db->fetchRow($sql);
		}
		if($options !== null) {
			$this->setOptions($options);
		}
		$this->config = null;
		return $this;
	}
	
	/**
	 * Find shop id method
	 *
	 * @param string $shop
	 * @return int
	 */
 	public static function findShop($shop = null)
	{
		if (!empty($shop)&&is_numeric($shop)) {
			$sql = 'SELECT id FROM s_core_multilanguage WHERE id=?';
			$result = self::$db->fetchOne($sql, array($shop));
			if(!empty($result)) {
				return (int) $result;
			}
		}
	
		if(empty($shop) && !empty($_SERVER['HTTP_HOST'])) {
			$shop = $_SERVER['HTTP_HOST'];
		}
		
		if (!empty($shop) && is_string($shop)) {
			$sql = 'SELECT id FROM s_core_multilanguage WHERE domainaliase LIKE ?';
			$result =  self::$db->fetchOne($sql, array('%'.$shop.'%'));
			if(!empty($result)) {
				return (int) $result;
			}
		}
		
		$sql = 'SELECT id FROM s_core_multilanguage WHERE `default`=1';
		$shop = (int) self::$db->fetchOne($sql);
		return empty($shop) ? false : $shop;
	}
	
	/**
	 * Set options method
	 *
	 * @param array $options
	 * @return Shopware_Models_Shop
	 */
	public function setOptions(array $options)
	{
		if(isset($options['shop'])) {
			$this->setShop($options['shop']);
			unset($options['shop']);
		}
		foreach ($options as $key => $option) {
			switch ($key) {
				case 'id':
					$this->id = (int) $option;
					break;
				case 'name':
					$this->name = $option;
					break;
				case 'locale':
					$this->setLocale($option);
					break;
				case 'defaultcurrency':
					$this->properties[$key] = $option;
				case 'currency':
					$this->setCurrency($option);
					break;
				case 'switchCurrencies':
					$this->switchCurrencies = explode('|', $option);
					break;
				case 'switchLanguages':
					$this->switchShops = explode('|', $option);
					break;
				case 'host':
					$this->setHost($option);
					break;
				case 'domainaliase':
					if(!empty($option)) {
						$option = explode("\n", $option);
						$host = current($option);
						$this->properties['host'] = $host;
						$this->setHost($host);
					}
					break;
				case 'template':
					$this->setTemplate($option);
					$this->properties[$key] = $option;
					break;
				default:
					$this->properties[$key] = $option;
					break;
			}
		}
		return $this;
	}
	
	/**
	 * Register resources
	 *
	 * @param Enlight_Bootstrap $bootstrap
	 * @return Shopware_Models_Shop
	 */
	public function registerResources(Enlight_Bootstrap $bootstrap)
	{
		$bootstrap->registerResource('Shop', $this);
		$bootstrap->registerResource('Locale', $this->Locale());
		$bootstrap->registerResource('Currency', $this->Currency());
		$bootstrap->registerResource('Config', $this->Config());
		return $this;
	}
	
	/**
	 * Returns the shop id
	 *
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}
	
	/**
	 * Set locale instance
	 *
	 * @param int|string|Shopware_Models_Locale $locale
	 * @return Shopware_Models_Shop
	 */
	public function setLocale($locale = null)
	{
		$this->locale = $locale;
		return $this;
	}
	
	/**
	 * Set shop host method
	 *
	 * @param int|string $host
	 * @return Shopware_Models_Shop
	 */
	public function setHost($host = null)
	{
		if($host === null) {
			if(isset($this->properties['host']) && $this->properties['host'] !== null) {
				return $this->setHost($this->properties['host']);
			} elseif($this->config !== null && $this->config->host !== null) {
				return $this->setHost($this->config->host);
			} else {
				return $this;
			}
		}
		$this->host = trim($host);
		if($this->config!==null) {
			$this->config->basePath = str_replace($this->config->host, $this->host, $this->config->basePath);
			$this->config->host = $this->host;
		}
		return $this;
	}

	/**
	 * Set currency instance
	 *
	 * @param int|string|Shopware_Models_Currency $currency
	 * @return Shopware_Models_Shop
	 */
	public function setCurrency($currency = null)
	{
		$this->currency = $currency;
		return $this;
	}
	
	/**
	 * Set template method
	 *
	 * @param string $template
	 * @return Shopware_Models_Shop
	 */
	public function setTemplate($template = null)
	{
		if($template===null || $template==-1) {
			return $this->setTemplate($this->properties['template']);
		}
		$this->template = preg_replace('#\W#', '', basename($template));
		if($this->config!==null) {
			$this->config['sTEMPLATEPATH'] = dirname($this->config['sTEMPLATEPATH']). '/' .$this->template;
			$this->config['sTEMPLATEOLD'] = (bool) preg_match('#^[0-9]+$#', $this->template);
		}
		return $this;
	}
	
	/**
	 * Set cache instance
	 *
	 * @param Zend_Cache_Core $cache
	 */
	public static function setCache(Zend_Cache_Core $cache=null)
	{
		self::$cache = $cache;
	}
	
	/**
	 * Set database instance
	 *
	 * @param Zend_Db_Adapter_Abstract $db
	 */
	public static function setDb(Zend_Db_Adapter_Abstract $db)
	{
		self::$db = $db;
	}
	
	/**
	 * Returns shop template
	 *
	 * @return string
	 */
	public function getTemplate()
	{
		return $this->template;
	}
	
	/**
	 * Returns shop name
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}
	
	/**
	 * Returns shop host
	 *
	 * @return string
	 */
	public function getHost()
	{
		return $this->host;
	}
	
	/**
	 * Returns option by name
	 *
	 * @param string $name
	 * @return string
	 */
	public function get($name)
	{
		return isset($this->properties[$name]) ? $this->properties[$name] : null;
	}
	
	/**
	 * Returns shop currency
	 *
	 * @return Shopware_Components_Currency
	 */
	public function Currency()
	{
		if (!$this->currency instanceof Shopware_Models_Currency) {
			$this->currency = new Shopware_Models_Currency($this->currency, $this->Locale());
		}
		return $this->currency;
	}
    
	/**
	 * Returns shop locale
	 *
	 * @return Shopware_Models_Locale
	 */
	public function Locale()
	{
		if (!$this->locale instanceof Shopware_Models_Locale) {
			$this->locale = new Shopware_Models_Locale($this->locale);
		}
		return $this->locale;
	}
	
	/**
	 * Init shop config
	 *
	 * @return Shopware_Models_Shop
	 */
	public function initConfig()
	{
		$this->config = new Shopware_Models_Config(array('cache'=>$this->Cache(), 'shop'=>$this));
		
		$this->config['sDefaultCustomerGroup'] = $this->get('defaultcustomergroup');
		if($this->config['sHOSTORIGINAL'] === null) {
			$this->config['sHOSTORIGINAL'] = $this->config['sHOST'];
		}
		
		$this->setTemplate($this->getTemplate());
		$this->setHost($this->getHost());
				
		return $this;
	}
	
	/**
	 * Returns shop config
	 *
	 * @return Shopware_Models_Config
	 */
	public function Config()
	{
		if(!$this->config) {
			$this->initConfig();
		}
		return $this->config;
	}
	
	/**
	 * Returns cache instance
	 *
	 * @return Zend_Cache_Core
	 */
	public static function Cache()
	{
		return self::$cache;
	}
	
	/**
	 * Returns db instance
	 *
	 * @return Zend_Db_Adapter_Abstract
	 */
	public static function Db()
	{
		return self::$db;
	}
	
	/**
	 * Sleep instance method
	 *
	 * @return array
	 */
    public function __sleep()
    {
        return array('id', 'locale', 'currency', 'host', 'template');
    }
    
    /**
     * Wakeup instance method
     */
	public function __wakeup()
    {
    	$locale = $this->locale;
    	$currency = $this->currency;
    	$host = $this->host;
    	$template = $this->template;
    	    	
    	$this->setShop($this->id);
    	$this->setLocale($locale);
    	$this->setCurrency($currency);
    	$this->setHost($host);
    	$this->setTemplate($template);
    }
}