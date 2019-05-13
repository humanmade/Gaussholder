<?php
/**
 * Plugin Name: Gaussholder
 * Plugin URI: http://hmn.md/
 * Description: Quick and beautiful image placeholders using Gaussian blur.
 * Version: 1.1.3
 * Author: Human Made
 * Author URI: http://hmn.md/
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Network: true
 */

namespace Gaussholder;

use WP_CLI;

define( __NAMESPACE__ . '\\PLUGIN_DIR', __DIR__ );

require_once __DIR__ . '/inc/class-plugin.php';
require_once __DIR__ . '/inc/frontend/namespace.php';
require_once __DIR__ . '/inc/jpeg/namespace.php';

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	require_once __DIR__ . '/inc/class-wp-cli-command.php';
	WP_CLI::add_command( 'gaussholder', 'Gaussholder\\CLI_Command' );
}

add_action( 'plugins_loaded', __NAMESPACE__ . '\\Frontend\\bootstrap' );
add_filter( 'wp_update_attachment_metadata', __NAMESPACE__ . '\\queue_generate_placeholders_on_save', 10, 2 );
add_action( 'gaussholder.generate_placeholders', __NAMESPACE__ . '\\generate_placeholders' );
// We <3 you!
if ( WP_DEBUG && ! defined( 'WP_I_AM_A_GRUMPY_PANTS' ) ) {
	add_action( 'admin_head-plugins.php', function () {
		echo '<style>[data-slug="gaussholder"] .plugin-version-author-uri:after { content: "Made with \002764\00FE0F, just for you."; font-size: 0.8em; opacity: 0; float: right; transition: 300ms opacity; } [data-slug="gaussholder"]:hover .plugin-version-author-uri:after { opacity: 0.3; }</style>';
	});
}
