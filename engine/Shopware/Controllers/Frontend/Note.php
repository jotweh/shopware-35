<?php
class Shopware_Controllers_Frontend_Note extends Enlight_Controller_Action
{
	public function indexAction(){
		$this->View()->sNotes = Shopware()->Modules()->Basket()->sGetNotes();
	}
	
	public function deleteAction(){
		if (!empty($this->request()->sDelete)){
			Shopware()->Modules()->Basket()->sDeleteNote($this->request()->sDelete);
		}
		$this->forward("Index");
	}
	
	public function addAction(){
		$ordernumber = $this->request()->ordernumber;
		if (!empty($ordernumber)){
			$articleID = Shopware()->Modules()->Articles()->sGetArticleIdByOrderNumber($ordernumber);
			$articleName = Shopware()->Modules()->Articles()->sGetArticleNameByOrderNumber($ordernumber);
			$this->View()->sArticleName = $articleName;
			if (!empty($articleID)){
				Shopware()->Modules()->Basket()->sAddNote($articleID, $articleName, $ordernumber);
			}
		}
		$this->forward("Index");
	}
}