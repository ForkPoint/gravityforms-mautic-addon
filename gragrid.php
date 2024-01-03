<?php
/**
 * Grautic: Gravity Forms + Mautic
 *
 * @package           Grautic
 * @author            ForkPoint
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       Grautic: Gravity Forms + Mautic
 * Plugin URI:        https://github.com/ForkPoint/gravityforms-mautic-addon/tree/develop
 * Description:       Integrates Gravity Forms with Mautic, allowing form submissions to be automatically sent to your Mautic contact lists.
 * Version:           2.2.2
 * Requires at least: 5.2
 * Requires PHP:      7.3
 * Author:            ForkPoint
 * Author URI:        https://github.com/ForkPoint
 * Text Domain:       gragrid
 * Domain Path:       /languages
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Current add-on version
 */
define( 'GRAGRID_VERSION', '2.2.2' );

/**
 * If the Feed Add-On Framework exists, Mautic Add-On is loaded.
 *
 * @since 1.0.0
 */
function gragrid_load() {
	if ( ! method_exists( 'GFForms', 'include_feed_addon_framework' ) ) {
		return;
	}

	require_once 'class-gragrid.php';

	GFAddOn::register( 'Gragrid' );
}
add_action( 'gform_loaded', 'gragrid_load', 5 );
