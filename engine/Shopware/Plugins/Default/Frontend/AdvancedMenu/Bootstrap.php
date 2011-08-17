<?php
/**
 * Shopware AdvancedMenu Plugin
 * 
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Heiner Lohaus
 * @package Shopware
 * @subpackage Plugins
 */
class Shopware_Plugins_Frontend_AdvancedMenu_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
	/**
	 * Install plugin method
	 *
	 * @return bool
	 */
	public function install()
	{
		$event = $this->createEvent(
			'Enlight_Controller_Action_PostDispatch',
			'onPostDispatch'
		);
		$this->subscribeEvent($event);

		$form = $this->Form();
		
		$form->setElement('checkbox', 'show', array('label'=>'Menu zeigen', 'value'=>1, 'scope'=>Shopware_Components_Form::SCOPE_SHOP));
		$form->setElement('checkbox', 'caching', array('label'=>'Caching aktivieren', 'value'=>1));
		$form->setElement('text', 'cachetime', array('label'=>'Cachezeit', 'value'=>86400));
		$form->setElement('text', 'levels', array('label'=>'Anzahl Ebenen', 'value'=>2, 'scope'=>Shopware_Components_Form::SCOPE_SHOP));
		
		$form->save();

		return true;
	}
	
	/**
	 * Event listener method
	 *
	 * @param Enlight_Event_EventArgs $args
	 */
	public static function onPostDispatch(Enlight_Event_EventArgs $args)
	{	
		$request = $args->getSubject()->Request();
		$response = $args->getSubject()->Response();
		$view = $args->getSubject()->View();
			
		if(!$request->isDispatched()||$response->isException()||$request->getModuleName()!='frontend') {
			return;
		}
		
		$parent = Shopware()->Shop()->get('parentID');
		$category = empty(Shopware()->System()->_GET['sCategory']) ? $parent : Shopware()->System()->_GET['sCategory'];
		$usergroup = Shopware()->System()->sUSERGROUPDATA['id'];
		$config = Shopware()->Plugins()->Frontend()->AdvancedMenu()->Config();
		
		if(empty($config->show) && $config->show!==null) {
			return;
		}
		
		$compile_id = $view->Template()->compile_id;
		//$cache_id = 'frontend|index|plugins|advanced_menu|'.$category.'|'.$usergroup;
		//$template = 'frontend/plugins/advanced_menu/advanced_menu.tpl';
		$view->assign('sAdvancedMenuConfig', array(
			'cache_id' => $cache_id,
			'template' => $template,
			'caching' => (bool) $config->caching,
			'cachtime' => (int) $config->cachetime
		));
		//if(!$view->Engine()->isCached($template, $cache_id, $compile_id)) {
			$view->assign('sAdvancedMenu', Shopware()->Plugins()->Frontend()->AdvancedMenu()->getAdvancedMenu(
				$parent,
				$category,
				(int) $config->levels
			));
			$view->extendsTemplate('frontend/plugins/advanced_menu/index.tpl');
		//}
	}
	
	/**
	 * Returns the complete menu with category path.
	 *
	 * @param int $category
	 * @param int $categoryFlag
	 * @param int $depth
	 * @return array
	 */
	public function getAdvancedMenu($category, $categoryFlag=null, $depth=null)
	{
		$shopID = Shopware()->Shop()->getId();
		$config = Shopware()->Plugins()->Frontend()->AdvancedMenu()->Config();
		$id = 'Shopware_AdvancedMenu_Tree_'.$shopID.'_'.$category.'_'.Shopware()->System()->sUSERGROUPDATA['id'];
		$cache = Shopware()->Cache();
		
		if(!empty($config->caching)) {
			if(!$cache->test($id)) {
				$tree = $this->getCategoryTree($category, $depth);
				$cache->save($tree, $id, array('Shopware_Plugin'), $config->cachetime);
			} else {
				$tree = $cache->load($id);
			}
			
			$id = 'Shopware_AdvancedMenu_Path_'.$categoryFlag.'_'.$category;
			$cache = Shopware()->Cache();
			if(!$cache->test($id)) {
				$path = $this->getCatogeryPath($categoryFlag, $category);
				$cache->save($path, $id, array('Shopware_Plugin'), $config->cachetime);
			} else {
				$path = $cache->load($id);
			}
		} else {
			$tree = $this->getCategoryTree($category, $depth);
			$path = $this->getCatogeryPath($categoryFlag, $category);
		}
		
		$ref =& $tree;
		foreach ($path as $categoryId) {
			if(isset($ref[$categoryId])) {
				$ref[$categoryId]['flag'] = true;
				$ref =& $ref[$categoryId]['sub'];
			} else {
				break;
			}
		}
		return $tree;
	}
	
	/**
	 * Returns a category tree.
	 *
	 * @param int $category
	 * @param int $depth
	 * @return array
	 */
	public function getCategoryTree($category, $depth=null)
	{
		$sql = '
			SELECT c.*, (SELECT COUNT(*) FROM s_articles_categories WHERE categoryID = c.id GROUP BY categoryID) AS countArticles
			FROM s_categories c
			WHERE parent = ? 
			AND active = 1
			AND hidetop = 0
			AND (
				SELECT categoryID 
				FROM s_categories_avoid_customergroups 
				WHERE categoryID = c.id AND customergroupID = '.Shopware()->System()->sUSERGROUPDATA['id'].'
			) IS NULL
			HAVING countArticles>0
			ORDER BY position, description
		';
		$result = Shopware()->Db()->query($sql, array($category));
		$depth--;
		$categories = array();
		while ($row = $result->fetch()) {
			$row['name'] = $row['description'];
			if($row['countArticles']==1 && !empty(Shopware()->Config()->CategoryDetailLink)) {
				$sql = '
					SELECT a.id, name
					FROM s_articles a, s_articles_categories ac
					WHERE a.id = ac.articleID
					AND ac.categoryID=?
					AND a.active=1
					LIMIT 1
				';
				$article = Shopware()->Db()->fetchRow($sql, array($row['id']));
				$row['link'] = Shopware()->Router()->assemble(array('sViewport'=>'detail', 'sArticle'=>$article['id']));
			} elseif (!empty($row['external'])) {
				$row['link'] = $row['external'];
			} else {
				$row['link'] = Shopware()->Router()->assemble(array('sViewport'=>'cat', 'sCategory'=>$row['id']));
			}
			if($depth===null || $depth>=0) {
				$row['sub'] = $this->getCategoryTree($row['id'], $depth);
			}
			$categories[(int) $row['id']] = $row;
		}
		return $categories;
	}
	
	/**
	 * Returns a catogery path by category id.
	 *
	 * @param int $category
	 * @param int $end
	 * @return array
	 */
	public function getCatogeryPath($category, $end)
	{
		if($category == $end) {
			return array();
		}
		$category = (int) $category;
		$sql = 'SELECT parent FROM s_categories WHERE id=?';
		$next = Shopware()->Db()->fetchOne($sql, array($category));
		if(empty($next) || $category == $next) {
			return array($category);
		}
		$result = $this->getCatogeryPath($next, $end);
		$result[] = $category;
		return $result;
	}
}