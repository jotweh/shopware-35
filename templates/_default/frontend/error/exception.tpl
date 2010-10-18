<h2>{se name="ExceptionHeader"}Ups! Ein Fehler ist aufgetreten!{/se}</h2>

{if $exception}
	<p>
		{se name="ExceptionText"}Die nachfolgenden Hinweise sollten Ihnen weiterhelfen.{/se}
	</p>

	<h3>{$exception->getMessage()} in {$error_file} on line {$exception->getLine()}</h3>
	
	<h3>Stack trace:</h3>
	<div style="overflow:auto;">
	<pre>{$error_trace|escape}</pre>
	</div>
{else}
	{se name="InformText"}Wir wurden bereits über das Problem informiert und arbeiten an einer Lösung, bitte versuchen Sie es in Kürze erneut.{/se}
{/if}
<div class="doublespace">&nbsp;</div>