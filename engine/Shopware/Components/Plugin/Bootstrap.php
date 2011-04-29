<?php
/**
 * Shopware Plugin Bootstrap
 * 
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Heiner Lohaus
 */
abstract class Shopware_Components_Plugin_Bootstrap extends Enlight_Plugin_Bootstrap
{	
	protected $capabilities;
	
	/**
	 * Constructor method
	 *
	 * @param Enlight_Plugin_Namespace $namespace
	 * @param string $name
	 */
	public function __construct(Enlight_Plugin_Namespace $namespace, $name)
	{
		parent::__construct($namespace, $name);
		$this->capabilities = $this->getCapabilities();
	}
	
	/**
	 * Install plugin method
	 *
	 * @return bool
	 */
	public function install()
	{
		return !empty($this->capabilities['install']);
	}
	
	/**
	 * Uninstall plugin method
	 *
	 * @return bool
	 */
	public function uninstall()
	{
		if(empty($this->capabilities['uninstall'])) {
			return false;
		}
		
		$this->unsubscribeHooks();
		$this->unsubscribeEvents();
		$this->deleteForm();
		$this->deleteConfig();
		$this->deleteMenuItems();
		$this->unsubscribeCron();
		return true;
	}

	/**
	 * Update plugin method
	 *
	 * @return bool
	 */
	public function update()
	{
		if(empty($this->capabilities['update'])||empty($this->capabilities['install'])) {
			return false;
		}
		
		$this->uninstall();
		$this->install();
		
		return true;
	}
	
	/**
	 * Enable plugin method
	 *
	 * @return bool
	 */
	public function enable()
	{
		if(empty($this->capabilities['enable'])) {
			return false;
		}
		
		$sql = 'UPDATE `s_core_plugins` SET `active`=1 WHERE `id`=?';
		Shopware()->Db()->query($sql, $this->getId());
		
		return true;
	}
	
	/**
	 * Disable plugin method
	 *
	 * @return bool
	 */
	public function disable()
	{
		if(empty($this->capabilities['disable'])) {
			return false;
		}
		
		$sql = 'UPDATE `s_core_plugins` SET `active`=0 WHERE `id`=?';
		Shopware()->Db()->query($sql, $this->getId());
		
		return true;
	}
	
	/**
	 * Returns plugin config
	 *
	 * @return Shopware_Models_Plugin_Config
	 */
	public function Config()
	{
		return $this->namespace->getConfig($this->name);
	}
	
	/**
	 * Returns plugin form
	 *
	 * @return Shopware_Components_Form
	 */
	public function Form()
	{
		$form = new Shopware_Components_Form();
		$form->setId($this->getId());
		$saveHandler = new Shopware_Components_Form_SaveHandler_DbTable();
		$form->setSaveHandler($saveHandler);
		$form->load();
		return $form;
	}
	
	/**
	 * Returns shopware menu
	 *
	 * @return Shopware_Components_Menu
	 */
	public function Menu()
	{
		return Shopware()->Menu();
	}
			
	/**
	 * Enter description here...
	 *
	 * @return Enlight_Event_EventHandler
	 */
	public function createEvent($event, $listener, $position=null)
	{
		$event = new Enlight_Event_EventHandler(
	 		$event,
	 		get_class($this).'::'.$listener,
	 		$position,
	 		$this->getId()
	 	);
	 	return $event;
	}
	
	/**
	 * Enter description here...
	 *
	 * @return Enlight_Core_Hook_HookHandler
	 */
	public function createHook($class, $method, $listener, $type=null, $position=null)
	{
		$hook = new Enlight_Hook_HookHandler(
    		$class,
    		$method,
    		get_class($this).'::'.$listener,
    		$type,
    		$position,
	 		$this->getId()
    	);
		return $hook;
	}
	
	/**
	 * Enter description here...
	 *
	 * @return Enlight_Core_Hook_HookHandler
	 */
	public function createMenuItem($options)
	{
		$options['pluginID'] = $this->getId();
		return Shopware_Components_Menu_Item::factory($options);
	}
	
	/**
	 * Subscribe cron method
	 *
	 * @param Shopware_Components_Cron_CronHandler $handler
	 * @return Shopware_Components_Plugin_Bootstrap
	 */
	public function subscribeCron($handler)
	{
		if(!$handler instanceof Shopware_Components_Cron_CronHandler) {
			$reflection = new ReflectionClass('Shopware_Components_Cron_CronHandler');
			$handler = $reflection->newInstanceArgs(func_get_args());
		}
		if(!$handler->getPlugin()) {
			$handler->setPlugin($this->getId());
		}
		Shopware()->Cron()->addCronJob($handler);
		
		return $this;
	}
	
	/**
	 * Subscribe cron method
	 *
	 * @param Enlight_Event_EventHandler $handler
	 * @return Shopware_Components_Plugin_Bootstrap
	 */
	public function subscribeEvent(Enlight_Event_EventHandler $handler)
	{
		Shopware()->Subscriber()->subscribeEvent($handler);
		
		return $this;
	}
	
	/**
	 * Subscribe hook method
	 *
	 * @param Enlight_Hook_HookHandler $handler
	 * @return Shopware_Components_Plugin_Bootstrap
	 */
	public function subscribeHook(Enlight_Hook_HookHandler $handler)
	{
		Shopware()->Subscriber()->subscribeHook($handler);
		
		return $this;
	}
	
	/**
	 * Unsubscribe cron method
	 */
	public function unsubscribeCron()
	{
		$sql = 'DELETE FROM `s_crontab` WHERE `pluginID`=?';
		Shopware()->Db()->query($sql, $this->getId());
		
		return $this;
	}
		
	/**
	 * Unsubscribe hooks
	 */
	public function unsubscribeHooks()
	{
		Shopware()->Subscriber()->unsubscribeHooks(array('pluginID'=>$this->getId()));
		
		return $this;
	}
	
	/**
	 * Unsubscribe events
	 */
	public function unsubscribeEvents()
	{
		Shopware()->Subscriber()->unsubscribeEvents(array('pluginID'=>$this->getId()));
		
		return $this;
	}
	
	/**
	 * Delete plugin form
	 */
	public function deleteForm()
	{
		$sql = 'DELETE FROM `s_core_plugin_elements` WHERE `pluginID`=?';
		Shopware()->Db()->query($sql, $this->getId());
		
		return $this;
	}
	
	/**
	 * Delete plugin config
	 */
	public function deleteConfig()
	{
		$sql = 'DELETE FROM `s_core_plugin_configs` WHERE `pluginID`=?';
		Shopware()->Db()->query($sql, $this->getId());
		
		return $this;
	}
	
	/**
	 * Delete menu items
	 */
	public function deleteMenuItems()
	{
		$sql = 'DELETE FROM `s_core_menu` WHERE `pluginID`=?';
		Shopware()->Db()->query($sql, $this->getId());
		
		return $this;
	}
	
	/**
	 * Returns capabilities
	 */
	public function getCapabilities()
    {
        return array(
    		'install' => true,
    		'uninstall' => true,
    		'update' => true,
    		'enable' => true,
    		'disable' => true,
    	);
    }
    
    /**
	 * Returns plugin id
	 *
	 * @return int
	 */
	public function getId()
	{
		return $this->namespace->getPluginId($this->name);
	}
    
	/**
	 * Returns plugin version
	 *
	 * @return string
	 */
    public function getVersion()
    {
        return '1.0.0';
    }
    
    /**
	 * Returns plugin name
	 *
	 * @return string
	 */
    public function getName()
    {
    	return $this->name;
    }
    
    /**
	 * Returns plugin source
	 *
	 * @return string
	 */
    public function getSource()
    {
    	return $this->namespace->getSource($this->name);
    }
    
    /**
	 * Returns plugin info
	 *
	 * @return array
	 */
    public function getInfo()
    {
    	return array(
    		'version' => $this->getVersion(),
			'autor' => 'shopware AG',
			'copyright' => 'Copyright © 2010, shopware AG',
			'label' => $this->getName(),
			'source' => $this->getSource(),
			'description' => '',
			'license' => '',
			'support' => 'http://www.shopware.de/wiki/',
			'link' => 'http://www.shopware.de/'
    	);
    }
}