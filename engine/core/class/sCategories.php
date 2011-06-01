<?php
/**
 * Managing shopware categories
 * @link http://www.shopware.de
 * @package Shopware
 * @subpackage Core\Class
 * @copyright (C) Shopware AG 211
 * @version Shopware 3.5.4
 */
class sCategories
{

	var $sSYSTEM;
	
	/**
	 * This function is deprecated
	 * @param int $id
	 * @return array
	 */
	public function sGetCategoriesAsArrayByIdTest($id=0)
	{
		return $this->sGetCategories($id);
	}
	
	/**
	* Alle Unterkategorien einer bestimmten Kategorie auslesen
	* $id int Kategorie ID
	* @return array 
	**/
	public function sGetCategories($id=0)
	{

		if (empty($id)){
			$id = $this->sSYSTEM->sLanguageData[$this->sSYSTEM->sLanguage]["parentID"];
			if (!$id){
				$id = $this->sSYSTEM->sCONFIG["sCATEGORYPARENT"];
			}
		}
		
		// Get base-ID
		$baseId = $this->sSYSTEM->sLanguageData[$this->sSYSTEM->sLanguage]["parentID"] ? $this->sSYSTEM->sLanguageData[$this->sSYSTEM->sLanguage]["parentID"] : $this->sSYSTEM->sCONFIG["sCATEGORYPARENT"];
		
		// Check for category alias
		$categoryInfo = $this->sGetCategoryContent($id);
		
		$categoryParent = $this->sGetCategoryIdsByParent($id);
		if (isset($categoryParent[count($categoryParent)-2])){
			$categoryParent = $categoryParent[count($categoryParent)-2];
		}

		if ($id!=$baseId){
			
			$parentIds = array_merge(array($id),$this->sGetCategoryIdsByParent($id));
		
			foreach ($parentIds as $pId) {
				
				$sql = "
					SELECT c.*,
						(SELECT COUNT(*) FROM s_articles_categories WHERE categoryID = c.id GROUP BY categoryID) AS countArticles
					FROM s_categories c
					WHERE parent = ? 
					AND c.active = 1
					AND (
						SELECT categoryID 
						FROM s_categories_avoid_customergroups 
						WHERE categoryID = c.id AND customergroupID = ".$this->sSYSTEM->sUSERGROUPDATA["id"]."
					) IS NULL
					HAVING countArticles>0
					ORDER BY position, description
				";
				
				$getCategories = $this->sSYSTEM->sDB_CONNECTION->CacheGetAll($this->sSYSTEM->sCONFIG['sCACHECATEGORY'],$sql,array($pId));

				if(!empty($getCategories)) {
					foreach ($getCategories as $categoryKey => $categoryObject){

						if ($getCategories[$categoryKey]["id"]==$id){
							
							$getCategories[$categoryKey]["flag"] = true;
						}else {
							$getCategories[$categoryKey]["flag"] = false;
						}
						if ($getCategories[$categoryKey]["id"]==$backupId){
							$getCategories[$categoryKey]["subcategories"] = $backup;
						} else {
							$getCategories[$categoryKey]["subcategories"] = array();
						}
						if ($categoryObject["countArticles"]==1 && !empty($this->sSYSTEM->sCONFIG["sCATEGORYDETAILLINK"])) {
							$articleId = $this->sSYSTEM->sDB_CONNECTION->GetOne("
								SELECT s_articles.id FROM s_articles, s_articles_categories
								WHERE s_articles.id = s_articles_categories.articleID
								AND s_articles_categories.categoryID = ? AND s_articles.active = 1
							",	array($categoryObject["id"]));
							$articleName = $this->sSYSTEM->sMODULES['sCore']->sGetArticleNameByArticleId($articleId);
							$getCategories[$categoryKey]["link"] = $this->sSYSTEM->sMODULES['sCore']->sRewriteLink(
								$this->sSYSTEM->sCONFIG['sBASEFILE'] . '?sViewport=detail&sArticle=' . $articleId, $articleName
							);
						} elseif(!empty($categoryObject["external"])) {
							$getCategories[$categoryKey]["link"] = $categoryObject["external"];
						}
						if(empty($getCategories[$categoryKey]["link"])) {
							$getCategories[$categoryKey]["link"] = $this->sSYSTEM->sCONFIG['sBASEFILE']."?sViewport=cat&sCategory={$categoryObject['id']}";	
						}
					}
				}
				$backupId = $pId;
				$backup = $getCategories;
			}
		} else {
			
			$sql = "
			SELECT *
			FROM s_categories c
			WHERE parent = ?  
			AND c.active = 1
			AND (
					SELECT categoryID 
					FROM s_categories_avoid_customergroups 
					WHERE categoryID = c.id AND customergroupID = ".$this->sSYSTEM->sUSERGROUPDATA["id"]."
				) IS NULL
			AND (
				SELECT categoryID
				FROM s_articles_categories
				WHERE categoryID = c.id
				LIMIT 1
			)
			ORDER BY position, description
			";

			$getCategories = $this->sSYSTEM->sDB_CONNECTION->CacheGetAll($this->sSYSTEM->sCONFIG['sCACHECATEGORY'],$sql,array($id));
			
			
			foreach ($getCategories as $categoryKey => $categoryObject){
				$getCategories[$categoryKey]["flag"] = false;
				$getCategories[$categoryKey]["subcategories"] = array();
				$getCategories[$categoryKey]["link"] = $this->sSYSTEM->sCONFIG['sBASEFILE']."?sViewport=cat&sCategory={$categoryObject['id']}";
					
			}
		}
		

		return $getCategories;
	}

	/**
	* Alle Unterkategorien einer bestimmten Kategorie auslesen
	* $id int Kategorie ID
	* @return array 
	**/
	public function sGetCategoriesAsArrayById($id=0)
	{
		if (empty($id)) {
			$id = $this->sSYSTEM->sLanguageData[$this->sSYSTEM->sLanguage]["parentID"];
			if (!$id){
				$id = $this->sSYSTEM->sCONFIG["sCATEGORYPARENT"];
			}
		}
		
		// Get base-ID
		$baseId = $this->sSYSTEM->sLanguageData[$this->sSYSTEM->sLanguage]["parentID"] ? $this->sSYSTEM->sLanguageData[$this->sSYSTEM->sLanguage]["parentID"] : $this->sSYSTEM->sCONFIG["sCATEGORYPARENT"];
			
		$getCategories = $this->sSYSTEM->sDB_CONNECTION->CacheGetAll($this->sSYSTEM->sCONFIG['sCACHECATEGORY'],"
		SELECT id, description, parent, alias,ac_attr1,ac_attr2,ac_attr3,ac_attr4,ac_attr5,ac_attr6 FROM s_categories AS K
		WHERE K.parent=? 
		AND (
					SELECT categoryID 
					FROM s_categories_avoid_customergroups 
					WHERE categoryID = K.id AND customergroupID = ".$this->sSYSTEM->sUSERGROUPDATA["id"]."
					) IS NULL
		AND K.active=1 ORDER BY position, description
		",array($id));
		
		// Parsing result, check if sub-categories exists and set link
		if (count($getCategories)){
			foreach ($getCategories as $categoryKey => $categoryObject){
				$checkSubCategory = $this->sSYSTEM->sDB_CONNECTION->CacheGetRow($this->sSYSTEM->sCONFIG['sCACHECATEGORY'],"
				SELECT id FROM s_categories WHERE parent=? AND active=1 LIMIT 1
				",array($categoryObject["id"]));
				if ($checkSubCategory["id"]){
					$getCategories[$categoryKey]["hasSubCategories"] = true;
				}else {
					$getCategories[$categoryKey]["hasSubCategories"] = false;
				}
				// Setting link
				$getCategories[$categoryKey]["link"] = $this->sSYSTEM->sCONFIG['sBASEFILE']."?sViewport=cat&sCategory={$categoryObject['id']}";
			}
		}else {
			// Occurs if the category has no sub-categories any-more, then we show all categories with same parent
				$getCategoryParent = $this->sSYSTEM->sDB_CONNECTION->CacheGetRow($this->sSYSTEM->sCONFIG['sCACHECATEGORY'],"
				SELECT id, description, parent, alias,ac_attr1,ac_attr2,ac_attr3,ac_attr4,ac_attr5,ac_attr6 FROM s_categories AS K
				WHERE K.id=? 
				AND (
					SELECT categoryID 
					FROM s_categories_avoid_customergroups 
					WHERE categoryID = K.id AND customergroupID = ".$this->sSYSTEM->sUSERGROUPDATA["id"]."
					) IS NULL
				AND K.active = 1 ORDER BY position, description
				",array($id));
				$parentId = $getCategoryParent["parent"];
				
				// Get categories with same parent
				$getCategories = $this->sSYSTEM->sDB_CONNECTION->CacheGetAll($this->sSYSTEM->sCONFIG['sCACHECATEGORY'],"
				SELECT id, description, parent, alias,ac_attr1,ac_attr2,ac_attr3,ac_attr4,ac_attr5,ac_attr6 FROM s_categories AS K
				WHERE K.parent=? 
				AND (
					SELECT categoryID 
					FROM s_categories_avoid_customergroups 
					WHERE categoryID = K.id AND customergroupID = ".$this->sSYSTEM->sUSERGROUPDATA["id"]."
					) IS NULL
				AND K.active = 1 ORDER BY position, description
				",array($parentId));
				if (empty($getCategories) || !is_array($getCategories)) $getCategories = array();
				
				foreach ($getCategories as $categoryKey => $categoryObject){
					$checkSubCategory = $this->sSYSTEM->sDB_CONNECTION->CacheGetRow($this->sSYSTEM->sCONFIG['sCACHECATEGORY'],"
					SELECT id FROM s_categories WHERE parent=? LIMIT 1
					",array($categoryObject["id"]));
					if ($checkSubCategory["id"]){
						$getCategories[$categoryKey]["hasSubCategories"] = true;
					}else {
						$getCategories[$categoryKey]["hasSubCategories"] = false;
					}
					if ($id!=$getCategories[$categoryKey]["id"]){
						// Setting link
						$getCategories[$categoryKey]["link"] = $this->sSYSTEM->sCONFIG['sBASEFILE']."?sViewport=cat&sCategory={$categoryObject['id']}";
					}else {
						// The current choosen category doesn´t get linked anymore
						$getCategories[$categoryKey]["link"] = "";
					}
				} // -- for every category
		} // no sub-categories

		return $getCategories;
	}
	
	/**
	* Hauptkategorien auslesen
	* @return array 
	**/
	public function sGetMainCategories()
	{
		
		$baseId = $this->sSYSTEM->sLanguageData[$this->sSYSTEM->sLanguage]["parentID"] ? $this->sSYSTEM->sLanguageData[$this->sSYSTEM->sLanguage]["parentID"] : $this->sSYSTEM->sCONFIG["sCATEGORYPARENT"];

		$sql = "
			SELECT c.*, (SELECT COUNT(*) FROM s_articles_categories WHERE categoryID = c.id GROUP BY categoryID) AS countArticles
			FROM s_categories c
			WHERE parent = ? 
			AND (
					SELECT categoryID 
					FROM s_categories_avoid_customergroups 
					WHERE categoryID = c.id AND customergroupID = ".$this->sSYSTEM->sUSERGROUPDATA["id"]."
					) IS NULL
			AND active = 1
			HAVING countArticles>0
			ORDER BY position, description
		";
	
		$mainCategories = $this->sSYSTEM->sDB_CONNECTION->CacheGetAll($this->sSYSTEM->sCONFIG['sCACHECATEGORY'],$sql,array($baseId));
		

		if (isset($this->sSYSTEM->_GET['sCategory'])){
			// Grep parent of the current choosen category
			$getParent = $this->sGetCategoriesByParent($this->sSYSTEM->_GET['sCategory']);
			if (count($getParent) && isset($getParent[count($getParent)-1]["link"])){
				$categoryActiveId = preg_replace("/(.*)sCategory=(.*)/u","\\2",$getParent[count($getParent)-1]["link"]);
			}else {
				$categoryActiveId = 0;
			}
		}else {
			$categoryActiveId = 0;
		}
		
		
		foreach ($mainCategories as $categoryKey => $categoryObject){
			
			$mainCategories[$categoryKey][1] = $categoryObject["description"];	// SW 2.1
			
			
			if ($categoryObject["countArticles"]==1 && !empty($this->sSYSTEM->sCONFIG["sCATEGORYDETAILLINK"])){
				$getArticle = $this->sSYSTEM->sDB_CONNECTION->GetRow("
				SELECT s_articles.id, name FROM s_articles, s_articles_categories WHERE s_articles.id = s_articles_categories.articleID
				AND s_articles_categories.categoryID = ? AND s_articles.active = 1
				",array($categoryObject["id"]));
				$mainCategories[$categoryKey]["link"] = $this->sSYSTEM->sMODULES['sCore']->sRewriteLink($this->sSYSTEM->sCONFIG['sBASEFILE']."?sViewport=detail&sArticle={$getArticle['id']}",$getArticle["name"]);
			}elseif($categoryObject["external"]){
				// Task
				$mainCategories[$categoryKey]["link"] = $categoryObject["external"];
				
			}
			else {
				$mainCategories[$categoryKey]["link"] = $this->sSYSTEM->sCONFIG['sBASEFILE']."?sViewport=cat&sCategory={$categoryObject['id']}";
			}
			if ($categoryObject["id"]==$categoryActiveId){
				$mainCategories[$categoryKey]["flag"] = true;
			}else {
				$mainCategories[$categoryKey]["flag"] = false;
			}

		}


		return $mainCategories;
	}
	
	/**
	* Child Ids einer übergeordneten Kategorie auslesen
	* $cat int Kategorie ID
	* @return array 
	**/
	public function sGetCategoryIdsByParent ($cat)
	{
		$baseId = $this->sSYSTEM->sLanguageData[$this->sSYSTEM->sLanguage]["parentID"] ? $this->sSYSTEM->sLanguageData[$this->sSYSTEM->sLanguage]["parentID"] : $this->sSYSTEM->sCONFIG["sCATEGORYPARENT"];
		
		$id = $this->sSYSTEM->sDB_CONNECTION->CacheGetOne($this->sSYSTEM->sCONFIG['sCACHECATEGORY'],"SELECT parent FROM s_categories WHERE id=?",array($cat)); 

		$show[] = $id;
		
		if ($id!=$baseId && $id){
			$tmp =  $this->sGetCategoryIdsByParent($id);
			$show = array_merge($show,$tmp);
		}

		return $show;
	}
	
	/**
	* Childs einer übergeordneten Kategorie auslesen
	* $cat int Kategorie ID
	* @return array 
	**/
	public function sGetCategoriesByParent ($cat)
	{
		$baseId = $this->sSYSTEM->sLanguageData[$this->sSYSTEM->sLanguage]["parentID"] ? $this->sSYSTEM->sLanguageData[$this->sSYSTEM->sLanguage]["parentID"] : $this->sSYSTEM->sCONFIG["sCATEGORYPARENT"];
		
		if($baseId==$cat) return array();
		
		$getCategories = $this->sSYSTEM->sDB_CONNECTION->CacheGetRow($this->sSYSTEM->sCONFIG['sCACHECATEGORY'],"SELECT id,parent,description,ac_attr1,ac_attr2,ac_attr3,ac_attr4,ac_attr5,ac_attr6 FROM s_categories WHERE id=?",array($cat)); 
		
		if (empty($getCategories["parent"])) $getCategories["parent"] = 0;
		if (empty($getCategories["id"])) $getCategories["id"] = 0;
		if (empty($getCategories["description"])) $getCategories["description"] = "";
		
		$id = $getCategories["parent"];
		$tmpId = $getCategories["id"];
		$name = $getCategories["description"];
		
	
		$getCategoriesArray[] = array("link"=>$this->sSYSTEM->sCONFIG['sBASEFILE']."?sViewport=cat&sCategory=$tmpId","name"=>$name);
		
		if ($id!=$baseId && $id){
			$tmp =  $this->sGetCategoriesByParent($id);
			$getCategoriesArray = array_merge($getCategoriesArray,$tmp);
		}

		return $getCategoriesArray;
	}
	
	/**
	* Den kompletten Verzeichnisbaum beginnend mit $parent auslesen
	* $parent int Kategorie ID
	* @return array 
	**/
	public function sGetWholeCategoryTree ($parent)
	{
		
		$baseId = $this->sSYSTEM->sLanguageData[$this->sSYSTEM->sLanguage]["parentID"] ? $this->sSYSTEM->sLanguageData[$this->sSYSTEM->sLanguage]["parentID"] : $this->sSYSTEM->sCONFIG["sCATEGORYPARENT"];
		
		$sql = "
			SELECT c.*, (SELECT COUNT(*) FROM s_articles_categories WHERE categoryID = c.id GROUP BY categoryID) AS countArticles
			FROM s_categories c
			WHERE parent = ? 
			AND (
				SELECT categoryID 
				FROM s_categories_avoid_customergroups 
				WHERE categoryID = c.id AND customergroupID = ".$this->sSYSTEM->sUSERGROUPDATA["id"]."
			) IS NULL
			AND active = 1
			HAVING countArticles>0
			ORDER BY position, description
		";
		
		$getCategories = $this->sSYSTEM->sDB_CONNECTION->CacheGetAll($this->sSYSTEM->sCONFIG['sCACHECATEGORY'],$sql,array($parent)); 

		$getCategoriesArray = array();
		$subCategories = array();
		foreach ($getCategories as $category){
			$id = $category["id"];
			
			$name = $category["description"];
			
			if ($id!=$baseId && $id){
				$tmp =  $this->sGetWholeCategoryTree($id);
				if (count($tmp)){
					$subCategories = $tmp;
				}else {
					$subCategories = array();
				}
			}
			if ($category["countArticles"]==1 && !empty($this->sSYSTEM->sCONFIG["sCATEGORYDETAILLINK"])){
				$getArticle = $this->sSYSTEM->sDB_CONNECTION->GetRow("
				SELECT s_articles.id, name FROM s_articles, s_articles_categories WHERE s_articles.id = s_articles_categories.articleID
				AND s_articles_categories.categoryID = ? AND s_articles.active = 1
				",array($category["id"]));
				$link = $this->sSYSTEM->sMODULES['sCore']->sRewriteLink($this->sSYSTEM->sCONFIG['sBASEFILE']."?sViewport=detail&sArticle={$getArticle['id']}",$getArticle["name"]);
			}elseif($category["external"]){
				// Task
				$link = $category["external"];
			}else {
				$link = $this->sSYSTEM->sCONFIG['sBASEFILE']."?sViewport=cat&sCategory=$id";
			}
			
			$getCategoriesArray[] = array("link"=>$link,"name"=>$name,"sub"=>$subCategories);
			

		}

		return $getCategoriesArray;
	}
	
	/**
	 * Die Tiefe einer bestimmten Kategorie auslesen
	 *
	 * @param int $cat Kategorie
	 * @return unknown
	 */
	public function sGetCategoryDepth($cat)
	{
		$baseId = $this->sSYSTEM->sLanguageData[$this->sSYSTEM->sLanguage]["parentID"] ? $this->sSYSTEM->sLanguageData[$this->sSYSTEM->sLanguage]["parentID"] : $this->sSYSTEM->sCONFIG["sCATEGORYPARENT"];
		
		$id = $this->sSYSTEM->sDB_CONNECTION->CacheGetOne($this->sSYSTEM->sCONFIG['sCACHECATEGORY'],"SELECT parent FROM s_categories WHERE id=?",array($cat));
		$depth=0;
		if ($id==$baseId) $depth++;
		if ($id!=$baseId && $id){
			$tmp =  $this->sGetCategoryDepth($id);
			$depth++;
			$depth+=$tmp;
		}
		return $depth;
	}
	
	/**
	* Alle Informationen (s_categories.*) einer besitmmten Kategorie
	* @param int $id Kategorie ID
	* @return array 
	**/
	public function sGetCategoryContent ($id)
	{
		$getCategoryCMS = $this->sSYSTEM->sDB_CONNECTION->CacheGetRow($this->sSYSTEM->sCONFIG['sCACHECATEGORY'],"
		SELECT * FROM s_categories WHERE id=?
		",array($id));
		$getCategoryCMS["subcategories"] = $this->sGetCategoriesAsArrayById($getCategoryCMS);
		$this->sSYSTEM->sExtractor[] = "sRss";
		$this->sSYSTEM->sExtractor[] = "sAtom";
		$getCategoryCMS["rssFeed"] = $this->sSYSTEM->sCONFIG['sBASEFILE'].$this->sSYSTEM->sBuildLink(array("sRss"=>1),false);
		$getCategoryCMS["atomFeed"] = $this->sSYSTEM->sCONFIG['sBASEFILE'].$this->sSYSTEM->sBuildLink(array("sAtom"=>1),false);
		$getCategoryCMS["sSelf"] = $this->sSYSTEM->sCONFIG['sBASEFILE'].$this->sSYSTEM->sBuildLink(array(),false);
		return $getCategoryCMS;
	}
}