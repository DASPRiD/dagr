<?php
declare(strict_types = 1);

namespace Dagr\Exception;

use DomainException;

final class UnsupportedTemporalTypeException extends DomainException implements ExceptionInterface
{
}
