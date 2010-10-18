{if $_GET.sTarget}
	<input name="sTarget" type="hidden" value="sale" />
{/if}
<div class="paypoint{if $sChoosenPayment==$sPayment.id} paypoint_active{/if}">
	<input class="radio" name="sPayment" id="cash" value="{$sPayment.id}" type="radio" {if $sChoosenPayment==$sPayment.id OR (!$sChoosenPayment AND $sConfig.sDEFAULTPAYMENT==$sPayment.id)}checked{/if} />
	<label class="paylabel" for="cash">{$sPayment.description}</label><br />

    {if $sPayment.additionaldescription}<p class="paydescr">{$sPayment.additionaldescription}</p>{/if}
</div>

