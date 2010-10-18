{* PARAM $sBundles *}
{if $sBundles}
	<div id='bundle_box'{if $sArticle.sVariants} style="display:none;"{/if}>
	<h2>{* sSnippet: start compare *}{$sConfig.sSnippets.sArticlesBundleSaveMoney}:</h2>

	{foreach from=$sBundles item=bundle}
		{if $bundle.sBundleArticles}

		<form name="sAddToBasket" method="GET" action="{$sStart}" class="clearfix" style="margin-bottom: 5px;">
		<div id='bundleset_{$bundle.id}' class="box_bundleset">
		<div class="box_bundleArticle">
			{* SELECTED ARTICLE *}
				{if $sConfig.sSHOWBUNDLEMAINARTICLE}
	
					<div id='bundleImg_{$bundle.id}' class="box_bundleImg">
						{if $sArticle.image.src.1}
							<a href='#' title="{$sArticle.articleName}" class="bundleImg" style="background-image: url({$sArticle.image.src.1});"></a>
						{else}
							<a href='#' title="{$sArticle.articleName}" class="bundleImg" style="background-image: url(../../media/img/de/layout/no_picture.jpg);"></a>
						{/if}
					</div>
					<p class="BundlePlus">+</p>
				{/if}
			{* /SELECTED ARTICLE *}
	
			{assign var="first" value=0}
				{foreach from=$bundle.sBundleArticles item=bundleArticle}
					{if $first != 0}
					<p class="BundlePlus">+</p>
					{/if}
	
						{if $bundleArticle.sDetails.image.src[1]}
						<div class="box_bundleImg">
							<a href="{$bundleArticle.sDetails.linkDetails}" title="{$bundleArticle.sDetails.articleName}" class="bundleImg" style="background-image: url({$bundleArticle.sDetails.image.src[1]});"></a>
						</div>
						{else}
						<div class="box_bundleImg">
							<a href='{$bundleArticle.sDetails.linkDetails}' title="{$bundleArticle.sDetails.articleName}" class="bundleImg" style="background-image: url(../../media/img/de/layout/no_picture.jpg);"></a>
						</div>
						{/if}
	
					{assign var="first" value=1}
				{/foreach}
		</div>
		<div class="box_bundlePrice">
			<div class="bundleBasketbutton">
				<input type='hidden' name='sAddBundle' value='' />
				<input type='hidden' name='sBID' value='{$bundle.id}' />
				<input type='hidden' name='sViewport' value='basket' />
				
				<input class="bundleBasketButton" type="submit"  title="{$sArticle.articleName} {* sSnippet: add article to basket *}{$sConfig.sSnippets.sArticleinthebasket}" name="{* sSnippet: add to basked *}{$sConfig.sSnippets.sArticleaddtobasked}" value="{$sConfig.sSnippets.sArticleaddtobasked}" style="visibility: visible; opacity: 1;"/>
			</div>
			<p>{* sSnippet: Prices for all *}{$sConfig.sSnippets.sArticlesBundlePricesForAll}:</p>
			<p class="bundlePrice"><span>{$sConfig.sCURRENCYHTML} </span><span id='price_bundle_{$bundle.id}'></span></p>
			<p class="bundleDiscount">({* sSnippet: Bundle instead *}{$sConfig.sSnippets.sArticlesBundleInstead} {$sConfig.sCURRENCYHTML} <span id='price_rabAbs_{$bundle.id}'></span> - <span id='price_rabPro_{$bundle.id}'></span>{* sSnippet: Prices for all *}{$sConfig.sSnippets.sBundleDiscountPostfix})</p>
		</div>
		<div class="fixfloat" style="height: 0em;"></div>
		</div>
		
		<div class="box_bundleArticleNames">
			<span>{$sArticle.articleName}</span>
			{foreach from=$bundle.sBundleArticles item=bundleArticle}
				{if $bundleArticle.sDetails.sConfiguratorGroups}
					{foreach from=$bundleArticle.sDetails.sConfiguratorGroups item=groupInfo}
						{if $groupInfo.groupname && $groupInfo.optionname}
							{if !$confgroups}
								{assign var="confgroups" value="`$groupInfo.groupname`: `$groupInfo.optionname`"}
							{else}
								{assign var="confgroups" value="`$confgroups` - `$groupInfo.groupname`: `$groupInfo.optionname`"}
							{/if}
						{/if}
					{/foreach}
					{if $confgroups}
					{assign var="confgroups" value="(`$confgroups`)"}
					{/if}
				{/if}
				<span><br>+ <a href="{$bundleArticle.sDetails.linkDetails}" title="{$bundleArticle.sDetails.articleName} {$confgroups}">{$bundleArticle.sDetails.articleName} {$confgroups}</a></span>
			{/foreach}
		</div>
		<div class="fixfloat" style="height: 0em;"></div>
		
		</form>

		{/if}
	{/foreach}

	</div>
	<div class="fixfloat"></div>
{/if}
