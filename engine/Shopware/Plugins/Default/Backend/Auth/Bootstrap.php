<?php
/**
 * Shopware Auth Plugin
 *
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author h.lohaus
 * @author st.hamann
 * @package Shopware
 * @subpackage Plugins
 */
class Shopware_Plugins_Backend_Auth_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
	/**
	 * Install plugin method
	 *
	 * @return bool
	 */
	public function install()
	{		
	 	$event = $this->createEvent(
	 		'Enlight_Bootstrap_InitResource_Auth',
	 		'onInitResourceAuth'
	 	);
		$this->subscribeEvent($event);
		$event = $this->createEvent(
	 		'Enlight_Bootstrap_InitResource_Acl',
	 		'onInitResourceAcl'
	 	);
		$this->subscribeEvent($event);
		$event = $this->createEvent(
	 		'Enlight_Controller_Action_PreDispatch',
	 		'onPreDispatchBackend'
	 	);
		$this->subscribeEvent($event);
		return true;
	}
	
	/**
	 * Event listener method
	 *
	 * @param Enlight_Event_EventArgs $args
	 */
	public static function onInitResourceAuth(Enlight_Event_EventArgs $args)
	{
		$request = Shopware()->Front()->Request();
    	$path = rtrim($request->getBasePath(),'/').'/';
    	$host = $request->getHttpHost()=='localhost' ? null : '.'.$request->getHttpHost();
		
		Enlight_Components_Session::start(array(
			'name' => get_cfg_var('session.name') ? get_cfg_var('session.name') : 'PHPSESSID',
			'gc_maxlifetime' => 60 * 90,
			'cookie_lifetime' => 0,
			'cookie_path' => $path,
			'cookie_domain' => $host
		));
		
		$adapter = new Enlight_Components_Auth_Adapter_DbTable();
        
        $adapter->setTableName('s_core_auth')
            ->setIdentityColumn('username')
            ->setCredentialColumn('password')
            ->setCredentialTreatment("MD5(CONCAT('A9ASD:_AD!_=%a8nx0asssblPlasS$',MD5(?)))")
            ->addCondition('active=1')
		 	->addCondition('lockeduntil <= NOW()')
            ->setExpiryColumn('lastlogin')
            ->setSessionIdColumn('sessionID')
            ->setSessionId(Enlight_Components_Session::getId());

		$storage = new Zend_Auth_Storage_Session('Shopware', 'Auth');
		
		$auth = Enlight_Components_Auth::getInstance();
		
		$auth->setAdapter($adapter);
		$auth->setStorage($storage);
		
		if ($auth->hasIdentity()) {
			$auth->refresh();
		}
		
        return $auth;
	}
	
	/**
	 * Event listener method
	 *
	 * @param Enlight_Event_EventArgs $args
	 */
	public static function onInitResourceAcl(Enlight_Event_EventArgs $args)
	{
        $auth = Shopware()->Auth();
        if (!$auth->hasIdentity()) {
			return;
		}
		if(isset($auth->getIdentity()->acl)) {
			return $auth->getIdentity()->acl;
		}
		
		$user = $auth->getIdentity();
		
		$acl = new Zend_Acl();
		$acl->addRole('user')
		    ->addRole('admin', 'user')
		    ->addRole($auth->getIdentity()->role, $user->admin ? 'admin' : 'user')
		    ->allow('admin');
		$sql = '
			SELECT `onclick` as `resource`, id IN (' . Shopware()->Db()->quote($user->rights) . ') as `allowed`
			FROM `s_core_menu`
		';
		$resources = Shopware()->Db()->fetchPairs($sql);
		foreach ($resources as $resource => $allowed) {
			if(preg_match('#\'(.+?)\'#', $resource, $match)) {
				if(!$acl->has($match[1])) {
					$acl->addResource($match[1]);
				}
				$privilege = strpos($resource, 'deleteCache') === 0 ? 'cache' : null;
				if(!empty($allowed)) {
					$acl->allow($user->role, $match[1], $privilege);
				}
			}
		}
		$user->acl = $acl;
		
		return $acl;
	}
	
	protected $noAuth = false;
	
	/**
	 * Auth login method
	 *
	 * @param string $username
	 * @param string $password
	 * @return bool
	 */
	public function login($username, $password)
	{
		$auth = Shopware()->Auth();
		
		$auth->getAdapter()->setIdentity($username);
		$auth->getAdapter()->setCredential($password);
				
		$auth->authenticate();
		
		if ($user = $auth->getIdentity()) {
			
			$_SESSION['sName'] = $user->name;
			$_SESSION['sID'] = $user->id;
			$_SESSION['sSidebar'] = $user->sidebar;
			$_SESSION['sWindow_Width'] = $user->window_width;
			$_SESSION['sWindow_Height'] = $user->window_height;
			
			$user->window_size = unserialize($user->window_size);
			$user->rights = unserialize($user->rights);
			$_SESSION['sWindow_Size'] = $user->window_size;
			$_SESSION['sRights'] = $user->rights;
			$_SESSION['sAdmin'] = $user->admin;
			
			$_SESSION['sUsername'] = $user->username;
			$_SESSION['sPassword'] = $user->password;
			$user->role = 'role';
			
			// Ticket #5427 - Prevent brute force logins
			// At successful login, reset failed login counter
			Shopware()->Db()->query("
				UPDATE s_core_auth SET lockeduntil = '0000-00-00 00:00:00',
				failedlogins = 0 WHERE username = ?
			", array($user->username));
		} elseif (!empty($username)) {
			// Ticket #5427 - Prevent brute force logins
			try {
				$sql = "
					UPDATE s_core_auth SET
						failedlogins = failedlogins + 1,
						lockeduntil = IF(
							failedlogins > 4,
							DATE_ADD(NOW(), INTERVAL (failedlogins + 1) * 30 SECOND),
							'0000-00-00 00:00:00'
						)
					WHERE username = ?
				";
				Shopware()->Db()->query($sql, array($username));
			} catch (Exception $e){
				
			}
		}

		return $auth->hasIdentity();
	}
	
	/**
	 * Event listener method
	 *
	 * @param Enlight_Event_EventArgs $args
	 */
	public static function onPreDispatchBackend(Enlight_Event_EventArgs $args)
	{
		$request = $args->getSubject()->Request();
		
		if($request->getModuleName()!='backend' || $request->getControllerName()=='error') {
			return;
		}
		
		if(!Shopware()->Plugins()->Backend()->Auth()->shouldAuth()) {
			return;
		}
		
		if(Shopware()->Auth()->hasIdentity()) {
			$resource = strtolower($request->getControllerName());
			$acl = Shopware()->Acl();
			if(!$acl->has($resource)) {
				return;
			}
			$identity = Shopware()->Auth()->getIdentity();
			if($acl->isAllowed($identity->role, $resource, 'view')) {
				return;
			}
		}
			
		if($request->isXmlHttpRequest()) {
			throw new Enlight_Controller_Exception('Unauthorized', 401); 
		} elseif(Shopware()->Auth()->hasIdentity()) {
			$args->getSubject()->redirect('backend');
		} else {
			$args->getSubject()->redirect('backend/auth');
		}
	}
	
	/**
	 * Set no auth method
	 *
	 * @param bool $flag
	 * @return unknown
	 */
	public function setNoAuth($flag = true)
	{
		$this->noAuth = $flag ? true : false;
		return $this;
	}
	
	/**
	 * Should auth method
	 *
	 * @return bool
	 */
	public function shouldAuth()
    {
    	return !$this->noAuth;
    }
}