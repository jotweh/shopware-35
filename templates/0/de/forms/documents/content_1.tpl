
<div id="content_middle">
      <table cellpadding="0" width="100%">
        <tbody valign="top">
        <tr class="head">
          <td align="left" width="5%">
           <b>Pos.</b>
          </td>
          <td align="left" width="10%">
           <b>Art-Nr.</b>
          </td>
          <td align="left">
           <b>Bezeichnung</b>
          </td>
          <td align="right" width="5%">
           <b>Anz.</b>
          </td>
        </tr>
       {foreach item=Entry key=kEntry from=$Details}
       
	   {if $kEntry == 1}
       <tr><td colspan="7" class="line2"></td></tr>
       {else}
        <tr><td colspan="7" class="line"></td></tr>
       {/if}
        <tr>
          <td valign="top">{$kEntry}</td>
          <td valign="top">{$Entry.articleordernumber}</td>
          <td valign="top">{$Entry.name}</td>
          <td valign="top" align="right">{$Entry.quantity}</td>
        </tr>
        {/foreach}
  </tbody>
  </table>
  <div id="under">
  </div>
</div>