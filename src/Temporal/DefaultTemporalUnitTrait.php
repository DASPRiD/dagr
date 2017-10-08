<?php
declare(strict_types = 1);

namespace Dagr\Temporal;

use Dagr\Chrono\ChronoLocalDateInterface;
use Dagr\Chrono\ChronoLocalDateTimeInterface;
use Dagr\Exception\UnsupportedTemporalTypeException;
use Dagr\LocalTime;

trait DefaultTemporalUnitTrait
{
    public function isSupportedBy(TemporalInterface $temporal) : bool
    {
        if ($temporal instanceof LocalTime) {
            return $this->isTimeBased();
        }

        if ($temporal instanceof ChronoLocalDateInterface) {
            return $this->isDateBased();
        }

        if ($temporal instanceof ChronoLocalDateTimeInterface) {
            return true;
        }

        try {
            $temporal->plus(1, $this);
            return true;
        } catch (UnsupportedTemporalTypeException $e) {
            return false;
        }
    }

    abstract public function isDateBased() : bool;

    abstract public function isTimeBased() : bool;
}
