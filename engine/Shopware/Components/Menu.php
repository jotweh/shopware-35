<?php
class Shopware_Components_Menu extends Zend_Navigation
{
	protected $_defaultPageClass = 'Shopware_Components_Menu_Item';
	protected $_saveHandler = null;
	
	public function save()
	{
		if($this->_saveHandler===null) {
			throw new Enlight_Exception('A save handler are required failure');
		}
		$this->_saveHandler->save($this);
	}
	
	public function load()
	{
		if($this->_saveHandler===null) {
			throw new Enlight_Exception('A save handler are required failure');
		}
		$this->_saveHandler->load($this);
	}
	
	public function setSaveHandler($saveHandler)
	{
		$this->_saveHandler = $saveHandler;
	}
	
	public function addItem($page)
	{
		return $this->addPage($page);
	}
	
	public function addItems($pages)
	{
		return $this->addPages($pages);
	}
	
	public function addPages($pages)
	{
		if ($pages instanceof Zend_Config) {
            $pages = $pages->toArray();
        }
        while ($page = array_shift($pages)) {
        	if ($page instanceof Zend_Config) {
        		$page = $page->toArray();
	        }
	        if(is_array($page)&&empty($page['parent'])) {
	        	unset($page['parent']);
	        }
        	if(is_array($page)&&isset($page['parent'])&&$page['parent']!==null&&!$page['parent'] instanceof Zend_Navigation_Container) {
	        	$parent = $this->findOneBy('id', $page['parent']);
	        	if(!empty($parent)) {
	        		unset($page['parent']);
	        		$parent->addPage($page);
	        	} else {
	        		array_push($pages, $page);
	        	}
	        } else {
	        	$this->addPage($page);
	        }
        }
	}

	public function addPage($page)
	{
		if ($page instanceof Zend_Config) {
			$page = $pages->toArray();
		}
		
		if(is_array($page)&&isset($page['parent'])&&$page['parent']!==null&&!$page['parent'] instanceof Zend_Navigation_Container) {
			$page['parent'] = $this->findOneBy('id', $page['parent']);
		}
				
		if (is_array($page)) {
			$page = call_user_func($this->_defaultPageClass.'::factory', $page);
		}
		
		$container = $page->get('parent');

		if(isset($container)) {
			$container->addPage($page);
		} else {
			parent::addPage($page);
		}
	}
}