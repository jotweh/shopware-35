<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<meta name="author" content=""/>
<meta name="copyright" content="" />

<title></title>
<style type="text/css">
{$css}
</style>
<!--
<link rel="stylesheet" href="{$sPath}css/base.css" type="text/css" />
{include file='footer.tpl'}
-->
<body>
{foreach item=Details key=kDetails from=$sBillingData.details}
{if $kDetails!=1}
<!--NewPage-->
{/if}
<div id="content">
{include file='header.tpl'}
{if $typ == ''}
{include file='content.tpl'}
{elseif $typ == 0}
{include file='content.tpl'}
{else}
{assign var=foo value="content_"}
{include file=$foo|cat:$typ|cat:".tpl"}
{/if}
{if $kDetails == $sBillingData.pages}
{include file='footer.tpl'}
{/if}
</div>
{/foreach}
</body>
</html>