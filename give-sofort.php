<?php
/**
 * Plugin Name: Give - Sofort Payment Gateway
 * Plugin URI: https://github.com/WordImpress/Give-Sofort
 * Description: Accept donations with the Sofort payment gateway.
 * Version: 1.0
 * Author: WordImpress, CoachBirgit
 * Author URI: http://wordimpress.com
 * Text Domain: give-sofort
 * Domain Path: /languages
 *
 * Give Sofort is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * Give Sofort is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Give. If not, see <https://www.gnu.org/licenses/>.
 *
 * A Tribute to Open Source:
 *
 * "Open source software is software that can be freely used, changed, and shared (in modified or unmodified form) by anyone. Open
 * source software is made by many people, and distributed under licenses that comply with the Open Source Definition."
 *
 * -- The Open Source Initiative
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'GIVE_SOFORT_VERSION' ) ) {
	define( 'GIVE_SOFORT_VERSION', '1.0' );
}

if ( ! defined( 'GIVE_SOFORT_MIN_GIVE_VER' ) ) {
	define( 'GIVE_SOFORT_MIN_GIVE_VER', '2.0' );
}

if ( ! defined( 'GIVE_SOFORT_FILE' ) ) {
	define( 'GIVE_SOFORT_FILE', __FILE__ );
}

if ( ! defined( 'GIVE_SOFORT_BASENAME' ) ) {
	define( 'GIVE_SOFORT_BASENAME', plugin_basename( GIVE_SOFORT_FILE ) );
}


if ( ! defined( 'GIVE_SOFORT_DIR' ) ) {
	define( 'GIVE_SOFORT_DIR', plugin_dir_path( GIVE_SOFORT_FILE ) );
}

if ( ! defined( 'GIVE_SOFORT_URL' ) ) {
	define( 'GIVE_SOFORT_URL', plugin_dir_url( GIVE_SOFORT_FILE ) );
}


if ( file_exists( GIVE_SOFORT_DIR . 'vendor/autoload.php' ) ) {
	require GIVE_SOFORT_DIR . 'vendor/autoload.php';
}

/**
 * Class Give_Sofort_Gateway
 */
class Give_Sofort_Gateway {

	/**
	 * @var Give_Sofort_Gateway The one true Give_Sofort_Gateway
	 */
	private static $instance;

	/**
	 * Main Sofort Instance
	 *
	 * @since     v1.0
	 * @static    var array $instance
	 * @return    Give_Sofort_Gateway()
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new Give_Sofort_Gateway();
			self::$instance->sofort_init();
		}

		return self::$instance;
	}


	public function sofort_init() {

		// Actions
		add_action( 'plugins_loaded', array( $this, 'load_plugin_textdomain' ) );

		// Includes
		include_once GIVE_SOFORT_DIR . 'includes/admin/class-admin-settings.php';
		include_once GIVE_SOFORT_DIR . 'includes/class-sofort-payment.php';

	}

	/**
	 * Load textdomain for translations.
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain( 'give-sofort', false, basename( dirname( __FILE__ ) ) . '/languages/' );
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

function give_sofort() {

	if ( ! class_exists( 'Give' ) ) {
		return false;
	}

	if ( is_admin() ) {
		require_once GIVE_SOFORT_DIR . 'includes/admin/plugin-activation.php';
	}

	// Setup licence.
	if ( class_exists( 'Give_License' ) ) {
		new Give_License( GIVE_SOFORT_FILE, 'Sofort', GIVE_SOFORT_VERSION, 'WordImpress' );
	}

	return Give_Sofort_Gateway::instance();
}

add_action( 'plugins_loaded', 'give_sofort' );
