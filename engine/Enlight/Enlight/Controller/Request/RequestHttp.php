<?php
/**
 * HTTP request object for use with Enlight_Controller 
 * 
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Heiner Lohaus
 * @package Enlight
 * @subpackage Controller
 */
class Enlight_Controller_Request_RequestHttp extends Zend_Controller_Request_Http implements Enlight_Controller_Request_Request
{
	/**
     * Set GET values method
     *
     * @param  string|array $spec
     * @param  null|mixed $value
     * @return Zend_Controller_Request_Http
     */
	public function setQuery($spec, $value = null)
    {
    	if(!is_array($spec) && $value===null) {
    		unset($_GET[$spec]);
    		return $this;
    	}
    	return parent::setQuery($spec, $value);
    }
    
    /**
     * Set HTTP host method
     *
     * @param string $host
     */
    public function setHttpHost($host)
    {
    	$_SERVER['HTTP_HOST'] = $host;
    	return $this;
    }
}