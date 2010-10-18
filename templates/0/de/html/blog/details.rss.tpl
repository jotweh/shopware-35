<?xml version="1.0" encoding="ISO-8859-1" ?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
<channel>
<atom:link href="{$sCategory.rssFeed|escape}" rel="self" type="application/rss+xml" />
<title>{$sCategory.description} / RSS Feed</title>
<link>{$sCategory.sSelf}</link>
<description>{$sConfig.sSHOPNAME} - {$sCategory.description}</description>
<language>de-de</language>
<lastBuildDate>{"r"|date}</lastBuildDate>
{foreach from=$sArticles item=sArticle key=key name="counter"}
<item> 
	<title>{$sArticle.articleName|strip_tags|replace:"\"":""|strip|truncate:80:"...":true|escape}</title>
	<guid>{$sArticle.linkDetails|escape}</guid>
	<link>{$sArticle.linkDetails|escape}</link>
	<description>{$sArticle.description_long|strip_tags|strip|truncate:280:"...":true|escape}</description>
{if $sArticle.changetime} 	{assign var="sArticleChanged" value=$sArticle.changetime|strtotime}<pubDate>{$sArticleChanged|date_format:"%a, %d %b %Y %H:%M:%S +100"}</pubDate>{/if}

</item>
{/foreach}

</channel>
</rss>