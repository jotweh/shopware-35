<?php
class Shopware_Components_Modules extends Enlight_Class implements ArrayAccess
{
	protected $module_path;
	protected $system;
	protected $modules_list;
	protected $modules_container = array();
	protected $custom_path;
	public function init()
	{
		$this->module_path = Shopware()->OldPath().'engine/core/class/';
		$this->custom_path = Shopware()->OldPath().'engine/local_old/class/';
		$this->modules_list = Shopware()->Db()->fetchAssoc('
			SELECT basename, basefile, inheritname, inheritfile FROM s_core_factory
		');
	}
	public function setSystem($system)
	{
		$this->system = $system;
	}
	public function loadModule($name)
	{
		if(!isset($this->modules_container[$name]))
		{
			$this->modules_container[$name] = null;
			$path = $this->module_path;
			$name = basename($name);
			$module = isset($this->modules_list[$name]) ? $this->modules_list[$name] : array();
			$module['custom'] = 'sCustom'.substr($name, 1);
			$module['customfile'] = $module['custom'].'.php';
			
			if(empty($module['basename']))
			{
				$module['basename'] = $name;
			}
			if(empty($module['basefile']))
			{
				$module['basefile'] = $module['basename'].'.php';
			}
			
			if(!empty($module['inheritfile']) && $name!='sSystem' && file_exists($this->module_path.'inherit/'.$module['inheritfile']))
			{
				require_once($this->module_path.'inherit/'.$module['inheritfile']);
			}
			elseif(file_exists($this->module_path.$module['basefile'] ))
			{
				require_once($this->module_path.$module['basefile']);
			}
			if($name!='sSystem'	&& file_exists($this->custom_path.$module['customfile']))
			{
				require_once($this->custom_path.$module['customfile']);
			}
			
			if(class_exists($module['custom']))
			{
				$class_name = $module['custom'];
			}
			elseif(!empty($module['inheritname']) && class_exists($module['inheritname']))
			{
				$class_name = $module['inheritname'];
			}
			elseif (class_exists($module['basename']))
			{
				$class_name = $module['basename'];
			}
			if(!empty($class_name))
			{
				Shopware()->Hooks()->setAlias($name, $class_name);
				$proxy = Shopware()->Hooks()->getProxy($class_name);
				$this->modules_container[$name] = new $proxy;
			}
						
			if(!empty($this->modules_container[$name]))
			{
				$this->modules_container[$name]->sSYSTEM = $this->system;
			}
		}
	}
	public function getModule($name)
	{
		if(substr($name, 0, 1)!='s')
		{
			$name = 's'.$name;
		}
		if(!isset($this->modules_container[$name]))
		{
			$this->loadModule($name);
		}
		return $this->modules_container[$name];
	}
    public function offsetSet($offset, $value)
    {
    }
    public function offsetExists($offset)
    {
        return (bool) $this->getModule($offset);
    }
    public function offsetUnset($offset)
    {
    }
    public function offsetGet($offset)
    {
        return $this->getModule($offset);
    }
    public function __call($name, $value=null)
    {
        return $this->getModule($name);
    }
}