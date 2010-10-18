{* FORM_BOX *}
<div class="form_box">
    <p class="heading">{* sSnippet: Download sorted by date *}{$sConfig.sSnippets.sAccountDownloadssortedbydate}</p>
    <fieldset>
        <div class="line">		
            {foreach from=$sOpenOrders item=offerPosition}
                {foreach from=$offerPosition.details item=article}
                    {if $article.esdarticle}
                        <div style="float: left; width: 400px; height: 45px; padding: 0 0 0 10px;">
                        {$offerPosition.datum}<br />
                        <strong>{$article.name}</strong><br />
                            {if $article.serial}
                             {* sSnippet: Your Serialnumber *}{$sConfig.sSnippets.sAccountyourSerialnumber} <strong>{$article.serial}</strong>
                            {/if}
                        </div>
                        
                            {if $article.esdarticle}
                                <div style="float: left; width: 130px; height: 45px; padding: 0;">
                                <a href="{$article.esdLink}" class="button">{* sSnippet: Download *}{$sConfig.sSnippets.sAccountDownload}</a>
                                </div>
                            {/if}
                    {/if}
                {/foreach}
            {/foreach}
        <div class="fixfloat"></div>
        </div>
    </fieldset>
</div>
{* /FORM_BOX *}