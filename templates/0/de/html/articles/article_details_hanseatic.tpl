{if $sArticle.sFinance}
<div class="deliverable3">
{$sConfig.sSnippets.sArticlefrom} {$sConfig.sCURRENCYHTML} {$sArticle.sFinance.rateAmount} *<br>
{$sConfig.sSnippets.sArticleduration}: {$sArticle.sFinance.rateMonth} {$sConfig.sSnippets.sArticlemonth}<br>
<br>
{$sConfig.sSnippets.sHanseaticRateNotice}<br>
<br></div>
{/if}
