<?php

namespace Gaussholder\Admin;

/**
 * Set up hooked callbacks on admin_init
 */
function bootstrap() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	if ( ! class_exists( 'Imagick' ) ) {
		add_action( 'admin_notices', __NAMESPACE__ . '\\display_imagick_notice' );
	}
}

/**
 * Display a notice when Imagick PHP extension is not available.
 */
function display_imagick_notice() {
	?>
	<div class="notice notice-error">
		<p><?php esc_html_e( 'The Imagick PHP extension is not installed. Gaussholder cannot process images without it. Please, install and activate Imagick extension.', 'gaussholder' ); ?></p>
	</div>
	<?php
}
