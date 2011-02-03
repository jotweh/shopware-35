<?php
class Enlight_Collection_ArrayCollection implements Enlight_Collection_Collection
{
    protected $_elements; 
    
    public function __construct($elements)
    {
        $this->_elements = (array) $elements;
    }
    
    public function count()
    {
        return isset($this->_elements) ? 0 : count($this->_elements);
    }
    public function set($key, $value)
    {
        $this->_elements[$key] = $value;
    }
    public function get($key)
    {
        return $this->containsKey($key) ? $this->_elements[$key] : null;
    }
    public function containsKey($key)
    {
        return isset($this->_elements[$key]);
    }
    public function remove($key)
    {
        unset($this->_elements[$key]);
    }
    
   	public function offsetExists($key)
    {
        return $this->containsKey($key);
    }
    public function offsetUnset($key)
    {
        $this->remove($key);
    }
    public function offsetGet($key)
    {
        return $this->get($key);
    }
    public function offsetSet($key, $value)
    {
        $this->set($key, $value);
    }
    
    public function getIterator()
    {
        $ref = &$this->_elements;
        return new ArrayIterator($ref);
    }
    
    public function __set($key, $value=null)
    {
        $this->set($key, $value);
    }
    public function __get($key)
    {
        return $this->get($key);
    }
    public function __isset($key)
    {
        return $this->containsKey($key);
    }
    public function __unset($key)
    {
        $this->remove($key);
    }
    function __call($name, $args = null)
    {
        static $camel_func;
        if(!isset($camel_func))
            $camel_func = create_function('$c', 'return "_" . strtolower($c[1]);'); 
        switch (substr($name, 0, 3))
        {
            case 'get':
            case 'set':
                $key = strtolower(substr($name, 3, 1)) . substr($name, 4);
                $key = preg_replace_callback('/([A-Z])/', $camel_func, $key);
            case 'get':
                return $this->get($key);
            case 'set':
                return $this->set($key, iseet($args[0]) ? $args[0] : null);
            default:
                return parent::__call($name, $args);
        }
    }
}