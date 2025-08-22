<script type="text/javascript">
	//Setup Microsoft Clarity
	{literal}
	(function(c,l,a,r,i,t,y){
		c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};
		t=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/"+i;
		y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);
	{/literal}
	})(window, document, "clarity", "script", "{$ClarityID}");

	{literal}
	//Setup Consent for Matomo
	var _paq = window._paq = window._paq || [];
	_paq.push(['requireCookieConsent']);
	{/literal}
	{if isset($ExcludedQueryParams)}_paq.push(["setExcludedQueryParams", {$ExcludedQueryParams nofilter}]);{/if}

	{literal}
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

		//Activate Google Analytics with cookies
		gtag('consent', 'update', {
			'ad_user_data': 'granted',
			'ad_personalization': 'granted',
			'ad_storage': 'granted',
			'analytics_storage': 'granted'
		});
		
		//Activate Microsoft Clarity with cookies
		window.clarity('consentv2',{
			ad_storage: "granted",
			analytics_storage: "granted"
		});
		window.clarity('consent');
	}
</script>
