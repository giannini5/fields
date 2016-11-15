<?php

namespace DAG\Framework\Utils;

use DAG\Framework\Exception\Precondition;
use DAG\Framework\Services;

/**
 * Class comprises of common string manipulation functions
 */
class StringUtilities
{
    /**
     * Make a UTF-8 string fit into a set number of bytes. Will do so without mangling characters like substr.
     *
     * @param string  $string       The UTF-8 string to be cut.
     * @param integer $bytesAllowed The positive number of bytes the string is allowed to be.
     *
     * @return string trimmed UTF-8 string
     */
    public static function cutUTF8StringDownToSize($string, $bytesAllowed)
    {
        if (empty($string)) {
            return '';
        }
        Precondition::isPositiveInt($bytesAllowed, "bytesAllowed");

        // Trim extra multi-byte characters from string
        $strLength = strlen($string);
        if ($strLength > $bytesAllowed) {
            $string    = mb_substr($string, 0, $bytesAllowed, 'UTF-8');
            $strLength = $bytesAllowed;
        }

        // If string still contains multi-byte characters, could require additional cutting
        while (strlen($string) > $bytesAllowed) {
            $strLength--;
            $string = mb_substr($string, 0, $strLength, 'UTF-8');
        }

        // Take out chars other than UTF-8
        $string = iconv('UTF-8', 'UTF-8//IGNORE', $string);

        return $string;
    }

    /**
     * Convert UTF-16 characters to UTF-8 characters to facilitate Push Notification's display text on the Client
     *
     * @param string $string String that could contain UTF-16 characters
     *
     * @return string String with UTF-16 characters decoded to UTF-8
     */
    public static function convertAnyUTF16CharsToUTF8($string)
    {
        if (empty($string)) {
            return '';
        }

        $result = '';
        for ($index = 0; $index < strlen($string) - 3; $index++) {
            // UTF-16 characters are 4-bytes long
            $fourByteSubStringToCheck = substr($string, $index, 4);

            // Only valid UTF-16 characters are down-converted to 2-byte UTF-8 characters; otherwise false is returned
            $twoByteResultMaybe = iconv("UTF-16", "UTF-8", $fourByteSubStringToCheck);
            if (2 === strlen($twoByteResultMaybe)) {
                // Successful conversion happened so add result to output string and skip bytes that got converted
                $result .= $twoByteResultMaybe;
                $index  += 3;
            } else {
                // Unsuccessful conversion happened so add the first byte to output and prepare to check the next 4-byte chunk
                $result .= $string[$index];
            }
        }

        // Possible that the last 3 or fewer bytes of the input string remain unchecked (too few bytes to be converted) so make sure to add them to the output
        $result .= substr($string, $index);
        return $result;
    }

    /**
     * Encodes array/object into a JSON string. If PHP json_encode fails, it uses Services_JSON to encode the object/array
     *
     * @param array|object $data array/object to encode into JSON string
     *
     * @return string JSON string
     */
    public static function encodeInputIntoJSONStringWithBackup($data)
    {
        Precondition::isTrue(is_array($data) || is_object($data), 'input must be an array or object');

        $result    = @json_encode($data);
        $lastError = json_last_error();

        if (JSON_ERROR_NONE != $lastError || false === $result) {
            // Create a JSON object and do the encoding
            require_once $_SERVER['API_ROOT'] . '/3rdParty/json.php';

            $json   = new \Services_JSON();
            $result = $json->encode($data);
        }

        return $result;
    }

    /**
     * Given a JSON string, returns a string/array/object encoded in UTF-8
     *
     * @param string $string JSON string
     *
     * @return array|object|string depending on the JSON input
     */
    public static function jsonDecodeStringToUTF8EncodedOutput($string)
    {
        Precondition::isNonEmptyString($string, 'string');

        // UTF-8 encode the JSON string and JSON-decode it to an string/array/object
        $result = json_decode(utf8_encode($string));

        if (is_array($result)) {
            $result = self::utf8DecodeArrayElements($result);
        } elseif (is_object($result)) {
            $result = self::utf8DecodeObjectElements($result);
        } else {
            // Result is just a scalar value so just decode
            $result = utf8_decode($result);
        }

        return $result;
    }

    /**
     * Given an UTF-8 encoded, JSON decoded array/object/string/other, returns a UTF-8 decoded version of the input
     *
     * @param mixed[]|\stdClass|string $inputValue input to be UTF-8 decoded
     *
     * @return mixed[]|\stdClass|string UTF-8 decoded input
     */
    private static function utf8DecodeValue($inputValue)
    {
        if (is_array($inputValue)) {
            $output = self::utf8DecodeArrayElements($inputValue);
        } elseif (is_object($inputValue)) {
            $output = self::utf8DecodeObjectElements($inputValue);
        } else {
            // Should be scalar so just decode
            $output = utf8_decode($inputValue);
        }

        return $output;
    }

    /**
     * Given an UTF-8 encoded, JSON decoded array of array/object/string, returns an array with values UTF-8 decoded
     *
     * @param mixed[] $inputArray JSON decoded array (string $key => mixed $value)
     *
     * @return mixed[] array with values decoded
     */
    private static function utf8DecodeArrayElements($inputArray)
    {
        Precondition::isArray($inputArray, 'array');

        $output = array();
        foreach ($inputArray as $key => $value) {
            // Clean the key from whitespaces
            $key = trim($key);

            // Removed code to check for key with null value which cannot happen with successful json_decode()
            //   array keys will be converted to empty string by array() constructor ($array[""])
            //   null object keys will cause json_decode() to fail
            //   empty string ("") object keys will be converted to the _empty_ keyword ($object->_empty_)

            // UTF-8 decode the key
            $key = utf8_decode($key);

            // Recursively decode element properties
            $output[$key] = self::utf8DecodeValue($value);
        }

        return $output;
    }

    /**
     * Given an UTF-8 encoded, JSON decoded object containing array/object/string, returns an object with values UTF-8 decoded
     *
     * @param \stdClass $inputObject JSON decoded object (with properties string $key = mixed $value)
     *
     * @return \stdClass object with values decoded
     */
    private static function utf8DecodeObjectElements($inputObject)
    {
        Precondition::isObject($inputObject, 'object');

        $output           = new \stdClass();
        $objectProperties = get_object_vars($inputObject);
        foreach ($objectProperties as $key => $value) {
            // UTF-8 decode the key
            $key = utf8_decode($key);

            // Recursively decode element properties
            $output->$key = self::utf8DecodeValue($value);
        }

        return $output;
    }

    /**
     * Given an array/object, return a JSON string
     *
     * @param array/object $data data object
     *
     * @return string JSON string
     */
    public static function convertUTF8DecodedInputToJSONString($data)
    {
        Precondition::isTrue(is_array($data) || is_object($data), 'input must be an array or object');

        // json_encode() only works with UTF-8-encoded data so must encode $data
        if (is_object($data)) {
            $data = self::utf8EncodeObjectElements($data);
        } else {
            // Must be array due to precondition check
            $data = self::utf8EncodeArrayElements($data);
        }

        // Encode to a JSON string
        $jsonStr = json_encode($data);

        // Decode back from UTF-8 to native encoding
        $jsonStr = utf8_decode($jsonStr);

        return $jsonStr;
    }

    /**
     * Given an array/object/string/other, encodes the input to UTF-8
     *
     * @param mixed[]|\stdClass|string $inputValue input to be UTF-8 encoded
     *
     * @return mixed[]|\stdClass|string UTF-8 encoded input
     */
    private static function utf8EncodeValue($inputValue)
    {
        if (is_array($inputValue)) {
            $output = self::utf8EncodeArrayElements($inputValue);
        } elseif (is_object($inputValue)) {
            $output = self::utf8EncodeObjectElements($inputValue);
        } else {
            // Should be scalar so just encode
            $output = utf8_encode($inputValue);
        }

        return $output;
    }

    /**
     * Given an array, encodes the input to UTF-8
     *
     * @param mixed[] $inputArray array to encode
     *
     * @return mixed[] encoded object
     */
    private static function utf8EncodeArrayElements($inputArray)
    {
        Precondition::isArray($inputArray, 'inputArray');

        $output = array();

        // Loop through the array elements and encode
        foreach ($inputArray as $key => $value) {
            // Clean the key from whitespaces
            $key = trim($key);

            if (is_null($key)) {
                continue;
            }

            $key = utf8_encode($key);

            // Recursively encode element properties
            $output[$key] = self::utf8EncodeValue($value);
        }

        return $output;
    }

    /**
     * Given an object, encodes the input to UTF-8
     *
     * @param mixed $inputObject object to encode
     *
     * @return \stdClass encoded object
     */
    private static function utf8EncodeObjectElements($inputObject)
    {
        Precondition::isObject($inputObject, 'inputObject');

        $output           = new \stdClass();
        $objectProperties = get_object_vars($inputObject);

        // Loop through the object properties and encode
        foreach ($objectProperties as $key => $value) {
            // UTF-8 encode the key
            $key = utf8_encode($key);

            // Recursively encode element properties
            $output->$key = self::utf8EncodeValue($value);
        }

        return $output;
    }

    /**
     * Given an string, checks whether input contains multibyte characters
     *
     * @param string $string input to test
     *
     * @return bool true if multibyte, false otherwise
     */
    public static function isMultibyte($string)
    {
        return strlen($string) != mb_strlen($string) || strlen($string) != mb_strlen(utf8_decode($string));
    }

    /**
     * Checks whether a string is UTF-8 compliant
     *
     * @param string $string input to test
     *
     * @return bool true if input is UTF-8 compliant, false otherwise
     */
    public static function isStringUTF8Compliant($string)
    {
        $result = true;

        if (0 < strlen($string)) {
            $result = (1 === preg_match('/^.{1,}/us', $string));
        }

        return $result;
    }

    /**
     * Given an array/object/string, checks whether input is UTF-8 compliant
     *
     * @param mixed $data input to check
     *
     * @return bool true if input is UTF-8 compliant, false otherwise
     */
    public static function isInputUTF8Compliant($data)
    {
        // Check for UTF-8 compatibility
        if (is_object($data)) {
            $result = self::isObjectUTF8Compliant($data);
        } elseif (is_array($data)) {
            $result = self::isArrayUTF8Compliant($data);
        } else {
            // Handles scalar values that could be treated as a string
            $result = self::isStringUTF8Compliant($data);
        }

        return $result;
    }

    /**
     * Given an array, checks whether input is UTF-8 compliant
     *
     * @param mixed[] $inputArray array to check
     *
     * @return bool true is UFT-8 compliant, false otherwise
     */
    private static function isArrayUTF8Compliant($inputArray)
    {
        Precondition::isArray($inputArray, "inputArray");

        $result = true;

        foreach ($inputArray as $key => $value) {
            if (!self::isStringUTF8Compliant($key)) {
                $result = false;
                break;
            }
            $result = self::isInputUTF8Compliant($value);
            if (!$result) {
                // Once any part of the input is found to be non-UTF-8 compatible, exit the loop
                break;
            }
        }

        return $result;
    }

    /**
     * Given an object, checks whether object is UTF-8 compliant
     *
     * @param mixed $inputObject data to check
     *
     * @return bool true if UTF-8 compliant, false otherwise
     */
    private static function isObjectUTF8Compliant($inputObject)
    {
        Precondition::isObject($inputObject, "inputObject");

        $result = true;

        $objectProperties = get_object_vars($inputObject);
        foreach ($objectProperties as $key => $value) {
            if (!self::isStringUTF8Compliant($key)) {
                $result = false;
                break;
            }
            $result = self::isInputUTF8Compliant($value);
            if (!$result) {
                // Once any part of the input is found to be non-UTF-8 compatible, exit the loop
                break;
            }
        }

        return $result;
    }
}
