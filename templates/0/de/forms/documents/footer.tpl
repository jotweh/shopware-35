{if $sVoucher}
<div style="font-size:11px;color:#333;">
Für den nächsten Einkauf schenken wir Ihnen einen {$sVoucher.value} {$sVoucher.prefix} Gutschein
mit dem Code "{$sVoucher.code}".<br />
</div>
{/if}

{if $sBillingData.comment}
<div style="font-size:11px;color:#333;">
Kommentar:
{$sBillingData.comment}
</div>
{/if}

{if $sDispatch.name}
<div style="font-size:11px;color:#333;">
Gewählte Versandart:
{$sDispatch.name}
</div>
{/if}

{if $test}
<div id="footer" style="position:static;">
	{$style.footer.footer}
</div>
{else}
<div id="footer">
	{$style.footer.footer}
</div>
{/if}