<?php
/**
 * Eos payment controller
 * 
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Heiner Lohaus
 * @package Shopware
 * @subpackage Controllers
 */
class Shopware_Controllers_Backend_PaymentEos extends Shopware_Controllers_Backend_ExtJs
{	
	/**
	 * Pre dispatch action method
	 */
	public function preDispatch()
	{
		if(!in_array($this->Request()->getActionName(), array('index', 'skeleton', 'load'))) {
			Shopware()->Plugins()->Controller()->ViewRenderer()->setNoRender();
		}
	}
	
	/**
	 * Skeleton action method
	 */
	public function skeletonAction ()
	{
	}
	
	/**
	 * Index action method
	 */
	public function indexAction()
	{
	}
	
	/**
	 * Status action method
	 */
	public function statusAction()
	{
		$eosStatus = array(
			0 => 'Offen',
			1 => 'Reserviert',
			2 => 'Gebucht',
			3 => 'Storniert',
			4 => 'Reservierung fehlgeschlagen',
			5 => 'Buchung abgebrochen',
			6 => 'Gutschrift',
			7 => 'Status unbekannt',
			8 => 'Geldeingang',
			9 => 'Geldauszahlung',
			10 => 'Buchung fehlgeschlagen',
			11 => '3-D Secure fehlgeschlagen',
			12 => 'User Accepted',
			13 => 'InitFolgezahlung',
			14 => 'Rückbuchung',
			15 => 'Warte auf Zahlungseingang'
		);
		$data = array();
		foreach ($eosStatus as $statusId => $status) {
			$data[] = array(
				'id' => $statusId,
				'name' => $status
			);
		}
		echo Zend_Json::encode(array('data'=>$data, 'total'=>count($data), 'success'=>true));
	}
	
	/**
	 * Index action method
	 */
	public function listAction()
	{
		$limit = $this->Request()->getParam('limit', 20);
		$start = $this->Request()->getParam('start', 0);
		
		if($sort = $this->Request()->getParam('sort')) {
			$sort = substr($sort, 1, -1);
			$sort = Zend_Json::decode($sort);
		}
		$direction = empty($sort['direction']) || $sort['direction'] == 'DESC' ? 'DESC' : 'ASC';
		$property = empty($sort['property']) ? 'added' : $sort['property'];
		
		$select = Shopware()->Db()
			->select()
			->from(array('p' => 's_plugin_payment_eos'), array(
				new Zend_Db_Expr('SQL_CALC_FOUND_ROWS p.id'), '*'
			))
			->joinLeft(
				array('o' => 's_order'),
				'o.transactionID = p.transactionID AND o.userID = p.userID',
				array('orderID' => 'id', 'clearedID' => 'cleared')
			)
			->joinLeft(
				array('b' => 's_order_billingaddress'),
				'b.orderID = o.id',
				new Zend_Db_Expr("IF(b.company='', CONCAT(b.firstname, ' ', b.lastname), b.company) as customer")
			)
			->order(array($property . ' ' . $direction))
			->limit($limit, $start);
			
		if($search = $this->Request()->getParam('search')) {
			$search = trim($search);
			$search = '%'.$search.'%';
			$search = $this->db->quote($search); 
			
			$select->where('transactionID LIKE ' . $search)
				->orWhere('reference LIKE ' . $search)
				->orWhere('customer LIKE ' . $search);
		}
		
		$rows = Shopware()->Db()->fetchAll($select);
		
		foreach ($rows as $key=>$row) {			
			if(function_exists('mb_convert_encoding')) {
				$rows[$key]['customer'] = mb_convert_encoding($row['customer'], 'UTF-8', 'HTML-ENTITIES');
			} else {
				$rows[$key]['customer'] = utf8_encode(html_entity_decode($row['customer'], ENT_NOQUOTES));
			}
		}
		
		$total = Shopware()->Db()->fetchOne('SELECT FOUND_ROWS()');

		echo Zend_Json::encode(array('data'=>$rows, 'total'=>$total, 'success'=>true));
	}
}