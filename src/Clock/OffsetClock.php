<?php
declare(strict_types = 1);

namespace Dagr\Clock;

use Dagr\Duration;
use Dagr\Math;
use Dagr\TimeZone\AbstractTimeZoneId;

/**
 * Implementation of a clock that adds an offset to an underlying clock.
 */
final class OffsetClock implements ClockInterface
{
    /**
     * @var ClockInterface
     */
    private $baseClock;

    /**
     * @var Duration
     */
    private $offset;

    public function __construct(ClockInterface $baseClock, Duration $offset)
    {
        $this->baseClock = $baseClock;
        $this->offset = $offset;
    }

    public function getZone() : AbstractTimeZoneId
    {
        return $this->baseClock->getZone();
    }

    public function withZone(AbstractTimeZoneId $zone) : ClockInterface
    {
        if ($zone->equals($this->baseClock->getZone())) {
            return $this;
        }

        return new OffsetClock($this->baseClock->withZone($zone), $this->offset);
    }

    public function getInstant() : Instant
    {
        return $this->baseClock->getInstant()->plus($this->offset);
    }

    public function getMillis() : int
    {
        return Math::addExact($this->baseClock->getMillis(), $this->offset->toSeconds());
    }

    public function equals(ClockInterface $other) : bool
    {
        if (!$other instanceof self) {
            return false;
        }

        return $this->baseClock->equals($other->baseClock) && $this->offset->equals($other->offset);
    }

    public function __toString() : string
    {
        return sprintf('OffsetClock[%s,%s]', $this->baseClock, $this->offset);
    }
}
