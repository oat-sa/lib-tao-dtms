<?php

/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2022 (original work) Open Assessment Technologies SA.
 */

namespace oat\dtms;

use DateTimeZone;
use DateTime as BaseDateTime;
use InvalidArgumentException;
use DateInterval as BaseDateInterval;

class DateTime extends BaseDateTime
{
    /**
     * Improved ISO8601 format string with support of microseconds.
     */
    public const ISO8601 = 'Y-m-d\TH:i:s.u\Z';

    /**
     * @var int Current number of microseconds.
     */
    public $microseconds;

    /**
     * Sets microseconds data to object.
     *
     * @param $microseconds
     */
    public function setMicroseconds($microseconds)
    {
        $this->microseconds = intval($microseconds);
    }

    /**
     * Gets microseconds data from object
     *
     * @param boolean $asSeconds If defined, microseconds will be converted to seconds with fractions
     * @return int|float
     */
    public function getMicroseconds($asSeconds = false)
    {
        if ($asSeconds) {
            return round($this->microseconds * 1/1e6, 6);
        }

        return intval($this->microseconds);
    }

    /**
     * Parse a string into a new DateTime object according to the specified format
     *
     * @param string $format
     * @param string $time
     * @param null $timezone
     *
     * @return DateTime|BaseDateTime
     */
    public static function createFromFormat($format, $time, $timezone = null)
    {
        if ($timezone === null) {
            $timezone = new DateTimeZone(date_default_timezone_get());
        }

        $datetime = BaseDateTime::createFromFormat($format, $time, $timezone);

        return new self($datetime->format(DateTime::ISO8601), $timezone);
    }

    /**
     * Instantiates custom DateTime object with support of microseconds.
     *
     * @param string $time
     * @param DateTimeZone|null $timezone
     */
    public function __construct($time = 'now', DateTimeZone $timezone = null)
    {
        if ($timezone === null) {
            $timezone = new DateTimeZone(date_default_timezone_get());
        }

        $nativeTime = new BaseDateTime($time, $timezone);
        [$u, $s] = $time == 'now'
            ? explode(' ', microtime())
            : array(
                $nativeTime->format('u') / 1e6,
                $nativeTime->getTimestamp()
            );

        $time = BaseDateTime::createFromFormat('U.u', join('.', array($s, str_replace('0.', '', sprintf('%6f', $u)))));
        $this->microseconds = $time->format('u') ?: 0;

        parent::__construct($time->format(static::ISO8601), $timezone);
    }

    /**
     * Gets the Unix timestamp in seconds with microseconds.
     *
     * @return int
     */
    public function getTimestampWithMicroseconds()
    {
        return $this->getTimestamp() + $this->getMicroseconds(true);
    }

    /**
     * Subtracts an amount of microseconds from a DateTime object
     *
     * @param $microseconds
     */
    protected function addMicroseconds($microseconds)
    {
        if ($microseconds < 0) {
            throw new InvalidArgumentException('Value of microseconds should be positive.');
        }

        $diff = $this->getMicroseconds() + $microseconds;
        $seconds = floor($diff / 1e6);
        $diff -= $seconds * 1e6;

        if ($diff >= 1e6) {
            $diff -= 1e6;
            $seconds++;
        }

        $this->modify("+$seconds seconds");
        $this->setMicroseconds($diff);
    }

    /**
     * Adds an amount of microseconds to a DateTime object
     *
     * @param $microseconds
     */
    protected function subMicroseconds($microseconds)
    {
        if ($microseconds < 0) {
            throw new InvalidArgumentException('Value of microseconds should be positive.');
        }

        $diff = $this->getMicroseconds() - $microseconds;
        $seconds = floor($diff / 1e6);
        $diff -= $seconds * 1e6;

        if ($diff < 0) {
            $diff = abs($diff);
            $seconds++;
        }

        $this->modify("$seconds seconds");
        $this->setMicroseconds($diff);
    }

    /**
     * Adds an amount of days, months, years, hours, minutes, seconds and microseconds to a DateTime object
     *
     * @param DateInterval $interval
     * @return DateTime $this
     */
    public function add($interval)
    {
        parent::add($interval);

        if ($interval instanceof DateInterval) {
            if ($interval->invert) { // is negative, then sub
                $this->subMicroseconds($interval->u);
            } else {
                $this->addMicroseconds($interval->u);
            }
        }

        return $this;
    }

    /**
     * Subtracts an amount of days, months, years, hours, minutes and seconds from a DateTime object
     *
     * @param DateInterval $interval
     * @return DateTime $this
     */
    public function sub($interval)
    {
        parent::sub($interval);

        if ($interval instanceof DateInterval) {
            if ($interval->invert) {
                $this->addMicroseconds($interval->u);
            } else {
                $this->subMicroseconds($interval->u);
            }
        }

        return $this;
    }

    /**
     * Alter the timestamp of a DateTime object by incrementing or decrementing in a format accepted by strtotime().
     * Added support for microseconds: (+|-)10 mic|micro|microsecond|microseconds
     *
     * @param string $modify
     *
     * @return BaseDateTime
     */
    public function modify($modify)
    {
        if (preg_match('/(\+|-)([0-9]+)(?:\s?)(?:microseconds|microsecond|micro|mic)$/', $modify, $matches)) {
            $modify = str_replace($matches[0], '', $modify);
            $number = intval($matches[2]);
            switch ($matches[1]) {
                case '-':
                    $this->subMicroseconds($number);
                    break;
                case '+':
                    $this->addMicroseconds($number);
                    break;
            }
        }

        if (!$modify) {
            $modify = '0 seconds';
        }

        return parent::modify($modify);
    }

    /**
     * Returns the difference between two DateTime objects represented as a DateInterval.
     *
     * @param BaseDateTime $datetime
     * @param bool|false $absolute
     *
     * @throws InvalidArgumentException In case of $datetime is not a DateTime nor a oat\dtms\DateTime object.
     * @return DateInterval
     */
    public function diff($datetime, $absolute = false): BaseDateInterval
    {
        $d1 = clone $this;

        if ($datetime instanceof BaseDateTime) {
            $d2 = new static($datetime->format(DateTime::ISO8601));
        } elseif ($datetime instanceof DateTime) {
            $d2 = clone $datetime;
        } else {
            throw new InvalidArgumentException('First Argument must be an instance of DateTime or oat\dtms\DateTime');
        }

        $d1Ts = $d1->getTimestampWithMicroseconds();
        $d2Ts = $d2->getTimestampWithMicroseconds();
        $negative = $d1Ts > $d2Ts;

        $d1s = intval($d1Ts);
        $d2s = intval($d2Ts);
        $d1u = round($d1Ts - $d1s, 6);
        $d2u = round($d2Ts - $d2s, 6);

        if (!$negative) {
            $comparison = $d2u < $d1u;
            $u = $d2u - $d1u;
        } else {
            $comparison = $d2u > $d1u;
            $u = $d1u - $d2u;
        }

        if ($u < 0) {
            $u += 1;
        }

        if ($comparison) {
            $d2->modify(($negative ? '+' : '-') . '1 second');
        }

        $interval = new DateInterval('PT0.000000S');
        foreach (get_object_vars(parent::diff($d2)) as $property => $value) {
            $interval->{$property} = $value;
        }


        $interval->u = round($u, 6) * 1e6;
        $interval->invert = $absolute ? false : $negative;;

        return $interval;
    }

    /**
     * Returns date formatted according to given format.
     *
     * @param string $format
     */
    public function format($format): string
    {
        $format = str_replace('u', sprintf('%06d', $this->microseconds), $format);

        return parent::format($format);
    }

    /**
     * Converts DateTime object to string using ISO8601 format.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->format(static::ISO8601);
    }
}
