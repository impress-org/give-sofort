<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Give Display Donors Activation Banner
 *
 * Includes and initializes Give activation banner class.
 *
 * @since 1.0
 */
function give_sofort_activation_banner() {

	// Check for if give plugin activate or not.
	$is_give_active = defined( 'GIVE_PLUGIN_BASENAME' ) ? is_plugin_active( GIVE_SOFORT_BASENAME ) : false;

	// Check to see if Give is activated, if it isn't deactivate and show a banner.
	if ( current_user_can( 'activate_plugins' ) && ! $is_give_active ) {

		add_action( 'admin_notices', 'give_sofort_inactive_notice' );

		// Don't let this plugin activate.
		deactivate_plugins( GIVE_SOFORT_BASENAME );

		if ( isset( $_GET['activate'] ) ) {
			unset( $_GET['activate'] );
		}

		return false;

	}

	// Check for activation banner inclusion.
	if (
		! class_exists( 'Give_Addon_Activation_Banner' )
		&& file_exists( GIVE_SOFORT_DIR . 'includes/admin/class-addon-activation-banner.php' )
	) {
		include GIVE_SOFORT_DIR . 'includes/admin/class-addon-activation-banner.php';
	}

	// Initialize activation welcome banner.
	if ( class_exists( 'Give_Addon_Activation_Banner' ) ) {

		$args = array(
			'file'              => GIVE_SOFORT_FILE,
			'name'              => __( 'SOFORT Gateway', 'give-sofort' ),
			'version'           => GIVE_SOFORT_VERSION,
			'settings_url'      => admin_url( 'edit.php?post_type=give_forms&page=give-settings&tab=gateways&section=sofort' ),
			'documentation_url' => 'http://docs.givewp.com/addon-sofort',
			'support_url'       => 'https://givewp.com/support/',
			'testing'           => false, // Never leave true.
		);

		new Give_Addon_Activation_Banner( $args );
	}

	return false;

}

add_action( 'admin_init', 'give_sofort_activation_banner');

/**
 * Notice for No Core Activation
 *
 * @since 1.0
 */
function give_sofort_inactive_notice() {
	echo '<div class="error"><p>' . __( '<strong>Activation Error:</strong> You must have the <a href="https://givewp.com/" target="_blank">Give</a> plugin installed and activated for the PayFast add-on to activate.', 'give-sofort' ) . '</p></div>';
}

/**
 * Notice for min. version violation.
 *
 * @since 1.0
 */
function give_sofort_version_notice() {
	echo '<div class="error"><p>' . sprintf( __( '<strong>Activation Error:</strong> You must have <a href="%1$s" target="_blank">Give</a> minimum version %2$s for the PayFast add-on to activate.', 'give-sofort' ), 'https://givewp.com', GIVE_SOFORT_MIN_GIVE_VER ) . '</p></div>';
}


/**
 * Plugins row action links
 *
 * @since 1.0
 *
 * @param array $actions An array of plugin action links.
 *
 * @return array An array of updated action links.
 */
function give_sofort_plugin_action_links($actions ) {
	$new_actions = array(
		'settings' => sprintf(
			'<a href="%1$s">%2$s</a>',
			admin_url( 'edit.php?post_type=give_forms&page=give-settings&tab=gateways&section=sofort' ),
			__( 'Settings', 'give-sofort' )
		),
	);

	return array_merge( $new_actions, $actions );
}

add_filter( 'plugin_action_links_' . GIVE_SOFORT_BASENAME, 'give_sofort_plugin_action_links');


/**
 * Plugin row meta links
 *
 * @since 1.0
 *
 * @param array  $plugin_meta An array of the plugin's metadata.
 * @param string $plugin_file Path to the plugin file, relative to the plugins directory.
 *
 * @return array
 */
function give_sofort_plugin_row_meta($plugin_meta, $plugin_file ) {

	if ( $plugin_file !== GIVE_SOFORT_BASENAME ) {
		return $plugin_meta;
	}

	$new_meta_links = array(
		sprintf(
			'<a href="%1$s" target="_blank">%2$s</a>',
			esc_url( add_query_arg( array(
					'utm_source'   => 'plugins-page',
					'utm_medium'   => 'plugin-row',
					'utm_campaign' => 'admin',
				), 'http://docs.givewp.com/addon-sofort' )
			),
			__( 'Documentation', 'give-sofort' )
		),
		sprintf(
			'<a href="%1$s" target="_blank">%2$s</a>',
			esc_url( add_query_arg( array(
					'utm_source'   => 'plugins-page',
					'utm_medium'   => 'plugin-row',
					'utm_campaign' => 'admin',
				), 'https://givewp.com/addons/' )
			),
			__( 'Add-ons', 'give-sofort' )
		),
	);

	return array_merge( $plugin_meta, $new_meta_links );
}

add_filter( 'plugin_row_meta', 'give_sofort_plugin_row_meta', 10, 2 );
