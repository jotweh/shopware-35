<?php
abstract class Shopware_Components_Plugin_Bootstrap extends Enlight_Plugin_Bootstrap
{	
	protected $capabilities;
	
	public function __construct(Enlight_Plugin_Namespace $namespace, $name)
	{
		parent::__construct($namespace, $name);
		$this->capabilities = $this->getCapabilities();
	}
	
	/**
	 * Enter description here...
	 *
	 * @return bool
	 */
	public function install()
	{
		return !empty($this->capabilities['install']);
	}
	
	/**
	 * Enter description here...
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
		
		return true;
	}

	/**
	 * Enter description here...
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
	
	public function enable()
	{
		if(empty($this->capabilities['enable'])) {
			return false;
		}
		
		$sql = 'UPDATE `s_core_plugins` SET `active`=1 WHERE `id`=?';
		Shopware()->Db()->query($sql, $this->getId());
		
		return true;
	}
	
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
	 * Enter description here...
	 *
	 * @return Shopware_Models_Plugin_Config
	 */
	public function Config()
	{
		return $this->namespace->getConfig($this->name);
	}
	
	/**
	 * Enter description here...
	 *
	 * @return Shopware_Components_Menu
	 */
	public function Menu()
	{
		return Shopware()->Menu();
	}
	
	public function getId()
	{
		return $this->namespace->getPluginId($this->name);
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
	 * Enter description here...
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
	
	public function subscribeEvent(Enlight_Event_EventHandler $handler)
	{
		Shopware()->Subscriber()->subscribeEvent($handler);
	}
	
	public function subscribeHook(Enlight_Hook_HookHandler $handler)
	{
		Shopware()->Subscriber()->subscribeHook($handler);
	}
	
	public function unsubscribeEvent(Enlight_Event_EventHandler $handler)
	{
		Shopware()->Subscriber()->subscribeEvent($handler);
	}
	
	public function unsubscribeHooks()
	{
		Shopware()->Subscriber()->unsubscribeHooks(array('pluginID'=>$this->getId()));
	}
	
	public function unsubscribeEvents()
	{
		Shopware()->Subscriber()->unsubscribeEvents(array('pluginID'=>$this->getId()));
	}
	
	public function deleteForm()
	{
		$sql = 'DELETE FROM `s_core_plugin_elements` WHERE `pluginID`=?';
		Shopware()->Db()->query($sql, $this->getId());
	}
	
	public function deleteConfig()
	{
		$sql = 'DELETE FROM `s_core_plugin_configs` WHERE `pluginID`=?';
		Shopware()->Db()->query($sql, $this->getId());
	}
	
	public function deleteMenuItems()
	{
		$sql = 'DELETE FROM `s_core_menu` WHERE `pluginID`=?';
		Shopware()->Db()->query($sql, $this->getId());
	}
	
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
    
    public function getVersion()
    {
        return 1;
    }
    
    public function getName()
    {
    	return $this->name;
    }
    
    public function getSource()
    {
    	return $this->namespace->getSource($this->name);
    }
    
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