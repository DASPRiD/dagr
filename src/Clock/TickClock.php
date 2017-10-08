<?php
declare(strict_types = 1);

namespace Dagr\Clock;

use Dagr\Duration;
use Dagr\Math;
use Dagr\TimeZone\AbstractTimeZoneId;

/**
 * Implementation of a clock that adds an offset to an underlying clock.
 */
final class TickClock implements ClockInterface
{
    /**
     * @var ClockInterface
     */
    private $baseClock;

    /**
     * @var int
     */
    private $tickNanos;

    public function __construct(ClockInterface $baseClock, int $tickNanos)
    {
        $this->baseClock = $baseClock;
        $this->tickNanos = $tickNanos;
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

        return new OffsetClock($this->baseClock->withZone($zone), $this->tickNanos);
    }

    public function getInstant() : Instant
    {
        if (0 === ($this->tickNanos % 1000000)) {
            $millis = $this->baseClock->getMillis();
            return Instant::ofEpochSecond($millis - Math::floorMod($millis, intdiv($this->tickNanos, 1000000)));
        }

        $instant = $this->baseClock->getInstant();
        $nanos = $instant->getNanos();
        $adjust = Math::floorMod($nanos, $this->tickNanos);
        return $instant->minusNanos($adjust);
    }

    public function getMillis() : int
    {
        $millis = $this->baseClock->getMillis();
        return $millis - Math::floorMod($millis, intdiv($this->tickNanos, 1000000));
    }

    public function equals(ClockInterface $other) : bool
    {
        if (!$other instanceof self) {
            return false;
        }

        return $this->baseClock->equals($other->baseClock) && $this->tickNanos === $other->tickNanos;
    }

    public function __toString() : string
    {
        return sprintf('TickClock[%s,%s]', $this->baseClock, Duration::ofNanos($this->tickNanos));
    }
}
