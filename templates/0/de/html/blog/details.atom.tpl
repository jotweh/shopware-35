<?xml version="1.0" encoding="ISO-8859-1" ?>
<feed xmlns="http://www.w3.org/2005/Atom">
<author>
    <name>{$sConfig.sSHOPNAME}</name>
</author>
<title>{$sCategory.description}</title>
<id>{$sCategory.sSelf|escape}</id>
<updated>{$smarty.now|date_format:"%Y-%m-%dT%H:%M:%SZ"}</updated>
{foreach from=$sArticles item=sArticle key=key name="counter"}
<entry> 
	<title>{$sArticle.articleName|strip_tags|replace:"\"":""|strip|truncate:80:"...":true|escape}</title>
	<id>{$sArticle.linkDetails|escape}</id>
	<link href="{$sArticle.linkDetails|escape}"/>
	<summary type="html">{if $sArticle.description}{$sArticle.description|strip_tags|strip|truncate:280:"...":true|escape}{else}{$sArticle.description_long|strip_tags|strip|truncate:280:"...":true|escape}{/if}</summary>
	<content type="html"><![CDATA[{$sArticle.description_long}]]></content>
{if $sArticle.changetime} 	{assign var="sArticleChanged" value=$sArticle.changetime|strtotime}<updated>{$sArticleChanged|date_format:"%a, %d %b %Y %H:%M:%S +100"}</updated>{/if}

</entry>
{/foreach}
</feed>