<?php
declare(strict_types = 1);

namespace Dagr\Clock;

use Dagr\TimeZone\AbstractTimeZoneId;

interface ClockInterface
{
    public function getZone() : AbstractTimeZoneId;

    public function withZone(AbstractTimeZoneId $zone) : self;

    public function getInstant() : Instant;

    public function getMillis() : int;

    public function equals(self $other) : bool;

    public function __toString() : string;
}
