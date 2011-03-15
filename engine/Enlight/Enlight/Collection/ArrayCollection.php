<?php
/**
 * Enlight Collection
 * 
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Heiner Lohaus
 */
class Enlight_Collection_ArrayCollection implements Enlight_Collection_Collection
{
	protected $_elements;

	/**
	 * Constructor method
	 *
	 * @param array $elements
	 */
	public function __construct($elements = array())
	{
		$this->_elements = (array) $elements;
	}

	/**
	 * Count elements method
	 *
	 * @return int
	 */
	public function count()
	{
		return isset($this->_elements) ? 0 : count($this->_elements);
	}
	
	/**
	 * Set element method
	 *
	 * @param string $key
	 * @param mixed $value
	 * @return Enlight_Collection_ArrayCollection
	 */
	public function set($key, $value)
	{
		$this->_elements[$key] = $value;
		return $this;
	}
	
	/**
	 * Returns element method
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function get($key)
	{
		return $this->containsKey($key) ? $this->_elements[$key] : null;
	}
	
	/**
	 * Contains element
	 *
	 * @param string $key
	 * @return bool
	 */
	public function containsKey($key)
	{
		return isset($this->_elements[$key]);
	}
	
	/**
	 * Remove element method
	 *
	 * @param string $key
	 * @return Enlight_Collection_ArrayCollection
	 */
	public function remove($key)
	{
		unset($this->_elements[$key]);
		return $this;
	}

	/**
	 * Contains element
	 *
	 * @param string $key
	 * @return bool
	 */
	public function offsetExists($key)
	{
		return $this->containsKey($key);
	}
	
	/**
	 * Remove element method
	 *
	 * @param unknown_type $key
	 */
	public function offsetUnset($key)
	{
		$this->remove($key);
	}
	
	/**
	 * Returns element method
	 *
	 * @param string $key
	 */
	public function offsetGet($key)
	{
		return $this->get($key);
	}
	
	/**
	 * Set element method
	 *
	 * @param string $key
	 * @param mixed $value
	 */
	public function offsetSet($key, $value)
	{
		$this->set($key, $value);
	}

	/**
	 * Returns iterator
	 *
	 * @return Iterator
	 */
	public function getIterator()
	{
		$ref = &$this->_elements;
		return new ArrayIterator($ref);
	}

	/**
	 * Set element method
	 *
	 * @param string $key
	 * @param mixed $value
	 */
	public function __set($key, $value = null)
	{
		$this->set($key, $value);
	}
	
	/**
	 * Returns element method
	 *
	 * @param string $key
	 */
	public function __get($key)
	{
		return $this->get($key);
	}
	
	/**
	 * Contains element
	 *
	 * @param string $key
	 * @return bool
	 */
	public function __isset($key)
	{
		return $this->containsKey($key);
	}
	
	/**
	 * Remove element method
	 *
	 * @param string $key
	 * @return Enlight_Collection_ArrayCollection
	 */
	public function __unset($key)
	{
		$this->remove($key);
	}
	
	/**
	 * Call setter or getter method
	 *
	 * @param string $name
	 * @param array $args
	 * @return mixed
	 */
	function __call($name, $args = null)
	{
		switch (substr($name, 0, 3)) {
			case 'get':
				$key = strtolower(substr($name, 3, 1)).substr($name, 4);
				$key = strtolower(preg_replace('/([A-Z])/', '_$0', $key));
				return $this->get($key);
			case 'set':
				$key = strtolower(substr($name, 3, 1)).substr($name, 4);
				$key = strtolower(preg_replace('/([A-Z])/', '_$0', $key));
				
				return $this->set($key, isset($args[0]) ? $args[0] : null);
			default:
				throw new Enlight_Exception('Method "'.get_class($this).'::'.$name.'" not found failure', Enlight_Exception::Method_Not_Found);
		}
	}
}