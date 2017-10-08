<?php
/**
 * @package     WordPress
 * @subpackage  Give
 * @author      Birgit Olzem
 * @copyright   2017, Birgit Olzem
 * @link        http://coachbirgit.com
 * @license     http://www.opensource.org/licenses/gpl-2.0.php GPL License
 */
/** @define "GIVE_SOFORT_DIR" "/Users/coachbirgit/PhpstormProjects/give-sofort" */

// No direct access is allowed
if( ! defined( 'ABSPATH' ) ) exit;

require GIVE_SOFORT_DIR . 'vendor/autoload.php';


if( ! class_exists( 'Give_Sofort_Gateway_Processor' ) ) :

	/**
	 * Handles the actual gateway (SOFORT Banking)
	 *
	 * Adds admin options, frontend fields
	 * and handles payment processing
	 *
	 * @since   1.0
	 */
	class Give_Sofort_Gateway_Processor {

		//public $payment_id;

		/**
		 * Initialize the gateway
		 *
		 * @since   1.0
		 * @uses    apply_filters()
		 */
		public function __construct()
		{
			// filters & actions

			add_action( 'give_sofort_form', 'give_sofort_payment_form' );

			add_action( 'give_gateway_sofort', array( $this, 'give_process_sofort_payment' ), 10, 1 );
			//add_action( 'give_gateway_sofort', 'give_process_sofort_payment' );
			//add_filter( 'give_receipt_status_notice', 'give_sofort_donation_receipt_status_notice', 10, 2 );

		}


		function give_sofort_payment_form( $form_id ) {
			// Get sofort payment instruction.
			$sofort_instructions = give_get_sofort_payment_instruction( $form_id, true );
			ob_start();
			/**
			 * Fires before the offline info fields.
			 *
			 * @since 1.0
			 *
			 * @param int $form_id Give form id.
			 */
			do_action( 'give_before_sofort_info_fields', $form_id );
			?>
			<fieldset id="give_sofort_payment_info">
				<?php echo stripslashes( $offline_instructions ); ?>
			</fieldset>
			<?php
			/**
			 * Fires after the offline info fields.
			 *
			 * @since 1.0
			 *
			 * @param int $form_id Give form id.
			 */
			do_action( 'give_after_sofort_info_fields', $form_id );
			echo ob_get_clean();
		}

		/**
		 * Sofort. Payments
		 *
		 * @param $payment_data
		 */
		public function give_process_sofort_payment( $purchase_data ) {
			//global $give_options;
			$errors = give_get_errors();

			// No errors: Continue with payment processing
			if ( ! $errors ) {



				$payment_data = array(
					'price'           => $purchase_data['price'],
					'give_form_title' => $purchase_data['post_data']['give-form-title'],
					'give_form_id'    => intval( $purchase_data['post_data']['give-form-id'] ),
					'give_price_id'   => isset( $purchase_data['post_data']['give-price-id'] ) ? $purchase_data['post_data']['give-price-id'] : '',
					'date'            => $purchase_data['date'],
					'user_email'      => $purchase_data['user_email'],
					'purchase_key'    => $purchase_data['purchase_key'],
					'currency'        => give_get_currency(),
					'user_info'       => $purchase_data['user_info'],
					'status'          => 'pending', /** THIS MUST BE SET TO PENDING TO AVOID PHP WARNINGS */
					'gateway'         => 'sofort' /** USE YOUR SLUG AGAIN HERE */
				);

				$payment_id = give_insert_payment( $payment_data );

				/**
				 * Here you will reach out to whatever payment processor you are building for and record a successful payment
				 *
				 * If it's not correct, make $payment false and attach errors
				 */

				$payment_amount = give_get_payment_amount( $payment_id );
				$api_amount = (float) number_format( $payment_amount, 2, '.', '' );

				//$api_notification_url = give_send_back_to_checkout( '?payment-mode=' . $purchase_data['post_data']['give-gateway'] );

				$api_config_key =  give_get_option('sofort_config_key');
				$api_reason1 = give_get_option('sofort_reason');
				$api_reason2 = $purchase_data['purchase_key'];
				$api_currency = give_get_currency();
				$api_success_page = give_get_success_page_uri();
				$api_abort_page = give_get_failed_transaction_uri();
				$api_notification_url = give_get_success_page_uri();


				$api = new Sofort\SofortLib\Sofortueberweisung( $api_config_key );

				$api->setAmount( $api_amount );

				$api->setCurrencyCode($api_currency);
				$api->setReason($api_reason1, $api_reason2);

				$api->setUserVariable( $payment_id );

				$api->setSuccessUrl( $api_success_page , true); // i.e. http://my.shop/order/success
				$api->setAbortUrl( $api_abort_page );
				$api->setNotificationUrl( $api_notification_url );

				$api->sendRequest();

				if($api->isError()) {
					// SOFORT-API didn't accept the data
					echo $api->getError();
				} else {


                // buyer must be redirected to $paymentUrl else payment cannot be successfully completed!
                $paymentUrl = $api->getPaymentUrl();
                header('Location: '.$paymentUrl);

                $api = new Sofort\SofortLib\Notification();

                // get unique transaction-ID useful for check payment status
                $txn_id = $api->getTransactionId();

                $data = new SofortLib_TransactionData( $api_config_key );
                $data->setTransaction( $txn_id );
                $data->sendRequest();

                $reason = $data->getStatusReason();
                $order_id = $data->getUserVariable( 0 );
                $first_name = $data->getUserVariable( 1 );
                $last_name = $data->getUserVariable( 2 );
                $email = $data->getUserVariable( 3 );
                $order_key = $data->getUserVariable( 4 );
                $status = $data->getStatus();

                // record the payment which is super important so you have the proper records in the Give administration
                $payment = give_insert_payment( $payment_data );

                if ( $payment && $status == 'received' ) {
                    give_update_payment_status( $payment, 'publish' ); /** This line will finalize the donation, you can run some other verification function if you want before setting to publish */
                    give_send_to_success_page();
                } else {

                    // Log with DB.
                    give_record_gateway_error( esc_html__( 'Sofort. Error', 'give-sofort' ), sprintf( __( 'An error happened while processing a donation.<br><br>Details: %1$s <br><br>Code: %2$s', 'give-sofort' ), $exception->getMessage(), $exception->getCode() ) );

                    // Display error for user.
                    give_set_error( 'sofort_error', $error_message );

                    // if errors are present, send the user back to the donation form so they can be corrected
                    give_send_back_to_checkout( '?payment-mode=' . $purchase_data['post_data']['give-gateway'] );
                }

            }
        } // end if $errors

        // Return api if set.
        if ( isset( $api ) ) {
            return $api;
        } else {
            return false;
        }

    }

    function give_log_sofort_error( $exception ) {

        $generic_message = esc_html__( 'An error occurred during processing of the donation.', 'give-sofort' );
        $error_message = $generic_message . ' ' . esc_html__( 'Please try again.', 'give-sofort' );
    }

    /**
     * Set notice for offline donation.
     *
     * @since 1.7
     *
     * @param string $notice
     * @param int    $id
     *
     * @return string
     */
    function give_sofort_donation_receipt_status_notice( $notice, $id ) {
        $payment = new Give_Payment( $id );

        if ( 'sofort' !== $payment->gateway || $payment->is_completed() ) {
            return $notice;
        }

        return Give()->notices->print_frontend_notice( __( 'Payment Pending: Please follow the instructions below to complete your donation.', 'give' ), false, 'warning' );
    }
        

    } // give_process_sofort_payment()

	return new Give_Sofort_Gateway_Processor();

endif;
