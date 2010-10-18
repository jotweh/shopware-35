{* ARTBOX *}
<div class="artbox_2col {cycle values="grid_left,grid_right2"}">
{if $sArticle.highlight}<div class="ico_tipp"><span class="hidden">{* sSnippet: tip *}{$sConfig.sSnippets.sArticletip}</span></div>{/if}
{if $sArticle.newArticle}<div class="ico_new"><span class="hidden">{* sSnippet: new *}{$sConfig.sSnippets.sArticlenew}</span></div>{/if}
{if $sArticle.esd}<div class="ico_esd"><span class="hidden">{* sSnippet: Available as an immediate download *}{$sConfig.sSnippets.sArticletipavailableasanimmedi}</span></div>{/if}
<div class="left">
	{* ARTICLE PICTURE *}
	<a href="{$sArticle.linkDetails}" title="{$sArticle.articleName|replace:"&":"&"}" class="artbox_thumb">{if $sArticle.image.src}<img src="{$sArticle.image.src.2}" alt="{$sArticle.articleName|replace:"&":"&"}" title="{$sArticle.articleName|replace:"&":"&"}" border="0"/>{else}<img src="../../media/img/de/layout/no_picture.jpg" alt="{* sSnippet: No picture available *}{$sConfig.sSnippets.sArticlenoPicture}" />{/if}</a>
	{* /ARTICLE PICTURE *}

		{* ARTICLE RATING *}
        {if $sArticle.sVoteAverange.averange!="0.00"}
        <p class="stat">
            {if $sArticle.sVoteAverange.averange < 0.5}
            <img src="../../media/img/default/stars/star_0.gif" alt="{* sSnippet: zero Points *}{$sConfig.sSnippets.sArticlezeropoints}" />
            {elseif $sArticle.sVoteAverange.averange >= 0.5 && $sArticle.sVoteAverange.averange < 1}
            <img src="../../media/img/default/stars/star_01.gif" alt="{* sSnippet: one Point *}{$sConfig.sSnippets.sArticleonepoint}" />
            {elseif $sArticle.sVoteAverange.averange >= 1.0 && $sArticle.sVoteAverange.averange < 1.5}
            <img src="../../media/img/default/stars/star_02.gif" alt="{* sSnippet: two Points *}{$sConfig.sSnippets.sArticletwopoints}" />
            {elseif $sArticle.sVoteAverange.averange >= 1.5 && $sArticle.sVoteAverange.averange < 2}
            <img src="../../media/img/default/stars/star_03.gif" alt="{* sSnippet: three Points *}{$sConfig.sSnippets.sArticlethreepoints}" />
            {elseif $sArticle.sVoteAverange.averange >= 2.0 && $sArticle.sVoteAverange.averange < 2.5}
            <img src="../../media/img/default/stars/star_04.gif" alt="{* sSnippet: four Points *}{$sConfig.sSnippets.sArticlefourpoints}" />
            {elseif $sArticle.sVoteAverange.averange >= 2.5 && $sArticle.sVoteAverange.averange < 3}
            <img src="../../media/img/default/stars/star_05.gif" alt="{* sSnippet: five Points *}{$sConfig.sSnippets.sArticlefivepoints}" />
            {elseif $sArticle.sVoteAverange.averange >= 3.0 && $sArticle.sVoteAverange.averange < 3.5}
            <img src="../../media/img/default/stars/star_06.gif" alt="{* sSnippet: six Points *}{$sConfig.sSnippets.sArticlesixpoints}" />
            {elseif $sArticle.sVoteAverange.averange >= 3.5 && $sArticle.sVoteAverange.averange < 4}
            <img src="../../media/img/default/stars/star_07.gif" alt="{* sSnippet: seven Points *}{$sConfig.sSnippets.sArticlesevenpoints}" />
            {elseif $sArticle.sVoteAverange.averange >= 4.0 && $sArticle.sVoteAverange.averange < 4.5}
            <img src="../../media/img/default/stars/star_08.gif" alt="{* sSnippet: eight Points *}{$sConfig.sSnippets.sArticleeightpoints}" />
            {elseif $sArticle.sVoteAverange.averange >= 4.5 && $sArticle.sVoteAverange.averange < 5}
            <img src="../../media/img/default/stars/star_09.gif" alt="{* sSnippet: nine Points *}{$sConfig.sSnippets.sArticleninepoints}" />
            {elseif $sArticle.sVoteAverange.averange >= 5.0}
            <img src="../../media/img/default/stars/star_10.gif" alt="{* sSnippet: ten Points *}{$sConfig.sSnippets.sArticletenpoints}" />
            {/if}
        </p>	
        {/if}
	{* /ARTICLE RATING *}
	
{* ARTICLE COMPARE / MORE *}
	{if !$sHideCompare}<a href="#" onclick="addCompare('{$sArticle.articleID}')" class="compare_artbox2">{$sConfig.sSnippets.sArticleButtonCompare}</a>{/if}
	<a href="{$sArticle.linkDetails}" title="{$sArticle.articleName|wordwrap:29:"":true}" class="more_artbox2">{$sConfig.sSnippets.sArticleButtonMore}</a>
</div>
{* /ARTICLE COMPARE / MORE *}

<div class="right">
	{* ARTICLE NAME *}
		<a href="{$sArticle.linkDetails}" title="{$sArticle.articleName|wordwrap:39:"":true}" class="headline">{$sArticle.articleName|truncate:47}</a>
	{* /ARTICLE NAME *}

	{* ARTICLE DESCRIPTION *}
	<div class="article-description">
		<p>{$sArticle.description_long|replace:"<b>":""|replace:"<strong>":""|replace:"</strong>":""|replace:"</b>":""|replace:"<B>":""|replace:"<STRONG>":""|replace:"</STRONG>":""|replace:"</B>":""|replace:"&":"&"|truncate:80}</p>	
		
		{if $sArticle.purchaseunit}
	    	{if $sArticle.purchaseunit == $sArticle.referenceunit} {else}
		    <div style="padding-top:20px;">
		     {if $sArticle.referenceunit}{$sArticle.referenceunit} {$sArticle.sUnit.description} = {$sArticle.referenceprice} {$sConfig.sCURRENCYHTML}{/if}
		     </div>
	     	{/if}
     	{/if}
	</div>
	{* /ARTICLE DESCRIPTION *}

	{* ARTICLE PRICE *}
        <p {if $sArticle.pseudoprice} class="article-price2"{else} class="article-price"{/if}>
        {if $sArticle.pseudoprice}<s>{$sConfig.sCURRENCYHTML} {$sArticle.pseudoprice}</s>&nbsp;{/if}
        <strong>{if $sArticle.priceStartingFrom && !$sArticle.liveshoppingData}{* sSnippet: from *}{$sConfig.sSnippets.sArticlefrom} {/if}{$sConfig.sCURRENCYHTML} {$sArticle.price}</strong>*
        </p>
	{* /ARTICLE PRICE *}

</div>
</div>
{* /ARTBOX *}