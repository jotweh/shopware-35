
<div class="paypoint{if $sChoosenPayment==$sPayment.id} paypoint_active{/if}">
<input class="radio" name="sPayment" value="{$sPayment.id}" id="debit" type="radio" {if $sChoosenPayment==$sPayment.id OR (!$sChoosenPayment AND $sConfig.sDEFAULTPAYMENT==$sPayment.id)}checked{/if} />
<label class="paylabel" for="debit">{$sPayment.description}</label><br />		
    {if $sPayment.additionaldescription}<p class="paydescr">{$sPayment.additionaldescription}</p>{/if}
<!--  BANKLASTSCHRIFT  START-->  
<p class="none">
<label for="kontonr">{* sSnippet: account number *}{$sConfig.sSnippets.sPaymentaccountnumber}</label>
<input name="sDebitAccount" type="text" id="kontonr" value="{$_POST.sDebitAccount}" class="normal {if $sErrorFlag.sDebitAccount}instyle_error{/if}" />
</p>
<p class="none">
<label for="blz"> {* sSnippet: bank code number *}{$sConfig.sSnippets.sPaymentbankcodenumber}</label>
<input name="sDebitBankcode" type="text" id="blz" value="{$_POST.sDebitBankcode}" class="normal {if $sErrorFlag.sDebitBankcode}instyle_error{/if}" />
</p>
<p class="none">
<label for="bank">{* sSnippet: your bank *}{$sConfig.sSnippets.sPaymentyourbank}</label>
<input name="sDebitBankName" type="text" id="bank" value="{$_POST.sDebitBankName}" class="normal {if $sErrorFlag.sDebitBankName}instyle_error{/if}" />
</p>
<p class="none">
<label for="bank">{* sSnippet: your bank *}{$sConfig.sSnippets.sPaymentyourname}</label>
<input name="sDebitBankHolder" type="text" id="bank" value="{$_POST.sDebitBankHolder}" class="normal {if $sErrorFlag.sDebitBankHolder}instyle_error{/if}" />
</p>
<p class="description">{* sSnippet: the fields marked with * are mandatory *}{$sConfig.sSnippets.sPaymentmarkedfieldsare}	
</p>
	
<!--  BANKLASTSCHRIFT END-->	
</div>		