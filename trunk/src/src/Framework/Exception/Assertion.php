<?php

namespace DAG\Framework\Exception;

/**
 * Contains methods for verifying assertions.
 * If an assertion fails, an \AssertionException is thrown.
 */
class Assertion
{
    /**
     * Statement must strictly equal true
     *
     * @param boolean $statement    Boolean expression
     * @param string  $message      Error string when expression is false
     * @throws \AssertionException
     */
    public static function isTrue($statement, $message)
    {
        if ($statement !== true) {
            static::throwException($message);
        }
    }

    /**
     * Helper function to throw an \AssertionException
     *
     * @param string    $message    Error message
     * @throws \AssertionException
     */
    protected static function throwException($message)
    {
        $backtrace = debug_backtrace();
        $lastCall = $backtrace[1];
        throw new \AssertionException($message, $lastCall['file'], $lastCall['line'], -1);
    }
}
