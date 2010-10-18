<?php
/**
 * Url router
 * @link http://www.shopware.de
 * @package core
 * @subpackage class
 * @copyright (C) Shopware AG 2002-2010
 * @version Shopware 3.5.0
 */
class sRouter
{
	public $sSYSTEM;
				
	public function sUserSupportCookies()
	{
		if($this->sUserIsBot())
			return true;
		elseif(empty($this->sSYSTEM->_COOKIE))
			return false;
		else
			return true;
	}
	public function sUserIsBot()
	{
		static $result;
		if(isset($result))
		{
			return $result;
		}
		$result = false;
		$useragent = preg_replace("/[^a-z]/", "", strtolower($_SERVER['HTTP_USER_AGENT']));
		$bots = preg_replace("/[^a-z;]/", "", strtolower($this->sSYSTEM->sCONFIG["sBOTBLACKLIST"]));
		$bots = explode(";",$bots);
		if(!empty($useragent) && str_replace($bots, '', $useragent)!=$useragent)
		{
			$result = true;
		}
		return $result;
	}
	public function sUserUseSSL()
	{
		return !empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS'])!='off';
	}
			
	public function sGetLocation()
	{
		static $location;
		if(!isset($location))
		{
			$location = $this->sUserUseSSL() ? 'https://' : 'http://';
			$location .= $this->sSYSTEM->sCONFIG['sHOST'];
			$location .= $_SERVER['REQUEST_URI'];
		}
		return $location;
	}
		
	public function sRedirectLocation($url)
	{
		$new_query = array();
			
		$sql = 'SELECT org_path FROM s_core_rewrite_urls WHERE main=0 AND path LIKE ? AND subshopID=?';
		$result = $this->sSYSTEM->sDB_CONNECTION->GetRow($sql, array($url, $this->sSYSTEM->sSubShop['id']));
		if(!empty($result['org_path']))
		{
			parse_str($result['org_path'], $new_query);
		}
		elseif(!empty($this->sSYSTEM->_GET['sViewport'])&&$this->sSYSTEM->_GET['sViewport']=='basket')
		{
			foreach ($this->sSYSTEM->_GET as $key=>$value)
			{
				if(!empty($value)&&strpos($key, '_')===false)
				{
					$new_query[$key] = $value;
				}
			}
		}
		elseif(!empty($this->sSYSTEM->_GET['sViewport'])&&$this->sSYSTEM->_GET['sViewport']=='searchFuzzy')
		{
			$new_query = $this->sSYSTEM->_GET;
			unset($new_query['sLanguage']);
		}
		elseif(!empty($this->sSYSTEM->_GET['sViewport'])&&$this->sSYSTEM->_GET['sViewport']=='sale'
			&& $this->sSYSTEM->_GET['sAction']=='doSale'&&empty($_POST))
		{
			$new_query = $this->sSYSTEM->_GET;
		}
		elseif(empty($this->sSYSTEM->_GET['sViewport'])&&empty($this->sSYSTEM->_GET['sCoreId'])&&!$this->sUserSupportCookies())
		{
			$new_query['sViewport'] = false;
		}
		elseif(empty($this->sSYSTEM->_GET['sViewport'])&&!empty($this->sSYSTEM->_GET['sCoreId'])&&$this->sUserSupportCookies())
		{
			$new_query['sViewport'] = false;
		}
		elseif (!empty($this->sSYSTEM->sCONFIG['sREDIRECTBASEFILE'])&&$url==$this->sSYSTEM->sCONFIG['sBASEFILE']
			&& empty($_POST)&&empty($this->sSYSTEM->_GET))
		{
			$new_query['sViewport'] = false;
		}
		elseif (!empty($this->sSYSTEM->_GET['sViewport'])
			&& in_array($this->sSYSTEM->_GET['sViewport'], array('detail', 'cat', 'campaign', 'custom')))
		{
			$new_query = $this->sSYSTEM->_GET;
		}
		if(!empty($this->sSYSTEM->_GET['sViewport'])&&!empty($this->sSYSTEM->sCONFIG['sREDIRECTNOTFOUND']))
		{
			if($this->sSYSTEM->_GET['sViewport']=='cat')
			{
				$sql = 'SELECT id FROM s_categories WHERE active=1 AND parent!=1 AND id=?';
				$result = $this->sSYSTEM->sDB_CONNECTION->GetOne($sql, array($this->sSYSTEM->_GET['sCategory']));
				if(empty($result))
				{
					$new_query['sViewport'] = false;
				}
			}
			elseif($this->sSYSTEM->_GET['sViewport']=='detail')
			{
				$sql = 'SELECT id FROM s_articles WHERE active=1 AND id=?';
				$result = $this->sSYSTEM->sDB_CONNECTION->GetOne($sql, array($this->sSYSTEM->_GET['sArticle']));
				if(empty($result))
				{
					$new_query['sViewport'] = false;
				}
			}
		}
		if(!empty($new_query))
		{
			$new_path = $this->sCustomRewrite($new_query);
			if(!empty($new_path)&&$new_path!=$this->sGetLocation())
			{
				return $new_path;
			}
		}
		return false;
	}
}