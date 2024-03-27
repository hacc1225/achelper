<script type="text/javascript">
	{literal}
	//Setup Consent for Matomo
	var _paq = window._paq = window._paq || [];
	_paq.push(['requireCookieConsent']);

	//Setup Consent for Google Analytics
	window.dataLayer = window.dataLayer || [];
	function gtag(){dataLayer.push(arguments);}
	gtag('consent', 'default', {
		'ad_storage': 'denied',
		'ad_user_data': 'denied',
		'ad_personalization': 'denied',
		'analytics_storage': 'denied'
	});
	{/literal}
	{if $GA_URL_Passthrough}gtag('set', 'url_passthrough', true);{/if}
	{if $GA_Ads_Data_Redaction}gtag('set', 'ads_data_redaction', true);{/if}

	//Activate tracking code
	function allConsentGranted() {
		//Activate Matomo with cookies
		_paq.push(['rememberCookieConsentGiven']);

		//Activate Google Analytics
		gtag('consent', 'update', {
			'ad_user_data': 'granted',
			'ad_personalization': 'granted',
			'ad_storage': 'granted',
			'analytics_storage': 'granted'
		});
	}
</script>
