<div class="register grid_16 last first">
	<form name="frmRegister" method="post" action="{url controller=account action=savePayment sTarget='checkout'}" class="payment">
		<div class="payment_method">
			<h2 class="headingbox_dark largesize">{s name='RegisterPaymentHeadline'}Gewählte Zahlungsart{/s}</h2>
			
			{foreach from=$sPayments item=payment_mean name=register_payment_mean}
				<div class="grid_15 method method_hide">
					{block name='frontend_checkout_payment_fieldset_input_radio'}
					<div class="grid_5 first">
					<input type="radio" name="register[payment]" class="radio" value="{$payment_mean.id}" id="payment_mean{$payment_mean.id}"{if $payment_mean.id eq $sUserData.additional.payment.id} checked="checked"{/if} /> <label class="description" for="payment_mean{$payment_mean.id}">{$payment_mean.description}</label>
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
						<div class="grid_8 bankdata method_hide">
							{include file="frontend/plugins/payment/`$payment_mean.template`"}
						</div>
					{/if}
					{/block}
				</div>
			{/foreach}
			
			{block name="frontend_checkout_payment_action_buttons"}
				<div class="actions method_hide">
					<input type="submit" value="{s name='PaymentLinkSend'}Ändern{/s}" class="button-right large right" />
				</div>
			{/block}
			
			<div class="grid_15 method method_selected">
				<div class="grid_5 first">
					<input type="radio" name="" class="radio" value="{$sUserData.additional.payment.id}" checked="checked" />
					<label class="description">{$sUserData.additional.payment.description}</label>
				</div>
				<div class="grid_10 last">
					{$sUserData.additional.payment.additionaldescription}
				</div>
			</div>
			
			<div class="change">
				<a class="button-middle small" title="{s name='PaymentLinkChange'}Zahlungsart ändern{/s}" href="{url controller='account' action='payment' sTarget='checkout'}">
					{se name='PaymentLinkChange'}Zahlungsart ändern{/se}
				</a>
			</div>
			
			<div class="clear">&nbsp;</div>
		</div>
	</form>
</div>
<div class="space">&nbsp;</div>	