<?php
declare(strict_types = 1);

namespace Dagr\Temporal;

interface TemporalAmountInterface
{
    public function get(TemporalUnitInterface $unit) : int;

    /**
     * @return TemporalUnitInterface[]
     */
    public function getUnits() : array;

    public function addTo(TemporalInterface $temporal) : TemporalInterface;

    public function subtractFrom(TemporalInterface $temporal) : TemporalInterface;
}
