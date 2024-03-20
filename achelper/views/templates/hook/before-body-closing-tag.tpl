<script type="text/javascript">
	_paq.push(['trackPageView']);

	function ACHelperDeleteCookie(name, domain, path) {
		let cookieString = name + "=; expires=Thu, 01 Jan 1970 00:00:00 GMT";
		if (domain) {
			cookieString += "; domain=" + domain;
		}
		if (path) {
			cookieString += "; path=" + path;
		}
		document.cookie = cookieString;
	}

	if (typeof lgcookieslaw_cookie_values === 'object') {
		if (lgcookieslaw_cookie_values === null) {
			//Confirm Cookies consent status when the user first enters the page (lgcookieslaw_cookie_values is null at this time)

			// Select the element that controls the consent for Analytics Cookies
			const cookiesConsentToggle = document.getElementById('lgcookieslaw_slider_{$lgcookieslawID}');

			// Define a function to handle changes in the consent toggle for cookies
			const onConsentToggleChange = function(mutationsList, observer) {
				for (let mutation of mutationsList) {
					if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
						const currentClass = cookiesConsentToggle.className;
						// Check if the class attribute matches our target state
						if (currentClass.includes('lgcookieslaw-slider') && currentClass.includes('lgcookieslaw-slider-checked')) {
							allConsentGranted();
						}
						consentObserver.disconnect();
					}
				}
			}

			// Instantiate MutationObserver with a callback to monitor attribute changes
			const consentObserver = new MutationObserver(onConsentToggleChange);

			// Start observing the class attribute changes of the cookies consent toggle
			consentObserver.observe(cookiesConsentToggle, { attributes: true });
		}
		else {
			if (lgcookieslaw_cookie_values.lgcookieslaw_purpose_{$lgcookieslawID} === true) {
				allConsentGranted();
			}
			else {
				_paq.push(['forgetCookieConsentGiven']);
				ACHelperDeleteCookie('_ga_{$GMeasureID}', '.{$TopLevelDomain}', '/');
				ACHelperDeleteCookie('_ga', '.{$TopLevelDomain}', '/');
			}
		}
	}
</script>
