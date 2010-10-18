<?php
class Shopware_Controllers_Backend_Index extends Enlight_Controller_Action
{	
	public function indexAction()
	{
		//$menu = $this->_getMenu();
		
		Shopware()->Plugins()->Core()->License()->checkLicense('sPREMIUM');
		
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
		
		$this->View()->rssData = $this->_getRssFeed();
		$this->View()->Scheme = $this->Request()->getScheme();
		$this->View()->BackendUsers = implode(',',$this->_getUsers());
		$this->View()->SidebarActive = $_SESSION['sSidebar'];
		$this->View()->Amount = $this->_getAmount();
		$this->View()->UserName = $_SESSION['sName'];
		$connectString = "?domain=".Shopware()->Config()->Host."&pairing=".Shopware()->Config()->AccountId;
		$this->View()->accountUrl = "https://support.shopware2.de/account2/index.php$connectString";
	}
	
	public function logoutAction()
	{
		Shopware()->Plugins()->Controller()->ViewRenderer()->setNoRender();	
		Zend_Session::destroy(true);
		return $this->redirect('backend/auth');
	}
	
	protected function _getUsers()
	{
		$getUsers = Shopware()->Db()->fetchAll('SELECT id, username FROM s_core_auth ORDER BY username ASC');
		$users = array();
		foreach ($getUsers as $user){
			$users[] = json_encode(array($user['id'], utf8_encode($user['username'])));
		}
		return $users;
	}
	
	protected function _getRssFeed()
	{
    	$channel = new Zend_Feed_Rss('http://www.shopware-ag.de/rss.xml');
    	$jsrss = '[';
    	$i = 0;
    	foreach ($channel as $item) {
    		$i++;
    		$title = utf8_decode($item->title());
    		$link = urlencode($item->link);
    		$jsData[] = "[$i,'$title','$link']";
			if ($i>=5) break;
    	}
    	$jsrss .= implode(',',$jsData);
		$jsrss .= '];';
		if ($i==0){
	    	unset($jsrss);
		}
	    return $jsrss;
	}
	
	protected function _getMenu(){
		$sql = '
			SELECT 
				id,	parent, hyperlink, name, onclick, style, class, position, ul_properties
			FROM 
				s_core_menu
			WHERE
				active=1 AND parent = 0
			ORDER BY position ASC 
		';
		$entrys = Shopware()->Db()->fetchAll($sql);
		
		if (!$entrys){
			throw new Enlight_Exception('Could not load backend menu');
		}
		
		$entrys = $this->_getChilds($entrys);
		return $entrys;
	}

	protected function _getChilds($entrys)
	{
		foreach ($entrys as &$entry){
			$getChilds = Shopware()->Db()->fetchAll('
				SELECT 
					id,	parent, hyperlink, name, onclick, style, class, position, ul_properties
				FROM 
					s_core_menu
				WHERE
					active=1 AND parent = ?
				ORDER BY position ASC 
			',array($entry['id']));
			if (!empty($getChilds)){
				$entry['childs'] = $this->_getChilds($getChilds);
			}
		}
		return $entrys;
	}
	
	protected function _getAmount()
	{
		$post = array();
		
		$post['domain'] = Shopware()->Config()->Host;
		$post['pairing'] = Shopware()->Config()->AccountId;
		
		if (empty($post['domain']) && empty($post['pairing'])){
			return false;
		}
		$post['server_ip'] = getenv('SERVER_ADDR');
			
		$post = http_build_query($post,'','&');
		
		$url = 'https://support.shopware2.de/account/info.php';
		$referer = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'];

		$timeout = 5;
		
		if (function_exists('curl_init'))
		{
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_FAILONERROR, 1);
			curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,0);
			curl_setopt($ch, CURLOPT_REFERER, $referer); 
			$response = curl_exec($ch);
		}
		elseif(function_exists('fsockopen') || function_exists('pfsockopen'))
		{
			$url = parse_url($url);
			$scheme = $url['scheme'];
			$host = $url['host'];
			$path = $url['path'];
			if ($scheme == 'https' && function_exists('pfsockopen')){
				$isHTTPS = true;
				$port = 443;
			} else {
				$isHTTPS = false;
				$port = 80;
			}
			if ($isHTTPS){
				$fp = pfsockopen('ssl://'.$host, $port, $errno, $errstr, $timeout);
			} else {
				$fp = fsockopen($host, $port, $errno, $errstr, $timeout);
			}
			$response = '';
			if ($fp)
			{
				fputs($fp, "POST $path HTTP/1.1\r\n");
				fputs($fp, "Host: $host\r\n");
				fputs($fp, "Referer: $referer\r\n");
				fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n");
				fputs($fp, "Content-length: ". strlen($post) ."\r\n");
				fputs($fp, "Connection: close\r\n\r\n");
				fputs($fp, $post);
				while(!feof($fp)) {
					$response .= fgets($fp, 128);
				}
				fclose($fp);
				$response = substr($response, strpos($response, "\r\n\r\n")+4);
			}
		}
		return $response;
	}	
}