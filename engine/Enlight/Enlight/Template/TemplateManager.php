<?php
require_once(Enlight::Instance()->Path() . 'Vendor/Smarty/libs/Smarty.class.php');

/**
 * Enlight Template Manager
 * 
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Heiner Lohaus
 */
class Enlight_Template_TemplateManager extends Smarty
{    
    public $helper_manager;
    public $block_manager;
    public $template_old = false;
    public $default_resource_type = 'extends'; 
    public $ignore_namespace = false;
    
    /**
     * Class constructor, initializes basic smarty properties
     */
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
        
        $this->allow_phptemplates = true;
		$this->allow_php_tag = true;
    }

    /**
     * Sets the template dir
     *
     * @param array|string $template_dir
     * @return void
     */
    public function setTemplateDir($template_dir)
    {
    	$template_dir = (array) $template_dir;
    	$template_dir = array_unique($template_dir);
    	return parent::setTemplateDir($template_dir);
    }
    
    /**
     * Sets the template old mode
     *
     * @param bool $value
     * @return Enlight_Template_TemplateManager
     */
    public function setTemplateOld($value=true)
    {
    	$this->template_old = (bool) $value;
    	$this->auto_literal = !$this->template_old;
    	return $this;
    }
    
    /**
     * Checks if template is old
     *
     * @deprecated
     * @return bool
     */
    public function isTemplateOld()
    {
    	return $this->template_old;
    }
    
    /**
     * Sets helper manager instance
     *
     * @deprecated
     * @param Enlight_Helper_HelperManager $helpers
     */
    public function setHelpers($helpers)
    {
    	$this->helper_manager = $helpers;
    	$helpers->setTemplateManager($this);
    	$this->assignGlobal('this', $this->helper_manager);
    }
    
    /**
     * Returns helper manager instance
     *
     * @deprecated
     * @return Enlight_Helper_HelperManager
     */
    public function Helpers()
    {
    	return $this->helper_manager;
    }
    
    /**
     * Magic caller
     * 
     * Captures old register methods
     * 
     * @param string $name unknown methode name
     * @param array $args argument array
     */
    public function __call($name, $args)
    {
    	switch ($name) {
			case 'register_resource':
				return $this->register->resource($args[0], $args[1]);
			case 'register_modifier':
				return $this->register->modifier($args[0], $args[1]);
			case 'register_function':
				return $this->register->templateFunction($args[0], $args[1]);
			default:
				return parent::__call($name, $args);
		}
    }
}