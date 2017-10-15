<?php
declare(strict_types = 1);

namespace Dagr\Format;

use Dagr\NotClonableTrait;
use Dagr\NotSerializableTrait;
use Dagr\StringHelper;

final class DecimalStyle
{
    use NotClonableTrait;
    use NotSerializableTrait;

    /**
     * @var string
     */
    private $zeroDigit;

    /**
     * @var string
     */
    private $positiveSign;

    /**
     * @var string
     */
    private $negativeSign;

    /**
     * @var string
     */
    private $decimalSeparator;

    /**
     * @var self|null
     */
    private static $standard;

    private function __construct(
        string $zeroDigit,
        string $positiveSign,
        string $negativeSign,
        string $decimalSeparator
    ) {
        $this->zeroDigit = $zeroDigit;
        $this->positiveSign = $positiveSign;
        $this->negativeSign = $negativeSign;
        $this->decimalSeparator = $decimalSeparator;
    }

    public static function standard() : self
    {
        return self::$standard ?: self::$standard = new self('0', '+', '-', '.');
    }

    /**
     * Gets the character that represents zero.
     *
     * This character used to represent digits may vary by culture. This method specifies the zero character to use,
     * which implies the characters for one to tine.
     */
    public function getZeroDigit() : string
    {
        return $this->zeroDigit;
    }

    /**
     * Gets the character that represents the positive sign
     *
     * The character used to represent a positive number may vary by culture. This method specifies the character to
     * use.
     */
    public function getPositiveSign() : string
    {
        return $this->positiveSign;
    }

    /**
     * Gets the character that represents the negative sign
     *
     * The character used to represent a negative number may vary by culture. This method specifies the character to
     * use.
     */
    public function getNegativeSign() : string
    {
        return $this->negativeSign;
    }

    /**
     * Gets the character that represents the decimal point.
     */
    public function getDecimalSeparator() : string
    {
        return $this->decimalSeparator;
    }

    public function convertToDigit(string $char) : int
    {
        $value = StringHelper::ordinal($char) - StringHelper::ordinal($this->zeroDigit);
        return ($value >= 0 && $value <= 9) ? $value : -1;
    }

    /**
     * Converts the input numeric text to the internationalized form using the zero character.
     */
    public function convertNumberToI18n(string $numericText) : string
    {
        if ('0' === $this->zeroDigit) {
            return $numericText;
        }

        $diff = StringHelper::ordinal($this->zeroDigit) - StringHelper::ordinal('0');
        $result = '';

        foreach (str_split($numericText) as $digit) {
            $result .= StringHelper::character(StringHelper::ordinal($digit) + $diff);
        }

        return $result;
    }
}
