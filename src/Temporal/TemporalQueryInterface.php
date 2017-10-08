<?php
declare(strict_types = 1);

namespace Dagr\Temporal;

interface TemporalQueryInterface
{
    public function queryFrom(TemporalAccessorInterface $temporal);
}
