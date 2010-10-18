<?php
class Shopware_Controllers_Frontend_SitemapXml extends Enlight_Controller_Action
{
	public function init()
	{
		Shopware()->Plugins()->Controller()->ViewRenderer()->setNoRender();
		Shopware()->Config()->sDONTATTACHSESSION = true;
		$this->Response()->setHeader('Content-Type', 'text/xml; charset=iso-8859-1');
	}
	
	public function indexAction()
	{
		Shopware()->Config()->sDONTATTACHSESSION = true;
		
		echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\r\n";
		echo "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\r\n";
		
		$sql= "
			SELECT
				a.id,
				a.name as title,
				DATE(IF(a.changetime!='0000-00-00 00:00:00',a.changetime,IF(a.datum!='0000-00-00', a.datum, ''))) as lastmod
			FROM s_articles as a, s_articles_categories ac
			WHERE a.active=1
			AND a.id = ac.articleID
			AND ac.categoryID=?
		";
		
		$result = Shopware()->Db()->fetchAll($sql, array(Shopware()->System()->sSubShop["parentID"]));
		if ($result){
			foreach ($result as $article)
			{
				
				$article["loc"] = $this->Front()->Router()->assemble(array('sViewport'=>'detail','sArticle'=>$article["id"]));
				$url = '<url>';
				$url .= '<loc>'.$article['loc'].'</loc>';
				if(!empty($article['lastmod']))
					$url .= '<lastmod>'.$article['lastmod'].'</lastmod>';
				$url .= '<changefreq>weekly</changefreq><priority>0.5</priority>';
				$url .= '</url>';
				$url .= "\r\n";
				echo $url;
			}
			
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
				AND c.parent!=1
				GROUP BY ac.categoryID
			";
			$result = Shopware()->Db()->fetchAll($sql);
			if($result){
				foreach ($result as $category){
					
					$category["loc"] = $this->Front()->Router()->assemble(array('sViewport'=>'cat','sCategory'=>$category["id"]));
					$url = '<url>';
					$url .= '<loc>'.$category['loc'].'</loc>';
					if(!empty($category['lastmod']))
						$url .= '<lastmod>'.$category['lastmod'].'</lastmod>';
					$url .= '<changefreq>weekly</changefreq><priority>0.5</priority>';
					$url .= '</url>';
					$url .= "\r\n";
					echo $url;
				}
			}
			echo "</urlset>\r\n";
		}
	}
}