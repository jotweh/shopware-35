<?php
/**
 * Shopware Backend Controller
 * Display backend / administration 
 *
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Heiner Lohaus
 * @author Stefan Hamann
 * @package Shopware
 * @subpackage Controllers
 */
class Shopware_Controllers_Backend_Index extends Enlight_Controller_Action
{

	/**
	 * On index - get all Resources that we need in backend area
	 * Backend Menu
	 * Licence Information
	 * Rss-Data for example
	 */
	public function indexAction()
	{
		$this->View()->Menu = Shopware()->Menu();
		$this->View()->PremiumLicence = Shopware()->License()->checkLicense('sPREMIUM');
		if(Shopware()->License()->checkLicense('sTICKET') && !empty(Shopware()->Config()->sTICKETSIDEBAR)) {
			$this->View()->TicketSystemActive = true;
		} else {
			$this->View()->TicketSystemActive = false;
		}
		
		if(!Shopware()->License()->checkLicense('sCORE') && !Shopware()->License()->checkLicense('sCOMMUNITY')) {
			$this->View()->ShowActivate = true;
		} else {
			$this->View()->ShowActivate = false;
		}
		
		if(Shopware()->License()->checkLicense('sCORE')){
			$this->View()->Logo = 'logo';
		} else {
			$this->View()->Logo = 'logoCE';
		}
		
		$this->View()->BaseUrl = $this->Request()->getBaseUrl();
		$this->View()->BasePath = $this->Request()->getBasePath();
		
		$this->View()->rssData = $this->getRssFeed();
		$this->View()->Scheme = $this->Request()->getScheme();
		$this->View()->BackendUsers = implode(',', $this->getUsers());
		$this->View()->Amount = $this->getAmount();
		$identity = Shopware()->Auth()->getIdentity();
		$this->View()->SidebarActive = $identity->sidebar;
		$this->View()->UserName = $identity->name;
		$this->View()->accountUrl = 'https://account.shopware.de/register.php'
			. '?domain=' .urlencode(Shopware()->Config()->Host)
			. '&pairing=' .urlencode(Shopware()->Config()->AccountId);
	}

	/**
	 * On logout destroy session and redirect to auth controller
	 */
	public function logoutAction()
	{
		Shopware()->Plugins()->Controller()->ViewRenderer()->setNoRender();	
		Zend_Session::destroy(true);
		return $this->redirect('backend/auth');
	}
	
	/**
	 * Get all backend users as an array
	 *
	 * @return array
	 */
	protected function getUsers()
	{
		$getUsers = Shopware()->Db()->fetchAll('SELECT id, username FROM s_core_auth ORDER BY username ASC');
		$users = array();
		foreach ($getUsers as $user){
			$users[] = Zend_Json::encode(array($user['id'], utf8_encode($user['username'])));
		}
		return $users;
	}
	
	/**
	 * Get shopware rss feed to display in sidebar
	 *
	 * @return array
	 */
	protected function getRssFeed()
	{
    	$data = array();
    	try {
			Zend_Feed::setHttpClient(
				new Zend_Http_Client(
					null,
					array(
					'timeout' => 3 // seconds
					)
				)
			);
	    	$channel = new Zend_Feed_Rss('http://www.shopware.de/rss.xml');
	    	foreach ($channel as $i => $item) {
	    		$title = (string) $item->title;
	    		if(function_exists('mb_convert_encoding')) {
	    			$title = mb_convert_encoding($title, 'HTML-ENTITIES', 'UTF-8');
	    		}
	    		$data[] = array($i+1, $title, (string) $item->link);
				if ($i>3) {
					break;
				}
	    	}
    	} catch (Exception $e) { }
	    return Zend_Json::encode($data);;
	}
	
	/**
	 * Get current amount from shopware account if configured
	 *
	 * @return float
	 */
	protected function getAmount()
	{
		if (empty(Shopware()->Config()->Host)
		  || empty(Shopware()->Config()->AccountId)){
			return false;
		}
		
		$url = 'https://account.shopware.de/core/credit.php';

		$client = new Zend_Http_Client($url, array(
			'useragent' => 'Shopware/' . Shopware()->Config()->Version,
			'timeout' => 5,
		));
		$client->setParameterPost(array(
			'domain' => Shopware()->Config()->Host,
			'pairing' => Shopware()->Config()->AccountId,
			'server_ip' => getenv('SERVER_ADDR')
		));
		$client->setHeaders('Referer', 
			$this->Request()->getScheme() . '://'
			. $this->Request()->getHttpHost()
			. $this->Request()->getBasePath()
		);
		if (extension_loaded('curl')) {
			$adapter = new Zend_Http_Client_Adapter_Curl();
			$adapter->setCurlOption(CURLOPT_SSL_VERIFYPEER, false);
			$adapter->setCurlOption(CURLOPT_SSL_VERIFYHOST, false);
			$client->setAdapter($adapter);
		}
		
		try {
			$response = $client->request('POST');
		} catch (Exception $e) {
			return false;
		}
		
		return $response->getBody();
	}
}