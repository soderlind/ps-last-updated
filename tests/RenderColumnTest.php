<?php
/**
 * Tests for the rendered Last Updated cell.
 *
 * @package PSLastUpdatedAdminColumns
 */

declare( strict_types=1 );

namespace PS\LastUpdated\Tests;

use Brain\Monkey\Functions;
use WP_User;

final class RenderColumnTest extends TestCase {

	private const POST_ID = 42;

	protected function setUp(): void {
		parent::setUp();

		Functions\when( 'get_the_modified_date' )->alias(
			static function ( $format, $post_id ) {
				$dates = array(
					DATE_W3C => '2026-07-14T21:56:00+02:00',
					'Y/m/d'  => '2026/07/14',
				);

				return $dates[ $format ] ?? '';
			}
		);

		Functions\when( 'get_the_modified_time' )->alias(
			static function ( $format, $post_id ) {
				return 'g:i a' === $format ? '9:56 pm' : '';
			}
		);
	}

	public function test_nothing_is_rendered_for_other_columns(): void {
		$output = $this->capture(
			fn() => $this->columns->render_column( 'title', self::POST_ID )
		);

		$this->assertSame( '', $output );
	}

	public function test_date_and_time_render_on_a_single_line(): void {
		$this->mock_editor( 0 );

		$output = $this->capture(
			fn() => $this->columns->render_column( 'last_updated', self::POST_ID )
		);

		$this->assertStringContainsString(
			'<time datetime="2026-07-14T21:56:00+02:00">2026/07/14 at 9:56 pm</time>',
			$output
		);
		$this->assertStringNotContainsString( '<br>', $output );
	}

	public function test_last_editor_is_rendered_in_a_row_actions_block(): void {
		$this->mock_editor( 7, 'per' );
		Functions\when( 'get_edit_user_link' )->justReturn( '' );

		$output = $this->capture(
			fn() => $this->columns->render_column( 'last_updated', self::POST_ID )
		);

		$this->assertStringContainsString(
			'<div class="row-actions"><span class="last-updated-by">By per</span></div>',
			$output
		);
	}

	public function test_last_editor_links_to_the_user_profile(): void {
		$this->mock_editor( 7, 'per' );
		Functions\when( 'get_edit_user_link' )->justReturn( 'http://example.test/wp-admin/user-edit.php?user_id=7' );

		$output = $this->capture(
			fn() => $this->columns->render_column( 'last_updated', self::POST_ID )
		);

		$this->assertStringContainsString(
			'<a href="http://example.test/wp-admin/user-edit.php?user_id=7" aria-label="View profile of per">per</a>',
			$output
		);
	}

	public function test_profile_link_is_omitted_when_the_user_cannot_be_edited(): void {
		$this->mock_editor( 7, 'per' );
		Functions\when( 'get_edit_user_link' )->justReturn( '' );

		$output = $this->capture(
			fn() => $this->columns->render_column( 'last_updated', self::POST_ID )
		);

		$this->assertStringNotContainsString( '<a href', $output );
		$this->assertStringContainsString( 'By per', $output );
	}

	public function test_post_author_is_used_when_no_editor_is_recorded(): void {
		Functions\when( 'get_post_meta' )->justReturn( '' );
		Functions\expect( 'get_post_field' )
			->once()
			->with( 'post_author', self::POST_ID )
			->andReturn( '3' );
		Functions\when( 'get_userdata' )->justReturn( new WP_User( 3, 'author-name' ) );
		Functions\when( 'get_edit_user_link' )->justReturn( '' );

		$output = $this->capture(
			fn() => $this->columns->render_column( 'last_updated', self::POST_ID )
		);

		$this->assertStringContainsString( 'By author-name', $output );
	}

	public function test_no_editor_block_is_rendered_when_no_user_resolves(): void {
		$this->mock_editor( 0 );

		$output = $this->capture(
			fn() => $this->columns->render_column( 'last_updated', self::POST_ID )
		);

		$this->assertStringNotContainsString( 'row-actions', $output );
	}

	public function test_output_is_escaped(): void {
		$this->mock_editor( 7, '<script>alert(1)</script>' );
		Functions\when( 'get_edit_user_link' )->justReturn( '' );

		$output = $this->capture(
			fn() => $this->columns->render_column( 'last_updated', self::POST_ID )
		);

		$this->assertStringNotContainsString( '<script>', $output );
	}

	/**
	 * Mock the user lookup chain used by the column.
	 *
	 * @param int    $user_id      Resolved user ID, or 0 for none.
	 * @param string $display_name Display name for the resolved user.
	 * @return void
	 */
	private function mock_editor( int $user_id, string $display_name = '' ): void {
		Functions\when( 'get_post_meta' )->justReturn( (string) $user_id );
		Functions\when( 'get_post_field' )->justReturn( '0' );
		Functions\when( 'get_userdata' )->alias(
			static function ( $id ) use ( $display_name ) {
				return $id ? new WP_User( (int) $id, $display_name ) : false;
			}
		);
	}
}
