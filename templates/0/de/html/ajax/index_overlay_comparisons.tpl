<div id="comparisons_header" style="float:right;">
<a class="bt_compare_del" style="cursor: pointer;" onclick="hideCompareList()">{* sSnippet: Close Compare *}{$sConfig.sSnippets.sCompareClose}</a>
</div>

<div style="clear:both"></div>

<table border="1" align="center" style="background-color:#fff; z-index:9000;">
	<tr valign="top">
		<td style="border-right:1px solid #efefef;">
			{include file="ajax/article_box_desccol.tpl" sArticle=$sComparisons.articles sProperties=$sComparisons.properties}
		</td>
	{foreach from=$sComparisons.articles item=sComparison key=key name="counter"}
		<td style="width:200px;border-right:1px solid #efefef;" width="200">
			{include file="ajax/article_box_1col.tpl" sArticle=$sComparison sProperties=$sComparisons.properties}
		</td>
		{/foreach}
	</tr>
</table>

<input id="article_count" type="hidden" value="{$sComparisons.articles|@count}" />
