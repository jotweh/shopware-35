{* 
	BENÖTIGTE PARAMETER
	sLiveshoppingData > Liveshopping-Datensatz
*}

{* Zwischenspeicherung des aktuellen Preises als float *}
<span class="{$uniquekey}{$sLiveshoppingData.ordernumber}_price" style="display:none;">{$sLiveshoppingData.price}</span>
<span class="{$uniquekey}{$sLiveshoppingData.ordernumber}_lastupdatemin" style="display:block;"></span>

<script type="text/javascript">
{literal}
window.setInterval(function () {	
	updateLiveshopping({/literal}'{$uniquekey}','{$sLiveshoppingData.ordernumber}', '{$sLiveshoppingData.max_quantity_enable}', '{$sLiveshoppingData.max_quantity}', '{$sLiveshoppingData.sells}', '{$sLiveshoppingData.minPrice}', '{$sLiveshoppingData.typeID}', '{$sLiveshoppingData.valid_to_ts}'{literal});
},1000);
{/literal}
</script>