<?php

class sViewportNote{
	var $sSYSTEM;
	
	function sRender(){
		
		/*
		if (!$this->sSYSTEM->_COOKIE["sUniqueID"]){ 
			$templates = array(
			"sContainer"=>"/error/error.tpl",
			"sContainerRight"=>""
			);
		
			$variables = array("sError"=>$this->sSYSTEM->sCONFIG['sErrors']['sErrorCookiesDisabled']);
			return array("templates"=>$templates,"variables"=>$variables);
		}
		*/
		
		
		// Add article to basket
		if ($this->sSYSTEM->_GET["sAdd"]){
			
			// Cross-Selling
			$articleID = $this->sSYSTEM->sMODULES['sArticles']->sGetArticleIdByOrderNumber($this->sSYSTEM->_GET["sAdd"]);
			$articleName = $this->sSYSTEM->sMODULES['sArticles']->sGetArticleNameByOrderNumber($this->sSYSTEM->_GET["sAdd"]);
			
			$variables["sArticleName"] = $articleName;
			
			
			if ($articleID){
				$articleName = addslashes($articleName);
				 $this->sSYSTEM->sMODULES['sBasket']->sAddNote($articleID, $articleName, $this->sSYSTEM->_GET["sAdd"]);
			}
			
		
		}
		
		// Delete article from basket
		if ($this->sSYSTEM->_GET["sDelete"]){
			$this->sSYSTEM->sMODULES['sBasket']->sDeleteNote($this->sSYSTEM->_GET["sDelete"]);
		}
		
		
		
		$variables["sNotes"] = $this->sSYSTEM->sMODULES['sBasket']->sGetNotes();
		
		
		$templates = array("sContainer"=>"/basket/note_middle.tpl","sContainerRight"=>"");
		

		
		$variables["sBreadcrumb"] = array(0=>array("name"=>$this->sSYSTEM->sCONFIG['sViewports'][$this->sSYSTEM->_GET["sViewport"]]["name"]));
			
		
		
		return array("templates"=>$templates,"variables"=>$variables);
	}
}
?>