<ul id="submenu">
    {foreach from=$sCategories item=sCategory}
        {if $sCategory.subcategories}
            <li><a  href="{$sCategory.link}" title="{$sCategory.description|truncate:24:".":true}" class="active">{$sCategory.description}</a>
                {include file="category/category_subcategories.tpl" sCategories=$sCategory.subcategories}
            </li>
        {else}
            {if $sCategory.flag}
                <li><a href="{$sCategory.link}" title="{$sCategory.description|truncate:24:".":true}" class="flag">{$sCategory.description}</a></li>
            {else}
                <li><a href="{$sCategory.link}" title="{$sCategory.description|truncate:24:".":true}">{$sCategory.description}</a></li>
            {/if}
        {/if}
    {/foreach}
</ul>