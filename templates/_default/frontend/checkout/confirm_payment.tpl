{if $sRegisterFinished}
<div class="register grid_16 last first">
	<form name="" method="POST" action="{url controller=account action=savePayment sTarget='checkout'}" class="payment">
		<div class="payment_method">
			<h2 class="headingbox_dark largesize">{s name='CheckoutPaymentHeadline'}Zahlungsart{/s}</h2>
			
			{foreach from=$sPayments item=payment_mean name=register_payment_mean}
				<div class="grid_15 method">
					{block name='frontend_checkout_payment_fieldset_input_radio'}
					<div class="grid_5 first">
						<input type="radio" name="register[payment]" class="radio" value="{$payment_mean.id}" id="payment_mean{$payment_mean.id}"{if $payment_mean.id eq $sPayment.id} checked="checked"{/if} />
						<label class="description" for="payment_mean{$payment_mean.id}">{$payment_mean.description}</label>
					</div>
					{/block}
					
					{block name='frontend_checkout_payment_fieldset_description'}
					<div class="grid_10 last">
						{$payment_mean.additionaldescription}
					</div>
					{/block}
					
					{block name='frontend_checkout_payment_fieldset_template'}
					<div class="payment_logo_{$payment_mean.name}"></div>
					{if "frontend/plugins/payment/`$payment_mean.template`"|template_exists}
						<div class="space">&nbsp;</div>
						<div class="grid_8 bankdata">
							{if $payment_mean.id eq $sPayment.id}
								{include file="frontend/plugins/payment/`$payment_mean.template`" form_data=$sPayment.data}
							{else}
								{include file="frontend/plugins/payment/`$payment_mean.template`"}
							{/if}
						</div>
					{/if}
					{/block}
				</div>
			{/foreach}
			
			{block name="frontend_checkout_payment_action_buttons"}
				<div class="actions">
					<input type="submit" value="{s name='CheckoutPaymentLinkSend'}Ändern{/s}" class="button-right large right" />
				</div>
			{/block}
			
			<div class="clear">&nbsp;</div>
		</div>
	</form>
</div>
<div class="space">&nbsp;</div>
{/if}