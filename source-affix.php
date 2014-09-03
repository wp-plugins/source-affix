<?php
/**
 * Source Affix Plugin
 *
 * @package   Source_Affix
 * @author    Nilambar Sharma <nilambar@outlook.com>
 * @license   GPL-2.0+
 * @link      http://nilambar.net
 * @copyright 2013 Nilambar Sharma
 *
 * @wordpress-plugin
 * Plugin Name:       Source Affix
 * Plugin URI:        http://wordpress.org/plugins/source-affix
 * Description:       Plugin to add sources in your posts
 * Version:           1.0.3
 * Author:            Nilambar Sharma
 * Author URI:        http://nilambar.net
 * Text Domain:       source-affix
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'SOURCE_AFFIX_VERSION', '1.0.3' );
define( 'SOURCE_AFFIX_NAME', 'Source Affix' );
define( 'SOURCE_AFFIX_SLUG', 'source-affix' );
define( 'SOURCE_AFFIX_BASENAME', basename( dirname( __FILE__ ) ) );
define( 'SOURCE_AFFIX_DIR', rtrim( plugin_dir_path( __FILE__ ), '/' ) );
define( 'SOURCE_AFFIX_URL', rtrim( plugin_dir_url( __FILE__ ), '/' ) );
// define( 'SOURCE_AFFIX_META_KEY_OLD', 'sa_source' );
// define( 'SOURCE_AFFIX_META_KEY_NEW', 'source_affix_src_meta' );

require_once( plugin_dir_path( __FILE__ ) . 'includes/helper.php' );

/*
 * Include plugin classes
 */
require_once( plugin_dir_path( __FILE__ ) . 'class-source-affix.php' );
require_once( plugin_dir_path( __FILE__ ) . 'class-source-affix-admin.php' );

/*
 * Register hooks that are fired when the plugin is activated or deactivated.
 * When the plugin is deleted, the uninstall.php file is loaded.
 */
register_activation_hook( __FILE__, array( 'Source_Affix', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Source_Affix', 'deactivate' ) );

/*
 *
 */
add_action( 'plugins_loaded', array( 'Source_Affix', 'get_instance' ) );
add_action( 'plugins_loaded', array( 'Source_Affix_Admin', 'get_instance' ) );
