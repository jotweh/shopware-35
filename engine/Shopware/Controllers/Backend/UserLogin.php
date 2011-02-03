<?php
class Shopware_Controllers_Backend_UserLogin extends Enlight_Controller_Action
{
	public function init()
	{
		Shopware()->Plugins()->Controller()->ViewRenderer()->setNoRender();
	}
	
	public function indexAction()
	{
		$userID = $this->request()->id;
		$sql = 'SELECT id, email, password, customergroup, subshopID FROM s_user WHERE id = ?';
		$user = Shopware()->Db()->fetchRow($sql, array($userID));
		
		if(!empty($user['email'])) {
			$shop = new Shopware_Models_Shop($user['subshopID']);
			$shop->registerResources(Shopware()->Bootstrap());
			
			Shopware()->Session()->Shop = $shop;
			Shopware()->Session()->Admin = true;
			
			Shopware()->Db()->query('UPDATE s_user SET lastlogin=NOW(), sessionID=? WHERE id=?', array(Shopware()->SessionID(), $userID));
			Shopware()->Session()->sUserMail = $user['email'];
			Shopware()->Session()->sUserPassword = $user['password'];
			Shopware()->Session()->sUserId = $user['id'];
			Shopware()->Modules()->Admin()->sCheckUser();
		}
		echo Shopware()->SessionID();
	}
	
	public function previewDetailAction(){
		
		if (empty($this->Request()->id) || empty($this->Request()->article)){
			return;
		}
		
		$shopId = (int) $this->Request()->id;
		$articleId = (int) $this->Request()->article;
		
		$shop = new Shopware_Models_Shop($this->Request()->id);
		Shopware()->Bootstrap()->registerResource('Shop', $shop);
		$shop->registerResources(Shopware()->Bootstrap());
		
		Shopware()->Session()->Shop = $shop;
		Shopware()->Session()->Admin = true;
		
		$session = Shopware()->SessionID();
		
		$url = $this->Front()->Router()->assemble(array('sViewport'=>'detail', 'sArticle'=>$articleId, 'module'=>'frontend', 'appendSession'=>true));
		
		$this->redirect($url);
	}
}