<?php
/**
 *  give-sofort-settings.php
 *
 * @description:
 * @copyright  : http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since      : 1.0.0
 * @created    : 9/11/2015
 */


/**
 * Register the gateway settings
 *
 * @access      public
 * @since       1.0
 * @return      array
 */
function give_register_sofort_settings( $settings ) {

    $sofort_settings = apply_filters( 'give_gateway_sofort_settings', array(
        array(
            'name' => __( 'Sofort Settings', 'give-sofort' ),
            'desc' => '<hr>',
            'id'   => 'give_title_sofort',
            'type' => 'give_title'
        ),
        array(
            'id'   => 'sofort_config_key',
            'name' => __( 'Live config key', 'give-sofort' ),
            'desc' => __( 'Enter your live Sofort config key', 'give-sofort' ),
            'type' => 'text',
        ),
        array(
            'id'   => 'sofort_reason',
            'name' => __( 'Reason', 'give-sofort' ),
            'desc' => __( 'Enter you reason', 'give-sofort' ),
            'type' => 'text',
        ),
        array(
            'id'   => 'sofort_sandbox_config_key',
            'name' => __( 'Sandbox Config Key', 'give-sofort' ),
            'desc' => __( 'Enter your stage account Sofort Config Key', 'give-sofort' ),
            'type' => 'text',
        ),
        array(
            'id'   => 'sofort_sandbox_reason',
            'name' => __( 'Sandbox Reason', 'give-sofort' ),
            'desc' => __( 'Enter your stage account Sofort reason', 'give-sofort' ),
            'type' => 'text',
        ),
        array(
            'name'    => __( 'Billing Details', 'give' ),
            'desc'    => __( 'This option will enable the billing details section for Sofort. which requires the donor\'s address to complete the donation. These fields are not required by PayPal to process the transaction, but you may have a need to collect the data.', 'give' ),
            'id'      => 'sofort_billing_details',
            'type'    => 'radio_inline',
            'default' => 'disabled',
            'options' => array(
                'enabled'  => __( 'Enabled', 'give' ),
                'disabled' => __( 'Disabled', 'give' ),
            )
        ),
       ) );

    return array_merge( $settings, $sofort_settings );
}

add_filter( 'give_settings_gateways', 'give_register_sofort_settings' );
