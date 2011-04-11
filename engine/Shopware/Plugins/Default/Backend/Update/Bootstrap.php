<?php
/**
 * Shopware update plugin
 * 
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Heiner Lohaus
 * @package Shopware
 * @subpackage Plugins
 */
class Shopware_Plugins_Backend_Update_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{	
	/**
	 * Install plugin method
	 *
	 * @return bool
	 */
	public function install()
	{	
		/*
		$event = $this->createEvent(
	 		'Enlight_Controller_Action_PostDispatch_Frontend_Index',
	 		'onPostDispatchIndex'
	 	);
		$this->subscribeEvent($event);

		$form = $this->Form();
		$form->setElement('text', 'text', array('label'=>'Text', 'value'=>'Hallo', 'scope'=>Shopware_Components_Form::SCOPE_SHOP));
		$form->setElement('checkbox', 'checkbox', array('label'=>'Checkbox', 'value'=>true));
		$form->setElement('textarea', 'textarea', array('label'=>'Textarea', 'value'=>'Welt'));
		$form->save();
		*/
		
		$parent = $this->Menu()->findOneBy('label', 'Einstellungen');
		$item = $this->createMenuItem(array(
			'label' => 'Update',
			'onclick' => 'openAction(\'update\');',
			'class' => 'ico2 swap1',
			'active' => 1,
			'parent' => $parent,
			'style' => 'background-position: 5px 5px;'
		));
		$this->Menu()->addItem($item);
		$this->Menu()->save();
		
		$event = $this->createEvent(
	 		'Enlight_Controller_Dispatcher_ControllerPath_Backend_Update',
	 		'onGetControllerPath'
	 	);
	 	$this->subscribeEvent($event);
		
	 	return true;
	}
	
	/**
	 * Event listener method
	 *
	 * @param Enlight_Event_EventArgs $args
	 */
	public static function onGetControllerPath(Enlight_Event_EventArgs $args)
    {
		return dirname(__FILE__).'/Controllers/Update.php';
    }
}