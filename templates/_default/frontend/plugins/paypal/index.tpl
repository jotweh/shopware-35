{* PayPal logo *}
{block name='frontend_index_left_menu' append}
	{if {config name="PaypalLogo"}}
		{include file='frontend/plugins/paypal/logo.tpl'}
	{/if}
{/block}

{* PayPal button *}
{block name="frontend_checkout_actions_confirm" append}
	{if {config name="Xpress"} && !$sUserLoggedIn}
		{include file='frontend/plugins/paypal/basket.tpl'}
	{/if}
{/block}