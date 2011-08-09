<?php
class
	shopware_articles
{
	// Preise auf Gültigkeit prüfen
	function check_prices (&$_POST, &$form ){
		
    	foreach ($_POST["priceregulary"] as $group => $content){
    			foreach ($content as $price => $content2){
    		#	echo "Gruppe:$group, Price: $price, content2: $content2 <br>";
    				// Konvertiere Zahlen
    				$_POST["priceregulary"][$group][$price] = str_replace(",",".",$_POST["priceregulary"][$group][$price]);
    				$_POST["priceEK"][$group][$price] = str_replace(",",".",$_POST["priceEK"][$group][$price]);
    				$_POST["pricepseudo"][$group][$price] = str_replace(",",".",$_POST["pricepseudo"][$group][$price]);
    				$_POST["percent"][$group][$price] = str_replace(",",".",$_POST["percent"][$group][$price]);
    				
    				#echo "<b>{$_POST["priceregulary"][$group][$price]}</b>";
    				// Prüfe eingegebene Zahlen auf Gültigkeit
    				if ($_POST["priceregulary"][$group][$price] && (!is_numeric($_POST["priceregulary"][$group][$price]) || $_POST["priceregulary"][$group][$price]<0)){
    					$form->error_handler(printf($sLang["articles"]["class_articles_invalid_price"],$group,($price+1)),$sLang["articles"]["class_articles_wrong_price"]);
					}
					// Zumindest der Endkunden-VK muss angegeben werden
					if ($group=="EK"){
						if (!$_POST["priceregulary"][$group][$price]){
							$form->error_handler(printf($sLang["articles"]["class_articles_invalid_price"],$group,($price+1)),$sLang["articles"]["class_articles_wrong_price"]);
						}
					}
					if ($_POST["priceEK"][$group][$price] && (!is_numeric($_POST["priceEK"][$group][$price]) || $_POST["priceEK"][$group][$price]<0)){
						$form->error_handler(printf($sLang["articles"]["class_articles_invalid_price"],$group,($price+1)),$sLang["articles"]["class_articles_wrong_price"]);
					}
					if ($_POST["pricepseudo"][$group][$price] && (!is_numeric($_POST["pricepseudo"][$group][$price]) || $_POST["pricepseudo"][$group][$price]<0)){
						$form->error_handler(printf($sLang["articles"]["class_articles_invalid_price"],$group,($price+1)),$sLang["articles"]["class_articles_wrong_price"]);
					}
					
					
    			} // Für jede Staffel
    		
    	} // Für jede Gruppe
	}
} // End Class

class
	shopware_admin
{
	// Connection Holder
	var $conn;
	var $_MSG;
	var $errors;
	
	function error_box($title,$message)
	{
		global $sLang;
		$this->errors[$title] = $message;
	}
	
	function warning_box($title,$message)
	{
		$this->error_box($title,$message);
	}
	
	function inform_box($title,$message)
	{
		echo "
		<table width='300' border='0' align='center' cellpadding='4' cellspacing='1' bgcolor='#20AF19'>
		  <tr>
		    <td bgcolor='#BDDDBB'><table width='100%' border='0' cellpadding='0' cellspacing='0'>
		        <tr>
		          <td width='36' valign='top'><img src='gfx_main/rightbox_good.gif' width='37' height='31'></td>
		          <td><font color='#1E8119' size='2' face='Arial, Helvetica, sans-serif'><b>$title</b><br>
		            <br>
		            $message</font></td>
		        </tr>
		      </table>
		    </td>
		  </tr>
		</table>";
	}
	
}

class
	shopware_forms
{
	var $elements;
	var $ruleCOUNTER;
	var $element_template;
	var $wysiwyg;
	var $wysiwyg_object;
	var $element_markup;
	var $element_price_template;
	var $errorhandlerCLS;
	var $fehler;
	var $mergefields;
	var $languages;
	var $translations;
	
	// Render Form-Header
	function header ($name,$method,$action){
		echo "<form name=\"$name\" id=\"$name\" method=\"$method\" action=\"$action\">";
		echo "<input name=\"validate\" value=\"1\" type=\"hidden\">";
	
	}
	
	// Render Form_Footer
	function footer (){
		echo "</form>";
	}
	
	// Fügt der Aufstellung ein Element hinzu
	function addElement($type, $name, $description,$render_as_set,$data,$required,$preClass,$help, $multilanguage,$disabled = false){
		
		$this->mergefields = false;
		
		switch ($type){
			case "price":
			// price collection
			$element = $this->element_price_template;
			$useowntemplate=1;
				break;
			// simply a textbox we want
			case "text":
				$nameId = str_replace("[","",$name);
				$nameId = str_replace("]","",$nameId);
				$len = strlen($data);
				
				if (!$preClass){
					if (!$len){
						$class = "w100";
					}else {
						if ($len<=5 || is_numeric($data)){
							$class = "w30";
							
						}else if ($len <= 10){
							$class = "w50";
						}else if ($len <= 20){
							$class = "w200";
						}else {
							$class = "w200";
						}
					}
				}else {
					$class = $preClass;
				}
				
				if ($class=="w30"){
					$this->mergefields = true;
				}
				if (!empty($disabled)) $disabled = "style=\"opacity:0.6;\" readonly='readonly'";
			
				$data = htmlspecialchars($data, ENT_COMPAT, null, false);
				$element = "<input name=\"$name\" id=\"$nameId\" value=\"$data\" class=\"$class\" $disabled>";
				if ($multilanguage){
					$element.= "<div style=\"margin-left:20px;margin-top:5px;float:left\">";
					
					$firstlanguage = true;
					
					foreach ($this->languages as $language){
						
						if ($this->translations[$language["isocode"]][$nameId]){
							$opacity = "opacity:1";
						}else {
							$opacity = "opacity:0.5";
						}
						
						$style = "style=\"margin-left:10px;$opacity;cursor:pointer\"";
						if ($_REQUEST["variante"]){
							$onclick = "onclick=\"sTranslations('$nameId','$nameId','{$_REQUEST["variante"]}','variant','{$language["isocode"]}')\"";
						}else {
							$onclick = "onclick=\"sTranslations('$nameId','$nameId','{$_GET["article"]}','article','{$language["isocode"]}')\"";
						}
						$element .= "<img src=\"../../../backend/img/default/icons/flags/{$language["flagbackend"]}\" $style $onclick>";	
						
					}
					$element .= "</div>";
				}
				
			break;
			
			// Textarea for some more text
			case "textarea":
				
				$nameId = str_replace("[","",$name);
				$nameId = str_replace("]","",$nameId);
				$data = htmlspecialchars($data, ENT_COMPAT);
				$element = "<textarea name=\"$name\" id=\"$nameId\" cols=\"30\" rows=\"6\" class=\"anlegen\">$data</textarea>";
				
				if ($multilanguage){
					
					
					$firstlanguage = true;
					
					foreach ($this->languages as $language){
						
						if ($this->translations[$language["isocode"]][$nameId]){
							$opacity = "opacity:1";
						}else {
							$opacity = "opacity:0.5";
						}
						
						$style = "style=\"margin-left:10px;$opacity;cursor:pointer\"";
						if ($_REQUEST["variante"]){
							$onclick = "onclick=\"sTranslations('$nameId','$nameId','{$_REQUEST["variante"]}','variant','{$language["isocode"]}')\"";
						}else {
							$onclick = "onclick=\"sTranslations('$nameId','$nameId','{$_GET["article"]}','article','{$language["isocode"]}')\"";
						}
						$element .= "<img src=\"../../../backend/img/default/icons/flags/{$language["flagbackend"]}\" $style $onclick>";	
						
					}
					
				}
				
			break;
			
			// Date Pickup
			case "date":
				// Editing Data-Value to pass german date formatings
				if (!$data){
					$data = date("d.m.Y");
				}
				$element = "
				<input id=\"$name\" name=\"$name\" value=\"$data\" onclick=\"displayDatePicker('$name', false, 'dmy', '.');\"><a class=\"ico calendar\"  onclick=\"displayDatePicker('$name', false, 'dmy', '.');\"></a>
				";
			break;
					
			case "wysiwyg":
				$nameId = str_replace("[","",$name);
				$nameId = str_replace("]","",$nameId);
				$data = htmlspecialchars($data, ENT_COMPAT);
				$element = "<textarea id=\"$nameId\" name=\"$name\" rows=\"15\" rows=\"80\" mce_editable=\"true\">$data</textarea>";
				$useowntemplate = true;
				
				if ($multilanguage){
					
					
					$firstlanguage = true;
					
					foreach ($this->languages as $language){
						
						if ($this->translations[$language["isocode"]][$nameId]){
							$opacity = "opacity:1";
						}else {
							$opacity = "opacity:0.5";
						}
						
						$style = "style=\"margin-left:10px;margin-top:10px;$opacity;cursor:pointer\"";
						if ($_REQUEST["variant"]){
							$onclick = "onclick=\"sTranslations('$nameId','$nameId','{$_GET["variant"]}','variant','{$language["isocode"]}')\"";
						}else {
							$onclick = "onclick=\"sTranslations('$nameId','$nameId','{$_GET["article"]}','article','{$language["isocode"]}')\"";
						}
						$element .= "<img src=\"../../../backend/img/default/icons/flags/{$language["flagbackend"]}\" $style $onclick>";	
						
					}
					
				}
				
			break;
			
			// Simply Yes/No decisions
			case "boolean":
				if ($data){
					$chk = "checked";	
				}else {
					$chk = "";	
				}
				$element = "<input type=\"checkbox\" name=\"$name\" value=\"1\" $chk>";

				/*
			 	 * @ticket 5860
			 	 * @author s.pohl
			 	 * @date 2011-08-09
				 *
				 * Fix a layout issue in the article core data fieldset which causes that the user
				 * couldn't identify the related form element to the label ("use data from price group")
				 * due to the large white space between the form element and the label.
			 	*/
				$this->mergefields = false;
				break;
			
			case "select":
				if (is_array($data)){
					$additional_render = "";
					foreach ($data as $datarow){
						if ($datarow["set"]){
							$add = "selected";	
						}else {
							$add = "";
						}
						$additional_render .= "<option value=\"".$datarow["value"]."\" $add>".$datarow["option"]."</option>";	
					}	
				}
				$nameId = str_replace("[","",$name);
				$nameId = str_replace("]","",$nameId);
				$element = "
				<select name=\"$name\" id=\"$nameId\" class=\"inputdefault\">
				$additional_render
				</select>
				";
			break;
			
			
			case "select_with_new_option":
				// Do we have data for render?
				// Default Eintrag
				$additional_render = "";	
				
				if (is_array($data)){
					
					foreach ($data as $datarow){
						
						if ($datarow["set"]){
							$add = "selected";	
						}else {
							$add = "";
						}
						if ($datarow["value"] && $datarow["option"]){
							$additional_render .= "<option value=\"".$datarow["value"]."\" $add>".$datarow["option"]."</option>";	
						}
					}	
				}
				$nameId = str_replace("[","",$name);
				$nameId = str_replace("]","",$nameId);
				$nameId = $nameId."Combo";
				$element = "
  				 <select id=\"$nameId\" name=\"$name"."[ALT]"."\" class=\"inputdefault\" style=\"margin:5px;\">
				 $additional_render
				 </select>
         		";
				$test = "<input value=\"".$data["NEU"]."\" name=\"$name"."[NEU]"."\" type=\"text\" class=\"inputdefault\" id=\"txtherstellerneu\" size=\"30\" maxlength=\"60\">
        		";
			break;	
		}
		
		if ($render_as_set){
		// Some Template stuff
			$temp_output = $this->element_template;
			
			if ($this->element_markup[$name]){
				$description = "<span style=\"color:#F00;font-weight:bold\">$description</span>";
			}else {
				if ($required){
					$description .= "*";	
				}	
			}
			
			
			// Bei Preiselementen nicht das Standardtemplate verwenden
			if (!isset($useowntemplate)){
				$temp_output = str_replace("{FORM_ELEMENT}",$element,$temp_output);
				
				$temp_output = str_replace("{FORM_DESCRIPTION}",$description,$temp_output);
				if ($help){
					$help = "class=\"toolTip\" title=\"".wordwrap($help,20,"<br />")."\"";
					$temp_output = str_replace("{FORM_HELP}",$help,$temp_output);	
				}else {
					$temp_output = str_replace("{FORM_HELP}","",$temp_output);
				}
				
				
				if (!$this->mergefields){
					$temp_output = str_replace("<!--","",$temp_output);
					$temp_output = str_replace("-->","",$temp_output);
				}
				
				echo $temp_output;
			}else {
				echo $element; 
			}
			
			#echo $element;	
		}
	}
	
	// Fügt eine Regel hinzu
	function addRule($name, $error_message, $check_type, $element_type){
		// Mal ein blöder Test
		$this->ruleCOUNTER++;
		
		echo "<input type=\"hidden\" name=\"rules[".$this->ruleCOUNTER."][name]\" value=\"$name\">";
		echo "<input type=\"hidden\" name=\"rules[".$this->ruleCOUNTER."][checktype]\" value=\"$check_type\">";
		echo "<input type=\"hidden\" name=\"rules[".$this->ruleCOUNTER."][error_message]\" value=\"$error_message\">";
		echo "<input type=\"hidden\" name=\"rules[".$this->ruleCOUNTER."][type]\" value=\"$element_type\">";	
	}
	function validate($POST,$HANDLER){
		//print_r($POST);	
		foreach ($POST["rules"] as $rule){
			#print_r($rule);
			#echo $rule["name"]."<br>";
			#echo $rule["checktype"]."<br>";
			#echo $rule["error_message"]."<br>";
			switch ($rule["checktype"]){
				case "required":
				if ($rule["type"]!="select_with_new_option"){
					if (!($POST[$rule["name"]])) 
					{
						$this->error_handler($rule["error_message"]);
						// Betreffendes Element Rot-markieren
						$this->element_markup[$rule["name"]] = 1;
						//echo "TEST";
					}
				}
				else {
					// Bei Select With New Option muss sowohl das Select Feld, wie auch das Textfeld geprüft werden
					if (!($POST[$rule["name"]]["ALT"]) && !($POST[$rule["name"]]["NEU"])) 
					{
					//	echo "Not realy set";
						$this->error_handler($rule["error_message"]);
						// Betreffendes Element Rot-markieren
						$this->element_markup[$rule["name"]] = 1;
					}
				}
				case "date":
				// Datum auf Gültigkeit prüfen ...
				$datum = $POST[$rule["name"]];
				$datum = explode(".",$datum);
				$tag = $datum[0];
				$monat = $datum[1];
				$jahr = $datum[2];
				#if (!checkdate($monat,$tag,$jahr)){
				#	$this->error_handler($rule["error_message"]);
				#	$this->element_markup[$rule["name"]] = 1;
				#}
				break;
				case "length":
					break;
				case "numeric":
					break;
				case "price":
					break;
				case "az":
					break;
			}
		}
		// Fehler ausgeben
		if ($this->fehler){
			$HANDLER->error_box("Bitte füllen Sie die folgenden Felder aus:<br />",$this->fehler);
			
			return array("FAILURE"=>$this->fehler);
		}else {
			return array("SUCCESS"=>true);	
		}
	}
	
	function error_handler($error_message){
		#echo "Folgende Daten fehlen:<br>";
		if ($error_message){
			$this->fehler .=  "$error_message<br>";
		}
	}
}
?>