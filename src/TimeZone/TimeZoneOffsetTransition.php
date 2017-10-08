<?php
declare(strict_types = 1);

namespace Dagr\TimeZone;

use Dagr\Duration;
use Dagr\Instant;
use Dagr\LocalDateTime;

final class TimeZoneOffsetTransition
{
    /**
     * @var LocalDateTime
     */
    private $transition;

    /**
     * @var TimeZoneOffset
     */
    private $offsetBefore;

    /**
     * @var TimeZoneOffset
     */
    private $offsetAfter;

    public function __construct(LocalDateTime $transition, TimeZoneOffset $offsetBefore, TimeZoneOffset $offsetAfter)
    {
        $this->transition = $transition;
        $this->offsetBefore = $offsetBefore;
        $this->offsetAfter = $offsetAfter;
    }

    public static function ofEpochSecond(int $epochSecond, TimeZoneOffset $offsetBefore, TimeZoneOffset $offsetAfter)
    {
        return new self(
            LocalDateTime::ofEpochSecond($epochSecond, 0, $offsetBefore),
            $offsetBefore,
            $offsetAfter
        );
    }

    public function getInstant() : Instant
    {
        return $this->transition->toInstant($this->offsetBefore);
    }

    public function toEpochSecond() : int
    {
        return $this->transition->toEpochSecond($this->offsetBefore);
    }

    public function getDateTimeBefore() : LocalDateTime
    {
        return $this->transition;
    }

    public function getDateTimeAfter() : LocalDateTime
    {
        return $this->transition->plusSeconds($this->getDurationSeconds());
    }

    public function getOffsetBefore() : TimeZoneOffset
    {
        return $this->offsetBefore;
    }

    public function getOffsetAfter() : TimeZoneOffset
    {
        return $this->offsetAfter;
    }

    public function getDuration() : Duration
    {
        return Duration::ofSeconds($this->getDurationSeconds());
    }

    public function isGap() : bool
    {
        return $this->offsetAfter->getTotalSeconds() > $this->offsetBefore->getTotalSeconds();
    }

    public function isOverlap() : bool
    {
        return $this->offsetAfter->getTotalSeconds() < $this->offsetBefore->getTotalSeconds();
    }

    public function isValidOffset(TimeZoneOffset $offset) : bool
    {
        return ($this->isGap() ? false : $this->offsetBefore->equals($offset) || $this->offsetAfter->equals($offset));
    }

    public function compareTo(self $other) : bool
    {
        return $this->getInstant()->compareTo($other->getInstant());
    }

    public function equals(self $other) : bool
    {
        return $this->transition->equals($other->transition)
            && $this->offsetBefore->equals($other->offsetBefore)
            && $this->offsetAfter->equals($other->offsetAfter);
    }

    public function __toString() : string
    {
        return 'Transition['
            . ($this->isGap() ? 'Gap' : 'Overlap')
            . ' at '
            . $this->transition
            . $this->offsetBefore
            . ' to '
            . $this->offsetAfter
            . ']';
    }

    private function getDurationSeconds() : int
    {
        return $this->offsetAfter->getTotalSeconds() - $this->offsetBefore->getTotalSeconds();
    }
}
