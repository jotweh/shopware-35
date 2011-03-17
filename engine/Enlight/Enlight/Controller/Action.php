<?php
abstract class Enlight_Controller_Action extends Enlight_Class implements Enlight_Hook
{
	/**
	 * Enter description here...
	 *
	 * @var Enlight_Controller_Front
	 */
	protected $front;
	/**
	 * Enter description here...
	 *
	 * @var Enlight_View_ViewDefault
	 */
	protected $view;
	protected $request;
	protected $response;
	
	protected $controller_name;
	
	public function __construct(Enlight_Controller_Request_Request $request, Enlight_Controller_Response_Response $response)
	{
		$this->setRequest($request)->setResponse($response);
		
		$this->controller_name = $this->Front()->Dispatcher()->getFullControllerName($this->Request());

		Enlight()->Events()->notify(__CLASS__.'_Init', array('subject'=>$this, 'request'=>$this->Request(), 'response'=>$this->Response()));
		Enlight()->Events()->notify(__CLASS__.'_Init_'.$this->controller_name, array('subject'=>$this, 'request'=>$this->Request(), 'response'=>$this->Response()));
		
		parent::__construct();
	}
	
	public function preDispatch()
	{
		
	}
    
	public function postDispatch()
	{
		
	}
		
	public function dispatch($action)
	{
		Enlight()->Events()->notify(__CLASS__.'_PreDispatch', array('subject'=>$this,'request'=>$this->Request()));
		Enlight()->Events()->notify(__CLASS__.'_PreDispatch_'.$this->controller_name, array('subject'=>$this, 'request'=>$this->Request()));
		$this->preDispatch();
		
		if ($this->Request()->isDispatched()&&!$this->Response()->isRedirect())
		{
			$action_name = $this->Front()->Dispatcher()->getFullActionName($this->Request());

			if(!$event = Enlight()->Events()->notifyUntil(__CLASS__.'_'.$action_name, array('subject'=>$this)))
		    {
		    	$this->$action();
		    }
			
			$this->postDispatch();
		}
		
		Enlight()->Events()->notify(__CLASS__.'_PostDispatch_'.$this->controller_name, array('subject'=>$this, 'request'=>$this->Request()));
		Enlight()->Events()->notify(__CLASS__.'_PostDispatch', array('subject'=>$this,'request'=>$this->Request()));
	}
	
	public function forward($action, $controller = null, $module = null, array $params = null)
    {
		$request = $this->Request();
		
        if(isset($params))
        {
            $request->setParams($params);
        }

        if(isset($controller))
        {
            $request->setControllerName($controller);
            
            if(isset($module))
            {
                $request->setModuleName($module);
            }
        }

        $request->setActionName($action)->setDispatched(false);
    }
    
    public function redirect($url, array $options = array())
    {
    	if (is_array($url)) {
    		$url = $this->Front()->Router()->assemble($url);
    	}
    	if(!preg_match('#^(https?|ftp)://#', $url)) {
    		if(strpos($url, '/') !== 0) {
    			$url = $this->Request()->getBaseUrl() . $url;
    		}
    		$uri = $this->Request()->getScheme().'://'.$this->Request()->getHttpHost();
    		$url = $uri . $url;
    	}
    	$this->Response()->setRedirect($url, empty($options['code']) ? 302 : (int) $options['code']);
    }
	
	public function setView ($view)
	{
		$this->view = $view;
		return $this;
	}
		
	public function setRequest (Enlight_Controller_Request_Request $request)
	{
        $this->request = $request;
		return $this;
	}
	
	public function setResponse (Enlight_Controller_Response_Response $response)
	{
        $this->response = $response;
		return $this;
	}
	
	/**
	 * Enter description here...
	 *
	 * @return Enlight_View_ViewDefault
	 */
	public function View()
	{
		if($this->view===null) {
			$this->view = Enlight::Instance()->Bootstrap()->getResource('View');
		}
		return $this->view;
	}
	
	/**
	 * Enter description here...
	 *
	 * @return Enlight_Controller_Front
	 */
	public function Front()
	{
		if($this->front===null){
			$this->front = Enlight_Class::Instance('Enlight_Controller_Front');
		}
		return $this->front;
	}
	
	/**
	 * Enter description here...
	 *
	 * @return Enlight_Controller_Request_Request
	 */
	public function Request()
	{
		return $this->request;
	}
	
	/**
	 * Enter description here...
	 *
	 * @return Enlight_Controller_Response_Response
	 */
	public function Response()
	{
		return $this->response;
	}
	
	public function __call($name, $value=null)
    {
        if ('Action' == substr($methodName, -6)) {
            $action = substr($methodName, 0, strlen($methodName) - 6);
            throw new Enlight_Controller_Exception('Action "'.$this->controller_name.'_'.$name.'" not found failure', Enlight_Controller_Exception::ActionNotFound);
        }
        return parent::__call($name, $value);
    }
}