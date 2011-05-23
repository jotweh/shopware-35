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
			$sort = Zend_Json::decode($sort);
			$sort = current($sort);
		}
		$direction = empty($sort['direction']) || $sort['direction'] == 'DESC' ? 'DESC' : 'ASC';
		$property = empty($sort['property']) ? 'added' : $sort['property'];
		
		if($filter = $this->Request()->getParam('filter')) {
			$filter = Zend_Json::decode($filter);
			foreach ($filter as $value) {
				if(empty($value['property']) || empty($value['value'])) {
					continue;
				}
				if($value['property'] == 'search') {
					$this->Request()->setParam('search', $value['value']);
				}
			}
		}
		
		$select = Shopware()->Db()
			->select()
			->from(array('p' => 's_plugin_payment_eos'), array(
				new Zend_Db_Expr('SQL_CALC_FOUND_ROWS p.id'), '*'
			))
			->joinLeft(
				array('o' => 's_order'),
				'o.transactionID = p.transactionID AND o.userID = p.userID',
				array('
					orderID' => 'id', 'clearedID' => 'cleared',
					'order_date' => 'ordertime', 'order_number' => 'ordernumber',
				)
			)
			->joinLeft(
				array('a' => 's_core_paymentmeans'),
				'a.name =  p.payment_key COLLATE latin1_german1_ci',
				array(
					'payment_description' => 'a.description'
				)
			)
			->joinLeft(
				array('u' => 's_user_billingaddress'),
				'u.userID = p.userID',
				array()
			)
			->joinLeft(
				array('b' => 's_order_billingaddress'),
				'b.orderID = o.id',
				new Zend_Db_Expr("
					IF(b.id IS NULL,
						IF(u.company='', CONCAT(u.firstname, ' ', u.lastname), u.company),
						IF(b.company='', CONCAT(b.firstname, ' ', b.lastname), b.company)
					) as customer
				")
			)
			->where('`werbecode` IS NOT NULL')
			->order(array($property . ' ' . $direction))
			->limit($limit, $start);

		if($search = $this->Request()->getParam('search')) {
			$search = trim($search);
			$search = '%'.$search.'%';
			$search = Shopware()->Db()->quote($search);
						
			$select->where('p.transactionID LIKE ' . $search)
				->orWhere('p.reference LIKE ' . $search)
				->orWhere('o.ordernumber LIKE ' . $search)
				->orWhere('b.lastname LIKE ' . $search)
				->orWhere('u.lastname LIKE ' . $search)
				->orWhere('b.company LIKE ' . $search)
				->orWhere('u.company LIKE ' . $search);
		}
		
		$rows = Shopware()->Db()->fetchAll($select);
		
		foreach ($rows as $key=>$row) {			
			if(function_exists('mb_convert_encoding')) {
				$rows[$key]['customer'] = mb_convert_encoding($row['customer'], 'UTF-8', 'HTML-ENTITIES');
				$rows[$key]['fail_message'] = mb_convert_encoding($row['fail_message'], 'UTF-8', 'HTML-ENTITIES');
			} else {
				$rows[$key]['customer'] = utf8_encode(html_entity_decode($row['customer'], ENT_NOQUOTES));
				$rows[$key]['fail_message'] = utf8_encode(html_entity_decode($row['fail_message'], ENT_NOQUOTES));
			}
			
			$rows[$key]['amount_format'] = Shopware()->Currency()->toCurrency($row['amount'], array('currency' => $row['currency']));
			$rows[$key]['book_amount_format'] = Shopware()->Currency()->toCurrency($row['book_amount'], array('currency' => $row['currency']));
		}
		
		$total = Shopware()->Db()->fetchOne('SELECT FOUND_ROWS()');

		echo Zend_Json::encode(array('data'=>$rows, 'total'=>$total, 'success'=>true));
	}
}