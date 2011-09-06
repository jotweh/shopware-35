<?php
/**
 * Shopware DbTable Config Component
 * 
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Heiner Lohaus
 * @package Shopware
 * @subpackage Components_Config
 */
class Shopware_Components_Config_DbTable extends Shopware_Components_Config
{
	/**
	 * The table name in database.
	 *
	 * @var string
	 */
	protected $_name;
	
	/**
	 * The db table instance.
	 *
	 * @var string
	 */
	protected $_dbTable;
	
	/**
	 * The cache instance.
	 *
	 * @var string
	 */
	protected $_cache;
	
	/**
	 * The cache id prefix 
	 *
	 * @var string
	 */
	protected $_cacheIdPrefix = 'Shopware_Config_';
	
	/**
	 * The db table instance.
	 *
	 * @var string
	 */
	protected $_cacheId;
	
	/**
	 * The default cache tags.
	 *
	 * @var string
	 */
	protected $_cacheTags = array('Shopware_Config');
	
	/**
	 * The current section.
	 *
	 * @var string
	 */
	protected $_section;
	
	/**
	 * The section separator.
	 *
	 * @var string
	 */
	protected $_sectionSeparator = ':';
	
	/**
	 * The section extends.
	 *
	 * @var string
	 */
	protected $_extends;
	
	/**
	 * The name column in the database table.
	 *
	 * @var null|string
	 */
	protected $_nameColum = 'name';
	
	/**
	 * The value column in the database table.
	 *
	 * @var null|string
	 */
	protected $_valueColum = 'value';
	
	/**
	 * The section colum in the database table.
	 *
	 * @var null|string
	 */
	protected $_sectionColum = 'section';
	
	/**
	 * The automatic serialization option value.
	 *
	 * @var bool
	 */
	protected $_automaticSerialization = false;
	
	/**
	 * The allow modificationsoption value.
	 *
	 * @var bool
	 */
	protected $_allowModifications = false;
	
	/**
	 * The dirty fields list.
	 *
	 * @var bool
	 */
	protected $_dirtyFields = array();
	
	/**
	 * The created colum in the database table.
	 *
	 * @var null|string
	 */
	protected $_createdColumn = 'created';
	
	/**
	 * The created colum in the database table.
	 *
	 * @var null|string
	 */
	protected $_updatedColumn = 'updated';
	
	/**
	 * The local data list.
	 *
	 * @var null|string
	 */
    protected $_data = array();
	
	/**
	 * Constructor method
	 * 
	 * Expect an array with the options.
	 *
	 * @param array $config
	 */
	public function __construct(array $config)
    {
    	if($this->_name!==null && !isset($config['name'])) {
  			$config['name'] = $this->_name;
  		}
  		
    	$this->_dbTable = new Enlight_Components_Table($config);

    	$this->setOptions($config);

    	if(!$this->testCache()) {
    		$this->load();
    	}
    	
    	parent::__construct($this->_data, $this->_allowModifications);
    	
    	if($this->testCache()) {
    		$this->loadCache();
    	} else {
    		$this->saveCache();
    	}
    }
    
    /**
     * Sets the options of an array.
     *
     * @param array $options
     * @return Shopware_Components_Config_DbTable
     */
    public function setOptions(array $options)
    {
    	foreach ($options as $key=>$option) {
    		switch ($key) {
    			case 'section':
    				$this->setSection($option);
    				break;
    			case 'extends':
    				$this->setExtends($option);
    				break;
    			case 'cache':
    			case 'cacheTags':
    			case 'nameColum':
    			case 'valueColum':
    			case 'sectionColum':
    				$this->{'_'.$key} = $option;
    				break;
    			case 'automaticSerialization':
    			case 'allowModifications':
    				$this->{'_'.$key} = (bool) $option;
    				break;
    			case 'data':
    				$this->{'_'.$key} = (array) $option;
    				break;
    			default:
    				break;
    		}
    	}
    	return $this;
    }
    
    /**
     * Sets the current section of the config list.
     *
     * @param array|string $section
     * @return Shopware_Components_Config_DbTable
     */
    public function setSection($section)
    {
    	if(is_array($section)) {
			$section = implode($this->_sectionSeparator, $section);
		}
		$this->_section = $section;
		return $this;
    }
    
    /**
     * Sets the extends of the config list.
     *
     * @param array|string $extends
     * @return Shopware_Components_Config_DbTable
     */
    public function setExtends($extends)
    {
    	if(is_array($extends)) {
    		$extendingSection = $this->_section;
    		foreach ($extends as $key=>$extendedSection) {
    			if(!is_int($key)) {
    				$extendingSection = $key;
    			}
    			if(is_array($extendedSection)) {
    				$extendedSection = implode($this->_sectionSeparator, $extendedSection);
    			}
    			$this->setExtend($extendingSection, $extendedSection);
    		}
    	} else {
    		$this->_assertValidExtend($extendingSection, $extendedSection);
    		$this->setExtend($this->_section, $extends);
    	}
    	return $this;
    }
    
    /**
     * Sets an extending section and an expanded section.
     *
     * @param string $extendingSection
     * @param string $extendedSection
     * @return Shopware_Components_Config_DbTable
     */
    public function setExtend($extendingSection, $extendedSection = null)
    {
    	if($extendingSection!==$extendedSection){
    		parent::setExtend($extendingSection, $extendedSection);
    	}
    	return $this;
    }
    
    /**
     * Returns a unique id for the cache list.
     *
     * @return string
     */
    public function getCacheId()
    {
    	if($this->_cacheId === null) {
    		$this->_cacheId = $this->_cacheIdPrefix . md5(serialize(array(
    			$this->_dbTable->info('name'),
    			$this->_sectionColum,
    			$this->_section,
    			$this->_extends,
    			$this->_cacheTags
    		)));
    	}
    	return $this->_cacheId;
    }
    
    /**
     * Tests the data in the cache storage.
     *
     * @return bool
     */
    public function testCache()
    {
    	if($this->_cache === null) {
    		return false;
    	}
    	return $this->_cache->test($this->getCacheId());
    }
    
    /**
     * Loads the data from cache storage.
     *
     * @return bool
     */
    protected function loadCache()
    {
    	if($this->_cache === null) {
    		return false;
    	}
    	$this->_data = $this->_cache->load($this->getCacheId());
    	return $this->_data !== null;
    }
    
    /**
     * Stores the data in the cache storage.
     *
     * @return void
     */
    protected function saveCache()
    {
    	if($this->_cache === null) {
    		return false;
    	}
    	return $this->_cache->save($this->_data, $this->getCacheId(), $this->_cacheTags);
    }
    
    /**
     * Loads the default data and the sections from the data store.
     *
     * @return void
     */
    protected function load()
    {
    	$extendingSection = $this->_section;
    	$this->_data = $this->_arrayMergeRecursive($this->readSection($extendingSection), $this->_data);
    	if(!empty($this->_extends)) {
    		while (isset($this->_extends[$extendingSection])) {
	    		$extendingSection = $this->_extends[$extendingSection];
	    		$this->_data = $this->_arrayMergeRecursive($this->readSection($extendingSection), $this->_data);
	    	}
    	};
    }
    
    /**
     * Reads a section from the data store.
     *
     * @param string|array $section
     * @return array
     */
    protected function readSection($section)
    {
    	$select = $this->_dbTable->select()->from($this->_dbTable->info('name'), array($this->_nameColum, $this->_valueColum));
    	    	
    	if(!empty($this->_sectionColum)) {
    		if(is_array($this->_sectionColum)) {
    			if(!is_array($section)) {
    				$section = explode($this->_sectionSeparator, $section);
    			}
    			foreach ($this->_sectionColum as $key=>$sectionColum) {
    				if(isset($section[$key])) {
    					$select->where($sectionColum.'=?', $section[$key]);
    				}
    			}
    		} elseif($section!==null) {
    			$select->where($this->_sectionColum.'=?', $section);
    		}
    	}
    	if($this->_valueColum!=='*') {
    		$data = $this->_dbTable->getAdapter()->fetchPairs($select);
    	} else  {
    		$data = $this->_dbTable->getAdapter()->fetchAssoc($select);
    	}
    	
    	if($this->_automaticSerialization) {
	    	foreach ($data as $key=>$value) {
	    		$data[$key] = unserialize($value);
	    	}
    	}
    	return $data;
    }
    
    /**
     * Returns a specific value from the list.
     *
     * @param string $name
     * @param mixed $default
     * @param bool $save
     * @return mixed
     */
    public function get($name, $default=null, $save=false)
	{
		if($save && !$this->__isset($name)) {
			$this->insert($name, $default);
			return $default;
		} else {
			return parent::get($name, $default);
		}
	}
    
	/**
	 * Sets a value in the list by name.
	 *
	 * @param string $name
	 * @param mixed $value
	 * @return void
	 */
    public function __set($name, $value)
	{
		$this->_dirtyFields[] = $name;
		return parent::__set($name, $value);
	}
	
	/**
	 * Resets the dirty fields to an empty list.
	 */
	public function resetDirtyFields()
	{
		$this->_dirtyFields = array();
	}
	
	/**
	 * Returns the dirty field list as an array.
	 *
	 * @return array
	 */
	public function getDirtyFields()
	{
		$this->_dirtyFields = array_unique($this->_dirtyFields);
		return $this->_dirtyFields;
	}
	
	/**
	 * Adds a new value in the datastore.
	 *
	 * @param string $name
	 * @param mixed $value
	 * @return bool
	 */
	public function insert($name, $value)
    {
    	$this->__set($name, $value);
		return $this->save($name, false);
    }
	
    /**
     * Saves the data changes in the datastore.
     *
     * @param null|string|array $fields
     * @param bool $update
     * @return bool
     */
    public function save($fields=null, $update=true)
    {
    	$db = $this->_dbTable->getAdapter();
    	
    	if($fields===null) {
    		$fields = $this->getDirtyFields();
    	} elseif(empty($fields)) {
    		return true;
    	}
    	if(!is_array($fields)) {
    		$fields = array($fields);
    	}
    	
    	$updateData = array();
    	$insertData = array();
    	if($this->_updatedColumn!==null) {
			$updateData[$this->_updatedColumn] = new Zend_Db_Expr('NOW()');
			$insertData[$this->_updatedColumn] = new Zend_Db_Expr('NOW()'); 
		}
		if($this->_createdColumn!==null) {
			$insertData[$this->_createdColumn] = new Zend_Db_Expr('NOW()'); 
		}
			
    	$where = array();
    	if(is_array($this->_sectionColum)) {
			$section = explode($this->_sectionSeparator, $this->_section);
			foreach ($this->_sectionColum as $key=>$sectionColum) {
				if(isset($section[$key])) {
					$where[] = $db->quoteInto($sectionColum.'=?', $section[$key]);
					$insertData[$sectionColum] = $section[$key];
				}
			}
		} elseif($this->_section!==null) {
			$where[] = $db->quoteInto($this->_sectionColum.'=?', $this->_section);
			$insertData[$this->_sectionColum] = $this->_section;
		}
		
    	foreach ($fields as $field) {
    		$fieldWhere = $where;
    		$fieldWhere[] = $db->quoteInto($this->_nameColum.'=?', $field);
    		    		    		    		
			$row = $this->_dbTable->fetchRow($fieldWhere);
			    		
    		if($row!==null) {
    			if($update) {
    				$data = $updateData;
    				if ($this->_automaticSerialization){
						$data[$this->_valueColum] = serialize($this->get($field));
					} else {
	    				$data[$this->_valueColum] = $this->get($field);
					}
	    			$this->_dbTable->update($data, $fieldWhere);
    			}
    		} else {
    			$data = $insertData;
    			$data[$this->_nameColum] = $field;
    			if ($this->_automaticSerialization){
					$data[$this->_valueColum] = serialize($this->get($field));
				} else {
    				$data[$this->_valueColum] = $this->get($field);
				}
    			$this->_dbTable->insert($data);
    		}
    	}
    	$this->_dirtyFields = array_diff($this->_dirtyFields, $fields);
    	return true;
    }
}