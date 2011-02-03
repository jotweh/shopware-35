<?php
class Shopware_Components_Config extends Enlight_Config
{	
	public function __construct(array $array, $allowModifications = false)
    {
    	$data = array();
        foreach ($array as $key => $value) {
        	$data[$this->formatName($key)] = $value;
        }
        parent::__construct($data, $allowModifications);
    }
    	
	public function get($name, $default = null)
    {
    	$name = $this->formatName($name);
		return parent::get($name, $default);
	}
	public function __set($name, $value)
	{
		$name = $this->formatName($name);
		return parent::__set($name, $value);
	}
	public function __unset($name)
	{
		$name = $this->formatName($name);
		return parent::__unset($name);
	}
	public function __isset($name)
	{
		$name = $this->formatName($name);
		return parent::__isset($name);
	}
    
    public function formatName($name)
    {
    	if(preg_match('#^s[A-Z]#', $name))
    	{
    		$name = substr($name, 1);
    	}
    	return str_replace('_', '', strtolower($name));
    }    	
}