{if $sContentItem}
    <div class="subheadline">{if $sContentItem.date}{$sContentItem.date|date_format:"%d.%m.%Y"} - {/if}{$sContentItem.description}</div>
    <div style="width: 725px; margin:70px 10px 0 40px; font-size: 11px; color: #666;">
    <iframe src="{$sContentItem.link}" frameborder="0" style="width:725px;height:500px;border:1px solid #CDCDCD;"></iframe>
	</div>
{else}
    {* sSnippet: entry not found *}{$sConfig.sSnippets.sContententrynotfound}
{/if}
	
<div class="fixfloat"></div>

<a href="{$sBackLink}" class="btn_def_l button" style="margin:15px 0px 0px 40px;">{* sSnippet: back *}{$sConfig.sSnippets.sContentback}</a>
<a href="{$sContentItem.link}" class="btn_def_r button" target="_blank" style="width:250px; margin:15px 50px 0px 0px;">{* sSnippet: Open Newsletter in new window *}{$sConfig.sSnippets.sNewsletterNewWindow}</a><div class="fixfloat"></div>
