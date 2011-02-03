<?php
class Shopware_Plugins_Core_ErrorHandler_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{	
	public function install()
	{		
		$event = $this->createEvent(
			'Enlight_Controller_Front_StartDispatch',
			'onStartDispatch'
		);
		$this->subscribeEvent($event);
		return true;
	}
	
	public static function onStartDispatch($args)
	{
		Shopware()->Plugins()->Core()->ErrorHandler()->registerErrorHandler(E_ALL | E_STRICT);
	}

    /**
     *
     * @var callback
     */
    protected $_origErrorHandler       = null;
    
    /**
     *
     * @var boolean
     */
    protected $_registeredErrorHandler = false;
    
    /**
     *
     * @var array
     */
    protected $_errorHandlerMap        = null;
    
    /**
     *
     * @var array
     */
    protected $_errorLevel             = 0;
    
    /**
     *
     * @var array
     */
    protected $_errorLog             = false;
    
    /**
     *
     * @var array
     */
    protected $_errorList             = array();
    
    protected $_errorLevelList        = array(
     	E_ERROR				=> 'E_ERROR',
		E_WARNING			=> 'E_WARNING',
		E_PARSE				=> 'E_PARSE',
		E_NOTICE			=> 'E_NOTICE',
		E_CORE_ERROR		=> 'E_CORE_ERROR',
		E_CORE_WARNING		=> 'E_CORE_WARNING',
		E_COMPILE_ERROR		=> 'E_COMPILE_ERROR',
		E_COMPILE_WARNING	=> 'E_COMPILE_WARNING',
		E_USER_ERROR		=> 'E_USER_ERROR',
		E_USER_WARNING		=> 'E_USER_WARNING',
		E_USER_NOTICE		=> 'E_USER_NOTICE',
		E_ALL				=> 'E_ALL',
		E_STRICT			=> 'E_STRICT',
		E_RECOVERABLE_ERROR	=> 'E_RECOVERABLE_ERROR',
		8192				=> 'E_DEPRECATED',
		16384				=> 'E_USER_DEPRECATED',
    );
    
    /**
     * @link http://www.php.net/manual/en/function.set-error-handler.php Custom error handler
     */
    public function registerErrorHandler($errorLevel=E_ALL)
    {
        // Only register once.  Avoids loop issues if it gets registered twice.
        if ($this->_registeredErrorHandler) { 
        	return $this; 
        }
        
        $this->_origErrorHandler = set_error_handler(array($this, 'errorHandler'), $errorLevel);
                
        $this->_registeredErrorHandler = true;
        return $this;
    }
    
    /**
     * Error Handler will convert error into log message, and then call the original error handler
     *
     * @link http://www.php.net/manual/en/function.set-error-handler.php Custom error handler
     * @param int $errno
     * @param string $errstr
     * @param string $errfile
     * @param int $errline
     * @param array $errcontext
     * @return boolean
     */
    public function errorHandler($errno, $errstr, $errfile, $errline, $errcontext)
    {
    	if($this->_errorLog)
    	{
	    	$hash_id = md5($errno.$errstr.$errfile.$errline);
	    	if(!isset($this->_errorList[$hash_id]))
	    	{
	    		$errna = isset($this->_errorLevelList[$errno]) ? $this->_errorLevelList[$errno] : '';
	    		$this->_errorList[$hash_id] = array(
	    			'count'=>1,
	    			'code'=>$errno,
	    			'name'=>$errna,
	    			'message'=>$errstr,
	    			'line'=>$errline,
	    			'file'=>$errfile
	    			//'context'=>$errcontext
	    		);
	    	}
	    	else
	    	{
	    		++$this->_errorList[$hash_id]['count'];
	    	}
    	}
    	
    	switch ($errno)
    	{
    		case 0:
    		case E_NOTICE:
    		case E_WARNING:
    		case E_USER_NOTICE:
    		case E_RECOVERABLE_ERROR:
    		case E_STRICT:
    		case E_DEPRECATED:
    			break;
    		case E_CORE_WARNING:
    		case E_USER_WARNING:
    		case E_ERROR:
    		case E_USER_ERROR:
    		case E_CORE_ERROR:
    			break;
    		default:
    			throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
    			break;
    	}
        
        if ($this->_origErrorHandler !== null) {
            return call_user_func($this->_origErrorHandler, $errno, $errstr, $errfile, $errline, $errcontext);
        }
        return true;
    }
    
    public function getErrorLog()
    {
    	return $this->_errorList;
    }
    
    public function setEnabledLog($value=true)
    {
    	$this->_errorLog = $value ? true : false;
    }
    
    public function getCapabilities()
    {
        return array(
    		'install' => true,
    		'update' => true
    	);
    }
}