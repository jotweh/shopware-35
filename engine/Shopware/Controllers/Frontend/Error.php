<?php
class Shopware_Controllers_Frontend_Error extends Enlight_Controller_Action
{	
	public function indexAction()
	{
		
	}
	
	public function ajaxAction()
	{
		
	}
	
	public function cliAction()
	{
		$this->view->setTemplate();
		
		$response = new Enlight_Controller_Response_ResponseCli();
		$response->appendBody(strip_tags($this->view->exception)."\n");
		
		$this->front->setResponse($response);
	}
	
	public function errorAction()
	{		
		$this->Response()->setHeader('Content-Type', 'text/html; charset=ISO-8859-1');
		
		$error = $this->Request()->getParam('error_handler');
		
		if(!empty($error))
		{
			switch ($error->type) {
	            case 404:
	                $this->Response()->setHttpResponseCode(404);
	                break;
	            default:
	            	$this->Response()->setHttpResponseCode(503);
	                break;
	        }
	
			if($this->Front()->getParam('showException')) {
				$error_file = $error->exception->getFile();
				$error_file = str_replace(array(Enlight()->Path(), Enlight()->AppPath(), Enlight()->OldPath()), array('', Enlight()->App().'/', ''), $error_file);
				
				$error_trace = $error->exception->getTraceAsString();
				$error_trace = str_replace(array(Enlight()->Path(), Enlight()->AppPath(), Enlight()->OldPath()), array('', Enlight()->App().'/', ''), $error_trace);
				
				$error->error_file = $error_file;
				$error->error_trace = $error_trace;
				
				$this->View()->assign((array) $error);
			}
		}
		
		if($this->Request()->isXmlHttpRequest()||(Shopware()->Bootstrap()->issetResource('Db') && Shopware()->Config()->TemplateOld)) {
			$this->forward('ajax');
		} elseif(isset($_ENV['SHELL'])||empty($_SERVER['SERVER_NAME']))	{
			$this->forward('cli');
		} else {
			$this->forward('index');
		}
	}
	
	public function serviceAction()
	{
		$this->Response()->setHttpResponseCode(503);
	}
}