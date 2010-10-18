<?php
class Shopware_Models_Locale extends Zend_Locale
{
	protected $_id;
	
    public function setLocale($locale = null)
    {
    	if(is_numeric($locale)) {
    		$this->_id = (int) $locale;
    		$sql = 'SELECT `locale` FROM `s_core_locales` WHERE `id` LIKE ?';
    		$locale = Shopware()->Db()->fetchOne($sql, $this->_id);
    	} elseif (is_string($locale)) {
    		$sql = 'SELECT `id` FROM `s_core_locales` WHERE `locale` LIKE ?';
    		$this->_id = (int) Shopware()->Db()->fetchOne($sql, $locale);
    	}
    	return parent::setLocale($locale);
    }
    
    public function getId()
    {
    	return $this->_id;
    }
}