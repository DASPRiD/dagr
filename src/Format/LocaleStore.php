<?php
declare(strict_types = 1);

namespace Dagr\Format;

use Dagr\NotClonableTrait;
use Dagr\NotSerializableTrait;
use SplObjectStorage;

final class LocaleStore
{
    use NotClonableTrait;
    use NotSerializableTrait;

    /**
     * @var SplObjectStorage|string[][]
     */
    private $valueTextMap;

    /**
     * @var SplObjectStorage|int[][]
     */
    private $parsable;

    /**
     * @param SplObjectStorage|string[][] $valueTextMap
     */
    public function __construct(SplObjectStorage $valueTextMap)
    {
        $this->valueTextMap = $valueTextMap;
        $map = new SplObjectStorage();
        $allList = [];

        foreach ($valueTextMap as $textStyle) {
            $entryList = $valueTextMap[$textStyle];
            $reverse = array_flip($entryList);

            if (count($reverse) !== count($entryList)) {
                // Not parsable.
                continue;
            }

            uasort($reverse, function (string $a, string $b) : int {
                return strlen($a) - strlen($b);
            });

            $map[$textStyle] = $reverse;
            $allList += $reverse;
        }

        uasort($allList, function (string $a, string $b) : int {
            return strlen($a) - strlen($b);
        });

        foreach ($map as $key) {
            $reverse = $map[$key];
            $reverse[null] = $allList;
            $map[$key] = $reverse;
        }

        $this->parsable = $map;
    }

    /**
     * Gets the text for the specified field value, locale and style for purpose of printing.
     */
    public function getText(int $value, TextStyle $style) : ?string
    {
        if (! $this->valueTextMap->contains($style)) {
            return null;
        }

        $map = $this->valueTextMap->offsetGet($style);
        return $map[$value] ?? null;
    }

    /**
     * Gets an iterator of text to field for the specified style for the purpose of parsing.
     *
     * The iterator must be returned in order frm the longest text to the shortest.
     *
     * @return int[]
     */
    public function getTextIterator(TextStyle $style) : array
    {
        if (! $this->parsable->contains($style)) {
            return null;
        }

        return $this->parsable->offsetGet($style);
    }
}
