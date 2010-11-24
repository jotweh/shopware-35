<?php
require_once(Enlight::Instance()->Path().'Vendor/Smarty/libs/Smarty.class.php');
class Enlight_Template_TemplateManager extends Smarty
{    
    public $helper_manager;
    public $block_manager;
    public $template_old = false;
    public $default_resource_type = 'extends'; 
    
    public function __construct()
    {
    	if (is_callable('mb_internal_encoding')) {
    		$encoding = mb_internal_encoding();
        }
        parent::__construct();
        if (is_callable('mb_internal_encoding')) {
            mb_internal_encoding($encoding);
        }
        $this->request_use_auto_globals = true;
        $this->template_class = 'Enlight_Template_Template';
        $this->plugins_dir = array(dirname(__FILE__).'/Plugins/', SMARTY_PLUGINS_DIR);
        //$this->helper_manager = Enlight::Instance()->Bootstrap()->getRessource('Helper');
        //$this->assignGlobal('this', $this->helper_manager);
        
        $this->allow_phptemplates = true;
		$this->allow_php_tag = true;
    }
        
	const EXTENDS_APPEND = 'append';
    const EXTENDS_PREPEND = 'prepend';
    const EXTENDS_REPLACE = 'replace';
        
    const FILTER_POST = 'post';
    const FILTER_PRE = 'pre';
    const FILTER_OUTPUT = 'output';
    const FILTER_VARIABLE = 'variable';
    
    public function registerFilter($callback, $type = self::FILTER_OUTPUT)
    {
    	$this->registered_filters[$type][$this->_get_filter_name($callback)] = $callback;
    }
    
    public function unregisterFilter($callback, $type = self::FILTER_OUTPUT)
    {
    	$name = $this->_get_filter_name($callback);
    	if(isset($this->registered_filters[$type][$name])) {
    		unset($this->registered_filters[$type][$name]);
    	}
    }

    public function loadFilter($name, $type = self::FILTER_OUTPUT)
    {
    	$this->loadFilter($type, $name);
    }
    
    const PLUGIN_RESOURCE = 'resource';
    const PLUGIN_MODIFIER = 'modifier';
    const PLUGIN_FUNCTION = 'function';
    const PLUGIN_COMPILER = 'compiler';
    const PLUGIN_BLOCK = 'block';
    
    public function registerPlugin($tag, $callback, $type = self::PLUGIN_COMPILER, $cacheable = null, $cache_attr = null)
    {
    	if (isset($this->registered_plugins[$type][$tag])) {
            throw new Exception("Plugin tag \"{$tag}\" already registered");
        } elseif (!is_callable($callback)) {
            throw new Exception("Plugin \"{$tag}\" not callable");
        } elseif ($type==self::PLUGIN_RESOURCE) {
        	$this->_plugins[$type][$tag] = array($callback, false);
        } else {
        	$this->registered_plugins[$type][$tag] = array($callback, (bool) $cacheable);
            if (isset($cache_attr)&&in_array($type, array(self::PLUGIN_BLOCK, self::PLUGIN_FUNCTION))) {
            	$this->registered_plugins[$type][$tag][] = (array) $cache_attr;
            }
        }
    }
        
    public function registerHelper($tag, $class)
    {
         
    }
    
    public function setTemplateDir($template_dir)
    {
    	$template_dir = (array) $template_dir;
    	$template_dir = array_unique($template_dir);
    	return parent::setTemplateDir($template_dir);
    }
    
    public function setHelpers($helpers)
    {
    	$this->helper_manager = $helpers;
    	$helpers->setTemplateManager($this);
    	$this->assignGlobal('this', $this->helper_manager);
    }
    
    public function Helpers()
    {
    	return $this->helper_manager;
    }
    
    public function setTemplateOld($value=true)
    {
    	$this->template_old = (bool) $value;
    	$this->auto_literal = !$this->template_old;
    	return $this;
    }
    
    public function isTemplateOld()
    {
    	return $this->template_old;
    }
    
    public function __call($name, $params=null)
    {
    	switch ($name)
		{
			case 'register_resource':
				return $this->register->resource($params[0], $params[1]);
			case 'register_modifier':
				return $this->register->modifier($params[0], $params[1]);
			case 'register_function':
				return $this->register->templateFunction($params[0], $params[1]);
			default:
				return parent::__call($name, $params);
		}
    }
}