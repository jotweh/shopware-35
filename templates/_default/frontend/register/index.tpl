{extends file="frontend/index/index.tpl"}

{* Title *}
{block name='frontend_index_header_title'}
	{s name="RegisterTitle"}{/s} | {$this->config('sShopname')}
{/block}

{* Step box *}
{block name="frontend_index_content_top"}
	{include file="frontend/register/steps.tpl" sStepActive="register"}
{/block}

{* Hide sidebar left *}
{block name='frontend_index_content_left'}{/block}

{* Hide breadcrumb *}
{block name='frontend_index_breadcrumb'}<hr class="clear" />{/block}

{block name="frontend_index_content"}
	<div class="grid_16 register" id="center">
		{if $register.personal.form_data.sValidation}
			{block name='frontend_register_index_dealer_register'}
			    <div class="supplier_register">
			    	<div class="inner_container">
				        <h1>{$sShopname} {s name='RegisterHeadlineSupplier'}{/s}</h1>
				       	<strong>{s name='RegisterInfoSupplier'}{/s}</strong><br />
				        <a href="{url controller='account'}" class="account">{s name='RegisterInfoSupplier2'}{/s}</a>
				        
				        <div class="space">&nbsp;</div>
				        
						<h4 class="bold">{s name='RegisterInfoSupplier3'}{/s}</h4>
						
				        <h5 class="bold">{s name='RegisterInfoSupplier4'}{/s}</h5>{s name='RegisterInfoSupplier5'}{/s}
				        <div class="space">&nbsp;</div>
				        
				       <h5 class="bold">{s name='RegisterInfoSupplier6'}{/s}</h5>{s name='RegisterInfoSupplier7'}{/s}
			    	</div>
			    </div>
		    {/block}
		{/if}
			
		<form method="post" action="{$this->url(['action'=>'saveRegister'])}">
			
			{include file="frontend/register/error_message.tpl" error_messages=$register->personal->error_messages}
			{include file="frontend/register/personal_fieldset.tpl" form_data=$register->personal->form_data error_flags=$register->personal->error_flags}
			
			{include file="frontend/register/error_message.tpl" error_messages=$register->billing->error_messages}
			{include file="frontend/register/billing_fieldset.tpl" form_data=$register->billing->form_data error_flags=$register->billing->error_flags country_list=$register->billing->country_list}
			
			{include file="frontend/register/error_message.tpl" error_messages=$register->shipping->error_messages}
			{include file="frontend/register/shipping_fieldset.tpl" form_data=$register->shipping->form_data error_flags=$register->shipping->error_flags country_list=$register->shipping->country_list}
			
			<div class="payment_method register_last"></div>
			{*
			{include file="frontend/register/error_message.tpl" error_messages=$register->payment->error_messages}
			{include file="frontend/register/payment_fieldset.tpl" form_data=$register->payment->form_data error_flags=$register->payment->error_flags payment_means=$register->payment->payment_means}
			*}
			
			{* Privacy checkbox *}
			{if !$update}
				{if $this->config('ACTDPRCHECK')}
					{block name='frontend_register_index_input_privacy'}
						<div class="privacy">
							<input name="register[personal][dpacheckbox]" type="checkbox" id="dpacheckbox"{if $form_data.dpacheckbox} checked="checked"{/if} value="1" class="chkbox" />
							<label for="dpacheckbox" class="chklabel{if $register->personal->error_flags.dpacheckbox} instyle_error{/if}">{s name='RegisterLabelDataCheckbox'}{/s}</label>
							<div class="clear">&nbsp;</div>
						</div>
					{/block}
				{/if}
			{/if}
			
			{* Required fields hint *}
			<div class="required_fields">
				{s name='RegisterPersonalRequiredText' namespace='frontend/register/personal_fieldset'}{/s}
			</div>
	
			
			<div class="actions">
				<input id="registerbutton" type="submit" class="right" value="{s name='RegisterIndexActionSubmit'}{/s}" />
				<hr class="clear space" />
			</div>
			
		</form>
	</div>
{/block}
{* Sidebar right *}
{block name='frontend_index_content_right'}
	<div id="right" class="grid_5 register last">
		{s name='RegisterInfoAdvantages'}{/s}
	</div>
{/block}