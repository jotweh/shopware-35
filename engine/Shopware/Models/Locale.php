<?php
/**
 * Shopware Locale Model
 * 
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Heiner Lohaus
 */
class Shopware_Models_Locale extends Zend_Locale
{
	protected $id;
	
	/**
	 * Set locale method
	 *
	 * @param string $locale
	 * @return Shopware_Models_Locale
	 */
    public function setLocale($locale = null)
    {
    	if(is_numeric($locale)) {
    		$this->id = (int) $locale;
    		$sql = 'SELECT `locale` FROM `s_core_locales` WHERE `id` LIKE ?';
    		$locale = Shopware()->Db()->fetchOne($sql, $this->id);
    	} else {
    		$this->id = null;
    	}
    	parent::setLocale($locale);
    	if ($this->id === null) {
    		$sql = 'SELECT `id` FROM `s_core_locales` WHERE `locale` LIKE ?';
    		$this->id = (int) Shopware()->Db()->fetchOne($sql, $this->_locale);
    	}
    	return $this;
    }
    
    /**
     * Returns locale id
     *
     * @return int
     */
    public function getId()
    {
    	return $this->id;
    }
    
    /**
	 * Sleep instance method
	 *
	 * @return array
	 */
    public function __sleep()
    {
        return array('id', '_locale');
    }
}