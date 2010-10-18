<?php
class sViewportNewsletterListing
{
	public $sSYSTEM;
	public function sRender()
	{
		$customergroups = array('EK');
		if(!empty($this->sSYSTEM->sSubShop['defaultcustomergroup']))
		{
			$customergroups[] = $this->sSYSTEM->sSubShop['defaultcustomergroup'];
		}
		if(!empty($this->sSYSTEM->sUSERGROUPDATA['groupkey']))
		{
			$customergroups[] = $this->sSYSTEM->sUSERGROUPDATA['groupkey'];
		}
		$customergroups = array_unique($customergroups);
		$customergroups = "'".implode("', '", $customergroups)."'";
		
		if(empty($this->sSYSTEM->_GET['sID']))
		{
			$page = isset($this->sSYSTEM->_GET['sPage']) ? (int) $this->sSYSTEM->_GET['sPage'] : 1;
			$perpage = isset($this->sSYSTEM->sCONFIG['sCONTENTPERPAGE']) ? (int) $this->sSYSTEM->sCONFIG['sCONTENTPERPAGE'] : 0;
			
			$sql = "
				SELECT SQL_CALC_FOUND_ROWS id, IF(datum='00-00-0000','',datum) as `date`, subject as description, sendermail, sendername
				FROM `s_campaigns_mailings`
				WHERE `status`!=0
				AND plaintext=0
				AND publish!=0
				AND languageID=?
				AND customergroup IN ($customergroups)
				ORDER BY id DESC
			";
			
			$sql = $this->sSYSTEM->sDB_CONNECTION->limit($sql, $perpage, $perpage*($page-1));
			$result = $this->sSYSTEM->sDB_CONNECTION->query($sql, array($this->sSYSTEM->sLanguage));

			$content = array();
			while ($row = $result->fetch())
			{
				$row['link'] = $this->sSYSTEM->sCONFIG['sBASEFILE'].'?sViewport=newsletterListing&sID='.$row['id'];
				$content[] = $row;
			}
			
			$sql = 'SELECT FOUND_ROWS() as count_'.md5($sql);
			$count = $this->sSYSTEM->sDB_CONNECTION->fetchOne($sql);
			
			$pages = array();
			for ($i=1; $i<=$count; $i++)
			{
				if ($i==$page) {
					$pages['numbers'][$i]['markup'] = true;
				} else {
					$pages['numbers'][$i]['markup'] = false;
				}
				$pages['numbers'][$i]['value'] = $i;
				$pages['numbers'][$i]['link'] = $this->sSYSTEM->sCONFIG['sBASEFILE'].'?sViewport=newsletterListing&sPage='.$i;
			}
			
			
			$variables = array (
				'sPages' => $pages,
				'sContent' => $content, 
				'sBreadcrumb' => array(0=>array('name'=>$this->sSYSTEM->sCONFIG['sViewports'][$this->sSYSTEM->_GET['sViewport']]['name']))
			);			
			$templates = array('sContainer'=>'/newsletter/newsletter_listing.tpl');
		}
		else
		{
			$sql = "
				SELECT id, IF(datum='00-00-0000','',datum) as `date`, subject as description, sendermail, sendername
				FROM `s_campaigns_mailings`
				WHERE `status`!=0
				AND plaintext=0
				AND publish!=0
				AND languageID=?
				AND id=?
				AND customergroup IN ($customergroups)
			";
			
			$content = $this->sSYSTEM->sDB_CONNECTION->GetRow($sql, array($this->sSYSTEM->sLanguage, $this->sSYSTEM->_GET['sID']));
			if(!empty($content))
			{
				$content['hash'] = array($content['id'], $this->sSYSTEM->sLicenseData['sCORE']);
				$content['hash'] = md5(implode('|', $content['hash']));
				$content['link'] = 'http://'.$this->sSYSTEM->sCONFIG['sBASEPATH'].'/engine/core/php/campaigns.php?id='.$content['id'].'&hash='.$content['hash'];
			}
			
			$variables = array (
				'sContentItem' => $content,
				'sBackLink' => $this->sSYSTEM->sCONFIG['sBASEFILE'].'?sViewport=newsletterListing',
				'sBreadcrumb' => array(
					0 => array('name' => $this->sSYSTEM->sCONFIG['sViewports'][$this->sSYSTEM->_GET['sViewport']]['name'], 'link'=> $this->sSYSTEM->sCONFIG['sBASEFILE'].'?sViewport=newsletterListing')
				)
			);
			if(!empty($content['description']))
			{
				$variables['sBreadcrumb'][] = array('name' => $content['description']);
			}
			$templates = array('sContainer'=>'/newsletter/newsletter_details.tpl');
		}
		return array('templates'=>$templates,'variables'=>$variables);
	}
}
?>