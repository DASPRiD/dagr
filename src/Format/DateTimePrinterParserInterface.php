<?php
declare(strict_types = 1);

namespace Dagr\Format;

interface DateTimePrinterParserInterface
{
    /**
     * Prints the date-time object to the buffer.
     *
     * The context holds information to use during the format. It also contains the date-time information to be printed.
     */
    public function format(DateTimePrintContext $context) : ?string;

    /**
     * Parses text into date-time information.
     *
     * The context holds information to use during parse. It is also used to store the parsed date-time information.
     */
    public function parse(DateTimePrintContext $context, string $parseText, int $position) : int;

    public function __toString() : string;
}
