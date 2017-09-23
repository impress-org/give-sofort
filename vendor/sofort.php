<?php
/**
 *  sofort.php
 *
 * @description:
 * @copyright  : http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since      : 1.0.0
 * @created    : 9/11/2015
 */

namespace Sofort\SofortLib;

defined('ABSPATH') or die();

require __DIR__ . '/autoload.php';

/**
 * Get API Credentials
 *
 * @return mixed|void
 */
function get_api_credentials() {

    $creds = array();

    $sandbox = '';

    if ( give_is_test_mode() ) {
        $sandbox = 'sandbox_';
    }

    $config_key  = give_get_option( "sofort_{$sandbox}config_key" );

    $creds['config_key']  = ! empty( $config_key ) ? trim( $config_key ) : '';

    return apply_filters( 'give_sofort_get_api_creds', $creds );

}
/**
 * Get API Settings
 *
 * @return mixed|void
 */
function get_api_reasons() {

    $reasons = array();

    $sandbox = '';

    if ( give_is_test_mode() ) {
        $sandbox = 'sandbox_';
    }

    $reason  = give_get_option( "sofort_{$sandbox}reason" );

    $reasons['reason']  = ! empty( $reason ) ? trim( $reason ) : '';

    return apply_filters( 'give_sofort_get_api_reasons', $reasons );

}

/**
 * Process Sofort Payment.
 *
 * @since 1.0
 *
 * @param array $payment_data Payment data.
 *
 * @return void
 */
add_action ('give_gateway_sofort','give_process_sofort_payement');
function give_process_sofort_payement ( $payment_data) {


    // All the payment data to be sent to the gateway
    // Validate nonce.
    give_validate_nonce( $payment_data['gateway_nonce'], 'give-gateway' );
    $payment_id = give_create_payment( $payment_data );

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
        'status'          => 'pending',
        'gateway'         => 'sofort',
    );

    $payment_id = give_insert_payment( $payment_data );

    $config_key = $this->get_api_credentials();
    $reason     = $this->get_api_reasons();
    $successURL = give_send_to_success_page();
    $abortURL   = give_send_back_to_checkout();

    $Sofortueberweisung = new Sofortueberweisung($config_key);

    $Sofortueberweisung->setAmount( $payment_data['price'] );
    $Sofortueberweisung->setCurrencyCode( $payment_data['currency'] );
    $Sofortueberweisung->setReason( $reason, 'Verwendungszweck' );
    $Sofortueberweisung->setSuccessUrl( $successURL, true ); // i.e. http://my.shop/order/success
    $Sofortueberweisung->setAbortUrl($abortURL );
    // Check payment.
    if ( empty( $payment_id ) ) {
        // Record the error.
        give_record_gateway_error( __( 'Payment Error', 'give' ), sprintf( /* translators: %s: payment data */
            __( 'Payment creation failed before sending donor to Sofort. Payment data: %s', 'give' ), json_encode( $payment_data ) ), $payment_id );
        // Problems? Send back.
        give_send_back_to_checkout( '?payment-mode=' . $payment_data['post_data']['give-gateway'] );
    }

    // Try the Sofort. sale:
    try {

        $Sofortueberweisung->sendRequest();


        if($Sofortueberweisung->isError()) {
            // SOFORT-API didn't accept the data
            // Handle API response errors
            //give_sofort_handle_transaction_errors( $result, $payment_id );
            echo $Sofortueberweisung->getError();
        } else {
            // get unique transaction-ID useful for check payment status

            give_update_payment_status( $payment_id, 'complete' );
            // Update transaction id.
            give_set_payment_transaction_id( $payment_id, trim( $Sofortueberweisung->getTransactionId() ) );
            give_send_to_success_page();
            $payment_data['purchase_key'] = $Sofortueberweisung->getTransactionId();
            // buyer must be redirected to $paymentUrl else payment cannot be successfully completed!
            $paymentUrl = $Sofortueberweisung->getPaymentUrl();
            header('Location: '.$paymentUrl);
        }



    } catch ( Exception $e ) {

        // Log all exceptions.
        give_log_sofort_error( $e );

    }
    // Update payment status.
    //give_update_payment_status( $payment, 'publish' );



    // Add iATS payment meta.
    update_post_meta( $payment, '_sofort_donation_response', $Sofortueberweisung );
    // Send to success page.
    //give_send_to_success_page();

    return true;
}


function give_log_sofort_error() {

    $generic_message = esc_html__( 'An error occurred during processing of the donation.', 'give-sofort' );

    switch ( $exception ) {

        case ( $exception instanceof $Sofortueberweisung->getError ):
            $error_message = $generic_message . ' ' . sprintf( esc_html__( 'Details: %s', 'give-sofort' ), $exception->getErrorsString() );
            break;
        default:
            $error_message = $generic_message . ' ' . esc_html__( 'Please try again.', 'give-sofort' );

    }

    // Log with DB.
    give_record_gateway_error( esc_html__( 'Sofort. Error', 'give-sofort' ), sprintf( __( 'An error happened while processing a donation.<br><br>Details: %1$s <br><br>Code: %2$s', 'give-sofort' ), $exception->getMessage(), $exception->getCode() ) );

    // Display error for user.
    give_set_error( 'sofort_error', $error_message );

    // Send em' on back
    give_send_back_to_checkout( '?payment-mode=sofort' );
}


/**
 * Add an errors div
 *
 * @access      public
 * @since       1.0
 * @return      void
 */
function errors_div() {
    echo '<div id="give-sofort-payment-errors"></div>';
}



