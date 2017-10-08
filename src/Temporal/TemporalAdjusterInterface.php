<?php
declare(strict_types = 1);

namespace Dagr\Temporal;

interface TemporalAdjusterInterface
{
    public function adjustInto(TemporalInterface $temporal) : TemporalInterface;
}
