<?php

use \DAG\Framework\Exception\Assertion;
use \DAG\Framework\Exception\Precondition;

/**
 * @testSuite test SortTest
 */
class SortTest extends \PHPUnit_Framework_TestCase
{
    public function test_bubbleSort()
    {
        $this->assertEquals('1234', $this->bubbleSort('4321'));
        $this->assertEquals('1234', $this->bubbleSort('3241'));
        $this->assertEquals('1234', $this->bubbleSort('4213'));
        $this->assertEquals('1234', $this->bubbleSort('1432'));
        $this->assertEquals('1234', $this->bubbleSort('4132'));
        $this->assertEquals('123455789', $this->bubbleSort('378521954'));
    }

    public function test_quickSort()
    {
        $string = '378521954';
        $this->assertEquals('123455789', $this->quickSort($string));
        $string = '4321';
        $this->assertEquals('1234', $this->quickSort($string));
        $string = '3241';
        $this->assertEquals('1234', $this->quickSort($string));
        $string = '4213';
        $this->assertEquals('1234', $this->quickSort($string));
        $string = '1432';
        $this->assertEquals('1234', $this->quickSort($string));
        $string = '4132';
        $this->assertEquals('1234', $this->quickSort($string));
    }

    public function test_mergeSort()
    {
        $string = '378521954';
        $this->assertEquals('123455789', $this->msort($string));
        $string = '4321';
        $this->assertEquals('1234', $this->msort($string));
        $string = '3241';
        $this->assertEquals('1234', $this->msort($string));
        $string = '4213';
        $this->assertEquals('1234', $this->msort($string));
        $string = '1432';
        $this->assertEquals('1234', $this->msort($string));
        $string = '4132';
        $this->assertEquals('1234', $this->msort($string));
    }

    public function bubbleSort($string)
    {
        for ($i = 0; $i < strlen($string); ++$i) {
            $j = $i;
            $bubble = $string[$i];
            while ($j > 0) {
                if ($bubble < $string[$j - 1]) {
                    $string[$j] = $string[$j - 1];
                    $string[$j - 1] = $bubble;
                }
                $j = $j - 1;
            }
        }

        return $string;
    }

    public function quickSort(&$string, $startIndex = null, $endIndex = null)
    {
        $startIndex = isset($startIndex) ? $startIndex : 0;
        $endIndex = isset($endIndex) ? $endIndex : strlen($string) - 1;

        if ($startIndex >= $endIndex) {
            return $string;
        }

        $pivotIndex = (int)($startIndex + floor((($endIndex - $startIndex) + 1) / 2));
        Assertion::isTrue($pivotIndex >= $startIndex, "pivotIndex: $pivotIndex is less than startIndex: $startIndex");
        Assertion::isTrue($pivotIndex <= $endIndex, "pivotIndex: $pivotIndex is greater than endIndex: $endIndex");

        $pivotItem = $string[$pivotIndex];

        // Process items that occur before the pivot
        $i = $startIndex;
        while ($i < $pivotIndex) {
            if ($string[$i] > $string[$pivotIndex]) {
                // Swap pivot item and prior item
                $tempItem = $string[$pivotIndex - 1];
                $string[$pivotIndex - 1] = $pivotItem;
                $string[$pivotIndex] = $tempItem;

                // Swap indexed item with original pivot slot if not swapped above
                if ($i != $pivotIndex - 1) {
                    $string[$pivotIndex] = $string[$i];
                    $string[$i] = $tempItem;
                }

                $pivotIndex -= 1;
            } else {
                $i += 1;
            }
        }

        // Process items that occur after the pivot
        $i = $endIndex;
        while ($i > $pivotIndex) {
            if ($string[$i] < $string[$pivotIndex]) {
                // Swap pivot item and next item
                $tempItem = $string[$pivotIndex + 1];
                $string[$pivotIndex + 1] = $pivotItem;
                $string[$pivotIndex] = $tempItem;

                // Swap indexed item with original pivot slot if not swapped above
                if ($i != $pivotIndex + 1) {
                    $string[$pivotIndex] = $string[$i];
                    $string[$i] = $tempItem;
                }

                $pivotIndex += 1;
            } else {
                $i -= 1;
            }
        }

        // Process the list before resulting pivot
        $this->quickSort($string, $startIndex, $pivotIndex - 1);

        // Process the list after the resulting pivot
        $this->quickSort($string, $pivotIndex + 1, $endIndex);

        return $string;
    }

    public function msort($string)
    {
        Precondition::isNonEmptyString($string, "Invalid string");

        // Split the string into smaller strings starting as size 1 and continuing to strlen / 2
        // Then merge sort pairs
        // Return final result
        $maxLength      = strlen($string) / 2;
        $strings        = str_split($string, 1);
        while (count($strings) > 1) {
            $index          = 0;
            $resultStrings  = [];
            while ($index < count($strings) - 1) {
                $resultStrings[] = $this->mergeSort($strings[$index], $strings[$index + 1]);
                $index = $index + 2;
            }

            // Merge last entry for odd case
            if ($index < count($strings)) {
                $resultStrings[] = $strings[$index];
            }

            $strings = $resultStrings;
        }

        return $strings[0];
    }

    public function mergeSort($stringA, $stringB)
    {
        // merge the two strings and return resulting string
        $resultString = '';
        $stringAIndex = 0;
        $stringBIndex = 0;
        while ($stringAIndex < strlen($stringA) or $stringBIndex < strlen($stringB)) {
            if ($stringAIndex >= strlen($stringA)) {
                $resultString .= $stringB[$stringBIndex++];
            } else if ($stringBIndex >= strlen($stringB)) {
                $resultString .= $stringA[$stringAIndex++];
            } else {
                if ($stringA[$stringAIndex] < $stringB[$stringBIndex]) {
                    $resultString .= $stringA[$stringAIndex++];
                } else {
                    $resultString .= $stringB[$stringBIndex++];
                }
            }
        }

        return $resultString;
    }
}