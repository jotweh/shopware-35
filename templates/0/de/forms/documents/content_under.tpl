<div id="under" style="clear: both;">
  <table width="300px">
  <tbody>
  <tr>
  	<td align="right" width="100px">Gesamtkosten Netto:</td>
  	<td align="right" width="200px">{$sBillingData.invoice_amount_netto|replace:".":","} {$sCurrency}</td>
  </tr>
  {if $sBillingData.netto2 != true}
  {foreach item=tax key=kTax from=$sBillingData.tax}
  <tr><td colspan="7" class="line"></td></tr>
  <tr>
  	<td align="right">zzgl. {$kTax}% MwSt:</td>
  	<td align="right">{$tax|replace:".":","} {$sCurrency}</td>
  </tr>
  {/foreach}
  {/if}
  {if $sBillingData.invoice_shipping}
   <!--
  <tr><td colspan="7" class="line"></td></tr>
  <tr>
  	<td align="right">Versandkosten:</td>
  	<td align="right">{$sBillingData.invoice_shipping|replace:".":","} {$sCurrency}</td>
  </tr>
  {/if}
  -->
  {if $sBillingData.voucher}
    <!--
  <tr><td colspan="7" class="line"></td></tr>
  <tr>
  	<td align="right">Enthaltender Rabatt:</td>
  	<td align="right">{$sBillingData.voucher|replace:".":","} {$sCurrency}</td>
  </tr>
  -->
  {/if}
  <tr><td colspan="7" class="line2"></td></tr>
  <tr>
    <td align="right"><b>Gesamtkosten:</b></td>
    {if $sBillingData.netto2 != true}
    <td align="right"><b>{$sBillingData.invoice_amount|replace:".":","} {$sCurrency}</b></td>
    {else}
    <td align="right"><b>{$sBillingData.invoice_amount_netto|replace:".":","} {$sCurrency}</b></td>
    {/if}
  </tr>
  </tbody>
  </table>
 </div>
{if $typ == 0}
<div  style="clear: both;float: left;">
{if $sBillingData.netto2 == true}
<p>Hinweis: Der Empfänger der Leistung schuldet die Steuer.</p>
{/if}
<p>Gew&auml;hlte Zahlungsart {$sBillingData.payment}</p>
{$style.content_middle.text}
{if $sCurrencyFactor > 1}
	<br>Euro Umrechnungsfaktor: {$sCurrencyFactor|replace:".":","}
{/if}
</div>
{/if}