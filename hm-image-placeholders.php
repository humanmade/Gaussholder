<?php
/*
Plugin Name: HM Image Placeholders
Plugin URI: http://hmn.md
Description: Automatically generate gradient or solid color image placeholders.
Version: 0.1.0
Author: Human Made Limited
Author URI: http://hmn.md/
License: GPL-2.0+
License URI: http://www.gnu.org/licenses/gpl-2.0.txt
Text Domain: hm-gradient-placeholders
Domain Path: /languages
Network: true
 */

namespace HM_Image_Placeholder;

use WP_CLI;

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/inc/class-plugin.php';

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	require_once __DIR__  . '/inc/class-wp-cli-command.php';
	WP_CLI::add_command( 'hm-image-placeholder', 'HM_Image_Placeholder\\WP_CLI_Command' );
}

Plugin::get_instance();

if ( is_admin() ) {
	require_once __DIR__ . '/inc/class-hm-image-placeholders-admin.php';
	HM_Image_Placeholders_Admin::get_instance();
}

HM_Image_Placeholders::get_instance();
