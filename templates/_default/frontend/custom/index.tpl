{extends file='frontend/index/index.tpl'}

{* Main content *}
{block name='frontend_index_content'}
<div id="center" class="custom grid_13">

	<h1>{$sCustomPage.description}</h1>
	
	{* Article content *}
	{block name='frontend_custom_article_content'}
		{$sContent}
	{/block}
</div>
{/block}

{* Sidebar right *}
{block name='frontend_index_content_right'}
	{include file="frontend/custom/right.tpl"}
{/block}