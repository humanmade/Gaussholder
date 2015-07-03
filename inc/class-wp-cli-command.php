<?php

namespace HM_Image_Placeholder;

use WP_CLI, cli, WP_Query;

class WP_CLI_Command extends \WP_CLI_Command {

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

		$plugin        = Plugin::get_instance();
		$attachment_id = absint( $args[0] );

		// Unless regenerating, skip attachments that already have data.
		if ( ! $args_assoc['regenerate'] && $plugin->calculate_colors_for_attachment( $attachment_id ) ) {

			if ( $args_assoc['verbose'] ) {
				WP_CLI::line( sprintf( 'Skipping attachment %d. Data already exists.', $attachment_id ) );
			}

			return;

		}

		if ( ! $args_assoc['dry-run'] ) {


			$plugin->save_colors_for_attachment(
				$attachment_id,
				$plugin->calculate_colors_for_attachment( $attachment_id )
			);

		}

		if ( $args_assoc['verbose'] ) {
			WP_CLI::line( sprintf( 'Updated caclulated colors for attachment %d.', $attachment_id ) );
		}

	}

	/**
	 * Regenerate image color data for all attachments.
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
						'regenerate' => $args_assoc['dry-run']
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

}
