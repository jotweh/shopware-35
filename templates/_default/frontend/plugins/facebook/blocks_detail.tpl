{block name="frontend_detail_index_actions" append}
{if !$hideFacebook}
<div style="margin-top:25px">
<script src="http://connect.facebook.net/{$Locale}/all.js#xfbml=1"></script><fb:like href="{url sArticle=$sArticle.articleID}" width="250"></fb:like>
</div>
{/if}
{/block}

{block name="frontend_detail_index_tabs_related" append}
{if $app_id && !$hideFacebook}
<div id="facebook">
        <h2>Facebook-Kommentare</h2>
        <div class="container">
        <div id="fb-root"></div><script src="http://connect.facebook.net/{$Locale}/all.js#appId={$app_id}&amp;xfbml=1"></script><fb:comments xid="{$unique_id}" width="425"></fb:comments>
        </div>
</div>
{/if}
{/block}

{block name="frontend_detail_tabs_rating" append}
{if $app_id && !$hideFacebook}
<li>
	<a href="#facebook">Facebook-Kommentare</a>
</li>
{/if}
{/block}