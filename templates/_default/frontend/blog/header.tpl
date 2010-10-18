{extends file='frontend/index/header.tpl'}

{* Canonical link *}
{block name='frontend_index_header_canonical'}
<link rel="canonical" href="{$sCategoryContent.sSelf}" title="{if $sCategoryContent.description}{$sCategoryContent.description}{else}{$sShopname}{/if}" />
{/block}

{* RSS and Atom feeds *}
{block name="frontend_index_header_feeds"}
<link rel="alternate" type="application/rss+xml" title="{$sCategoryContent.description} RSS" href="{$sCategoryContent.rssFeed}" />
<link rel="alternate" type="application/atom+xml" title="{$sCategoryContent.description} ATOM" href="{$sCategoryContent.atomFeed}" />
{/block}