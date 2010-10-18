	
{if $_GET.sTarget}
	<input name="sTarget" type="hidden" value="sale" />
{/if}

<div class="paypoint{if $sChoosenPayment==$sPayment.id} paypoint_active{/if}">
	<input class="radio" name="sPayment" value="{$sPayment.id}" type="radio" id="prepayment" {if $sChoosenPayment==$sPayment.id OR !$sChoosenPayment}checked{/if} />
	<label class="paylabel" for="prepayment">{$sPayment.description}</label><br />		

    {if $sPayment.additionaldescription}<p class="paydescr">{$sPayment.additionaldescription}</p>{/if}
</div>
