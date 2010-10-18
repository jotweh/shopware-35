{if $_GET.sTarget}
	<input name="sTarget" type="hidden" value="sale" />
{/if}
<div class="paypoint{if $sChoosenPayment==$sPayment.id} paypoint_active{/if}">
	<input class="radio" name="sPayment" id="paypal" value="{$sPayment.id}" type="radio" {if $sChoosenPayment==$sPayment.id OR (!$sChoosenPayment AND $sConfig.sDEFAULTPAYMENT==$sPayment.id)}checked{/if} />
	<label class="paylabel" for="paypal">{$sPayment.description}</label><br/>
    {if $sPayment.additionaldescription}<p class="paydescr">{$sPayment.additionaldescription}</p>{/if}
</div>
<div class="fixfloat"></div>
<p class="paypal" style="height:60px;">
	<!-- PayPal Logo -->
		<a href="#" onclick="javascript:window.open('https://www.paypal.com/de/cgi-bin/webscr?cmd=xpt/Marketing/popup/OLCWhatIsPayPal-outside','olcwhatispaypal','toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=yes, width=400, height=500');">
		<img  src="https://www.paypal.com/de_DE/DE/i/logo/lockbox_100x45.gif" border="0" alt="PayPal-Bezahlmethoden-Logo"></a>
	<!-- PayPal Logo -->
</p>
