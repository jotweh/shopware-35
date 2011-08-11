<?php
/**
 * Shopware Application Bootstrap
 * 
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Heiner Lohaus
 */
class Shopware_Bootstrap extends Enlight_Bootstrap
{
	/**
	 * Run application method
	 *
	 * @return unknown
	 */
	public function run()
	{
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

		$front->Response()->setHeader('Content-Type', 'text/html; charset=iso-8859-1');

		return $front->dispatch();
	}
    
	/**
	 * Init template method
	 *
	 * @return Enlight_Template_TemplateManager
	 */
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
    
    /**
	 * Init view method
	 *
	 * @return Enlight_View_ViewDefault
	 */
    public function initView()
    {
    	return Enlight_Class::Instance('Enlight_View_ViewDefault');
    }
    
    /**
	 * Init database method
	 *
	 * @return Zend_Db_Adapter_Pdo_Abstract
	 */
    protected function initDb()
    {   
    	$config = Shopware()->getOption('db');
    	
    	if(!isset($config['charset'])) {
    		$config['charset'] = 'latin1';
    	}
    	    	
    	$db = Enlight_Components_Db::factory('PDO_MYSQL', $config);
    	$db->getConnection();

    	Shopware_Models_Shop::setDb($db);
		Zend_Db_Table_Abstract::setDefaultAdapter($db);

    	return $db;
    }
    
    /**
     * Init session id method
     *
     * @return unknown
     */
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
    
    /**
     * Init session method
     *
     * @return Enlight_Components_Session_Namespace
     */
    protected function initSession()
    {
    	$config_session = Shopware()->getOption('session') ? Shopware()->getOption('session') : array();
    	
    	if(!empty($config_session['unitTestEnabled'])) {
    		Enlight_Components_Session::$_unitTestEnabled = true;
    	}
    	unset($config_session['unitTestEnabled']);
    	
    	if(Enlight_Components_Session::isStarted())	{
    		Enlight_Components_Session::writeClose();
    	}
    	    	
    	$session_id = $this->getResource('SessionID');
    	if(!empty($session_id)) {
    		Enlight_Components_Session::setId($session_id);
    	}
    	    	
    	if($this->hasResource('Front')&&Shopware()->Front()->Request()) {
    		$request = Shopware()->Front()->Request();
    		$path = rtrim($request->getBasePath(), '/') . '/';
    		$host = $request->getHttpHost()=='localhost' ? null : '.' . $request->getHttpHost();
    	} else {
    		$config = $this->getResource('Config');
    		$path = rtrim(str_replace($config->get('Host'), '', $config->get('BasePath')),'/').'/';
    		$host = $config->get('Host')=='localhost' ? null : '.' . $config->get('Host');
    	}

    	$config_session['cookie_path'] = $path;
    	$config_session['cookie_domain'] = $host;
    	
    	if(empty($config_session['name'])) {
    		$config_session['name'] = 'SHOPWARESID';
    	}
    	if(!isset($config_session['cookie_lifetime'])) {
    		$config_session['cookie_lifetime'] = 0;
    	}
    	if(!isset($config_session['use_trans_sid'])) {
    		$config_session['use_trans_sid'] = 0;
    	}
    	if(!isset($config_session['gc_probability'])) {
    		$config_session['gc_probability'] = 1;
    	}
    	if(!isset($config_session['save_handler']) || $config_session['save_handler'] == 'db') {
    		$config_save_handler = array(
	    		'db'			 => $this->getResource('Db'),
		    	'name'           => 's_core_sessions',
		    	'primary'        => 'id',
		    	'modifiedColumn' => 'modified',
		    	'dataColumn'     => 'data',
		    	'lifetimeColumn' => 'expiry'
	    	);
	    	Enlight_Components_Session::setSaveHandler(new Enlight_Components_Session_SaveHandler_DbTable($config_save_handler));
	    	unset($config_session['save_handler']);
    	}
    	    	
    	Enlight_Components_Session::start($config_session);
    	
    	$this->registerResource('SessionID', Enlight_Components_Session::getId());
    	
		$namespace = new Enlight_Components_Session_Namespace('Shopware');

    	return $namespace;
    }
    
	/**
     * Init mail transport
     *
     * @return Zend_Mail_Transport_Abstract
     */
    protected function initMailTransport()
    {
    	$options = Shopware()->getOption('mail') ? Shopware()->getOption('mail') : array();
    	$config = $this->getResource('Config');
    	
    	if(!isset($options['type']) && !empty($config->MailerMailer) && $config->MailerMailer!='mail') {
			$options['type'] = $config->MailerMailer;
		}
		if(empty($options['type'])) {
    		$options['type'] = 'sendmail';
    	}
    	
    	if($options['type'] == 'smtp') {
	    	if(!isset($options['username']) && !empty($config->MailerUsername)) {
	    		if(!empty($config->MailerAuth)) {
	    			$options['auth'] = $config->MailerAuth;
	    		} elseif(empty($options['auth'])) {
					$options['auth'] = 'login';
				}
				$options['username'] = $config->MailerUsername;
				$options['password'] = $config->MailerPassword;
			}
			if(!isset($options['ssl']) && !empty($config->MailerSMTPSecure)) {
				$options['ssl'] = $config->MailerSMTPSecure;
			}
			if(!isset($options['port']) && !empty($config->MailerPort)) {
				$options['port'] = $config->MailerPort;
			}
			if(!isset($options['name']) && !empty($config->MailerHostname)) {
				$options['name'] = $config->MailerHostname;
			}
			if(!isset($options['host']) && !empty($config->MailerHost)) {
				$options['host'] = $config->MailerHost;
			}
		}
		
		if(!Shopware()->Loader()->loadClass($options['type'])) {
			$transportName = ucfirst(strtolower($options['type']));
			$transportName = 'Zend_Mail_Transport_'.$transportName;
			unset($options['type']);
		}
		if($transportName=='Zend_Mail_Transport_Smtp') {
			$transport = Enlight_Class::Instance($transportName, array($options['host'], $options));
		} elseif(!empty($options)) {
			$transport = Enlight_Class::Instance($transportName, array($options));
		} else {
			$transport = Enlight_Class::Instance($transportName);
		}
		Enlight_Components_Mail::setDefaultTransport($transport);
		
		if(!isset($options['from']) && !empty($config->Mail)) {
			$options['from'] = array('email'=>$config->Mail, 'name'=>$config->Shopname);
		}
				
    	if(!empty($options['from']['email'])) {
    		Enlight_Components_Mail::setDefaultFrom(
    			$options['from']['email'], 
    			!empty($options['from']['name']) ? $options['from']['name'] : null
    		);
    	}
    	if(!empty($options['replyTo']['email'])) {
    		Enlight_Components_Mail::setDefaultReplyTo(
    			$options['replyTo']['email'], 
    			!empty($options['replyTo']['name']) ? $options['replyTo']['name'] : null
    		);
    	}
    	
    	return $transport;
    }
    
    /**
     * Init mail method
     *
     * @return Enlight_Components_Mail
     */
    protected function initMail()
    {
    	if(!$this->loadResource('Config')
    	 || !$this->loadResource('MailTransport')) {
    		return null;
    	}
    	
    	$options = Shopware()->getOption('mail');
    	$config = $this->getResource('Config');
		
		if(isset($options['charset'])) {
			$defaultCharSet = $options['charset'];
		} elseif (!empty($config->CharSet)) {
			$defaultCharSet = $config->CharSet;
		} else {
			$defaultCharSet = null;
		}
    	
	    $mail = new Enlight_Components_Mail($defaultCharSet);
	    
    	return $mail;
    }
    
    /**
     * Init config method
     *
     * @return Shopware_Models_Config
     */
    protected function initConfig()
    {
    	if(!$this->issetResource('Db')) {
    		return null;
    	}
    	
    	$config = Shopware()->getOption('config');
    	if(empty($config)) {
    		$config = array();
    	}
    	if(!isset($config['cache'])) {
	    	$config['cache'] = $this->getResource('Cache');
    	}
    	
    	$modelConfig = new Shopware_Models_Config($config);
    	return $modelConfig;
    }
    
    /**
     * Init snippets method
     *
     * @return unknown
     */ 
    protected function initSnippets()
    {
    	if(!$this->issetResource('Db')) {
    		return;
    	}
    	
    	$snippet = new Shopware_Components_Snippet_SnippetManager();
    	    	
    	$snippet->setCache($this->getResource('Cache'));
    	$snippet->setLocale($this->getResource('Locale'));
        	
    	if($this->issetResource('Shop')) {
    		$snippet->setShop($this->getResource('Shop'));
    	}
    	
    	return $snippet;
    }
    
    /**
     * Init router method
     *
     * @return unknown
     */
    protected function initRouter()
    {
    	return $this->getResource('Front')->Router();
    }
    
    /**
     * Init subscriber method
     *
     * @return unknown
     */
    protected function initSubscriber()
    {
    	if(!$this->issetResource('Db')) {
    		return null;
    	}
    	return new Shopware_Components_Subscriber();
    }
    
    /**
     * Init plugins method
     *
     * @return Enlight_Plugin_PluginManager
     */
    protected function initPlugins()
    {
    	foreach (array('Core', 'Frontend', 'Backend') as $namespace) {
    		$namespace = new Shopware_Components_Plugin_Namespace($namespace);
    		foreach (array('Local', 'Community', 'Default') as $dir) {
	    		$namespace->addPrefixPath(
	    			'Shopware_Plugins_' . $namespace->getName(),
	    			Shopware()->AppPath('Plugins_' . $dir . '_' . $namespace->getName())
	    		);
	    	}
	    	$namespace->setCache($this->getResource('Cache'));
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
    
    /**
     * Init locale method
     *
     * @return Zend_Locale
     */
    protected function initLocale()
    {
    	if($this->hasResource('Db')) {
    		return new Shopware_Models_Locale('de_DE');
    	} else {
    		return new Zend_Locale('de_DE');
    	}
    }
    
    /**
     * Init currency method
     *
     * @return Zend_Currency
     */
    protected function initCurrency()
    {
    	if($this->hasResource('Db')) {
    		return new Shopware_Models_Currency('EUR', $this->getResource('Locale'));
    	} else {
    		return new Zend_Currency('EUR', $this->getResource('Locale'));
    	}
    }
    
    /**
     * Init date method
     *
     * @return Zend_Date
     */
    protected function initDate()
    {
    	$date = new Zend_Date($this->getResource('Locale'));
    	return $date;
    }
    
    /**
     * Init cache method
     *
     * @return unknown
     */
    protected function initCache()
    {
    	$config = Shopware()->getOption('cache');
    	
    	if(isset($config['frontendOptions'])) {
    		$frontendOptions = $config['frontendOptions'];
    	} else {
    		$frontendOptions = array(
	    		'automatic_serialization' => true,
	    		'automatic_cleaning_factor' => 0,
	    		'lifetime' => 3600
	    	);
    	}
    	
    	if(isset($config['backend'])) {
    		$backend = $config['backend'];
    	} else {
    		$backend = 'File';
    	}
    	
    	if(isset($config['backendOptions'])) {
    		$backendOptions = $config['backendOptions'];
    	} else {
    		$backendOptions = array(
				'hashed_directory_umask' => 0771,
				'cache_file_umask' => 0644,
				'hashed_directory_level' => 0,
				'file_name_prefix' => 'shopware',
				'cache_dir' => Shopware()->DocPath('cache_database'),
				'file_name_prefix' => 'shopware'
			);
    	}
    	
    	if(isset($backendOptions['hashed_directory_level'])
    	  && $backendOptions['hashed_directory_level'] === 0
    	  && !ini_get('safe_mode')) {
			$backendOptions['hashed_directory_level'] = 3;
    	}
    	
		$cache = Zend_Cache::factory('Core', $backend, $frontendOptions, $backendOptions);
		
    	Shopware_Models_Shop::setCache($cache);
		Zend_Db_Table_Abstract::setDefaultMetadataCache($cache);
		Zend_Locale_Data::setCache($cache);
    	return $cache;
    }
}