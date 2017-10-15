<?php
declare(strict_types = 1);

namespace Dagr\Format;

use Dagr\Chrono\AbstractChronology;
use Dagr\Chrono\IsoChronology;
use Dagr\NotClonableTrait;
use Dagr\NotSerializableTrait;
use Dagr\Temporal\ChronoField;
use Dagr\Temporal\TemporalFieldInterface;
use EmptyIterator;
use ResourceBundle;
use SplObjectStorage;
use Traversable;

final class DateTimeTextProvider
{
    use NotClonableTrait;
    use NotSerializableTrait;

    /**
     * @var array
     */
    private $store;

    /**
     * @var self|null
     */
    private static $instance;

    /**
     * @var ResourceBundle|null
     */
    private static $resourceBundle;

    /**
     * @var LocaleStore[]
     */
    private static $cache = [];

    public static function getInstance() : self
    {
        return self::$instance ?: self::$instance = new self();
    }

    public function getText(
        TemporalFieldInterface $field,
        int $value,
        TextStyle $style,
        string $locale,
        ?AbstractChronology $chronology
    ) : ?string {
        if (null === $chronology
            || $chronology === IsoChronology::getInstance()
            || ! $field instanceof ChronoField
        ) {
            $store = $this->findStore($field, $locale);

            if ($store instanceof LocaleStore) {
                return $store->getText($value, $style);
            }

            return null;
        }

        if ($field === ChronoField::era()) {
        }
    }

    private static function findStore(TemporalFieldInterface $field, string $locale) : ?LocaleStore
    {
        $key = (string) $field . '_' . $locale;
        return self::$cache[$key] ?? self::$cache[$key] = self::createStore($field, $locale);
    }

    private static function toWeekDay(int $calendarWeekDay) : int
    {
        if (0 === $calendarWeekDay) {
            return 7;
        }

        return $calendarWeekDay;
    }

    private static function createStore(TemporalFieldInterface $field, string $locale) : ?LocaleStore
    {
        if (null === self::$resourceBundle) {
            self::$resourceBundle = new ResourceBundle($locale, null);
        }

        $calendar = self::$resourceBundle['calendar']['gregorian'];
        $styleMap = new SplObjectStorage();

        if ($field === ChronoField::era()) {
            foreach (TextStyle::values() as $textStyle) {
                if ($textStyle->isStandalone()) {
                    // Stand-alone isn't applicable to era names.
                    continue;
                }

                $map = $calendar['eras'][$textStyle->toCalendarStyle()] ?? [];

                if (!empty($map)) {
                    $styleMap[$textStyle] = $map;
                }
            }

            return new LocaleStore($styleMap);
        }

        if ($field === ChronoField::monthOfYear()) {
            foreach (TextStyle::values() as $textStyle) {
                $values = self::findStyleValues($calendar['monthNames'], $textStyle);

                if (empty($values)) {
                    continue;
                }

                $map = [];

                foreach ($values as $key => $value) {
                    $map[$key + 1] = $value;
                }

                $styleMap[$textStyle] = $map;
            }

            return new LocaleStore($styleMap);
        }

        if ($field === ChronoField::dayOfWeek()) {
            foreach (TextStyle::values() as $textStyle) {
                $values = self::findStyleValues($calendar['dayNames'], $textStyle);

                if (empty($values)) {
                    continue;
                }

                $map = [];

                foreach ($values as $key => $value) {
                    $map[self::toWeekDay($key)] = $value;
                }

                $styleMap[$textStyle] = $map;
            }

            return new LocaleStore($styleMap);
        }

        if ($field === ChronoField::amPmOfDay()) {
            foreach (TextStyle::values() as $textStyle) {
                if ($textStyle->isStandalone()) {
                    // Stand-alone isn't applicable to AM/PM.
                    continue;
                }

                $values = [];

                switch ($textStyle->toCalendarStyle()) {
                    case 'full':
                        $values = $calendar['AmPmMarkers'] ?? null;
                        break;

                    case 'narrow':
                        $values = $calendar['AmPmMarkersNarrow'] ?? null;
                        break;
                }

                if (empty($values)) {
                    continue;
                }

                $map = [];

                foreach ($values as $key => $value) {
                    $map[$key] = $value;
                }

                $styleMap[$textStyle] = $map;
            }

            return new LocaleStore($styleMap);
        }

        if ($field === IsoFields::quarterOfYear()) {
            foreach (TextStyle::values() as $textStyle) {
                $values = self::findStyleValues($calendar['quarters'], $textStyle);

                if (empty($values)) {
                    continue;
                }

                $map = [];

                foreach ($values as $key => $value) {
                    $map[$key + 1] = $value;
                }

                $styleMap[$textStyle] = $map;
            }

            return new LocaleStore($styleMap);
        }

        return null;
    }

    private static function findStyleValues(ResourceBundle $bundle, TextStyle $textStyle) : Traversable
    {
        $calendarStyle = $textStyle->toCalendarStyle();

        if ($textStyle->isStandalone()) {
            return $bundle['stand-alone'][$calendarStyle] ?? $bundle['format'][$calendarStyle] ?? new EmptyIterator();
        }

        return $bundle['format'][$calendarStyle] ?? $bundle['stand-alone'][$calendarStyle] ?? new EmptyIterator();
    }
}
