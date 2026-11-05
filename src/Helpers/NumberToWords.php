<?php

namespace App\Helpers;

/**
 * Converts a numeric BDT amount into words using the South Asian
 * numbering system (Lakh / Crore), as required by the FCDF voucher.
 */
class NumberToWords
{
    private static array $ones = [
        '', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine',
        'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen',
        'Seventeen', 'Eighteen', 'Nineteen',
    ];

    private static array $tens = [
        '', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety',
    ];

    public static function convert(float $amount): string
    {
        $rupees = (int) floor($amount);
        $paisa = (int) round(($amount - $rupees) * 100);

        $words = 'Taka ' . self::convertInteger($rupees) . ' Only';
        if ($paisa > 0) {
            $words = 'Taka ' . self::convertInteger($rupees) . ' and ' . self::convertInteger($paisa) . ' Paisa Only';
        }
        return trim(preg_replace('/\s+/', ' ', $words));
    }

    private static function convertInteger(int $number): string
    {
        if ($number === 0) {
            return 'Zero';
        }

        $crore = intdiv($number, 10000000);
        $number %= 10000000;
        $lakh = intdiv($number, 100000);
        $number %= 100000;
        $thousand = intdiv($number, 1000);
        $number %= 1000;
        $hundred = intdiv($number, 100);
        $remainder = $number % 100;

        $parts = [];
        if ($crore) $parts[] = self::twoDigits($crore) . ' Crore';
        if ($lakh) $parts[] = self::twoDigits($lakh) . ' Lakh';
        if ($thousand) $parts[] = self::twoDigits($thousand) . ' Thousand';
        if ($hundred) $parts[] = self::ones()[$hundred] . ' Hundred';
        if ($remainder) $parts[] = self::twoDigits($remainder);

        return implode(' ', $parts);
    }

    private static function twoDigits(int $num): string
    {
        if ($num < 20) {
            return self::ones()[$num];
        }
        $t = intdiv($num, 10);
        $o = $num % 10;
        return trim(self::tens()[$t] . ' ' . self::ones()[$o]);
    }

    private static function ones(): array
    {
        return self::$ones;
    }

    private static function tens(): array
    {
        return self::$tens;
    }
}
