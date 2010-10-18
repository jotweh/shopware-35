<div id="header">


<div id="head_left">
<div id="head_top">
	{if $style.header.top}
	{$style.header.top}
	{/if}
</div>
<div class="fixfloat"></div>
{if $kDetails==1 && $sUserData}
{if $typ == '1'}
<div id="head_sender">
	<p class="sender">{$style.sender.sender}</p>
	{$sUserData.shippingaddress.company}<br />
	{if $sUserData.shippingaddress.department}{$sUserData.shippingaddress.department}<br />{/if}
	<!--{if $sUserData.shippingaddress.salutation eq "mr"}
		Herr
	{elseif $sUserData.shippingaddress.salutation eq "ms"}
		Frau
	{/if}<br />-->
	{$sUserData.shippingaddress.firstname} {$sUserData.shippingaddress.lastname}<br />
	{$sUserData.shippingaddress.street} {$sUserData.shippingaddress.streetnumber}<br />
	{$sUserData.shippingaddress.zipcode} {$sUserData.shippingaddress.city}<br />
	{$sUserData.additional.countryShipping.countryen}<br />
</div>
{else}
<div id="head_sender">
	<p class="sender">{$style.sender.sender}</p>
	{$sUserData.billingaddress.company}<br />
	<!--{if $sUserData.billingaddress.department}{$sUserData.shippingaddress.department}<br />{/if}
	{if $sUserData.billingaddress.salutation eq "mr"}
		Herr
	{elseif $sUserData.billingaddress.salutation eq "ms"}
		Frau
	{/if}<br />-->
	{if $sUserData.billingaddress.department}{$sUserData.billingaddress.department}<br />{/if}
	{$sUserData.billingaddress.firstname} {$sUserData.billingaddress.lastname}<br />
	{$sUserData.billingaddress.street} {$sUserData.billingaddress.streetnumber}<br />
	{$sUserData.billingaddress.zipcode} {$sUserData.billingaddress.city}<br />
	{$sUserData.additional.country.countryen}<br />
</div>
{/if}
{elseif $kDetails!=1 && $sUserData}
<div id="head_sender"></div>
{else}
<div id="head_sender">
	<p class="sender">{$style.sender.sender}</p>
	Demo GmbH<br>
	Herr<br>
	Max Mustermann<br>
	Straﬂe 3<br>
	00000 Musterstadt<br>
	GERMANY<br>
</div>
{/if}

<div id="head_right">
	{$style.header.right}
	<b>
	Kunden-Nr.: {$sUserData.billingaddress.customernumber|string_format:"%06d"}<br />
	{if $sUserData.billingaddress.ustid}
	USt-IdNr.: {$sUserData.billingaddress.ustid|replace:" ":""|replace:"-":""}<br />
	{/if}
	Bestell-Nr.: {$sBillingData.ordernumber|string_format:"%06d"}<br />
	{if $sSettings.bid}
	{if $typ == '3'}
	Beleg-Nr.: {$sSettings.bid|string_format:"%06d"}<br />
	{else}
	Rechnungs-Nr.: {$sSettings.bid|string_format:"%06d"}<br />
	{/if}
	{/if}
	Datum: {$sSettings.date}<br />
	{if $sSettings.delivery_date}
	Liefertermin: {$sSettings.delivery_date}<br />
	{/if}
	</b>
</div>

<div class="fixfloat"></div>

<div id="head_under">
	{if $typ == '1'}
	<h1 style="font-size: 20px;">Lieferschein Nr. {$ID|string_format:"%06d"}</h1>
	{elseif $typ == '2'}
	<h1 style="font-size: 20px;">Gutschrift Nr. {$ID|string_format:"%06d"}</h1>
	{elseif $typ == '3'}
	<h1 style="font-size: 20px;">Stornierung</h1>
	{else}
	<h1 style="font-size: 20px;">Rechnung Nr. {$ID|string_format:"%06d"}</h1>
	{/if}
	{if $sBillingData.pages > 1}
	 Seite {$kDetails} von {$sBillingData.pages}
	{/if}
</div>
</div>
</div>
<div class="fixfloat"></div>