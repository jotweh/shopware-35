<?php
class Shopware_Plugins_Core_Log_HttpHeaders extends Zend_Wildfire_Channel_HttpHeaders
{
	public static function getInstance($skipCreate=false)
    {
        if (self::$_instance===null && $skipCreate!==true) {
            return self::init(__CLASS__);
        }
        return self::$_instance;
    }
	
	protected function _registerControllerPlugin()
    {
    	$event = new Enlight_Event_EventHandler(
	 		'Enlight_Controller_Front_DispatchLoopShutdown',
	 		array($this, 'dispatchLoopShutdown'),
	 		1000
	 	);
		Enlight::Instance()->Events()->registerListener($event);
    }
    
    public function dispatchLoopShutdown()
    {
        $this->flush();
    }
    
    public function getRequest()
    {
        if (!$this->_request) {
            $request = Enlight::Instance()->Front()->Request();
            $this->_request = $request;
        }
        if (!$this->_request) {
            throw new Zend_Wildfire_Exception('Request objects not initialized.');
        }
        return $this->_request;
    }

    public function getResponse()
    {
        if (!$this->_response) {
            $response = Enlight::Instance()->Front()->Response();
            if ($response) {
            	$this->_response = $response;
            }
        }
        if (!$this->_response) {
            throw new Zend_Wildfire_Exception('Response objects not initialized.');
        }
        return $this->_response;
    }
}