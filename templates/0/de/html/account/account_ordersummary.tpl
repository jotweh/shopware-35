{if !$sOpenOrders}
<fieldset><p class="alert">{$sConfig.sSnippets.sAccountOrderNo}</p></fieldset>
{else}
<h1 style="margin-left:10px;">{* sSnippet: Orders sorted by Date *}{$sConfig.sSnippets.sAccountOrderssortedbydate}</h1>

{* OFFENE POSITIONEN START *}
<ul class="offers">
{foreach from=$sOpenOrders item=offerPosition}
{assign var="ordernumber" value=""}
<form name="sNewOrder" method="GET" action="{$sStart}">
<input name="sViewport" type="hidden" value="basket">
<input name="sAdd" type="hidden" value="-1111">
    <fieldset style="margin-bottom: 25px;">
    <li>
    <div class="orderoverview_active">
        <div style="padding:10px;">
            <span class="col1">
            <strong>{* sSnippet: From: *}{$sConfig.sSnippets.sAccountfrom}</strong> {$offerPosition.datum}<br />
            <strong>{* sSnippet: Ordernumber: *}{$sConfig.sSnippets.sAccountOrdernumber}</strong> {$offerPosition.ordernumber}<br />
            <strong>{* sSnippet: Order Total: *}{$sConfig.sSnippets.sAccountOrderTotal}</strong> {$offerPosition.currency_html} {$offerPosition.invoice_amount}<br />
            <strong>Versandart:</strong> {$offerPosition.dispatch.name}<br />
            
            {if $offerPosition.trackingcode}
				<strong>{* sSnippet: Package tracking *}{$sConfig.sSnippets.sAccountPackagetracking}</strong> 
				{if $offerPosition.dispatch.status_link}
					{eval var=$offerPosition.dispatch.status_link}
				{else}
					{$offerPosition.trackingcode}
				{/if}
			{/if}
            </span>
            <span class="col2">
                <p class="">
                {if $offerPosition.status==0}
                {* sSnippet: Order has not yet been processed *}{$sConfig.sSnippets.sAccountOrdernotvetprocessed}
                {elseif $offerPosition.status==1}
                {* sSnippet: Order is in progress *}{$sConfig.sSnippets.sAccountOrderinprogress}
                {elseif $offerPosition.status==2}
                {* sSnippet: Bestellung wurde verschickt *}{$sConfig.sSnippets.sAccountOrderhasbeenshipped}
                {elseif $offerPosition.status==3}
                {* sSnippet: Order was partially shipped *}{$sConfig.sSnippets.sAccountOrderpartiallyshipped}
                {elseif $offerPosition.status==4}
                {* sSnippet: Order was canceled *}{$sConfig.sSnippets.sAccountOrdercanceled}
                {/if}      
                </p><br />
                {if $offerPosition.comment}{* sSnippet: A comment was deposited *}{$sConfig.sSnippets.sAccountACommentisdeposited}{/if}
            </span>
            <input type="submit" value="{* sSnippet: Repeat the Order *}{$sConfig.sSnippets.sAccountRepeatOrder}">
            <div class="clearfix"></div>
    </div>
          <table width="770px" border="0" cellspacing="0" cellpadding="0" class="basket-middle">
            <tr>
              <th class="artikel">{* sSnippet: Article *}{$sConfig.sSnippets.sAccountArticle}</th>
              <th class="anzahl">{* sSnippet: Number *}{$sConfig.sSnippets.sAccountNumber}</th>
              <th class="stck">{* sSnippet: Unit price *}{$sConfig.sSnippets.sAccountUnitprice}</th>
              <th class="sum">{* sSnippet: Total *}{$sConfig.sSnippets.sAccountTotal}</th>
            </tr>
            
            {foreach from=$offerPosition.details item=article}
            {if $article.modus == 0}
            	{assign var="ordernumber" value=$ordernumber|cat:$article.articleordernumber|cat:";"}
            {/if}
            <tr>
                <td>{$article.name}
                {if $article.esdarticle}
                	<br /><a href="{$article.esdLink}"><p class="download">{* sSnippet: Download Now *}{$sConfig.sSnippets.sAccountDownloadNow}</p></a>
                {/if}	
                </td>
                <td>{$article.quantity}</td>
                <td>{if $article.price}{$article.price}{else}{* sSnippet: FREE *}{$sConfig.sSnippets.sAccountFree}{/if}</td>
                <td class="sum">{if $article.amount}{$offerPosition.currency_html} {$article.amount}{else}{* sSnippet: FREE *}{$sConfig.sSnippets.sAccountFree}{/if}</td>
        
            </tr>
             {if $article.serial}
                <tr>
                <td colspan="4"><strong>{* sSnippet: Your Serialnumber to *}{$sConfig.sSnippets.sAccountyourSerialnumber} {$article.name}:</strong> {$article.serial}</td>
                </tr>
              {/if}
            {/foreach}
            <input type="hidden" name="sAddAccessories" value="{$ordernumber}">
            <tr>
                    <td>&nbsp;</td>
                    <td colspan="2" style="text-align: right;"><strong>{* sSnippet: Shipping *}{$sConfig.sSnippets.sAccountShipping}</strong></td>
                    <td class="sum"><strong>{$offerPosition.currency_html} {$offerPosition.invoice_shipping}</strong></td>
                
            </tr>
            <tr>
                    <td>&nbsp;</td>
                    {if $offerPosition.taxfree}
                    <td colspan="2" style="text-align: right;"><strong>{* sSnippet: Grand total *}{$sConfig.sSnippets.sAccountNetGrandTotal}</strong></td>
                    {else}
                    <td colspan="2" style="text-align: right;"><strong>{* sSnippet: Grand total *}{$sConfig.sSnippets.sAccountgrandtotal}</strong></td>
                    {/if}
                    <td class="sum"><strong>{$offerPosition.currency_html} {$offerPosition.invoice_amount}</strong></td>
                
            </tr>
        </table>
        {if $offerPosition.comment}
        <div class="comment">
        {* sSnippet: Comment *}{$sConfig.sSnippets.sAccountComment}<br />
        {$offerPosition.comment}</div>
    </div>{/if}
    </li></fieldset>
</form>
{/foreach}


</ul>
{/if}