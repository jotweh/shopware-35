{extends file='frontend/index/index.tpl'}

{* Breadcrumb *}
{block name='frontend_index_start' append}
	{$sBreadcrumb[] = ['name'=>"{s name='PaymentTitle'}{/s}"]}
{/block}

{* Main content *}
{block name='frontend_index_content'}
<div id="center" class="grid_13">

{if empty($Payment.status) || $Payment.status == 'error'}
	{if $Payment.fail_message}
		<h2>Ein Fehler ist aufgetreten: {$Payment.fail_message|escape|nl2br}</h2>
	{else}
		<h2>Es ist ein unbekannter Fehler aufgetreten und die Bestellung konnte nicht abgeschlossen werden.</h2>
	{/if}
	<br />
	<h3>Bitte kontaktieren Sie den Shopbetreiber.</h3>
{elseif $Payment.status == 'fail'}
	<h2>Die Bezahlung ist fehlgeschlagen.</h2>
	{if $Payment.fail_message}
		<h2>{$Payment.fail_message|escape|nl2br}</h2>
	{/if}
	<br />
	<h3>Bitte versuchen Sie es mit einer anderen Zahlungsart nochmal.</h3>
{elseif $Payment.status == 'success'}
	<h2>Bezahlungsprozess wurde erfolgreich abgeschlossen!</h2>
	<p>
	Klicken Sie <a href="{url controller=checkout action=finish sUniqueID=$Payment.secret}" target="_top">hier</a> um auf die Bestellabschlussseite zu kommen.
	</p>
{elseif $Payment.status == 'back'}
	<h2>Sie haben den Bezahlungsprozess abgebrochen.</h2>
{/if}

<br />
<div class="actions">
	<a class="button-left large left" href="{url controller=checkout action=cart}" title="{s name='PaymentLinkBack'}{/s}">
		Warenkorb ändern
	</a>
	<a class="button-right large right" href="{url controller=account action=payment sTarget=checkout}" title="{s name='PaymentLinkBack'}{/s}">
		Zahlungsart ändern
	</a>
</div>
</div>
{/block}

{block name='frontend_index_actions'}{/block}
{block name='frontend_index_checkout_actions'}{/block}
{block name='frontend_index_search'}{/block}

{* Javascript *}
{block name="frontend_index_header_javascript" append}
<script type="text/javascript">
//<![CDATA[
	{if !empty($Payment.status) && $Payment.status == 'success'}
		var href = '{url controller=checkout action=finish sUniqueID=$Payment.secret}';
	{else}
		var href = '{url action=end}';
	{/if}
	if(opener && opener.top) {
		opener.top.location.href=href;
		self.close();
	} else if(opener) {
		opener.location.href=href;
		self.close();
	} else if(top != self) {
		top.location=href;
	}
//]]>
</script>
{/block}