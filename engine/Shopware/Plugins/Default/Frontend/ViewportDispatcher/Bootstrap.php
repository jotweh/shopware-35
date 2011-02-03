<?php
class Shopware_Plugins_Frontend_ViewportDispatcher_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
	public function install()
	{
		$event = $this->createEvent(
	 		'Enlight_Controller_Front_PreDispatch',
	 		'onPreDispatch',
	 		50
	 	);
		$this->subscribeEvent($event);
		
		return true;
	}
	
	public function init()
	{
		$this->config = Shopware()->Config();
	}
	
	protected $request;
	protected $action;
	protected $view;
	protected $config;
	
	public function onFilterAssemble(Enlight_Event_EventArgs $args)
	{
		$request = $args->getRequest();
		$params = $args->getReturn();
		
		if(empty($this->config['sTEMPLATEOLD'])) return $params;
		
		if(empty($params[$request->getControllerKey()])||$params[$request->getControllerKey()]=='viewport'||$params[$request->getControllerKey()]=='index')
		{
			$params[$request->getControllerKey()] = 'index';
			unset($params['title']);
		}
		
		return $params;
	}
	
	public static function onPreDispatch(Enlight_Event_EventArgs $args)
	{		
		$request = $args->getSubject()->Request();
		$response = $args->getSubject()->Response();
						
		if($request->getModuleName()&&$request->getModuleName()!='frontend'){
			return;
		}
		
		$plugin = Shopware()->Plugins()->Frontend()->ViewportDispatcher();
						
		$event = new Enlight_Event_EventHandler(
	 		'Enlight_Controller_Action_PostDispatch',
	 		array($plugin, 'onPostDispatchAction'),
	 		50
	 	);
		Shopware()->Events()->registerListener($event);
		
		$event = new Enlight_Event_EventHandler(
	 		'Enlight_Controller_Action_PostDispatch',
	 		array($plugin, 'onPostDispatchActionRender'),
	 		150
	 	);
		Shopware()->Events()->registerListener($event);
		
		$event = new Enlight_Event_EventHandler(
	 		'Enlight_Controller_Router_FilterAssembleParams',
	 		array($plugin, 'onFilterAssemble')
	 	);
		Shopware()->Events()->registerListener($event);
		
		$event = new Enlight_Event_EventHandler(
	 		'Enlight_Controller_Action_PostDispatch',
	 		array($plugin, 'onPostDispatch'),
	 		110
	 	);
		Shopware()->Events()->registerListener($event);
		
		$event = new Enlight_Event_EventHandler(
	 		'Enlight_Controller_Action_PostDispatch',
	 		array($plugin, 'onPostDispatchHome'),
	 		200
	 	);
		Shopware()->Events()->registerListener($event);
		
		//Shopware()->System()->_GET = $request->getQuery();
		//Shopware()->System()->_POST = $request->getPost();
		
		if($plugin->isDispatchable($request)) {
			$request->setParam('sViewport', $plugin->getViewportName($request));
			$request->setControllerName('viewport');
			$request->setActionName('index');
		}
	}
	
	public function onPostDispatchAction(Enlight_Event_EventArgs $args)
	{
		if($args->getSubject()->Request()->getControllerName()!='viewport') return;
		if($args->getSubject()->Response()->isException()) return;

		$this->request = $args->getSubject()->Request();
		$this->action = $args->getSubject();
		$this->view = $args->getSubject()->View();
				
		$this->dispatch();
	}
	
	public function onPostDispatchActionRender(Enlight_Event_EventArgs $args)
	{
		if($args->getSubject()->Request()->getControllerName()!='viewport') return;
		if($args->getSubject()->Response()->isException()) return;

		$this->request = $args->getSubject()->Request();
		$this->action = $args->getSubject();
		$this->view = $args->getSubject()->View();
		
		$this->render();
	}
	
	public function onPostDispatchHome(Enlight_Event_EventArgs $args)
	{
		$request = $args->getSubject()->Request();
		
		if($request->getModuleName()&&$request->getModuleName()!='frontend'){
			return;
		}
		
		if(Shopware()->Config()->TemplateOld) {
			if($request->getControllerName()=='index') {
				$view = $args->getSubject()->View();
				$render = Shopware()->Modules()->Core()->sStart();
				if(empty($render)) {
					$render = array('templates'=>array(), 'variables'=>array());;
				}
				$variables = $view->getAssign();
				if(!empty($variables)) {
					$render['variables'] = array_merge($variables, $render['variables']);
				}
				$render = Shopware()->Modules()->Core()->sCustomRenderer($render, '', '');
				$view->assign($render['variables']);
				$view->loadTemplate('index/index.tpl');
				$view->assign('sContainer', $view->fetch('index/index_home.tpl'));
			} elseif($request->getControllerName()=='viewport' && $request->getParam('sViewport')=='logout') {
				$args->getSubject()->forward('index', 'index');
			}
		}
	}
	
	public function onPostDispatch(Enlight_Event_EventArgs $args)
	{				
		$view = $args->getSubject()->View();
		$request = $args->getSubject()->Request();
		
		if(!$request->isDispatched()||$request->getModuleName()!='frontend') return;
						
		if(!empty(Shopware()->Config()->TemplateOld))
		{
			Shopware()->System()->_GET['sCategory'] = $view->sCategoryCurrent;
			
			$view->sMainCategories = Shopware()->Modules()->Categories()->sGetMainCategories();
			$view->sConfig = Shopware()->Config();
			$view->sSubShop = Shopware()->System()->sSubShop;
			
			$view->_POST = Shopware()->System()->_POST;
			$view->_GET = Shopware()->System()->_GET;
			$view->_SERVER = $_SERVER;
			$view->sViewport = $_SERVER['PHP_SELF'];
			$view->sBasefile = Shopware()->Config()->BaseFile;
			$view->sStart = 'http://'.Shopware()->Config()->BasePath.'/'.Shopware()->Config()->BaseFile;

			if(empty($view->sBasket))
			{
				$view->sBasket = array('Quantity' => $view->sBasketQuantity);
			}
			if (empty($_COOKIE))
			{
				$view->sCoreId = Shopware()->SessionID();
			}
			
			if (!empty(Shopware()->Session()->sUserId))
			{
				$username = Shopware()->Modules()->Admin()->sGetUserNameById(Shopware()->Session()->sUserId);
				$view->sUserName = $username['firstname'].' '.$username['lastname'];
				$view->sUserMail = Shopware()->Session()->sUserMail;
				if (Shopware()->System()->sUSERGROUPDATA['groupkey']!='EK')
				{
					$view->sUserGroupText = Shopware()->System()->sUSERGROUPDATA['description'];
				}
			}
		}
		elseif ($request->getControllerName()=='viewport')
		{
			$view->_GET = Shopware()->System()->_GET;
			$view->_POST = Shopware()->System()->_POST;
		}
	}
	
	public function getViewportName($request)
	{
		$viewport = null;
		if($request->getPost('sViewport')) {
			$viewport = $request->getPost('sViewport');
		} elseif($request->getControllerName()&&$request->getControllerName()!='viewport') {
			$viewport = $request->getControllerName();
		} elseif($request->getParam('sViewport')) {
			$viewport = $request->getParam('sViewport');
		} elseif($request->getControllerName()=='sViewport,ajax') {
			$viewport = 'ajax';
		} elseif ($request->getQuery('sCaptcha')) {
			$viewport = 'captcha';
		} elseif (!$request->getControllerName()&&$request->getActionName()=='register1') {
			$viewport = 'registerFC';
		}
		return $viewport;
	}
	
	public function getTemplateName($request)
	{
		$viewport = $request->getQuery('sViewport');
		$action = 'index';
		
		switch ($viewport)
		{
			case 'admin':
				if($request->getQuery('sAction'))
				switch ($request->getQuery('sAction'))
				{
					case 'billing':
					case 'shipping':
					case 'payment':
					case 'orders':
					case 'downloads':
						$action = $request->getQuery('sAction');
						break;
				}
				$viewport = 'account';
				break;
			case 'password':
			case 'login':
			case 'logout':
				$action = $viewport;
				$viewport = 'account';
				break;
			case 'registerFC':
			case 'register1':
			case 'register2':
				$viewport = 'register';
				break;
			case 'register2shipping':
				$action = 'shipping';
				$viewport = 'register';
				break;
			case 'register3':
				$action = 'payment';
				$viewport = 'register';
				break;
			case 'cat':		
				$blog = Shopware()->Db()->fetchOne(
					'SELECT blog FROM s_categories WHERE id=?',
					array($request->getQuery('sCategory'))
				);
				if(!empty($blog))
				{
					$viewport = 'blog';
				}
				else
				{
					$viewport = 'listing';
				}
				if($request->getQuery('sRss'))
				{
					header('Content-Type:text/xml; charset=ISO-8859-1');
					$action = 'rss';
				}
				elseif($request->getQuery('sAtom'))
				{
					header('Content-Type:text/xml; charset=ISO-8859-1');
					$action = 'atom';
				}
				break;
			case 'detail':		
				$blog = Shopware()->Db()->fetchOne(
					'SELECT mode FROM s_articles WHERE id=?',
					array($request->getQuery('sArticle'))
				);
				if(!empty($blog))
				{
					$viewport = 'blog';
					$action = 'detail';
				}
				break;
			case 'rma':
			case 'ticket':
			case 'support':
				if($request->getQuery('sAction'))
				{
					$action = $request->getQuery('sAction');
				}
				$viewport = 'forms';
				break;
			case 'newsletter':
			case 'tellafriend':
				$action = $viewport;
				$viewport = 'forms';
				break;
			case 'ticketdirect':
				$viewport = 'ticket';
				$action = 'detail';
				break;
			case 'ticketview':
				$viewport = 'ticket';
				if($request->getQuery('sAction'))
				{
					$action = $request->getQuery('sAction');
				}
				break;
			case 'searchFuzzy':
				$viewport = 'search';
				if (Shopware()->License()->checkLicense('sFUZZY')) {
					$action = 'fuzzy';
				}
				break;
			case 'search':
				if($request->getQuery('sSearchMode'))
				switch ($request->getQuery('sSearchMode'))
				{
					case 'supplier':
					case 'newest':
					case 'topseller':
						$action = $request->getQuery('sSearchMode');
						break;
				}
				$viewport = 'search';
				break;
			case 'sale':
				$viewport = 'sale';
				if($request->getQuery('sAction'))
				{
					$action = 'finished';
				}
				break;
			case 'content':
				if($request->getQuery('sCid'))
				{
					$action = 'detail';
				}
				break;
			case 'newsletterListing':
				$viewport = 'newsletter_listing';
				if($request->getQuery('sID'))
				{
					$action = 'detail';
				}
				break;
			default:
				if($request->getParam('sAction'))
				{
					$action = preg_replace('/[A-Z]/e', "'_'.strtolower('\\0')", $request->getParam('sAction'));
				}
				break;
		}
		return 'frontend/'.$viewport.'/'.$action.'.tpl';
	}
	
	public function isDispatchable($request)
    {    	
    	if(empty($this->config['sTEMPLATEOLD'])&&Shopware()->Front()->Dispatcher()->isDispatchable($request))
		{
			return false;
		}
		
		$viewport_name = $this->getViewportName($request);
		
		if(!$viewport_name) return false;
		
    	$custom_class = 'sCustomViewport'.ucfirst($viewport_name);
		$class = 'sViewport'.ucfirst($viewport_name);
				
		if(Enlight()->Loader()->isReadable(Shopware()->OldPath().'engine/custom/viewports/'.$custom_class.'.php'))
			return true;
		if(!empty($this->config['sViewports'][$viewport_name]['file']))
			return true;
		elseif(Enlight()->Loader()->isReadable(Shopware()->OldPath().'engine/core/class/viewports/s_'.$viewport_name.'.php'))
			return true;
			
		return false;
    }
	
	public function initViewport($viewport_name)
	{
		$custom_class = 'sCustomViewport'.ucfirst($viewport_name);
		$class = 'sViewport'.ucfirst($viewport_name);
		
		if(!empty($this->config['sViewports'][$viewport_name]['file']))
			require_once(Shopware()->OldPath().'engine/core/class/viewports/'.$this->config['sViewports'][$viewport_name]['file']);
		elseif(file_exists(Shopware()->OldPath().'engine/core/class/viewports/s_'.$viewport_name.'.php'))
			require_once(Shopware()->OldPath().'engine/core/class/viewports/s_'.$viewport_name.'.php');
		if(file_exists(Shopware()->OldPath().'engine/local_old/viewports/'.$custom_class.'.php'))
			require_once(Shopware()->OldPath().'engine/local_old/viewports/'.$custom_class.'.php');

		if(class_exists($custom_class))
		{
			$viewport = new $custom_class(Shopware()->System());
		}
		elseif(class_exists($class))
		{
			$viewport = new $class(Shopware()->System(), $t=null);
		}
		else
		{
			throw new Enlight_Exception('Viewport class "'.$class.'" not found');
		}
		
		if(!method_exists($viewport, 'sRender'))
		{
			throw new Enlight_Exception('Viewport method "sRender" in viewport "'.$viewport.'" not found');
		}
		
		$viewport->sSYSTEM = Shopware()->System();
		return $viewport;
	}
	
	public function dispatch()
	{
		$viewport_name = $this->getViewportName($this->request);
		if(!$viewport_name) return false;
				
		$viewport = $this->initViewport($viewport_name);
		if(!$viewport) return false;
		
		$render = $viewport->sRender();
				
		if(!empty($this->config['sTEMPLATEOLD'])) {
			$this->view->loadTemplate('index/index.tpl');
		} else {
			$this->view->loadTemplate($this->getTemplateName($this->request));
		}
		
		$render = Shopware()->Modules()->Core()->sCustomRenderer($render, '', '');
			
		if(!empty($render['variables'])) {
			$this->view->assign($render['variables']);
		}
		if(!empty($render['templates'])) {
			$this->view->sTemplates = $render['templates'];
		}
		
		$this->afterRender();
	}
	
	public function render()
	{
		if(!empty($this->config['sTEMPLATEOLD'])&&!empty($this->view->sTemplates))
		foreach ($this->view->sTemplates as $variableName => $templatePath)
		{
			if(empty($variableName)||empty($templatePath)) continue;
	
			$templatePath = ltrim($templatePath, '/');
			if($this->view->templateExists($templatePath))
			{
				$this->view->assign($variableName, $this->view->fetch($templatePath));
			}
		}
	}
	
	public function afterRender()
	{
		if(Shopware()->System()->_GET['sViewport']=='login'&&Shopware()->System()->_GET['sViewport']!='login')
		{
			Shopware()->System()->_POST['sAction'] = $_POST['sAction'] = 'login';
		}
		if(Shopware()->System()->_GET['sViewport']=='login'&&!empty(Shopware()->System()->_POST['sTarget']))
		{
			Shopware()->System()->_GET['sTarget'] = Shopware()->System()->_POST['sTarget'];
		}
		// Registrierung Patch
		if(Shopware()->System()->_GET['sViewport']=='sale'&&!empty(Shopware()->System()->_POST['sAction'])&&Shopware()->System()->_POST['sAction']=='doSale')
		{
			Shopware()->System()->_GET['sAction'] = Shopware()->System()->_POST['sAction'];
		}
		// Admin Patch
		if(Shopware()->System()->_GET['sViewport']=='admin'&&!empty(Shopware()->System()->_POST['sAction'])&&Shopware()->System()->_POST['sAction']!='login')
		{
			Shopware()->System()->_GET['sAction'] = Shopware()->System()->_POST['sAction'];
		}
		// Bestellung/Registrierung/Login Patch
		if((Shopware()->System()->_GET['sViewport']!=$_GET['sViewport'] || !empty($_POST['sAction']))
			&& empty($this->view->sErrorMessages)&&empty($this->view->sAGBError)&&empty($this->view->sVoucherError)
			&& !empty(Shopware()->System()->_GET['sViewport'])
			&& in_array(Shopware()->System()->_GET['sViewport'], array('registerFC', 'register2shipping', 'register3', 'admin', 'login', 'sale')))
		{
			unset(Shopware()->System()->_GET['sCategory']);
			if(Shopware()->System()->_GET['sViewport']=='admin')
			{
				unset(Shopware()->System()->_GET['sAction']);
			}
			Shopware()->System()->_GET['sUseSSL'] = 1;
			$new_path = Shopware()->Router()->assemble(Shopware()->System()->_GET);
			if(!empty($new_path))
			{
				header('Location: '.$new_path);
				exit;
			}
		}
		// Bestellabschluss Patch
		if(Shopware()->System()->_GET['sViewport']=='sale'&&!empty(Shopware()->System()->_SESSION['sOrderVariables']['ordernumber'])&&empty($this->view->sOrderNumber))
		{
			$this->view->sOrderNumber = Shopware()->System()->_SESSION['sOrderVariables']['ordernumber'];
			$this->view->sBasket = "";
			$this->view->sBasketQuantity = 0; 
		}
		// Login Patch
		if(Shopware()->System()->_GET['sViewport']=='login'&&!empty(Shopware()->System()->_GET['sTarget']))
		{
			/*$sRender['variables']['_POST']['sTarget'] = */
			Shopware()->System()->_POST['sTarget'] = $_POST['sTarget'] =  Shopware()->System()->_GET['sTarget'];
		}
	}
}