<?php
class Shopware_Bootstrap extends Enlight_Bootstrap
{
	public function run()
    {
    	$this->loadResource('Zend');
    	
        $front = $this->getResource('Front');
        
        try {
        	$this->loadResource('Cache');
        	$this->loadResource('Db');
        	$this->loadResource('Plugins');
        } catch (Exception $e) {
        	if ($front->throwExceptions()) {
                throw $e;
            }
            $front->Response()->setException($e);
        }
        
        $front->dispatch();
    }
            
	protected function initTemplate()
    {
   	    $template = parent::initTemplate();
   	    
   	    $template->setCompileDir(Shopware()->DocPath().'cache/templates/');
		$template->setCacheDir(Shopware()->DocPath().'cache/templates/');
		$template->setTemplateDir(array(
			Shopware()->DocPath().'templates/_local/',
			Shopware()->DocPath().'templates/_default/'
		));
		
		$config = Shopware()->getOption('template');
		foreach ($config as $key => $value) {
			$template->{'set'.$key}($value);
		}
			
        return $template;
    }
    
    protected function initDb()
    {   
    	$config = Shopware()->getOption('db');
    	    	
    	$db = Enlight_Components_Db::factory('PDO_MYSQL', $config);
    	$db->getConnection();

    	Shopware_Models_Shop::setDb($db);
		Zend_Db_Table_Abstract::setDefaultAdapter($db);

    	return $db;
    }
      
    protected function initSessionID()
    {
    	if (!empty($_GET['sCoreId'])){
			return $_GET['sCoreId'];
		} elseif (!empty($_POST['sCoreId'])){
			return $_POST['sCoreId'];
		} else {
			return false;
		}
    }
      
    protected function initSession()
    {
    	if(Enlight_Components_Session::isStarted())	{
    		Enlight_Components_Session::writeClose();
    	}
    	
    	$session_id = $this->getResource('SessionID');
    	if(!empty($session_id)) {
    		Enlight_Components_Session::setId($session_id);
    	}
    	
    	$config = $this->getResource('Config');
    	
    	if(!empty($config->SessionByDb)) {
	    	$config_save_handler = array(
	    		'db'			 => $this->getResource('Db'),
		    	'name'           => 's_core_sessions',
		    	'primary'        => 'id',
		    	'modifiedColumn' => 'modified',
		    	'dataColumn'     => 'data',
		    	'lifetimeColumn' => 'expiry'
	    	);
	    	Enlight_Components_Session::setSaveHandler(new Enlight_Components_Session_SaveHandler_DbTable($config_save_handler));
    	}
    	
    	if($this->hasResource('Front')&&Shopware()->Front()->Request()) {
    		$request = Shopware()->Front()->Request();
    		$path = rtrim($request->getBasePath(),'/').'/';
    		$host = $request->getHttpHost()=='localhost' ? null : '.'.$request->getHttpHost();
    	} else {
    		$path = rtrim(str_replace($config->get('Host'), '', $config->get('BasePath')),'/').'/';
    		$host = $config->get('Host')=='localhost' ? null : '.'.$config->get('Host');
    	}    	
    	
    	$config_session = array(
	    	'name'            => 'SHOPWARESID',
	    	'cookie_lifetime' => 0,
	    	//'cookie_path'     => rtrim(str_replace($config->get('Host'), '', $config->get('BasePath')),'/').'/',
	    	//'cookie_domain'   => '.'.$config->get('Host')
	    	'cookie_path' => $path,
			'cookie_domain'   => $host
    	);
    	Enlight_Components_Session::start($config_session);
    	
    	$this->registerResource('SessionID', Enlight_Components_Session::getId());
    	
		$namespace = new Enlight_Components_Session_Namespace('Shopware');

    	return $namespace;
    }
    
    protected function initMail()
    {
    	if(!$this->issetResource('Db')) {
    		return null;
    	}
    	$config = $this->getResource('Config');
    	
    	if($config->MailerMailer=='smtp') {
    		$options = array();
    		if(!empty($config->MailerUsername)) {
    			$options['auth'] =  'login';
    			$options['username'] =  $config->MailerUsername;
    			$options['password'] = $config->MailerPassword;
    		}
    		if(!empty($config->MailerSMTPSecure)) {
    			$options['ssl'] = $config->MailerSMTPSecure;
    		}
    		if(!empty($config->MailerPort)) {
    			$options['port'] = $config->MailerPort;
    		}
    		if(!empty($config->MailerHostname)) {
    			$options['name'] = $config->MailerHostname;
    		}
			$transport = new Zend_Mail_Transport_Smtp($config->MailerHost, $options);
			Zend_Mail::setDefaultTransport($transport);
    	}
		Enlight_Components_Mail::setDefaultFrom($config->Mail, $config->Shopname);
		
	    $mail = new Enlight_Components_Mail($config->CharSet);
    	
    	return $mail;
    }
    
    protected function initConfig()
    {
    	if(!$this->issetResource('Db')) {
    		return null;
    	}
    	
    	$config = new Shopware_Models_Config(array('cache'=>$this->getResource('Cache')));
    	return $config;
    }
        
    protected function initSnippets()
    {
    	if(!$this->issetResource('Db')) {
    		return null;
    	}
    	
    	$snippet = new Shopware_Components_Snippet_SnippetManager();
    	
    	$snippet->setCache($this->getResource('Cache'));
    	$snippet->setShop($this->getResource('Shop'));
    	$snippet->setNamespace('frontend');
    	
    	return $snippet;
    }
        
    protected function initFront()
    {
    	$front = parent::initFront();
    	
    	$config = Shopware()->getOption('Front');
    	
    	$front->setParams($config);
    	
    	$front->Response()->setHeader('Content-Type', 'text/html; charset=iso-8859-1');
    	
    	if(!empty($config['throwExceptions'])) {
    		$front->throwExceptions(true);
    	}
    	return $front;
    }
    
    protected function initRouter()
    {
    	return $this->getResource('Front')->Router();
    }
    
    protected function initZend()
    {
    	Shopware()->Loader()->registerNamespace('Zend', Shopware()->CorePath('Zend'));
    	Shopware()->Loader()->addIncludePath(Shopware()->CorePath());
    	Shopware()->Loader()->registerNamespace('Zend', Shopware()->VendorPath('Zend_library_Zend'));
    	Shopware()->Loader()->addIncludePath(Shopware()->VendorPath('Zend_library'));
    	return true;
    }
    
    protected function initSubscriber()
    {
    	if(!$this->issetResource('Db')) {
    		return null;
    	}
    	return new Shopware_Components_Subscriber();
    }
    
    protected function initPlugins()
    {
    	foreach (array('Core', 'Frontend', 'Backend') as $namespace) {
    		$namespace = new Shopware_Components_Plugin_Namespace($namespace);
    		foreach (array('Local', 'Community', 'Default') as $dir) {
	    		$namespace->addPrefixPath('Shopware_Plugins_'.$namespace->getName(), Shopware()->AppPath('Plugins_'.$dir.'_'.$namespace->getName()));
	    	}
	    	Shopware()->Plugins()->registerNamespace($namespace);
    	}
    	foreach (array('Local', 'Community', 'Default') as $dir) {
    		Shopware()->Loader()->registerNamespace('Shopware_Plugins', Shopware()->AppPath('Plugins_'.$dir));
    	}
    	
    	Shopware()->Events()->addSubscriber($this->getResource('Subscriber'));
    	Shopware()->Hooks()->addSubscriber($this->getResource('Subscriber'));
    	    	
    	//Shopware()->Plugins()->Core()->loadAll();
    	//Shopware()->Plugins()->Frontend()->loadAll();
    	//Shopware()->Plugins()->Backend()->loadAll();
    	    	
    	return Shopware()->Plugins();
    }
    
    protected function initLocale()
    {
    	if($this->hasResource('Db')) {
    		return new Shopware_Models_Locale('de_DE');
    	} else {
    		return new Zend_Locale('de_DE');
    	}
    }
    
    protected function initCurrency()
    {
    	if($this->hasResource('Db')) {
    		return new Shopware_Models_Currency('EUR', $this->getResource('Locale'));
    	} else {
    		return new Zend_Currency('EUR', $this->getResource('Locale'));
    	}
    }
    
    protected function initDate()
    {
    	$date = new Zend_Date($this->getResource('Locale'));
    	return $date;
    }
    
    protected function initCache()
    {
    	$config = Shopware()->getOption('cache');
    	
    	if(!empty($config['frontendOptions'])) {
    		$frontendOptions = $config['frontendOptions'];
    	} else {
    		$frontendOptions = array(
	    		'automatic_serialization' => true,
	    		'automatic_cleaning_factor' => 0,
	    		'lifetime' => 3600
	    	);
    	}
    	
    	if(!empty($config['backendOptions'])) {
    		$backendOptions = $config['backendOptions'];
    	} else {
    		$backendOptions = array(
				'hashed_directory_umask' => 0771,
				'cache_file_umask' => 0644,
				'hashed_directory_level' => 2,
				'file_name_prefix' => 'shopware',
				'cache_dir' => Shopware()->DocPath().'cache/database/',
				'file_name_prefix' => 'shopware'
			);
    	}
    	    	
		/*
		$backendOptions = array(
			'slow_backend' => 'File',
			'fast_backend' => 'Apc',
			'slow_backend_options' => $backendOptions,
			'fast_backend_options' => array()
		);
		$cache = Zend_Cache::factory('Core', 'Two Levels', $frontendOptions, $backendOptions);
		*/
		
		$cache = Zend_Cache::factory('Core', 'File', $frontendOptions, $backendOptions);    	
    	
    	Shopware_Models_Shop::setCache($cache);
		Zend_Db_Table_Abstract::setDefaultMetadataCache($cache);
		Zend_Locale_Data::setCache($cache);
    	return $cache;
    }
}