<div class="c2_box2 detail">
<p>{* sSnippet: the fields marked with * are mandatory. *}{$sConfig.sSnippets.sPaymentmarkedfieldsare}</p>
<form name="frmRegister" method="post" action="" class="registerform">
<fieldset>
<legend>{* sSnippet: your credit card *}{$sConfig.sSnippets.sPaymentyourcreditcard}</legend>
<input type="hidden" name="payment" value="1" />
<h2>&euro; <!-- Versandkosten einfuegen-->{$shipping_costs.Kreditkarte}<!-- END -->  {* sSnippet: shipping *}{$sConfig.sSnippets.sPaymentshipping}</h2><br />
<!--  KREDITKARTE START-->
<label for="payment_kk_karte">{* sSnippet: choose your credit card *}{$sConfig.sSnippets.sPaymentchooseyourcreditcard}</label>
<select name="payment_kk_karte" id="payment_kk_karte" class="normal {if $errormarkup.payment_kk_karte}instyle_error{/if}">
	<option value="Visa/Visa Electron" {if $payment_kk_karte eq "Visa/Visa Electron"}selected{/if}>Visa/Visa Electron</option>
	<option value="Master/Eurocard" {if $payment_kk_karte eq "Master/Eurocard"}selected{/if}>Master/Eurocard</option>
</select><br />
<label for="payment_kk_nr">{* sSnippet: your credit card number *}{$sConfig.sSnippets.sPaymentcreditcardnumber}</label>
<input name="payment_kk_nr" type="text" value="{$payment_kk_secure}" class="normal {if $errormarkup.payment_kk_nr}instyle_error{/if}" id="payment_kk_nr" /><br />
<label for="validmonth">{* sSnippet: valid until *}{$sConfig.sSnippets.sPaymentvaliduntil}</label>
<select name="validmonth" class="month {if $errormarkup.validmonth}instyle_error{/if}" id="validmonth">
	<option value=''>{* sSnippet: month *}{$sConfig.sSnippets.sPaymentmonth}</option>
	<option value='01' {if $validmonth eq "01"}selected{/if}>01</option>
	<option value='02' {if $validmonth eq "02"}selected{/if}>02</option>
	<option value='03' {if $validmonth eq "03"}selected{/if}>03</option>
	<option value='04' {if $validmonth eq "04"}selected{/if}>04</option>
	<option value='05' {if $validmonth eq "05"}selected{/if}>05</option>
	<option value='06' {if $validmonth eq "06"}selected{/if}>06</option>
	<option value='07' {if $validmonth eq "07"}selected{/if}>07</option>
	<option value='08' {if $validmonth eq "08"}selected{/if}>08</option>
	<option value='09' {if $validmonth eq "09"}selected{/if}>09</option>
	<option value='10' {if $validmonth eq "10"}selected{/if}>10</option>
	<option value='11' {if $validmonth eq "11"}selected{/if}>11</option>
	<option value='12' {if $validmonth eq "12"}selected{/if}>12</option>
</select>
<select name="validyear" class="year {if $errormarkup.validyear}instyle_error{/if}">
	<option value=''>{* sSnippet: year *}{$sConfig.sSnippets.sPaymentyear}</option>
	<option value='2005' {if $validyear eq "2005"}selected{/if}>2005</option>
	<option value='2006' {if $validyear eq "2006"}selected{/if}>2006</option>
	<option value='2007' {if $validyear eq "2007"}selected{/if}>2007</option>
	<option value='2008' {if $validyear eq "2008"}selected{/if}>2008</option>
	<option value='2009' {if $validyear eq "2009"}selected{/if}>2009</option>
	<option value='2010' {if $validyear eq "2010"}selected{/if}>2010</option>
	<option value='2011' {if $validyear eq "2011"}selected{/if}>2011</option>
	<option value='2012' {if $validyear eq "2012"}selected{/if}>2012</option>
	<option value='2013' {if $validyear eq "2013"}selected{/if}>2013</option>
</select><br />
<label for="payment_kk_inhaber">{* sSnippet: name of cardholder *}{$sConfig.sSnippets.sPaymentnameofcardholder}</label>
<input name="payment_kk_inhaber" type="text" value="{$payment_kk_inhaber}" id="payment_kk_inhaber" class="normal {if $errormarkup.payment_kk_inhaber}instyle_error{/if}" /><br />
<br />   
<input type="submit" name="Submit" value="Auswählen" class="submitbutton"/>  	   
<!--  KREDITKARTE END-->
</fieldset>
</form>
</div>