<?php
declare(strict_types = 1);

namespace Dagr\Temporal;

interface TemporalFieldInterface
{
    public function getBaseUnit() : TemporalUnitInterface;

    public function getRangeUnit() : TemporalUnitInterface;

    public function range() : ValueRange;

    public function isDateBased() : bool;

    public function isTimeBased() : bool;

    public function isSupportedBy(TemporalAccessorInterface $temporal) : bool;

    public function rangeRefinedBy(TemporalAccessorInterface $temporal) : ValueRange;

    public function getFrom(TemporalAccessorInterface $temporal) : int;

    public function adjustInto(TemporalInterface $temporal, int $newValue) : TemporalInterface;

    public function __toString() : string;
}
