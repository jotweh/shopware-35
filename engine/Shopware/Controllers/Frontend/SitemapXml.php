<?php
/**
 * Sitemap controller
 * 
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Heiner Lohaus
 * @package Shopware
 * @subpackage Controllers
 */
class Shopware_Controllers_Frontend_SitemapXml extends Enlight_Controller_Action
{
	/**
	 * Init controller method
	 */
	public function init()
	{
		Shopware()->Plugins()->Controller()->ViewRenderer()->setNoRender();
		
		$this->Front()->setParam('disableOutputBuffering', true);
		$this->Front()->returnResponse(true);
		
		$this->Response()->setHeader('Content-Type', 'text/xml; charset=utf-8');
		$this->Response()->sendResponse();
		
		set_time_limit(0);
	}
	
	/**
	 * Index action method
	 */
	public function indexAction()
	{
		echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\r\n";
		echo "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\r\n";
		
		$parentId = Shopware()->Shop()->get('parentID');
		
		$this->readCategoryUrls($parentId);
				
		$this->readArticleUrls($parentId);
				
		echo "</urlset>\r\n";
	}
	
	/**
	 * Print category urls
	 *
	 * @param int $parentId
	 */
	public function readCategoryUrls($parentId)
	{
		$sql= "
			SELECT
				c.id,
				c.description as title,
				MAX(DATE(a.changetime)) as `lastmod`
			FROM 
				s_categories c,
				s_articles_categories ac,
				s_articles a
			WHERE c.id=ac.categoryID
			AND a.id=ac.articleID
			AND a.active=1
			AND c.parent!=1
			AND c.external=''
			AND c.active=1
			And c.parent=?
			GROUP BY ac.categoryID
		";
		$result = Shopware()->Db()->query($sql, array($parentId));
		if (!$result->rowCount()) {
			return;
		}
		while ($url = $result->fetch()) {
			$url['loc'] = $this->Front()->Router()->assemble(array(
				'sViewport' => 'cat',
				'sCategory' => $url['id'],
				'title' => $url['title']
			));
			
			$this->printCategoryUrl($url);
			
			$this->readCategoryUrls($url['id']);
		}
	}
	
	/**
	 * Print category url
	 *
	 * @param array $url
	 */
	public function printCategoryUrl($url)
	{
		$line = '<url>';
		$line .= '<loc>'.$url['loc'].'</loc>';
		if(!empty($url['lastmod'])) {
			$line .= '<lastmod>'.$url['lastmod'].'</lastmod>';
		}
		$line .= '<changefreq>weekly</changefreq><priority>0.5</priority>';
		$line .= '</url>';
		$line .= "\r\n";
		echo $line;
	}
	
	/**
	 * Read article urls
	 *
	 * @param int $parentId
	 */
	public function readArticleUrls($parentId)
	{
		$sql = "
			SELECT
				a.id,
				a.name as title,
				DATE(
					IF(a.changetime!='0000-00-00 00:00:00', a.changetime, IF(a.datum!='0000-00-00', a.datum, ''))
				) as lastmod
			FROM s_articles as a, s_articles_categories ac
			WHERE a.active=1
			AND a.id = ac.articleID
			AND ac.categoryID=?
		";
		$result = Shopware()->Db()->query($sql, array($parentId));
		if (!$result->rowCount()) {
			return;
		}
		while ($url = $result->fetch()) {
			$url['loc'] = $this->Front()->Router()->assemble(array(
				'sViewport' => 'detail',
				'sArticle' => $url['id'],
				'title' => $url['title']
			));
			
			$this->printArticleUrls($url);
		}
	}
	
	/**
	 * Print article url
	 *
	 * @param array $url
	 */
	public function printArticleUrls($url)
	{
		$line = '<url>';
		$line .= '<loc>'.$url['loc'].'</loc>';
		if(!empty($url['lastmod'])) {
			$line .= '<lastmod>'.$url['lastmod'].'</lastmod>';
		}
		$line .= '<changefreq>weekly</changefreq><priority>0.5</priority>';
		$line .= '</url>';
		$line .= "\r\n";
		echo $line;
	}
}