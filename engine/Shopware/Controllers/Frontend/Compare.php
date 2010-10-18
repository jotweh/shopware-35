<?php
class Shopware_Controllers_Frontend_Compare extends Enlight_Controller_Action
{
	protected $articles;
	
	public function init()
	{
		$this->articles = Shopware()->Modules()->Articles();
	}
	
	public function indexAction()
	{
		$this->View()->sComparisons = $this->articles->sGetComparisons();
	}
	
	public function addArticleAction()
	{
		if($this->Request()->isPost())
		{
			$this->View()->sCompareAddResult = $this->articles->sAddComparison($this->request->getParam('articleID'));
		}
		$this->View()->sComparisons = $this->articles->sGetComparisons();
	}
	
	public function deleteArticleAction()
	{
		if($this->Request()->isPost())
		{
			$this->articles->sDeleteComparison($this->request->getParam('articleID'));
		}
		$this->forward('index');
	}
	
	public function deleteAllAction()
	{
		$this->articles->sDeleteComparisons();
		$this->forward('index');
	}
	
	public function getListAction()
	{
		$this->forward('index');
	}
	
	public function overlayAction()
	{
		$this->View()->sComparisonsList = $this->articles->sGetComparisonList();
	}
}