<?php
declare(strict_types = 1);

namespace Dagr\Temporal;

trait DefaultTemporalQueryQueryTrait
{
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
