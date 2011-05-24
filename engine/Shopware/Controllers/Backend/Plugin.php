<?php
/**
 * Shopware Plugin Manager
 *
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @package Shopware
 * @author st.hamann
 * @author h.lohaus
 * @subpackage Plugins
 */
class Shopware_Controllers_Backend_Plugin extends Enlight_Controller_Action
{	
	protected $db;

	/**
	 * Init resources
	 * Set renderer for index / detail / skeleton action
	 * @return void
	 */
	public function preDispatch()
	{
		$this->db = Shopware()->Db();
		if(!in_array($this->Request()->getActionName(), array('index', 'detail', 'skeleton'))) {
			Shopware()->Plugins()->Controller()->ViewRenderer()->setNoRender();
		}
	}

	/**
	 * Not required yet
	 * @return void
	 */
	public function indexAction()
	{
		
	}

	/**
	 * Load window properties from skeleton.tpl
	 * @return void
	 */
	public function skeletonAction ()
	{
		
	}

	/**
	 * Load plugin detail mask
	 * @return void
	 */
	public function detailAction()
	{
		$id = (int) $this->Request()->id;
		$select = $this->db->select()->from('s_core_plugins')->where('id=?', $id);
		
		$row = $this->db->fetchRow($select);
		
		$this->View()->callback = $this->Request()->callback;
		$this->View()->plugin = $row;
		
		$config = array();
		$select = $this->db->select()->from('s_core_plugin_configs')
			->where('pluginID=?', $id)
			->where('localeID=?', 1);
		$result = $this->db->query($select);
		while ($row = $result->fetch()) {
			$config[$row['shopID']][$row['name']] = unserialize($row['value']);
		}
		$this->View()->plugin_config = $config;
		
		$plugin = $this->getPluginById($id);
		$this->View()->form = $plugin->Form();
		
		$this->View()->shops = $this->getShops();
	}

	/**
	 * Save plugin details / configuration
	 * @return void
	 */
	public function saveDetailAction()
	{
		$pluginId = (int) $this->Request()->id;
		$config = $this->Request()->getPost('config');
		$plugin = $this->getPluginById($pluginId);
		
		foreach ($config as $shopId => $shop_config) {
			foreach ($shop_config as $config_name => $config_value)
			{
				$sql = '
		    	 	INSERT INTO `s_core_plugin_configs` (
						`name`,
						`value`,
						`pluginID`,
						`localeID`,
						`shopID`
					) VALUES (
						?, ?, ?, ?, ?
					) ON DUPLICATE KEY UPDATE
						value=VALUES(value)
		    	';
				$result = $this->db->query($sql, array(
		    		$config_name,
		    		serialize($config_value),
		    		$pluginId,
		    		1,
		    		$shopId,
		    	));
			}
		}
				
		$active = $this->Request()->getParam('active');
		if($active!==null) {
			if(empty($active)) {
				$plugin->disable();
			} else {
				$plugin->enable();
			}
		}
	}

	/**
	 * Get plugin list to display in grid
	 * @return void
	 */
	public function getListAction()
	{
		$this->refreshList();
		$select = $this->db->select()->from('s_core_plugins', array(new Zend_Db_Expr('SQL_CALC_FOUND_ROWS id as fake_column'),'*'))->order(array('added DESC','name'));
		
		$limit = $this->Request()->getParam('limit', 25);
		$start = $this->Request()->getParam('start', 0);
		
		$select->limit($limit, $start);
		
		if($this->Request()->getParam('path')) {
			$path = explode('/', $this->Request()->getParam('path'));
			if(!empty($path[0])) {
				$select->where('namespace=?', $path[1]);
			}
			if(!empty($path[1])) {
				$select->where('source=?', $path[0]);
			}
		} elseif($this->Request()->getParam('search')) {
			$search = trim($this->Request()->getParam('search'));
			$search = '%'.$search.'%';
			$search = $this->db->quote($search); 
			
			$select->where('namespace LIKE '.$search);
			$select->orWhere('name LIKE '.$search);
			$select->orWhere('label LIKE '.$search);
			$select->orWhere('source LIKE '.$search);
			$select->orWhere('autor LIKE '.$search);
		}
		
		$rows = $this->db->fetchAll($select);
		
		foreach ($rows as $key=>$row) {
			$rows[$key]['path'] = $row['namespace'].'/'.$row['source'].'/'.$row['name'];
			$rows[$key]['active'] = (bool) $row['active'];
			$rows[$key]['update_date'] = empty($row['update_date']) ? null : strtotime($row['update_date']);
			$rows[$key]['installation_date'] = empty($row['installation_date']) ? null : strtotime($row['installation_date']);
		}
		
		$count = $this->db->fetchOne('select FOUND_ROWS()');

		echo Zend_Json::encode(array('data'=>$rows, 'count'=>$count));
	}

	/**
	 * Get plugin list for delete combo (deprecated)
	 * @return void
	 */
	public function getDeleteListAction()
	{
		$list = array();
		
		$path = Shopware()->AppPath().'Plugins/Community';
		foreach (new DirectoryIterator($path) as $dir) {
		    if($dir->isDot()||strpos($dir->getFilename(), '.')===0){
		    	continue;
		    }
		    
		    $path2 = $dir->getPathname();
		    $file = $dir->getFilename();

		    if(in_array($file, array('Backend', 'Core', 'Frontend'))) {
		    	foreach (new DirectoryIterator($path2) as $dir2) {
		    		if($dir2->isDot()||strpos($dir2->getFilename(), '.')===0){
		    			continue;
		    		}

		    		$path3 = $dir2->getPathname();
		    		$file2 = $dir2->getFilename();
		    		
		    		if($this->isInstalled('Community', $file, $file2)) {
		    			continue;
		    		}

		    		$list[] = array('name'=>$file.'/'.$file2,'path'=>$path3);
		    	}
		    } else {
		    	$list[] = array('name'=>$file,'path'=>$path2);
		    }
		}

		echo Zend_Json::encode(array('data'=>$list, 'count'=>count($list)));
	}

	/**
	 * Get plugin scopes for tree menu
	 * @return void
	 */
	public function getTreeAction()
	{
		$node = $this->Request()->node;
		
		$list = array();
		
		if(empty($node)) {
			$this->refreshList();
			/*
			$select = $this->db->select()
				->distinct()
				->from('s_core_plugins', array('namespace as id', 'namespace as text'))
				->order(array('namespace'))
			;
			$list = $this->db->fetchAll($select);
			*/
			$list = array(
					array('id'=>'Default', 'text'=>'Core Plugins', 'leaf'=>false, 'expended'=>true),
					array('id'=>'Community', 'text'=>'CommunityStore', 'leaf'=>false, 'expended'=>true),
					array('id'=>'Local', 'text'=>'Lokale Plugins', 'leaf'=>false, 'expended'=>true));
					
			foreach ($list as $key=>$row) {
				$list[$key]['leaf'] = false;
				$list[$key]['expanded'] = true;
				$select = $this->db->select()
				->distinct()
				->from('s_core_plugins', array('namespace as id', 'namespace as text'))
				->order(array('namespace'));
				$children = $this->db->fetchAll($select);
				foreach ($children as &$child){
					$child["leaf"] = true;
					$list[$key]['expanded'] = false;
					$child["id"] = $list[$key]["id"]."/".$child["id"];
				}
				
				$list[$key]['children'] = $children;
			}
			
		} else {
			/*
			$select = $this->db->select()
				->from('s_core_plugins', array('id', 'name as text', 'active'))
				->where('namespace=?', $node)
				->order(array('name'))
			;
			$list = $this->db->fetchAll($select);
			foreach ($list as $key=>$row) {
				$list[$key]['name'] = utf8_encode($row['name']);
				$list[$key]['active'] = (bool) $row['active'];
				if(!$list[$key]['active']) {
					$list[$key]['cls'] = 'inactive';
				}
				$list[$key]['leaf'] = true;
			}
			*/
		}
		
		echo Zend_Json::encode($list);
	}

	/**
	 * Enable a certain plugin
	 * @return void
	 */
	public function enableAction()
	{
		$id = (int) $this->Request()->id;
		$plugin = $this->getPluginById($id);
		
		$result = $plugin->enable();

		echo Zend_Json::encode(array('success'=>$result));		
	}

	/**
	 * Disable a certain plugin
	 * @return void
	 */
	public function disableAction()
	{
		$id = (int) $this->Request()->id;
		$plugin = $this->getPluginById($id);
		
		$result = $plugin->disable();

		echo Zend_Json::encode(array('success'=>$result));
	}

	/**
	 * Do plugin installation
	 * @return void
	 */
	public function installAction()
	{		
		$id = (int) $this->Request()->id;
		$plugin = $this->getPluginById($id);
						
		
		try {
			$result = $plugin->install();
			if($result) {
				$data = array(
					'installation_date' => new Zend_Db_Expr('NOW()'),
					'update_date' => new Zend_Db_Expr('NOW()'),
				);
				$this->db->update('s_core_plugins', $data, array('id=?'=>$id));
			}
		} catch (Exception $e) {
			$message = $e->getMessage();
		}
		
		echo Zend_Json::encode(array('success'=>isset($message)?false:$result, 'message'=>isset($message)?$message:''));
	}

	/**
	 * Remove a certain plugin
	 * @return void
	 */
	public function uninstallAction()
	{
		$id = (int) $this->Request()->id;
		$plugin = $this->getPluginById($id);
		$result = $plugin->disable();
		$result = $plugin->uninstall();
		if($result) {
			$data = array(
				'installation_date' => NULL,
				'update_date' =>  NULL
			);
			$this->db->update('s_core_plugins', $data, array('id=?'=>$id));
		}
		
		echo Zend_Json::encode(array('success'=>$result));
	}

	/**
	 * Get all subshops
	 * @return
	 */
	public function getShops()
	{
		$sql = '
			SELECT `id`, `name`, `default`
			FROM `s_core_multilanguage`
			ORDER BY `default` DESC, `name`
		';
		$shops = $this->db->fetchAll($sql);
		return $shops;
	}

	/**
	 * Check if a certain plugin is already installed
	 * @param  $source
	 * @param  $namespace
	 * @param  $name
	 * @return bool
	 */
	public function isInstalled($source, $namespace, $name)
	{
		$select = $this->db->select()
			->from('s_core_plugins', 'id')
			->where('installation_date IS NOT NULL', $source)
			->where('source=?', $source)
			->where('name=?', $name)
			->where('namespace=?', $namespace);
		$id = $this->db->fetchOne($select);
		return !empty($id);
	}

	/**
	 * Get a certain plugin by id
	 * @param  $id
	 * @return bool
	 */
	public function getPluginById($id)
	{
		$select = $this->db->select()->from('s_core_plugins')->where('id=?', $id);
		$plugin =  $this->db->fetchRow($select);
		if(empty($plugin)) {
			return false;
		}
		$namespace = Shopware()->Plugins()->getNamespace($plugin['namespace']);
		if(empty($namespace)) {
			return false;
		}
		$plugin = $namespace->getPlugin($plugin['name']);
		if(empty($namespace)) {
			return false;
		}
		return $plugin;
	}

	/**
	 * Reload plugin list
	 * @return void
	 */
	public function refreshList()
	{
		foreach (Shopware()->Plugins() as $namespace_name => $namespace) {
			$namespace->loadAll();
			foreach ($namespace as $plugin_name => $plugin) {
				if(!$plugin instanceof Shopware_Components_Plugin_Bootstrap) {
					continue;
				}
				$info = $plugin->getInfo();
				$select = $this->db->select()
					->from('s_core_plugins', 'id')
					->where('name=?', $plugin_name)
					->where('namespace=?', $namespace_name);
				$id = $this->db->fetchOne($select);
				
				$data = array(
					'namespace' => $namespace_name,
					'name' => $plugin_name,
					'label' => !empty($info['label']) ? $info['label'] : $plugin_name,
					'version' => !empty($info['version']) ? $info['version'] : $plugin->getVersion(),
					'autor' => isset($info['autor']) ? $info['autor'] : 'shopware AG',
					'copyright' => isset($info['copyright']) ? $info['copyright'] : 'shopware AG',
					'description' => isset($info['description']) ? $info['description'] : '',
					'license' => isset($info['license']) ? $info['license'] : '',
					'support' => isset($info['support']) ? $info['support'] : '',
					'link' => isset($info['link']) ? $info['link'] : '',
					'changes' => isset($info['changes']) ? $info['changes'] : '',
					'source' => !empty($info['source']) ? $info['source'] : $plugin->getSource(),
				);
								
				if(empty($id)) {
					$data["added"] = new Zend_Db_Expr('NOW()');
					Shopware()->Db()->insert('s_core_plugins', $data);
				} else {
					$where = array('name=?'=>$plugin_name, 'namespace=?'=>$namespace_name);
					Shopware()->Db()->update('s_core_plugins', $data, $where);
				}
			}
		}
	}

	/**
	 * Upload a new plugin
	 * @return void
	 */
	public function uploadAction()
	{
		$upload = new Zend_File_Transfer_Adapter_Http();
		
		try {
			$upload->setDestination(Shopware()->DocPath().'files/downloads');
			$upload->addValidator('Extension', false, 'zip');
			if (!$upload->receive()) {
				$message = $upload->getMessages();
				$message = implode("\n", $message);
			} else {
				if (!$this->decompressFile($upload->getFileName())){
					echo htmlspecialchars(Zend_Json::encode(array('success'=>false, 'message'=>"Stellen Sie sicher, dass Schreibrechte auf die Ordner engine/Shopware/Plugins/Community [Frontend|Backend|Core) gesetzt sind.")));
					exit;
				}
			}
		} catch (Exception $e) {
			$message = $e->getMessage();
		}
		
		if($upload->getFileName()) {
			@unlink($upload->getFileName());
		}
		
		echo htmlspecialchars(Zend_Json::encode(array('success'=>isset($message)?false:true, 'message'=>isset($message)?$message:'')));
	}

	/**
	 * Decompress plugin zip
	 * @param  $file
	 * @return bool
	 */
	public function decompressFile($file)
	{
		$target = Shopware()->AppPath().'Plugins/Community';
		if(!is_dir($target)||!is_writable($target)) {
			return false;
		}
		$filter = new Zend_Filter_Decompress( array(
			'adapter' => 'Zip',
			'options' => array(
				'target' => $target
		)));
		$filter->filter($file);
		return true;
	}

	/**
	 * Direct download of a plugin zip
	 * @return void
	 */
	public function downloadAction()
	{
		$url = $this->Request()->link;
		$tmp = @tempnam(Shopware()->DocPath().'files/downloads', 'plugins');
		
		try {
			$client = new Zend_Http_Client($url, array(
				'timeout' => 10,
				'useragent' => 'Shopware/'.Shopware()->Config()->Version
			));
			$client->setStream($tmp);
			$client->request('GET');
			
			$this->decompressFile($tmp);
			
		} catch (Exception $e) {
			$message = $e->getMessage();
		}
		
		@unlink($tmp);
		 
		echo Zend_Json::encode(array('success'=>isset($message)?false:true, 'message'=>isset($message)?$message:''));
	}

	/**
	 * Delete plugin directory structure
	 * @return
	 */
	public function deleteAction()
	{
		$deletePath = $this->Request()->path;
		if(!file_exists($deletePath)) {
			return;
		}

		if(is_dir($deletePath)) {
			$dirIterator = new RecursiveDirectoryIterator($deletePath);
	        $iterator = new RecursiveIteratorIterator($dirIterator, RecursiveIteratorIterator::CHILD_FIRST);
	        foreach ($iterator as $file) {
	        	$path = $file->getPathname();
	            if (strpos($path, '.svn') !== false) {
	            	continue;
	            }
	            if ($file->isDir()) {
	                if (!$iterator->isDot()) {
	                    @rmdir($path);
	                } 
	            } else {
	                @unlink($path);
	            }
	        }
	        @rmdir($deletePath);
		} elseif(is_file($deletePath)) {
			@unlink($deletePath);
		}
		 
		echo Zend_Json::encode(array('success'=>isset($message)?false:true, 'message'=>isset($message)?$message:''));
	}
}