<?php
class Enlight_Config extends Zend_Config implements ArrayAccess
{
	protected $_defaultConfigClass = __CLASS__;
	protected $_nameFilters = array();
	protected $_valueFilters = array();
	
	public function __construct(array $array, $allowModifications = false)
    {
    	$data = array();
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $data[$key] = new $this->_defaultConfigClass($value, $allowModifications);
            } else {
                $data[$key] = $value;
            }
        }
        parent::__construct($data, $allowModifications);
    }
    	
	public function __set($name, $value)
	{
		if ($this->_allowModifications) {
            if (is_array($value)) {
                $value = new $this->_defaultConfigClass($value, true);
            }
        }
		return parent::__set($name, $value);
	}

	public function set($name, $value=null)
	{
		return $this->__set($name, $value);
	}

    public function offsetSet($name, $value)
    {
    	$this->__set($name, $value);
    }
    public function offsetExists($name)
    {
        return $this->__isset($name);
    }
    public function offsetUnset($name)
    {
    	$this->__unset($name);
    }
    public function offsetGet($name)
    {
        return $this->get($name);
    }
}