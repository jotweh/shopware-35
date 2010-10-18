{if $sSearchResults.sPropertyGroups||$sRequests.sFilter.propertygroup}
{* SEARCH_BOX *}
<div class="searchbox">
 <h2>{* sSnippet: search result *}{$sConfig.sSnippets.sSearchsearchresult} <br />{* sSnippet: after filters *}{$sConfig.sSnippets.sSearchafterfilters}</h2>
    <ul id="submenu">
    {if !$sRequests.sFilter.propertygroup}
    {foreach from=$sSearchResults.sPropertyGroups item=sPropertyGroup}
        <li><h3><a href="{$sLinks.sFilter.propertygroup}&sFilter_propertygroup={$sPropertyGroup.filerID}">{$sPropertyGroup.name} ({$sPropertyGroup.count})</a></h3></li>
    {/foreach}
    
    {else}
    	{foreach from=$sSearchResults.sPropertyGroups item=sPropertyGroup}
        <li><h3 class="active">{$sPropertyGroup.name}</h3></li>
        {/foreach}
    	<ul>
    	{foreach from=$sSearchResults.sPropertyOptions item=sPropertyOption key=optionID}
    		<li><h2>{$sPropertyOption.name}</h2></li>
    		<ul>
    		{if $sPropertyOption.selected}
    			{foreach from=$sSearchResults.sPropertyValues.$optionID item=sPropertyValue key=valueID}
	        		<li><h3 class="active">{$sPropertyValue.name}</h3></li>
    			{/foreach}
    			<li><h3><a href="{$sLinks.sFilter.propertygroup}&sFilter_propertygroup={$sRequests.sFilter.propertygroup|cat:'_'|replace:"_`$sPropertyOption.selected`_":'_'|trim:'_'|escape:'url'}" class="killfilter">{* sSnippet: show all *}{$sConfig.sSnippets.sSearchshowall}</a></h3></li>	
    		{else}
	        	{foreach from=$sSearchResults.sPropertyValues.$optionID item=sPropertyValue key=valueID}
	        		<li><h3><a href="{$sLinks.sFilter.propertygroup}&sFilter_propertygroup={$sRequests.sFilter.propertygroup|escape:'url'}_{$valueID}">{$sPropertyValue.name} ({$sPropertyValue.count})</a></h3></li>
	        	{/foreach}
        	{/if}
        	</ul>
    	{/foreach}
    	</ul>
       
        <li><h3><a href="{$sLinks.sFilter.propertygroup}" class="killfilter">{* sSnippet: all filters *}{$sConfig.sSnippets.sSearchallfilters}</a></h3></li>
    {/if}
    </ul>

    <div class="searchbox_cap"></div>
</div>
{* /SEARCH_BOX *}
{/if}

{if $sSearchResults.sSuppliers}
{* SEARCH_BOX *}
<div class="searchbox">
<h2>{* sSnippet: search result *}{$sConfig.sSnippets.sSearchsearchresult} <br />{* sSnippet: by manufacturer *}{$sConfig.sSnippets.sSearchbymanufacturer}</h2>
{assign var=sSuppliersFirst value=$sSearchResults.sSuppliers|@array_slice:0:10}
{assign var=sSuppliersRest value=$sSearchResults.sSuppliers|@array_slice:10}

    <ul id="submenu">
    {if !$sRequests.sFilter.supplier}
    {foreach from=$sSuppliersFirst item=supplier}
        <li><h3><a href="{$sLinks.sFilter.supplier}&sFilter_supplier={$supplier.id}">{$supplier.name} ({$supplier.count})</a></h3></li>
    {/foreach}
    
    {if $sSuppliersRest}
    <form name="frmsup" method="get" action="{$sLinks.sSupplier}" id="frmsup">
    <select name="sFilter_supplier" id="cSUP" onchange="$('frmsup').submit()">
        <option value="">{* sSnippet: other manufacturers *}{$sConfig.sSnippets.sSearchothermanufacturers}</option>
    {foreach from=$sSuppliersRest item=supplier}
        <option value="{$supplier.id}">{$supplier.name} ({$supplier.count})</option>
    {/foreach}
    </select>
    </form>
    {/if}
    {else}
        <li><h3 class="active">{$sSearchResults.sSuppliers[$sRequests.sFilter.supplier].name}</h3></li>
        <li><h3><a href="{$sLinks.sFilter.supplier}" class="killfilter">{* sSnippet: all manufacturer *}{$sConfig.sSnippets.sSearchallmanufacturer}</a></h3></li>
    {/if}
    </ul>

    <div class="searchbox_cap"></div>
</div>
{* /SEARCH_BOX *}
{/if}

{if $sSearchResults.sPrices||$sRequests.sFilter.price}
{* SEARCH_BOX *}
<div class="searchbox">
<h2>{* sSnippet: search result *}{$sConfig.sSnippets.sSearchsearchresult} <br />{* sSnippet: by price *}{$sConfig.sSnippets.sSearchbyprice}</h2>
<ul id="submenu">
    {if !$sRequests.sFilter.price}
    {foreach from=$sPriceFilter item=sFilterPrice key=sKey}
        {if $sSearchResults.sPrices.$sKey}
            <li><h3><a href="{$sLinks.sFilter.price}&sFilter_price={$sKey}">{$sFilterPrice.start}-{$sFilterPrice.end} {$sConfig.sCURRENCYHTML} ({$sSearchResults.sPrices.$sKey})
            {if $sFilterActive.price}{/if}</a></h3></li>
        {/if}
    {/foreach}
    
    {else}
        <li><h3 class="active">{$sPriceFilter[$sRequests.sFilter.price].start}-{$sPriceFilter[$sRequests.sFilter.price].end} €</h3></li>
        <li><h3><a href="{$sLinks.sFilter.price}" class="killfilter">{* sSnippet: all prices *}{$sConfig.sSnippets.sSearchallprices}</a></h3></li>
    {/if}
</ul>
<div class="searchbox_cap"></div>
</div>
{* /SEARCH_BOX *}
{/if}
{if $sSearchLoader}{$sSearchLoader}{/if}
<div class="fixfloat"></div>
