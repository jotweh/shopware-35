<?php
class Enlight_Loader extends Enlight_Class
{	
	private $namespaces = array();
	private $loaded_clasess = array();
	
	const Default_Separator = '_\\';
	const Default_Extension = '.php';
	
	public function init()
	{
		if (version_compare(PHP_VERSION, '5.3.0') >= 0) {
			spl_autoload_register(array($this, 'autoload'), true, true);
		} else {
			spl_autoload_register(array($this, 'autoload'), true);
		}
	}
	
	public function loadClass($class, $path=null)
	{
		if(is_array($class)) {
			return min(array_map(array($this, __METHOD__), $class));
		}
		if(!is_string($class)) {
    		throw new Enlight_Exception('Class name must be a string');
    	}
		if(!$this->isLoaded($class))
		{
			if(!$path) {
				$path = $this->getClassPath($class);
			}
			if($path) {
				$this->includeFile($path);
			}
		}
		if(!$this->isLoaded($class)) {
			return false;
		}
		
		$this->loaded_clasess[] = $class;
		return true;
	}
	
	public static function includeFile($path)
	{
		$path_org = $path;
        $path = realpath($path);
        if(!self::checkFile($path))
		{
			throw new Enlight_Exception('Security check: Illegal character in filename');
		}
		if(!file_exists($path)||!is_file($path))
		{
			throw new Enlight_Exception('File "'.$path_org.'" not exists failure');
		}
		@ob_start();
		include($path);
		@ob_end_clean();
	}
	
	public static function isReadable($path)
	{
		return self::checkFile($path)&&file_exists($path)&&is_file($path);
	}
	
	public static function explodeIncludePath($path = null)
    {
        if (null === $path) {
            $path = get_include_path();
        }

        if (PATH_SEPARATOR == ':') {
            // On *nix systems, include_paths which include paths with a stream 
            // schema cannot be safely explode'd, so we have to be a bit more
            // intelligent in the approach.
            $paths = preg_split('#:(?!//)#', $path);
        } else {
            $paths = explode(PATH_SEPARATOR, $path);
        }
        return $paths;
    }

	public function getClassPath($class)
	{
		foreach ($this->namespaces as  $namespace)
		{
			if(strpos($class, $namespace['namespace'])===0)
			{
				$path = substr($class, strlen($namespace['namespace'])+1);
				$path = str_replace(str_split($namespace['separator']), DIRECTORY_SEPARATOR, $path);
				$path = $namespace['path'].$path.$namespace['extension'];
				if(file_exists($path)) {
					return $path;
				}
			}
		}
		return false;
	}
	
	public function isLoaded($class)
	{
		return class_exists($class, false)||interface_exists($class, false);
	}
	
	public function registerNamespace($namespace, $path, $separator=self::Default_Separator, $extension=self::Default_Extension)
	{
		$this->namespaces[] = array(
			'namespace' => $namespace,
			'path' => $path,
			'separator' => $separator,
			'extension' => $extension
		);
	}
	
	public static function addIncludePath($path)
	{
		if(is_array($path)) {
			return (bool) array_map(__METHOD__, $path);
		}
		if(!is_string($path)||!file_exists($path)||!is_dir($path)) {
    		throw new Enlight_Exception('Path "'.$path.'" is not a dir failure');
    	}
    	
    	$paths = self::explodeIncludePath();
        
        if (array_search($path, $paths) !== false) {
        	return true;
        }
        
        array_push($paths, $path);
        
        $old = set_include_path(implode(PATH_SEPARATOR, $paths));
        if(!$old || $old == get_include_path()) {
        	throw new Enlight_Exception('Include path "'.$path.'" could not be added failure');
        }
        
        return true;
	}
	
	public function getLoadedClasses()
	{
		return $this->loaded_clasess;
	}
	
	public function autoload($class)
	{
		try
		{
			$this->loadClass($class);
		}
		catch (Exception $e) { }
	}
	
	public static function checkFile($path)
    {
        return !preg_match('/[^a-z0-9 \\/\\\\_.:-]/i', $path);
    }
}