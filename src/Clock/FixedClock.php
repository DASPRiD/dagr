<?php
declare(strict_types = 1);

namespace Dagr\Clock;

use Dagr\TimeZone\AbstractTimeZoneId;

/**
 * Implementation of a clock that always returns the same instant.
 *
 * This is typically used for testing.
 */
final class FixedClock implements ClockInterface
{
    /**
     * @var Instant
     */
    private $instant;

    /**
     * @var AbstractTimeZoneId
     */
    private $zone;

    public function __construct(Instant $instant, AbstractTimeZoneId $zone)
    {
        $this->instant = $instant;
        $this->zone = $zone;
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

        return new FixedClock($this->instant, $zone);
    }

    public function getInstant() : Instant
    {
        return $this->instant;
    }

    public function getMillis() : int
    {
        return $this->instant->toEpochSecond();
    }

    public function equals(ClockInterface $other) : bool
    {
        if (!$other instanceof self) {
            return false;
        }

        return $this->instant->equals($other->instant) && $this->zone->equals($other->zone);
    }

    public function __toString() : string
    {
        return sprintf('FixedClock[%s,%s]', $this->instant, $this->zone);
    }
}
