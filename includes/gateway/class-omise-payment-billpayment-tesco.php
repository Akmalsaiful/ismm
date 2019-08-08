<?php
defined( 'ABSPATH' ) or die( 'No direct script access allowed.' );

function register_omise_billpayment_tesco() {
	require_once dirname( __FILE__ ) . '/class-omise-payment.php';

	if ( ! class_exists( 'WC_Payment_Gateway' ) || class_exists( 'Omise_Payment_Billpayment_Tesco' ) ) {
		return;
	}

	/**
	 * @since 3.7
	 */
	class Omise_Payment_Billpayment_Tesco extends Omise_Payment {
		public function __construct() {
			parent::__construct();

			$this->id                 = 'omise_billpayment_tesco';
			$this->has_fields         = false;
			$this->method_title       = __( 'Omise Bill Payment: Tesco', 'omise' );
			$this->method_description = wp_kses(
				__( 'Accept payments through <strong>Tesco Bill Payment</strong> via Omise payment gateway.', 'omise' ),
				array( 'strong' => array() )
			);

			$this->init_form_fields();
			$this->init_settings();

			$this->title       = $this->get_option( 'title' );
			$this->description = $this->get_option( 'description' );

			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
			add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'display_barcode' ) );
		}

		/**
		 * @see WC_Settings_API::init_form_fields()
		 * @see woocommerce/includes/abstracts/abstract-wc-settings-api.php
		 */
		public function init_form_fields() {
			$this->form_fields = array(
				'enabled' => array(
					'title'   => __( 'Enable/Disable', 'omise' ),
					'type'    => 'checkbox',
					'label'   => __( 'Enable Omise Tesco Bill Payment', 'omise' ),
					'default' => 'no'
				),

				'title' => array(
					'title'       => __( 'Title', 'omise' ),
					'type'        => 'text',
					'description' => __( 'This controls the title the user sees during checkout.', 'omise' ),
					'default'     => __( 'Bill Payment: Tesco', 'omise' ),
				),

				'description' => array(
					'title'       => __( 'Description', 'omise' ),
					'type'        => 'textarea',
					'description' => __( 'This controls the description the user sees during checkout.', 'omise' )
				),
			);
		}

		/**
		 * @inheritdoc
		 */
		public function charge( $order_id, $order ) {
			$total      = $order->get_total();
			$currency   = $order->get_order_currency();
			$return_uri = add_query_arg(
				array( 'wc-api' => 'omise_billpayment_tesco_callback', 'order_id' => $order_id ), home_url()
			);
			$metadata   = array_merge(
				apply_filters( 'omise_charge_params_metadata', array(), $order ),
				array( 'order_id' => $order_id ) // override order_id as a reference for webhook handlers.
			);

			return OmiseCharge::create( array(
				'amount'      => Omise_Money::to_subunit( $total, $currency ),
				'currency'    => $currency,
				'description' => apply_filters( 'omise_charge_params_description', 'WooCommerce Order id ' . $order_id, $order ),
				'source'      => array( 'type' => 'bill_payment_tesco_lotus' ),
				'return_uri'  => $return_uri,
				'metadata'    => $metadata
			) );
		}

		/**
		 * @inheritdoc
		 */
		public function result( $order_id, $order, $charge ) {
			if ( 'failed' == $charge['status'] ) {
				return $this->payment_failed( $charge['failure_message'] . ' (code: ' . $charge['failure_code'] . ')' );
			}

			if ( 'pending' == $charge['status'] ) {
				return array(
					'result'   => 'success',
					'redirect' => $this->get_return_url( $order )
				);
			}

			return $this->payment_failed(
				sprintf(
					__( 'Please feel free to try submitting your order again, or contact our support team if you have any questions (Your temporary order id is \'%s\')', 'omise' ),
					$order_id
				)
			);
		}

		/**
		 * @param  int $order_id
		 */
		public function display_barcode( $order_id ) {
			if ( ! $order = $this->load_order( $order_id ) ) {
				return;
			}

			$charge_id = $this->get_charge_id_from_order();
			$charge    = OmiseCharge::retrieve( $charge_id );

			$amount  = $charge['amount'];
			$barcode = $charge['source']['references']['barcode'];
			$tax_id  = $charge['source']['references']['omise_tax_id'];
			$ref_1   = $charge['source']['references']['reference_number_1'];
			$ref_2   = $charge['source']['references']['reference_number_2'];
			?>

			<div class="omise omise-billpayment-tesco-details">
				<p><?php echo __( 'Use this barcode to pay at Tesco Lotus.', 'omise' ); ?></p>
				<div class="omise-billpayment-tesco-barcode">
					<img src="<?php echo $barcode; ?>" title="Omise Tesco Bill Payment Barcode" alt="Omise Tesco Bill Payment Barcode">
				</div>
				<small class="omise-billpayment-tesco-reference-number">|&nbsp;&nbsp;&nbsp; <?php echo $tax_id; ?> &nbsp;&nbsp;&nbsp; 00 &nbsp;&nbsp;&nbsp; <?php echo $ref_1; ?> &nbsp;&nbsp;&nbsp; <?php echo $ref_2; ?> &nbsp;&nbsp;&nbsp; <?php echo $amount; ?></small>
			</div>
			<?php
		}
	}

	if ( ! function_exists( 'add_omise_billpayment_tesco' ) ) {
		/**
		 * @param  array $methods
		 *
		 * @return array
		 */
		function add_omise_billpayment_tesco( $methods ) {
			$methods[] = 'Omise_Payment_Billpayment_Tesco';
			return $methods;
		}

		add_filter( 'woocommerce_payment_gateways', 'add_omise_billpayment_tesco' );
	}
}