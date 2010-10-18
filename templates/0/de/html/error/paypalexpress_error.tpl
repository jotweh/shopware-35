<h1>{* sSnippet: error *}{$sConfig.sSnippets.sErrorerror}</h1>
<p><strong>{$resArray}</strong></p><br /><br />
<p><strong>{$sError}</strong></p><br /><br />
{if $sErrorFlag.email}{$sErrorMessages.email}{/if}
{if $sErrorFlag.firstname}{$sErrorMessages.firstname}{/if}
{if $sErrorFlag.lastname}{$sErrorMessages.lastname}{/if}
{if $sErrorFlag.street}{$sErrorMessages.street}{/if}
{if $sErrorFlag.streetnumber}{$sErrorMessages.streetnumber}{/if}
{if $sErrorFlag.zipcode}{$sErrorMessages.zipcode}{/if}
{if $sErrorFlag.city}{$sErrorMessages.city}{/if}
{if $sErrorFlag.country}{$sErrorMessages.country}{/if}
{if $sErrorFlag.phone}{$sErrorMessages.phone}{/if}