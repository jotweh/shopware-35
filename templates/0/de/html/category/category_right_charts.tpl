{*
	Layered Navigation Shopware 2.1
	If you´re updating from shopware 2.0.4,
	be sure that the following changes were made in viewport s_cat.php
	$variables = array( ...
	"sPropertiesOptionsOnly"=>$categoryArticles['sPropertiesOptionsOnly'],
	"sPropertiesGrouped"=>$categoryArticles['sPropertiesGrouped'],
	... );
*}
{include file="category/category_right_campaigns.tpl" sCategoryCampaigns=$sCampaigns.rightTop}
{include file="category/category_right_filter.tpl"}

{*
	// Layered Navigation Shopware 2.1
*}
{include file="category/category_right_campaigns.tpl" sCategoryCampaigns=$sCampaigns.rightMiddle}
<!--  topseller -->
{foreach from=$sCharts item=sArticle key=key name="counter"}{/foreach}
<div id="topseller" class="box" style="height:{$smarty.foreach.counter.total*50+39+88+5}px;">
	<p class="heading">{* sSnippet: topseller *}{$sConfig.sSnippets.sCategorytopseller}</p>
{foreach from=$sCharts item=sArticle key=key}
{if $key==0}
	<div class="toprule top1 over" style="top:{$key*50+39}px; z-index:{$key+100-$key-$key}; margin-top: 0;">
	<div class="topruleimg" style="top: 10px;">
{else}		

	<div class="toprule top{$key+1} out" style="top:{$key*50+39}px; z-index:{$key+100-$key-$key};">
	<!-- article picture -->
	<div class="topruleimg">
{/if}
	</div>
	<div class="toprulecontent">
	<!-- article picture -->
	<div class="topimg">
	<a href="{$sArticle.linkDetails}" title="{$sArticle.articleName}">
	{if $sArticle.image.src}<img src="{$sArticle.image.src.2}" alt="{$sArticle.articleName}" title="{$sArticle.articleName}" border="0"/>{else}<img src="../../media/img/de/layout/no_picture.jpg" alt="{* sSnippet: no picture *}{$sConfig.sSnippets.sCategorynopicture}" />{/if}
	</a>
	</div>
	<!-- /article picture -->
	<p class="desc"><a href="{$sArticle.linkDetails}" title="{$sArticle.articleName}"><br />{$sArticle.articleName}</a></p>
	
	</div>
	</div>

{/foreach}
	
</div>
<!--  /topseller -->
<script src="../../html/category/category_right_charts.js" type="text/javascript"></script>
{include file="category/category_right_campaigns.tpl" sCategoryCampaigns=$sCampaigns.rightBottom}
