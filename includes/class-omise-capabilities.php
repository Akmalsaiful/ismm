<?php
defined( 'ABSPATH' ) || exit;

/**
 * @since 3.4
 */
class Omise_Capabilities {
	/**
	 * @var self
	 */
	protected static $the_instance = null;

	/**
	 * @var \OmiseCapabilities
	 */
	protected $capabilities;

	/**
	 * @return self  The instance of Omise_Capabilities
	 */
	public static function retrieve() {
		if ( ! self::$the_instance ) {
			self::$the_instance = new self();
			self::$the_instance->capabilities = OmiseCapabilities::retrieve();
		}

		return self::$the_instance;
	}
	
	/**
	 * Retrieves details of installment payment backends from capabilities.
	 *
	 * @return string
	 */
	public function getInstallmentBackends($currency = '', $amount = null)
	{
		$params   = array();
		$params[] = $this->capabilities->backendFilter['type']('installment');

		if ($currency) {
			$params[] = $this->capabilities->backendFilter['currency']($currency);
		}
		if (!is_null($amount)) {
			$params[] = $this->capabilities->backendFilter['chargeAmount']($amount);
		}

		return $this->capabilities->getBackends($params);
	}
}
