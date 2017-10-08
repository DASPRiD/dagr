<?php
declare(strict_types = 1);

namespace Dagr;

use Dagr\Exception\DateTimeException;

trait NotClonableTrait
{
    final public function __clone()
    {
        throw new DateTimeException('Object ' . get_class($this) . ' is not clonable');
    }
}
