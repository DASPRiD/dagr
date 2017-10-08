<?php
declare(strict_types = 1);

namespace Dagr\Temporal;

use Dagr\Exception\DateTimeException;
use Dagr\Exception\UnsupportedTemporalTypeException;

trait DefaultTemporalAccessorTrait
{
    abstract public function isSupportedField(TemporalFieldInterface $field) : bool;

    abstract public function getInt(TemporalFieldInterface $field) : int;

    public function range(TemporalFieldInterface $field) : ValueRange
    {
        if (!$field instanceof ChronoField) {
            return $field->rangeRefinedBy($this);
        }

        if ($this->isSupportedField($field)) {
            return $field->range();
        }

        throw new UnsupportedTemporalTypeException('Unsupported field: ' . $field);
    }

    public function get(TemporalFieldInterface $field) : int
    {
        $range = $this->range($field);
        $value = $this->getInt($field);

        if (!$range->isValidValue($value)) {
            throw new DateTimeException('Invalid value for ' . $field . ' (valid values ' . $range . '): ' . $value);
        }

        return $value;
    }

    public function query(TemporalQueryInterface $query)
    {
        if (TemporalQueries::zoneId() === $query
            || TemporalQueries::chronology() === $query
            || TemporalQueries::precision() === $query
        ) {
            return null;
        }

        return $query->queryFrom($this);
    }
}
