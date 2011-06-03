{block name="backend_index_index_menu_function"}
	{function name=backend_menu level=0}
		{foreach from=$categories item=category}
		{if $category->isVisible()}
		    <li {if !$level}class="main"{/if}>
				<a class="{$category->class}" style="{$category->style};cursor:pointer" {if $category->onclick}onclick="{$category->onclick|replace:'{release}':"{config name='Version'}"}"{/if}>
					{$category->label|snippet:null:'backend/index/menu'} 
				</a>
		    	{if $category->hasChildren()}
		    		<ul {if $level}style="margin-left:100%;width:100%"{/if}>
			     		{call name=backend_menu categories=$category level=$level+1}
			     	</ul>
			    {/if}
		    </li>
		{/if}
		{/foreach}
	{/function}
{/block}

{if $Menu}
	<ul id="nav">
		{call name=backend_menu categories=$Menu}
	</ul>
{/if}