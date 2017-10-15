<?php
declare(strict_types = 1);

namespace Dagr\Format;

use Dagr\NotClonableTrait;
use Dagr\NotSerializableTrait;

final class TextStyle
{
    use NotClonableTrait;
    use NotSerializableTrait;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $calendarStyle;

    /**
     * @var int
     */
    private $zoneNameStyleIndex;

    /**
     * @var bool
     */
    private $standalone;

    /**
     * @var self[]
     */
    private static $cache = [];

    private function __construct(string $name, string $calendarStyle, int $zoneNameStyleIndex, bool $standalone)
    {
        $this->name = $name;
        $this->calendarStyle = $calendarStyle;
        $this->zoneNameStyleIndex = $zoneNameStyleIndex;
        $this->standalone = $standalone;
    }

    /**
     * @return self[]
     */
    public static function values() : array
    {
        return [
            self::full(),
            self::fullStandalone(),
            self::short(),
            self::shortStandalone(),
            self::narrow(),
            self::narrowStandalone(),
        ];
    }

    /**
     * Returns true if the style is a stand-alone style.
     */
    public function isStandalone() : bool
    {
        return $this->standalone;
    }

    /**
     * Returns the stand-alone style with the same size.
     */
    public function asStandalone() : self
    {
        $key = $this->name . 'Standalone';
        return self::$cache[$key] ?? self::$cache[$key] = new self(
            $this->name,
            $this->calendarStyle,
            $this->zoneNameStyleIndex,
            true
        );
    }

    /**
     * Returns the normal style with the same size.
     */
    public function asNormal() : self
    {
        $key = $this->name;
        return self::$cache[$key] ?? self::$cache[$key] = new self(
            $this->name,
            $this->calendarStyle,
            $this->zoneNameStyleIndex,
            false
        );
    }

    /**
     * Returns the calendar style corresponding to this style.
     */
    public function toCalendarStyle() : string
    {
        return $this->calendarStyle;
    }

    /**
     * Returns the relative index value to an element of the zone string value, 0 for long names and 1 for short names.
     */
    public function zoneNameStyleIndex() : int
    {
        return $this->zoneNameStyleIndex;
    }

    /**
     * Full text, typically the full description.
     *
     * For example, day-of-week Monday might output "Monday".
     */
    public static function full() : self
    {
        return self::$cache['full'] ?? self::$cache['full'] = new self('full', 'wide', 0, false);
    }

    /**
     * Full text for stand-alone use, typically the full description.
     *
     * For example, day-of-week Monday might output "Monday".
     */
    public static function fullStandalone() : self
    {
        return self::$cache['fullStandalone'] ?? self::$cache['fullStandalone'] = new self('full', 'wide', 0, true);
    }

    /**
     * Short text, typically an abbreviation.
     *
     * For example, day-of-week Monday might output "Mon".
     */
    public static function short() : self
    {
        return self::$cache['short'] ?? self::$cache['short'] = new self('short', 'abbreviated', 1, false);
    }

    /**
     * Short text for stand-alone use, typically an abbreviation.
     *
     * For example, day-of-week Monday might output "Mon".
     */
    public static function shortStandalone() : self
    {
        return self::$cache['shortStandalone'] ?? self::$cache['shortStandalone'] = new self(
                'short',
                'abbreviated',
                1,
                true
            );
    }

    /**
     * Narrow text, typically a single letter.
     *
     * For example, day-of-week Monday might output "M".
     */
    public static function narrow() : self
    {
        return self::$cache['narrow'] ?? self::$cache['narrow'] = new self('narrow', 'narrow', 1, false);
    }

    /**
     * Narrow text for stand-alone use, typically a single letter.
     *
     * For example, day-of-week Monday might output "M".
     */
    public static function narrowStandalone() : self
    {
        return self::$cache['narrowStandalone'] ?? self::$cache['narrowStandalone'] = new self(
                'narrow',
                'narrow',
                1,
                true
            );
    }
}
