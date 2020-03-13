<?php

namespace Yoast\WP\SEO\Tests\Builders;

use Mockery;
use Yoast\WP\SEO\Helpers\Meta_Helper;
use Yoast\WP\SEO\Helpers\Primary_Term_Helper;
use Yoast\WP\SEO\Repositories\Primary_Term_Repository;
use Yoast\WP\SEO\Tests\Doubles\Primary_Term_Builder_Double;
use Yoast\WP\SEO\Tests\Mocks\Primary_Term;
use Yoast\WP\SEO\Tests\TestCase;

/**
 * Class Primary_Term_Builder_Test
 *
 * @group builders
 *
 * @coversDefaultClass \Yoast\WP\SEO\Builders\Primary_Term_Builder
 */
class Primary_Term_Builder_Test extends TestCase {

	/**
	 * Holds the primary term builder under test.
	 *
	 * @var Mockery\MockInterface|Primary_Term_Builder_Double
	 */
	private $instance;

	/**
	 * Holds the mock primary term repository.
	 *
	 * @var Mockery\MockInterface|Primary_Term_Repository
	 */
	private $repository;

	/**
	 * Holds the primary term helper.
	 *
	 * @var Mockery\MockInterface|Primary_Term_Helper
	 */
	private $primary_term;

	/**
	 * Holds the meta helper.
	 *
	 * @var Mockery\MockInterface|Meta_Helper
	 */
	private $meta;

	/**
	 * @inheritDoc
	 */
	public function setUp() {
		parent::setUp();

		$this->repository   = Mockery::mock( Primary_Term_Repository::class );
		$this->primary_term = Mockery::mock( Primary_Term_Helper::class );
		$this->meta         = Mockery::mock( Meta_Helper::class );
		$this->instance     = Mockery::mock(
			Primary_Term_Builder_Double::class,
			[ $this->repository, $this->primary_term, $this->meta ]
		)
			->shouldAllowMockingProtectedMethods()
			->makePartial();
	}

	/**
	 * Tests the saving of a primary term, the happy path.
	 *
	 * @covers ::save_primary_term
	 */
	public function test_save_primary_term() {
		$primary_term = Mockery::mock( Primary_Term::class );
		$primary_term->expects( 'save' )->once();

		$this->repository
			->expects( 'find_by_post_id_and_taxonomy' )
			->once()
			->with( 1, 'category', true )
			->andReturn( $primary_term );

		$this->meta
			->expects( 'get_value' )
			->with( 'primary_category', 1 )
			->andReturn( 1337 );

		$this->instance->save_primary_term( 1, 'category' );

		$this->assertEquals( 1337, $primary_term->term_id );
		$this->assertEquals( 1, $primary_term->post_id );
		$this->assertEquals( 'category', $primary_term->taxonomy );
	}

	/**
	 * @covers ::save_primary_term
	 */
	public function test_save_primary_term_with_no_term_selected() {
		$this->meta
			->expects( 'get_value' )
			->with( 'primary_category', 1 )
			->andReturn( false );

		$primary_term = Mockery::mock();
		$primary_term->expects( 'delete' )->once();
		$primary_term->expects( 'save' )->never();

		$this->repository
			->expects( 'find_by_post_id_and_taxonomy' )
			->with( 1, 'category', false )
			->andReturn( $primary_term );

		$this->instance->save_primary_term( 1, 'category' );
	}
}
