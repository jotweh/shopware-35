<?php
/**
 * Shopware form generation
 * 
 * @link http://www.shopware.de
 * @package core
 * @subpackage class
 * @copyright (C) Shopware AG 2002-2010
 * @version Shopware 3.5.0
 */
class sCmsSupport
{
	var $sSYSTEM;
	var $sFORMNAME;
	var $sSUPPORT;
	var $sELEMENTS;
	var $sLABELS;
	var $sFIELDS;
	var $sPOSTS;
	var $sERRORS;
	var $sID;
	
	/**
	 * Construct form method
	 *
	 * @param unknown_type $id
	 * @return unknown
	 */
	public function sConstruct ($id=false) {
		
		$this->sID = !empty($id) ? $id : intval($this->sSYSTEM->_GET['sFid']);
		if (empty($this->sID))
		{
			return false;
		}
		$sql = "
			SELECT `id` , `name` , `text`, `text2` , `email` , `email_template` , `email_subject` FROM `s_cms_support` WHERE id=?
		";
		$this->sSUPPORT = $this->sSYSTEM->sDB_CONNECTION->GetRow($sql,array($this->sID));
		
		if (empty($this->sSUPPORT))
		{
			return false;
		}
		$sql = "
			SELECT `id`, `name`, `note`, `typ`, `required`, `label`, `class`, `value`, `error_msg` FROM `s_cms_support_fields` WHERE `supportID` = ? ORDER BY position,added
		";
		$result = $this->sSYSTEM->sDB_CONNECTION->GetAll($sql,array($this->sID));
		foreach ($result as $row)
		{
			$this->sELEMENTS[$row['id']] = $row;
		}
		if (empty($this->sELEMENTS))
		{
			return false;
		}
		return true;
	}
	
	/**
	 * Create form method
	 *
	 * @return unknown
	 */
	public function create_form ()
	{
		$ret = "<form name=\"{$this->sSUPPORT['name']}\" class=\"{$this->sSUPPORT['class']}\" method=\"post\" action=\"\" enctype=\"multipart/form-data\">\r\n";
		foreach ($this->sELEMENTS as $element)
		{
			$ret .= "<p>\r\n";
			$ret .= $this->create_label_element($element);
			$ret .= $this->create_input_element($element);
			$ret .= "</p>\r\n";
		}
		$ret .= "</form>\r\n";
		return $ret;
	}

	/**
	 * Create input element method
	 *
	 * @return unknown
	 */
	public function create_input_element ($element,$post)
	{
		if ($element['required']==1)
			$req = "required";
		else
			$req = "";
			
		switch ($element['typ']) {
			case "password":
			case "email":
			case "text":
			case "textarea":
			case "file":
				if ((empty($post) && !empty($element["value"]))) $post = $element["value"];
				elseif (!empty($post)) $post = '{literal}'.str_replace('{/literal}','',$post).'{/literal}';
				break;
			case "text2":
				if (empty($post[0]) && !empty($element["value"][0])) $post[0] = $element["value"][0];
				elseif (!empty($post[0])) $post[0] = "{literal}{$post[0]}{/literal}";
				if (empty($post[1]) && !empty($element["value"][1])) $post[1] = $element["value"][1];
				elseif (!empty($post[1])) $post[1] = "{literal}{$post[1]}{/literal}";
				break;
			default:
				break;
		}

		switch ($element['typ']) {
			case "password":
			case "email":
			case "text":
				$ret .= "<input type=\"{$element['typ']}\" class=\"{$element['class']} $req\" value=\"{$post}\" id=\"{$element['name']}\" name=\"{$element['name']}\"/>\r\n";
				break;
			case "checkbox":
				if ($post==$element['value'])
					$checked = " checked";
				else 
					$checked = "";
				$ret .= "<input type=\"{$element['typ']}\" class=\"{$element['class']} $req\" value=\"{$element['value']}\" id=\"{$element['name']}\" name=\"{$element['name']}\"$checked/>\r\n";
				break;
			case "file":
				$ret .= "<input type=\"{$element['typ']}\" class=\"{$element['class']} $req file\" id=\"{$element['name']}\" name=\"{$element['name']}\" maxlength=\"100000\" accept=\"{$element['value']}\"/>\r\n";
				break;
			case "text2":
				$element['class'] = explode(";",$element['class']);
				$element['name'] = explode(";",$element['name']);
				$ret .= "<input type=\"text\" class=\"{$element['class'][0]} $req\" value=\"{$post[0]}\" id=\"{$element['name'][0]};{$element['name'][1]}\" name=\"{$element['name'][0]}\"/>\r\n";
				$ret .= "<input type=\"text\" class=\"{$element['class'][1]} $req\" value=\"{$post[1]}\" id=\"{$element['name'][0]};{$element['name'][1]}\" name=\"{$element['name'][1]}\"/>\r\n";
				break;
			case "textarea":
				if (empty($post) && $element["value"]) $post = $element["value"];
				$ret .= "<textarea class=\"{$element['class']} $req\" id=\"{$element['name']}\" name=\"{$element['name']}\">{$post}</textarea>\r\n";
				break;
			case "select":
				$values = explode(";", $element['value']);
				$ret .= "<select class=\"{$element['class']} $req\" id=\"{$element['name']}\" name=\"{$element['name']}\">\r\n\t<option selected=\"selected\" value=\"\">".$this->sSYSTEM->sCONFIG["sSnippets"]["sRegisterpleaseselect"]."</option>";
				foreach ($values as $value)
				{
					if ($value==$post)
						$ret .= "<option selected>$value</option>";
					else
						$ret .= "<option>$value</option>";
				}
				$ret .= "</select>\r\n";
				break;
			case "radio":
				$values = explode(";", $element['value']);
				foreach ($values as $value)
				{
					if ($value==$post)
						$checked = " checked";
					else 
						$checked = "";
					$ret .= "<input type=\"radio\" class=\"{$element['class']} $req\" value=\"$value\" id=\"{$element['name']}\" name=\"{$element['name']}\"$checked> $value ";
				}
				$ret .= "\r\n";
				break;
		}
		return $ret;
	}
	
	/**
	 * Create label element method
	 *
	 * @return unknown
	 */
	public function create_label_element ($element)
	{
		$ret = "<label for=\"{$element['name']}\">{$element['label']}";
		if ($element['required']==1)
			$ret .= "*";
		$ret .= ":</label>\r\n";
		return $ret;
	}
	
	/**
	 * Validate input method
	 *
	 * @return unknown
	 */
	public function validate_input ($inputs,$elements)
	{
		foreach ($elements as $element)
		{
			$valide = true;
			$value = "";
			if ($element['typ'] == "text2")
			{
				$element['name'] = explode(";",$element['name']);
				if(!empty($inputs[$element['name'][0]]))
					$value[0] = $inputs[$element['name'][0]];
				if(!empty($inputs[$element['name'][1]]))
					$value[1] = $inputs[$element['name'][1]];
			}
			elseif(!empty($inputs[$element['name']]))
			{ 
				$value = $inputs[$element['name']];
			}
			//echo $element['name'].":".$value."<br>";
			if(!empty($value))
			{
				switch ($element['typ']) {
					case "date":
						$values = preg_split("#[^0-9]#",$inputs[$element['id']],-1, PREG_SPLIT_NO_EMPTY);
						if (count($values)!=3)
						{
							unset($value); $valide=false; break;
						}
						if(strlen($values[0])==4)
						{
							$value = mktime(0,0,0,$values[1],$values[2],$values[0]);
						}
						else 
						{
							$value = mktime(0,0,0,$values[0],$values[2],$values[1]);
						}
						if (empty($value)||$value=-1)
						{
							unset($value); $valide=false; break;
						}
						else
						{
							$value = date("Y-m-d",$value);
						}
						break;
					case "email":
						$value = strtolower($value);
						if (!Zend_Validate::is($value, 'EmailAddress')) {
							unset($value); $valide=false;
						}
						$host = trim(substr($value, strpos($value, '@') + 1));
						if(empty($host) || !gethostbyname($host)) {
							unset($value); $valide=false;
						}
						break;
					case "text2":
						foreach (array_keys($value) as $key)
						{
							$value[$key] = trim(strip_tags($value[$key]));
							if (empty($value[$key]))
							{
								unset($value[$key]); $valide=false;
							}
							$value = array_values($value);
						}
						break;
					default:
						$value = trim(strip_tags($value));
						if(empty($value))
						{
							unset($value); $valide=true; break;
						}
						break;
				}
			}
			if($valide == false&&$element['required']==1)
			{
				$ret['v'][] = $element['id'];
				$ret['e'][$element['id']] = true;
			}
			elseif(empty($value)&&$element['required']==1)
			{
				$ret['e'][$element['id']] = true;
			}
			if (isset($value))
			{
				$this->sPOSTS[$element['id']] = $value;
			}			
		}
		//print_r($this->sPOSTS);
		return ($ret);
	}
}