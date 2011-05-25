<?php
class Shopware_Components_Form_SaveHandler_DbTable extends Zend_Db_Table_Abstract
{
	protected $_name = 's_core_plugin_elements';
	
	protected $_primary = 'id';
	
	protected $_formPrimary = 'pluginID';
		
	protected $_colums = array(
		'id' => 'id',
		'name' => 'name',
		'type' => 'type',
		'pluginID' => 'pluginID',
		'value' => 'value',
		'description' => 'description',
		'label' => 'label',
		'order' => 'order',
		//'allowEmpty' => 'allowEmpty',
		'required' => 'required',
		'filters' => 'filters',
		'validators' => 'validators',
		'scope' => 'scope',
		'options' => 'options'
	);
	
	protected $_order = array(
		'order', 'id'
	);
	
	public function __construct($config=array())
    {
        if ($config instanceof Zend_Config) {
            $config = $config->toArray();
        }
        foreach ($config as $key => $value) {
        	if($key=='order') {
        		$this->_order = $value;
        	} elseif(substr($key,-6)=='Column') {
        		$this->_colums[substr($key,0,-6)] = $value;
        	}
        }
        parent::__construct($config);
    }
    
    public function load(Shopware_Components_Form $form)
    {
    	if($this->_formPrimary!=null) {
    		$where = array($this->_formPrimary.'=?'=>(int)$form->getId());
    	} else {
    		$where = null;
    	}
    	$rows = $this->fetchAll($where, $this->_order);
		$array_elements = array();
		foreach ($rows as $row) {
			$array_element = array('options'=>array());
			foreach ($this->_colums as $key => $column) {
				if(in_array($key, array('filters', 'validators'))) {
					if(!empty($row->{$column})) {
						$array_element[$key] = unserialize($row->{$column});
					}
				}
				elseif ($key=='options'){
					// Support free attributes per field
					$array_element[$key] = unserialize($row->{$column});
				}
				elseif (isset($row->{$column})) {
					if(in_array($key, array('type','name'))) {
						$array_element[$key] = $row->{$column};
					} elseif($key=='value') {
						$array_element['options'][$key] = unserialize($row->{$column});
					} else {
						$array_element['options'][$key] = $row->{$column};
					}
				}
			}
			$array_elements[] = $array_element;
		}
		$form->setElements($array_elements);
    }
    
    public function getShortName($name)
	{
		if($name instanceof Zend_Form_Element) {
			$name = $name->getType();
		}
		if(is_object($name)) {
			$name = get_class($name);
		}
		return strtr($name, array('Zend_Form_Element_'=>'', 'Zend_Filter_'=>'', 'Zend_Validate_'=>''));
	}

	public function toArrayElement($element)
	{
		$options = array(
			'description',
			'allowEmpty',
			'ignore',
			'order',
			'label',
			'value',
			'id',
			'name',
			'belongsTo',
			'attributes'
		);

		$array_element = array(
			'type' => $this->getShortName($element),
			'options'=>$element->getAttribs()
		);
		unset($array_element['options']['helper']);
		
		$array_element['options']['required'] = $element->isRequired();
		//$array_element['options']['options'] = serialize($array_element['options']['options']);

		foreach ($options as $option) {
			$func = 'get'.ucwords($option);
			if ($func != "getAttributes"){
				$value = $element->$func();
			}
			if($value !== null) {
				$array_element['options'][$option] = $value;
			}
		}
		
		$filters = $element->getFilters();
		if($filters) {
			$array_element['filters'] = array();
			foreach ($filters as $filterKey => $filter) {
				$array_element['filters'][] = array('filter' => $this->getShortName($filter));
			}
		}
		
		$validators = $element->getValidators();
		if($validators) {
			$array_element['validators'] = array();
			foreach ($validators as $validatorKey => $validator) {
				$array_validator = array('validator' => $this->getShortName($validator));
				$validator_options = $validator->getMessageVariables();
				if($validator_options) {
					$array_validator['options'] = array();
					foreach ($validator_options as $validator_option) {
						$value = $validator->$validator_option;
						if($value !== null) {
							$array_validator['options'][$validator_option] = $validator->$validator_option;
						}
					}
				}
				$array_element['validators'][] = $array_validator;
			}
		}
		return $array_element;
	}
    
    public function save(Shopware_Components_Form $form)
    {
        foreach ($form->getElements() as $element) {
        	$array_element = $this->toArrayElement($element);
        	$data = array(
        		$this->_formPrimary=>$form->getId()
        	);
        	$data_id = null;
			
        	foreach ($this->_colums as $key => $colum) {
        		if($key=='id') {
					if(isset($array_element['options']['id']) && is_numeric($array_element['options']['id'])) {
						$data_id = (int) $array_element['options']['id'];
					}
				} elseif(in_array($key, array('filters', 'validators'))) {
					if(isset($array_element[$key])) {
						if(!empty($array_element[$key])) {
							$data[$key] = serialize($array_element[$key]);
						} else {
							$data[$key] = null;
						}
					}
				} elseif($key=='value') {
					if(isset($array_element['options'][$key])) {
						$data[$key] = serialize($array_element['options'][$key]);
					}
				} elseif (isset($array_element['options'][$key])) {
					$data[$key] = $array_element['options'][$key];
				}
				elseif ($key=='options'){
					// Support free attributes per field
					$data['options'] = serialize(isset($array_element['options']['attributes']) ? $array_element['options']['attributes'] : array());
				}
				elseif (isset($array_element[$key])) {
					$data[$key] = $array_element[$key];
				}
        	}
        	if($this->_formPrimary!==null) {
        		$where = array(
        			$this->_formPrimary.'=?'=>$form->getId(),
        			$this->_colums['name'].'=?'=>$data['name']
        		);
        		$row = $this->fetchRow($where);
        		if(!empty($row->id)) {
        			$data_id = $row->id;
        		}
        	}
        	if(!empty($data_id)) {
				$this->update($data, array($this->_colums['id'].'=?' => $data_id));
			} else {
				$element->id = $this->insert($data);
			}
        }
    }
}