<?php
/**
 * Shopware DbTable Config Component
 * 
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Heiner Lohaus
 */
class Shopware_Components_Config_DbTable extends Shopware_Components_Config
{
	protected $_name;
	protected $_dbTable;
	protected $_cache;
	protected $_cacheId;
	protected $_cacheTags = array('Shopware_Config');
	protected $_section;
	protected $_sectionSeparator = ':';
	protected $_extends;
	protected $_nameColum = 'name';
	protected $_valueColum = 'value';
	protected $_sectionColum = 'section';
	protected $_automaticSerialization = false;
	protected $_allowModifications = false;
	protected $_dirtyFields = array();
	protected $_createdColumn = 'created';
	protected $_updatedColumn = 'updated';
    protected $_data = array();
	
	/**
	 * Constructor method
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
     * Set options method
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
     * Set section method
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
     * Set extends method
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
     * Set extend method
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
     * Returns cache id method
     *
     * @return string
     */
    public function getCacheId()
    {
    	if($this->_cacheId === null) {
    		$this->_cacheId = md5(serialize(array(
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
     * Test cache method
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
     * Load cache method
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
     * Save cache method
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
     * Load data method
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
     * Read section method
     *
     * @param unknown_type $section
     * @return unknown
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
     * Returns value method
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
	 * Set value method
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
	 * Reset dirty fields
	 */
	public function resetDirtyFields()
	{
		$this->_dirtyFields = array();
	}
	
	/**
	 * Returns dirty fields
	 *
	 * @return array
	 */
	public function getDirtyFields()
	{
		$this->_dirtyFields = array_unique($this->_dirtyFields);
		return $this->_dirtyFields;
	}
	
	/**
	 * Insert value method
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
     * Save data method
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
			$insertData[$this->_sectionColum] = $section[$this->_section];
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