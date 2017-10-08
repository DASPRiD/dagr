<?php
declare(strict_types = 1);

namespace Dagr;

use Dagr\Exception\DateTimeException;

trait NotSerializableTrait
{
    final public function __wakeup()
    {
        throw new DateTimeException('Object ' . get_class($this) . ' is not serializable');
    }

    final public function __sleep()
    {
        throw new DateTimeException('Object ' . get_class($this) . ' is not unserializable');
    }
}
