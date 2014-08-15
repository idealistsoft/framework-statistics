<?php

use infuse\Database;

use app\users\models\User;
use app\statistics\models\Statistic;
use app\statistics\libs\StatisticsHelper;
use app\statistics\tests\TestMetric;
use app\statistics\tests\Test2Metric;

class StatisticsTest extends \PHPUnit_Framework_TestCase
{
	var $s;
	var $s2;
	var $dbTouched;

	function setUp()
	{
		$this->s = new TestMetric( TestBootstrap::app() );
		$this->s2 = new Test2Metric( TestBootstrap::app() );
	}

	function tearDown()
	{
		if( $this->dbTouched )
		{
			Database::delete( 'Statistics', [ 'metric' => $this->s->key() ] );

			Database::delete( 'Statistics', [ 'metric' => $this->s2->key() ] );
		}
	}

	function testPrefix()
	{
		$this->assertEquals( '', $this->s->prefix() );
	}

	function testSuffix()
	{
		$this->assertEquals( '', $this->s->suffix() );
	}

	function testSpan()
	{
		$this->assertGreaterThan( 0, $this->s->span() );
	}

	function testKey()
	{
		$this->assertEquals( 'test-statistic', $this->s->key() );
	}

	function testComputeValue()
	{
		$period = $this->s->nthPreviousPeriod();
		$lastPeriod = $this->s->nthPreviousPeriod( 1 );

		for( $i = 0; $i < 10; $i++ )
			$this->assertEquals( 1000, $this->s->computeValue( $period ) );

		for( $i = 0; $i < 10; $i++ )
			$this->assertEquals( 1023, $this->s->computeValue( $lastPeriod ) );
	}

	function testComputeDelta()
	{
		$this->assertEquals( -23, $this->s->computeDelta() );
	}

	function testValues()
	{
		$this->dbTouched = true;

		// test volume
		$values = $this->s->values();
		// TODO

		// test flow
		$values = $this->s2->values();
		// TODO
	}

	function testNeedsToBeCaptured()
	{
		$this->assertTrue( $this->s->needsToBeCaptured() );
	}

	/**
	 * @depends testNeedsToBeCaptured
	 */
	function testCapture()
	{
		$this->dbTouched = true;

		$this->assertTrue( $this->s->savePeriod() );

		$this->assertFalse( $this->s->needsToBeCaptured() );
	}

	function testToArray()
	{
		$expected = [
			'name' => 'Test Statistic',
			'key' => 'test-statistic',
			'granularity' => STATISTIC_GRANULARITY_DAY,
			'prefix' => '',
			'suffix' => '',
			'hasChart' => true,
			'span' => 4,
			'value' => 1000,
			'abbreviated_value' => '1K',
			'delta' => -23,
			'abbreviated_delta' => -23
		];

		$this->assertEquals( $expected, $this->s->toArray() );
	}

	function testNthPreviousPeriod()
	{
		$period = $this->s->nthPreviousPeriod();
		$period1 = $this->s->nthPreviousPeriod( 1 );
		$period2 = $this->s->nthPreviousPeriod( 2 );

		$this->assertTrue( $period != $period1 );
		$this->assertTrue( $period1 != $period2 );
	}

	function testInterval()
	{
		$this->assertEquals( 'day', $this->s->interval( STATISTIC_GRANULARITY_DAY ) );
		$this->assertEquals( 'week', $this->s->interval( STATISTIC_GRANULARITY_WEEK ) );
		$this->assertEquals( 'month', $this->s->interval( STATISTIC_GRANULARITY_MONTH ) );
		$this->assertEquals( 'year', $this->s->interval( STATISTIC_GRANULARITY_YEAR ) );
	}

	function testMetricClasses()
	{
		$classes = StatisticsHelper::metricClasses( TestBootstrap::app() );

		$this->assertGreaterThan( 2, count( $classes ) );

		foreach( $classes as $class )
			$this->assertInstanceOf( '\\app\\statistics\\metrics\\AbstractStat', $class );
	}

	function testCaptureMetrics()
	{
		$this->assertTrue( StatisticsHelper::captureMetrics( TestBootstrap::app() ) );

		$this->assertGreaterThan( 0, Statistic::totalRecords() );
	}
}