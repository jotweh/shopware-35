
<h2 style="margin-bottom:15px;margin-left:100px;margin-top:10px;">{* sSnippet: support management *}{$sConfig.sSnippets.sTicketSysSupportManagement}</h2>

{* load ticketoverview *}
<div class="ticketoverview_container">
	<div class="ticketoverview_header">
		<div class="ticketoverview_column col1">{* sSnippet: ticket from *}{$sConfig.sSnippets.sTicketSysTicketFrom}</div>
		<div class="ticketoverview_column col2">{* sSnippet: ticket id *}{$sConfig.sSnippets.sTicketSysTicketId}</div>
		<div class="ticketoverview_column col3">{* sSnippet: ticket type *}{$sConfig.sSnippets.sTicketSysTicketType}</div>
		<div class="ticketoverview_column col4">{* sSnippet: ticket status *}{$sConfig.sSnippets.sTicketSysTicketStatus}</div>
		<div class="ticketoverview_column collast">&nbsp;</div>
	</div>
	<div class="ticketoverview_content">
	
	{foreach from=$ticketStore.data item=ticketItem}
		{cycle assign=column_color values='#F9F9F9,#FFFFFF'}
		<div class="ticketoverview_column col1" style="background-color:{$column_color};">{$ticketItem.receipt}</div>
		<div class="ticketoverview_column col2" style="background-color:{$column_color};">#{$ticketItem.id}</div>
		<div class="ticketoverview_column col3" style="background-color:{$column_color};">{$ticketItem.ticket_type}</div>
		<div class="ticketoverview_column col4" style="background-color:{$column_color}; {if $ticketItem.status_color != '0'}color:{$ticketItem.status_color}{/if}">{$ticketItem.status}</div>
		<div class="ticketoverview_column collast" style="background-color:{$column_color};">
			<a href="{$sBasefile}?sViewport=ticketview&sAction=detail&tid={$ticketItem.id}&sUseSSL=1">{* sSnippet: show details *}{$sConfig.sSnippets.sTicketSysShowDetails}</a>
		</div>
		
		<div class="fixfloat" style="border-bottom:1px solid #DFDFDF;"></div>
	{/foreach}
		
	</div>
</div>	

<div class="fixfloat"></div>