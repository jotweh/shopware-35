<?php
/**
 * Shopware Currency Model
 * 
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Heiner Lohaus
 */
class Shopware_Models_Currency extends Zend_Currency
{
	protected $id;
	
	/**
	 * Constructor method
	 *
	 * @param int|string|array $options
	 * @param string|Zend_Locale $locale
	 */
    public function __construct($options = null, $locale = null)
    {
    	if(is_numeric($options)) {
    		$this->id = (int) $options;
    		$sql = 'SELECT `currency`, `templatechar` as `symbol`, `symbol_position` as `position` FROM `s_core_currencies` WHERE `id` LIKE ?';
    		$options = Shopware()->Db()->fetchRow($sql, $this->id);
    	} elseif(is_string($options)) {
    		$sql = 'SELECT `id`, `currency`, `templatechar` as `symbol`, `symbol_position` as `position` FROM `s_core_currencies` WHERE `currency` LIKE ?';
    		$options = Shopware()->Db()->fetchRow($sql, $options);
    		$this->id = isset($options['id']) ? (int) $options['id'] : null;
    	}
    	if(isset($options['symbol']) && ord($options['symbol']) == 128) {
			$options['symbol'] = '&euro;';
		}
		if(isset($options['position'])) {
			$options['position'] = (int) $options['position'];
			if(empty($options['position'])) {
				unset($options['position']);
			}
		}
    	parent::__construct($options, $locale);
    }
    
    /**
     * Returns currency id
     *
     * @return int
     */
    public function getId()
    {
    	return $this->id;
    }
}