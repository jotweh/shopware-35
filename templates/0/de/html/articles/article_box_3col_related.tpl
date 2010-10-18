{* ARTBOX *}
<div class="artbox4 {cycle values="listing_grid_left3,listing_grid_left3,listing_grid_right_"}">

	{* ARTICLE PICTURE *}
		<a href="{$sArticle.linkDetails}" title="{$sArticle.articleName|replace:"&":"&"}" class="artbox_thumb">{if $sArticle.image.src}<img src="{$sArticle.image.src.3}" alt="{$sArticle.articleName|replace:"&":"&"}" title="{$sArticle.articleName|replace:"&":"&"}" border="0"/>{else}<img src="../../media/img/de/layout/no_picture.jpg" alt="{* sSnippet: No picture available *}{$sConfig.sSnippets.sArticlenoPicture}" />{/if}
    	</a>
	{* /ARTICLE PICTURE *}
	
	{* ARTICLE NAME *}
		<h1 style="padding: 0;"><a href="{$sArticle.linkDetails}" title="{$sArticle.articleName|wordwrap:39:"":true}">{$sArticle.articleName|truncate:47}</a></h1>
	{* /ARTICLE NAME *}
	
	{* ARTICLE RATING *}
        {if $sArticle.sVoteAverange.averange!="0.00"}
        <p class="stat"> {* sSnippet: Review *}{$sConfig.sSnippets.sArticleReview} <br />
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
	
	{* ARTICLE PRICE *}
        <p {if $sArticle.pseudoprice} class="article-price2"{else} class="article-price"{/if}>
        {if $sArticle.pseudoprice}<s>{$sConfig.sCURRENCYHTML} {$sArticle.pseudoprice}</s><br />{/if}
        <strong>{if $sArticle.priceStartingFrom}{* sSnippet: from *}{$sConfig.sSnippets.sArticlefrom} {/if}{$sConfig.sCURRENCYHTML} {$sArticle.price}</strong>*
        </p>
	{* /ARTICLE PRICE *}
	
			{if $sArticle.purchaseunit}
	    	{if $sArticle.purchaseunit == $sArticle.referenceunit} {else}
		    <div style="padding-top:5px;padding-bottom:5px;">
		     {if $sArticle.referenceunit}{$sArticle.referenceunit} {$sArticle.sUnit.description} = {$sArticle.referenceprice} {$sConfig.sCURRENCYHTML}{/if}
		     </div>
	     	{/if}
     	{/if}   
	
	{* ARTICLE DESCRIPTION *}
	<div class="article-description">
		<p>{$sArticle.description_long|replace:"<b>":""|replace:"<strong>":""|replace:"</strong>":""|replace:"</b>":""|replace:"<B>":""|replace:"<STRONG>":""|replace:"</STRONG>":""|replace:"</B>":""|replace:"&":"&"|truncate:55}</p>	
	</div>
	{* /ARTICLE DESCRIPTION *}
	<div class="fixfloat"></div>

</div>
{* /ARTBOX *}

{if $key==$smarty.foreach.counter.total-1}
{else}
{if ($key+1) % 3 == 0}<div class="horline3"></div> {/if}
{/if}

