<?php
class Shopware_Models_Currency extends Zend_Currency
{
	protected $_id;
	
    public function __construct($options = null, $locale = null)
    {
    	if($options!==null&&is_numeric($options)) {
    		$this->_id = (int) $options;
    		$sql = 'SELECT `currency` FROM `s_core_currencies` WHERE `id` LIKE ?';
    		$options = Shopware()->Db()->fetchOne($sql, $this->_id);
    	}
    	parent::__construct($options, $locale);
    }
    
    public function getId()
    {
    	return $this->_id;
    }
}