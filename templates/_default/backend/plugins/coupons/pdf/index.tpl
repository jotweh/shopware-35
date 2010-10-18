<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<meta name="author" content=""/>
<meta name="copyright" content="" />

<title></title>
<style type="text/css">
* {
	margin: 0;
	padding: 0;
}
body {
	background: transparent url({link file='backend/plugins/coupons/pdf/img/voucher_rpt_bg.jpg'}) repeat 0 0;
	font-family: Helvetica, Arial;
	margin: 0;
	padding: 0;
}
#container {
	background-color: #fff;
	padding: 3px;
	width: 90%;
	border-radius: 15px;
	border: 2px solid #dd4800;
	text-align: center;
}
#border_between {
	background-color: #dd4800;
	border-radius: 10px;
	padding: 5px;
	color: #fff;
	padding-bottom: 7px;
}
#border_inner {
	border-radius: 10px;
	border: 0;
	border-bottom: 5px solid #fff;
	padding: 40px;
	margin-bottom: 7px;
	background-color: #fff;
	color: #000;
}
img#logo {
	margin: 30px 0 10px 0;
}
h1 {
	margin: 30px 0 0 0;
	padding: 0;
	text-transform: uppercase;
	color: #dd4800;
	font-size: 36px;
}
h1 small {
	margin: 10px 0;
	color: #7d7d7d;
}
h2#price {
	margin: 20px 0;
	padding: 0;
	font-size: 40px;
}
#urcode {
	margin: 50px auto 0 auto;
	background-color: #dbdbdb;
	border-radius: 5px;
	padding: 10px;
	width: 300px;
	text-align: left;
}
#urcode p {
	line-height: 20px;
	font-size: 14px;
	font-weight: bold;
	padding: 0;
	margin: 0;
}
#urcode code {
	font-size: 18px;
	line-height: 24px;
}
hr {
	margin: 60px 0;
	color: #dd4800;
}
#description {
	text-align: left;
	font-size: 14px;
	margin: 0 0 30px 0;
	color: #7F7F7F;
}
#domain {
	font-weight: bold;
	font-size: 16px;
	line-height: 30px;
	height: 50px;
}
</style>

<body>

<div id="container">
	<div id="border_between">
		<div id="border_inner">
			<header>
				<img src="{link file='frontend/_resources/images/logo.jpg'}" alt="Logo" id="logo" />
			</header>
			<hgroup>
				<h1>Einkaufsgutschein<br /><small>im Wert von:</small></h1>
				<h2 id="price">{$coupon.value|currency}</h2>
			</hgroup>
			<section>
				<div id="urcode" style="">
					<p>Ihr Gutscheincode:</p>
					<code>{$coupon.code}</code>
				</div>
			</section>
			<hr />
			<section>
				<p id="description">
				{if $coupon.valid_to != "0000-00-00"}
					{s name="PluginsBackendCouponsInfo" force}
					Der Gutschein ist gültig bis zum {$coupon.valid_to|date:date_long}
					{/s}
				{/if}
				{if $coupon.minimumcharge != 0}
					{s name="PluginsBackendCouponsCharge" force}
					Bitte beachten Sie den Mindestbestellwert von {$coupon.minimumcharge|currency}
					{/s}
				{/if}
				{s name="PluginsBackendCouponsText"}
				Sie können den Gutschein einfach während des Bestellprozesses im Warenkorb einlösen.
				Wir wünschen Ihnen viel Spaß bei dem Besuch unseres Shops. Bei Fragen oder Problemen erreichen Sie uns jederzeit
				unter folgenden Kontaktdaten: Musterfirma | Musterstraße | Musterort
				{/s}
				</p>
			</section>
		</div>
		<span id="domain">{$config->sBasePath}</span>
	</div>
</div>

</body>
</html>