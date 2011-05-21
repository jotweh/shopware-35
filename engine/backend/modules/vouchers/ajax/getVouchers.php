<?php
define('sAuthFile', 'sGUI');
define('sConfigPath',"../../../../../");
include("../../../../backend/php/check.php");
$result = new checkLogin();
$result = $result->checkUser();
if ($result!="SUCCESS"){
	die();
}

require_once("json.php");
$json = new Services_JSON();



function formatdate_ret ($date)
{
	if (!$date || $date == "0000-00-00") return "";
	//02.12.2005 <- 2005-12-02
	$date = explode("-",$date);
	return $date[2].".".$date[1].".".$date[0];
}


if(isset($_REQUEST["delete"]))
{
	//Deleted ID
	$delete = (int)$_REQUEST["delete"];
	//When the vouchers are deleted, delete all corresponding vouchercodes, too
	if(is_numeric($delete)){
		$sql = "DELETE FROM s_emarketing_vouchers WHERE id = $delete";
		mysql_query($sql);
		$sql = "DELETE FROM s_emarketing_voucher_codes WHERE voucherID = $delete";
		mysql_query($sql);
	}
}

if(isset($_POST["feedID"]))
{
	$sql_where = "WHERE id=".intval($_POST["feedID"]);
	$sql = "SELECT *
		FROM `s_emarketing_vouchers`
		$sql_where";
	$result = mysql_query($sql);
}
else {
	// Select all 
	$start = intval($_POST["start"]);
	$limit = intval($_POST["limit"]);
	if (!$limit) $limit = 20;
	
	if ($_POST["search"]) {
		if (strlen($_POST["search"])>1){
			$search = "%".mysql_real_escape_string(utf8_decode($_POST["search"]))."%";
		}else {
			$search = mysql_real_escape_string(utf8_decode($_POST["search"]))."%";
		}
		$searchSQL = "
		 WHERE
			description LIKE '$search'
		OR
			vouchercode LIKE '$search'
		OR
			value LIKE '$search'
		";
	}
	
	if (!$_POST["sort"]) $_POST["sort"] = "valid_from";
	if (!$_POST["dir"]) $_POST["dir"] = "ASC";
	$sort = mysql_real_escape_string($_POST["sort"]);
	if($sort == "numberofunits") $sort = "checkedIn";
	$dir = mysql_real_escape_string($_POST["dir"]);

	$sql = "
		SELECT *  , IF( modus = '0', 
		(SELECT count(*) FROM s_order_details WHERE articleordernumber =s_emarketing_vouchers.ordercode AND s_order_details.ordernumber!=0), 
		(SELECT count(*) FROM s_emarketing_voucher_codes WHERE voucherID =s_emarketing_vouchers.id AND cashed=1))  AS checkedIn
		FROM s_emarketing_vouchers
		{$searchSQL}
		ORDER BY {$sort} {$dir} LIMIT {$start}, {$limit}
	";
	
}
	$rows = array();
	$result = mysql_query($sql);
	if($result&&mysql_num_rows($result))
		while($row = mysql_fetch_assoc($result))
		{
			
			//++++++++++++++++++Validate the DB Data++++++++++++++++++++
			$row['description'] = trim(utf8_encode($row['description']));
			$row['vouchercode'] = trim(utf8_encode($row['vouchercode']));
			$row['restrictarticles'] = trim(utf8_encode($row['restrictarticles']));
			
			$row['modus'] = intval($row['modus']);
			$row['numberofunits'] = empty($row['numberofunits']) ? 0 : (int) $row['numberofunits'];
			$row['value'] = empty($row['value']) ? 0.0 : (double) $row['value'];
			$row['minimumcharge'] = empty($row['minimumcharge']) ? null : (double) $row['minimumcharge'];
			$row['shippingfree'] = empty($row['shippingfree']) ? null : (int) $row['shippingfree'];
			$row['strict'] = empty($row['strict']) ? null : (int) $row['strict'];
			$row['bindtosupplier'] =  empty($row['bindtosupplier']) ? null : (int) $row['bindtosupplier']; 
			$row['customergroup'] = empty($row['customergroup']) ? null : (int) $row['customergroup']; 
			$row['subshop'] = empty($row['subshopID']) ? null : (int) $row['subshopID']; 
			$row['ordercode'] = trim(utf8_encode($row['ordercode']));;
			$row['modus'] = (int) $row['modus'];
			$row['id'] = (int) $row['id'];
			$row['percental'] = (int) $row['percental'];
			$row['numorder'] = empty($row['numorder']) ? null : (int) $row['numorder'];
			$row['valid_from'] = formatdate_ret($row['valid_from']);
			$row['valid_to'] = formatdate_ret($row['valid_to']);
			$row["checkedIn"] = intval($row["checkedIn"]);
			$row["taxconfig"] = $row["taxconfig"];
			//++++++++++++++++++End: Validate the DB Data+++++++++++++++
			
			//clone function
			if(!empty($_REQUEST['clone']))
			{
				$row['vouchercode'] = "";
				$row['ordercode'] = "";
			}
			
			$rows[] = $row;
		}
	$sql = "
		SELECT count(*) as count FROM s_emarketing_vouchers 
	";
	
	$result = mysql_query($sql);
	if($result&&mysql_num_rows($result)){
		$count = (int) mysql_result($result,0,"count");
	}
	else {
		$count = 0;
	}
	echo  $json->encode(array("articles"=>$rows,"count"=>$count));
?>