<?php
declare(strict_types = 1);

namespace Dagr\Clock;

use Dagr\LocalTime;
use Dagr\Math;

final class Instant
{
    private const MIN_SECOND = -31557014167219200;
    private const MAX_SECOND = 31556889864403199;

    /**
     * @var int
     */
    private $seconds;

    /**
     * @var int
     */
    private $nanos;

    /**
     * @var self
     */
    private static $epoch;

    /**
     * @var self
     */
    private static $min;

    /**
     * @var self
     */
    private static $max;

    private function __construct(int $seconds, int $nanos)
    {
        $this->seconds = $seconds;
        $this->nanos = $nanos;
    }

    public static function now() : self
    {
        return SystemClock::utc()->getInstant();
    }

    public static function nowWith(ClockInterface $clock) : self
    {
        return $clock->getInstant();
    }

    public static function ofEpochSecond(int $epochSecond, int $nanoAdjustment = 0) : self
    {
        $seconds = Math::addExact($epochSecond, Math::floorDiv($nanoAdjustment, LocalTime::NANOS_PER_SECOND));
        $nanos = Math::floorMod($nanoAdjustment, LocalTime::NANOS_PER_SECOND);
        return self::create($seconds, $nanos);
    }

    public static function ofEpochMilli(int $epochMilli) : self
    {
        $seconds = Math::floorDiv($epochMilli, 1000);
        $millis = Math::floorMod($epochMilli, 1000);
        return self::create($seconds, $millis * 1000000);
    }

    private static function create(int $seconds, int $nanoOfSecond) : self
    {
        if (0 === ($seconds | $nanoOfSecond)) {
            return self::epoch();
        }

        if ($seconds < self::MIN_SECOND || $seconds > self::MAX_SECOND) {
            // @todo throw exception: Instant exceeds minimum or maximum instant
        }

        return new self($seconds, $nanoOfSecond);
    }

    public function getEpochSecond() : int
    {
        return $this->seconds;
    }

    public function getNano() : int
    {
        return $this->nanos;
    }

    public static function epoch() : self
    {
        return self::$epoch ?: self::$epoch = new self(0, 0);
    }

    public static function min() : self
    {
        return self::$min ?: self::$min = new self(self::MIN_SECOND, 0);
    }

    public static function max() : self
    {
        return self::$max ?: self::$max = new self(self::MAX_SECOND, 999999999);
    }
}
