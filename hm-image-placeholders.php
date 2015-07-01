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

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/inc/class-hm-image-placeholders.php';

HM_Image_Placeholders::get_instance();
