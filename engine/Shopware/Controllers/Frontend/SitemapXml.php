<?php
class Shopware_Controllers_Frontend_SitemapXml extends Enlight_Controller_Action
{
	protected $result;
	
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
			unset($result);
			foreach (Shopware()->Modules()->sCategories()->sGetMainCategories() as $category){
				$id = $category["id"];
				$this->result[] = array("id"=>$id,"link"=>$category["link"],"name"=>$category["description"]);
				$subs = Shopware()->Modules()->sCategories()->sGetWholeCategoryTree($id);
				
				$this->filter($subs);
				
				$result = $this->result;
			}
			if($result){
				foreach ($result as $category){
					
					$category["loc"] = $this->Front()->Router()->assemble(array('sViewport'=>'cat','sCategory'=>$category["id"]));
					$url = '<url>';
					$url .= '<loc>'.$category['loc'].'</loc>';
					$url .= '<lastmod>'.date("Y-m-d").'</lastmod>';
					$url .= '<changefreq>weekly</changefreq><priority>0.5</priority>';
					$url .= '</url>';
					$url .= "\r\n";
					echo $url;
				}
			}
			echo "</urlset>\r\n";
		}
	}
	
	public function filter($categories){
		foreach ($categories as $category){
			$this->result[] = array("id"=>$category["id"],"link"=>$category["link"],"name"=>$category["description"] ? $category["description"] : $category["name"]);
			if ($category["subs"]){
				$this->filter($category["subs"]);
			}
		}
	}
}