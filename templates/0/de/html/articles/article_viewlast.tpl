<!-- article last viewed -->
<div id="angeschaut" class="box">
	<p class="heading">{* sSnippet: last viewed *}{$sConfig.sSnippets.sArticleLastViewed}</p>
	
	
    {foreach from=$sLastArticles item=sArticle key=key name="counter"}
        <!-- lastview_rule  -->
        <div {if $key==$smarty.foreach.counter.total-1}class="lastview_rule_last"{else}class="lastview_rule"{/if}>
        
        <!-- article picture  -->
        {if $sArticle.img}
        	<a href="{$sArticle.linkDetails}" title="{$sArticle.name}" class="article_image" style="background: #fff url({$sArticle.img}) no-repeat center center;"><span class="hidden">{$sArticle.name}</span></a>
        {else}
        	<a href="{$sArticle.linkDetails}" title="{$sArticle.name}" class="article_image" style="background: #fff url(../../media/img/de/layout/no_picture.jpg) no-repeat center center;"><span class="hidden">{* sSnippet: more information *}{$sConfig.sSnippets.sArticleMoreinformation}</span></a>
        {/if}
        <!-- /article picture -->
        
        <!-- article name -->
        	<a href="{$sArticle.linkDetails}" title="{$sArticle.name}" class="article_description">{$sArticle.name|wordwrap:19:"<br/>":true}</a>
        <!-- /article name -->
        
        <div class="fixfloat"></div>
        </div>
        <!-- /lastview_rule  -->
    {/foreach}
    
<div class="boxcap2"></div>
</div>
<!-- /article last viewed -->