<?php
/**
 * Enlight Bootstrap
 * 
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Heiner Lohaus
 */
abstract class Enlight_Bootstrap extends Enlight_Class implements Enlight_Hook
{		
	const STATUS_BOOTSTRAP = 0;
	const STATUS_LOADED = 1;
	const STATUS_NOT_FOUND = 2;
	const STATUS_ASSIGNED = 3;
	
	/**
	 * Resource list
	 *
	 * @var array
	 */
    protected $resourceList = array();
    
    /**
     * Resource status list
     *
     * @var array
     */
    protected $resourceStatus = array();
    
    /**
     * Run application method
     * 
     * @return mixed
     */
    public function run()
    {
    	$front = $this->getResource('Front');
        return $front->dispatch();
    }
    
    /**
     * Init front method
     *
     * @return Enlight_Controller_Front
     */
    protected function initFront()
    {
    	$this->loadResource('Zend');
    	
    	$front = Enlight_Class::Instance('Enlight_Controller_Front');
   	    $front->Dispatcher()->addModuleDirectory(Enlight()->AppPath('Controllers'));
   	    
   	    $config = Enlight()->getOption('Front');
    	$front->setParams($config);
    	    	
    	if(!empty($config['throwExceptions'])) {
    		$front->throwExceptions(true);
    	}
    	if(!empty($config['returnResponse'])) {
    		$front->returnResponse(true);
    	}
   	    
        return $front;
    }
       
    /**
     * Init template method
     *
     * @return Enlight_Template_TemplateManager
     */ 
    protected function initTemplate()
    {
    	$template = Enlight_Class::Instance('Enlight_Template_TemplateManager');
   	    $template->setHelpers($this->getResource('Helpers'));
        return $template;
    }
    
    /**
     * Init helper method
     *
     * @return Enlight_Helper_HelperManager
     */
    protected function initHelpers()
    {
   	    $helpers = new Enlight_Helper_HelperManager();
        return $helpers;
    }
    
    /**
     * Init zend method
     *
     * @return bool
     */
    protected function initZend()
    {
    	Enlight()->Loader()->registerNamespace('Zend', Enlight()->CorePath('Zend'));
    	Enlight()->Loader()->addIncludePath(Enlight()->CorePath());
    	Enlight()->Loader()->registerNamespace('Zend', Enlight()->VendorPath('Zend_library_Zend'));
    	Enlight()->Loader()->addIncludePath(Enlight()->VendorPath('Zend_library'));
    	return true;
    }
    
    /**
     * Register resource method
     *
     * @param string $name
     * @param mixed $resource
     * @return Enlight_Bootstrap
     */
    public function registerResource($name, $resource)
    {
    	$this->resourceList[$name] = $resource;
    	$this->resourceStatus[$name] = self::STATUS_ASSIGNED;
    	return $this;
    }
    
    /**
     * Has resource method
     *
     * @param string $name
     * @return bool
     */
    public function hasResource($name)
    {
    	return isset($this->resourceList[$name])||$this->loadResource($name);
    }
    
    /**
     * Returns resource method
     *
     * @param string $name
     * @return bool
     */
    public function issetResource($name)
    {
    	return isset($this->resourceList[$name]);
    }
    
    /**
     * Returns resource method
     *
     * @param string $name
     * @return Enlight_Class
     */
    public function getResource($name)
    {
        if(!isset($this->resourceStatus[$name])) {
        	$this->loadResource($name);
        }
    	if($this->resourceStatus[$name]===self::STATUS_NOT_FOUND) {
    		throw new Enlight_Exception('Resource "'.$name.'" not found failure');
    	}
    	return $this->resourceList[$name];
    }

    /**
     * Load resource method
     *
     * @param string $name
     * @return bool
     */
    public function loadResource($name)
    {
    	if(isset($this->resourceStatus[$name])) {
    		switch ($this->resourceStatus[$name]) {
    			case self::STATUS_BOOTSTRAP:
    				throw new Enlight_Exception('Resource "'.$name.'" can\'t resolve all dependencies');
    			case self::STATUS_NOT_FOUND:
    				return false;
    			case self::STATUS_ASSIGNED:
    			case self::STATUS_LOADED:
    				return true;
    			default:
    				break;
    		}
    	}
    	
    	try {
	    	$this->resourceStatus[$name] = self::STATUS_BOOTSTRAP;
	    	if($event = Enlight()->Events()->notifyUntil('Enlight_Bootstrap_InitResource_'.$name, array('subject'=>$this))) {
		    	$this->resourceList[$name] = $event->getReturn();
		    } elseif(method_exists($this, 'init'.$name)) {
		    	$this->resourceList[$name] = call_user_func(array($this, 'init'.$name));
		    }
		    Enlight()->Events()->notify('Enlight_Bootstrap_AfterInitResource_'.$name, array('subject'=>$this));
    	} catch (Exception $e) {
    		$this->resourceStatus[$name] = self::STATUS_NOT_FOUND;
	    	throw $e;
    	}
	    
	    if(isset($this->resourceList[$name])&&$this->resourceList[$name]!==null) {
	    	$this->resourceStatus[$name] = self::STATUS_LOADED;
	    	return true;
	    } else {
	    	$this->resourceStatus[$name] = self::STATUS_NOT_FOUND;
	    	return false;
	    }
    }
    
    /**
     * Reset resource method
     *
     * @param string $name
     * @return Enlight_Bootstrap
     */
    public function resetResource($name)
    {
    	if(isset($this->resourceList[$name])) {
	    	unset($this->resourceList[$name]);
	    	unset($this->resourceStatus[$name]);
    	}
    	return $this;
    }

    /**
     * Returns called resource
     *
     * @param string $name
     * @param array $arguments
     * @return Enlight_Class Resource
     */
    public function __call ($name, $arguments=null)
    {
    	return $this->getResource($name);
    }
}