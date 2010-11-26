{block name="frontend_detail_data"}
	{* Caching instock status *}
	{if !$sView}
		<input id='instock_{$sArticle.ordernumber}'type='hidden' value='{$sArticle.instock}' /> 
	{/if}
	
	{if $sArticle.sBlockPrices && (!$sArticle.sConfigurator || $sArticle.pricegroupActive) && $sArticle.sConfiguratorSettings.type!=2} 
		{foreach from=$sArticle.sBlockPrices item=row key=key} 
			{if $row.from=="1"} 
				<input id='price_{$sArticle.ordernumber}'type='hidden' value='{$row.price|replace:",":"."}' /> 
			{/if} 
		{/foreach} 
	{else}
		{if !$sView}
			<input id='price_{$sArticle.ordernumber}' type='hidden' value='{$sArticle.price|replace:".":""|replace:",":"."}' />
		{/if}
	{/if} 
	
	{* Order number *}
	{if $sArticle.ordernumber} 
		{block name='frontend_detail_data_ordernumber'}
			<p>{se name="DetailDataId"}{/se} {$sArticle.ordernumber}</p>
		{/block}
	{/if}
	
	{* Attributes fields *}
	{block name='frontend_detail_data_attributes'}
		{if $sArticle.attr1} 
			<p>{$sArticle.attr1}</p>
		{/if}
		{if $sArticle.attr2} 
			<p>{$sArticle.attr2}</p>
		{/if}
	{/block}
		   
	{block name="frontend_detail_data_delivery"}
	
		{* Delivery informations *}
		{include file="frontend/plugins/index/delivery_informations.tpl" sArticle=$sArticle}
	{/block}
	
	{if !$sArticle.liveshoppingData.valid_to_ts}
	
		{* Graduated prices *}
		{if $sArticle.sBlockPrices && (!$sArticle.sConfigurator || $sArticle.pricegroupActive) && $sArticle.sConfiguratorSettings.type!=2 && !$sArticle.liveshoppingData.valid_to_ts}
			{block name='frontend_detail_data_block_prices_start'}
			<div class="space">&nbsp;</div>
			<h5 class="bold">{se name="DetailDataHeaderBlockprices"}{/se}</h5>
				
			<table width="220"  border="0" cellspacing="0" cellpadding="0" class="text">
				<thead>
					<tr>
						<td width="90">
							<strong>{se name="DetailDataColumnQuantity"}{/se}</strong>
						</td>
						<td width='70'>
							<strong>{se name="DetailDataColumnPrice"}{/se}</strong>
						</td>
					</tr>
				</thead>
				
				<tbody>
					{foreach from=$sArticle.sBlockPrices item=row key=key}
						{block name='frontend_detail_data_block_prices'}
						<tr valign="top">
							<td>
								{if $row.from=="1"} 
									{se name="DetailDataInfoUntil"}{/se} {$row.to}
								{else}
									{se name="DetailDataInfoFrom"}{/se} {$row.from}
								{/if}
							</td>
							<td>
								<strong>
									{$row.price|currency}*
								</strong>
							</td>
						</tr>
						{/block}
					{/foreach}
				</tbody>
			</table>
			{/block}
			
			{* Article price *}
			{block name='frontend_detail_data_price_info'}
				<p class="modal_open">
					{s name="DetailDataPriceInfo"}{/s} 
				</p>
			{/block}
		
		{else}
			{* Pseudo price *}
			<div class='article_details_bottom'>
				<div {if $sArticle.pseudoprice} class='article_details_price2'>{else} class='article_details_price'>{/if}
					{block name='frontend_detail_data_pseudo_price'}
					{if $sArticle.pseudoprice}
					{* if $sArticle.sVariants || $sArticle.priceStartingFrom*}
					<div class="PseudoPrice displaynone">
		            	<em>{$sArticle.pseudoprice|currency}</em>
		            	{if $sArticle.pseudopricePercent.float}
		            		<span>
		            			({$sArticle.pseudopricePercent.float} % {se name="DetailDataInfoSavePercent"}{/se})
		            		</span>
		            	{/if}
		            </div>
		          	{*/if*}
		            {/if}
		            {/block}
		            
		          	{* Article price configurator *}
		            {block name='frontend_detail_data_price_configurator'}
					<strong {if $sArticle.priceStartingFrom && $sView} class="starting_price"{/if}>
						{if $sArticle.priceStartingFrom && !$sArticle.sConfigurator && $sView}
							<span id="DetailDataInfoFrom">{se name="DetailDataInfoFrom"}{/se}</span>
							{$sArticle.priceStartingFrom|currency}
						{else}
							{$sArticle.price|currency}
						{/if}
					</strong>
					{/block}
				</div>
				
				{* Article price *}
				{block name='frontend_detail_data_price_info'}
				<p class="tax_attention modal_open">
					{s name="DetailDataPriceInfo"}{/s}
				</p>
				{/block}
			</div>
		{/if}	
		{if $sArticle.purchaseunit}
				{* Article price *}
				{block name='frontend_detail_data_price'}
					<hr class="space" />
					<div class='article_details_price_unit'>
					<strong>
						<span>
							{se name="DetailDataInfoContent"}{/se} {$sArticle.purchaseunit} {$sArticle.sUnit.description}
						</span>
						
						<br />
						{if $sArticle.purchaseunit != $sArticle.referenceunit}
							<span class="smallsize">
			 				{if $sArticle.referenceunit}
			 					{se name="DetailDataInfoBaseprice"}{/se} {$sArticle.referenceunit} {$sArticle.sUnit.description} = {$sArticle.referenceprice} {$this->config('CURRENCYHTML')}
			 				{/if}
			 				</span>
						{/if}
					</strong>
					</div>
				{/block}
		{/if}
	{/if}
	
	{block name="frontend_detail_data_liveshopping"}
		{* Liveshopping *}
		{if $sArticle.liveshoppingData.valid_to_ts}
			{if $sArticle.liveshoppingData.typeID == 2 || $sArticle.liveshoppingData.typeID == 3}
				{include file="frontend/detail/liveshopping/detail_countdown.tpl" sLiveshoppingData=$sArticle.liveshoppingData}
			{else}
				{include file="frontend/detail/liveshopping/detail.tpl" sLiveshoppingData=$sArticle.liveshoppingData sArticlePseudoprice=$sArticle.pseudoprice}
			{/if}
		{/if}
	{/block}
{/block}