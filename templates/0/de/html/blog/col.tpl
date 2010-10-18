{* ARTBOX *}
<div class="blog_col">
	<div class="head">
		<h2><a href="{$sArticle.linkDetails}" title="{$sArticle.articleName}">{$sArticle.articleName}</a></h2>
		<p class="post_metadata">
		<span class="first">{* sSnippet: by *}{$sConfig.sSnippets.sArticleby} {if !$homepage}<a href="{$sArticle.linkSupplier}" title="{$sArticle.supplierName}">{/if}{$sArticle.supplierName}{if !$homepage}</a>{/if}</span>
		<span>{$sArticle.datumFormated|german}</span>
		<span><a href="{$sArticle.categoryInfo.linkCategory}" title="{$sArticle.categoryInfo.description}">{$sArticle.categoryInfo.description}</a></span>
		<span><a href="{$sArticle.linkDetails}" title="{$sArticle.articleName}">{if $sArticle.sVoteAverange.count}{$sArticle.sVoteAverange.count}{else}0{/if} {* sSnippet: by *}{$sConfig.sSnippets.sBlogComments}</a></span>
		{if $sArticle.sVoteAverange.averange!="0.00"}
        <span class="last">
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
        </span>	
        {/if}
		</p>
	
	</div>
		<div class="content">

		{if $sArticle.image.src.4}
			<div class="blogimage">
			
			<div id="imgTarget">
			    {if !$homepage}
				<a href="{$sArticle.image.src.original}" rel="lightbox[photos]" title="{$sArticle.articleName}" class="main_image">
				{else}
				<a href="{$sArticle.linkDetails}" title="{$sArticle.articleName}" class="main_image">
				{/if}
			    <img src="{$sArticle.image.src.3}" alt="{$sArticle.articleName}" border="0" title="{if $sArticle.image.res.description}{$sArticle.image.res.description}{else}{$sArticle.articleName}{/if}" />
			    </a>
		    </div>
		    <div class="fixfloat"></div>
		    </div>
		{/if}	

		{if $sArticle.description}{$sArticle.description|nl2br}{else}{$sArticle.description_long|nl2br}{/if}


	<div class="fixfloat"></div>

	<p><a href="{$sArticle.linkDetails}" title="{$sArticle.articleName}" class="more_info">{* sSnippet: by *}{$sConfig.sSnippets.sBlogReadMore}</a></p>

	</div>
	<div class="fixfloat"></div>
</div>
{* /ARTBOX *}

