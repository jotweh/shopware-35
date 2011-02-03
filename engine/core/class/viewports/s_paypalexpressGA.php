<?php
include("s_paypalexpressGAReg.php");
include_once("s_sale.php");

class sViewportPaypalexpressGA{
	var $sSYSTEM;
	var $sViewportAdmin;
	var $sViewportSale;
	var $sViewportPaypalexpressGAReg;
	
	function sViewportPaypalexpressGA(&$sSYSTEM,&$parentClass){

		$this->sViewportPaypalexpressGAReg = new sViewportPaypalexpressGAReg;
		$this->sViewportPaypalexpressGAReg->sSYSTEM = $sSYSTEM;
		
		if(get_class($parentClass)=="sViewportSale"){
			$this->sViewportSale = $parentClass;
		}else {
			$this->sViewportSale = new sViewportSale($sSYSTEM,$this);
			$this->sViewportSale->sSYSTEM = $sSYSTEM;			
		}
	}
	
	function sRender(){
		$this->sSYSTEM->_SESSION["sRegisterFinished"] = false;
			
		if (!$this->sSYSTEM->sMODULES['sAdmin']->sCheckUser() || $this->sSYSTEM->_SESSION['GuestUser'] == "1" ){
		// Check data 
			
			$resArray = $this->sSYSTEM->_SESSION['reshash'];
		
			$tmp = $resArray["COUNTRYCODE"];
			$variables["resArray"] = $tmp;


			// get countryId from DB
			$countryiso = strtoupper($resArray["SHIPTOCOUNTRYCODE"]);
			$sql = "SELECT id FROM s_core_countries WHERE countryiso= '$countryiso'";
			$getCountryId = $this->sSYSTEM->sDB_CONNECTION->GetRow($sql);
							
			if (!empty($getCountryId['id'])) $getCountryId = $getCountryId['id'];
						
			// set POST parameters
			$this->sSYSTEM->_POST['email'] = urldecode($resArray["EMAIL"]);
			$this->sSYSTEM->_POST['skipLogin'] = "1";
			$this->sSYSTEM->_POST['salutation'] = "mr";
			$this->sSYSTEM->_POST['firstname'] = utf8_decode($resArray["FIRSTNAME"]);
			$this->sSYSTEM->_POST['lastname'] = utf8_decode($resArray["LASTNAME"]);
			$this->sSYSTEM->_POST['street'] = utf8_decode($resArray["SHIPTOSTREET"]);
			$this->sSYSTEM->_POST['streetnumber'] = " ";
			$this->sSYSTEM->_POST['zipcode'] = $resArray["SHIPTOZIP"];
			$this->sSYSTEM->_POST['city'] = utf8_decode($resArray["SHIPTOCITY"]);
			$this->sSYSTEM->_POST['country'] = $getCountryId;

			if (!empty($resArray["BUSINESS"])) $this->sSYSTEM->_POST['company'] = utf8_decode($resArray["BUSINESS"]);
		
			$checkData = ($this->sSYSTEM->sMODULES['sAdmin']->sValidateStep1());
			if (!count($checkData["sErrorMessages"])){
				$accountError = false;
				
			}else {
			
				// Uups, something goes wrong
				$variables["sError"] = "PayPal Express Error - ValidateStep 1";
				$variables["sErrorFlag"] = $checkData["sErrorFlag"];
				$variables["sErrorMessages"] = $checkData["sErrorMessages"];
				$accountError = true;

				// Go to error 
				$templates = array("sContainer"=>"/error/paypalexpress_error.tpl","sContainerRight"=>"");	
				return array("templates"=>$templates,"variables"=>$variables);

			}
			
			// Data for Step-2
			
			// Define field-rules
			$rules = array(
			"salutation"=>array("required"=>1),
			"company"=>array("addicted"=>array("salutation"=>"company"),"required"=>1),
			"firstname"=>array("required"=>1),
			"lastname"=>array("required"=>1),
			"street"=>array("required"=>1),
			"streetnumber"=>array("required"=>0),
			"zipcode"=>array("required"=>1),
			"city"=>array("required"=>1),
			"phone"=>array("required"=>0),
			"fax"=>array("required"=>0),
			"country"=>array("required"=>1),
			"department"=>array("required"=>0),
			"shippingAddress"=>array("required"=>0),
			"ustid"=>array("required"=>0),
			"text1"=>array("required"=>0),
			"text2"=>array("required"=>0),
			"text3"=>array("required"=>0),
			"text4"=>array("required"=>0),
			"text5"=>array("required"=>0),
			"text6"=>array("required"=>0),
			"sValidation"=>array("required"=>0),
			"birthyear"=>array("required"=>0),
			"birthmonth"=>array("required"=>0),
			"birthday"=>array("required"=>0)
			);
			
					
			// Check data 
			$checkData = ($this->sSYSTEM->sMODULES['sAdmin']->sValidateStep2($rules));
			
			if (count($checkData["sErrorMessages"])  && !$accountError){

				// Uups, something goes wrong - pass error through template
				if (!count($variables["sErrorFlag"])) $variables["sErrorFlag"] = array();
				if (!count($variables["sErrorMessages"])) $variables["sErrorMessages"] = array();
				
				if (count($checkData["sErrorFlag"])){
					$variables["sErrorFlag"] = array_merge($variables["sErrorFlag"],$checkData["sErrorFlag"]);
				}
				if (count($checkData["sErrorMessages"])){
					$variables["sErrorMessages"] = array_merge($variables["sErrorMessages"],$checkData["sErrorMessages"]);
				}
				
				// Go to error 
				$templates = array("sContainer"=>"/error/paypalexpress_error.tpl","sContainerRight"=>"");	
				$variables = array("sError"=>"PayPal Express Error - ValidateStep 2");
				return array("templates"=>$templates,"variables"=>$variables);
				
			}

			// Define field-rules for Shipping Adress
			$rules = array(
			"salutation"=>array("required"=>1),
			"company"=>array("addicted"=>array("salutation"=>"company"),"required"=>1),
			"firstname"=>array("required"=>1),
			"lastname"=>array("required"=>1),
			"street"=>array("required"=>1),
			"streetnumber"=>array("required"=>0),
			"zipcode"=>array("required"=>1),
			"city"=>array("required"=>1),
			"department"=>array("required"=>0),
			"country"=>array("required"=>1),
			"text1"=>array("required"=>0),
			"text2"=>array("required"=>0),
			"text3"=>array("required"=>0),
			"text4"=>array("required"=>0),
			"text5"=>array("required"=>0),
			"text6"=>array("required"=>0)
			);
			
			//divide Firstname and Lastname from SHIPTONAME
			$SHIPTONAME = utf8_decode($resArray["SHIPTONAME"]);
			$shiptonameArray = explode(" ", $SHIPTONAME);
			
			$max = count($shiptonameArray)-1;
			
			for ($i = 0; $i < $max; $i++) {
				$SHIPTOFIRSTNAME.= $shiptonameArray[$i];
			}
				$SHIPTOLASTNAME = $shiptonameArray[$max];
			
			if (empty($SHIPTOFIRSTNAME)) $SHIPTOFIRSTNAME = " ";
			if (empty($SHIPTOLASTNAME)) $SHIPTOLASTNAME = " ";
			//set Firstname and Lastname for ShippingAdress
			$this->sSYSTEM->_POST['firstname'] = $SHIPTOFIRSTNAME;
			$this->sSYSTEM->_POST['lastname'] = $SHIPTOLASTNAME;

			// Check data 
			$checkData = ($this->sSYSTEM->sMODULES['sAdmin']->sValidateStep2ShippingAddress($rules));
			if (count($checkData["sErrorMessages"])){
				
				// Uups, something goes wrong
				$variables["sErrorFlag"] = $checkData["sErrorFlag"];
				$variables["sErrorMessages"] = $checkData["sErrorMessages"];

				// Go to error 
				$templates = array("sContainer"=>"/error/paypalexpress_error.tpl","sContainerRight"=>"");	
				$variables = array("sError"=>"PayPal Express Error - ValidateStep ShippingAddress");
				return array("templates"=>$templates,"variables"=>$variables);
			}


			// Go to Register User!
			$this->sSYSTEM->_POST['sAction'] = "doReg";
			$this->sSYSTEM->_GET["sViewport"] = "paypalexpressGAReg";
			return $this->sViewportPaypalexpressGAReg->sRender();
			
		}
		
				
		// Go to order-confirmation
		$this->sSYSTEM->_GET["sViewport"] = "sale";
		return $this->sViewportSale->sRender();
		

	}
}
?>