<?php
require_once(Enlight::Instance()->Path().'Vendor/Smarty/libs/Smarty.class.php');
class Enlight_Template_TemplateManager extends Smarty
{    
    public $helper_manager;
    public $block_manager;
    public $template_old = false;
    public $default_resource_type = 'extends'; 
    public $ignore_namespace = true;
    
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
                    
    public function setTemplateDir($template_dir)
    {
    	$template_dir = (array) $template_dir;
    	$template_dir = array_unique($template_dir);
    	return parent::setTemplateDir($template_dir);
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