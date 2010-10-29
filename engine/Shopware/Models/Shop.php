<?php
class Shopware_Models_Shop extends Enlight_Class implements Enlight_Hook
{
    protected static $_db;
	protected static $_cache;
	
	protected $_id;
	protected $_name;
	protected $_host;
	protected $_locale;
	protected $_currency;
	protected $_config;
	protected $_template;
	protected $_properties = array();
	
	protected $_switchShops = array();
	protected $_switchCurrencies = array();
	protected $_switchLocales = array();
	
	public function __construct($shop=null, $locale=null, $currency=null)
	{
		if($shop!==null&&is_array($shop)) {
			$this->setOptions($shop);
		} else {
			$this->setShop($shop);
		}
		if($locale!==null) {
			$this->setLocale($locale);
		}
		if($currency!==null) {
			$this->setLocale($currency);
		}
	}
	
	public function setShop($shop=null)
	{
		if (is_numeric($shop)) {
			$sql = 'SELECT * FROM s_core_multilanguage WHERE id=?';
			$options = self::$_db->fetchRow($sql, array($shop));
		} elseif (is_string($shop)&&!empty($shop)) {
			$sql = 'SELECT * FROM s_core_multilanguage WHERE domainaliase LIKE ?';
			$options = self::$_db->fetchRow($sql, array($shop.'%'));
		} elseif ($shop===null) {
			$sql = 'SELECT * FROM s_core_multilanguage WHERE `default`=1';
			$options = self::$_db->fetchRow($sql);
		}
		if(isset($options)) {
			$this->setOptions($options);
		}
		$this->_config = null;
	}
	
	public static function findShop($shop=null)
	{
		if (!empty($shop)&&is_numeric($shop)) {
			$sql = 'SELECT id FROM s_core_multilanguage WHERE id=?';
			$result = self::$_db->fetchOne($sql, array($shop));
			if(!empty($result)) {
				return (int) $result;
			}
		}
	
		if(empty($shop)&&!empty($_SERVER['SERVER_NAME'])) {
			$shop = $_SERVER['SERVER_NAME'];
		}
		
		if (!empty($shop)&&is_string($shop)) {
			$sql = 'SELECT id FROM s_core_multilanguage WHERE domainaliase LIKE ?';
			$result =  self::$_db->fetchOne($sql, array('%'.$shop.'%'));
			if(!empty($result)) {
				return (int) $result;
			}
		}
		
		$sql = 'SELECT id FROM s_core_multilanguage WHERE `default`=1';
		$shop = (int) self::$_db->fetchOne($sql);
		return empty($shop) ? false : $shop;
	}
	
	public function setOptions($options)
	{
		if(isset($options['shop'])) {
			$this->setShop($options['shop']);
			unset($options['shop']);
		}
		foreach ($options as $key => $option) {
			switch ($key) {
				case 'id':
					$this->_id = (int) $option;
					break;
				case 'name':
					$this->_name = $option;
					break;
				case 'locale':
					$this->setLocale($option);
					break;
				case 'defaultcurrency':
					$this->_properties[$key] = $option;
				case 'currency':
					$this->setCurrency($option);
					break;
				case 'switchCurrencies':
					$this->_switchCurrencies = explode('|', $option);
					break;
				case 'switchLanguages':
					$this->_switchShops = explode('|', $option);
					break;
				case 'host':
					$this->setHost($option);
					break;
				case 'domainaliase':
					if(!empty($option)) {
						$option = explode("\n", $option);
						$this->setHost(current($option));
					}
					break;
				case 'template':
					$this->setTemplate($option);
					$this->_properties[$key] = $option;
					break;
				default:
					$this->_properties[$key] = $option;
					break;
			}
		}
		return $this;
	}
	
	public function registerResources(Enlight_Bootstrap $bootstrap)
	{
		$bootstrap->registerResource('Shop', $this);
		$bootstrap->registerResource('Locale', $this->Locale());
		$bootstrap->registerResource('Currency', $this->Currency());
		$bootstrap->registerResource('Config', $this->Config());
	}
	
	public function getId()
	{
		return $this->_id;
	}
	
	public function setLocale($locale = null)
	{
		if ($locale instanceof Shopware_Models_Locale) {
			$this->_locale = $locale;
		} else {
			$this->_locale = new Shopware_Models_Locale($locale);
		}
		return $this;
	}
	
	public function setHost($host = null)
	{
		if($host!==null) {
			$this->_host = trim($host);
		} else {
			unset($this->_host);
		}
	}
		
	public function setCurrency($currency = null)
	{
		if ($currency instanceof Shopware_Models_Currency) {
			$this->_currency = $currency;
		} else {
			$this->_currency = new Shopware_Models_Currency($currency, $this->Locale());
		}
		return $this;
	}
	
	public static function setCache(Zend_Cache_Core $cache=null)
	{
		self::$_cache = $cache;
	}
	
	public static function setDb($db)
	{
		self::$_db = $db;
	}
	
	public function initConfig()
	{
		$this->_config = new Shopware_Models_Config(array('cache'=>$this->Cache(), 'shop'=>$this));
		
		if(!isset($this->_config['sHOSTORIGINAL'])) {
			$this->_config['sHOSTORIGINAL'] = $this->_config['sHOST'];
		}
		$this->_config['sBASEPATH'] = str_replace($this->_config['sHOST'], $this->getHost(), $this->_config['sBASEPATH']);
		$this->_config['sHOST'] = $this->getHost();
		$this->_config['sTEMPLATEPATH'] = dirname($this->_config['sTEMPLATEPATH']).'/'.$this->getTemplate();
		$this->_config['sTEMPLATEOLD'] = (bool) preg_match('#^[0-9]+$#', $this->getTemplate());
				
		return $this;
	}
		
	public function setTemplate($template=null)
	{
		if($template!==null&&$template!=-1) {
			$this->_template = preg_replace('#\W#', '', basename($template));
			if($this->_config!==null) {
				$this->_config['sTEMPLATEPATH'] = dirname($this->_config['sTEMPLATEPATH']).'/'.$this->_template;
				$this->_config['sTEMPLATEOLD'] = (bool) preg_match('#^[0-9]+$#', $this->_template);
			}
		} else {
			$this->setTemplate($this->_properties['template']);
		}
		return $this;
	}
	
	public function getTemplate()
	{
		return $this->_template;
	}
	
	public function getName()
	{
		return $this->_name;
	}
	
	public function getHost()
	{
		return $this->_host;
	}
	
	public function get($name)
	{
		return isset($this->_properties[$name]) ? $this->_properties[$name] : null;
	}
	
	/**
	 * Enter description here...
	 *
	 * @return Shopware_Components_Currency
	 */
	public function Currency()
	{
		return $this->_currency;
	}
    
	/**
	 * Enter description here...
	 *
	 * @return Shopware_Models_Locale
	 */
	public function Locale()
	{
		return $this->_locale;
	}
	
	/**
	 * Enter description here...
	 *
	 * @return Shopware_Models_Locale
	 */
	public function Config()
	{
		if(!$this->_config) {
			$this->initConfig();
		}
		return $this->_config;
	}
	
	/**
	 * Enter description here...
	 *
	 * @return Zend_Cache_Core
	 */
	public static function Cache()
	{
		return self::$_cache;
	}
	
    public function __sleep()
    {
        return array('_id', '_locale', '_currency', '_host', '_template');
    }
    
	public function __wakeup()
    {
    	$locale = $this->_locale;
    	$currency = $this->_currency;
    	$host = $this->_host;
    	$template = $this->_template;
    	
    	$this->setShop($this->_id);
    	$this->setLocale($locale);
    	$this->setCurrency($currency);
    	$this->setHost($host);
    	$this->setTemplate($template);
    }
}