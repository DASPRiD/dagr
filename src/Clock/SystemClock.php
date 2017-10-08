<?php
declare(strict_types = 1);

namespace Dagr\Clock;

use Dagr\LocalTime;
use Dagr\TimeZone\AbstractTimeZoneId;
use Dagr\TimeZone\TimeZoneOffset;

final class SystemClock implements ClockInterface
{
    /**
     * @var AbstractTimeZoneId
     */
    private $zone;

    /**
     * @var self
     */
    private static $utc;

    /**
     * @var self
     */
    private static $defaultTimeZone;

    public function __construct(AbstractTimeZone $zone)
    {
        $this->zone = $zone;
    }

    public static function utc() : self
    {
        return self::$utc ?: self::$utc = new self(TimeZoneOffset::utc());
    }

    public static function defaultTimeZone() : self
    {
        return self::$defaultTimeZone ?: self::$defaultTimeZone = new self(AbstractTimeZone::systemDefault());
    }

    public function getZone() : AbstractTimeZoneId
    {
        return $this->zone;
    }

    public function withZone(AbstractTimeZoneId $zone) : ClockInterface
    {
        if ($zone->equals($this->zone)) {
            return $this;
        }

        return new SystemClock($zone);
    }

    public function getInstant() : Instant
    {
        return Instant::ofEpochSecond($this->getMillis());
    }

    public function getMillis() : int
    {
        return (int) (microtime(true) * 1000);
    }

    public function equals(ClockInterface $other) : bool
    {
        if (!$other instanceof self) {
            return false;
        }

        return $this->zone->equals($other->zone);
    }

    public function __toString() : string
    {
        sprintf('SystemClock[%s]', $this->zone);
    }

}
