<!-- col_center2 -->
<div class="col_center2">
        <div class="bg_cross">
            <p class="heading">{* sSnippet: this article is no longer in our store *}{$sConfig.sSnippets.sErrorthisarticleisnolonger}</p>
           
           
            <a href="{$sBasefile}" title="Startseite" class="btn_high_r button">{* sSnippet: home *}{$sConfig.sSnippets.sErrorhome}</a>
        </div>
    
    <!-- Kunden kauften auch -->
    {if $sCross}
        <!-- cross_box -->
        <div class="cross_box">
            <div class="cross_box_top1"><p>{* sSnippet: More interesting articles *}{$sConfig.sSnippets.sErrormoreinterestingarticles}</p></div>
                    <div class="cross_box_content">
                {foreach from=$sCross item=sArticle key=key}				
                    {include file="articles/article_box_4col_cross_boughttoo.tpl" sArticle=$sArticle}		
                {/foreach}
                <div class="fixfloat"></div>
                </div>
            <div class="cross_box_cap"></div>
        </div>
        <!-- cross_box -->
    {/if}
    <!-- /Kunden kauften auch -->
    
   
    
    <div class="fixfloat"></div>
</div>
<!-- /col_center2 -->



