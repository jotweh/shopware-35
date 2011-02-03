<?php
class Shopware_Models_Plugin_Config extends Shopware_Components_Config_DbTable
{
	protected $_name = 's_core_plugin_configs';
	protected $_sectionColum = array('pluginID', 'localeID', 'shopID');
	protected $_allowModifications = true;
	protected $_cacheTags = array('Shopware_Plugin');
	protected $_automaticSerialization = true;
	protected $_createdColumn = null;
	protected $_updatedColumn = null;
}