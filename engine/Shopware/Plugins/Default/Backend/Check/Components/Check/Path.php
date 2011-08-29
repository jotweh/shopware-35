<?php
/**
 * Shopware Config Component
 * 
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Heiner Lohaus
 */
class
	Shopware_Components_Check_Path implements IteratorAggregate
{
	protected $list;
		
	public function checkAll()
	{
		foreach ($this->list as $requirement) {
			$requirement->version = $this->check($requirement->name);
			$requirement->result = $this->compare(
				$requirement->name,
				$requirement->version,
				$requirement->required
			);
		}
	}
	
	public function check($name)
	{
		return file_exists($name) && is_readable($name) && is_writeable($name);
	}
	
	public function compare($name, $version, $required)
	{
		return $version;
	}
	
	public function getList()
	{
		if($this->list === null) {
			$this->list = new Zend_Config_Xml(
				dirname(__FILE__) . '/Data/Path.xml',
				'files'
			);
			$this->checkAll();
		}
		return $this->list;
	}
	
	public function getIterator()
    {
        return $this->getList();
    }
    
    public function toArray()
    {
    	return $this->getList()->toArray();
    }
    
    public function count()
    {
    	return $this->getList()->count();
    }
}