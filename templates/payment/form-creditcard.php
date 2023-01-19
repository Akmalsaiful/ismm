<style>
#omise {
	margin: 0 auto;
	width: 100%;
	height: 550px;
	background: white;
	padding: 10px;
	border-radius: 10px;
	border: 1px solid grey;
}
.omise-new-card-form {
	padding: 0px;
	border: none;
}
</style>
<form>
<form>
<div id="omise"></div>
<script>
	document.querySelectorAll("input[name='card_id']").forEach((input) => {
		input.addEventListener('change', (e) => {
			let display = 'block';

			if(!e.target.value) {
				showOmiseJs();
				display = 'none';
			}

			document.getElementById('place_order').style.display = display;
		});
	});

	document.querySelectorAll("input[name='payment_method']").forEach((input) => {
		input.addEventListener('change', () => {
			showOmiseJs()
			document.getElementById('place_order').style.display = 'none';
		});
	});

	function showOmiseJs() {
		OmiseCard.configure({
			element: document.getElementById('omise'),
			embedded: true,
			publicKey: "pkey_5t7f2zoyuaqxdbuupnf"
		});

		OmiseCard.open({
			backgroundColor: 'white',
			amount: 12345,
			currency: "THB",
			image: '',
			frameLabel: "",
			frameDescription: "",
			style: {
				fontFamily: 'Circular,Arial,sans-serif',
				closeButton: {
					visible: false,
				},
				body: {
					width: '100%',
					padding: {
						desktop: '10px',
						mobile: '10px',
					},
				},
				submitButton: {
					backgroundColor: '#192c66',
					textColor: 'white',
				},
			},
			onCreateTokenSuccess: (token) => {
				creditCardPaymentHandler(token);
			}
		});
	}

	function creditCardPaymentHandler(token) {
		if (!token) {
			return false;
		}

		jQuery(document).ready(function($) {
			const $form = $('form.checkout, form#order_review');
			const rememberCard = document.getElementById('omise_save_customer_card');
			$form.append('<input type="hidden" name="omise_save_customer_card" id="omise_save_customer_card" value="' + rememberCard + '" />');
			$form.append('<input type="hidden" class="omise_token" name="omise_token" value="' + token + '"/>');
			console.log($form);
			$form.submit();
		});
	}
</script>
