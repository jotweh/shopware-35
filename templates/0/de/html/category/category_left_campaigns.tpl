{if $sCategoryCampaigns}
	{foreach from=$sCategoryCampaigns item=sCampaign}
		{if $sCampaign.image}
			{if $sCampaign.link}
			<!-- Kampagne mit Link und Grafik -->
			<a href="{$sCampaign.link}" class="campaign_box" title="{$sCampaign.description}" target="{$sCampaign.linktarget}">
				<img src="{$sCampaign.image}" width=149  alt="{$sCampaign.description}" />
			</a>
			{else}
			<!-- Kampagne ohne Link mit Grafik -->
			<img src="{$sCampaign.image}" width=149 alt="{$sCampaign.description}" />
			{/if}
		
		{else}
		<!-- Kampagne ohne Grafik, Textausgabe -->
		<a href="{$sCampaign.link}" class="campaign_box" title="{$sCampaign.description}" target="{$sCampaign.linktarget}" style="margin: 5px 0 0 0;">
		{$sCampaign.description}
		</a>
		{/if}

	{/foreach}
{/if}