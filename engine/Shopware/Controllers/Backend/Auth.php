<?php
class Shopware_Controllers_Backend_Auth extends Enlight_Controller_Action
{	
	public function init()
	{
		Shopware()->Plugins()->Backend()->Auth()->setNoAuth();
	}
	
	public function preDispatch()
	{
		if(!in_array($this->Request()->getActionName(), array('index'))) {
			Shopware()->Plugins()->Controller()->ViewRenderer()->setNoRender();	
		}
	}
	
	public function indexAction()
	{
		$host = trim(Shopware()->Config()->Host);
		if(!empty($host) && $this->Request()->getHttpHost() != $host) {
			$redirect = 'http://'.trim(Shopware()->Config()->sBASEPATH).'/backend/auth';
			if($this->existsUrl($redirect.'?test=1') && empty($this->Request()->redirect)) {
				return $this->redirect($redirect.'?redirect=1');
			}
		}
		
		if (strpos($this->Request()->getHeader('USER_AGENT'), 'MSIE') !== false){
			$this->View()->BrowserError = true;
		} else {
			$this->View()->BrowserError = false;
		}
	}
	
	public function loginAction()
	{
		Shopware()->Plugins()->Controller()->ViewRenderer()->setNoRender();	
		
		$username = $this->Request()->get('username');
		$password = $this->Request()->get('password');
		$locale = $this->Request()->get('locale');
		
		if (!empty($username) && !empty($password)) {
			$checkLogin = Shopware()->Plugins()->Backend()->Auth()->login($username, $password, $locale);
		}
		
		echo Zend_Json::encode(array('success'=>!empty($checkLogin)));
	}
	
	public function existsUrl($url, $timeout=0.5)
	{
		$url = parse_url($url);
	
		$url['path'] = empty($url['path']) ? '/' : $url['path'];
		if(!empty($url['query'])) {
			$url['path'] .= '?'.$url['query'];
		}
		$url['port'] = (!empty($url['scheme'])&&$url['scheme']=='https') ? 443 : 80;
		$url['hostname'] = ($url['port']==443) ? 'ssl://'.$url['host'] : $url['host'];
	
		$fp = fsockopen($url['hostname'], $url['port'], $errno, $errstr, $timeout);
		$out = "GET {$url["path"]} HTTP/1.1\r\n";
		$out .= "Host: {$url["host"]}\r\n";
		$out .= "Connection: Close\r\n\r\n";
		fwrite($fp, $out);
		$content = stream_get_line($fp, 65535, "\r\n");
		fclose($fp);
		return preg_match('#^HTTP\/\d+\.\d+\s+[2]\d\d\s+.*$#',$content);
	}
}