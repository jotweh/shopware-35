{if $sDispatches}
<div class="register grid_16 last first">
	<form name="" method="POST" action="{url action='calculateShippingCosts' sTargetAction=$sTargetAction}" class="payment">
		<div class="payment_method">
			<h2 class="headingbox_dark largesize">{s name='CheckoutDispatchHeadline'}Versandart{/s}</h2>
			
			{if $sDispatches|count>1}
				{foreach from=$sDispatches item=dispatch}
					<div class="grid_15 method">
						{block name='frontend_checkout_dispatch_fieldset_input_radio'}
						<div class="grid_5 first">
							<input id="confirm_dispatch{$dispatch.id}" type="radio" class="radio auto_submit" value="{$dispatch.id}" name="sDispatch" {if $dispatch.id eq $sDispatch.id}checked="checked"{/if} />
							<label class="description" for="confirm_dispatch{$dispatch.id}">{$dispatch.name}</label>
						</div>
						{/block}
						
						{block name='frontend_checkout_dispatch_fieldset_description'}
						{if $dispatch.description}
						<div class="grid_10 last">
							{$dispatch.description}
						</div>
						{/if}
						{/block}
					</div>
				{/foreach}
				
				{block name="frontend_checkout_shipping_action_buttons"}
					<div class="actions">
						<input type="submit" value="{s name='CheckoutDispatchLinkSend'}Ändern{/s}" class="button-right small_right right" />
					</div>
				{/block}
			{else}
				<div class="grid_15 method">
					{block name='frontend_checkout_dispatch_fieldset_input_radio'}
					<div class="grid_5 first">
						<label class="description" for="confirm_dispatch{$dispatch.id}">{$dispatch.name}</label>
					</div>
					{/block}
					
					{block name='frontend_checkout_dispatch_fieldset_description'}
					{if $dispatch.description}
					<div class="grid_10 last">
						{$dispatch.description}
					</div>
					{/if}
					{/block}
				</div>
			{/if}
						
			<div class="clear">&nbsp;</div>
		</div>
	</form>
</div>
<div class="space">&nbsp;</div>	
{/if}