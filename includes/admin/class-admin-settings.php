<?php
/**
 * Class Give_Sofort_Admin_Settings
 *
 * @since 1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Give_Sofort_Admin_Settings' ) ) :

	class Give_Sofort_Admin_Settings {

		/**
		 * Instance.
		 *
		 * @since  1.0
		 * @access static
		 *
		 * @var object $instance
		 */
		static private $instance;

		/**
		 * Payment gateways ID
		 *
		 * @since 1.0
		 *
		 * @var string $gateways_id
		 */
		private $gateways_id = '';

		/**
		 * Payment gateway's label
		 *
		 * @since 1.0
		 *
		 * @var string $gateways_label
		 */
		private $gateway_label = '';

		/**
		 * Singleton pattern.
		 *
		 * @since  1.0
		 * @access private
		 *
		 * Give_Sofort_Admin_Settings constructor.
		 */
		private function __construct() {
			add_filter( 'give_payment_gateways', array( $this, 'register_gateway' ) );
		}

		/**
		 * Get instance.
		 *
		 * @since  1.0
		 * @access static
		 *
		 * @return static
		 */
		static function get_instance() {
			if ( null === static::$instance ) {
				self::$instance = new static();
			}

			return self::$instance;
		}

		/**
		 * Setup hooks
		 *
		 * @since  1.0
		 * @access public
		 */
		public function setup() {

			$this->gateways_id    = 'sofort';
			$this->gateway_label = __( 'Sofort&uuml;berweisung', 'give-sofort' );

			add_filter( 'give_get_settings_gateways', array( $this, 'add_settings' ) );
			add_filter( 'give_get_sections_gateways', array( $this, 'add_gateways_section' ) );

		}

		/**
		 * Registers the Sofort Payment Gateway.
		 *
		 * @param array $gateways Payment Gateways List.
		 *
		 * @since  1.0
		 * @access public
		 *
		 * @return mixed
		 */
		public function register_gateway( $gateways ) {

			$gateways[ $this->gateways_id ] = array(
				'admin_label'    => $this->gateway_label,
				'checkout_label' => $this->get_payment_method_label(),
			);

			return $gateways;
		}

		/**
		 * Adds the Sofort Settings to the Payment Gateways.
		 *
		 * @param array $settings Payment Gateway Settings.
		 *
		 * @since  1.0
		 * @access public
		 *
		 * @return array
		 */
		public function add_settings( $settings ) {

			if ( $this->gateways_id !== give_get_current_setting_section() ) {
				return $settings;
			}

			$sofort_settings = array(
				array(
					'id'   => $this->gateways_id,
					'type' => 'title',
				),
				array(
					'name'    => __( 'Payment Method Label', 'give-sofort' ),
					'id'      => 'sofort_checkout_label',
					'type'    => 'text',
					'default' => $this->get_payment_method_label(),
					'desc'    => __( 'Payment method label will be appear on frontend.', 'give-sofort' ),
				),
				array(
					'id'   => 'live_sofort_config_key',
					'name' => __( 'Live Configuration Key', 'give-sofort' ),
					'desc' => __( 'Enter your LIVE project Sofort configuration key', 'give-sofort' ),
					'type' => 'api_key',
				),
				array(
					'id'   => 'sandbox_sofort_config_key',
					'name' => __( 'Test Configuration Key', 'give-sofort' ),
					'desc' => __( 'Enter your TEST project Sofort configuration key', 'give-sofort' ),
					'type' => 'api_key',
				),
				array(
					'id'   => 'sofort_reason',
					'name' => __( 'Reason', 'give-sofort' ),
					'desc' => __( 'Enter your reason', 'give-sofort' ),
					'type' => 'text',
				),
				array(
					'name'    => __( 'Accept Billing Details', 'give-sofort' ),
					'desc'    => __( 'This option will enable the billing details section for Sofort which requires the donor\'s address to complete the donation. These fields are not required by Sofort.com to process the transaction, but you may have a need to collect the data.', 'give-sofort' ),
					'id'      => 'sofort_billing_details',
					'type'    => 'radio_inline',
					'default' => 'disabled',
					'options' => array(
						'enabled'  => __( 'Enabled', 'give-sofort' ),
						'disabled' => __( 'Disabled', 'give-sofort' ),
					),
				),
				array(
					'name'    => __( 'Trust Pending Payments', 'give-sofort' ),
					'desc'    => __( 'This option will set the donation as successful regardless of whether Sofort has reported it back to Give as successful or not. Accepting pending payments from sofort.com and complete the donation.', 'give-sofort' ),
					'id'      => 'sofort_trust_pending',
					'type'    => 'radio_inline',
					'default' => 'enabled',
					'options' => array(
						'enabled'  => __( 'Enabled', 'give-sofort' ),
						'disabled' => __( 'Disabled', 'give-sofort' ),
					),
				),
				array(
					'id'   => $this->gateways_id,
					'type' => 'sectionend',
				),
			);

			return $sofort_settings;
		}

		/**
		 * Add Sofort to payment gateway section
		 *
		 * @param array $section Payment Gateway Sections.
		 *
		 * @since  1.0
		 * @access public
		 *
		 * @return mixed
		 */
		public function add_gateways_section( $section ) {
			$section[ $this->gateways_id ] = __( 'Sofort', 'give-sofort' );

			return $section;
		}

		/**
		 * Get Payment Method Label.
		 *
		 * @since 1.0
		 *
		 * @return string
		 */
		public function get_payment_method_label() {
			return give_get_option( 'sofort_checkout_label', $this->gateway_label );
		}
	}

endif;

// Initialize settings.
Give_Sofort_Admin_Settings::get_instance()->setup();
