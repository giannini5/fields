<?php

namespace DAG\Orm\Schedule;

use DAG\Framework\Orm\FieldValidator as FV;
use DAG\Framework\Orm\PersistenceConfig as PC;
use DAG\Framework\Orm\PersistenceModel;
use DAG\Framework\Orm\DuplicateEntryException;

/**
 * @property int    $id
 * @property int    $seasonId
 * @property string $day
 */
class GameDateOrm extends PersistenceModel
{
    const FIELD_ID            = 'id';
    const FIELD_SEASON_ID     = 'seasonId';
    const FIELD_DAY           = 'day';

    protected static $fields = [
        self::FIELD_ID            => [FV::INT,    [FV::NO_CONSTRAINTS], null],
        self::FIELD_SEASON_ID     => [FV::INT,    [FV::NO_CONSTRAINTS]],
        self::FIELD_DAY           => [FV::DATE,   [FV::NO_CONSTRAINTS]],
    ];

    protected static $config = [
        PC::PERSISTENCE_DRIVER => PC::DRIVER_MYSQL,
        PC::SCHEMA             => 'schedule_rw',
        PC::TABLE              => 'gameDate',
        PC::AUTO_INC_FIELD     => self::FIELD_ID,
        PC::PRIMARY_KEYS       => [self::FIELD_SEASON_ID, self::FIELD_DAY],
    ];

    /**
     * Create a GameDateOrm
     *
     * @param int       $seasonId
     * @param string    $day
     *
     * @return GameDateOrm
     * @throws DuplicateEntryException
     */
    public static function create(
        $seasonId,
        $day)
    {
        $result = self::getPersistenceDriver()->create(
                [
                    self::FIELD_SEASON_ID       => $seasonId,
                    self::FIELD_DAY             => $day,
                ],
                function ($item) {
                    return $item !== null;
                }
        );

        return new static($result);
    }

    /**
     * Load a GameDateOrm by id
     *
     * @param int $id
     *
     * @return GameDateOrm
     */
    public static function loadById($id)
    {
        $result = self::getPersistenceDriver()->getOne([self::FIELD_ID => $id]);

        return new static($result);
    }

    /**
     * Load a GameDateOrm by seasonId, day
     *
     * @param int       $seasonId
     * @param string    $day
     *
     * @return GameDateOrm
     */
    public static function loadBySeasonIdAndDay($seasonId, $day)
    {
        $result = self::getPersistenceDriver()->getOne(
            [
                self::FIELD_SEASON_ID   => $seasonId,
                self::FIELD_DAY         => $day,
            ]);

        return new static($result);
    }

    /**
     * Load a GameDateOrms by seasonId
     *
     * @param int $seasonId
     *
     * @return GameDateOrm[]
     */
    public static function loadBySeasonId($seasonId)
    {
        $results = self::getPersistenceDriver()->getMany(
            [
                self::FIELD_SEASON_ID  => $seasonId
            ]);

        $gameDateOrms = [];
        foreach ($results as $result) {
            $gameDateOrms[] = new static($result);
        }

        return $gameDateOrms;
    }

    /**
     * Get the day of the week.
     *
     * @return int 0 - Sun, 1 - Mon, 2 - Tues, 3 - Wed, 4 - Thur, 5 - Fri, 6 - Sat
     */
    public function getDayOfWeek()
    {
        // TODO make dayOfweek an attribute of the GameDate object in database.
        $dateTime   = \DateTime::createFromFormat('Y-m-d', $this->day);
        $year       = $dateTime->format('Y');
        $month      = $dateTime->format('m');
        $day        = $dateTime->format('d');
        $gday       = gregoriantojd($month, $day, $year);
        $jday       = jddayofweek($gday);

        return $jday;
    }

    /**
     * Check to see if this game date falls on a Sunday
     *
     * @return bool
     */
    public function isSunday()
    {
        return $this->getDayOfWeek() == 0;
    }

    /**
     * Check to see if this game date falls on a Saturday
     *
     * @return bool
     */
    public function isSaturday()
    {
        return $this->getDayOfWeek() == 6;
    }
}