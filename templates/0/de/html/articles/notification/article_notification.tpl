{if $sArticle.notifyLicence}
<div id="article_notification"{if $sArticle.instock>0}style="display:none"{/if}>
	{if $sArticle.notification ==1} 
	 	{if $sArticle.sAlreadyForArticleRegistered}
			<p style="padding: 15px 5px;">	
				{$sConfig.sSnippets.sAlreadyForArticleRegistered}
			</p>
		{elseif $sArticle.sShowNotificationFunction || $sArticle.sShowWrongEmailMessage}
			<form name="frmNotification" method="POST" action="" id="sendArticleNotification">
				<input name="sAction" type="hidden" value="sendArticleNotification" />
				<input name="sViewport" type="hidden" value="{$_GET.sViewport}" />
				<input name="sArticle" id="variantOrdernumber" type="hidden" value="{$sArticle.ordernumber}" />
				
				<fieldset>
					{if $sArticle.sShowWrongEmailMessage}
						<p>
							{$sConfig.sSnippets.sErrorValidEmail}
						</p>
					{/if}
					<p>
						{$sConfig.sSnippets.sRegisterForNotification}
					</p>
					<p>{$sConfig.sSnippets.sNotificationLabel}
						<input name="sNotificationEmail" type="text" id="txtmail" class="normal" value="{$sConfig.sSnippets.sArticleyourmail}" onclick="this.value='';this.onclick=null;" />
						<input type="submit" value="{$sConfig.sSnippets.sArticlesNotificationSignIn}" class="btn_high_r button width_reset" />	
					</p>
				</fieldset>
			</form>
			
			<div class="fixfloat"></div>
			<span id="articleNotificationWasSend" style="display:none">
				<p style="padding: 15px 5px;">	
					{$sConfig.sSnippets.sArticleNotificationSend}
				</p>
			</span>
		{else}
			<p style="padding: 15px 5px;">	
				{$sConfig.sSnippets.sArticleNotificationSend}
			</p>
		{/if}
		{else}
		
	{/if}
</div>

<script language="JavaScript" type="text/javascript">
	{literal}
		function checkNotification(ordernumber) {
			var variantOrdernumberArray = new Array();
			{/literal}
			{foreach from=$sArticle.sNotificationVariants item=notify}
				variantOrdernumberArray.push('{$notify}');
			{/foreach}
			{literal}
			var isSet = false;
			for (var i = 0; i < variantOrdernumberArray.length; ++i){
				
				if(variantOrdernumberArray[i] == ordernumber){
					isSet = true;
					try {$('articleNotificationWasSend').style.display="block";
					}catch (e){}
					try {$('sendArticleNotification').style.display="none";
					}catch (e){}
				}
			}
			if(isSet == false){
				try {
					$('articleNotificationWasSend').style.display="none";
				} catch(e){}
				try {
				$('sendArticleNotification').style.display="block";
				}catch (e){}
			}
			if (ordernumber != "0") {
				var currentInstock = $('instock_'+ordernumber).value;
				if(currentInstock > 0) {
					$('article_notification').setStyle('display', 'none');
				}
				else{
					$('article_notification').setStyle('display', 'block');
					try {
					$('variantOrdernumber').value=ordernumber;
					} catch(e){}
				}
			}
		}
	{/literal}
</script>
{/if}