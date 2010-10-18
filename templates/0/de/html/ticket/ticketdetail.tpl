{if !$ticketDetails.id}
	<div class="allright2">
	{* sSnippet: ticket id not found *}{$sConfig.sSnippets.sTicketSysTicketIdNotFound}
	</div>
{else}
	{if $error!=""}
		<div class="error" style="margin-bottom:20px;margin-left:100px;margin-right:0;margin-top:0;width:552px;">{$error}</div>
	{/if}
	
	{if $accept!=""}
		<div class="accept_box_ticket" style="margin-bottom:20px;margin-left:100px;margin-right:0;margin-top:0;width:552px;">{$accept}</div>
	{/if}
	
	
	<h2 style="margin-bottom:15px;margin-top:10px;margin-left:100px;">{* sSnippet: details of the ticket *}{$sConfig.sSnippets.sTicketSysDetailsOfTicket} #{$ticketDetails.id}</h2>
	
	<label class="ticketdetail_lbl">{$ticketDetails.receipt} | {* sSnippet: your ticket enquiry *}{$sConfig.sSnippets.sTicketSysYourTicketEnquiry}</label>
	<div class="ticketdetail_txtbox">{$ticketDetails.message}</div>
	<div class="fixfloat" style="height:40px;"></div>
	
	{foreach from=$ticketHistoryDetails.data item=historyItem}
		
		<label class="ticketdetail_lbl">
		{$historyItem.date} - {$historyItem.time} | {if $historyItem.direction == "OUT"}
			Anwort vom Shop
		{else}
			{* sSnippet: your ticket answer *}{$sConfig.sSnippets.sTicketSysYourTicketAnswer}
		{/if}:</label>
		
		<div class="ticketdetail_txtbox">{$historyItem.message}</div>
		<div class="fixfloat" style="height:40px;"></div>
	{/foreach}
	
	
	{* ANSWER FORMULAR *}
	
	{if $ticketDetails.closed}
	 	<div class="cat_text" style="margin-bottom:20px;margin-left:100px;margin-right:0;margin-top:0;width:552px;">
		{* sSnippet: this ticket has been closed *}<h1>{$sConfig.sSnippets.sTicketSysTicketClosed}</h1>
		</div>
	{elseif !$ticketDetails.responsible}
		<div class="cat_text" style="margin-bottom:20px;margin-left:100px;margin-right:0;margin-top:0;width:552px;">
		{* sSnippet: we are working for your answer *}<h1>{$sConfig.sSnippets.sTicketSysWorkingForAnswer}</h1>
		</div>
	{else}
		<form action="" method="POST">
			<label class="ticketdetail_lbl">{* sSnippet: your ticket answer *}{$sConfig.sSnippets.sTicketSysYourTicketAnswer}:</label>
			<textarea name="sAnswer" class="ticketdetail_txtarea"></textarea>
			
			<input class="btn_high_r button" type="submit" value="Senden" name="sSubmit" style="margin-right:65px;margin-top:10px;"/>
		</form>
	{/if}
{/if}


	