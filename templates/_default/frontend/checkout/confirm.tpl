{extends file="frontend/index/index.tpl"}

{* Javascript *}
{block name="frontend_index_header_javascript" append}
<script type="text/javascript">
//<![CDATA[
	if(top!=self){
		top.location=self.location;
	}
//]]>
</script>
{/block}

{* Hide breadcrumb *}
{block name='frontend_index_breadcrumb'}<hr class="clear" />{/block}

{block name="frontend_index_content_top"}
<div class="grid_20 first">

	{* Step box *}
	{include file="frontend/register/steps.tpl" sStepActive="finished"}
	
	{* AGB is not accepted by user *}
	{if $sAGBError}
		<div class="error agb_confirm">
			<div class="center">
				<strong>
					{s name='ConfirmErrorAGB'}{/s}
				</strong>
			</div>
		</div>
	{/if}
	
	{* Check order headline *}
	<div class="check_order">
		<h2 class="headingbox">{s name="ConfirmHeader"}{/s}</h2>
		<div class="inner_container">			
			{* Payment informations *}
		 	<p>
		 		{s name="ConfirmInfoChange"}{/s}
		 	</p>
		 	
		 	<p>
		 		{s name="ConfirmInfoPaymentData"}{/s}
		 	</p>
	 	</div>
 	</div>
</div>
{/block}

{* Sidebar left *}
{block name='frontend_index_content_left'}
	{include file="frontend/checkout/confirm_left.tpl"}
{/block}

{block name="frontend_index_header_javascript" append}
<script type="text/javascript">
//<![CDATA[
	jQuery(document).ready(function($) {
		$('.payment_method .change a').click(function(){
			$('.method_hide').fadeIn();
			$(this).css('display', 'none');
			$('.method_selected').css('display', 'none');
			return false;
		});
	});
//]]>
</script>
{/block}

{* Main content *}
{block name="frontend_index_content"}
<div id="confirm" class="grid_16 first">

	{block name='frontend_checkout_confirm_payment'}
	{include file='frontend/checkout/confirm_payment.tpl'}
	{/block}
	
	{* Error messages *}
	{block name='frontend_checkout_confirm_error_messages'}
		{include file="frontend/checkout/error_messages.tpl"}
	{/block}
	
	<div class="table grid_16">
		{include file="frontend/checkout/confirm_header.tpl"}
		
		{* Article items *}
		{foreach name=basket from=$sBasket.content item=sBasketItem key=key}
			{include file='frontend/checkout/confirm_item.tpl'}	
		{/foreach}
		
		{* Premium articles *}
		{block name='frontend_checkout_confirm_premiums'}
		{include file='frontend/checkout/premiums.tpl'}
		{/block}
		
		{* Dispatch method *}
		{block name='frontend_checkout_confirm_shipping'}
		{if $sDispatches}
			<div class="shipping_select">
				<h4 class="bold">{se name="ConfirmHeadDispatch"}{/se}</h4>
				<p>
					<strong>{s name="ConfirmLabelDispatch"}{/s}:</strong> 
					{$sDispatch.name}
				</p>
				
				{if $sDispatches}
					<form id="recalcShipping" method="POST" action="{url action='calculateShippingCosts' sTargetAction=$sTargetAction}"> 
						<div class="select_shipping">
							{foreach from=$sDispatches item=dispatch}
								<div class="select">
									<input id="basket_dispatch{$dispatch.id}" type="radio" value="{$dispatch.id}" name="sDispatch" {if $dispatch.id eq $sDispatch.id}checked="checked"{/if} class="auto_submit" />
									{$dispatch.name}
								</div>
							{/foreach}
							<div class="clear">&nbsp;</div>
						</div>
						<noscript>
							<input type="submit" value="{s name='ConfirmLinkChangeDispatch'}{/s}" />
						</noscript>
					</form>
				{/if}
				
				{if $sDispatch.description}
					<div class="space">&nbsp;</div>
					<div class="description">
						<h4 class="bold">{se name="ConfirmHeadDispatchNotice"}{/se}</h4>
						<div class="shipping_description">
							<blockquote>{$sDispatch.description}</blockquote>
						</div>
					</div>
				{/if}
				
			</div>
		{/if}
		{/block}
				
		{* Table footer *}
		{include file="frontend/checkout/confirm_footer.tpl"}
	</div>
	
	<div class="space">&nbsp;</div>
	
	{* Additional footer *}
	<div class="additional_footer">
		<form  method="post" action="{if !$sUserData.additional.payment.embediframe}{url action='finish'}{else}{url action='payment'}{/if}">
		
			<div class="clear">&nbsp;</div>
			
			{* User comment *}
			{block name='frontend_checkout_confirm_comment'}
			<div class="comment">
	    		<label for="sComment">{s name="ConfirmLabelComment"}{/s}</label>
	           	<textarea name="sComment" rows="5" cols="20">{$sComment|escape}</textarea>
			</div>
			{/block}
		
			{* Newsletter registration *}
			{block name='frontend_checkout_confirm_newsletter'}
			{if !$sUserData.additional.user.newsletter}
			<div class="more_info">
				<h4 class="bold">{s name="ConfirmHeaderNewsletter"}{/s}</h4>
				<p>
		    		<input type="checkbox" name="sNewsletter" value="1" class="chkbox"{if $sNewsletter} checked="checked"{/if} />
		    		<label for="newsletter" class="chklabel">
		    			{s name="ConfirmLabelNewsletter"}{/s}
		    		</label>
		    	</p>
			</div>
			{/if}
			{/block}
			{block name='frontend_checkout_confirm_footer'}
			{* AGB checkbox *}
			<div class="agb">
				{s name="ConfirmTextRightOfRevocation" class="revocation"}{/s}
				{block name='frontend_checkout_confirm_agb'}
				{if !$this->config('IGNOREAGB')}
					<div>
				    	<div class="agb_accept">
				    		<input type="checkbox" class="left" name="sAGB" id="sAGB" value="1" />
				    		<label for="sAGB" class="chklabel modal_open {if $sAGBError}instyle_error{/if}">{s name="ConfirmTerms"}{/s}</label>
				    	</div>
				    	<div class="space">&nbsp;</div>
				    
				    	<div class="agb_info">
				    		{s name="ConfirmTextOrderDefault"}{/s}
				    	</div>
			    	</div>
		    	{/if}
		    	{/block}
		    	{if !$sLaststock.hideBasket}
			    	{block name='frontend_checkout_confirm_submit'}
			    	{* Submit order button *}
			    	<div class="actions">
			    		{if !$sUserData.additional.payment.embediframe}
			    			<input type="submit" class="button-right large" id="basketButton" value="{s name='ConfirmActionSubmit'}{/s}" />
			    		{else}
			    			<input type="submit" class="button-right large" id="basketButton" value="{s name='ConfirmDoPayment'}Zahlung durchführen{/s}" />
			    		{/if}
			    	</div>
			    	{/block}
		    	{else}
			    	{block name='frontend_checkout_confirm_stockinfo'}
			    	<div class="error">
						<div class="center">
							<strong>
								{s name='ConfirmErrorStock'}Ein Artikel aus Ihrer Bestellung ist nicht mehr verfügbar! Bitte entfernen Sie die Position aus dem Warenkorb!{/s}
							</strong>
						</div>
					</div>
					{/block}
		    	{/if}
		    	<div class="clear">&nbsp;</div>
	    	{/block}
			</div>
		</form>
	</div>
	<div class="doublespace">&nbsp;</div>
</div>
{/block}