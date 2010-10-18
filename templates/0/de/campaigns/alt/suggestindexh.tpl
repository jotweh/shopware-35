

{$sRecommendations.description|strip_tags|strip}
#################################################################
{foreach from=$sRecommendations.data item=sArticle name=artikelListe}
{$sArticle.articleName|truncate:40:"[..]"|strip_tags}

{$sArticle.description_long|truncate:50:"..."|strip_tags|trim}

{if $sArticle.pseudoprice}

statt {$sConfig.sCURRENCY} {$sArticle.pseudoprice}
{/if}

{$sConfig.sCURRENCY} {$sArticle.price}

<a target="_blank" href="{$sArticle.linkDetails}" title="{$sArticle.articleName}"></a>

#################################################################
{/foreach}