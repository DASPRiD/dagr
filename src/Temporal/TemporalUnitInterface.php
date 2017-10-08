<?php
declare(strict_types = 1);

namespace Dagr\Temporal;

use Dagr\Duration;

interface TemporalUnitInterface
{
    public function getDuration() : Duration;

    public function isDurationEstimated() : bool;

    public function isDateBased() : bool;

    public function isTimeBased() : bool;

    public function isSupportedBy(TemporalInterface $temporal) : bool;

    public function addTo(TemporalInterface $temporal, int $amount) : TemporalInterface;

    public function between(TemporalInterface $temporal1Inclusive, TemporalInterface $temporal2Exclusive) : int;

    public function __toString() : string;
}
