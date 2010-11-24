{extends file='frontend/checkout/cart_item.tpl'}

{* Article price *}
{block name='frontend_checkout_cart_item_details_inline'}
<p>
	{s name='CheckoutItemPrice'}{/s} {$sBasketItem.price|currency}
</p>
{/block}
{block name='frontend_checkout_cart_item_price'}{/block}

{block name='frontend_checkout_cart_item_quantity'}
{if $sLaststock.articles[$sBasketItem.ordernumber].OutOfStock == true}
<div class="grid_1">
	-
</div>
{else}
	{$smarty.block.parent}
{/if}
{/block}

{block name='frontend_checkout_cart_item_delivery_informations'}
{if $sLaststock.articles[$sBasketItem.ordernumber].OutOfStock == true}
	<div class="grid_3">
		<div class="status4">&nbsp;</div>
		<p class="deliverable2">{s name="CheckoutItemLaststock"}Nicht lieferbar!{/s}</p>
	</div>
{else}
	{$smarty.block.parent}
{/if}
{/block}


{* Tax price *}
{block name='frontend_checkout_cart_item_tax_price'}
<div class="grid_2">
	<div class="textright">
		{if $sUserData.additional.charge_vat}{$sBasketItem.tax|currency}{else}&nbsp;{/if}
	</div>
</div>
{/block}

{* Bundle price *}
{block name='frontend_checkout_cart_item_bundle_price'}
<div class="grid_3 push_3">
	<div class="textright">
		<strong>
			{$sBasketItem.amount|currency}*
		</strong>
	</div>
	<div class="clear">&nbsp;</div>
</div>
{/block}

{* Bundle tax price *}
{block name='frontend_checkout_cart_item_bundle_tax_price'}
<div class="grid_2 push_4">
	<div class="textright">
		{if $sUserData.additional.charge_vat}{$sBasketItem.tax|currency}{else}&nbsp;{/if}
	</div>
</div>
{/block}

{* Voucher price *}
{block name="frontend_checkout_cart_item_voucher_price"}
<div class="grid_3 push_3">
	<div class="textright">
		<strong>
			{if $sBasketItem.itemInfo}
				{$sBasketItem.itemInfo}
			{else}
				{$sBasketItem.price|currency}*
			{/if}
		</strong>
	</div>
	<div class="clear">&nbsp;</div>
</div>
{/block}

{* Voucher tax price *}
{block name='frontend_checkout_cart_item_voucher_tax_price'}
<div class="grid_2 push_4">
	<div class="textright">
		{if $sUserData.additional.charge_vat}{$sBasketItem.tax|currency}{else}&nbsp;{/if}
	</div>
</div>
{/block}

{* Premium price *}
{block name="frontend_checkout_cart_item_premium_price"}
<div class="grid_3 push_3">
	<div class="textright">
		<strong>
			{s name="CartItemInfoFree"}{/s}
		</strong>
	</div>
	<div class="clear">&nbsp;</div>
</div>
{/block}

{* Premium tax price *}
{block name='frontend_checkout_cart_item_premium_tax_price'}
<div class="grid_2 push_4">
	<div class="textright">
		{if $sUserData.additional.charge_vat}{$sBasketItem.tax|currency}{else}&nbsp;{/if}
	</div>
</div>
{/block}

{* Small quantitiy price *}
{block name='frontend_checkout_Cart_item_small_quantities_price'}
<div class="grid_3 push_3">
	<div class="textright">
		<strong>
			{if $sBasketItem.itemInfo}
				{$sBasketItem.itemInfo}
			{else}
				{$sBasketItem.price|currency}*
			{/if}
		</strong>
	</div>
	<div class="clear">&nbsp;</div>
</div>
{/block}

{* Small quanitity tax price *}
{block name='frontend_checkout_cart_item_small_quantites_tax_price'}
<div class="grid_2 push_4">
	<div class="textright">
		{if $sUserData.additional.charge_vat}{$sBasketItem.tax|currency}{else}&nbsp;{/if}
	</div>
</div>
{/block}

{* Rebate price *}
{block name='frontend_checkout_cart_item_rebate_price'}
<div class="grid_3 push_3">
	<div class="textright">
		<strong>
			{if $sBasketItem.itemInfo}
				{$sBasketItem.itemInfo}
			{else}
				{$sBasketItem.price|currency}*
			{/if}
		</strong>
	</div>
	<div class="clear">&nbsp;</div>
</div>
{/block}

{* Rebate tax price *}
{block name='frontend_checkout_cart_item_rebate_tax_price'}
<div class="grid_2 push_4">
	<div class="textright">
		{if $sUserData.additional.charge_vat}{$sBasketItem.tax|currency}{else}&nbsp;{/if}
	</div>
</div>
{/block}

{* Hide tax symbol *}
{block name='frontend_checkout_cart_tax_symbol'}{/block}