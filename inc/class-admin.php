<?php

namespace HM_Image_Placeholder;

class Admin {

	private static $instance;

	public static function get_instance() {

		if ( ! ( self::$instance instanceof Admin ) ) {
			self::$instance = new Admin();
			self::$instance->init();
		}

		return self::$instance;
	}

	public function init() {

		add_action( 'admin_init', array( $this, 'display_settings_fields' ) );
	}

	public function display_settings_fields() {

		register_setting( 'media', 'hmip_placeholder_type', array( $this, 'validate_options' ) );

		add_settings_section( 'hmip_settings', __( 'HM Image Placeholders Settings', 'hmip' ), array( $this, 'section_callback' ), 'media' );

		add_settings_field( 'hmip_placeholder_type', __( 'Choose placeholder type', 'hmip' ), array( $this, 'field_callback' ), 'media', 'hmip_settings' );
	}

	public function validate_options( $value ) {

		if ( in_array( $value, array( 'gradient', 'solid' ) ) ) {
			return $value;
		}
	}

	public function section_callback() {

		printf( '<p>%s</p>', __( 'Here you can control whether you\'d like to use solid colors or gradients for the image placeholders.', 'hmip' ) );

	}

	public function field_callback() {

		$placeholder_type = get_site_option( 'hmip_placeholder_type', 'gradient' );

		ob_start(); ?>

				<fieldset>

					<legend class="screen-reader-text"><span><?php esc_html_e( 'Choose placeholder type', 'hmip' ); ?></span></legend>

					<label><input type="radio" name="hmip_placeholder_type" value="gradient" <?php checked( 'gradient' === $placeholder_type ); ?>> <?php esc_html_e( 'Gradients', 'hmip' ); ?></label><br>

					<label><input type="radio" name="hmip_placeholder_type" value="solid" <?php checked( 'solid' === $placeholder_type ); ?>> <?php esc_html_e( 'Solid colors', 'hmip' ); ?></label><br>

				</fieldset>

		<?php echo ob_get_clean();

	}

}
