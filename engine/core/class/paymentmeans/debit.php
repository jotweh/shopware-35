<?php
class sPaymentMean{
	var $sSYSTEM;
	
	function sInit(){
		// Validate data
		#sDebitAccount
		#sDebitBankcode
		#sDebitBankName
		
		if (!$this->sSYSTEM->_POST["sDebitAccount"]){
			$sErrorFlag["sDebitAccount"] = true;
		}
		if (!$this->sSYSTEM->_POST["sDebitBankcode"]){
			$sErrorFlag["sDebitBankcode"] = true;
		}
		if (!$this->sSYSTEM->_POST["sDebitBankName"]){
			$sErrorFlag["sDebitBankName"] = true;
		}
		if (empty($this->sSYSTEM->_POST["sDebitBankHolder"])&&isset($this->sSYSTEM->_POST["sDebitBankHolder"])){
			$sErrorFlag["sDebitBankHolder"] = true;
		}
		
		
		$checkColumns = $this->sSYSTEM->sDB_CONNECTION->GetAll("SHOW COLUMNS FROM s_user_debit");
		$foundColumn = false;
		foreach ($checkColumns as $column){
			if ($column["Field"]=="bankholder"){
				$foundColumn = true;
			}
		}
		if (empty($foundColumn)){
			
			$this->sSYSTEM->sDB_CONNECTION->Execute("ALTER TABLE `s_user_debit` ADD `bankholder` VARCHAR( 255 ) NOT NULL ;");
		}
		
		
		if (count($sErrorFlag)) $error = true;
		
		if ($error){
			$sErrorMessages[] = $this->sSYSTEM->sCONFIG['sErrors']['sErrorBillingAdress'];
			
			return array("sErrorFlag"=>$sErrorFlag,"sErrorMessages"=>$sErrorMessages);
		}else {
			return true;
		}
		
		return array();
	}
	
	function sUpdate(){
		if (empty($this->sSYSTEM->_SESSION["sUserId"])) return;
		
		if (count($this->getData())){
			$data = array(
				$this->sSYSTEM->_POST["sDebitAccount"],
				$this->sSYSTEM->_POST["sDebitBankcode"],
				$this->sSYSTEM->_POST["sDebitBankName"],
				$this->sSYSTEM->_POST["sDebitBankHolder"],
				$this->sSYSTEM->_SESSION["sUserId"]
			);
			
			$update = $this->sSYSTEM->sDB_CONNECTION->Execute("
			UPDATE s_user_debit SET account=?, bankcode=?, bankname=?,bankholder=?
			WHERE userID=?",$data);
		}else {
			$data = array(
				$this->sSYSTEM->_SESSION["sUserId"],
				$this->sSYSTEM->_POST["sDebitAccount"],
				$this->sSYSTEM->_POST["sDebitBankcode"],
				$this->sSYSTEM->_POST["sDebitBankName"],
				$this->sSYSTEM->_POST["sDebitBankHolder"]
			);
			$update = $this->sSYSTEM->sDB_CONNECTION->Execute("
			INSERT INTO s_user_debit (userID, account, bankcode, bankname, bankholder)
			VALUES (?,?,?,?,?)
			",$data);
		}
	}
	
	function sInsert($userId){
		if (!$userId) return false;
		// Insert data
		$data = array(
				$userId,
				$this->sSYSTEM->_POST["sDebitAccount"],
				$this->sSYSTEM->_POST["sDebitBankcode"],
				$this->sSYSTEM->_POST["sDebitBankName"],
				$this->sSYSTEM->_POST["sDebitBankHolder"]
			);
		$update = $this->sSYSTEM->sDB_CONNECTION->Execute("
		INSERT INTO s_user_debit (userID, account, bankcode, bankname,bankholder)
		VALUES (?,?,?,?,?)
		",$data);
		return true;
	}
	
	function getData(){
		if (empty($this->sSYSTEM->_SESSION["sUserId"])) return array();
		
		$getData = $this->sSYSTEM->sDB_CONNECTION->GetRow("
		SELECT account AS sDebitAccount, bankcode AS sDebitBankcode, bankname AS sDebitBankName, bankholder AS sDebitBankHolder FROM s_user_debit WHERE
		userID=?",array($this->sSYSTEM->_SESSION["sUserId"]));
		
		return $getData;
	}
	
	function sIdentify(){
		echo "Lastschrift Version 1.1";
	}
	
	
}
?>