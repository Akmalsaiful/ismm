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
		if(e.target.value) {
			document.getElementById('place_order').style.display = 'block';
		}
		else {
			showOmiseJs()
			document.getElementById('place_order').style.display = 'none';
		}
	});
});

document.querySelectorAll("input[name='payment_method']").forEach((input) => {
    input.addEventListener('change', () => {
		document.getElementById('place_order').style.display = 'block';
	});
});

function showOmiseJs() {
	OmiseCard.configure({
		element: document.getElementById('omise'),
		embedded: true,
		publicKey: "<?php echo '' ?>"
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
		onCreateTokenSuccess: (nonce) => {

		}
	});
}
</script>
