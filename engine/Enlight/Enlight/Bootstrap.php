<?php
abstract class Enlight_Bootstrap extends Enlight_Class implements Enlight_Hook
{		
	const StatusBootstrap = 0;
	const StatusLoaded = 1;
	const StatusNotFound = 2;
	const StatusAssigned = 3;
	
    protected $resource_list = array();
    protected $resource_status = array();
    
    public function run()
    {
    	$front = $this->getResource('Front');
        $front->dispatch();
    }
    
    protected function initFront()
    {
    	$this->loadResource('Zend');
    	
    	$front = Enlight_Class::Instance('Enlight_Controller_Front');
   	    $front->Dispatcher()->addModuleDirectory(Enlight()->AppPath('Controllers'));
        return $front;
    }
        
    protected function initTemplate()
    {
    	$template = Enlight_Class::Instance('Enlight_Template_TemplateManager');
   	    $template->setHelpers($this->getResource('Helpers'));
        return $template;
    }
    
    protected function initHelpers()
    {
   	    $helpers = new Enlight_Helper_HelperManager();
        return $helpers;
    }
    
    protected function initZend()
    {
    	Enlight()->Loader()->registerNamespace('Zend', Shopware()->CorePath('Zend'));
    	Enlight()->Loader()->addIncludePath(Shopware()->CorePath());
    	Enlight()->Loader()->registerNamespace('Zend', Shopware()->VendorPath('Zend_library_Zend'));
    	Enlight()->Loader()->addIncludePath(Shopware()->VendorPath('Zend_library'));
    	return true;
    }
    	
    public function registerResource($name, $resource)
    {
    	$this->resource_list[$name] = $resource;
    	$this->resource_status[$name] = self::StatusAssigned;
    }
    
    public function hasResource($name)
    {
    	return isset($this->resource_list[$name])||$this->loadResource($name);
    }
    
    public function issetResource($name)
    {
    	return isset($this->resource_list[$name]);
    }
    
    public function getResource($name)
    {
        if(!isset($this->resource_status[$name])) {
        	$this->loadResource($name);
        }
    	if($this->resource_status[$name]===self::StatusNotFound) {
    		throw new Enlight_Exception('Resource "'.$name.'" not found failure');
    	}
    	return $this->resource_list[$name];
    }

    public function loadResource($name)
    {
    	if(isset($this->resource_status[$name])) {
    		switch ($this->resource_status[$name]) {
    			case self::StatusBootstrap:
    				throw new Enlight_Exception('Resource "'.$name.'" can\'t resolve all dependencies');
    			case self::StatusNotFound:
    				return false;
    			case self::StatusAssigned:
    			case self::StatusLoaded:
    				return true;
    			default:
    				break;
    		}
    	}
    	
    	try {
	    	$this->resource_status[$name] = self::StatusBootstrap;
	    	if($event = Enlight()->Events()->notifyUntil('Enlight_Bootstrap_InitResource_'.$name, array('subject'=>$this))) {
		    	$this->resource_list[$name] = $event->getReturn();
		    } elseif(method_exists($this, 'init'.$name)) {
		    	$this->resource_list[$name] = call_user_func(array($this, 'init'.$name));
		    }
		    Enlight()->Events()->notify('Enlight_Bootstrap_AfterInitResource_'.$name, array('subject'=>$this));
    	} catch (Exception $e) {
    		$this->resource_status[$name] = self::StatusNotFound;
	    	throw $e;
    	}
	    
	    if(isset($this->resource_list[$name])&&$this->resource_list[$name]!==null) {
	    	$this->resource_status[$name] = self::StatusLoaded;
	    	return true;
	    } else {
	    	$this->resource_status[$name] = self::StatusNotFound;
	    	return false;
	    }
    }
    
    public function resetResource($name)
    {
    	if(isset($this->resource_list[$name])) {
	    	unset($this->resource_list[$name]);
	    	unset($this->resource_status[$name]);
    	}
    	return $this;
    }

    public function __call ($name, $arguments=null)
    {
    	return $this->getResource($name);
    }
}