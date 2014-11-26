<?php

use app\statistics\models\Statistic;
use app\statistics\libs\StatisticsHelper;

class StatisticsTest extends \PHPUnit_Framework_TestCase
{
    public $s;
    public $s2;
    public $dbTouched;

    public static function setUpBeforeClass()
    {
        include 'TestMetric.php';
        include 'Test2Metric.php';
    }

    public function setUp()
    {
        $this->s = new TestMetric( TestBootstrap::app() );
        $this->s2 = new Test2Metric( TestBootstrap::app() );
    }

    public function tearDown()
    {
        if ($this->dbTouched) {
            $db = TestBootstrap::app('db');
            $db->delete('Statistics')->where('metric', $this->s->key())->execute();

            $db->delete('Statistics')->where('metric', $this->s2->key())->execute();
        }
    }

    public function testPrefix()
    {
        $this->assertEquals( '', $this->s->prefix() );
    }

    public function testSuffix()
    {
        $this->assertEquals( '', $this->s->suffix() );
    }

    public function testSpan()
    {
        $this->assertGreaterThan( 0, $this->s->span() );
    }

    public function testKey()
    {
        $this->assertEquals( 'test_statistic', $this->s->key() );
    }

    public function testComputeValue()
    {
        $period = $this->s->nthPreviousPeriod();
        $lastPeriod = $this->s->nthPreviousPeriod( 1 );

        for( $i = 0; $i < 10; $i++ )
            $this->assertEquals( 1000, $this->s->computeValue( $period ) );

        for( $i = 0; $i < 10; $i++ )
            $this->assertEquals( 1023, $this->s->computeValue( $lastPeriod ) );
    }

    public function testComputeDelta()
    {
        $this->assertEquals( -23, $this->s->computeDelta() );
    }

    public function testValues()
    {
        $this->dbTouched = true;

        // test volume
        $values = $this->s->values();
        // TODO

        // test flow
        $values = $this->s2->values();
        // TODO
    }

    public function testNeedsToBeCaptured()
    {
        $this->assertTrue( $this->s->needsToBeCaptured() );
    }

    /**
	 * @depends testNeedsToBeCaptured
	 */
    public function testCapture()
    {
        $this->dbTouched = true;

        $this->assertTrue( $this->s->savePeriod() );

        $this->assertFalse( $this->s->needsToBeCaptured() );
    }

    public function testToArray()
    {
        $expected = [
            'name' => 'Test Statistic',
            'key' => 'test_statistic',
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

    public function testNthPreviousPeriod()
    {
        $period = $this->s->nthPreviousPeriod();
        $period1 = $this->s->nthPreviousPeriod( 1 );
        $period2 = $this->s->nthPreviousPeriod( 2 );

        $this->assertTrue( $period != $period1 );
        $this->assertTrue( $period1 != $period2 );
    }

    public function testInterval()
    {
        $this->assertEquals( 'day', $this->s->interval( STATISTIC_GRANULARITY_DAY ) );
        $this->assertEquals( 'week', $this->s->interval( STATISTIC_GRANULARITY_WEEK ) );
        $this->assertEquals( 'month', $this->s->interval( STATISTIC_GRANULARITY_MONTH ) );
        $this->assertEquals( 'year', $this->s->interval( STATISTIC_GRANULARITY_YEAR ) );
    }

    public function testMetricClasses()
    {
        $classes = StatisticsHelper::metricClasses( TestBootstrap::app() );

        $this->assertGreaterThan( 2, count( $classes ) );

        foreach( $classes as $class )
            $this->assertInstanceOf( '\\app\\statistics\\metrics\\AbstractStat', $class );
    }

    public function testCaptureMetrics()
    {
        $this->assertTrue( StatisticsHelper::captureMetrics( TestBootstrap::app() ) );

        $this->assertGreaterThan( 0, Statistic::totalRecords() );
    }
}
