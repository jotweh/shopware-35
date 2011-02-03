{function name=categories_top level=0}
<ul class="{if !$level}dropdown{else}droplevel{/if} droplevel{$level}">
{foreach from=$categories item=category}
    <li class="{if $category.flag}active{/if}{if $category.sub} sub{/if}">
     	<a href="{$category.link}" class="{if $category.flag} active{/if}">{$category.name}</a>
    	{if $category.sub}
	     	{call name=categories_top categories=$category.sub level=$level+1}
	    {/if}
    </li>
{/foreach}
</ul>
{/function}

<div id="mainNavigation" class="grid_20">
	<ul>
		<li class="{if $sCategoryCurrent eq $sCategoryStart}active{/if}">
			<a href="{url controller='index'}" title="{s name='IndexLinkHome'}Home{/s}" class="first{if $sCategoryCurrent eq $sCategoryStart} active{/if}">
				{se name='IndexLinkHome'}{/se}
			</a>
		</li>
	    {foreach from=$sAdvancedMenu item=sCategory}
			<li class="{if $sCategory.flag}active{/if}{if $sCategory.sub} dropactive{/if}">
	        	<a href="{$sCategory.link}" title="{$sCategory.description}" {if $sCategory.flag} class="active"{/if}>
	        		{$sCategory.description}
	        	</a>
	        	{if $sCategory.sub}
			     	{call name=categories_top categories=$sCategory.sub}
			    {/if}
	        </li>
		{/foreach}
	</ul>
</div>