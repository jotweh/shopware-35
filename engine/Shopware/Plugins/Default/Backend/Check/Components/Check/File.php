<?php
/**
 * Shopware Check File
 * 
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Heiner Lohaus
 */
class Shopware_Components_Check_File implements IteratorAggregate, Countable
{
	protected $list;
		
	/**
	 * Checks all requirements
	 */
	protected function checkAll()
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
	
	/**
	 * Checks a requirement
	 *
	 * @param object $file
	 * @return bool
	 */
	protected function check($file)
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
		unset($file->test);
		
		return $file->version;
	}
	
	/**
	 * Compares the requirement with the version
	 *
	 * @param string $name
	 * @param string $version
	 * @param string $required
	 * @return bool
	 */
	protected function compare($name, $version, $required)
	{
		return version_compare($required, $version, '<=');
	}
	
	/**
	 * Returns the check list
	 *
	 * @return Iterator
	 */
	public function getList()
	{
		if($this->list === null) {
			$this->list = new Zend_Config_Xml(
				dirname(__FILE__) . '/Data/File.xml',
				'files',
				true
			);
			$this->list = $this->list->file;
			$this->checkAll();
		}
		return $this->list;
	}
	
	/**
	 * Returns the check list
	 *
	 * @return Iterator
	 */
	public function getIterator()
    {
        return $this->getList();
    }
    
    /**
	 * Returns the check list
	 *
	 * @return array
	 */
    public function toArray()
    {
    	return $this->getList()->toArray();
    }
    
    /**
     * Counts the check list
     *
     * @return int
     */
    public function count()
    {
    	return $this->getList()->count();
    }
}