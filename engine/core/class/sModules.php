<?php
/**
 * Provides access to shopware core classes
 * @link http://www.shopware.de
 * @package core
 * @subpackage class
 * @copyright (C) Shopware AG 2002-2010
 * @version Shopware 3.5.0
 */
class sModules implements ArrayAccess
{
	private $sModulePath;
	private $sSystem;
	private $sModulesList;
	private $sModulesContainer;
	public function __construct ($sSystem)
	{
		$this->sModulePath = $sSystem->sBasePath.'engine/core/class/';
		$this->sCustomPath = $sSystem->sBasePath.'engine/custom/class/';
		$this->sSystem = $sSystem;
		$this->sModulesList = $sSystem->sDB_CONNECTION->GetAssoc('
			SELECT basename as `key`, basename, basefile, inheritname, inheritfile FROM s_core_factory
		');
	}
	public function sGetModule($name)
	{
		if(substr($name, 0, 1)!='s')
		{
			$name = 's'.$name;
		}
		if(!isset($this->sModulesContainer[$name]))
		{
			$this->sModulesContainer[$name] = false;
			$path = $this->sModulePath;
			$name = basename($name);
			$module = isset($this->sModulesList[$name]) ? $this->sModulesList[$name] : array();
			$module['custom'] = 'sCustom'.substr($name, 1);
			$module['customfile'] = $module['custom'].'.php';
			
			/*
			if(empty($module['inheritname']))
			{
				$module['inheritname'] = 'my'.substr($name, 1);
			}
			if(empty($module['inheritfile']))
			{
				$module['inheritfile'] = $module['inheritname'].'.php';
			}
			*/
			if(empty($module['basename']))
			{
				$module['basename'] = $name;
			}
			if(empty($module['basefile']))
			{
				$module['basefile'] = $module['basename'].'.php';
			}
			
			if(!empty($module['inheritfile']) && $name!='sSystem' && file_exists($this->sModulePath.'inherit/'.$module['inheritfile']))
			{
				require_once($this->sModulePath.'inherit/'.$module['inheritfile']);
			}
			elseif(file_exists($this->sModulePath.$module['basefile'] ))
			{
				require_once($this->sModulePath.$module['basefile']);
			}
			if($name!='sSystem'	&& file_exists($this->sCustomPath.$module['customfile']))
			{
				require_once($this->sCustomPath.$module['customfile']);
			}
			
			if(class_exists($module['custom']))
			{
				$this->sModulesContainer[$name] = new $module['custom'];
			}
			elseif(!empty($module['inheritname']) && class_exists($module['inheritname']))
			{
				$this->sModulesContainer[$name] = new $module['inheritname'];
			}
			elseif (class_exists($module['basename']))
			{
				$this->sModulesContainer[$name] = new $module['basename'];
			}
						
			if(!empty($this->sModulesContainer[$name]))
			{
				$this->sModulesContainer[$name]->sSYSTEM = $this->sSystem;
			}
		}
		return $this->sModulesContainer[$name];
	}
    public function offsetSet($offset, $value)
    {
    }
    public function offsetExists($offset)
    {
        return $this->sGetModule($offset)!==null;
    }
    public function offsetUnset($offset)
    {
    }
    public function offsetGet($offset)
    {
        return $this->sGetModule($offset);
    }
    public function __call($name, $value=null)
    {
        return $this->sGetModule($name);
    }
}