<?php
declare(strict_types = 1);

namespace DagrTest\Temporal;

use Dagr\Exception\DateTimeException;
use Dagr\Temporal\ChronoUnit;
use Dagr\Temporal\TemporalAccessorInterface;
use Dagr\Temporal\TemporalFieldInterface;
use Dagr\Temporal\TemporalInterface;
use Dagr\Temporal\TemporalUnitInterface;
use Dagr\Temporal\ValueRange;

final class MockFieldNoValue implements TemporalFieldInterface
{
    public function getBaseUnit() : TemporalUnitInterface
    {
        return ChronoUnit::weeks();
    }

    public function getRangeUnit() : TemporalUnitInterface
    {
        return ChronoUnit::months();
    }

    public function range() : ValueRange
    {
        return ValueRange::ofTwo(1, 20);
    }

    public function isDateBased() : bool
    {
        return false;
    }

    public function isTimeBased() : bool
    {
        return false;
    }

    public function isSupportedBy(TemporalAccessorInterface $temporal) : bool
    {
        return true;
    }

    public function rangeRefinedBy(TemporalAccessorInterface $temporal) : ValueRange
    {
        return ValueRange::ofTwo(1, 20);
    }

    public function getFrom(TemporalAccessorInterface $temporal) : int
    {
        throw new DateTimeException('Mock');
    }

    public function adjustInto(TemporalInterface $temporal, int $newValue) : TemporalInterface
    {
        throw new DateTimeException('Mock');
    }

    public function __toString() : string
    {
        return '';
    }
}
