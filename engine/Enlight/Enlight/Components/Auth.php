<?php
class Enlight_Components_Auth extends Zend_Auth
{
	protected $_adapter = null;
	
	public function getAdapter()
	{
		return $this->_adapter;
	}
	
	public function setAdapter(Zend_Auth_Adapter_Interface $adapter)
	{
		$this->_adapter = $adapter;
	}
	
	public function authenticate(Zend_Auth_Adapter_Interface $adapter=null)
    {
    	if(!$adapter)
    	{
    		$adapter = $this->_adapter;
    	}
    	$result = parent::authenticate($adapter);

    	if ($result->isValid()&&method_exists($adapter, 'getResultRowObject')) {
    		$user = $adapter->getResultRowObject();
    		$this->getStorage()->write($user);
    	}

       return $result;
    }
    
    public static function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }
}