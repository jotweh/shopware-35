<?php
/**
 * Shopware Config Component
 * 
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Heiner Lohaus
 */
class
	Shopware_Components_Check_System implements IteratorAggregate, Countable
{	
	protected $list;
	
	protected function checkAll()
	{
		foreach ($this->list as $requirement) {
			$requirement->version = $this->check($requirement->name);
			$requirement->result = $this->compare(
				$requirement->name,
				$requirement->version,
				$requirement->required
			);
		}
	}
	
	public function getList()
	{
		if($this->list === null) {
			$this->list = new Zend_Config_Xml(
				dirname(__FILE__) . '/Data/System.xml',
				'requirements',
				true
			);
			$this->list = $this->list->requirement;
			$this->checkAll();
		}
		return $this->list;
	}
	
	public function check($name)
	{
		$m = 'check'.str_replace(' ', '', ucwords(str_replace(array('_','.'), ' ', $name)));
		if(method_exists($this, $m)) {
			return $this->$m();
		} elseif (extension_loaded($name)) {
			return true;
		} elseif (function_exists($name)) {
			return true;
		} elseif(($value = ini_get($name))!==null) {
			if(strtolower($value)=='off' || $value==0) {
				return false;
			} elseif (strtolower($value)=='on' || $value==1) {
				return true;
			} else {
				return $value;
			}
			return (!empty($value)&&strtolower($value)!='off');
		} else {
			return null;
		}
	}
	
	public function compare($name, $version, $required)
	{
		$m = 'compare'.str_replace(' ', '', ucwords(str_replace(array('_','.'), ' ', $name)));
		if(method_exists($this, $m)) {
			return $this->$m($version, $required);
		} elseif(preg_match('#^[0-9]+[A-Z]$#', $required)) {
			return $this->decodePhpSize($required)<=$this->decodePhpSize($version);
		} elseif(preg_match('#^[0-9]+ [A-Z]+$#i', $required)) {
			return $this->decodeSize($required)<=$this->decodeSize($version);
		} elseif(preg_match('#^[0-9][0-9\.]+$#', $required)) {
			return version_compare($required, $version, '<=');
		} else {
			return $required==$version;
		}
	}
	
	public function getIterator()
    {
        return $this->getList();
    }
		
	public function checkZendOptimizer()
	{
		if(!extension_loaded('Zend Optimizer')) {
			return false;
		}
		ob_start();
		phpinfo(1);
		$s = ob_get_contents();
		ob_end_clean();
		if(preg_match('/Zend&nbsp;Optimizer&nbsp;v([0-9.]+)/',$s,$match)) {
			return $match[1];
		}
		return false;
	}
	
	public function checkIonCubeLoader()
	{
		if(!extension_loaded('ionCube Loader')) {
			return false;
		}
		ob_start();
		phpinfo(1);
		$s = ob_get_contents();
		ob_end_clean();
		if(preg_match('/ionCube&nbsp;PHP&nbsp;Loader&nbsp;v([0-9.]+)/',$s,$match)) {
			return $match[1];
		}
		return false;
	}
	
	public function checkPhp()
	{
		if(strpos(phpversion(), '-')) {
			return substr(phpversion(), 0, strpos(phpversion(), '-'));
		} else {
			return phpversion();
		}
	}
	
	public function checkMysql()
	{
		if(Shopware()->Db()) {
			$v = Shopware()->Db()->getConnection()->getAttribute(Zend_Db::ATTR_SERVER_VERSION);
			if(strpos($v, '-')) {
				return substr($v, 0, strpos($v, '-'));
			} else {
				return $v;
			}
		}
		return false;
	}
	
	public function checkCurl()
	{
		if (function_exists('curl_version')) {
			$curl = curl_version();
			return $curl['version'];
		} elseif(function_exists('curl_init')) {
			return true;
		} else {
			return false;
		}
	}
	
	public function checkLibXml()
	{
		if(defined('LIBXML_DOTTED_VERSION')) {
			return LIBXML_DOTTED_VERSION;
		} else {
			return false;
		}
	}
	
	public function checkGd()
	{
		if (function_exists('gd_info')) {
			$gd = gd_info();
			if(preg_match('#[0-9.]+#', $gd['GD Version'], $match)) {
				if(substr_count($match[0],'.')==1) {
					$match[0] .='.0';
				}
				return $match[0];
			}
			return $gd['GD Version'];
		} else {
			return false;
		}
	}
	
	public function checkGdJpg()
	{
		if (function_exists('gd_info')) {
			$gd = gd_info();
			return !empty($gd['JPEG Support'])||!empty($gd['JPG Support']);
		} else {
			return false;
		}
	}
	
	public function checkFreetype()
	{
		if (function_exists('gd_info')) {
			$gd = gd_info();
			return !empty($gd['FreeType Support']);
		} else {
			return false;
		}
	}
	
	public function checkSessionSavePath()
	{
		if (function_exists('session_save_path')) {
			return (bool) session_save_path();
		} elseif (ini_get('session.save_path')) {
			return true;
		} else {
			return false;
		}
	}
	
	public function checkMagicQuotes()
	{
		if(function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc()) {
			return true;
		} elseif(function_exists('get_magic_quotes_runtime') && get_magic_quotes_runtime()) {
			return true;
		} else {
			return false;
		}
	}
	
	public function checkDiskFreeSpace()
	{
		if(function_exists('disk_free_space')) {
			return $this->encodeSize(disk_free_space(dirname(__FILE__)));
		} else {
			return false;
		}
	}
		
	public function checkIncludePath()
	{
		if (function_exists('set_include_path')) {
			$old = set_include_path(get_include_path().PATH_SEPARATOR.dirname(__FILE__).DIRECTORY_SEPARATOR);
			return $old && get_include_path()!=$old;
		} else {
			return false;
		}
	}
	
	public function compareMaxExecutionTime($version, $required)
	{
		if(!$version) {
			return true;
		}
		return version_compare($required, $version, '<=');
	}
	
	public static function decodePhpSize ($val)
	{
	    $val = trim($val);
	    $last = strtolower($val[strlen($val)-1]);
	    switch($last) 
	    {
	        case 'g':
	            $val *= 1024;
	        case 'm':
	            $val *= 1024;
	        case 'k':
	            $val *= 1024;
	    }
	    return $val;
	}
	
	public static function decodeSize ($val)
	{
	    $val = trim($val);
	    list($val, $last) = explode(' ',$val);
	    switch(strtoupper($last))
	    {
	    	case 'TB':
	            $val *= 1024;
	        case 'GB':
	            $val *= 1024;
	        case 'MB':
	            $val *= 1024;
	        case 'KB':
	            $val *= 1024;
	        case 'B':
	            $val = (float) $val;
	    }
	    return $val;
	}
	
	public static function encodeSize($bytes)
	{
	    $types = array( 'B', 'KB', 'MB', 'GB', 'TB' );
	    for( $i = 0; $bytes >= 1024 && $i < ( count( $types ) -1 ); $bytes /= 1024, $i++ );
	    return( round( $bytes, 2 ) . ' ' . $types[$i] );
	}
	
	public function toArray()
    {
    	return $this->getList()->toArray();
    }
    
    public function count()
    {
    	return $this->getList()->count();
    }
}