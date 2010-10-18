<div id="header">
<div id="head_right">
	{$style.header.right}
	<b>
	Kunden Nr.: {$sUserData.id|string_format:"%06d"}<br />
	Bestellnummer Nr.: {$sBillingData.ordernumber|string_format:"%06d"}<br />
	Datum: {$sSettings.date}<br />
	{if $sSettings.delivery_date}
	Liefertermin: {$sSettings.delivery_date}<br />
	{/if}
	</b>
</div>

<div id="head_left">
{if $typ == '1'}
<div id="head_sender">
	<p class="sender">{$style.sender.sender}</p>
	{$sUserData.shippingaddress.company}<br />
	{if $sUserData.shippingaddress.salutation eq "mr"}
		Herr
	{elseif $sUserData.shippingaddress.salutation eq "ms"}
		Frau
	{/if}<br />
	{$sUserData.shippingaddress.firstname} {$sUserData.shippingaddress.lastname}<br />
	{$sUserData.shippingaddress.street} {$sUserData.shippingaddress.streetnumber}<br />
	{$sUserData.shippingaddress.zipcode} {$sUserData.shippingaddress.city}<br />
	{$sUserData.additional.country.countryen}<br />
</div>
{else}
<div id="head_sender">
	<p class="sender">{$style.sender.sender}</p>
	{$sUserData.billingaddress.company}<br />
	{if $sUserData.billingaddress.salutation eq "mr"}
		Herr
	{elseif $sUserData.billingaddress.salutation eq "ms"}
		Frau
	{/if}<br />
	{$sUserData.billingaddress.firstname} {$sUserData.billingaddress.lastname}<br />
	{$sUserData.billingaddress.street} {$sUserData.billingaddress.streetnumber}<br />
	{$sUserData.billingaddress.zipcode} {$sUserData.billingaddress.city}<br />
	{$sUserData.additional.country.countryen}<br />
</div>
{/if}


<div id="head_under">
	{if $typ == '1'}
	<h1 style="font-size: 20px;">Lieferschein Nr. {$ID|string_format:"%06d"}</h1>
	{elseif $typ == '2'}
	<h1 style="font-size: 20px;">Gutschrift Nr. {$ID|string_format:"%06d"}</h1>
	{else}
	<h1 style="font-size: 20px;">Rechnung Nr. {$ID|string_format:"%06d"}</h1>
	{/if}
</div>

</div>

</div>