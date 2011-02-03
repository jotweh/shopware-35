<?php
define('sAuthFile', 'sGUI');
define('sConfigPath',"../../../../");
include("../../../backend/php/check.php");
$result = new checkLogin();
$result = $result->checkUser();
if ($result!="SUCCESS"){
echo "
	<html><title>Time-Out</title><head></head>
	<script language=\"javascript\">
	parent.location.reload();
	</script>
	<body>Bitte loggen Sie sich neu ein!</body></html>
	";
	die();
}
// *****************
$articleID = (int)$_GET["article"];
?>


<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Suche</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="../../../backend/css/modules.css" rel="stylesheet" type="text/css" />
<link href="../../../backend/css/icons.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" type="text/css" href="../../../vendor/ext/resources/css/ext-all.css" />
<script type="text/javascript" src="../../../vendor/ext/adapter/ext/ext-base.js"></script>


<script type="text/javascript" src="../../../vendor/ext/ext-all.js"></script>

</head>
<style>
/*
 * Ext JS Library 1.0
 * Copyright(c) 2006-2007, Ext JS, LLC.
 * licensing@extjs.com
 * 
 * http://www.extjs.com/license
 */



</style>
<body>
<div id="containerMsg" style="float:left"> 
</div>
<div id="container" style="float:left"> 
</div>
<?php
$sql = "
			SELECT SUM(price*quantity) AS amount FROM s_order_details, s_order WHERE articleID=$articleID
			AND s_order.id=s_order_details.orderID
			AND s_order.status != 4 AND s_order.status != -1
			"; 
$queryTotalAmount = mysql_query($sql);

if (@mysql_num_rows($queryTotalAmount)){
	$totalAmount = $sCore->sFormatPrice(mysql_result($queryTotalAmount,0,"amount"));
}else {
	$totalAmount = "0,00";
}
$sql = "
	SELECT 
		sum(od.quantity) AS `Sales`
	FROM 
		s_order_details as od,
		s_order as o
	WHERE '$articleID'=od.articleID
	AND
		o.id=od.orderID
	AND o.status != 4 AND o.status != -1
	AND 
		o.ordertime >= DATE(DATE_SUB(NOW(), INTERVAL 30 DAY))
	GROUP BY articleID
";
$result=mysql_query($sql);
if (@mysql_num_rows($result)){
	$Sales = mysql_result($result,0,"Sales");
}else {
	$Sales = "0";
}

$sql = "
	SELECT 
		count(articleID) AS `Views`
	FROM 
		s_emarketing_lastarticles
	WHERE 
		articleID = '$articleID'
	AND
		time >= DATE(DATE_SUB(NOW(), INTERVAL 30 DAY))
	GROUP BY 
		articleID
";
$result=mysql_query($sql);
if (@mysql_num_rows($result)){
	$Views = mysql_result($result,0,"Views");
}else {
	$Views = "0";
}

?>
<script type="text/javascript">
/*!
 * Ext JS Library 3.0.0
 * Copyright(c) 2006-2009 Ext JS, LLC
 * licensing@extjs.com
 * http://www.extjs.com/license
 */
Ext.chart.Chart.CHART_URL = '../../../vendor/ext/resources/charts.swf';

Ext.onReady(function(){

    var store = new Ext.data.JsonStore({
        fields:['name', 'buys', 'views'],
        <?php
        for ($i=1;$i<=12;$i++){
			$month = $i;
			$year = date("Y");
			// Build query
			$sql = "
			SELECT SUM(price*quantity) AS amount FROM s_order_details, s_order WHERE articleID=$articleID
			AND MONTH(ordertime)=$month AND YEAR(ordertime)=$year AND s_order.id=s_order_details.orderID
			AND s_order.status != 4 AND s_order.status != -1
			"; 
			
			$queryMonth = mysql_query($sql);
			
			
			$amount = @mysql_result($queryMonth,0,"amount") ? @mysql_result($queryMonth,0,"amount") : 0;
			
			$monthText = date("M",mktime(0,0,0,$month,1,$year));
			//echo "<set label='$monthText' value='$amount' />\n";
			$data[] = "{name:'$monthText', buys: $amount, views: 3000000}";
			
		}
        ?>
        data: [
            <?php echo implode(",\n",$data)?>
        ]
    });

    // extra extra simple
    new Ext.Panel({
        title: 'Umsatz nach Monaten',
        renderTo: 'container',
        width:600,
        height:400,
        layout:'fit',
        frame:true,
        animate: true,
		iconCls:'chart',
        items: {
            xtype: 'linechart',
            store: store,
            xField: 'name',
            yField: 'buys',
			listeners: {
				itemclick: function(o){
					var rec = store.getAt(o.index);
					Ext.example.msg('Item Selected', 'You chose {0}.', rec.get('name'));
				}
			}
        },chartStyle: {
                padding: 10,
                animationEnabled: true,
        }
    });

   new Ext.Panel({
		title: 'Statistiken',
		normal: false,
		renderTo: 'containerMsg',
		width: 600,
		height: 400,
		html: '<strong><?php echo $sLang["articles"]["statistics_Total_sales"] ?> </strong><?php echo $totalAmount ?> <?php echo $sLang["articles"]["statistics_euro"] ?><strong><?php echo $sLang["articles"]["statistics_Number_of_sales"] ?> </strong><?php echo $Sales ?><strong><?php echo $sLang["articles"]["statistics_Number_of_requests"] ?> </strong><?php echo $Views ?>'
	});


    // more complex with a custom look
   
});
</script>
</body>
</html>