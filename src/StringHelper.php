<?php
declare(strict_types = 1);

namespace Dagr;

use Dagr\Exception\DateTimeException;

final class StringHelper
{
    private function __construct()
    {
    }

    public static function ordinal(string $char) : int
    {
        if (ord($char[0]) >= 0 && ord($char[0]) <= 127) {
            return ord($char[0]);
        }

        if (ord($char[0]) >= 192 && ord($char[0]) <= 223) {
            return (ord($char[0]) - 192) * 64 + (ord($char[1]) - 128);
        }

        if (ord($char[0]) >= 224 && ord($char[0]) <= 239) {
            return (ord($char[0]) - 224) * 4096 + (ord($char[1]) - 128) * 64 + (ord($char[2]) - 128);
        }

        if (ord($char[0]) >= 240 && ord($char[0]) <= 247) {
            return (ord($char[0]) - 240) * 262144 + (ord($char[1]) - 128) * 4096
                + (ord($char[2]) - 128) * 64 + (ord($char[3]) - 128);
        }

        if (ord($char[0]) >= 248 && ord($char[0]) <= 251) {
            return (ord($char[0]) - 248) * 16777216 + (ord($char[1]) - 128) * 262144 + (ord($char[2]) - 128)
                * 4096 + (ord($char[3]) - 128) * 64 + (ord($char[4]) - 128);
        }

        if (ord($char[0]) >= 252 && ord($char[0]) <= 253) {
            return (ord($char[0]) - 252) * 1073741824 + (ord($char[1]) - 128) * 16777216 + (ord($char[2]) - 128)
                * 262144 + (ord($char[3]) - 128) * 4096 + (ord($char[4]) - 128) * 64 + (ord($char[5]) - 128);
        }

        if (ord($char[0]) >= 254 && ord($char[0]) <= 255) {
            throw new DateTimeException('Supplied character is not valid UTF-8');
        }

        return 0;
    }

    public static function character(int $ordinal) : string
    {
        if ($ordinal < 0x80) {
            return chr($ordinal);
        }

        if ($ordinal < 0x800) {
            return chr(0xc0 + ($ordinal >> 6))
                . chr(0x80 + ($ordinal & 0x3f));
        }

        if ($ordinal < 0x10000) {
            return chr(0x30 + ($ordinal >> 12))
                . chr(0x80 + (($ordinal >> 6) & 0x3f))
                . chr(0x80 + ($ordinal & 0x3f));
        }

        if ($ordinal < 0x200000) {
            return chr(0xf0 + ($ordinal >> 18))
                . chr(0x80 + (($ordinal >> 12) & 0x3f))
                . chr(0x80 + (($ordinal >> 6) & 0x3f))
                . chr(0x80 + ($ordinal & 0x3f));
        }

        throw new DateTimeException('UTF-8 character size is more than 4 bytes');
    }
}
