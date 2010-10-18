
<div id="content_middle">
      <table cellpadding="0" width="100%">
        <tbody valign="top">
        <tr class="head">
          <td align="left" width="5%">
           <b>Pos.</b>
          </td>
          <td align="left" width="10%">
           <b>Art-Nr.</b>
          </td>
          {if $sBillingData.netto2 != true}
          <td align="left" width="48%">
          {else}
          <td align="left" width="55%">
          {/if}
           <b>Bezeichnung</b>
          </td>
          <td align="right" width="5%">
           <b>Anz.</b>
          </td>
          {if $sBillingData.netto2 != true}
          <td align="right" width="6%">
           <b>MwSt.</b>
          </td>
          {/if}
          {if $sBillingData.netto == true||$sBillingData.netto2 == true}
          <td align="right" width="10%">
           <b>Netto Preis</b>
          </td>
          {/if}
          {if $sBillingData.netto != true&&$sBillingData.netto2 != true}
          <td align="right" width="10%">
           <b>Brutto Preis</b>
          </td>
          {/if}
          {if $sBillingData.netto == true||$sBillingData.netto2 == true}
          <td align="right" width="12%">
           <b>Netto Gesamt</b>
          </td>
          {else}
          <td align="right" width="12%">
           <b>Brutto Gesamt</b>
          </td>
          {/if}
        </tr>
       {foreach item=Entry key=kEntry from=$Details}
	   {if $kEntry == 1}
       <tr><td colspan="8" class="line2"></td></tr>
       {else}
        <tr><td colspan="8" class="line"></td></tr>
       {/if}
        <tr>
          <td valign="top">{$kEntry}</td>
          <td valign="top">{$Entry.articleordernumber}</td>
          <td valign="top" class="name">{$Entry.name}</td>
          <td valign="top" align="right">{$Entry.quantity}</td>
          {if $sBillingData.netto2 != true}
          <td valign="top" align="right">{$Entry.tax} %</td>
          {/if}
          {if $sBillingData.netto == true||$sBillingData.netto2 == true}
          <td valign="top" align="right">{$Entry.netto|replace:".":","} {$sCurrency}</td>
          {/if}
          {if $sBillingData.netto != true&&$sBillingData.netto2 != true}
          <td valign="top" align="right">{$Entry.price|replace:".":","} {$sCurrency}</td>
          {/if}
          {if $sBillingData.netto != true&&$sBillingData.netto2 != true}
          <td valign="top" align="right">{$Entry.amount|replace:".":","} {$sCurrency}</td>
          {else}
           <td valign="top" align="right">{$Entry.amount_netto|replace:".":","} {$sCurrency}</td>
          {/if}
        </tr>
        {/foreach}
        
        
  </tbody>
  </table>
  {if $kDetails == $sBillingData.pages}
	{include file='content_under.tpl'}
  {/if}
</div>