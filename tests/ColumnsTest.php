<?php
/**
 * Tests for column registration and ordering.
 *
 * @package PSLastUpdatedAdminColumns
 */

declare( strict_types=1 );

namespace PS\LastUpdated\Tests;

use Brain\Monkey\Actions;
use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use PHPUnit\Framework\Attributes\DataProvider;
use WP_Screen;

final class ColumnsTest extends TestCase {

	public function test_column_is_inserted_directly_after_the_date_column(): void {
		$columns = $this->columns->add_column(
			array(
				'cb'     => '',
				'title'  => 'Title',
				'author' => 'Author',
				'date'   => 'Date',
			)
		);

		$this->assertSame(
			array( 'cb', 'title', 'author', 'date', 'last_updated' ),
			array_keys( $columns )
		);
	}

	public function test_column_keeps_trailing_columns_after_itself(): void {
		$columns = $this->columns->add_column(
			array(
				'title' => 'Title',
				'date'  => 'Date',
				'seo'   => 'SEO',
			)
		);

		$this->assertSame(
			array( 'title', 'date', 'last_updated', 'seo' ),
			array_keys( $columns )
		);
	}

	public function test_column_is_appended_when_there_is_no_date_column(): void {
		$columns = $this->columns->add_column(
			array(
				'cb'    => '',
				'title' => 'Title',
			)
		);

		$this->assertSame(
			array( 'cb', 'title', 'last_updated' ),
			array_keys( $columns )
		);
	}

	public function test_column_is_labelled(): void {
		$columns = $this->columns->add_column( array( 'date' => 'Date' ) );

		$this->assertSame( 'Last Updated', $columns['last_updated'] );
	}

	public function test_column_sorts_by_modified_date(): void {
		$columns = $this->columns->make_column_sortable( array( 'title' => 'title' ) );

		$this->assertSame( 'modified', $columns['last_updated'] );
	}

	public function test_hooks_are_registered_for_a_public_post_type(): void {
		Functions\when( 'get_post_type_object' )->justReturn(
			(object) array(
				'name'    => 'post',
				'public'  => true,
				'show_ui' => true,
			)
		);

		Filters\expectAdded( 'manage_post_posts_columns' )->once();
		Actions\expectAdded( 'manage_post_posts_custom_column' )->once();
		Filters\expectAdded( 'manage_edit-post_sortable_columns' )->once();

		$this->columns->register_columns( new WP_Screen( 'edit', 'post' ) );
	}

	/**
	 * @param WP_Screen|null $screen           Current admin screen.
	 * @param object|null    $post_type_object Post type object returned by WordPress.
	 */
	#[DataProvider( 'provide_unsupported_screens' )]
	public function test_hooks_are_not_registered_for_unsupported_screens( $screen, $post_type_object ): void {
		Functions\when( 'get_post_type_object' )->justReturn( $post_type_object );

		Filters\expectAdded( 'manage_post_posts_columns' )->never();
		Filters\expectAdded( 'manage_attachment_posts_columns' )->never();
		Actions\expectAdded( 'manage_post_posts_custom_column' )->never();

		$this->columns->register_columns( $screen );
	}

	public static function provide_unsupported_screens(): array {
		$public_post = (object) array(
			'name'    => 'post',
			'public'  => true,
			'show_ui' => true,
		);

		return array(
			'not a screen object'    => array( null, $public_post ),
			'not a list table'       => array( new WP_Screen( 'post', 'post' ), $public_post ),
			'screen without a type'  => array( new WP_Screen( 'edit', '' ), $public_post ),
			'unregistered post type' => array( new WP_Screen( 'edit', 'post' ), null ),
			'non-public post type'   => array(
				new WP_Screen( 'edit', 'post' ),
				(object) array(
					'name'    => 'post',
					'public'  => false,
					'show_ui' => true,
				),
			),
			'post type without UI'   => array(
				new WP_Screen( 'edit', 'post' ),
				(object) array(
					'name'    => 'post',
					'public'  => true,
					'show_ui' => false,
				),
			),
			'media library'          => array(
				new WP_Screen( 'edit', 'attachment' ),
				(object) array(
					'name'    => 'attachment',
					'public'  => true,
					'show_ui' => true,
				),
			),
		);
	}
}
