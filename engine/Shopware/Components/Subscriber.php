<?php
class Shopware_Components_Subscriber extends Enlight_Class implements Enlight_Event_EventSubscriber, Enlight_Hook_HookSubscriber
{
	protected $db;
	
	public function init()
	{
		$this->db = Shopware()->Db();
	}
    /**
     * Returns an array of events that this subscriber listens 
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
    	$sql = '
    	 	SELECT ce.subscribe as event, ce.listener, ce.position, ce.pluginID, cp.namespace as plugin_namespace, cp.name as plugin_name
    	 	FROM s_core_subscribes ce
    	 	LEFT JOIN s_core_plugins cp
    	 	ON cp.id=ce.pluginID
    	 	WHERE (cp.id IS NULL OR cp.active=1)
    	 	AND ce.type=0
    	 	ORDER BY event, position
    	 ';
    	 $rows = $this->db->fetchAll($sql);
    	 
    	 $events = array();
    	 
    	 if(!empty($rows))
    	 foreach ($rows as $row)
    	 {
    	 	$events[] = new Enlight_Event_EventHandler(
    	 		$row['event'],
    	 		$row['listener'],
    	 		$row['position'],
    	 		$row['pluginID']
    	 	);
    	 }
    	     	 
    	 return $events;
    }
    
    /**
     * Returns an array of events that this subscriber listens 
     *
     * @return array
     */
    public function getSubscribedHooks()
    {
    	 $sql = '
    	 	SELECT ce.subscribe, ce.listener as hook, ce.position, ce.type, ce.pluginID, cp.namespace as plugin_namespace, cp.name as plugin_name
    	 	FROM s_core_subscribes ce
    	 	LEFT JOIN s_core_plugins cp
    	 	ON cp.id=ce.pluginID
    	 	WHERE (cp.id IS NULL OR cp.active=1)
    	 	AND ce.type IN (1,2,3)
    	 	ORDER BY position
    	 ';
    	 $rows = $this->db->fetchAll($sql);
    	 
    	 $hooks = array();
    	 
    	 if(!empty($rows))
    	 foreach ($rows as $row)
    	 {
    	 	list($row['class'], $row['method']) = explode('::', $row['subscribe']);
    	 	
    	 	$hooks[] = new Enlight_Hook_HookHandler(
    	 		$row['class'],
    	 		$row['method'],
    	 		$row['hook'],
    	 		$row['type'],
    	 		$row['position'],
    	 		$row['pluginID']
    	 	);
    	 }
    	 
    	 return $hooks;
    }
    
    public function subscribeEvent(Enlight_Event_EventHandler $handler)
    {
    	return $this->saveSubscribe($handler);
    }
    
    public function subscribeHook(Enlight_Hook_HookHandler $handler)
    {
    	return $this->saveSubscribe($handler);
    }
    
    public function unsubscribeEvent(Enlight_Event_EventHandler $handler)
    {
    	return $this->deleteSubscribe($handler);
    }
    
    public function unsubscribeHook(Enlight_Hook_HookHandler $handler)
    {
    	return $this->deleteSubscribe($handler);
    }
    
    public function unsubscribeEvents($where=array())
    {
    	$where['type'] = 0;
     	return $this->deleteSubscribes($where);
    }
    
    public function unsubscribeHooks($where=array())
    {
    	if(!isset($where['type'])) {
    		$where['type'] = array(1,2,3);
    	}
    	return $this->deleteSubscribes($where);
    }
    
    protected function deleteSubscribes($values)
    {
    	$where = array();
    	if(isset($values['type'])) {
    		$where[] = $this->db->quoteInto('type IN (?)', $values['type']);
    	}
    	if(isset($values['listener'])) {
    		$where[] = $this->db->quoteInto('listener=?', $values['listener']);
    	}
    	if(isset($values['position'])) {
    		$where[] = $this->db->quoteInto('position=?', $values['position']);
    	}
    	if(isset($values['pluginID'])) {
    		$where[] = $this->db->quoteInto('pluginID=?', $values['pluginID']);
    	}
    	if(!$where) {
    		return false;
    	}
    	$where = implode(' AND ', $where);
    	$sql = 'DELETE FROM `s_core_subscribes` WHERE '.$where;
    	$result = $this->db->query($sql);
    	return (bool) $result;
    }
        
    protected function deleteSubscribe($handler)
    {
    	switch (get_class($handler))
    	{
    		case 'Enlight_Event_EventHandler':
    			$type = 0;
    			break;
    		case 'Enlight_Hook_HookHandler':
    			$type = $handler->getType();
    			break;
    		default:
    			return false;
    	}
    	$sql = '
    	 	DELETE FROM `s_core_subscribes`
    	 	WHERE `subscribe`=?,
			AND `type`=?,
			AND `listener`=?,
			AND `position`=?,
			AND `pluginID`=?
    	';
    	$result = $this->db->query($sql, array(
    		$handler->getName(),
    		$type,
    		$handler->getListener(),
    		$handler->getPosition(),
    		$handler->getPlugin(),
    	));
    	return (bool) $result;
    }
        
    protected function saveSubscribe($handler)
    {
    	switch (get_class($handler))
    	{
    		case 'Enlight_Event_EventHandler':
    			$type = 0;
    			break;
    		case 'Enlight_Hook_HookHandler':
    			$type = $handler->getType();
    			break;
    		default:
    			return false;
    	}
    	$sql = '
    	 	INSERT INTO `s_core_subscribes` (
    	 		`subscribe`,
				`type`,
				`listener`,
				`position`,
				`pluginID`
			) VALUES (
				?, ?, ?, ?, ?
			) ON DUPLICATE KEY UPDATE
				position=VALUES(position),
				pluginID=VALUES(pluginID)
    	';
		$result = $this->db->query($sql, array(
    		$handler->getName(),
    		$type,
    		$handler->getListener(),
    		$handler->getPosition(),
    		$handler->getPlugin(),
    	));
    	return (bool) $result;
    }
}