(function ($) {
	const $form = $('form.checkout, form#order_review');

	function hideError() {
		$(".woocommerce-error").remove();
	}

	function showError(message) {
		if (!message) {
			return;
		}

		$(".woocommerce-error, input.omise_token").remove();
		let $ulError = $("<ul>").addClass("woocommerce-error");

		if ($.isArray(message)) {
			$.each(message, function(i,v) {
				$ulError.append($("<li>" + v + "</li>"));
			})
		} else {
			$ulError.html("<li>" + message + "</li>");
		}

		$form.prepend($ulError);
		$("html, body").animate({ scrollTop:0 },"slow");
	}

	function googlePay() {
		window.addEventListener('loadpaymentdata', event => {
			document.getElementById('place_order').style.display = 'inline-block';
			const params = {
				method: 'googlepay',
				data: JSON.stringify(JSON.parse(event.detail.paymentMethodData.tokenizationData.token))
			}
			const billingAddress = (event.detail.paymentMethodData.info?.billingAddress);
			if (billingAddress) {
				Object.assign(params, {
					billing_name: billingAddress.name,
					billing_city: billingAddress.locality,
					billing_country: billingAddress.countryCode,
					billing_postal_code: billingAddress.postalCode,
					billing_state: billingAddress.administrativeArea,
					billing_street1: billingAddress.address1,
					billing_street2: [billingAddress.address2, billingAddress.address3].filter(s => s).join(' '),
					billing_phone_number: billingAddress.phoneNumber,
				});
			}

			hideError();

			Omise.setPublicKey(omise_params.key);
			Omise.createToken('tokenization', params, (statusCode, response) => {
				if (statusCode == 200) {
					document.getElementById('googlepay-button-container').style.display = 'none';
					document.getElementById('googlepay-text').innerHTML = 'Card is successfully selected. Please proceed to \'Place order\'.';
					document.getElementById('googlepay-text').classList.add('googlepay-selected');

					const form = document.querySelector('form.checkout');
					const input = document.createElement('input');
					input.setAttribute('type', 'hidden');
					input.setAttribute('class', 'omise_token');
					input.setAttribute('name', 'omise_token');
					input.setAttribute('value', response.id);
					form.appendChild(input);
				} else {
					handleTokensApiError(response)
				}
			});
		});
	}

	function handleTokensApiError(response) {
		if (response.object && 'error' === response.object && 'invalid_card' === response.code) {
			showError(omise_params.invalid_card + "<br/>" + mapApiResponseToTranslatedTest(response.message));
		} else if (response.message) {
			showError(omise_params.cannot_create_token + "<br/>" + mapApiResponseToTranslatedTest(response.message));
		} else if (response.responseJSON && response.responseJSON.message) {
			showError(omise_params.cannot_create_token + "<br/>" + mapApiResponseToTranslatedTest(response.responseJSON.message));
		} else if (response.status == 0) {
			showError(omise_params.cannot_create_token + "<br/>" + omise_params.cannot_connect_api + omise_params.retry_checkout);
		} else {
			showError(omise_params.cannot_create_token + "<br/>" + omise_params.retry_checkout);
		}
		$form.unblock();
	}

	/**
	 * Return a translated localized text if found else return the same text.
	 *
	 * @param {string} message
	 * @returns string
	 */
	function mapApiResponseToTranslatedTest(message)
	{
		return omise_params[message] ? omise_params[message] : message;
	}

	$(function () {
		$('body').on('checkout_error', function () {
			$('.omise_token').remove();
		});

		$('form.checkout').unbind('checkout_place_order_omise');
		$('form.checkout').on('checkout_place_order_omise', function () {
			// In the parent page
			window.addEventListener('message', event => {
				if(!event.data) {
					return;
				}

				return creditCardPaymentHandler(event.data);
			});
		});

		/* Pay Page Form */
		$('form#order_review').on('submit', function () {
			// In the parent page
			window.addEventListener('message', event => {
				if(!event.data) {
					return;
				}

				return creditCardPaymentHandler(event.data);
			});
		});

		/* Both Forms */
		$('form.checkout, form#order_review').on('change', '#omise_cc_form input', function() {
			$('.omise_token').remove();
		});

		googlePay();

		// In the parent page
		window.addEventListener('message', event => {
			if(!event.data) {
				return;
			}

			return creditCardPaymentHandler(event.data);
		});
	})

	function creditCardPaymentHandler(tokenData) {
		const token = JSON.parse(tokenData);

		if (token.data) {
			const rememberCard = document.getElementById('omise_save_customer_card');
			$form.append('<input type="hidden" name="omise_save_customer_card" id="omise_save_customer_card" value="' + rememberCard + '" />');
			$form.append('<input type="hidden" class="omise_token" name="omise_token" value="' + token.data + '"/>');
			console.log($form);
			$form.submit();
		} else {
			// log error
			/**
			handleTokensApiError({
				object: 'error',
				
			});
			 */
		};

		return false;
	}
})(jQuery)
