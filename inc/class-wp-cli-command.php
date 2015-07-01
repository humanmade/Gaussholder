<?php

namespace HM_Image_Placeholder;

use WP_CLI, cli, WP_Query;

class WP_CLI_Command extends \WP_CLI_Command {

	/**
	 * Regenerate image color data for a single attachment.
	 *
	 * @subcommand regenerate
	 * @synopsis <id> [--dry-run] [--verbose]
	 */
	public function regenerate( $args, $args_assoc ) {

		$args_assoc = wp_parse_args( $args_assoc, array(
			'verbose' => true,
			'dry-run' => false,
		) );

		$attachment_id = absint( $args[0] );

		if ( ! $args_assoc['dry-run'] ) {

			$plugin = Plugin::get_instance();

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
	 * @subcommand regenerate-all
	 * @synopsis [--dry-run] [--count=<int>] [--offset=<int>]
	 */
	public function _regenerate_all( $args, $args_assoc ) {

		$args_assoc = wp_parse_args( $args_assoc, array(
			'count'   => 1,
			'offset'  => 0,
			'dry-run' => false,
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
				$progress_bar = new cli\progress\Bar( sprintf( 'Regenerating image color data for %d', absint( $query->found_posts ) ), absint( $query->found_posts ), 100 );
				$progress_bar->display();
			}

			foreach ( $query->posts as $post_id ) {

				$progress_bar->tick();

				$this->regenerate(
					array( $post_id ),
					array( 'verbose' => false, 'dry-run' => $args_assoc['dry-run'] )
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
