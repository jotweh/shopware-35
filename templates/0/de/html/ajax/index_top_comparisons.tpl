<div id="compareContainer" onmouseover="showCompare();" onmouseout="hideCompare();" {if $sComparisons|@count == 0}style="display:none;"{/if} style="z-index:1000;">
{if $sComparisons|@count >= 1}
	<span  style="font-weight:bold" id="compareHighlight">{$sComparisons|@count} {* sSnippet: compare article *}{$sConfig.sSnippets.sAjaxcomparearticle}</span>

{/if}

</div>
<div id="compareContainerResults" style="display:none; z-index:7000;" onmouseover="showCompare()" onmouseout="hideCompare()">
	<ul>
	{foreach from=$sComparisons item=compare}
	<li>
	<div style="float:left;width:190px;">{$compare.articlename}</div> <a onclick="deleteCompare('{$compare.articleID}')" class="del_comp">&nbsp;</a><div class="fixfloat"></div>
	</li>
	{/foreach}
	<li><a onclick="startComparison()" style="cursor:pointer;" class="bt_compare">{* sSnippet: start compare *}{$sConfig.sSnippets.sAjaxstartcompare}</a></li>
	
	<li><a onclick="deleteComparisons()" style="cursor:pointer" class="bt_compare_del">{* sSnippet: delete compare *}{$sConfig.sSnippets.sAjaxdeletecompare}</a></li>
	</ul>
</div>	


