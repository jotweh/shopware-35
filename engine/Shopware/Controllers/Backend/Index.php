<?php
/**
 * Shopware Backend Controller
 * 
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Stefan Hamann
 * @package Shopware
 * @subpackage Controllers
 */
class Shopware_Controllers_Backend_Index extends Enlight_Controller_Action
{	
	/**
	 * Index action method
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
			$this->View()->Logo = "logo";
		}else{
			$this->View()->Logo = "logoCE";
		}
		
		$this->View()->BaseUrl = $this->Request()->getBaseUrl();
		$this->View()->BasePath = $this->Request()->getBasePath();
		
		$this->View()->rssData = $this->getRssFeed();
		$this->View()->Scheme = $this->Request()->getScheme();
		$this->View()->BackendUsers = implode(',', $this->getUsers());
		$this->View()->SidebarActive = $_SESSION['sSidebar'];
		$this->View()->Amount = $this->getAmount();
		$this->View()->UserName = $_SESSION['sName'];
		$this->View()->accountUrl = 'https://account.shopware.de/register.php'
			. '?domain=' .urlencode(Shopware()->Config()->Host)
			. '&pairing=' .urlencode(Shopware()->Config()->AccountId);
	}
	
	/**
	 * logout action method
	 */
	public function logoutAction()
	{
		Shopware()->Plugins()->Controller()->ViewRenderer()->setNoRender();	
		Zend_Session::destroy(true);
		return $this->redirect('backend/auth');
	}
	
	/**
	 * Returns backend users
	 *
	 * @return array
	 */
	protected function getUsers()
	{
		$getUsers = Shopware()->Db()->fetchAll('SELECT id, username FROM s_core_auth ORDER BY username ASC');
		$users = array();
		foreach ($getUsers as $user){
			$users[] = json_encode(array($user['id'], utf8_encode($user['username'])));
		}
		return $users;
	}
	
	/**
	 * Returns sidebar rss feed
	 *
	 * @return array
	 */
	protected function getRssFeed()
	{
    	$data = array();
    	try {
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
	 * Returns account amount
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
		$referer = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'];

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