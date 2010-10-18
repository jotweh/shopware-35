{* COL_CENTER *}
    <div class="col_center" style="padding-top:0;margin-top:0;">
            
    {foreach from=$sCampaign.containers item=sCampaignContainer}
    
        {if $sCampaignContainer.type == "ctBanner"}
        
            {* CONTAINER_BANNER *}
            {if $sCampaignContainer.data.link}
                <a href="{$sCampaignContainer.data.link}" target="{$sCampaignContainer.data.linkTarget}"><img src="{$sCampaignContainer.data.image}" style="margin-bottom:10px;" /></a>
            {else}
                <img src="{$sCampaignContainer.data.image}" style="margin-bottom:10px;">
            {/if}
            {* /CONTAINER_BANNER *}
        
        {elseif $sCampaignContainer.type == "ctLinks"}
        
        {* CONTAINER_LINKS *}
            <div class="cat_text">
            <h2>{$sCampaignContainer.description}</h2>
                <ul style="margin: 10px 0 5px 0px;">
                {foreach from=$sCampaignContainer.data item=sLink}
                    <li style="background:none;"><a href="{$sLink.link}" target="{$sLink.target}" class="ico link">{$sLink.description}</a></li>
                {/foreach}
                </ul>
            </div>
        {* /CONTAINER_LINKS *}
        
        {elseif $sCampaignContainer.type == "ctArticles"}
         
        {* CONTAINER_ARTICLES *}
            {* LISTING_BOX2 *}
            <div style="margin-top: 20px;">
                <!-- container headline --><h2>{$sCampaignContainer.description}</h2>
                {foreach from=$sCampaignContainer.data item=sArticle key=key  name="counter"}
                {if $sArticle.mode=="gfx"}
                	{if $sArticle.link}
                	<a href="{$sArticle.link}" {if $sArticle.linkTarget}target="{$sArticle.linkTarget}"{/if}><img src="{$sArticle.img}" style="margin: 9px 0 0 0; float: left;"></a>
                	{else}
                	<img src="{$sArticle.img}" style="margin: 9px 0 0 0; float: left;">
                	{/if}
                {else}
                    {include file="articles/article_box_3col.tpl" sArticle=$sArticle}
                {/if}
                {/foreach}
                <div class="listing_box_cap2"></div>
            </div>
            <div class="fixfloat"></div>
            {* /LISTING_BOX2 *}
        {* /CONTAINER_ARTICLES *}
        
        {elseif $sCampaignContainer.type == "ctText"}
        {* CONTAINER_TEXT *}
            <div class="fixfloat"></div>
            <div class="cat_text">
                <h1>{$sCampaignContainer.description}</h1>
                <p>{$sCampaignContainer.data.html}</p>
            </div>
        {* /CONTAINER_TEXT *}	 
        {/if}
    
    {/foreach}
    </div>
{* /COL_CENTER *}



{* COL_RIGHT1 *}
    <div class="col_right1">
        {include file="campaign/campaign_right.tpl"}
    </div>
{* /COL_RIGHT1 *}
<div class="fixfloat"></div>

