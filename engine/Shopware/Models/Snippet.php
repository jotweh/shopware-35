<?php
class Shopware_Models_Snippet extends Shopware_Components_Config_DbTable
{
	protected $_name = 's_core_snippets';
	protected $_sectionColum = array('namespace', 'localeID', 'shopID');
	protected $_allowModifications = true;

	public function getNamespace()
	{
		return $this->_sectionColum[0]=='namespace' ? $this->_section[0] : null;
	}
}