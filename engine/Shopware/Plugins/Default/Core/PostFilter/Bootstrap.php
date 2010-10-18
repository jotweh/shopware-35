<?php
class Shopware_Plugins_Core_PostFilter_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
	public function install()
	{		
		$event = $this->createEvent(
	 		'Enlight_Plugins_ViewRenderer_FilterRender',
	 		'onFilterRender'
	 	);
		$this->subscribeEvent($event);
		return true;
	}

	public $sTemplate;
	public $sConfig;
	public $sRequest;
	public $sBasePathUrl = '';
	public $sBasePath = '';
	public $sMediaPaths = array();
	public $sUseSSL = false;
	public $sNeedSession = false;
	
	public $sBacklinkWhitelist = array();
	public $sViewportBlacklist;
	public $sQueryBlacklist;
	
	public static function onFilterRender(Enlight_Event_EventArgs $args)
	{
		if(!Shopware()->Bootstrap()->issetResource('Shop')){
			return $args->getReturn();
		}
		return Shopware()->Plugins()->Core()->PostFilter()->sFilterSource($args->getReturn());
	}
	
	public function initConfig()
	{
		$this->sTemplate = Shopware()->Template();
		$this->sConfig = Shopware()->Config();
		$this->sRequest = Shopware()->Front()->Request();
		$this->sUseSSL = Shopware()->Front()->Request()->isSecure();
		$this->sNeedSession = $this->sUserNeedSessionID();
		$this->sBasePath = $this->sRequest->getHttpHost().$this->sRequest->getBasePath().'/';
		$this->sBasePathUrl = $this->sRequest->getScheme().'://'.$this->sBasePath;
		
		$shop = Shopware()->Shop();
		
		/*
		$this->sViewportBlacklist = preg_replace('#\s#', '', $this->sConfig['sSEOVIEWPORTBLACKLIST']);
		$this->sViewportBlacklist = explode(',', $this->sViewportBlacklist);
		
		$this->sQueryBlacklist = preg_replace('#\s#', '', $this->sConfig['sSEOQUERYBLACKLIST']);
		$this->sQueryBlacklist = explode(',', $this->sQueryBlacklist);
		*/
		
		$this->sBacklinkWhitelist = preg_replace('#\s#', '', $this->sConfig['sSEOBACKLINKWHITELIST']);
		$this->sBacklinkWhitelist = explode(',', $this->sBacklinkWhitelist);
		
		$this->sBacklinkWhitelist[] = $this->sConfig['sHOST'];
		if(!empty(Shopware()->sSubShops))
		foreach (Shopware()->sSubShops as $subshop)
		{
			$domains = explode("\n", $subshop['domainaliase']);
			$domain = trim(reset($domains));
			if(!empty($domain))
			{
				$this->sBacklinkWhitelist[] = $domain;
			}
		}

		if($this->sConfig->get('sTEMPLATEOLD'))
		{
			$this->sMediaPaths = array(
				$this->sConfig->get('sTEMPLATEPATH').'/'.$shop->get('isocode').'/',
				$this->sConfig->get('sTEMPLATEPATH').'/de/',
				dirname($this->sConfig->get('sTEMPLATEPATH')).'/0/de/',
				'templates/default/',
			);
		}
		else
		{
			$this->sMediaPaths = array(
				$this->sConfig->get('sTEMPLATEPATH').'/',
				'templates/default/',
			);
		}
		$this->sMediaPaths = array_unique($this->sMediaPaths);
	}
	
	public function &sFilterSource($source)
	{
		if (!empty(Shopware()->Config()->UseDefaultTemplates) && !empty(Shopware()->System()->sSubShop['inheritstyles']) && Shopware()->System()->sLanguageData[Shopware()->System()->sLanguage]['isocode']!='de') {
			$source = preg_replace_callback('/\<link.+rel="stylesheet".+\>/U',array($this,'sDuplicateStyle'),$source);
		}
		
		// Rewrite path for <link href - CSS-Styles
		$source = preg_replace_callback('#<(link|img|script|input|a|form|iframe)[^<>]*(href|src|action)="([^"]*)".*>#Umsi', array($this,'sRewriteSrc'), $source);
		$source = preg_replace_callback('#((style))="[^"]*(url\([^\)]+\))[^"]*"#', array($this,'sRewriteStyle'), $source);
				
		// User defined, runtime rewriterules
		$sql = 'SELECT search, `replace` FROM s_core_rewrite ORDER BY id ASC';
		$replaceRules = Shopware()->Db()->fetchPairs($sql);
		if(!empty($replaceRules))
		{
			$source = preg_replace(array_keys($replaceRules),array_values($replaceRules),$source);
		}
		// If cookies disabled, add sessionid to all form-handlers
		if (Shopware()->SessionID() && empty(Shopware()->System()->_COOKIE))
		{
		    $source = preg_replace('/<form ([^>]+)>/U','<form \\1><input type="hidden" name="sCoreId" value="'.Shopware()->SessionID().'" />',$source); 
		}
		
		// Fuzzy search patch
		if(!empty(Shopware()->System()->_GET['sViewport'])&&Shopware()->System()->_GET['sViewport']=='searchFuzzy')
		{
			$replace = '';
			foreach (Shopware()->System()->_GET as $key => $value)
			{
				if(in_array($key, array('sSort', 'sCategory', 'sViewport'))) continue;
				$replace .= '<input type="hidden" name="'.htmlspecialchars($key).'" value="'.htmlspecialchars($value).'">';
			}
			$source = preg_replace('#(<form name="frmsort" [^>]+>)#U','\\1'.$replace, $source);
		}
		return $source; 
	}
	
	public function sDuplicateStyle($arr)
	{
		$link = $arr[0];
		$link .= str_replace('href="../../','href="../../../de/',$arr[0]);
		return $link;
	}
	
	public function sRewriteSrc($src)
	{
		if(!$this->sConfig) $this->initConfig();
				
		if(!empty($this->sBacklinkWhitelist)) {
			if($src[1]=='a' && preg_match('#^https?://#', $src[3]))	{
				$host = @parse_url($src[3], PHP_URL_HOST);
				if(!strstr($src[0], 'rel=')&&!in_array($host, $this->sBacklinkWhitelist))
				{
					$src[0] = rtrim($src[0], '>').' rel="nofollow">';
				}
			}
		}
		if(empty($src[3])) {
			return $src[0];
		}
		$link = $src[3];
		switch ($src[1])
		{
			case 'td':
			case 'input':
			case 'img':
			case 'link':
			case 'script':
				if(strpos($src[3],'../../')===0)
				{
					$file = substr($src[3], 6);
					$file = str_replace('get.php?file=', '', $file);
					$query = strstr($file, '?');
					$file = parse_url($file, PHP_URL_PATH);
					foreach ($this->sMediaPaths as $testpath)
					if(file_exists(Shopware()->OldPath().$testpath.$file))
					{
						$link = $this->sBasePathUrl.$testpath.$file;
						if(!empty($query))
							$link .= $query;
						break;
					}
				}
				elseif(strpos($src[3],$this->sConfig['sBASEFILE'])===0)
				{
					if(preg_match('#title="([^"]*)"#', $src[0], $match))
						$title = $match[1];
					else
						$title = '';
					
					$link = $this->sRewriteLink($src[3], $title);
				}
				break;
			case 'form':
			case 'a':	
				if(strpos($src[3],$this->sConfig['sBASEFILE'])===0) {
					$query = parse_url($src[3], PHP_URL_QUERY);
					parse_str($query, $query);
					
					if(preg_match('#title="([^"]*)"#', $src[0], $match)) {
						$query['title'] = $title;
					}
					
					$link = Shopware()->Front()->Router()->assemble($query);
					
					/*if($src[1]=='a') {
						if(!empty($this->sQueryBlacklist)||!empty($this->sViewportBlacklist)) {
							if(!empty($query['sViewport']))
							if(!strstr($src[0], 'rel='))
							if(in_array($query['sViewport'], $this->sViewportBlacklist)
								||array_intersect($this->sQueryBlacklist, array_keys($query)))
							{
								$src[0] = rtrim($src[0], '>').' rel="nofollow">';
							}
						}
					}*/
				}
				
				if ($this->sNeedSession
					&& preg_match('#^https?://'.preg_quote($this->sBasePath).'#i', $link)
					&& strpos($link, 'sCoreId=')===false)
				{
					$link .= strpos($link, '?')===false ? '?' : '&';
					$link .= 'sCoreId='.Shopware()->SessionID();
				}
				
				break;
			case 'iframe':
				// Bugfix for external payment means
				if(preg_match('#^[./]+engine/connectors/#', $src[3])) {
					$link = $this->sBasePathUrl.preg_replace('#^[./]+#', '', $src[3]);
				}
				break;
			default:
				break;
		}
		if(strpos($link, 'www.')===0) {
			$link = 'http://'.$link;
		}
		if (!preg_match('#^[a-z]+:|^\#|^/#', $link)) {
			$link = $this->sBasePathUrl.$link;
		}
		if($this->sUseSSL && $src[1]!='a') {
			$link = str_replace('http://'.$this->sBasePath, 'https://'.$this->sBasePath, $link);
		}
		$src[0] = str_replace($src[2].'="'.$src[3].'"', $src[2].'="'.$link.'"', $src[0]);
		return $src[0];
	}
	
	public function sUserSupportCookies()
	{
		if($this->sUserIsBot()) {
			return true;
		} elseif(empty($_COOKIE)) {
			return false;
		} else {
			return true;
		}
	}
	
	public function sUserIsBot()
	{
		static $result;
		if(isset($result))
		{
			return $result;
		}
		$result = false;
		$useragent = preg_replace('/[^a-z]/', '', strtolower($_SERVER['HTTP_USER_AGENT']));
		$bots = preg_replace('/[^a-z;]/', '', strtolower(Shopware()->Config()->BotBlackList));
		$bots = explode(';',$bots);
		if(!empty($useragent) && str_replace($bots, '', $useragent)!=$useragent)
		{
			$result = true;
		}
		return $result;
	}
	
	public function sUserNeedSessionID()
	{
		return Shopware()->SessionID() && !$this->sUserSupportCookies() && empty(Shopware()->Config()->DontAttachSession);
	}
	
	public function sRewriteStyle($style)
	{
		if(preg_match('#url\((.+)\)#', $style[3], $src))
		{
			if(strpos($src[1],'../../')===0)
			{
				$file = substr($src[1], 6);
				foreach ($this->sMediaPaths as $testpath)
				if(file_exists(Shopware()->OldPath().$testpath.$file))
				{
					$link = $this->sBasePathUrl.$testpath.$file;
					break;
				}
			}
			elseif(!preg_match('#^[a-z]+://|^\#|^/#', $src[1]))
			{
				$link = $this->sBasePathUrl.$src[1];
			}
			if(!empty($link))
			{
				$style[0] = str_replace($src[1], $link, $style[0]);
			}
		}
		return $style[0];
	}
	
	public function sRewriteLink($link=null, $title=null)
	{
		$url = str_replace(',', '=', $link);
		$url = html_entity_decode($url);
		$query = parse_url($url, PHP_URL_QUERY);
		parse_str($query, $query);
		
		if(!empty($title))
		{
			$query['title'] = $title;
		}
		
		return Shopware()->Front()->Router()->assemble($query);
	}
	
	public function getCapabilities()
    {
        return array(
    		'install' => true,
    		'update' => true
    	);
    }
}