<?php

namespace DAG\Framework\Utils;

use DAG\Framework\Exception\Precondition;

/**
 * Class TimeUtils
 * This class comprises of common time manipulation functions
 * @package DAG\Framework\Utils
 */
class TimeUtils
{
    //defaults
    const BIG_BANG_TIME = '0000-00-00 00:00:00';

    // Constant for calculating the dw calendarId
    const DATE_SEED_CALENDAR_ID = '2005-12-31';

    // CalendarId to BirthDateId Conversion
    const CALENDER_ID_TO_BIRTH_DATE_ID        = 38894;
    const CALENDAR_MONTH_ID_TO_BIRTH_MONTH_ID =  1272;

    // Constants for basic date formats
    const SQL_DATETIME_FORMAT = 'Y-m-d H:i:s';
    const DATE_SHORT_FORMAT   = 'Y-m-d';
    const ISO8601_FORMAT      = 'c';
    const SQL_TIME_FORMAT     = 'H:i:s';
    const TIMESTAMP_SECONDS   = 'U';

    // constants for time conversions
    const MAX_DAYS_IN_WEEK = 7;
    const DAY_IN_SECONDS   = 86400;
    const HOUR_IN_SECONDS  = 3600;
    const YEAR_IN_MONTHS   = 12;

    /**
     * Get the date/time in the UTC timezone for a given epoch time.
     *
     * @param int    $time   - number of seconds since the Unix Epoch (January 1 1970 00:00:00 GMT).
     * @param string $format - The format of the outputted date string. Default is SQL_DATETIME_FORMAT
     *
     * @return string - a formatted date/time string
     */
    public static function getUTCDateTime($time, $format = self::SQL_DATETIME_FORMAT)
    {
        Precondition::isNonNegativeInt($time, 'time');

        return gmdate($format, $time);
    }

    /**
     * Get the date/time in the local server timezone for a given epoch time.
     *
     * @param int    $time   - number of seconds since the Unix Epoch (January 1 1970 00:00:00 GMT).
     * @param string $format - The format of the outputted date string.
     *
     * @return string - a formatted date/time string
     */
    public static function getServerDateTime($time, $format = self::SQL_DATETIME_FORMAT)
    {
        Precondition::isNonNegativeInt($time, 'time');

        return date($format, $time);
    }

    /**
     * Convert the datetime to unix timestamp
     *
     * @param string $date - date string
     *
     * @return int - the epoch time associated with the date string passed in
     */
    public static function getUTCTimestampFromDateTime($date)
    {
        Precondition::isDate($date, 'date');

        return strtotime($date);
    }

    /**
     * Get a date in the UTC timezone for the current time in the requested format
     *
     * @param int    $decimals - resolution of microseconds
     * @param string $format   - The format of the outputted date string.
     *
     * @return string a formatted string with microseconds appended
     */
    public static function getCurrentUTCDateTimeWithMicroseconds($decimals, $format)
    {
        Precondition::isPositiveInt($decimals, 'decimals');

        list($usec, $sec) = explode(' ', microtime());

        $usec = str_replace('0.', '', $usec);

        if (strlen($usec) > $decimals) {
            $usec = substr($usec, 0, $decimals);
        }

        $format = str_replace('s', "s.{$usec}", $format);

        return gmdate($format, $sec);
    }

    /**
     * Returns a Date/Time exactly X seconds ago from reference date/time.
     *
     * @param int    $time       - number of seconds since the Unix Epoch (January 1 1970 00:00:00 GMT).
     * @param int    $secondsAgo - number of seconds ago. Note: a negative number for X seconds will return some time in the future.'
     * @param string $format     - The format of the outputted date string.
     *
     * @return string - date/time exactly X seconds ago from now
     */
    public static function getUTCXSecondsAgo($time, $secondsAgo = 0, $format = self::SQL_DATETIME_FORMAT)
    {
        Precondition::isNonNegativeInt($time, 'time');
        Precondition::isInt($secondsAgo, 'secondsAgo');

        return gmdate($format, $time - $secondsAgo);
    }

    /**
    * Gets the calendar Id for a date that is used by Datawarehouse
    *
    * @param string $date - date string
    *
    * @return int
    */
    public static function getCalendarId($date)
    {
        Precondition::isDate($date, 'date');

        $datetime1 = new \DateTime(self::DATE_SEED_CALENDAR_ID);
        $datetime2 = new \DateTime($date);
        $interval  = $datetime1->diff($datetime2);
        $sign      = 0 < $interval->invert ? -1 : 1;

        return (int)($sign * $interval->format('%a'));
    }

    /**
     * Converts the calendarId to a date
     *
     * @param int $calendarId - calendar Id used by the DW to represent a specific date
     *
     * @return string
     */
    public static function getDateFromCalendarId($calendarId)
    {
        Precondition::isInt($calendarId, 'calendarId');

        $date = new \DateTime(self::DATE_SEED_CALENDAR_ID);

        if ($calendarId < 0) {
            $calendarId *= -1;
            $date->sub(new \DateInterval("P{$calendarId}D"));
        } else {
            $date->add(new \DateInterval("P{$calendarId}D"));
        }

        return $date->format('Y-m-d');
    }

    /**
     * Gets the calendar month Id for a date that is used by Datawarehouse
     *
     * @param string $date - date string
     *
     * @return int
     */
    public static function getCalendarMonthId($date)
    {
        Precondition::isDate($date, 'date');

        $referenceDate = new \DateTime(self::DATE_SEED_CALENDAR_ID);
        $targetDate    = new \DateTime($date);
        $monthId = NULL;

        if (0 < self::getDateTimeDiffInDays($date, self::DATE_SEED_CALENDAR_ID)) {
            $referenceDate->add(new \DateInterval('P1D'));
            $yearsDifference = $targetDate->format('Y') - $referenceDate->format('Y');
            $monthId         = (int)(12 * $yearsDifference) + (int)$targetDate->format('n');
        } else {
            $yearsDifference  = $referenceDate->format('Y') - $targetDate->format('Y');
            $monthsDifference = $referenceDate->format('n') - $targetDate->format('n');
            $monthId          = (int)(-12 * $yearsDifference) - (int)$monthsDifference;
        }

        return $monthId;
    }

    /**
     * Gets the calendar week Id for a date that is used by Datawarehouse
     *
     * @param string $date - date string
     *
     * @return int
     */
    public static function getCalendarWeekId($date)
    {
        $calendarId = self::getCalendarId($date);
        $weekId     = (int)(($calendarId - 1) / 7) + 1;

        return $weekId < 53 ? $weekId : $weekId - 52;
    }

    /**
     * Calculates a birth month ID from a date of birth with reference to
     *
     * @param string $dateOfBirth - date of birth
     *
     * @return int  - birth month ID
     */
    public static function getBirthMonthIdByDateOfBirth($dateOfBirth)
    {
        return (int)(self::getCalendarMonthId($dateOfBirth) + self::CALENDAR_MONTH_ID_TO_BIRTH_MONTH_ID);
    }

    /**
     * Calculates a birth month ID from an age as of now
     *
     * @param int $age - age of user, used to calculate their pseudo-date of birth
     *
     * @return int - birth month ID
     */
    public static function getMinBirthMonthIdByAge($age)
    {
        Precondition::isNonNegativeInt($age, 'age');

        $date = new \DateTime();
        $date->sub(new \DateInterval("P{$age}Y"));

        return self::getBirthMonthIdByDateOfBirth($date->format('Y-m-d'));
    }

    /**
     * Returns the number of days since the oldest living person as of 2015-11-13 (Susannah Mushatt Jones)
     *
     * @param string $dateOfBirth - date of birth
     *
     * @return int
     */
    public static function getBirthDateId($dateOfBirth)
    {
        return (int)(self::getCalendarId($dateOfBirth) + self::CALENDER_ID_TO_BIRTH_DATE_ID);
    }

    /**
     * Calculates a birth month ID from an age as of now
     *
     * @param int $age - age of user, used to calcuate their pseuo-date of birth
     *
     * @return int - birth month ID
     */
    public static function getMaxBirthMonthIdByAge($age)
    {
        Precondition::isNonNegativeInt($age, 'age');

        $date = new \DateTime();
        ++$age;
        $date->sub(new \DateInterval("P{$age}Y"));

        return (self::getBirthMonthIdByDateOfBirth($date->format('Y-m-d')));
    }

    /**
     * Calculate the age by the birthday
     *
     * @param $birthday - yyyy-mm-dd date string
     *
     * @return int - age in years
     */
    public static function getAgeByBirthday($birthday)
    {
        Precondition::isDate($birthday, 'birthday');

        $from = new \DateTime($birthday);
        $to   = new \DateTime('today');

        return $from->diff($to)->y;
    }

    /**
     * Converts the unix timestamp to an hour ID
     *
     * @param int $time - number of seconds since the Unix Epoch (January 1 1970 00:00:00 GMT).
     *
     * @return int - number of hours since the Unix Epoch (January 1 1970 00:00:00 GMT).
     */
    public static function getHourId($time)
    {
        Precondition::isNonNegativeInt($time, 'time');

        return (int)($time / self::HOUR_IN_SECONDS);
    }

    /**
     * Converts hourId into a UTC datetime
     *
     * @param int $hourId - number of hours since the Unix Epoch (January 1 1970 00:00:00 GMT).
     *
     * @return string - UTC date/time in 'Y-m-d H:i:s' format
     */
    public static function getUTCDateTimeFromHourId($hourId)
    {
        Precondition::isNonNegativeInt($hourId, 'hourId');

        return self::getUTCDateTime($hourId * self::HOUR_IN_SECONDS);
    }

    /**
     * Returns day of week in UTC for a Date Time string
     *
     * @param string $dateTime - String of data time of an acceptable format
     *
     * @return int - Numeric representation of the day of the week. 0 (for Sunday) through 6 (for Saturday)
     */
    public static function getDayOfWeek($dateTime)
    {
        Precondition::isDate($dateTime, 'dateTime');

        return (int)gmdate('w', strtotime($dateTime));
    }

    /**
     * Returns day of month in UTC for a Date Time string
     *
     * @param int $dateTime - String of data time of an acceptable format
     *
     * @return int - Day of the month without leading zeros
     */
    public static function getDayOfMonth($dateTime)
    {
        Precondition::isDate($dateTime, 'dateTime');

        return (int)gmdate('j', strtotime($dateTime));
    }

    /**
     * Returns day of year in UTC for a Date Time string
     *
     * @param int $dateTime - String of data time of an acceptable format
     *
     * @return int - The day of the year (starting from 0)
     */
    public static function getDayOfYear($dateTime)
    {
        Precondition::isDate($dateTime, 'dateTime');

        return (int)gmdate('z', strtotime($dateTime));
    }

    /**
     * GetWeekOfYear computes the week number of the year in which the date fits.
     *
     * @param string $date - the date in format YYYY-MM-DD
     *
     * @return string $weekNumber - a string number between '01' and '52'
     */
    public static function getWeekOfYear($date)
    {
        Precondition::isDate($date, 'date');

        $dateObj    = new \DateTime($date);
        $month      = $dateObj->format('m');
        $weekOfYear = $dateObj->format('W');

        // According to ISO 8601 doc (http://en.wikipedia.org/wiki/ISO_8601#Week_dates)
        // we try to avoid having days in next year 1st week or last year last week.
        // Also, when the 53rd week exists, put those days in the 52nd week.
        if (1 == (int)$month && 1 < (int)$weekOfYear) {
            return '01';
        } elseif (12 == (int)$month && (52 < (int)$weekOfYear || 1 == (int)$weekOfYear)) {
            return '52';
        } else {
            return $weekOfYear;
        }
    }

    /**
     * Get a unique day-of-week list given a datetime limit in the past
     *
     * @param string $dateTimeInPast - The Date Time limit in the past, format of YYYY-MM-DD HH:MM:SS
     *
     * @return int[] - List of day-of-week depending on the limit
     */
    public static function getDayOfWeekList($dateTimeInPast)
    {
        Precondition::isDate($dateTimeInPast, 'dateTimeInPast');

        // interval limitations
        $todayDateObj = new \DateTime(self::getUTCDateTime(time()));

        //create day of week list
        $dowList         = array();
        $pastDateTimeObj = new \DateTime($dateTimeInPast);
        $interval        = $pastDateTimeObj->diff($todayDateObj);

        while (0 <= $interval->days) {
            $dowList[] = self::getDayOfWeek($todayDateObj->format(self::SQL_DATETIME_FORMAT));

            // if at the limit in the past, we're done!
            if (0 === $interval->days || self::MAX_DAYS_IN_WEEK < count($dowList)) {
                break;
            }

            $todayDateObj->sub(new \DateInterval('P1D'));
            $interval = $pastDateTimeObj->diff($todayDateObj);
        }

        return array_unique($dowList);
    }

    /**
     * Returns the latest date time given two date time strings
     *
     * @param string $dateTime1 - Date time in the format YYYY-MM-DD HH:MM:SS
     * @param string $dateTime2 - Date time in the format YYYY-MM-DD HH:MM:SS
     *
     * @return string - The bigger/latest date time string
     */
    public static function getMaxDateTime($dateTime1, $dateTime2)
    {
        Precondition::isDate($dateTime1, 'dateTime1');
        Precondition::isDate($dateTime2, 'dateTime2');

        $dt1Obj = new \DateTime($dateTime1);
        $dt2Obj = new \DateTime($dateTime2);

        return ($dt1Obj > $dt2Obj) ?
            $dt1Obj->format(self::SQL_DATETIME_FORMAT) :
            $dt2Obj->format(self::SQL_DATETIME_FORMAT);
    }

    /**
     * Returns the difference between two date time strings in days ($dateTime2 - $dateTime1 = difference)
     *
     * @param string $dateTime1 - Date/time reference 1
     * @param string $dateTime2 - Date time reference 2
     *
     * @return int - The difference in days
     *
     */
    public static function getDateTimeDiffInDays($dateTime1, $dateTime2)
    {
        Precondition::isDate($dateTime1, 'dateTime1');
        Precondition::isDate($dateTime2, 'dateTime2');

        $dt1Obj = new \DateTime($dateTime1);
        $dt2Obj = new \DateTime($dateTime2);

        return ($dt1Obj > $dt2Obj) ?
            -1 * (int)$dt1Obj->diff($dt2Obj)->format('%a') :
            (int)$dt2Obj->diff($dt1Obj)->format('%a');
    }

    /**
     * Returns the difference between two date time strings in seconds ($dateTime2 - $dateTime1 = difference)
     *
     * @param string $dateTime1 - Date/time reference 1
     * @param string $dateTime2 - Date time reference 2
     *
     * @return int - The difference in seconds
     *                  dateTime2 > dateTime1: positive number
     *                  dateTime2 == dateTime1: 0
     *                  dateTime2 < dateTime1: negative number
     */
    public static function getDateTimeDiffInSeconds($dateTime1, $dateTime2)
    {
        Precondition::isDate($dateTime1, 'dateTime1');
        Precondition::isDate($dateTime2, 'dateTime2');

        $dt1Obj = new \DateTime($dateTime1);
        $dt2Obj = new \DateTime($dateTime2);

        return $dt2Obj->getTimestamp() - $dt1Obj->getTimestamp();
    }

    /**
     * Converts a date/time from one timezone to another
     *
     * @param string $dateTime     - date/time string
     * @param string $fromTimeZone - current timezone for date/time that is passed in
     * @param string $toTimeZone   - timezone to switch to
     * @param string $format       - php date/time format of the result. Default is SQL_DATETIME_FORMAT
     *
     * @return string - date/time that was passed in converted to the toTimezone
     */
    public static function convertDateTimeBetweenTimeZones(
        $dateTime,
        $fromTimeZone,
        $toTimeZone,
        $format = self::SQL_DATETIME_FORMAT)
    {
        Precondition::isDate($dateTime, 'dateTime');
        Precondition::isNonEmptyString($fromTimeZone, 'fromTimeZone');
        Precondition::isNonEmptyString($toTimeZone, 'toTimeZone');

        $fromTimeZoneObj = new \DateTimeZone($fromTimeZone);
        $toTimeZoneObj   = new \DateTimeZone($toTimeZone);
        $date            = new \DateTime($dateTime, $fromTimeZoneObj);

        $date->setTimezone($toTimeZoneObj);

        return $date->format($format);
    }

    /**
     * Checks if date passed is valid SQL date time.
     *
     * A valid date can be in one of these formats:
     * 2014-04-24 23:20:10
     * 2014-04-24T23:20:10Z
     *
     * @param string $date - date that needs to be validated
     *
     * @return bool
    */
    public static function isValidSQLDateTimeFormat($date)
    {
        Precondition::isDate($date, 'date');

        $match = preg_match(
            '([0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])(\s|T)(0[0-9]|1[0-9]|2[0-3]):(0[0-9]|[1-5][0-9]):(0[0-9]|[1-5][0-9])(Z?))',
            $date
        );

        return empty($match) ? FALSE : TRUE;
    }

    /**
     * Validates if the date provided has the expected format
     *
     * @param string $dateTime
     * @param string $format
     *
     * @return bool
     */
    public static function doesDateMatchFormat($dateTime, $format = self::SQL_DATETIME_FORMAT)
    {
        Precondition::isDate($dateTime, 'dateTime');

        $d = \DateTime::createFromFormat($format, $dateTime);

        return $d && 0 === strcasecmp($d->format($format), $dateTime);
    }

    /**
     * Checks if the given datetime is before today.
     *
     * @param string $referenceDate - date to compare against today
     *
     * @return bool TRUE if the reference date is before today, FALSE otherwise.
     */
    public static function isDateTimeBeforeToday($date)
    {
        Precondition::isDate($date, 'date');

        $refDate = new \DateTime($date);
        $today   = new \DateTime();

        return 0 < self::getDateTimeDiffInSeconds(
            $refDate->format(self::DATE_SHORT_FORMAT),
            $today->format(self::DATE_SHORT_FORMAT)
        );
    }

    /**
     * Checks if a given datetime is older than X days ago.
     *
     * @param string $date Date to compare.
     * @param int    $days The days to compare.
     *
     * @return bool
     */
    public static function isDateOlderThanXDays($date, $days)
    {
        $daysAgoTime = time() - $days * self::DAY_IN_SECONDS;
        $dateTime    = self::getUTCTimestampFromDateTime($date);

        return $dateTime < $daysAgoTime;
    }
}
