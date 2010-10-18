<?php
class Enlight_Helper_HelperManager extends Enlight_Class
{
	protected $template_manager = array();
    protected $_helper_list = array();
    protected $_helper_dir = array();
    
    public function init()
    {
        $this->_helper_dir = array('Enlight_Helper_'=>Enlight::Instance()->CorePath('Helper'));
    }
    
	public function __call($name, $args=null)
    {
        if($helper = $this->getHelper($name))
        {
            return $helper;
        }
        elseif($this->template_manager->loadPlugin('smarty_modifier_'.$name))
        {
            return call_user_func_array('smarty_modifier_'.$name, $args);
        }
    }
    
    public function __get($name)
    {
        return $this->getHelper($name);
    }
    
    static public function addPath($path, $prefix = 'Enlight_Helper')
    {
        self::getPluginLoader()->addPrefixPath($prefix, $path);
    }
    
    public function getHelper($name)
    {
    	$this->loadHelper($name);
        if(isset($this->_helper_list[$name]))
        {
        	return $this->_helper_list[$name];
        }
    }
    
    public function loadHelper($name)
    {
		if(!isset($this->_helper_list[$name]))
        {
        	foreach ($this->_helper_dir as $namespace => $dir)
        	{
        		if(file_exists($dir.$name.'.php'))
        		{
        			$class = $namespace.$name;
        			$this->_helper_list[$name] = new $class($this->template_manager);
        		}
        	}
        }
    }
    
    public function setTemplateManager($template_manager)
    {
    	$this->template_manager = $template_manager;
    }
    
    public function setAction()
    {
    }
}