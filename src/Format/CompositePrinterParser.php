<?php
declare(strict_types = 1);

namespace Dagr\Format;

use Dagr\NotClonableTrait;
use Dagr\NotSerializableTrait;

final class CompositePrinterParser implements DateTimePrinterParserInterface
{
    use NotClonableTrait;
    use NotSerializableTrait;

    /**
     * @var DateTimePrinterParserInterface[]
     */
    private $printerParsers;

    /**
     * @var bool
     */
    private $optional;

    public function __construct(bool $optional, DateTimePrinterParserInterface ...$printerParsers)
    {
        $this->optional = $optional;
        $this->printerParsers = $printerParsers;
    }

    public function format(DateTimePrintContext $context) : ?string
    {
        $formatted = '';

        if ($this->optional) {
            $context->startOptional();
        }

        try {
            foreach ($this->printerParsers as $printerParser) {
                $result = $printerParser->format($context);

                if (null === $result) {
                    return '';
                }

                $formatted .= $result;
            }
        } finally {
            if ($this->optional) {
                $context->endOptional();
            }
        }

        return $formatted;
    }

    public function parse(DateTimePrintContext $context, string $parseText, int $position) : int
    {
        if (! $this->optional) {
            foreach ($this->printerParsers as $printerParser) {
                $position = $printerParser->parse($context, $parseText, $position);

                if ($position < 0) {
                    break;
                }
            }

            return $position;
        }

        $originalPosition = $position;
        $context->startOptional();

        foreach ($this->printerParsers as $printerParser) {
            $position = $printerParser->parse($context, $parseText, $position);

            if ($position < 0) {
                $context->endOptional();
                return $originalPosition;
            }
        }

        return $position;
    }

    public function __toString() : string
    {
        $result = ($this->optional ? '[' : '(');

        foreach ($this->printerParsers as $printerParser) {
            $result .= (string) $printerParser;
        }

        $result .= ($this->optional ? ']' : ')');
        return $result;
    }
}
