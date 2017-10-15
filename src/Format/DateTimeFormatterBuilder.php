<?php
declare(strict_types = 1);

namespace Dagr\Format;

use Dagr\Chrono\AbstractChronology;
use Dagr\Chrono\ChronoLocalDateInterface;
use Dagr\Exception\IllegalArgumentException;
use Dagr\NotClonableTrait;
use Dagr\NotSerializableTrait;
use Dagr\Temporal\TemporalFieldInterface;
use Dagr\Temporal\TemporalQueries;
use Dagr\TimeZone\TimeZoneId;

final class DateTimeFormatterBuilder
{
    use NotClonableTrait;
    use NotSerializableTrait;

    /**
     * @var self
     */
    private $active;

    /**
     * @var self
     */
    private $parent;

    /**
     * @var DateTimePrinterParserInterface[]
     */
    private $printerParsers;

    /**
     * @var bool
     */
    private $optional;

    /**
     * @var int|null
     */
    private $padNextWidth;

    /**
     * @var string|null
     */
    private $padNextChar;

    /**
     * @var int
     */
    private $valueParserIndex = -1;

    public function __construct()
    {
        $this->active = $this;
    }

    public function parseCaseSensitive() : self
    {
        $this->appendInternal(SettingsParser::sensitive());
        return $this;
    }

    public function parseCaseInsensitive() : self
    {
        $this->appendInternal(SettingsParser::insensitive());
        return $this;
    }

    public function parseStrict() : self
    {
        $this->appendInternal(SettingsParser::strict());
        return $this;
    }

    public function parseLenient() : self
    {
        $this->appendInternal(SettingsParser::lenient());
        return $this;
    }

    public function parseDefaulting(TemporalFieldInterface $field, int $value) : self
    {
        $this->appendInternal(new DefaultValueParser($field, $value));
        return $this;
    }

    public function appendValue(
        TemporalFieldInterface $field,
        int $minWidth = 1,
        int $maxWidth = 19,
        SignStyle $signStyle = null
    ) : self {
        if (null === $signStyle) {
            $signStyle = SignStyle::normal();
        }

        if ($minWidth < 1 || $minWidth > 19) {
            throw new IllegalArgumentException('The minimum width must be from 1 to 19 inclusive but was ' . $minWidth);
        }

        if ($maxWidth < 1 || $maxWidth > 19) {
            throw new IllegalArgumentException('The maximum width must be from 1 to 19 inclusive but was ' . $minWidth);
        }

        if ($maxWidth < $minWidth) {
            throw new IllegalArgumentException(
                'The maximum width must exceed or equal the miniumm width but ' . $maxWidth . ' < ' . $minWidth
            );
        }

        $this->appendNumberPrinterParser(new NumberPrinterParser($field, $minWidth, $maxWidth, $signStyle));
        return $this;
    }

    public function appendValueReduced(
        TemporalFieldInterface $field,
        int $width,
        int $maxWidth,
        ChronoLocalDateInterface $baseDate
    ) : self {
        $this->appendNumberPrinterParser(new ReducedPrinterParser($field, $width, $maxWidth, 0, $baseDate));
        return $this;
    }

    private function appendNumberPrinterParser(NumberPrinterParser $printerParser) : self
    {
        if ($this->active->valueParserIndex < 0) {
            $this->active->valueParserIndex = $this->appendInternal($printerParser);
            return $this;
        }

        $activeValueParser = $this->active->valueParserIndex;
        $basePrinterParser = $this->active->printerParsers[$activeValueParser];

        if ($printerParser->getMinWidth() === $printerParser->getMaxWidth()
            && $printerParser->getSignStyle() === SignStyle::notNegative()
        ) {
            $basePrinterParser = $basePrinterParser->withSubsequentWidth($printerParser->getMaxWidth());
            $this->appendInternal($printerParser->withWifxedWidth());
            $this->active->valueParserIndex = $activeValueParser;
        } else {
            $basePrinterParser = $basePrinterParser->withFixedWidth();
            $this->active->valueParserIndex = $this->appendInternal($printerParser);
        }

        $this->active->printerParsers[$activeValueParser] = $basePrinterParser;
        return $this;
    }

    public function appendFraction(
        TemporalFieldInterface $field,
        int $minWidth,
        int $maxWidth,
        bool $decimalPoint
    ) : self {
        $this->appendInternal(new FractionalPrinterParser($field, $minWidth, $maxWidth, $decimalPoint));
        return $this;
    }

    public function appendText(TemporalFieldInterface $field, TextStyle $textStyle = null) : self
    {
        if (null === $textStyle) {
            $textStyle = TextStyle::full();
        }

        $this->appendInternal(new TextPrinterParser($field, $textStyle, DateTimeTextProvider::getInstance()));
        return $this;
    }

    public function appendTextLookup(TemporalFieldInterface $field, array $textLookup) : self
    {
        // @todo $provider =…

        $this->appendInternal(new TextPrinterParser($field, TextStyle::full(), $provider));
        return $this;
    }

    public function appendInstant(int $fractionalDigits = null) : self
    {
        if (null === $fractionalDigits) {
            $fractionalDigits = -2;
        } elseif ($fractionalDigits < -1 || $fractionalDigits > 9) {
            throw new IllegalArgumentException(
                'The fractional digits must be from -1 to 9 inclusive but was ' . $fractionalDigits
            );
        }

        $this->appendInternal(new InstantPrinterParser($fractionalDigits));
        return $this;
    }

    public function appendOffsetId() : self
    {
        $this->appendInternal(OffsetIdPrinterParser::instanceIdZ());
        return $this;
    }

    public function appendOffset(string $pattern, string $noOffsetText) : self
    {
        $this->appendInternal(new OffsetPrinterParser($pattern, $noOffsetText));
        return $this;
    }

    public function appendLocalizedOffset(TextStyle $style) : self
    {
        if (TextStyle::full() !== $style && TextStyle::short() !== $style) {
            throw new IllegalArgumentException('Style must be either full or short');
        }

        $this->appendInternal(new LocalizedOffsetIdPrinterParser($style));
        return $this;
    }

    public function appendZoneId() : self
    {
        $this->appendInternal(new ZoneIdPrinterParser(TemporalQueries::zoneId(), 'ZoneId()'));
        return $this;
    }

    public function appendZoneRegionId() : self
    {
        $this->appendInternal(new ZoneIdPrinterParser(QUERY_REGION_ONLY, 'ZoneRegionId()')); //@todo
        return $this;
    }

    public function appendZoneOrOffsetId() : self
    {
        $this->appendInternal(new ZoneIdPrinterParser(TemporalQueries::zone(), 'ZoneOrOffsetId()'));
        return $this;
    }

    public function appendZoneText(TestStyle $textStyle, TimeZoneId ...$preferredZones) : self
    {
        if (empty($preferredZones)) {
            $this->appendInternal(new ZoneTextPrinterParser($textStyle, null));
            return $this;
        }

        $this->appendInternal(new ZoneTextPrinterParser($textStyle, ...$preferredZones));
        return $this;
    }

    public function appendChronologyId() : self
    {
        $this->appendInternal(new ChronoPrinterParser(null));
        return $this;
    }

    public function appendChronologyText(TextStyle $textStyle) : self
    {
        $this->appendInternal(new ChronoPrinterParser($textStyle));
        return $this;
    }

    public function appendLocalized(?FormatStyle $dateStyle = null, ?FormatStyle $timeStyle = null) : self
    {
        if (null === $dateStyle && null === $timeStyle) {
            throw new IllegalArgumentException('Either the date or the time style must be non-null');
        }

        $this->appendInternal(new LocalizedPrinterParser($dateStyle, $timeStyle));
        return $this;
    }

    public function appendLiteral(string $literal) : self
    {
        $this->appendInternal(new CharLiteralPrinterParser($literal));
        return $this;
    }

    public function append(DateTimeFormatter $formatter) : self
    {
        $this->appendInternal($formatter->toPrinterParser(false));
        return $this;
    }

    public function appendOptional(DateTimeFormatter $formatter) : self
    {
        $this->appendInternal($formatter->toPrinterParser(true));
        return $this;
    }

    public function appendPattern(string $pattern) : self
    {
        $this->parsePattern($pattern);
        return $this;
    }

    private function parsePattern(string $pattern) : void
    {
        // @todo
    }

    public function padNext(int $padWidth, string $padChar = ' ') : self
    {
        if ($padWidth < 1) {
            throw new IllegalArgumentException('The pad width must be at least one but was ' . $padWidth);
        }

        if (1 !== strlen($padChar)) {
            throw new IllegalArgumentException('The pad character must be exactly one byte long');
        }

        $this->active->padNextWidth = $padWidth;
        $this->active->padNextChar = $padChar;
        $this->active->valueParserIndex = -1;
        return $this;
    }

    public function optionalStart() : self
    {
        $this->active->valueParserIndex = -1;
        $this->active = new DateTimeFormatterBuilder();
        $this->active->parent = $this;
        $this->active->optional = true;
        return $this;
    }

    public function optionalEnd() : self
    {
        if (null === $this->active->parent) {
            throw new IllegalStateException(
                'Cannot call optionalEnd() as there was no previous call to optionalStart()'
            );
        }

        if (!empty($this->active->printerParsers)) {
            $compositePrinterParser = new CompositePrinterParser(
                $this->active->optional,
                ...$this->active->printerParsers
            );
            $this->active = $this->active->parent;
            $this->appendInternal($compositePrinterParser);
            return $this;
        }

        $this->active = $this->active->parent;
        return $this;
    }

    private function appendInternal(DateTimePrinterParserInterface $printerParser) : int
    {
        if (0 === $this->active->padNextWidth) {
            $this->active->printerParsers[] = $printerParser;
            $this->active->valueParserIndex = -1;
            return count($this->active->printerParsers) - 1;
        }

        if (null !== $this->active->padNextWidth) {
            $printerParser = new PadPrinterParserDecorator(
                $printerParser,
                $this->active->padNextWidth,
                $this->active->padNextChar
            );
            $this->active->padNextWidth = null;
            $this->active->padNextChar = null;
        }

        $this->active->printerParsers[] = $printerParser;
        $this->active->valueParserIndex = -1;
        return count($this->active->printerParsers) - 1;
    }

    public function toFormatter(
        ?string $locale = null,
        ?ResolverStyle $resolverStyle = null,
        ?AbstractChronology $chrono = null
    ) : DateTimeFormatter {
        if (null === $locale) {
            $locale = Locale::getDefault(Locale::category()->format());
        }

        $compositePrinterParser = new CompositePrinterParser(false, ...$this->printerParsers);
        return new DateTimeFormatter(
            $compositePrinterParser,
            $locale,
            DecimalStyle::standard(),
            $resolverStyle,
            [],
            $chrono,
            null
        );
    }
}
