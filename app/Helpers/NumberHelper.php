<?php
// app/Helpers/NumberHelper.php

namespace App\Helpers;

class NumberHelper
{
    public static function convertToWords($number)
    {
        $whole = floor($number);
        $fraction = round(($number - $whole) * 100);
        
        $words = self::convertWholeNumberToWords($whole);
        
        if ($fraction > 0) {
            $words .= ' Shillings and ' . self::convertWholeNumberToWords($fraction) . ' Cents';
        } else {
            $words .= ' Shillings';
        }
        
        return $words;
    }

    private static function convertWholeNumberToWords($number)
    {
        if ($number == 0) {
            return 'Zero';
        }
        
        $ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine'];
        $teens = ['Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
        $tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];
        
        if ($number < 10) {
            return $ones[$number];
        } elseif ($number < 20) {
            return $teens[$number - 10];
        } elseif ($number < 100) {
            return $tens[floor($number / 10)] . ($number % 10 != 0 ? ' ' . $ones[$number % 10] : '');
        } elseif ($number < 1000) {
            return $ones[floor($number / 100)] . ' Hundred' . ($number % 100 != 0 ? ' and ' . self::convertWholeNumberToWords($number % 100) : '');
        } elseif ($number < 1000000) {
            return self::convertWholeNumberToWords(floor($number / 1000)) . ' Thousand' . ($number % 1000 != 0 ? ' ' . self::convertWholeNumberToWords($number % 1000) : '');
        } elseif ($number < 1000000000) {
            return self::convertWholeNumberToWords(floor($number / 1000000)) . ' Million' . ($number % 1000000 != 0 ? ' ' . self::convertWholeNumberToWords($number % 1000000) : '');
        } else {
            return 'Very Large Amount';
        }
    }
}