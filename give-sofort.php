<?php
/*
Plugin Name: Give Sofort - Sofort. Payment Solution
Plugin URI: https://plugins.coachbirgit.com
Description: Extends the Give WP plugin with the payment gateway Sofort. from Sofort
Version: 1.0
Author: CoachBirgit
Author URI: http://coachbirgit.com
License: A "Slug" license name e.g. GPL2
*/


//Exit if accessed directly

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

//Plugin version.
if ( ! defined( 'GIVE_SOFORT_VERSION' ) ) {
    define( 'GIVE_SOFORT_VERSION', '1.0' );
}

// Plugin Folder Path.
if ( ! defined( 'GIVE_SOFORT_DIR' ) ) {
    define( 'GIVE_SOFORT_DIR', plugin_dir_path( __FILE__ ) );
	/** @define "GIVE_SOFORT_DIR" "/Users/coachbirgit/PhpstormProjects/give-sofort" */
}

//Plugin Folder URL.
if ( ! defined( 'GIVE_SOFORT_URL' ) ) {
    define( 'GIVE_SOFORT_URL', plugin_dir_url( __FILE__ ) );
}

// Sofort API Version that Give uses.
if ( ! defined( 'GIVE_SOFORT_API_VERSION' ) ) {
    define( 'GIVE_SOFORT_API_VERSION', apply_filters( 'give_sofort_api_version', '' ) );
}

/**
 * Class Give_Sofort_Gateway
 */
class Give_Sofort_Gateway {



    /** Singleton *************************************************************/

    /**
     * @var Give_Sofort_Gateway The one true Give_Sofort_Gateway
     */
    private static $instance;



    /**
     * Main Sofort Instance
     *
     * @since     v1.0
     * @static var array $instance
     * @return    Give_Sofort_Gateway()
     */
    public static function instance() {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new Give_Sofort_Gateway();
            self::$instance->sofort_init();
        }

        return self::$instance;
    }


    function sofort_init() {

        // Filters
        add_filter( 'give_payment_gateways', array( $this, 'register_gateway' ) );
        // Actions
        //Sofort Gateway does not need a CC form, so remove it.
        add_action( 'give_sofort_cc_form', '__return_false' );

        //Includes
        include_once GIVE_SOFORT_DIR . 'includes/admin/settings.php';
	    include_once GIVE_SOFORT_DIR . 'vendor/class-sofort-payment.php';

	    //include_once GIVE_SOFORT_DIR . 'vendor/php-console/php-console/src/PhpConsole/__autoload.php';
		//$isActiveClient = PhpConsole\Connector::getInstance()->isActiveClient();

    }

    /**
     * Register Sofort Gateway
     *
     * @param $gateways
     *
     * @return mixed
     */
    public function register_gateway( $gateways ) {


        $checkout_label = __( 'Sofort&uuml;berweisung', 'give-sofort' );

        $gateways['sofort'] = array(
            'admin_label'    => __( 'Sofort', 'give-sofort' ),
            'checkout_label' => $checkout_label
        );

        return $gateways;
    }



}

/**
 * The main function responsible for returning the one true Give_Sofort_Gateway Instance
 * to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: <?php $sofort = Give_Sofort_Gateway(); ?>
 *
 * @since v1.0
 *
 * @return mixed one true Give_Sofort_Gateway Instance
 */

function Give_Sofort() {

    if ( ! class_exists( 'Give' ) ) {
        return false;
    }

    return Give_Sofort_Gateway::instance();
}

add_action( 'plugins_loaded', 'Give_Sofort' );