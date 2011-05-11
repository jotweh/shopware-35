{extends file="frontend/checkout/confirm.tpl"}

{block name='frontend_index_content_left'}{/block}

{* Main content *}
{block name="frontend_index_content"}
{if $PaymentErrorMessages}
<div class="error center bold grid_20" style="margin: 10px 0 10px 20px;width: 940px;">
	{$PaymentErrorMessages|escape|nl2br}
</div>
{/if}
<div id="payment" class="grid_20 register" style="margin:10px 0 10px 20px;width:959px;">
	
	<form name="frmRegister" method="post" action="{url action=direct}" class="payment">
		<h2 class="headingbox_dark largesize">{se name="PaymentHeader"}Bitte führen Sie nun die Zahlung durch:{/se}</h2>
		<div>
			<label for="kontonr">Kontonummer*:</label>
			<input type="text" class="text " id="kontonr" name="account_number" value="{$PaymentParams.kontonummer|escape}">
		</div>
		<div>
			<label for="blz">Bankleitzahl*:</label>
			<input type="text" class="text " id="blz" name="account_bank" value="{$PaymentParams.blz|escape}">
		</div>
		<div>
			<label for="bank2">Konto-Inhaber*:</label>
			<input type="text" class="text " id="bank2" name="account_holder" value="{$PaymentParams.kontoinhaber|escape}">
		</div>
		<p class="description">Die mit einem * markierten Felder sind Pflichtfelder.	
		</p>
		<div class="actions" style="margin: 20px">
			<input type="submit" value="Zahlung durchführen" class="button-right large right" />
		</div>
		<div class="space">&nbsp;</div>
	</form>
</div>

<div class="doublespace">&nbsp;</div>
{/block}