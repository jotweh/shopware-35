<?php
/**
 * Shopware Config Component
 * 
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Heiner Lohaus
 */
class Shopware_Components_Config extends Enlight_Config
{	
	/**
	 * Constructor method
	 *
	 * @param array $array
	 * @param unknown_type $allowModifications
	 */
	public function __construct(array $array, $allowModifications = false)
    {
    	$data = array();
        foreach ($array as $key => $value) {
        	$data[$this->formatName($key)] = $value;
        }
        parent::__construct($data, $allowModifications);
    }
    
    /**
     * Returns value method
     *
     * @param unknown_type $name
     * @param unknown_type $default
     * @return unknown
     */
	public function get($name, $default = null)
    {
    	$name = $this->formatName($name);
		return parent::get($name, $default);
	}
	
	/**
	 * Set value method
	 *
	 * @param unknown_type $name
	 * @param unknown_type $value
	 * @return unknown
	 */
	public function __set($name, $value)
	{
		$name = $this->formatName($name);
		return parent::__set($name, $value);
	}
	
	/**
	 * Unset value method
	 *
	 * @param unknown_type $name
	 * @return unknown
	 */
	public function __unset($name)
	{
		$name = $this->formatName($name);
		return parent::__unset($name);
	}
	
	/**
	 * Isset value method
	 *
	 * @param unknown_type $name
	 * @return unknown
	 */
	public function __isset($name)
	{
		$name = $this->formatName($name);
		return parent::__isset($name);
	}
    
	/**
	 * Format name method
	 *
	 * @param string $name
	 * @return string
	 */
    public function formatName($name)
    {
    	if(strpos($name, 's') === 0 
    	  && preg_match('#^s[A-Z]#', $name)) {
    		$name = substr($name, 1);
    	}
    	return str_replace('_', '', strtolower($name));
    }    	
}