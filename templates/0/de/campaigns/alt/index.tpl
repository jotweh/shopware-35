{foreach from=$sCampaign.containers item=sCampaignContainer}
{if $sCampaignContainer.type == "ctText"}
{eval var=$sCampaignContainer.description|strip_tags|trim|strip}
#################################################################
{eval var=$sCampaignContainer.data.html|replace:"</p>":"\n"|strip|strip_tags|strip|trim}
{/if}
{if $sCampaignContainer.type == "ctLinks"}
{eval var=$sCampaignContainer.description|strip}
{foreach from=$sCampaignContainer.data item=sLink}
{eval var=$sLink.description|strip_tags|strip}
#################################################################
** {$sLink.link}
{/foreach}
{/if}
{if $sCampaignContainer.type == "ctSuggest"}
@suggestions
{/if}
{if $sCampaignContainer.type == "ctArticles"}
{eval var=$sCampaignContainer.description|strip_tags}
#################################################################
{foreach from=$sCampaignContainer.data item=sArticle name=artikelListe}
{$sArticle.articleName|truncate:40:"[..]"|strip_tags}

{$sArticle.description_long|truncate:50:"..."|strip_tags|trim}

{if $sArticle.pseudoprice}

statt {$sConfig.sCURRENCY} {$sArticle.pseudoprice}
{/if}

{$sConfig.sCURRENCY} {$sArticle.price}

<a target="_blank" href="{$sArticle.linkDetails}" title="{$sArticle.articleName}"></a>

#################################################################
{/foreach}

{/if}
{/foreach}


{$sConfig.sSnippets.sCampaignsPlain}
{if $sUserGroup.tax}{$sConfig.sSnippets.sIndexpricesinclvat}{else}{$sConfig.sSnippets.sIndexallpricesexcludevat}{/if}