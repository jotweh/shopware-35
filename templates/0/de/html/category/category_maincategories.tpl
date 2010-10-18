{foreach from=$sCategories item=sCategory}
    {if $sCategory.subcategories}
        <!-- submenu -->
        <div class="box3">
            {include file="category/category_subcategories.tpl" sCategories=$sCategory.subcategories}
        </div>
        <!-- /submenu -->
    {else}
    {/if}
{/foreach}