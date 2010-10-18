<div class="blog_detail">
	<div class="left">
		<div class="scroll_pages top">

			<div class="alignment">{if $sArticle.sNavigation.sPrevious.link}<a href="{$sArticle.sNavigation.sPrevious.link}" title="{$sArticle.sNavigation.sPrevious.name}" class="last">{* sSnippet: more information about *}{$sConfig.sSnippets.sBlogOlderArticles}</a>{/if}</div>
			<div class="alignment"><a href="{$sArticle.sNavigation.sCurrent.sCategoryLink}" title="{$sArticle.sNavigation.sCurrent.sCategoryName}" class="overview">{* sSnippet: overview *}{$sConfig.sSnippets.sBlogtoOverview}</a></div>
			<div class="alignment">{if $sArticle.sNavigation.sNext.link}<a href="{$sArticle.sNavigation.sNext.link}" title="{$sArticle.sNavigation.sNext.name}" class="next">{* sSnippet: overview *}{$sConfig.sSnippets.sBlogNewerArticles}</a>{/if}</div>

		<div class="fixfloat"></div>
		</div>
		<div class="head">
			<h1>{$sArticle.articleName}</h1>
			<p class="post_metadata">
			<span class="first">{* sSnippet: overview *}{$sConfig.sSnippets.sArticleby} {$sArticle.supplierName}</span>
			<span>{$sArticle.datumFormated|german}</span>
			<span>{* sSnippet: category assignment *}{$sConfig.sSnippets.sBlogCategoryAssignment}</span>
			<span class="last"><a href="#commentcontainer" title="{* sSnippet: category assignment *}{$sConfig.sSnippets.sBlogToComments}">{if $sArticle.sVoteAverange.count}{$sArticle.sVoteAverange.count}{else}0{/if} {* sSnippet: category assignment *}{$sConfig.sSnippets.sBlogComments}</a></span>
			</p>
		</div>
		<div class="content">
		{if $sArticle.image.src.4}
		<div class="blogimage">
			{if $sArticle.image.res.relations}
			{* Saving image resource, for support variant depending images *}
			<div id="img{$sArticle.image.res.relations}" style="display:none">
			    <a href="{$sArticle.image.src.original}" rel="lightbox[photos]" title="{if $sArticle.image.res.description}{$sArticle.image.res.description}{else}{$sArticle.articleName}{/if}" class="main_image">
			    <img src="{$sArticle.image.src.4}" alt="{$sArticle.articleName}" border="0" title="{if $sArticle.image.res.description}{$sArticle.image.res.description}{else}{$sArticle.articleName}{/if}" /> </a>
	   		</div>
			{/if}
			<div id="imgTarget">
			    <a href="{$sArticle.image.src.original}" rel="lightbox[photos]" title="{if $sArticle.image.res.description}{$sArticle.image.res.description}{else}{$sArticle.articleName}{/if}" class="main_image">
			    <img src="{$sArticle.image.src.4}" alt="{$sArticle.articleName}" border="0" title="{if $sArticle.image.res.description}{$sArticle.image.res.description}{else}{$sArticle.articleName}{/if}" />
			    </a>
		    </div>
			{if $sArticle.images}
		    <div class="thumbnail_box">
				{foreach from=$sArticle.images item=sArticleImage}
					{if $sArticleImage.relations}
			
				    {* Saving image resource, for support variant depending images *} 
				    <div id="img{$sArticleImage.relations}" style="display:none"><a href="{$sArticleImage.src.5}" rel="lightbox[photos]" title="{if $sArticleImage.description}{$sArticleImage.description}{else}{$sArticle.articleName}{/if}"><img src="{$sArticleImage.src.4}" title="{if $sArticleImage.description}{$sArticleImage.description}{else}{$sArticle.articleName}{/if}" /></a></div> 
				    {else}
				     <a href="{$sArticleImage.src.5}" rel="lightbox[photos]" title="{if $sArticleImage.description}{$sArticleImage.description}{else}{$sArticle.articleName}{/if}" style="background: #fff url({$sArticleImage.src.1}) no-repeat center center;">
				     </a> 
				    {/if}
				{/foreach}
		    <div class="fixfloat"></div>
		    </div>
		    {/if}
		</div>
		{/if}	

		{$sArticle.description_long|nl2br}
		<div class="fixfloat"></div>

		</div>
		
		<div class="foot">
			{if $sArticle.sLinks|@count>1}
			<div class="links">
			<p class="label">{* sSnippet: more information about *}{$sConfig.sSnippets.sArticletipmoreinformation}</p>
				{foreach from=$sArticle.sLinks item=information}
					{if $information.supplierSearch}
					{else}
						<a href="{$information.link}" title="{$information.description}" target="{$information.target}" rel="nofollow" class="ico link">{$information.description}</a><br />
					{/if}
				{/foreach}
			</div>
			{/if}        
	        
	        {if $sArticle.sDownloads}
			<div class="downloads">
			<p class="label">{* sSnippet: available downloads *}{$sConfig.sSnippets.sArticleavailabledownloads}</p>
				{foreach from=$sArticle.sDownloads item=download}
					<a href="{$download.filename}" title="{$download.description}" target="_blank" class="ico link">{* sSnippet: download *}{$sConfig.sSnippets.sArticledownload} {$download.description}</a><br />
				{/foreach}
			</div>
			{/if}
	
			{if $sArticle.attr3}
			<div id="unser_kommentar">
				<p class="label">{* sSnippet: our comment on *}{$sConfig.sSnippets.sArticleourcommenton} "{$sArticle.articleName}"</p>
					{$sArticle.attr3}
			</div>	
			{/if}		

			<div class="rating">
			<span>{* sSnippet: comment voting *}{$sConfig.sSnippets.sCompareheadlinevoting}:</span>
	        <span>
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
	        <span>( {* sSnippet: count *}{$sConfig.sSnippets.sAccountNumber}: {$sArticle.sVoteAverange.count}, {* sSnippet: count *}{$sConfig.sSnippets.sArticletopaveragecustomerrevi} {$sArticle.sVoteAverange.averange|round:1} {* sSnippet: count *}{$sConfig.sSnippets.sArticleof} 5 )</span>        

			</div>
			{if $sArticle.sProperties}
			<div class="tags">
				<span>{* sSnippet: tags *}{$sConfig.sSnippets.sBlogTags}</span>
				{foreach from=$sArticle.sProperties item=sProperty}
					<a href="{$sProperty.id}" title="{$sProperty.value}">{$sProperty.value}</a> |
				{/foreach}
			</div>
			{/if}
			{if !$sArticle.sBookmarks}
			<div class="bookmarks">
				<a href="http://twitter.com/home?status={$sArticle.articleName}+-+{$sArticle.linkDetailsRewrited}" title="Twittere diesen Artikel!" class="bookmark twitter" rel="nofollow" target="_blank">Twittere diesen Artikel!</a>
				<a href="http://www.facebook.com/share.php?v=4&amp;src=bm&amp;u={$sArticle.linkDetailsRewrited}&amp;t={$sArticle.articleName}" title="Empfehle diesen Artikel bei Facebook" class="bookmark facebook" rel="nofollow" target="_blank">Empfehle diesen Artikel bei Facebook</a>
				<a href="http://del.icio.us/post?url={$sArticle.linkDetailsRewrited}&amp;title={$sArticle.articleName}" title="Empfehle diesen Artikel bei del.icio.us" class="bookmark delicious" rel="nofollow" target="_blank">Empfehle diesen Artikel bei del.icio.us</a>
				<a href="http://digg.com/submit?phase=2&amp;url={$sArticle.linkDetailsRewrited}&amp;title={$sArticle.articleName}" title="Digg this!" class="bookmark digg" rel="nofollow" target="_blank">Digg this!</a> 
			<div class="fixfloat"></div>
			</div>
			{/if}
			<div id="commentcontainer">
				<p class="headline">{$sArticle.sVoteAverange.count} {* sSnippet: comments *}{$sConfig.sSnippets.sBlogComments}</p>
				<ul>
				{foreach from=$sArticle.sVoteComments item=vote}
					<li class="{cycle values='white,grey'}">
						<span class="author">{$vote.name}</span> <span class="date">{$vote.date}</span>
						<p class="hline">{$vote.headline}</p>
						<p class="comment">{$vote.comment}</p>
					</li>
				{/foreach}
				</ul>
				<h3>Kommentar schreiben</h3>
				<p class="desc">{* sSnippet: the fields marked with * are mandatory. *}{$sConfig.sSnippets.sArticlethefieldsmarked}</p>

				{if $_POST.sAction == "saveComment"}
					<div id="rezension">
						{if $sErrorFlag}
							<div class="error">{* sSnippet: please fill out all fields marked in red *}{$sConfig.sSnippets.sArticlefilloutallredfields}</div>
						{else}
						{if $sConfig.sOPTINVOTE && !$_GET.sConfirmation}
							<div class="allright2" style="margin:10px 0; width:374px;">{* sSnippet: the commit save was successful *}{$sConfig.sSnippets.sArticleCommitSavedOptIn}</div>
						{else}
							<div class="allright2" style="margin:10px 0; width:374px;">{* sSnippet: the commit save was successful *}{$sConfig.sSnippets.sArticleCommitSaved}</div>
						{/if}
						{/if}
					</div>
				{/if}			

				<form name="frmComment" method="POST" action="#bewertungen" id="schnellregistrierung" class="kommentare">
				    <input name="sAction" type="hidden" value="saveComment" />
				    <input name="sViewport" type="hidden" value="{$_GET.sViewport}" />
				    <input name="sArticle" type="hidden" value="{$_GET.sArticle}" />
					<p class="col">
						<label for="sVoteName">{* sSnippet: your name *}{$sConfig.sSnippets.sArticleyourname}*:</label><br />
						<input name="sVoteName" type="text" id="sVoteName" value="{$_POST.sVoteName}" class="normal {if $sErrorFlag.sVoteName}instyle_error{/if}" />
					</p>
					<p class="col">
						<label for="sVoteMail">{* sSnippet: your email *}{$sConfig.sSnippets.sArticleyourmail}*:</label><br />
						<input name="sVoteMail" type="text" id="sVoteMail" value="{$_POST.sVoteMail}" class="normal {if $sErrorFlag.sVoteMail}instyle_error{/if}" />
					</p>
					<p class="col">
						<label for="sVoteStars">{* sSnippet: review *}{$sConfig.sSnippets.sArticlereview1}*:</label><br />
						<select name="sVoteStars" class="normal" id="sVoteStars">
							<option value="10">{* sSnippet: 10 (very well) *}{$sConfig.sSnippets.sArticle10}</option>
							<option value="9">{* sSnippet: 9 *}{$sConfig.sSnippets.sArticle9}</option>
							<option value="8">{* sSnippet: 8 *}{$sConfig.sSnippets.sArticle8}</option>
							<option value="7">{* sSnippet: 7 *}{$sConfig.sSnippets.sArticle7}</option>
							<option value="6">{* sSnippet: 6 *}{$sConfig.sSnippets.sArticle6}</option>
							<option value="5">{* sSnippet: 5 *}{$sConfig.sSnippets.sArticle5}</option>
							<option value="4">{* sSnippet: 4 *}{$sConfig.sSnippets.sArticle4}</option>
							<option value="3">{* sSnippet: 3 *}{$sConfig.sSnippets.sArticle3}</option>
							<option value="2">{* sSnippet: 2 *}{$sConfig.sSnippets.sArticle2}</option>
							<option value="1">{* sSnippet: 1 (very bad) *}{$sConfig.sSnippets.sArticle1}</option>
						</select>
					</p>
					<p class="col">
						<label for="sVoteSummary">{* sSnippet: summary *}{$sConfig.sSnippets.sArticlesummary}*:</label><br />
						<input name="sVoteSummary" type="text" value="{$_POST.sVoteSummary}" id="sVoteSummary" class="normal {if $sErrorFlag.sVoteSummary}instyle_error{/if}" />
					</p>
					<p>
						<label for="sVoteComment">{* sSnippet: your opinion *}{$sConfig.sSnippets.sArticleyouropinion}*:</label><br />
						<textarea name="sVoteComment" id="sVoteComment" class="normal {if $sErrorFlag.sVoteComment}instyle_error{/if}">{$_POST.sVoteComment|escape}</textarea>
					</p>
					<div class="captcha" style="padding:15px; float:left;">
						<img src="{$sStart}?sCaptcha=1&sCoreId={$sCoreId}"/>
						<div class="code" style="margin-left:15px;">
							<label style="height: 55px; width: 150px;">{* sSnippet: please enter the numbers in the following text box *}{$sConfig.sSnippets.sArticleenterthenumbers}</label>
							<input type="text" name="sCaptcha" style="width:154px;" class="{if $sErrorFlag.sCaptcha}instyle_error{else}instyle{/if}">
							<p style="display:none;"><input type="text" name="sCaptchaTest" /></p>
						</div>
					</div>
					
					<input class="button" type="submit" name="Submit" value="{* sSnippet: save *}{$sConfig.sSnippets.sArticletosave}" style="float: right; margin-right:10px;"/>
				</form>
				<div class="fixfloat"></div>
			</div>
		</div>
		<div class="scroll_pages bottom">

			<div class="alignment">{if $sArticle.sNavigation.sPrevious.link}<a href="{$sArticle.sNavigation.sPrevious.link}" title="{$sArticle.sNavigation.sPrevious.name}" class="last">{* sSnippet: older articles *}{$sConfig.sSnippets.sBlogOlderArticles}</a>{/if}</div>
			<div class="alignment"><a href="{$sArticle.sNavigation.sCurrent.sCategoryLink}" title="{$sArticle.sNavigation.sCurrent.sCategoryName}" class="overview">{* sSnippet: overview *}{$sConfig.sSnippets.sBlogtoOverview}</a></div>
			<div class="alignment">{if $sArticle.sNavigation.sNext.link}<a href="{$sArticle.sNavigation.sNext.link}" title="{$sArticle.sNavigation.sNext.name}" class="next">{* sSnippet: overview *}{$sConfig.sSnippets.sBlogNewerArticles}</a>{/if}</div>

		<div class="fixfloat"></div>
		</div>
	</div>
	<div class="right">
        {if $sArticle.sSimilarArticles}
            {* ARTICLE_SIMILAR *}
            <div id="aehnlich" class="box">
                <h2>{* sSnippet: overview *}{$sConfig.sSnippets.sIndexsuitablearticles}</h2>
                {foreach name=line from=$sArticle.sSimilarArticles item=sSimilarArticle key=key name="counter"}
                	{include file="articles/article_box_similar.tpl" sArticle=$sSimilarArticle}
                {/foreach}
                <div class="boxcap2_blue"></div>
            </div>
            {* /ARTICLE_SIMILAR *}
        {/if}
	</div>
	<div class="fixfloat"></div>
</div>

{literal}
<script type="text/javascript">
window.onload=function()
{
	Lightbox.init({descriptions: '.lightboxDesc', showControls: true});
}
</script>
{/literal}