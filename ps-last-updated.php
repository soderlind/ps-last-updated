<?php
/**
 * Plugin Name: PS Last Updated Admin Columns
 * Description: Adds a sortable Last Updated column to public post type admin lists.
 * Version: 1.0.0
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Text Domain: ps-last-updated
 * Domain Path: /languages
 *
 * @package PSLastUpdatedAdminColumns
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Adds a sortable modified-date column to public post type lists.
 */
final class PS_Last_Updated_Admin_Columns {

	/**
	 * Register the admin screen hook.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
		add_action( 'current_screen', array( $this, 'register_columns' ) );
	}

	/**
	 * Load translations from the plugin's languages directory.
	 *
	 * @return void
	 */
	public function load_textdomain() {
		load_plugin_textdomain(
			'ps-last-updated',
			false,
			dirname( plugin_basename( __FILE__ ) ) . '/languages'
		);
	}

	/**
	 * Register column hooks for the current public post type.
	 *
	 * @param WP_Screen $screen Current admin screen.
	 * @return void
	 */
	public function register_columns( $screen ) {
		if ( ! $screen instanceof WP_Screen || 'edit' !== $screen->base || empty( $screen->post_type ) ) {
			return;
		}

		$post_type = get_post_type_object( $screen->post_type );
		if ( ! $post_type || ! $post_type->public || ! $post_type->show_ui || 'attachment' === $post_type->name ) {
			return;
		}

		add_filter(
			"manage_{$post_type->name}_posts_columns",
			array( $this, 'add_column' )
		);
		add_action(
			"manage_{$post_type->name}_posts_custom_column",
			array( $this, 'render_column' ),
			10,
			2
		);
		add_filter(
			"manage_edit-{$post_type->name}_sortable_columns",
			array( $this, 'make_column_sortable' )
		);
	}

	/**
	 * Add the Last Updated column after the date.
	 *
	 * @param array $columns Existing list table columns.
	 * @return array
	 */
	public function add_column( $columns ) {
		$updated_columns = array();

		foreach ( $columns as $key => $label ) {
			$updated_columns[ $key ] = $label;

			if ( 'date' === $key ) {
				$updated_columns['last_updated'] = __( 'Last Updated', 'ps-last-updated' );
			}
		}

		if ( ! isset( $updated_columns['last_updated'] ) ) {
			$updated_columns['last_updated'] = __( 'Last Updated', 'ps-last-updated' );
		}

		return $updated_columns;
	}

	/**
	 * Render the modified date and time, plus the last editor on row hover.
	 *
	 * @param string $column_name Column being rendered.
	 * @param int    $post_id     Current post ID.
	 * @return void
	 */
	public function render_column( $column_name, $post_id ) {
		if ( 'last_updated' !== $column_name ) {
			return;
		}

		printf(
			'<time datetime="%1$s">%2$s</time>',
			esc_attr( get_the_modified_date( DATE_W3C, $post_id ) ),
			esc_html(
				sprintf(
					/* translators: 1: Post modified date, 2: Post modified time. */
					__( '%1$s at %2$s', 'ps-last-updated' ),
					get_the_modified_date( __( 'Y/m/d' ), $post_id ),
					get_the_modified_time( __( 'g:i a' ), $post_id )
				)
			)
		);

		$editor = $this->get_last_editor( $post_id );
		if ( ! $editor ) {
			return;
		}

		$editor_name = esc_html( $editor->display_name );
		$profile_url = get_edit_user_link( $editor->ID );

		if ( $profile_url ) {
			$editor_name = sprintf(
				'<a href="%1$s" aria-label="%2$s">%3$s</a>',
				esc_url( $profile_url ),
				/* translators: %s: Display name of the user who last updated the post. */
				esc_attr( sprintf( __( 'View profile of %s', 'ps-last-updated' ), $editor->display_name ) ),
				$editor_name
			);
		}

		printf(
			'<div class="row-actions"><span class="last-updated-by">%s</span></div>',
			sprintf(
				/* translators: %s: Display name of the user who last updated the post. */
				esc_html__( 'By %s', 'ps-last-updated' ),
				$editor_name
			)
		);
	}

	/**
	 * Get the user who last updated the post.
	 *
	 * @param int $post_id Current post ID.
	 * @return WP_User|false User object, or false when unknown.
	 */
	private function get_last_editor( $post_id ) {
		$user_id = (int) get_post_meta( $post_id, '_edit_last', true );

		if ( ! $user_id ) {
			$user_id = (int) get_post_field( 'post_author', $post_id );
		}

		if ( ! $user_id ) {
			return false;
		}

		return get_userdata( $user_id );
	}

	/**
	 * Make the column sortable by the post modified date.
	 *
	 * @param array $columns Existing sortable columns.
	 * @return array
	 */
	public function make_column_sortable( $columns ) {
		$columns['last_updated'] = 'modified';

		return $columns;
	}
}

( new PS_Last_Updated_Admin_Columns() )->register();
