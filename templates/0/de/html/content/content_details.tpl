{if $sContentItem}
    <div class="subheadline">{$sContentItem.dateExploded.0}. {$sContentItem.dateExploded.1}. {$sContentItem.dateExploded.2} - {$sContentItem.description}</div>
    
    <div style="width: 540px; margin:70px 10px 0 40px; font-size: 11px; color: #666;">
    {if $sContentItem.img}
        <div id="article-images" class="clearfix" style="margin: 10px 20px 0 0;">
                <a href="{$sContentItem.imgBig}" rel="lightbox[photos]" title="{* sSnippet: on this picture *}{$sConfig.sSnippets.sContentonthispicture} {$sContentItem.description}" class="main_image">
                <img src="{$sContentItem.img}" alt="{$sContentItem.description}" border="0" title="{$sContentItem.description}" />
                </a>	
        </div>
    {/if}
    
    
    {$sContentItem.text}
            {if $sContentItem.link}
                <h2 class="blue" style="margin-top:20px;">{* sSnippet: more informations *}{$sConfig.sSnippets.sContentmoreinformations}</h2>
                <a href="{$sContentItem.link}">{$sContentItem.link}</a>
            {/if}
            
            {if $sContentItem.attachment}
                <h2 class="blue" style="margin-top:20px;">{* sSnippet: attachment *}{$sConfig.sSnippets.sContentattachment}</h2>
                <a href="{$sContentItem.attachment}" target="_blank">{* sSnippet: download *}{$sConfig.sSnippets.sContentdownload}</a>
            {/if}
    
    {else}
    {* sSnippet: entry not found *}{$sConfig.sSnippets.sContententrynotfound}
{/if}
	</div>
<div class="fixfloat"></div>


<a href="javascript:history.back();" class="btn_def_l button" style="margin:15px 0px 0px 40px;">{* sSnippet: back *}{$sConfig.sSnippets.sContentback}</a><div class="fixfloat"></div>
