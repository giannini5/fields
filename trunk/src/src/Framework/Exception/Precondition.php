<?php

namespace DAG\Framework\Exception;
use DAG\Framework\Exception\PreconditionException;

/**
 * Contains methods for verifying preconditions.
 * If a precondition fails, a \PreconditionException is thrown.
 */
class Precondition
{
    /**
     * Statement must be non-empty (not 0, null, etc)
     *
     * @param mixed     $statement  Any value
     * @param string    $message    Error message if expression is false
     * @throws \PreconditionException
     */
    public static function isNonEmpty($statement, $message)
    {
        if (!$statement) {
            static::throwException($message);
        }
    }

    /**
     * Statement must strictly equal true
     *
     * @param boolean   $statement  Boolean expression
     * @param string    $message    Error message if expression is false
     * @throws \PreconditionException
     */
    public static function isTrue($statement, $message)
    {
        if ($statement !== true) {
            static::throwException($message);
        }
    }

    /**
     * Value must be a boolean
     *
     * @param mixed     $value          Value being tested
     * @param string    $variableName   Name of variable to help with debugging
     * @throws \PreconditionException
     */
    public static function isBool($value, $variableName)
    {
        $ok = is_bool($value);
        if (!$ok) {
            static::throwException("$variableName is not a bool [" . print_r($value, true) . ']');
        }
    }

    /**
     * Value must be an integer (no strict-type checking)
     *
     * @param mixed     $value          Value being tested
     * @param string    $variableName   Name of variable to help with debugging
     * @throws \PreconditionException
     */
    public static function isInt($value, $variableName)
    {
        $ok = is_numeric($value) && intval($value) == $value;
        if (!$ok) {
            static::throwException("$variableName is not an integer [" . print_r($value, true) . ']');
        }
    }

    /**
     * Value must be a positive integer (no strict-type checking)
     *
     * @param mixed     $value          Value being tested
     * @param string    $variableName   Name of variable to help with debugging
     * @throws \PreconditionException
     */
    public static function isPositiveInt($value, $variableName)
    {
        $ok = is_numeric($value) && intval($value) == $value && $value > 0;
        if (!$ok) {
            static::throwException("$variableName not a positive integer:[" . print_r($value, true) . ']');
        }
    }

    /**
     * Value must be a non-negative integer (no strict-type checking)
     *
     * @param mixed     $value          Value being tested
     * @param string    $variableName   Name of variable to help with debugging
     * @throws \PreconditionException
     */
    public static function isNonNegativeInt($value, $variableName)
    {
        $ok = is_numeric($value) && intval($value) == $value && $value >= 0;
        if (!$ok) {
            static::throwException("$variableName is not a non-negative integer [" . print_r($value, true) . ']');
        }
    }

    /**
     * Value must be an integer within inclusive range (no strict-type checking)
     *
     * @param mixed     $value          Value being tested
     * @param string    $variableName   Name of variable to help with debugging
     * @param int       $minValue       Min accepted value
     * @param int       $maxValue       Max accepted value
     * @throws \PreconditionException
     */
    public static function isIntRange($value, $variableName, $minValue, $maxValue)
    {
        $ok = is_numeric($value) && intval($value) >= $minValue && intval($value) <= $maxValue;
        if (!$ok) {
            static::throwException(
                "$variableName is not an integer in the inclusive range [$minValue, $maxValue] [" .
                print_r($value, true) .
                ']'
            );
        }
    }

    /**
     * Value must be a float (no strict-type checking)
     *
     * @param mixed     $value          Value being tested
     * @param string    $variableName   Name of variable to help with debugging
     * @throws \PreconditionException
     */
    public static function isFloat($value, $variableName)
    {
        $ok = is_numeric($value) && floatval($value) == $value;
        if (!$ok) {
            static::throwException("$variableName is not a float [" . print_r($value, true) . ']');
        }
    }

    /**
     * Value must be a positive float (no strict-type checking)
     *
     * @param mixed     $value          Value being tested
     * @param string    $variableName   Name of variable to help with debugging
     * @throws \PreconditionException
     */
    public static function isPositiveFloat($value, $variableName)
    {
        $ok = is_numeric($value) && floatval($value) == $value && $value > 0;
        if (!$ok) {
            static::throwException("$variableName is not a positive float [" . print_r($value, true) . ']');
        }
    }

    /**
     * Value must be a non-negative float (no strict-type checking)
     *
     * @param mixed     $value          Value being tested
     * @param string    $variableName   Name of variable to help with debugging
     * @throws \PreconditionException
     */
    public static function isNonNegativeFloat($value, $variableName)
    {
        $ok = is_numeric($value) && floatval($value) == $value && $value >= 0;
        if (!$ok) {
            static::throwException("$variableName is not a non-negative float [" . print_r($value, true) . ']');
        }
    }

    /**
     * Value must be a string
     *
     * @param mixed     $value          Value being tested
     * @param string    $variableName   Name of variable to help with debugging
     * @throws \PreconditionException
     */
    public static function isString($value, $variableName)
    {
        $ok = is_string($value);
        if (!$ok) {
            static::throwException("$variableName is not a string [" . print_r($value, true) . ']');
        }
    }

    /**
     * Value must be a non-empty string
     *
     * @param mixed     $value          Value being tested
     * @param string    $variableName   Name of variable to help with debugging
     * @throws \PreconditionException
     */
    public static function isNonEmptyString($value, $variableName)
    {
        $ok = is_string($value) && $value != '';
        if (!$ok) {
            static::throwException("$variableName is not a non-empty string [" . print_r($value, true) . ']');
        }
    }

    /**
     * Value must be a non-empty string with length not exceeding the provided limit
     *
     * @param mixed     $value          Value being tested
     * @param string    $variableName   Name of variable to help with debugging
     * @param int       $maxLength      Max allowed length of string
     * @throws \PreconditionException
     */
    public static function isStringMaxLength($value, $variableName, $maxLength)
    {
        $strLength = strlen($value);
        $ok = is_string($value) && $strLength > 0 && $strLength <= $maxLength;
        if (!$ok) {
            static::throwException(
                "$variableName is not a string with length in [1, $strLength] [" .
                print_r($value, true) .
                ']'
            );
        }
    }

    /**
     * Value must be a non-empty string with length matching the provided limit
     *
     * @param mixed     $value          Value being tested
     * @param string    $variableName   Name of variable to help with debugging
     * @param int       $exactLength    Exact length required for string
     * @throws \PreconditionException
     */
    public static function isStringExactLength($value, $variableName, $exactLength)
    {
        $strLength = strlen($value);
        $ok = is_string($value) && $strLength == $exactLength;
        if (!$ok) {
            static::throwException(
                "$variableName is not a string with length $strLength [" .
                print_r($value, true) .
                ']'
            );
        }
    }

    /**
     * Value must be a scalar
     *
     * @param mixed     $value          Value being tested
     * @param string    $variableName   Name of variable to help with debugging
     * @throws \PreconditionException
     */
    public static function isScalar($value, $variableName)
    {
        $ok = is_scalar($value);
        if (!$ok) {
            static::throwException("$variableName is not a scalar [" . print_r($value, true) . ']');
        }
    }

    /**
     * Value must be an array
     *
     * @param mixed     $value          Value being tested
     * @param string    $variableName   Name of variable to help with debugging
     * @throws \PreconditionException
     */
    public static function isArray($value, $variableName)
    {
        $ok = is_array($value);
        if (!$ok) {
            static::throwException("$variableName is not an array [" . print_r($value, true) . ']');
        }
    }

    /**
     * Value must be a non-empty array
     *
     * @param mixed     $value          Value being tested
     * @param string    $variableName   Name of variable to help with debugging
     * @throws \PreconditionException
     */
    public static function isNonEmptyArray($value, $variableName)
    {
        $ok = is_array($value) && count($value) > 0;
        if (!$ok) {
            static::throwException("$variableName is not an array or is empty [" . print_r($value, true) . ']');
        }
    }

    /**
     * Key must exist in array. You must check that $array is an array before calling this.
     *
     * @param array     $array          Array to be tested
     * @param string    $key            Key to be searched for in array
     * @param string    $variableName   Name of variable to help with debugging
     * @throws \PreconditionException
     */
    public static function arrayKeyExists($array, $key, $variableName = '')
    {
        $ok = array_key_exists($key, $array);
        if (!$ok) {
            static::throwException("key $key not in array $variableName");
        }
    }

    /**
     * Value must exist in array. You must check that $array is an array before calling this.
     *
     * @param array     $array          Array to be tested
     * @param mixed     $value          Value to be searched for in array
     * @param string    $variableName   Name of variable to help with debugging
     * @throws \PreconditionException
     */
    public static function arrayValueExists($array, $value, $variableName = '')
    {
        $ok = in_array($value, $array);
        if (!$ok) {
            static::throwException('value ' . print_r($value, true) . " is null or is not in array $variableName");
        }
    }

    /**
     * Value must be an object
     *
     * @param mixed     $value          Value to be tested to see if it is an object
     * @param string    $variableName   Name of variable to help with debugging
     * @throws \PreconditionException
     */
    public static function isObject($value, $variableName)
    {
        $ok = is_object($value);
        if (!$ok) {
            static::throwException("$variableName is not an object [" . print_r($value, true) . ']');
        }
    }

    /**
     * Property must exist in object. You must check that $object is an object before calling this.
     *
     * @param string    $object         Object to be tested
     * @param string    $property       Property of object to be tested
     * @param string    $variableName   Name of variable to help with debugging
     * @throws \PreconditionException
     */
    public static function objectPropertyExists($object, $property, $variableName = '')
    {
        $ok = isset($object->$property);
        if (!$ok) {
            static::throwException("property $property not in object $variableName");
        }
    }

    /**
     * Property must exist in object and not be empty. You must check that $object is an object before calling this.
     *
     * @param string    $object         Object to be tested
     * @param string    $property       Property of object to be tested
     * @param string    $variableName   Name of variable to help with debugging
     * @throws \PreconditionException
     */
    public static function isObjectPropertyNonEmpty($object, $property, $variableName = '')
    {
        $ok = !empty($object->$property);
        if (!$ok) {
            static::throwException("property $property is empty, $variableName");
        }
    }

    /**
     * Value must be parseable by strtotime(). You must check that $value is a string before calling this.
     *
     * @param mixed     $value          Value being tested
     * @param string    $variableName   Name of variable to help with debugging
     * @throws \PreconditionException
     */
    public static function isDate($value, $variableName)
    {
        $ok = strtotime($value) !== false;
        if (!$ok) {
            static::throwException("$variableName not a valid date [" . print_r($value, true) . ']');
        }
    }

    /**
     * Helper function to throw a \PreconditionException
     *
     * @param string    $message    Error message
     * @throws \PreconditionException
     */
    protected static function throwException($message)
    {
        $backtrace = debug_backtrace();
        $lastCall = $backtrace[1];
        throw new PreconditionException($message, $lastCall['file'], $lastCall['line'], -1, '500 Internal Server Error');
    }
}
