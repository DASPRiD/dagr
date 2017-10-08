<?php
declare(strict_types = 1);

namespace Dagr\Exception;

use Throwable;

final class DateTimeParseException extends DateTimeException implements ExceptionInterface
{
    /**
     * @var string
     */
    private $parsedString;

    public function __construct(string $message, string $parsedData, int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->parsedString = $parsedData;
    }

    public function getParsedString() : string
    {
        return $this->parsedString;
    }
}
