{if $sArticle.notifyLicence}
	{if $sArticle.notifyMessage.sMessage}
		{if $sArticle.notifyMessage.sConfirmed==0}
			<div class="error">
				{$sArticle.notifyMessage.sMessage}
			</div>
		{else}
			<div class="accept">
				<strong>{$sArticle.notifyMessage.sMessage}</strong>
			</div>
		{/if}
		<div class="fixfloat"></div>
	{/if}
{/if}
