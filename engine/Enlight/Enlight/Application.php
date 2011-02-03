<?php
class Enlight_Application
{
	protected $environment;
	protected $options;
	
	protected $ds;			// Directory seperator
	protected $path;		// Framework path
	protected $app;			// Application name
	protected $app_path;	// Application path
	protected $core_path;	// Framework core path
	
	protected static $_instance;
	protected $_loader;
	protected $_hooks;
	protected $_events;
	protected $_plugins;
	
	protected $_bootstrap;

	public function __construct($environment, $options = null)
	{
		self::$_instance = $this;
		
		$this->environment = $environment;
		
		$options = $this->loadConfig($options);
		
		$this->ds = DIRECTORY_SEPARATOR;
		$this->path = dirname(dirname(__FILE__)).$this->ds;
		
		if(!empty($options['app'])) {
			$this->app = $options['app'];
		} else {
			$this->app = 'Default';
		}
		if(!empty($options['app_path'])) {
			$this->app_path = realpath($options['app_path']).$this->ds;
		} else {
			$this->app_path = realpath($this->path.'Apps/'.$app).$this->ds;
		}
			
		$this->core_path = $this->path.'Enlight'.$this->ds;
		
		if(!file_exists($this->app_path)&&!is_dir($this->app_path))
		{
			throw new Exception('App "'.$this->app.'" with path "'.$this->app_path.'" not found failure');
		}
		
		require_once($this->CorePath().'Exception.php');
        require_once($this->CorePath().'Hook.php');
        require_once($this->CorePath().'Singleton.php');
		require_once($this->CorePath().'Class.php');
		require_once($this->CorePath().'Loader.php');
		
		$this->_loader = new Enlight_Loader();
		$this->_loader->registerNamespace('Enlight', $this->CorePath());
		$this->_loader->registerNamespace($this->App(), $this->AppPath());
		
		$this->setOptions($options);
		
		$this->_hooks = new Enlight_Hook_HookManager();
		$this->_events = new Enlight_Event_EventManager();
		$this->_plugins = new Enlight_Plugin_PluginManager();
	}
	
	public function run()
	{
		return $this->Bootstrap()->run();
	}
	
	public static function DS()
	{
		return self::$_instance->ds;
	}
	public function Path($path=null)
	{
		if(isset($path))
		{
			$path = str_replace('_', $this->ds, $path);
			return $this->path.$path.$this->ds;
		}
		return $this->path;
	}
	public function AppPath($path=null)
	{
		if(isset($path))
		{
			$path = str_replace('_', $this->ds, $path);
			return $this->app_path.$path.$this->ds;
		}
		return $this->app_path;
	}
	public function CorePath($path=null)
	{
		if(isset($path))
		{
			$path = str_replace('_', $this->ds, $path);
			return $this->core_path.$path.$this->ds;
		}
		return $this->core_path;
	}
	public function ComponentsPath()
	{
		return $this->path.'Components'.$this->ds;
	}
	public function VendorPath($path=null)
	{
		if(isset($path))
		{
			$path = str_replace('_', $this->ds, $path);
			return $this->path.'Vendor'.$this->ds.$path.$this->ds;
		}
		return $this->path.'Vendor'.$this->ds;
	}
	
	public function App()
	{
		return $this->app;
	}
	public function Environment()
	{
		return $this->environment;
	}
	
	/**
	 * Enter description here...
	 *
	 * @return Enlight_Loader
	 */
	public function Loader()
	{
		return $this->_loader;
	}
	/**
	 * Enter description here...
	 *
	 * @return HookManager
	 */
	public function Hooks()
	{
		return $this->_hooks;
	}
	/**
	 * Enter description here...
	 *
	 * @return Enlight_Event_EventManager
	 */
	public function Events()
	{
		return $this->_events;
	}
	/**
	 * Enter description here...
	 *
	 * @return EventManager
	 */
	public function Plugins()
	{
		return $this->_plugins;
	}
	/**
	 * Enter description here...
	 *
	 * @return Enlight_Bootstrap
	 */
	public function Bootstrap()
	{
		if(!$this->_bootstrap) {
			$class = $this->App().'_Bootstrap';
			$this->_bootstrap = Enlight_Class::Instance($class);
		}
		return $this->_bootstrap;
	}
	/**
	 * Enter description here...
	 *
	 * @return Application
	 */
	public static function Instance()
	{
		return self::$_instance;	
	}
	
	public function loadConfig($config)
	{
		if ($config instanceof Zend_Config) {
			return $options->toArray();
		} elseif (is_array($config)) {
			return $config;
		}
		
		$environment = $this->Environment();
        $suffix      = strtolower(pathinfo($config, PATHINFO_EXTENSION));

        switch ($suffix) {
            case 'ini':
                $config = new Zend_Config_Ini($config, $environment);
                break;
            case 'xml':
                $config = new Zend_Config_Xml($config, $environment);
                break;
            case 'php':
            case 'inc':
                $config = include $config;
                if (!is_array($config)) {
                    throw new Enlight_Exception('Invalid configuration file provided; PHP file does not return array value');
                }
                return $config;
                break;
			case 'yaml':
                $config = new Zend_Config_Yaml($config, $environment);
                break;
            default:
                throw new Enlight_Exception('Invalid configuration file provided; unknown config type');
        }

        return $config->toArray();
	}

	public function setOptions(array $options)
	{
		$options = array_change_key_case($options, CASE_LOWER);

		$this->options = $options;

		if (!empty($options['phpsettings']))
		{
			$this->setPhpSettings($options['phpsettings']);
		}

		if (!empty($options['includepaths']))
		{
			$path = implode(PATH_SEPARATOR, $options['includepaths']);
			set_include_path($path . PATH_SEPARATOR . get_include_path());
		}

		if (!empty($options['autoloadernamespaces']))
		{
			foreach ($options['autoloadernamespaces'] as $namespace=>$path)
			{
				if(is_int($namespace))
				{
					$namespace = $path;
					$path = null;
				}
				$this->_loader->registerNamespace($namespace, $path);
			}
		}		

		return $this;
	}
	
	public function getOptions()
    {
        return $this->options;
    }
	
	public function getOption($key)
    {
       $options = $this->getOptions();
       $options = array_change_key_case($options, CASE_LOWER);
       $key = strtolower($key);
       return isset($options[$key]) ? $options[$key] : null;
    }
    
    public function setPhpSettings(array $settings, $prefix = '')
    {
        foreach ($settings as $key => $value) {
            $key = empty($prefix) ? $key : $prefix . $key;
            if (is_scalar($value)) {
                ini_set($key, $value);
            } elseif (is_array($value)) {
                $this->setPhpSettings($value, $key . '.');
            }
        }
        return $this;
    }
    
    public function setIncludePaths(array $paths)
    {
        $path = implode(PATH_SEPARATOR, $paths);
        set_include_path($path . PATH_SEPARATOR . get_include_path());
        return $this;
    }

    public function __call($name, $value=null)
	{
		if(!$this->Bootstrap()->hasResource($name))
        {
			throw new Exception('Method "'.get_class($this).'::'.$name.'" not found failure', Enlight_Exception::Method_Not_Found);
		}
        return $this->Bootstrap()->getResource($name);
	}
	
	public static function __callStatic($name, $value=null)
	{
		$enlight = self::Instance();
		if(!$enlight->_bootstrap||!$enlight->_bootstrap->hasResource($name))
        {
			throw new Exception('Method "'.get_class($this).'::'.$name.'" not found failure', Enlight_Exception::Method_Not_Found);
		}
        return $enlight->_bootstrap->getResource($name);
	}
}