<?php
class sViewportSitemap{
	var $sSYSTEM;
	
	function sRender(){
		
		foreach ($this->sSYSTEM->sMODULES["sCategories"]->sGetMainCategories() as $category){
			$id = $category["id"];
			//print_r($category);
			$result[] = array("link"=>$category["link"],"name"=>$category["description"],"sub"=>$this->sSYSTEM->sMODULES["sCategories"]->sGetWholeCategoryTree($id));
		}
		
		//print_r($result);
		$variables["sCategoryTree"] = $result;
		$templates = array("sContainer"=>"/category/category_sitemap.tpl","sContainerRight"=>"");
		

		
		$variables["sBreadcrumb"] = array(0=>array("name"=>$this->sSYSTEM->sCONFIG['sViewports'][$this->sSYSTEM->_GET["sViewport"]]["name"]));
			
		
		
		return array("templates"=>$templates,"variables"=>$variables);
	}
}
?>