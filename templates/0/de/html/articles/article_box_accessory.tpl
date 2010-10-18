<div class="box_2col" style="width:250px; padding: 10px; background-color: #fff; border: 1px solid #DD4800;">
	
    {if $sArticle.shippingfree}<div class="shippingfree"><span class="hidden">{* sSnippet: FREE SHIPPING *}{$sConfig.sSnippets.sArticlefreeshipping}</span></div>{/if}
	{if $sArticle.topseller}<span class="marker">{* sSnippet: TOP *}{$sConfig.sSnippets.sArticletop}</span>{/if}
	{if $sArticle.highlight}<div class="ico_tipp"><span class="hidden">{* sSnippet: tip *}{$sConfig.sSnippets.sArticletip}</span></div>{/if}
	{if $sArticle.esd}<div class="ico_esd"><span class="hidden">{* sSnippet: Available as an immediate download *}{$sConfig.sSnippets.sArticletipavailableasanimmedi}</span></div>{/if}
	
    <div class="box_2col_top">


    {* ARTICLE NAME *}
	<h1>
		<a href="{$sArticle.linkDetails}" title="Mehr Informationen zu {$sArticle.articleName|wordwrap:39:"":true}">{$sArticle.articleName|wordwrap:39:"":true}</a>
	</h1>
	{* /ARTICLE NAME *}
	
	{* ARTICLE PICTURE *}
        <a href="{$sArticle.linkDetails}" title="{* sSnippet: More information *}{$sConfig.sSnippets.sArticleMoreinformation} {$sArticle.articleName|replace:"&":"&"}" class="thumb_image" style="margin-top: 5px; border:none;">
        {if $sArticle.image.src}<img src="{$sArticle.image.src.2}" alt="{$sArticle.articleName|replace:"&":"&"}" title="{$sArticle.articleName|replace:"&":"&"}" border="0"/>
        {else}<img src="../../media/img/de/layout/no_picture.jpg" alt="{* sSnippet: No picture available *}{$sConfig.sSnippets.sArticlenoPicture}" />{/if}
        </a>
	{* /ARTICLE PICTURE *}
	
	
		{if $sArticle.sVoteAverange.averange!="0.00"}
		<p class="stat" style="float: left; width: 100px;"> 
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
	
	{* ARTICLE DESCRIPTION *}
	<div class="article-description clearfix">
		<p>{$sArticle.description_long|replace:"<b>":""|replace:"<strong>":""|replace:"</strong>":""|replace:"</b>":""|replace:"<B>":""|replace:"<STRONG>":""|replace:"</STRONG>":""|replace:"</B>":""|replace:"&":"&"|truncate:180|wordwrap:19:"\n":true}</p>	
	</div>
	{* ARTICLE DESCRIPTION *}
	<div class="fixfloat"></div>
	</div>
		
	{* ARTICLE PRICE *}
	<p {if $sArticle.pseudoprice} class="article-price2"{else} class="article-price"{/if}>
		{if $sArticle.pseudoprice}<s>{$sConfig.sCURRENCYHTML} {$sArticle.pseudoprice}</s><br />{/if}
			<strong style="font-size:14px;color:#000;">{if $sArticle.priceStartingFrom}ab {/if}{$sConfig.sCURRENCYHTML} {$sArticle.price}*</strong>
	</p>
	{* /ARTICLE PRICE *}
		
		<div class="fixfloat"></div>
		
		<div class="article_items">
            {if $sArticle.sReleaseDate}
                <p class="deliverable3">{* sSnippet: Available from *}{$sConfig.sSnippets.sArticleAvailablefrom} {$sArticle.sReleaseDate}</p>
            {else}
                {if $sArticle.instock>0 OR $sArticle.esd}
                    <p class="deliverable1" style="float: left; width:120px;">{* sSnippet: Immediately available *}{$sConfig.sSnippets.sArticletopImmediatelyavailabl}</p>
                {elseif $sArticle.shippingtime}
                    <p class="deliverable2" style="float: left; width:120px;">{* sSnippet: Delivery time *}{$sConfig.sSnippets.sArticledeliverytime} {$sArticle.shippingtime} {* sSnippet: Days *}{$sConfig.sSnippets.sArticledays}</p>
                {else}
                    <p class="deliverable3" style="float: left; width:120px;">{$sConfig.sNOTAVAILABLE}</p>
                {/if}
            {/if}
			<div class="fixfloat"></div>
		</div>
</div>
{if $key % 2 != 0}<div class="fixfloat"></div>{/if}