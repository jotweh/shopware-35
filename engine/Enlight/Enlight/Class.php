<?php
abstract class Enlight_Class
{
	static protected $instances = array();
	
	public function __construct ()
	{
		$class = get_class($this);
		if($this instanceof Enlight_Singleton) {
			if(!isset(self::$instances[$class])) {
				self::$instances[$class] = $this;
			} else {
				throw new Enlight_Exception('Class "'.get_class($this).'" is singleton, please use the instance method');
			}
		}
		if($this instanceof Enlight_Hook && (!$this instanceof Enlight_Hook_Proxy && Enlight::Instance()->Hooks()->hasProxy($class))) {
			throw new Enlight_Exception('Class "'.get_class($this).'" has hooks, please use the instance method');
		}
		if(method_exists($this, 'init')) {
            if(func_num_args()) {
            	$arg_list =& func_get_args();
                call_user_func_array(array($this, 'init'), $arg_list);
            } else {
            	$this->init();
            }
        }
	}
	
	/**
	 * Enter description here...
	 *
	 * @param string $class
	 * @return string
	 */
	public static function getClassName($class=null)
	{
		if(empty($class)) {
			if(function_exists('get_called_class')) {
				$class = get_called_class();
			} else {
				throw new Enlight_Exception('Method not supported');
			}
		}
		
		if(in_array('Enlight_Hook', class_implements($class))) {
    		$class = Enlight::Instance()->Hooks()->getProxy($class);
    	}
    	return $class;
	}

	/**
	 * Enter description here...
	 *
	 * @param string $class
	 * @param array $args
	 * @return Enlight_Class
	 */
	static public function Instance($class=null, $args=null)
	{
		$class = self::getClassName($class);
    	if(isset(self::$instances[$class])) {
			return self::$instances[$class];
		}		
    	
        $rc = new ReflectionClass($class);

        if(isset($args)) {
			$instance = $rc->newInstanceArgs($args);
		} else {
			$instance = $rc->newInstance();
		}
		return $instance;
	}
	
	static public function resetInstance($class=null)
	{
		$class = self::getClassName($class);
		unset(self::$instances[$class]);
	}
	
	public function __call($name, $value=null)
	{
		throw new Enlight_Exception('Method "'.get_class($this).'::'.$name.'" not found failure', Enlight_Exception::Method_Not_Found);
	}
	
	static public function __callStatic($name, $value=null)
	{
		throw new Enlight_Exception('Method "'.get_called_class().'::'.$name.'" not found failure', Enlight_Exception::Method_Not_Found);
	}
	
	public function __get($name)
	{
		throw new Enlight_Exception('Property "'.$name.'" not found failure', Enlight_Exception::Property_Not_Found);
	}
	
	public function __set($name, $value=null)
	{
		throw new Enlight_Exception('Property "'.$name.'" not found failure', Enlight_Exception::Property_Not_Found);
	}
}