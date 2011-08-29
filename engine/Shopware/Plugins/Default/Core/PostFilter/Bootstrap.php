<?php
/**
 * Post Filter Plugin
 *
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Heiner Lohaus
 * @package Shopware
 * @subpackage Plugins
 */
class Shopware_Plugins_Core_PostFilter_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
	/**
	 * Install filter plugin
	 * 
	 * @return bool
	 */
	public function install()
	{		
		$event = $this->createEvent(
	 		'Enlight_Plugins_ViewRenderer_FilterRender',
	 		'onFilterRender'
	 	);
		$this->subscribeEvent($event);
		return true;
	}

	protected $config;
	protected $basePathUrl = '';
	protected $basePath = '';
	protected $mediaPaths;
	protected $useSecure = false;
	protected $appendSession = false;
	protected $backlinkWhitelist = array();
	
	/**
	 * Plugin event method
	 *
	 * @param Enlight_Event_EventArgs $args
	 */
	public static function onFilterRender(Enlight_Event_EventArgs $args)
	{
		if(!Shopware()->Bootstrap()->issetResource('Shop')) {
			return $args->getReturn();
		}
		return Shopware()->Plugins()->Core()->PostFilter()->filterSource($args->getReturn());
	}
	
	/**
	 * Initializes plugin config
	 */
	public function initConfig()
	{
		$this->config = Shopware()->Config();
		$this->useSecure = Shopware()->Front()->Request()->isSecure();
		$this->appendSession = $this->checkAppendSession();
		
		$request = Shopware()->Front()->Request();
		$this->basePath = $request->getHttpHost() . $request->getBasePath().'/';
		$this->basePathUrl = $request->getScheme() . '://' . $this->basePath;
		
		$shop = Shopware()->Shop();
				
		$this->backlinkWhitelist = preg_replace('#\s#', '', $this->config['sSEOBACKLINKWHITELIST']);
		$this->backlinkWhitelist = explode(',', $this->backlinkWhitelist);
		
		if(!empty(Shopware()->System()->sSubShops))
		foreach (Shopware()->System()->sSubShops as $subshop) {
			$domains = explode("\n", $subshop['domainaliase']);
			$domain = trim(reset($domains));
			if(!empty($domain)) {
				$this->backlinkWhitelist[] = $domain;
			}
		}

		if($this->config->templateOld) {
			$this->mediaPaths = array(
				$this->config->templatePath . '/' . $shop->get('isocode') . '/',
				$this->config->templatePath . '/de/',
				dirname($this->config->templatePath) . '/0/de/',
				'templates/default/',
			);
			$this->mediaPaths = array_unique($this->mediaPaths);
		}
	}
	
	/**
	 * Filter html source
	 *
	 * @param string $source
	 * @return string
	 */
	public function &filterSource($source)
	{
		if (!empty(Shopware()->Config()->UseDefaultTemplates) && !empty(Shopware()->System()->sSubShop['inheritstyles']) && Shopware()->System()->sLanguageData[Shopware()->System()->sLanguage]['isocode']!='de') {
			$source = preg_replace_callback('/\<link.+rel="stylesheet".+\>/U', array($this, 'duplicateStyle'), $source);
		}
		
		// Rewrite path for <link href - CSS-Styles
		if(Shopware()->Config()->templateOld) {
			$source = preg_replace_callback('#<(link|img|script|input|a|form|iframe)[^<>]*(href|src|action)="([^"]*)".*>#Umsi', array($this, 'rewriteSrc'), $source);
			$source = preg_replace_callback('#((style))="[^"]*(url\([^\)]+\))[^"]*"#', array($this,'rewriteStyle'), $source);
		} else {
			$source = preg_replace_callback('#<(a|form|iframe|link)[^<>]*(href|src|action)="([^"]*)".*>#Umsi', array($this, 'rewriteSrc'), $source);
		}
				
		// User defined, runtime rewriterules
		$sql = 'SELECT search, `replace` FROM s_core_rewrite ORDER BY id ASC';
		$replaceRules = Shopware()->Db()->fetchPairs($sql);
		if(!empty($replaceRules)) {
			$source = preg_replace(array_keys($replaceRules), array_values($replaceRules),$source);
		}
		
		// If cookies disabled, add sessionid to all form-handlers
		if ($this->checkAppendSession()) {
		    $source = preg_replace('/<form ([^>]+)>/U', '<form \\1><input type="hidden" name="sCoreId" value="'.Shopware()->SessionID().'" />', $source); 
		}
		
		// Fuzzy search patch
		if(!empty(Shopware()->System()->_GET['sViewport']) && Shopware()->System()->_GET['sViewport']=='searchFuzzy') {
			$replace = '';
			foreach (Shopware()->System()->_GET as $key => $value) {
				if(in_array($key, array('sSort', 'sCategory', 'sViewport'))) {
					continue;
				}
				$replace .= '<input type="hidden" name="'.htmlspecialchars($key).'" value="'.htmlspecialchars($value).'">';
			}
			$source = preg_replace('#(<form name="frmsort" [^>]+>)#U', '\\1' . $replace, $source);
		}
		
		return $source; 
	}
	
	/**
	 * Duplicate style sheets
	 *
	 * @param array $arr
	 * @return string
	 */
	public function duplicateStyle($arr)
	{
		$link = $arr[0];
		$link .= str_replace('href="../../', 'href="../../../de/', $arr[0]);
		return $link;
	}
	
	/**
	 * Rewrite source link
	 *
	 * @param array $src
	 * @return string
	 */
	public function rewriteSrc($src)
	{
		if(!$this->config) {
			$this->initConfig();
		}
		
		if(empty($src[3])) {
			return $src[0];
		}
			
		if(!empty($this->backlinkWhitelist)) {
			if($src[1]=='a' && preg_match('#^https?://#', $src[3]))	{
				$host = @parse_url($src[3], PHP_URL_HOST);
				if(!strstr($src[0], 'rel=') && !in_array($host, $this->backlinkWhitelist)) {
					$src[0] = rtrim($src[0], '>').' rel="nofollow">';
				}
			}
		}
		
		$link = $src[3];
		switch ($src[1]) {
			case 'td':
			case 'input':
			case 'img':
			case 'link':
			case 'script':
				if(!empty($this->mediaPaths) && strpos($src[3],'../../')===0) {
					$file = substr($src[3], 6);
					$file = str_replace('get.php?file=', '', $file);
					$query = strstr($file, '?');
					$file = parse_url($file, PHP_URL_PATH);
					foreach ($this->mediaPaths as $testpath) {
						if(file_exists(Shopware()->OldPath() . $testpath.$file)) {
							$link = $this->basePathUrl . $testpath . $file;
							if(!empty($query)) {
								$link .= $query;
							}
							break;
						}
					}
				} elseif(strpos($src[3], $this->config->baseFile)===0) {
					if(preg_match('#title="([^"]+)"#', $src[0], $match)){
						$title = $match[1];
					} else {
						$title = null;
					}
					$link = $this->rewriteLink($src[3], $title);
				}
				break;
			case 'form':
			case 'a':	
				if(strpos($src[3], $this->config->baseFile)===0) {
					if(preg_match('#title="([^"]+)"#', $src[0], $match)){
						$title = $match[1];
					} else {
						$title = null;
					}
					$link = $this->rewriteLink($src[3], $title);
				}
				
				if ($this->appendSession
					&& preg_match('#^https?://'.preg_quote($this->basePath).'#i', $link)
					&& strpos($link, 'sCoreId=')===false)
				{
					$link .= strpos($link, '?')===false ? '?' : '&';
					$link .= 'sCoreId=' . Shopware()->SessionID();
				}
				break;
			case 'iframe':
				// Bugfix for external payment means
				if(preg_match('#^[./]+engine/connectors/#', $src[3])) {
					$link = $this->basePathUrl . preg_replace('#^[./]+#', '', $src[3]);
				}
				break;
			default:
				break;
		}
		
		if(strpos($link, 'www.')===0) {
			$link = 'http://' . $link;
		}
		if (!preg_match('#^[a-z]+:|^\#|^/#', $link)) {
			$link = $this->basePathUrl . $link;
		}
		if($this->useSecure && $src[1] != 'a') {
			$link = str_replace('http://'.$this->basePath, 'https://'.$this->basePath, $link);
		}
		
		$src[0] = str_replace($src[2].'="'.$src[3].'"', $src[2].'="'.$link.'"', $src[0]);
		return $src[0];
	}

	/**
	 * Checks if the session id must be attached
	 *
	 * @return bool
	 */
	public function checkAppendSession()
	{
		return Shopware()->SessionID()
			&& empty($_COOKIE)
			&& empty(Shopware()->Config()->dontAttachSession)
			&& empty(Shopware()->Session()->Bot);
	}
	
	/**
	 * Rewrite style link
	 *
	 * @param array $style
	 * @return string
	 */
	public function rewriteStyle($style)
	{
		if(!$this->config) {
			$this->initConfig();
		}
		
		if(preg_match('#url\((.+)\)#', $style[3], $src)) {
			if(strpos($src[1],'../../')===0) {
				$file = substr($src[1], 6);
				foreach ($this->mediaPaths as $testpath)
				if(file_exists(Shopware()->OldPath().$testpath.$file))
				{
					$link = $this->basePathUrl.$testpath.$file;
					break;
				}
			} elseif(!preg_match('#^[a-z]+://|^\#|^/#', $src[1])) {
				$link = $this->basePathUrl.$src[1];
			}
			if(!empty($link)) {
				$style[0] = str_replace($src[1], $link, $style[0]);
			}
		}
		return $style[0];
	}
	
	/**
	 * Rewrite a link with the title
	 *
	 * @param string $link
	 * @param string $title
	 * @return string
	 */
	public function rewriteLink($link=null, $title=null)
	{
		$url = str_replace(',', '=', $link);
		$url = html_entity_decode($url);
		$query = parse_url($url, PHP_URL_QUERY);
		parse_str($query, $query);
		
		if(!empty($title)) {
			$query['title'] = $title;
		}
		
		return Shopware()->Front()->Router()->assemble($query);
	}
	
	/**
	 * Returns plugin capabilities
	 *
	 * @return array
	 */
	public function getCapabilities()
    {
        return array(
    		'install' => true,
    		'update' => true
    	);
    }
}