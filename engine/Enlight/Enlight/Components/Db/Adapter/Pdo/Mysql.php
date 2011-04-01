<?php
/**
 * Enlight Mysql Db Adapter
 * 
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Heiner Lohaus
 * @package Enlight
 * @subpackage Components
 */
class	Enlight_Components_Db_Adapter_Pdo_Mysql extends Zend_Db_Adapter_Pdo_Mysql
{
	/**
     * Quote a raw string.
     *
     * @param string $value
     * @return string
     */
	protected function _quote($value)
    {
    	if($value instanceof Zend_Date) {
    		$value = $value->toString('YYYY-MM-dd HH:mm:ss');
    	}
    	return parent::_quote($value);
    }
    
    /**
     * Special handling for PDO query().
     * All bind parameter names must begin with ':'
     *
     * @param string|Zend_Db_Select $sql The SQL statement with placeholders.
     * @param array $bind An array of data to bind to the placeholders.
     * @return Zend_Db_Statement_Pdo
     * @throws Zend_Db_Adapter_Exception To re-throw PDOException.
     */
    public function query($sql, $bind = array())
    {
        if (empty($bind) && $sql instanceof Zend_Db_Select) {
            $bind = $sql->getBind();
        }

        if (is_array($bind)) {
            foreach ($bind as $name => $value) {
                if($value instanceof Zend_Date) {
		    		$bind[$name] = $value->toString('YYYY-MM-dd HH:mm:ss');
		    	}
            }
        }
        
        return parent::query($sql, $bind);
    }
}