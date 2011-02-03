{if $sNet}
	{assign var="sRealAmount" value=$sAmountNet|replace:",":"."}
{else}
	{if $sAmountWithTax}
		{assign var="sRealAmount" value=$sAmountWithTax|replace:",":"."}
	{else}
		{assign var="sRealAmount" value=$sAmount|replace:",":"."}
	{/if}
{/if}
<div class="trustedshops_form">
	<div class="grid_3">
		<form name="formSiegel" method="post" action="https://www.trustedshops.com/shop/certificate.php" target="_blank">
			<input type="image" src="{link file='templates/_default/frontend/_resources/images/logo_trusted_shop.gif'}" title="{s name='WidgetsTrustedShopsHeadline'}{/s}" />
			<input name="shop_id" type="hidden" value="{$this->config('TSID')}" />
		</form>
	</div>
	<div class="grid_11">
		<form id="formTShops" name="formTShops" method="post" action="https://www.trustedshops.com/shop/protection.php" target="_blank">
			<input name="shop_id" type="hidden" value="{$this->config('TSID')}" />
			<input name="title" type="hidden" value="{if $sUserData.billingaddress.salutation eq 'mr'}{s name='WidgetsTrustedShopsSalutationMr'}{/s}{elseif $sUserData.billingaddress.salutation eq 'ms'}{s name='WidgetsTrustedShopsSalutationMs'}{/s}{else}{s name='WidgetsTrustedShopsSalutationCompany'}{/s}{/if}" />
			<input name="email" type="hidden" value="{$sUserData.additional.user.email}" />
			<input name="first_name" type="hidden" value="{$sUserData.billingaddress.firstname}" />
			<input name="last_name" type="hidden" value="{$sUserData.billingaddress.lastname}" />
			<input name="street" type="hidden" value="{$sUserData.billingaddress.street} {$sUserData.billingaddress.streetnumber}" />
			<input name="zip" type="hidden" value="{$sUserData.billingaddress.zipcode}" />
			<input name="city" type="hidden" value="{$sUserData.billingaddress.city}" />
			<input name="country" type="hidden" value="{$sUserData.additional.country.countryname}" />
			<input name="phone" type="hidden" value="{$sUserData.billingaddress.phone}" />
			<input name="fax" type="hidden" value="{$sUserData.billingaddress.fax}" />
			<input name="delivery" type="hidden" value="" />
			<input name="amount" type="hidden" value="{$sRealAmount}" />
			<input name="curr" type="hidden" value="{$this->config('Currency')}" />
			<input name="KDNR" type="hidden" value="{$sUserData.billingaddress.customernumber}" />
			<input name="ORDERNR" type="hidden" value="{$sOrderNumber}" />
			
			{* Descriptiontext *}
			<p>
				{se name='WidgetsTrustedShopsText' class='actions'}{/se}
			</p>
	
			<input type="submit" class="button-right large" name="btnProtect"value="{s name='WidgetsTrustedShopsInfo'}{/s}" />
		</form>
	</div>
	<div class="clear">&nbsp;</div>
</div>