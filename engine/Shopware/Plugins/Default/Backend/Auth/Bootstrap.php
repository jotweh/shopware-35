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
		
		Zend_Session::start(array(
			'name' => get_cfg_var('session.name'),
			'gc_maxlifetime' => 60*90,
			'cookie_lifetime' => 0,
			'cookie_path' => $path,
			'cookie_domain' => $host
		));
		
		$adapter = new Zend_Auth_Adapter_DbTable();
        
        $adapter->setTableName('s_core_auth')
            ->setIdentityColumn('username')
            ->setCredentialColumn('password');
            
		$select = $adapter->getDbSelect();
		$select->where('active=1');
		
		//$storage = new Zend_Auth_Storage_Session('Shopware', 'Auth');
		$storage = new Zend_Auth_Storage_NonPersistent();
		
		$auth = Enlight_Components_Auth::getInstance();
		
		$auth->setAdapter($adapter);
		$auth->setStorage($storage);
								
		if(!$auth->hasIdentity()&&!empty($_SESSION['sUsername'])&&!empty($_SESSION['sPassword'])) {
			$select->where('lastlogin>=DATE_SUB(NOW(),INTERVAL 60*90 SECOND)');
			$select->where('sessionID=?', Zend_Session::getId());
			$adapter->setCredentialTreatment('?');
			$adapter->setIdentity($_SESSION['sUsername']);
			$adapter->setCredential($_SESSION['sPassword']);
			$auth->authenticate();
			if (!$auth->hasIdentity()){
				$auth->clearIdentity();
				$select->reset(Zend_Db_Select::WHERE);
				$select->where('active=1');
			}
		}
		
		$adapter->setCredentialTreatment("MD5(CONCAT('A9ASD:_AD!_=%a8nx0asssblPlasS$',MD5(?)))");
		
		if ($auth->hasIdentity()) {
			$userId = $auth->getIdentity()->id;
			$sql = '
				UPDATE s_core_auth SET lastlogin = NOW() WHERE id=?
			';
			$update = Shopware()->Db()->query($sql, array($userId));
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

		if ($auth->hasIdentity()) {
			$user = $auth->getIdentity();
			$id = Zend_Session::getId();
			
			$sql = '
				UPDATE s_core_auth SET sessionID=?, lastlogin = NOW() WHERE id = ?
			';
			$update = Shopware()->Db()->query($sql,array($id, $user->id));
			
			$_SESSION['sName'] = $user->name;
			$_SESSION['sID'] = $user->id;
			$_SESSION['sSidebar'] = $user->sidebar;
			$_SESSION['sWindow_Width'] =$user->window_width;
			$_SESSION['sWindow_Height'] = $user->window_height;
			
			$_SESSION['sWindow_Size'] = unserialize($user->window_size);
			$_SESSION['sRights'] = unserialize($user->rights);
			$_SESSION['sAdmin'] = $user->admin;
			
			$_SESSION['sUsername'] = $user->username;
			$_SESSION['sPassword'] = $user->password;
			return true;
		} else {
			$auth->clearIdentity();
			return false;
		}
	}
	
	/**
	 * Event listener method
	 *
	 * @param Enlight_Event_EventArgs $args
	 */
	public static function onPreDispatchBackend(Enlight_Event_EventArgs $args)
	{
		$request = $args->getSubject()->Request();
		
		if($request->getModuleName()!='backend'||$request->getControllerName()=='error') {
			return;
		}
		if(Shopware()->Plugins()->Backend()->Auth()->shouldAuth() && !Shopware()->Auth()->hasIdentity()) {
			if($request->isXmlHttpRequest()) {
				throw new Enlight_Controller_Exception('Unauthorized', 401); 
			} else {
				$args->getSubject()->redirect(array('module'=>'backend', 'controller'=>'auth'));
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