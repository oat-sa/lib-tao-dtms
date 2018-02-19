<?php

namespace oat\dtms\Test;

use oat\dtms\DateInterval;
use oat\dtms\DateTime;

class DateTimeTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        date_default_timezone_set('UTC');
    }

    public function tearDown()
    {
        // do nothing
    }

    public function invokeMethod(&$object, $methodName, array $parameters = array())
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }

    /**
     * @covers \oat\dtms\DateTime::getMicroseconds
     */
    public function testSetMicroseconds()
    {
        $dt = new DateTime();
        $dt->setMicroseconds(123456);
        $this->assertSame(123456, $dt->microseconds);
        $dt->setMicroseconds('123456');
        $this->assertSame(123456, $dt->microseconds);

        $dt->setMicroseconds(654);
        $this->assertSame(654, $dt->microseconds);

        $dt->setMicroseconds('987000');
        $this->assertSame(987000, $dt->microseconds);

        $dt->setMicroseconds('000123');
        $this->assertSame(123, $dt->microseconds);
    }

    /**
     * @covers \oat\dtms\DateTime::getMicroseconds
     */
    public function testGetMicroseconds()
    {
        $dt = new DateTime();
        $dt->microseconds = 123456;
        $this->assertSame(123456, $dt->getMicroseconds());
        $this->assertSame(0.123456, $dt->getMicroseconds(true));

        $dt->microseconds = 456;
        $this->assertSame(456, $dt->getMicroseconds());
        $this->assertSame(0.000456, $dt->getMicroseconds(true));
    }

    /**
     * @covers \oat\dtms\DateTime::createFromFormat
     */
    public function testCreateFromFormat()
    {
        $dt1 = new DateTime('2015-08-08 10:10:10.123456');
        $dt2 = DateTime::createFromFormat(DateTime::ISO8601, '2015-08-08T10:10:10.123456Z');

        $this->assertEquals($dt1, $dt2);
    }

    /**
     * @covers \oat\dtms\DateTime::__construct
     */
    public function testConstruct()
    {
        $dt = new DateTime();
        $this->assertInstanceOf('oat\\dtms\\DateTime', $dt);
        $this->assertObjectHasAttribute('microseconds', $dt);

        $dt = new DateTime('2015-08-08 10:10:10.123456');
        $this->assertSame(123456, $dt->getMicroseconds());
    }

    /**
     * @covers \oat\dtms\DateTime::getTimestampWithMicroseconds
     */
    public function testGetTimestampWithMicroseconds()
    {
        $dt = new DateTime('2015-08-08 10:10:10.123456');
        $this->assertSame(1439028610 + 123456 / 1e6, $dt->getTimestampWithMicroseconds());
    }

    /**
     * @covers \oat\dtms\DateTime::addMicroseconds
     */
    public function testAddMicroseconds()
    {
        $dt = new DateTime('2015-08-08 10:10:10.123456');
        $this->invokeMethod($dt, 'addMicroseconds', array(0));
        $this->assertEquals('1439028610.123456', $dt->format('U.u'));

        $dt = new DateTime('2015-08-08 10:10:10.123456');
        $this->invokeMethod($dt, 'addMicroseconds', array(123456));
        $this->assertEquals('1439028610.246912', $dt->format('U.u'));

        $dt = new DateTime('2015-08-08 10:10:10.123456');
        $this->invokeMethod($dt, 'addMicroseconds', array(999999));
        $this->assertEquals('1439028611.123455', $dt->format('U.u'));

        $dt = new DateTime('2015-08-08 10:10:10.123456');
        $this->invokeMethod($dt, 'addMicroseconds', array(876544));
        $this->assertEquals('1439028611.000000', $dt->format('U.u'));

        $dt = new DateTime('2015-08-08 10:10:10.123456');
        $this->invokeMethod($dt, 'addMicroseconds', array(1876544));
        $this->assertEquals('1439028612.000000', $dt->format('U.u'));

        $this->setExpectedException(
            'InvalidArgumentException', 'Value of microseconds should be positive.'
        );
        $dt = new DateTime('2015-08-08 10:10:10.123456');
        $this->invokeMethod($dt, 'addMicroseconds', array(-111111));
    }

    /**
     * @covers \oat\dtms\DateTime::subMicroseconds
     */
    public function testSubMicroseconds()
    {
        $dt = new DateTime('2015-08-08 10:10:10.123456');
        $this->invokeMethod($dt, 'subMicroseconds', array(0));
        $this->assertEquals('1439028610.123456', $dt->format('U.u'));

        $dt = new DateTime('2015-08-08 10:10:10.123456');
        $this->invokeMethod($dt, 'subMicroseconds', array(12345));
        $this->assertEquals('1439028610.111111', $dt->format('U.u'));

        $dt = new DateTime('2015-08-08 10:10:10.123456');
        $this->invokeMethod($dt, 'subMicroseconds', array(654321));
        $this->assertEquals('1439028609.469135', $dt->format('U.u'));

        $dt = new DateTime('2015-08-08 10:10:10.123456');
        $this->invokeMethod($dt, 'subMicroseconds', array(123456));
        $this->assertEquals('1439028610.000000', $dt->format('U.u'));

        $dt = new DateTime('2015-08-08 10:10:10.123456');
        $this->invokeMethod($dt, 'subMicroseconds', array(1123456));
        $this->assertEquals('1439028609.000000', $dt->format('U.u'));

        $this->setExpectedException(
            'InvalidArgumentException', 'Value of microseconds should be positive.'
        );
        $dt = new DateTime('2015-08-08 10:10:10.123456');
        $this->invokeMethod($dt, 'subMicroseconds', array(-111111));
    }

    /**
     * @covers \oat\dtms\DateTime::add
     */
    public function testAdd()
    {
        $dt = new DateTime('2015-08-08 10:10:10.123456');
        $dt->add(new DateInterval('PT0.000000S'));
        $this->assertEquals('1439028610.123456', $dt->format('U.u'));

        $dt = new DateTime('2015-08-08 10:10:10.123456');
        $dt->add(new DateInterval('PT1.123456S'));
        $this->assertEquals('1439028611.246912', $dt->format('U.u'));

        $dt = new DateTime('2015-08-08 10:10:10.123456');
        $dt->add(new DateInterval('PT1.999999S'));
        $this->assertEquals('1439028612.123455', $dt->format('U.u'));

        $dt = new DateTime('2015-08-08 10:10:10.123456');
        $dt->add(new DateInterval('PT1.876544S'));
        $this->assertEquals('1439028612.000000', $dt->format('U.u'));


        $dt = new DateTime('2015-08-08 10:10:10.123456');
        $dt->add(new DateInterval('-PT0.000000S'));
        $this->assertEquals('1439028610.123456', $dt->format('U.u'));

        $dt = new DateTime('2015-08-08 10:10:10.123456');
        $dt->add(new DateInterval('-PT1.123456S'));
        $this->assertEquals('1439028609.000000', $dt->format('U.u'));

        $dt = new DateTime('2015-08-08 10:10:10.123456');
        $dt->add(new DateInterval('-PT1.999999S'));
        $this->assertEquals('1439028608.123457', $dt->format('U.u'));

        $dt = new DateTime('2015-08-08 10:10:10.123456');
        $dt->add(new DateInterval('-PT1.876544S'));
        $this->assertEquals('1439028608.246912', $dt->format('U.u'));
    }

    /**
     * @covers \oat\dtms\DateTime::sub
     */
    public function testSub()
    {
        $dt = new DateTime('2015-08-08 10:10:10.123456');
        $dt->sub(new DateInterval('PT0.000000S'));
        $this->assertEquals('1439028610.123456', $dt->format('U.u'));

        $dt = new DateTime('2015-08-08 10:10:10.123456');
        $dt->sub(new DateInterval('PT1.123456S'));
        $this->assertEquals('1439028609.000000', $dt->format('U.u'));

        $dt = new DateTime('2015-08-08 10:10:10.123456');
        $dt->sub(new DateInterval('PT1.999999S'));
        $this->assertEquals('1439028608.123457', $dt->format('U.u'));

        $dt = new DateTime('2015-08-08 10:10:10.123456');
        $dt->sub(new DateInterval('PT1.876544S'));
        $this->assertEquals('1439028608.246912', $dt->format('U.u'));


        $dt = new DateTime('2015-08-08 10:10:10.123456');
        $dt->sub(new DateInterval('-PT0.000000S'));
        $this->assertEquals('1439028610.123456', $dt->format('U.u'));

        $dt = new DateTime('2015-08-08 10:10:10.123456');
        $dt->sub(new DateInterval('-PT1.123456S'));
        $this->assertEquals('1439028611.246912', $dt->format('U.u'));

        $dt = new DateTime('2015-08-08 10:10:10.123456');
        $dt->sub(new DateInterval('-PT1.999999S'));
        $this->assertEquals('1439028612.123455', $dt->format('U.u'));

        $dt = new DateTime('2015-08-08 10:10:10.123456');
        $dt->sub(new DateInterval('-PT1.876544S'));
        $this->assertEquals('1439028612.000000', $dt->format('U.u'));
    }

    /**
     * @covers \oat\dtms\DateTime::modify
     */
    public function testModify()
    {
        $dt = new DateTime('2015-08-08 10:10:10.123456');
        $dt->modify('+10 microseconds');
        $this->assertEquals('1439028610.123466', $dt->format('U.u'));

        $dt = new DateTime('2015-08-08 10:10:10.123456');
        $dt->modify('+10 microsecond');
        $this->assertEquals('1439028610.123466', $dt->format('U.u'));

        $dt = new DateTime('2015-08-08 10:10:10.123456');
        $dt->modify('+10 micro');
        $this->assertEquals('1439028610.123466', $dt->format('U.u'));

        $dt = new DateTime('2015-08-08 10:10:10.123456');
        $dt->modify('+10 mic');
        $this->assertEquals('1439028610.123466', $dt->format('U.u'));

        $dt = new DateTime('2015-08-08 10:10:10.123456');
        $dt->modify('+10microseconds');
        $this->assertEquals('1439028610.123466', $dt->format('U.u'));

        $dt = new DateTime('2015-08-08 10:10:10.123456');
        $dt->modify('+10microsecond');
        $this->assertEquals('1439028610.123466', $dt->format('U.u'));

        $dt = new DateTime('2015-08-08 10:10:10.123456');
        $dt->modify('+10micro');
        $this->assertEquals('1439028610.123466', $dt->format('U.u'));

        $dt = new DateTime('2015-08-08 10:10:10.123456');
        $dt->modify('+10mic');
        $this->assertEquals('1439028610.123466', $dt->format('U.u'));

        $dt = new DateTime('2015-08-08 10:10:10.123456');
        $dt->modify('-10 microseconds');
        $this->assertEquals('1439028610.123446', $dt->format('U.u'));

        $dt = new DateTime('2015-08-08 10:10:10.123456');
        $dt->modify('-10 microsecond');
        $this->assertEquals('1439028610.123446', $dt->format('U.u'));

        $dt = new DateTime('2015-08-08 10:10:10.123456');
        $dt->modify('-10 micro');
        $this->assertEquals('1439028610.123446', $dt->format('U.u'));

        $dt = new DateTime('2015-08-08 10:10:10.123456');
        $dt->modify('-10 mic');
        $this->assertEquals('1439028610.123446', $dt->format('U.u'));

        $dt = new DateTime('2015-08-08 10:10:10.123456');
        $dt->modify('+999999 micro');
        $this->assertEquals('1439028611.123455', $dt->format('U.u'));

        $dt = new DateTime('2015-08-08 10:10:10.123456');
        $dt->modify('-999999 micro');
        $this->assertEquals('1439028609.123457', $dt->format('U.u'));

        $dt = new DateTime('2015-08-08 10:10:10.123456');
        $dt->modify('+1999999 micro');
        $this->assertEquals('1439028612.123455', $dt->format('U.u'));

        $dt = new DateTime('2015-08-08 10:10:10.123456');
        $dt->modify('-1999999 micro');
        $this->assertEquals('1439028608.123457', $dt->format('U.u'));

        $dt = new DateTime('2015-08-08 10:10:10.123456');
        $dt->modify('+10 min +10 seconds +123456 micro');
        $this->assertEquals('1439029220.246912', $dt->format('U.u'));

        $dt = new DateTime('2015-08-08 10:10:10.123456');
        $dt->modify('-10 min -10 seconds -123456 micro');
        $this->assertEquals('1439028000.000000', $dt->format('U.u'));
    }

    /**
     * @covers \oat\dtms\DateTime::diff
     */
    public function testDiff()
    {
        // Using oat\dtms\DateTime objects for both date 1 & date 2.
        $dt1 = new DateTime('2005-10-10 23:57:01.100000');
        $dt2 = new DateTime('2005-10-10 23:59:01.050000');
        $this->assertEquals('+PT1M59.950000S', $dt1->diff($dt2)->format('%RPT%iM%sS'));
        $this->assertEquals('-PT1M59.950000S', $dt2->diff($dt1)->format('%RPT%iM%sS'));
        
        $dt1 = new DateTime('2005-10-10 23:59:01.555554');
        $dt2 = new DateTime('2005-12-30 23:59:01.555555');
        $this->assertEquals('+P2M20DT0.000001S', $dt1->diff($dt2)->format('%RP%mM%dDT%sS'));
        $this->assertEquals('-P2M20DT0.000001S', $dt2->diff($dt1)->format('%RP%mM%dDT%sS'));
        
        $dt1 = new DateTime('2015-08-08 10:10:10.123456');
        $dt2 = new DateTime('2015-08-08 10:10:05.654321');
        $this->assertEquals('-PT4.469135S', $dt1->diff($dt2)->format('%RPT%sS'));
        $this->assertEquals('+PT4.469135S', $dt2->diff($dt1)->format('%RPT%sS'));

        $dt1 = new DateTime('2015-08-08 10:10:10.123456');
        $dt2 = new DateTime('2015-08-08 10:10:10.123455');
        $this->assertEquals('-PT0.000001S', $dt1->diff($dt2)->format('%RPT%sS'));
        $this->assertEquals('+PT0.000001S', $dt2->diff($dt1)->format('%RPT%sS'));

        $dt1 = new DateTime('2015-08-08 10:10:10.123456');
        $dt2 = new DateTime('2015-08-08 10:10:15.654321');
        $this->assertEquals('+PT5.530865S', $dt1->diff($dt2)->format('%RPT%sS'));
        $this->assertEquals('-PT5.530865S', $dt2->diff($dt1)->format('%RPT%sS'));

        $dt1 = new DateTime('2015-08-08 10:10:10.123456');
        $dt2 = new DateTime('2015-08-08 10:10:10.123456');
        $this->assertEquals('+PT0S', $dt1->diff($dt2)->format('%RPT%sS'));
        $this->assertEquals('+PT0S', $dt2->diff($dt1)->format('%RPT%sS'));

        $dt1 = new DateTime('2015-08-08 10:10:10.123456');
        $dt2 = new DateTime('2015-08-08 10:10:11.654321');
        $this->assertEquals('+PT1.530865S', $dt1->diff($dt2, true)->format('%RPT%sS'));
        $this->assertEquals('+PT1.530865S', $dt2->diff($dt1, true)->format('%RPT%sS'));

        $dt1 = new DateTime('2015-08-08 10:10:10.123456');
        $dt2 = new DateTime('2015-08-18 10:10:05.654321');
        $this->assertEquals('+P9DT23H59M55.530865S', $dt1->diff($dt2)->format('%RP%dDT%hH%iM%sS'));
        $this->assertEquals('-P9DT23H59M55.530865S', $dt2->diff($dt1)->format('%RP%dDT%hH%iM%sS'));

        $dt1 = new DateTime('2015-08-08 10:10:10.123456');
        $dt2 = new DateTime('2015-12-12 10:10:10.123456');
        $this->assertEquals('+P4M4DT0S', $dt1->diff($dt2)->format('%RP%mM%dDT%sS'));
        $this->assertEquals('-P4M4DT0S', $dt2->diff($dt1)->format('%RP%mM%dDT%sS'));

        $dt1 = new DateTime('2015-08-10 10:10:10.101010');
        $dt2 = new DateTime('2018-08-14 16:18:10.101010');
        $this->assertEquals('+P3Y0M4DT6H8M0S', $dt1->diff($dt2)->format('%RP%yY%mM%dDT%hH%iM%sS'));
        $this->assertEquals('-P3Y0M4DT6H8M0S', $dt2->diff($dt1)->format('%RP%yY%mM%dDT%hH%iM%sS'));

        $dt1 = new DateTime('1985-11-27 10:00:00.340000');
        $dt2 = new DateTime('1985-11-27 10:00:00.340000');
        $this->assertEquals('+P0Y0M0DT0H0M0S', $dt1->diff($dt2)->format('%RP%yY%mM%dDT%hH%iM%sS'));
        $this->assertEquals('+P0Y0M0DT0H0M0S', $dt2->diff($dt1)->format('%RP%yY%mM%dDT%hH%iM%sS'));

        $dt1 = new DateTime('1985-11-27 10:00:05.990000');
        $dt2 = new DateTime('1985-11-27 10:00:10.100000');
        $this->assertEquals('+P0Y0M0DT0H0M4.110000S', $dt1->diff($dt2)->format('%RP%yY%mM%dDT%hH%iM%sS'));
        $this->assertEquals('-P0Y0M0DT0H0M4.110000S', $dt2->diff($dt1)->format('%RP%yY%mM%dDT%hH%iM%sS'));

        $dt1 = new DateTime('1985-11-27 10:00:05.450000');
        $dt2 = new DateTime('1985-11-27 10:00:10.440000');
        $this->assertEquals('+P0Y0M0DT0H0M4.990000S', $dt1->diff($dt2)->format('%RP%yY%mM%dDT%hH%iM%sS'));
        $this->assertEquals('-P0Y0M0DT0H0M4.990000S', $dt2->diff($dt1)->format('%RP%yY%mM%dDT%hH%iM%sS'));

        $dt1 = new DateTime('1985-11-27 10:00:05.990000');
        $dt2 = new DateTime('1985-11-27 10:00:10.999999');
        $this->assertEquals('+P0Y0M0DT0H0M5.009999S', $dt1->diff($dt2)->format('%RP%yY%mM%dDT%hH%iM%sS'));
        $this->assertEquals('-P0Y0M0DT0H0M5.009999S', $dt2->diff($dt1)->format('%RP%yY%mM%dDT%hH%iM%sS'));

        $dt1 = new DateTime('1985-11-27 10:00:05.555555');
        $dt2 = new DateTime('2017-09-28 10:00:10.555556');
        $this->assertEquals('+P31Y10M1DT0H0M5.000001S', $dt1->diff($dt2)->format('%RP%yY%mM%dDT%hH%iM%sS'));
        $this->assertEquals('-P31Y10M1DT0H0M5.000001S', $dt2->diff($dt1)->format('%RP%yY%mM%dDT%hH%iM%sS'));

        // Using oat\dtms\DateTime objects as date 1 AND \DateTime as date 2.
        $dt1 = new DateTime('2005-10-10 23:57:01.100000');
        $dt2 = new \DateTime('2005-10-10 23:59:01');
        $this->assertEquals('+PT1M59.900000S', $dt1->diff($dt2)->format('%RPT%iM%sS'));
        $this->assertEquals('-PT2M0S', $dt2->diff($dt1)->format('%RPT%iM%sS'));

        $dt1 = new DateTime('2005-10-10 23:59:01.555554');
        $dt2 = new \DateTime('2005-12-30 23:59:01');
        $this->assertEquals('+P2M19DT59.444446S', $dt1->diff($dt2)->format('%RP%mM%dDT%sS'));
        $this->assertEquals('-P2M20DT0S', $dt2->diff($dt1)->format('%RP%mM%dDT%sS'));

        $dt1 = new DateTime('2015-08-08 10:10:10.123456');
        $dt2 = new \DateTime('2015-08-08 10:10:05');
        $this->assertEquals('-PT5.123456S', $dt1->diff($dt2)->format('%RPT%sS'));
        $this->assertEquals('+PT5S', $dt2->diff($dt1)->format('%RPT%sS'));

        $dt1 = new DateTime('2015-08-08 10:10:10.123456');
        $dt2 = new \DateTime('2015-08-08 10:10:15');
        $this->assertEquals('+PT4.876544S', $dt1->diff($dt2)->format('%RPT%sS'));
        $this->assertEquals('-PT5S', $dt2->diff($dt1)->format('%RPT%sS'));

        $dt1 = new DateTime('2015-08-08 10:10:10.123456');
        $dt2 = new \DateTime('2015-08-08 10:10:10');
        $this->assertEquals('-PT0.123456S', $dt1->diff($dt2)->format('%RPT%sS'));
        $this->assertEquals('+PT0S', $dt2->diff($dt1)->format('%RPT%sS'));

        $dt1 = new DateTime('2015-08-08 10:10:10.123456');
        $dt2 = new \DateTime('2015-08-08 10:10:11');
        $this->assertEquals('+PT0.876544S', $dt1->diff($dt2, true)->format('%RPT%sS'));
        $this->assertEquals('+PT1S', $dt2->diff($dt1, true)->format('%RPT%sS'));

        $dt1 = new DateTime('2015-08-08 10:10:10.123456');
        $dt2 = new \DateTime('2015-08-18 10:10:05');
        $this->assertEquals('+P9DT23H59M54.876544S', $dt1->diff($dt2)->format('%RP%dDT%hH%iM%sS'));
        $this->assertEquals('-P9DT23H59M55S', $dt2->diff($dt1)->format('%RP%dDT%hH%iM%sS'));

        $dt1 = new DateTime('2015-08-08 10:10:10.123456');
        $dt2 = new \DateTime('2015-12-12 10:10:10');
        $this->assertEquals('+P4M3DT23H59M59.876544S', $dt1->diff($dt2)->format('%RP%mM%dDT%hH%iM%sS'));
        $this->assertEquals('-P4M4DT0S', $dt2->diff($dt1)->format('%RP%mM%dDT%sS'));

        $dt1 = new DateTime('2015-08-10 10:10:10.101010');
        $dt2 = new \DateTime('2018-08-14 16:18:10');
        $this->assertEquals('+P3Y0M4DT6H7M59.898990S', $dt1->diff($dt2)->format('%RP%yY%mM%dDT%hH%iM%sS'));
        $this->assertEquals('-P3Y0M4DT6H8M0S', $dt2->diff($dt1)->format('%RP%yY%mM%dDT%hH%iM%sS'));

        $dt1 = new DateTime('1985-11-27 10:00:00.340000');
        $dt2 = new \DateTime('1985-11-27 10:00:00');
        $this->assertEquals('-P0Y0M0DT0H0M0.340000S', $dt1->diff($dt2)->format('%RP%yY%mM%dDT%hH%iM%sS'));
        $this->assertEquals('+P0Y0M0DT0H0M0S', $dt2->diff($dt1)->format('%RP%yY%mM%dDT%hH%iM%sS'));

        $dt1 = new DateTime('1985-11-27 10:00:05.990000');
        $dt2 = new \DateTime('1985-11-27 10:00:10');
        $this->assertEquals('+P0Y0M0DT0H0M4.010000S', $dt1->diff($dt2)->format('%RP%yY%mM%dDT%hH%iM%sS'));
        $this->assertEquals('-P0Y0M0DT0H0M5S', $dt2->diff($dt1)->format('%RP%yY%mM%dDT%hH%iM%sS'));

        $dt1 = new DateTime('1985-11-27 10:00:05.450000');
        $dt2 = new \DateTime('1985-11-27 10:00:10');
        $this->assertEquals('+P0Y0M0DT0H0M4.550000S', $dt1->diff($dt2)->format('%RP%yY%mM%dDT%hH%iM%sS'));
        $this->assertEquals('-P0Y0M0DT0H0M5S', $dt2->diff($dt1)->format('%RP%yY%mM%dDT%hH%iM%sS'));

        $dt1 = new DateTime('1985-11-27 10:00:05.990000');
        $dt2 = new \DateTime('1985-11-27 10:00:10');
        $this->assertEquals('+P0Y0M0DT0H0M4.010000S', $dt1->diff($dt2)->format('%RP%yY%mM%dDT%hH%iM%sS'));
        $this->assertEquals('-P0Y0M0DT0H0M5S', $dt2->diff($dt1)->format('%RP%yY%mM%dDT%hH%iM%sS'));

        $dt1 = new DateTime('1985-11-27 10:00:05.555555');
        $dt2 = new \DateTime('2017-09-28 10:00:10');
        $this->assertEquals('+P31Y10M1DT0H0M4.444445S', $dt1->diff($dt2)->format('%RP%yY%mM%dDT%hH%iM%sS'));
        $this->assertEquals('-P31Y10M1DT0H0M5S', $dt2->diff($dt1)->format('%RP%yY%mM%dDT%hH%iM%sS'));

        // Using DateTime objects as date 1 AND oat\dtms\DateTime as date 2.
        $dt1 = new \DateTime('2005-10-10 23:57:01');
        $dt2 = new DateTime('2005-10-10 23:59:01.050000');
        $this->assertEquals('+PT2M0S', $dt1->diff($dt2)->format('%RPT%iM%sS'));
        $this->assertEquals('-PT2M0.050000S', $dt2->diff($dt1)->format('%RPT%iM%sS'));

        $dt1 = new \DateTime('2005-10-10 23:59:01');
        $dt2 = new DateTime('2005-12-30 23:59:01.555555');
        $this->assertEquals('+P2M20DT0S', $dt1->diff($dt2)->format('%RP%mM%dDT%sS'));
        $this->assertEquals('-P2M20DT0.555555S', $dt2->diff($dt1)->format('%RP%mM%dDT%sS'));

        $dt1 = new \DateTime('2015-08-08 10:10:10');
        $dt2 = new DateTime('2015-08-08 10:10:05.654321');
        $this->assertEquals('-PT5S', $dt1->diff($dt2)->format('%RPT%sS'));
        $this->assertEquals('+PT4.345679S', $dt2->diff($dt1)->format('%RPT%sS'));

        $dt1 = new \DateTime('2015-08-08 10:10:10');
        $dt2 = new DateTime('2015-08-08 10:10:10.123455');
        $this->assertEquals('+PT0S', $dt1->diff($dt2)->format('%RPT%sS'));
        $this->assertEquals('-PT0.123455S', $dt2->diff($dt1)->format('%RPT%sS'));

        $dt1 = new \DateTime('2015-08-08 10:10:10');
        $dt2 = new DateTime('2015-08-08 10:10:15.654321');
        $this->assertEquals('+PT5S', $dt1->diff($dt2)->format('%RPT%sS'));
        $this->assertEquals('-PT5.654321S', $dt2->diff($dt1)->format('%RPT%sS'));

        $dt1 = new \DateTime('2015-08-08 10:10:10');
        $dt2 = new DateTime('2015-08-08 10:10:10.123456');
        $this->assertEquals('+PT0S', $dt1->diff($dt2)->format('%RPT%sS'));
        $this->assertEquals('-PT0.123456S', $dt2->diff($dt1)->format('%RPT%sS'));

        $dt1 = new \DateTime('2015-08-08 10:10:10');
        $dt2 = new DateTime('2015-08-08 10:10:11.654321');
        $this->assertEquals('+PT1S', $dt1->diff($dt2, true)->format('%RPT%sS'));
        $this->assertEquals('+PT1.654321S', $dt2->diff($dt1, true)->format('%RPT%sS'));

        $dt1 = new \DateTime('2015-08-08 10:10:10');
        $dt2 = new DateTime('2015-08-18 10:10:05.654321');
        $this->assertEquals('+P9DT23H59M55S', $dt1->diff($dt2)->format('%RP%dDT%hH%iM%sS'));
        $this->assertEquals('-P9DT23H59M55.654321S', $dt2->diff($dt1)->format('%RP%dDT%hH%iM%sS'));

        $dt1 = new \DateTime('2015-08-08 10:10:10');
        $dt2 = new DateTime('2015-12-12 10:10:10.123456');
        $this->assertEquals('+P4M4DT0S', $dt1->diff($dt2)->format('%RP%mM%dDT%sS'));
        $this->assertEquals('-P4M4DT0.123456S', $dt2->diff($dt1)->format('%RP%mM%dDT%sS'));

        $dt1 = new \DateTime('2015-08-10 10:10:10');
        $dt2 = new DateTime('2018-08-14 16:18:10.101010');
        $this->assertEquals('+P3Y0M4DT6H8M0S', $dt1->diff($dt2)->format('%RP%yY%mM%dDT%hH%iM%sS'));
        $this->assertEquals('-P3Y0M4DT6H8M0.101010S', $dt2->diff($dt1)->format('%RP%yY%mM%dDT%hH%iM%sS'));

        $dt1 = new \DateTime('1985-11-27 10:00:00');
        $dt2 = new DateTime('1985-11-27 10:00:00.340000');
        $this->assertEquals('+P0Y0M0DT0H0M0S', $dt1->diff($dt2)->format('%RP%yY%mM%dDT%hH%iM%sS'));
        $this->assertEquals('-P0Y0M0DT0H0M0.340000S', $dt2->diff($dt1)->format('%RP%yY%mM%dDT%hH%iM%sS'));

        $dt1 = new \DateTime('1985-11-27 10:00:05');
        $dt2 = new DateTime('1985-11-27 10:00:10.100000');
        $this->assertEquals('+P0Y0M0DT0H0M5S', $dt1->diff($dt2)->format('%RP%yY%mM%dDT%hH%iM%sS'));
        $this->assertEquals('-P0Y0M0DT0H0M5.100000S', $dt2->diff($dt1)->format('%RP%yY%mM%dDT%hH%iM%sS'));

        $dt1 = new \DateTime('1985-11-27 10:00:05');
        $dt2 = new DateTime('1985-11-27 10:00:10.440000');
        $this->assertEquals('+P0Y0M0DT0H0M5S', $dt1->diff($dt2)->format('%RP%yY%mM%dDT%hH%iM%sS'));
        $this->assertEquals('-P0Y0M0DT0H0M5.440000S', $dt2->diff($dt1)->format('%RP%yY%mM%dDT%hH%iM%sS'));

        $dt1 = new \DateTime('1985-11-27 10:00:05.990000');
        $dt2 = new DateTime('1985-11-27 10:00:10.999999');
        $this->assertEquals('+P0Y0M0DT0H0M5S', $dt1->diff($dt2)->format('%RP%yY%mM%dDT%hH%iM%sS'));
        $this->assertEquals('-P0Y0M0DT0H0M5.009999S', $dt2->diff($dt1)->format('%RP%yY%mM%dDT%hH%iM%sS'));

        $dt1 = new \DateTime('1985-11-27 10:00:05.555555');
        $dt2 = new DateTime('2017-09-28 10:00:10.555556');
        $this->assertEquals('+P31Y10M1DT0H0M5S', $dt1->diff($dt2)->format('%RP%yY%mM%dDT%hH%iM%sS'));
        $this->assertEquals('-P31Y10M1DT0H0M5.000001S', $dt2->diff($dt1)->format('%RP%yY%mM%dDT%hH%iM%sS'));
    }

    public function testDiffInvalidArgument() {
        $this->setExpectedException('\InvalidArgumentException');
        $dt1 = new DateTime('1985-11-27 10:00:05.990000');
        $diff = $dt1->diff(new \stdClass());
    }

    /**
     * @covers \oat\dtms\DateTime::__toString
     */
    public function testToString()
    {
        $dt = new DateTime('2015-08-08 10:10:10.123456');
        $this->assertSame('2015-08-08T10:10:10.123456Z', '' . $dt);

        $dt->setMicroseconds(456);
        $this->assertSame('2015-08-08T10:10:10.000456Z', '' . $dt);

        $dt->setMicroseconds(101010);
        $this->assertSame('2015-08-08T10:10:10.101010Z', '' . $dt);
    }

    /**
     * @covers \oat\dtms\DateTime::format
     */
    public function testFormat()
    {
        $dt = new DateTime('2015-08-08 10:10:10.123456');
        $this->assertSame('08.08.2015 10:10:10.123456', $dt->format('d.m.Y H:i:s.u'));
        $this->assertSame('08.08.2015 10:10:10', $dt->format('d.m.Y H:i:s'));
        $this->assertSame('1439028610.123456', $dt->format('U.u'));
    }
}
