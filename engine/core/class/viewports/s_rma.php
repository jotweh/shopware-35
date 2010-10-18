<?php
include("s_support.php");

class sViewportRma extends sViewportSupport {
	function sRender()
	{
		return parent::sRender();

	}
	
	function sManageData($variables,$sPOSTS,$sELEMENTS){
		
		//return parent::sManageData($variables,$sPOSTS,$sELEMENTS);
		foreach ($sPOSTS as $key => $value)
		{
			$value = mysql_real_escape_string($value);
			$result[$sELEMENTS[$key]['name']] = $value;
			$variables["sSupport"]["email_template"] = str_replace("{\$".$sELEMENTS[$key]['name']."}",$value,$variables["sSupport"]["email_template"]);
		}
		
		// Standard values
		$variables["sSupport"]["email_template"] = str_replace("{\$date}",date("Y-m-d"),$variables["sSupport"]["email_template"]);
		
		$this->sSYSTEM->sDB_CONNECTION->Execute($variables["sSupport"]["email_template"]);
		
		if ($this->sSYSTEM->sDB_CONNECTION->ErrorMsg()){
			echo "<strong>Error while sending email, please try again later</strong>";
			echo $this->sSYSTEM->sDB_CONNECTION->ErrorMsg();
		}
		
		/*print_r($variables);
		print_r($result);
		*/
		
		/*$this->sSYSTEM->sDB_CONNECTION->Execute("
		INSERT INTO s_user_service_defect
		(clientnumber, email, billingnumber, articles, description, description2, description3,
		description4,date)
		VALUES (
			'{$kdnr}',
			'{$email}',
			'{$rechnung}',
			'{$artikel}',
			'{$fehler}',
			'{$rechner}',
			'{$system}',
			'{$wie}',
			'{$date}'
		)
		");*/
		
	}
}

?>