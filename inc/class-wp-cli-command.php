<?php

namespace Gaussholder;

use cli;
use WP_CLI;
use WP_CLI_Command;
use WP_Query;

class CLI_Command extends WP_CLI_Command {

	/**
	 * Calculate image color data for a single attachment.
	 *
	 * @subcommand process
	 * @synopsis <id> [--dry-run] [--verbose] [--regenerate]
	 */
	public function process( $args, $args_assoc ) {

		$args_assoc = wp_parse_args( $args_assoc, array(
			'verbose'    => true,
			'dry-run'    => false,
			'regenerate' => false,
		) );

		$attachment_id = absint( $args[0] );
		$metadata = wp_get_attachment_metadata( $attachment_id );

		if ( ! $args_assoc['regenerate'] ) {
			return;
		}

		// Unless regenerating, skip attachments that already have data.
		$has_placeholder = false;
		if ( ! $args_assoc['regenerate'] && $has_placeholder ) {

			if ( $args_assoc['verbose'] ) {
				WP_CLI::line( sprintf( 'Skipping attachment %d. Data already exists.', $attachment_id ) );
			}

			return;

		}

		if ( ! $args_assoc['dry-run'] ) {
			$result = generate_placeholders( $attachment_id );
		}

		if ( is_wp_error( $result ) ) {
			WP_CLI::error( implode( "\n", $result->get_error_messages() ) );
		}

		if ( $args_assoc['verbose'] ) {
			WP_CLI::line( sprintf( 'Updated caclulated colors for attachment %d.', $attachment_id ) );
		}

	}

	/**
	 * Process image color data for all attachments.
	 *
	 * @subcommand process-all
	 * @synopsis [--dry-run] [--count=<int>] [--offset=<int>] [--regenerate]
	 */
	public function process_all( $args, $args_assoc ) {

		$args_assoc = wp_parse_args( $args_assoc, array(
			'count'      => 1,
			'offset'     => 0,
			'dry-run'    => false,
			'regenerate' => false,
		) );

		if ( empty( $page ) ) {
			$page = absint( $args_assoc['offset'] ) / absint( $args_assoc['count'] );
			$page = ceil( $page );
			if ( $page < 1 ) {
				$page = 1;
			}
		}

		while ( empty( $no_more_posts ) ) {

			$query = new WP_Query( array(
				'post_type'      => 'attachment',
				'post_status'    => 'inherit',
				'fields'         => 'ids',
				'posts_per_page' => $args_assoc['count'],
				'paged'          => $page,
			) );

			if ( empty( $progress_bar ) ) {
				$progress_bar = new cli\progress\Bar( sprintf( 'Processing images', absint( $query->found_posts ) ), absint( $query->found_posts ), 100 );
				$progress_bar->display();
			}

			foreach ( $query->posts as $post_id ) {

				$progress_bar->tick();

				$this->process(
					array( $post_id ),
					array(
						'verbose' => false,
						'dry-run' => $args_assoc['dry-run'],
						'regenerate' => $args_assoc['regenerate']
					)
				);

			}

			if ( $query->get('paged') >= $query->max_num_pages ) {
				$no_more_posts = true;
			}

			if ( $query->get('paged') === 0 ) {
				$page = 2;
			} else {
				$page = absint( $query->get('paged') ) + 1;
			}

		}

		$progress_bar->finish();

	}

	/**
	 * Check how big the placeholder will be for an image or file with a given
	 * radius.
	 *
	 * @subcommand check-size
	 * @synopsis <id_or_file> <radius>
	 * @param array $args
	 */
	public function check_size( $args ) {
		if ( is_numeric( $args[0] ) ) {
			$attachment_id = absint( $args[0] );
			$file = get_attached_file( $attachment_id );
			if ( empty( $file ) ) {
				WP_CLI::error( __( 'Attachment does not exist', 'gaussholder' ) );
			}
		} else {
			$file = $args[1];
		}

		if ( ! file_exists( $file ) ) {
			WP_CLI::error( sprintf( __( 'File %s does not exist', 'gaussholder' ), $file ) );
		}

		// Generate a placeholder with the radius
		$radius = absint( $args[1] );
		$data = JPEG\data_for_file( $file, $radius );
		WP_CLI::line( sprintf( '%s: %dB (%dpx radius)', basename( $file ), strlen( $data[0] ), $radius ) );
	}

}
