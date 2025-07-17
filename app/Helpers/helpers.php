<?php

if (!function_exists('convertLessThanThousand')) {
    function convertLessThanThousand($num, $units, $tens) {
        if ($num == 0) {
            return '';
        }

        $words = '';

        if ($num < 20) {
            $words = $units[$num];
        } elseif ($num < 100) {
            $words = $tens[floor($num / 10)];
            if ($num % 10 > 0) {
                $words .= ' ' . $units[$num % 10];
            }
        } else {
            $words = $units[floor($num / 100)] . ' Hundred';
            if ($num % 100 > 0) {
                $words .= ' and ' . convertLessThanThousand($num % 100, $units, $tens);
            }
        }

        return $words;
    }
}

if (!function_exists('numberToWords')) {
    function numberToWords($number) {
        // Handle zero case
        if ($number == 0) {
            return 'Zero Rupees Only';
        }

        // Split the number into integer and decimal parts
        $integerPart = floor($number);
        $decimalPart = round(($number - $integerPart) * 100); // For paise (2 decimal places)

        // Arrays for word conversion
        $units = [
            '', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten',
            'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'
        ];
        $tens = [
            '', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'
        ];
        $thousands = ['', 'Thousand', 'Lakh', 'Crore'];

        // Convert the integer part (Rupees)
        $rupeesWords = '';
        $num = $integerPart;
        $thousandIndex = 0;

        if ($num == 0) {
            $rupeesWords = 'Zero';
        } else {
            while ($num > 0) {
                if ($thousandIndex == 0 || $thousandIndex == 1) {
                    // For units and thousands
                    $chunk = $num % 1000;
                } else {
                    // For lakhs and crores (Indian numbering system: 1,00,000; 1,00,00,000)
                    $chunk = $num % 100;
                    $num = floor($num / 100);
                    if ($chunk == 0) {
                        $thousandIndex++;
                        continue;
                    }
                }

                if ($chunk > 0) {
                    $chunkWords = convertLessThanThousand($chunk, $units, $tens);
                    $rupeesWords = $chunkWords . ' ' . $thousands[$thousandIndex] . ($rupeesWords ? ' ' : '') . $rupeesWords;
                }

                if ($thousandIndex == 0 || $thousandIndex == 1) {
                    $num = floor($num / 1000);
                }
                $thousandIndex++;
            }
        }

        $result = $rupeesWords . ' Rupees';

        // Convert the decimal part (Paise)
        if ($decimalPart > 0) {
            $paiseWords = convertLessThanThousand($decimalPart, $units, $tens);
            $result .= ' and ' . $paiseWords . ' Paise';
        }

        $result .= ' Only';

        return $result;
    }
}