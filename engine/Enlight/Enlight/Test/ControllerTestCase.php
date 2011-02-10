<?php
/**
 * Enter description here...
 *
 */
abstract class Enlight_Test_ControllerTestCase extends Enlight_Test_TestCase
{
	/**
     * @var Zend_Controller_Front
     */
    protected $_front;
    
    /**
     * @var Enlight_Template_TemplateManager
     */
    protected $_template;
    
    /**
     * @var Enlight_View_ViewDefault
     */
    protected $_view;

    /**
     * @var Zend_Controller_Request_Abstract
     */
    protected $_request;

    /**
     * @var Zend_Controller_Response_Abstract
     */
    protected $_response;
    	
	public function setUp()
    {
        $this->reset();
        $this->Front()
             ->setRequest($this->Request())
             ->setResponse($this->Response());
    }
    
    public function dispatch($url = null)
    {
        $request    = $this->Request();
        if (null !== $url) {
            $request->setRequestUri($url);
        }
        $request->setPathInfo(null);
        
        $this->Front()
             ->setRequest($request)
             ->setResponse($this->Response());

        return Enlight::Instance()->run();
    }
    
    public function reset()
    {		
    	$this->resetRequest();
        $this->resetResponse();
        
        $this->_view = null;
        $this->_template = null;
        $this->_front = null;
        
        Enlight::Instance()->Hooks()->resetHooks();
        Enlight::Instance()->Events()->resetEvents();
        Enlight::Instance()->Plugins()->resetPlugins();
        
        $ressources = array(
        	'Plugins' => 'Enlight_Plugin_PluginManager',
        	'Template' => 'Enlight_Template_TemplateManager',
        	'Front' => 'Enlight_Controller_Front',
        	'View' => 'Enlight_View_ViewDefault',
        	'ViewRenderer' => 'Enlight_Controller_Plugins_ViewRenderer_Bootstrap'
        );
        
        foreach ($ressources as $ressource=>$class) {
        	Enlight_Class::resetInstance($class);
			Enlight::Instance()->Bootstrap()->resetResource($ressource);
        }
    }
            
    /**
     * Reset the request object
     *
     * Useful for test cases that need to test multiple trips to the server.
     *
     * @return Enlight_Test_ControllerTestCase
     */
    public function resetRequest()
    {
        if ($this->_request instanceof Enlight_Controller_Request_RequestTestCase) {
            $this->_request->clearQuery()
                           ->clearPost()
                           ->clearCookies();
        }
        $this->_request = null;
        return $this;
    }

    /**
     * Reset the response object
     *
     * @return Enlight_Test_ControllerTestCase
     */
    public function resetResponse()
    {
        $this->_response = null;
        return $this;
    }
    
    /**
     * Retrieve front controller instance
     *
     * @return Enlight_Controller_Front
     */
    public function Front()
    {
        if (null === $this->_front) {
            $this->_front = Enlight::Instance()->Bootstrap()->getResource('Front');
        }
        return $this->_front;
    }
    
    /**
     * Retrieve front controller instance
     *
     * @return Enlight_Template_TemplateManager
     */
    public function Template()
    {
        if (null === $this->_template) {
            $this->_template = Enlight::Instance()->Bootstrap()->getResource('Template');
        }
        return $this->_template;
    }
    
    /**
     * Retrieve front controller instance
     *
     * @return Enlight_View_ViewDefault
     */
    public function View()
    {
        if (null === $this->_view) {
            $this->_view = Enlight::Instance()->Bootstrap()->getResource('View');
        }
        return $this->_view;
    }
    
    /**
     * Retrieve test case request object
     *
     * @return Enlight_Controller_Request_RequestHttp
     */
    public function Request()
    {
        if (null === $this->_request) {
            $this->_request = new Enlight_Controller_Request_RequestTestCase;
        }
        return $this->_request;
    }

    /**
     * Retrieve test case response object
     *
     * @return Enlight_Controller_Response_ResponseHttp
     */
    public function Response()
    {
        if (null === $this->_response) {
            $this->_response = new Enlight_Controller_Response_ResponseTestCase;
        }
        return $this->_response;
    }
    
    /**
     * @param mixed $name
     * @return void
     */
    public function __get($name)
    {
        switch ($name) {
            case 'request':
                return $this->Request();
            case 'response':
                return $this->Response();
            case 'front':
            case 'frontController':
                return $this->Front();
        }
        return null;
    }
}