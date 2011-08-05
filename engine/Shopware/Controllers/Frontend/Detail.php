<?php
/**
 * Product detail controller
 * 
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Heiner Lohaus
 * @package Shopware
 * @subpackage Controllers
 */
class Shopware_Controllers_Frontend_Detail extends Enlight_Controller_Action
{	
	/**
	 * Pre dispatch method
	 */
	public function preDispatch()
	{
		return;
		if($this->Request()->getPost('group')
			|| Shopware()->Shop()->get('defaultcustomergroup')!=Shopware()->System()->sUSERGROUP
			|| Shopware()->Shop()->get('defaultcurrency')!=Shopware()->Currency()->getId()
		) {
			return;
		}
		$this->View()->setCaching(true);
		$this->View()->addCacheID(array(
			'frontend',
			'detail',
			(int) $this->Request()->sArticle,
			(int) $this->Request()->sCategory
		));
	}
	
	/**
	 * Error action method
	 * 
	 * Read similar products
	 */
	public function errorAction()
	{
		$this->View()->sRelatedArticles = Shopware()->Modules()->Marketing()->sGetSimilarArticles($this->Request()->sArticle, 4);
	}
	
	/**
	 * Index action method
	 * 
	 * Read product details
	 */
	public function indexAction()
	{
		$id = (int) $this->Request()->sArticle;
		if (empty($id)) {
			return $this->forward('error');
		}
		
		$this->view->assign('sAction', isset($this->view->sAction) ? $this->view->sAction : 'index', true);
		$this->view->assign('sErrorFlag', isset($this->view->sErrorFlag) ? $this->view->sErrorFlag : array(), true);
		$this->view->assign('sFormData', isset($this->view->sFormData) ? $this->view->sFormData : array(), true);
						
		if(!$this->view->isCached()) {
			$article = Shopware()->Modules()->Articles()->sGetArticleById($id);
			if (empty($article) || empty($article["articleName"])) {
				return $this->forward('error');
			}
						
			$category = (int) Shopware()->System()->_GET['sCategory'];
			
			$article = Shopware()->Modules()->Articles()->sGetConfiguratorImage($article);
			$article['sBundles'] = Shopware()->Modules()->Articles()->sGetArticleBundlesByArticleID($id);
					
			if (!empty(Shopware()->Config()->InquiryValue)) {
				$this->View()->sInquiry = $this->Front()->Router()->assemble(array(
					'sViewport'=>'support',
					'sFid'=>Shopware()->Config()->InquiryID,
					'sInquiry'=>'detail',
					'sOrdernumber'=>$article['ordernumber']
				));
			}
			
			if (!empty(Shopware()->Session()->sUserId) &&  empty($this->Request()->sVoteName)){
				$userData = Shopware()->Modules()->Admin()->sGetUserData();
				Shopware()->System()->_POST['sVoteName'] = $userData['billingaddress']['firstname'].' '.$userData['billingaddress']['lastname'];
			}
			
			if (!empty($category)){	
				$breadcrumb = array_reverse(Shopware()->Modules()->sCategories()->sGetCategoriesByParent($category));
				$categoryInfo = end($breadcrumb);
			} else {
				$breadcrumb = array();
				$categoryInfo = null;
			}
			
			$breadcrumb[] = array(
				'link' => $article['linkDetails'],
				'name' => $article['articleName']
			);
			
			$this->View()->sBreadcrumb = $breadcrumb;
			$this->View()->sCategoryInfo = $categoryInfo;
			$this->View()->sArticle = $article;
			$this->View()->rand = md5(uniqid(rand()));
		}
		
		if(!empty($article['template'])) {
			$this->View()->loadTemplate('frontend/detail/' . $article['template']);
		} elseif(!empty($article['mode'])) {
			$this->View()->loadTemplate('frontend/blog/detail.tpl');
		}
	}
	
	/**
	 * Rating action method
	 * 
	 * Save an check product rating
	 */
	public function ratingAction()
	{
		$id = (int) $this->Request()->sArticle;
		if (empty($id)) {
			return $this->forward('error');
		}
		
		$article = Shopware()->Modules()->Articles()->sGetArticleNameByArticleId($id);
		if (empty($article)) {
			return $this->forward('error');
		}
		
		$voteConfirmed = false;
		
		if ($hash = $this->Request()->sConfirmation){
			$getVote = Shopware()->Db()->fetchRow('
				SELECT * FROM s_core_optin WHERE hash = ?
			', array($hash));
			if (!empty($getVote['data'])){
				Shopware()->System()->_POST = unserialize($getVote['data']);
				$voteConfirmed = true;
				Shopware()->Db()->query('DELETE FROM s_core_optin WHERE hash = ?', array($hash));
			}
		}

		if (empty(Shopware()->System()->_POST['sVoteName'])) {
			$sErrorFlag['sVoteName'] = true;
		}
		if (empty(Shopware()->System()->_POST['sVoteSummary'])) {
			$sErrorFlag['sVoteSummary'] = true;
		}
				
		if (!empty(Shopware()->Config()->CaptchaColor) && !$voteConfirmed) {
			
			$captcha = str_replace(' ', '', strtolower($this->Request()->sCaptcha));
			$rand = $this->Request()->getPost('sRand');
			
			$random = $rand;
			$random .= Shopware()->Plugins()->Core()->License()->getLicense("community");
			$random .= Shopware()->Plugins()->Core()->License()->getLicense("core");
			$random = md5($random);
			$calculatedValue = substr($random,0,5);
			if (!empty($rand) && $captcha == $calculatedValue){
			} else {
				$sErrorFlag['sCaptcha'] = true;
			}
		}
		$validator = new Zend_Validate_EmailAddress();

		if (!empty(Shopware()->Config()->sOPTINVOTE)
		  && (empty(Shopware()->System()->_POST['sVoteMail'])
		  || !$validator->isValid(Shopware()->System()->_POST['sVoteMail']))) {
			$sErrorFlag['sVoteMail'] = true;
		}
				
		if (empty($sErrorFlag)) {
			if (!empty(Shopware()->Config()->sOPTINVOTE)
			  && !$voteConfirmed&& empty(Shopware()->Session()->sUserId)) {
			    $hash = md5(uniqid(rand()));
			    
			    $sql = '
				    INSERT INTO s_core_optin (datum, hash, data)
				    VALUES (NOW(), ?, ?)
			    ';
			    Shopware()->Db()->query($sql, array(
			    	$hash, serialize(Shopware()->System()->_POST)
			    ));
			    
				$mail = clone Shopware()->Mail();
				$template = Shopware()->Config()->Templates->sOPTINVOTE;
				
				$mail->IsHTML($template['ishtml']);
				$mail->From     = $template['frommail'];
				$mail->FromName = $template['fromname'];
				$mail->Subject  = $template['subject'];
				if (!empty($template['ishtml'])) {
			    	$mail->Body = $template['contentHTML'];
			    	$mail->AltBody = $template['content'];
			    } else {
			    	$mail->Body = $template['content'];
			    }
			   
			    $link = $this->Front()->Router()->assemble(array(
			    	'sViewport'=>'detail',
			    	'action'=>'rating',
			    	'sArticle'=>$id,
			    	'sConfirmation'=>$hash
			    ));
			    
			    $mail->Body = str_replace('{$sConfirmLink}', $link, $mail->Body);
			    $mail->Body = str_replace('{$sArticle.articleName}', $article, $mail->Body);
				$mail->ClearAddresses();
				$mail->AddAddress($this->Request()->getParam('sVoteMail'), '');
				
				$mail->Send();
			} else {
				unset(Shopware()->Config()->sOPTINVOTE);
				Shopware()->Modules()->Articles()->sSaveComment($id);
			}
		} else {
			$this->View()->sFormData = Shopware()->System()->_POST;
			$this->View()->sErrorFlag = $sErrorFlag;
		}
		
		$this->View()->sAction = 'ratingAction';
		
		return $this->forward('index');
	}
}