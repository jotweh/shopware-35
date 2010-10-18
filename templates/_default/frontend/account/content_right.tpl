<h2 class="headingbox largesize">{se name="AccountHeaderNavigation"}{/se}</h2>
<div class="adminbox">
	<ul>
		
		{* Overview *}
		<li>
			<a href="{url controller='account'}">
				{se name="AccountLinkOverview"}{/se}
			</a>
		</li>
		
		{* My orders *}
		<li>
			<a href="{url controller='account' action='orders'}">
				{se name="AccountLinkPreviousOrders"}{/se}
			</a>
		</li>
		
		{* My esd articles *}
		<li>
			<a href="{url controller='account' action='downloads'}">
				{se name="AccountLinkDownloads"}{/se}
			</a>
		</li>
		
		{* Change billing address *}
		<li>
			<a href="{url controller='account' action='billing'}">
				{se name="AccountLinkBillingAddress"}{/se}
			</a>
		</li>
		
		{* Change shipping address *}
		<li>
			<a href="{url controller='account' action='shipping'}">
				{se name="AccountLinkShippingAddress"}{/se}
			</a>
		</li>
		
		{* Change payment method *}
		<li>
			<a href="{url controller='account' action='payment'}">
				{se name="AccountLinkPayment"}{/se}
			</a>
		</li>		
		
		{* Supportmanagement *}
		{if $sTicketLicensed}
			<li>
				<a href="{url controller='ticket' action='listing'}">
					{se name="sTicketSysSupportManagement"}{/se}
				</a>
			</li>
			
			<li class="sub"><a href="{url controller='ticket' action='request'}">{s name='TicketLinkSupport'}{/s}</a></li>		
		{/if}
		
		{* Leaflet *}
		<li>
			<a href="{url controller='note'}">
				{se name="AccountLinkNotepad"}{/se}
			</a>
		</li>
		
		{* Logout *}
		<li class="last">
			<a href="{url controller='account' action='logout'}" class="logout">
				{se name="AccountLinkLogout"}{/se}
			</a>
		</li>
	</ul>	
</div>