<?php
/**
 * Shopware Auth Plugin
 * 
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
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
			'gc_maxlifetime' => 60*90,
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
		
		if(Shopware()->Plugins()->Backend()->Auth()->shouldAuth() && !Shopware()->Auth()->hasIdentity()) {
			if($request->isXmlHttpRequest()) {
				throw new Enlight_Controller_Exception('Unauthorized', 401); 
			} else {
				$args->getSubject()->redirect('backend/auth');
			}
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