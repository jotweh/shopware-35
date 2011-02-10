<?php
class Enlight_Controller_Request_RequestTestCase extends Zend_Controller_Request_HttpTestCase implements Enlight_Controller_Request_Request
{
	public function setQuery($spec, $value = null)
    {
    	if(!is_array($spec) && $value===null) {
    		unset($_GET[$spec]);
    		return $this;
    	}
    	return parent::setQuery($spec, $value);
    }
}