{if $_GET.sTarget}
	<input name="sTarget" type="hidden" value="sale" />
{/if}
<div class="paypoint">
	<input class="radio" name="sPayment" id="money" value="{$sPayment.id}" type="radio" {if $sChoosenPayment==$sPayment.id OR (!$sChoosenPayment AND $sConfig.sDEFAULTPAYMENT==$sPayment.id)}checked{/if} />
	<label class="paylabel" for="money">{$sPayment.description}</label>
  {if $sChoosenPayment==$sPayment.id}<span class="enabled">{* sSnippet: Payment currently selected *}{$sConfig.sSnippets.sPaymentcurrentlyselected}</span>{/if}<br />
  {if $sPayment.additionaldescription}<p class="paydescr">{$sPayment.additionaldescription}</p>{/if}
    <div class="payment_img_{$sPayment.name}"></div>
</div>
