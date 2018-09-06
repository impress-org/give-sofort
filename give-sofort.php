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
	 * Notices (array)
	 *
	 * @since 1.0.1
	 *
	 * @var array
	 */
	public $notices = array();

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
			self::$instance->setup();
		}

		return self::$instance;
	}

	/**
	 * Setup Give Sofort Gateway.
	 *
	 * @since  1.0.1
	 * @access private
	 */
	private function setup() {

		// Give init hook.
		add_action( 'give_init', array( $this, 'init' ), 10 );
		add_action( 'admin_init', array( $this, 'check_environment' ), 999 );
		add_action( 'admin_notices', array( $this, 'admin_notices' ), 15 );
	}


	public function init() {

		$this->load_plugin_textdomain();
		$this->licensing();

		if ( ! $this->get_environment_warning() ) {
			return;
		}

		// Actions
		$this->activation_banner();

		if ( is_admin() ) {
			require_once GIVE_SOFORT_DIR . 'includes/admin/plugin-activation.php';
		}

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

	/**
	 * Check plugin environment.
	 *
	 * @since  1.0.0
	 * @access public
	 *
	 * @return bool
	 */
	public function check_environment() {
		// Flag to check whether plugin file is loaded or not.
		$is_working = true;

		// Load plugin helper functions.
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . '/wp-admin/includes/plugin.php';
		}

		/* Check to see if Give is activated, if it isn't deactivate and show a banner. */
		// Check for if give plugin activate or not.
		$is_give_active = defined( 'GIVE_PLUGIN_BASENAME' ) ? is_plugin_active( GIVE_PLUGIN_BASENAME ) : false;

		if ( empty( $is_give_active ) ) {
			// Show admin notice.
			$this->add_admin_notice( 'prompt_give_activate', 'error', sprintf( __( '<strong>Activation Error:</strong> You must have the <a href="%s" target="_blank">Give</a> plugin installed and activated for Give - Sofort to activate.', 'give-sofort' ), 'https://givewp.com' ) );
			$is_working = false;
		}

		return $is_working;
	}

	/**
	 * Check plugin for Give environment.
	 *
	 * @since  1.1.2
	 * @access public
	 *
	 * @return bool
	 */
	public function get_environment_warning() {
		// Flag to check whether plugin file is loaded or not.
		$is_working = true;

		// Verify dependency cases.
		if (
			defined( 'GIVE_VERSION' )
			&& version_compare( GIVE_VERSION, GIVE_SOFORT_MIN_GIVE_VER, '<' )
		) {

			/* Min. Give. plugin version. */
			// Show admin notice.
			$this->add_admin_notice( 'prompt_give_incompatible', 'error', sprintf( __( '<strong>Activation Error:</strong> You must have the <a href="%s" target="_blank">Give</a> core version %s for the Give - Sofort add-on to activate.', 'give-sofort' ), 'https://givewp.com', GIVE_SOFORT_MIN_GIVE_VER ) );

			$is_working = false;
		}

		return $is_working;
	}

	/**
	 * Allow this class and other classes to add notices.
	 *
	 * @since 1.0
	 *
	 * @param $slug
	 * @param $class
	 * @param $message
	 */
	public function add_admin_notice( $slug, $class, $message ) {
		$this->notices[ $slug ] = array(
			'class'   => $class,
			'message' => $message,
		);
	}

	/**
	 * Display admin notices.
	 *
	 * @since 1.0
	 */
	public function admin_notices() {

		$allowed_tags = array(
			'a'      => array(
				'href'  => array(),
				'title' => array(),
				'class' => array(),
				'id'    => array(),
			),
			'br'     => array(),
			'em'     => array(),
			'span'   => array(
				'class' => array(),
			),
			'strong' => array(),
		);

		foreach ( (array) $this->notices as $notice_key => $notice ) {
			echo "<div class='" . esc_attr( $notice['class'] ) . "'><p>";
			echo wp_kses( $notice['message'], $allowed_tags );
			echo '</p></div>';
		}

	}

	/**
	 * Show activation banner for this add-on.
	 *
	 * @since 1.0
	 *
	 * @return bool
	 */
	public function activation_banner() {

		// Check for activation banner inclusion.
		if (
			! class_exists( 'Give_Addon_Activation_Banner' )
			&& file_exists( GIVE_PLUGIN_DIR . 'includes/admin/class-addon-activation-banner.php' )
		) {
			include GIVE_PLUGIN_DIR . 'includes/admin/class-addon-activation-banner.php';
		}

		// Initialize activation welcome banner.
		if ( class_exists( 'Give_Addon_Activation_Banner' ) ) {

			// Only runs on admin.
			$args = array(
				'file'              => GIVE_SOFORT_FILE,
				'name'              => __( 'Sofort Gateway', 'give-sofort' ),
				'version'           => GIVE_SOFORT_VERSION,
				'settings_url'      => admin_url( 'edit.php?post_type=give_forms&page=give-settings&tab=gateways&section=sofort' ),
				'documentation_url' => 'http://docs.givewp.com/addon-sofort',
				'support_url'       => 'https://givewp.com/support/',
				'testing'           => false, // Never leave true.
			);
			new Give_Addon_Activation_Banner( $args );
		}
	}

	/**
	 * Implement Give Licensing for Give Sofort Add On.
	 *
	 * @since  1.0.1
	 * @access private
	 */
	private function licensing() {
		if ( class_exists( 'Give_License' ) ) {
			new Give_License( GIVE_SOFORT_FILE, 'Sofort', GIVE_SOFORT_VERSION, 'WordImpress' );
		}
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
	return Give_Sofort_Gateway::instance();
}

give_sofort();
