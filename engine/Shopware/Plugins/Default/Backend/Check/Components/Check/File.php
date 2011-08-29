<?php
/**
 * Shopware Config Component
 * 
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Heiner Lohaus
 */
class
	Shopware_Components_Check_File implements IteratorAggregate
{
	protected $list;
		
	public function checkAll()
	{
		foreach ($this->list as $requirement) {
			$requirement->required = Shopware()->Config()->Version;
			$requirement->version = $this->check($requirement);
			$requirement->result = $this->compare(
				$requirement->name,
				$requirement->version,
				$requirement->required
			);
		}
	}
	
	public function check($file)
	{
		if (!file_exists($file->name)) {
			return false;
		}
		$file->hash = md5_file($file->name);
		
		foreach ($file->test as $test) {
			if($test->hash == $file->hash) {
				$file->version = $test->version;
			}
		}
		return $file->version;
	}
	
	public function compare($name, $version, $required)
	{
		return version_compare($required, $version, '<=');
	}
	
	public function getList()
	{
		if($this->list === null) {
			$this->list = new Zend_Config_Xml(
				dirname(__FILE__) . '/Data/File.xml',
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