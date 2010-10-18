<h1>The PayPal API has returned an error!</h1>
<br />
{if $urlError}
	<p>Error Number: {$errorCode}</p>
	<p>Error Message: {$errorMessage}</p>
{else}
	<p>ACK: {$ACK}</p>
	<p>CORRELATIONID: {$CORRELATIONID}</p>
	<p>VERSION: {$VERSION}</p>

  {section name=row loop=$paypalAPIError}
      {strip}
         <p>Error Number: {$paypalAPIError[row].errorCode}</p>
         <p>Short Message: {$paypalAPIError[row].shortMessage}</p>
         <p>Long Message: {$paypalAPIError[row].longMessage}</p>
      {/strip}
   {/section}

{/if}
<br />
{$sConfig.sSnippets.sPaypalexpressApiError}
<br /><br /><a href="{$payPalURL}"><strong>{$sConfig.sSnippets.sPaypalexpressApiErrorLink}</strong></a>
