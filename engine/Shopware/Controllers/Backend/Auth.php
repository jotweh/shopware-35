<?php
class Shopware_Controllers_Backend_Auth extends Enlight_Controller_Action
{	
	public function init()
	{
		Shopware()->Plugins()->Backend()->Auth()->setNoAuth();
	}
	
	public function indexAction()
	{
		if (preg_match("/MSIE/i",$_SERVER['HTTP_USER_AGENT'])){
			$this->View()->BrowserError = true;
		}else {
			$this->View()->BrowserError = false;
		}
		
		if($_SERVER["HTTP_HOST"]!=trim(Shopware()->Config()->sHOST))
		{
			$redirect = 'http://'.trim(Shopware()->Config()->sBASEPATH).'/backend/auth';
			if($this->url_exists($redirect.'?test=1')&&empty($this->Request()->redirect))
			{
				return $this->redirect($redirect.'?redirect=1');
			}
		}
	}
	
	public function loginAction()
	{
		Shopware()->Plugins()->Controller()->ViewRenderer()->setNoRender();	
		
		$username = $this->Request()->getPost("username");
		$password = $this->Request()->getPost("password");
		
		if (!empty($username) && !empty($password)) {
			$checkLogin = Shopware()->Plugins()->Backend()->Auth()->login($username, $password);
			if ($checkLogin){
				echo json_encode(array("success"=>true, "location"=>$this->Front()->Router()->assemble(array("controller"=>"index"))));
			}else {
				echo json_encode(array("success"=>false));
			}
		} else {
			echo json_encode(array("success"=>false));
		}
	}
	
	public function url_exists($url,$timeout=0.5)
	{
		$url = parse_url($url);
	
		$url["path"] = empty($url["path"]) ? "/" : $url["path"];
		if(!empty($url["query"]))
			$url["path"] .= '?'.$url["query"];
		$url["port"] = (!empty($url["scheme"])&&$url["scheme"]=="https") ? 443 : 80;
		$url["hostname"] = ($url["port"]==443) ? "ssl://".$url["host"] : $url["host"];
	
		$fp = fsockopen($url["hostname"], $url["port"], $errno, $errstr, $timeout);
		$out = "GET {$url["path"]} HTTP/1.1\r\n";
		$out .= "Host: {$url["host"]}\r\n";
		$out .= "Connection: Close\r\n\r\n";
		fwrite($fp, $out);
		$content = stream_get_line($fp,65535,"\r\n");
		fclose($fp);
		return preg_match('#^HTTP\/\d+\.\d+\s+[2]\d\d\s+.*$#',$content);
	}
}