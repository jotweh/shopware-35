<div class="ajax_modal_custom">
	{block name='frontend_custom_ajax_action_buttons'}
	<div class="heading">
		<h2>{$sCustomPage.description}</h2>
	
		{* Close button *}
		<a href="#" class="modal_close" title="{s name='LoginActionClose'}{/s}">
			{s name='LoginActionClose'}{/s}
		</a>
	</div>

	<!-- <p>
		<input type="button" class="button-left large modal_close" value="{s name='CustomAjaxActionClose'}{/s}" />
		<a href="{url sCustom=$sCustomPage.id title=$sCustomPage.description}" class="button-right large right" target="_blank">{se name='CustomAjaxActionNewWindow'}{/se}</a>
	</p> -->
	{/block}
	{* Article content *}
	{block name='frontend_custom_ajax_article_content'}
		<div class="inner_container">
			{$sContent}
		</div>
	{/block}
</div>