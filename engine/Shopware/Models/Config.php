<?php
class Shopware_Models_Config extends Shopware_Components_Config_DbTable
{
	protected $_name = 's_core_config';
	protected $_allowModifications = true;
	protected $_shop;
	protected $_cacheTags = array('Shopware_Config');
	
  	public function __construct($config)
  	{
  		if(isset($config['shop'])) {
  			if($config['shop'] instanceof Shopware_Models_Shop) {
  				$this->_shop = $config['shop'];
  			} else {
  				$this->_shop = new Shopware_Models_Shop($config['shop']);
  			}
  			unset($config['shop']);
  			$this->_cacheTags[] = 'Shopware_Shop'.$this->_shop->getId();
  		}
  		parent::__construct($config);
  		$this->set('sErrors', $this->get('sSnippets'));
  	}
  	
  	protected function load()
  	{
  		$data = parent::load();
  		  		
  		if($this->_shop!==null && !$this->_shop->get('default') && !$this->_shop->get('skipbackend')) {
  			if($this->_shop->get('fallback')) {
  				$data = $this->_arrayMergeRecursive($data, $this->readTranslation('config', $this->_shop->get('fallback')));
			}
  			$data = $this->_arrayMergeRecursive($data, $this->readTranslation('config', $this->_shop->get('isocode')));
  		}
  		
    	$sql = 'SELECT name as `key`, cm.* FROM s_core_config_mails cm';
		$data['sTemplates'] = $this->_dbTable->getAdapter()->fetchAssoc($sql);
		
    	$sql = 'SELECT viewport, viewport_file as file, description as name FROM s_core_viewports';
		$data['sViewports'] = $this->_dbTable->getAdapter()->fetchAssoc($sql);
		
		$sql = 'SELECT name, value FROM s_core_config_text';
		$data['sSnippets'] = $this->_dbTable->getAdapter()->fetchPairs($sql);

		if($this->_shop!==null && !$this->_shop->get('default') && !$this->_shop->get('skipbackend')) {
			if($this->_shop->get('fallback')) {
				$data['sTemplates'] = $this->_arrayMergeRecursive($data['sTemplates'], $this->readTranslation('config_mails', $this->_shop->get('fallback')));
  				$data['sViewports'] = $this->_arrayMergeRecursive($data['sViewports'], $this->readTranslation('config_viewports', $this->_shop->get('fallback')));
  				$data['sSnippets'] = $this->_arrayMergeRecursive($data['sSnippets'], $this->readTranslation('config_snippets', $this->_shop->get('fallback')));
			}
  			$data['sTemplates'] = $this->_arrayMergeRecursive($data['sTemplates'], $this->readTranslation('config_mails', $this->_shop->get('isocode')));
  			$data['sViewports'] = $this->_arrayMergeRecursive($data['sViewports'], $this->readTranslation('config_viewports', $this->_shop->get('isocode')));
  			$data['sSnippets'] = $this->_arrayMergeRecursive($data['sSnippets'], $this->readTranslation('config_snippets', $this->_shop->get('isocode')));
  		}
    	
    	return $data;
  	}
  	
  	protected function readTranslation($type, $language)
	{
		$sql = '
			SELECT objectdata FROM s_core_translations WHERE objecttype=? AND objectlanguage=?
		';
		$data = $this->_dbTable->getAdapter()->fetchOne($sql, array($type, $language));
		if(empty($data)) {
			return array();
		}
		$data = unserialize($data);
		if(empty($data)) {
			return array();
		}
		
		switch ($type) {
			case 'config_viewports':
				$map = array('description'=>'name');
				break;
			case 'config_snippets':
				$map = array('value'=>null);
				break;
			default:
				break;
		}
		
		if(isset($map)) {
			foreach ($map as $mapKey=>$mapValue) {
				foreach ($data as $key => $value) {
					if(isset($value[$mapKey])) {
						if($mapValue!==null) {
							$data[$key][$mapValue] = $value[$mapKey];
							unset($data[$key][$mapKey]);
						} else {
							$data[$key] = $value[$mapKey];
						}
					}
				}
			}
		}
		
		return $data;
	}
	
	public function __call($name, $args)
	{
		return $this->get($name);
	}
}