<?php

namespace DAG\Domain\Schedule;

use DAG\Domain\Domain;
use DAG\Framework\Exception\Assertion;
use DAG\Framework\Orm\DuplicateEntryException;
use DAG\Orm\Schedule\GameTimeOrm;
use DAG\Framework\Exception\Precondition;


/**
 * @property int        $id
 * @property GameDate   $gameDate
 * @property Field      $field
 * @property string     $startTime
 * @property string     $genderPreference
 * @property Game       $game
 */
class GameTime extends Domain
{
    /** @var GameTimeOrm */
    private $gameTimeOrm;

    /** @var GameDate */
    private $gameDate;

    /** @var Field */
    private $field;

    /** @var Game */
    private $game;

    /**
     * @param GameTimeOrm   $gameTimeOrm
     * @param GameDate      $gameDate (defaults to null)
     * @param Field         $field (defaults to null)
     * @param Game          $game (defaults to null)
     */
    protected function __construct(GameTimeOrm $gameTimeOrm, $gameDate = null, $field = null, $game = null)
    {
        $this->gameTimeOrm  = $gameTimeOrm;
        $this->gameDate     = isset($gameDate) ? $gameDate : GameDate::lookupById($gameTimeOrm->gameDateId);
        $this->field        = isset($field) ? $field : Field::lookupById($gameTimeOrm->fieldId);
        if (isset($gameTimeOrm->gameId)) {
            $this->game         = isset($game) ? $game : (isset($gameTimeOrm->gameId) ? Game::lookupById($gameTimeOrm->gameId, $this) : null);
        }
        $this->game         = isset($game) ? $game : (isset($gameTimeOrm->gameId) ? Game::lookupById($gameTimeOrm->gameId, $this) : null);
    }

    /**
     * @param GameDate  $gameDate
     * @param Field     $field
     * @param string    $startTime
     * @param string    $genderPreference
     * @param Game      $game               - defaults to null
     * @param bool      $ignoreDuplicates   - defaults to false
     *
     * @return GameTime
     * @throws DuplicateEntryException
     */
    public static function create(
        $gameDate,
        $field,
        $startTime,
        $genderPreference,
        $game               = null,
        $ignoreDuplicates   = false)
    {
        try {
            $gameTimeOrm = GameTimeOrm::create($gameDate->id, $field->id, $startTime, $genderPreference, isset($game) ? $game->id : null);
            return new static($gameTimeOrm, $gameDate, $field, $game);
        } catch (DuplicateEntryException $e) {
            if ($ignoreDuplicates) {
                $gameTimeOrm = GameTimeOrm::loadByGameDateIdAndFieldIdAndStartTime($gameDate->id, $field->id, $startTime);
                return new static($gameTimeOrm, $gameDate, $field, $game);
            } else {
                throw $e;
            }
        }
    }

    /**
     * Create default games times for the specified gameDates and field based on startTime, endTime
     *
     * @param GameDate[]    $gameDates
     * @param Field         $field
     * @param string        $startTime
     * @param string        $endTime
     * @param bool          $ignoreDuplicates - defaults to false
     */
    public static function createByGameDates($gameDates, $field, $startTime, $endTime, $ignoreDuplicates = false)
    {
        $interval                   = $field->getGameDurationInMinutesInterval();
        $gameTimes                  = static::getDefaultGameTimes($startTime, $endTime, $interval);
        $startingGenderPreference   = GameTimeOrm::BOYS;
        $priorGameDate              = null;

        foreach ($gameDates as $gameDate) {
            if (isset($priorGameDate) and $priorGameDate->isSunday()) {
                $startingGenderPreference = $startingGenderPreference == GameTimeOrm::GIRLS ? GameTimeOrm::BOYS : GameTimeOrm::GIRLS;
            }
            $genderPreference = $startingGenderPreference;

            foreach ($gameTimes as $gameTime) {
                GameTime::create($gameDate, $field, $gameTime, $genderPreference, null, $ignoreDuplicates);
                $genderPreference = $genderPreference == GameTimeOrm::GIRLS ? GameTimeOrm::BOYS : GameTimeOrm::GIRLS;
            }

            $priorGameDate = $gameDate;
        }
    }

    /**
     * Get list of DateTimes by interval
     *
     * @param string        $startTime
     * @param string        $endTime
     * @param \DateInterval  $interval - time between games (30 minutes minimum)
     *
     * @return string[]
     */
    public static function getDefaultGameTimes($startTime, $endTime, $interval)
    {
        Precondition::isTrue($startTime < $endTime, "StartTime: $startTime is greater than or equal to EndTime: $endTime");
        Precondition::isTrue($interval->i >= 30, "Interval must be 30 minutes or greater");

        $gameTimes = [];
        $dateTime = \DateTime::createFromFormat('Y-m-d H:i:s', '2016-12-01 ' . $startTime);

        while ($dateTime->format('H:i:s') <= $endTime) {
            $gameTimes[] = $dateTime->format('H:i:s');
            $dateTime = $dateTime->add($interval);
        }

        return $gameTimes;
    }

    /**
     * @param int   $gameTimeId
     * @param Game  $game
     *
     * @return GameTime
     */
    public static function lookupById($gameTimeId, $game = null)
    {
        $gameTimeOrm = GameTimeOrm::loadById($gameTimeId);
        return new static($gameTimeOrm, null, null, $game);
    }

    /**
     * @param int $gameId
     *
     * @return GameTime
     */
    public static function lookupByGameId($gameId)
    {
        $gameTimeOrm = GameTimeOrm::loadByGameId($gameId);
        return new static($gameTimeOrm);
    }

    /**
     * @param GameDate      $gameDate
     *
     * @return array        $gameTimes
     */
    public static function lookupByGameDate($gameDate)
    {
        $gameTimes = [];

        $gameTimeOrms = GameTimeOrm::loadByGameDateId($gameDate->id);
        foreach ($gameTimeOrms as $gameTimeOrm) {
            $gameTimes[] = new static($gameTimeOrm, $gameDate);
        }

        return $gameTimes;
    }

    /**
     * @param GameDate      $gameDate
     * @param Field         $field
     * @param bool          $availableOnly defaults to false
     *
     * @return array        $gameTimes
     */
    public static function lookupByGameDateAndField($gameDate, $field, $availableOnly = false)
    {
        $gameTimes = [];

        $gameTimeOrms = GameTimeOrm::loadByGameDateIdAndFieldId($gameDate->id, $field->id);
        foreach ($gameTimeOrms as $gameTimeOrm) {
            $gameTime = new static($gameTimeOrm, $gameDate, $field, null);

            if ($availableOnly and isset($gameTime->game)) {
                continue;
            } else {
                $gameTimes[] = $gameTime;
            }
        }

        return $gameTimes;
    }

    /**
     * @param GameDate      $gameDate
     * @param Field         $field
     * @param string        $gender
     * @param bool          $availableOnly defaults to false
     *
     * @return array        $gameTimes
     */
    public static function lookupByGameDateAndFieldAndGender($gameDate, $field, $gender, $availableOnly = false)
    {
        $gameTimes = [];

        $gameTimeOrms = GameTimeOrm::loadByGameDateIdAndFieldIdAndGender($gameDate->id, $field->id, $gender);
        foreach ($gameTimeOrms as $gameTimeOrm) {
            $gameTime = new static($gameTimeOrm, $gameDate, $field, null);

            if ($availableOnly and isset($gameTime->game)) {
                continue;
            } else {
                $gameTimes[] = $gameTime;
            }
        }

        return $gameTimes;
    }

    /**
     * @param Field $field
     * @param bool  $availableOnly defaults to false
     *
     * @return GameTime[]
     */
    public static function lookupByField($field, $availableOnly = false)
    {
        $gameTimes = [];

        $gameTimeOrms = GameTimeOrm::loadByFieldId($field->id);
        foreach ($gameTimeOrms as $gameTimeOrm) {
            $gameTime = new static($gameTimeOrm, null, $field);

            if ($availableOnly and isset($gameTime->game)) {
                continue;
            } else {
                $gameTimes[] = $gameTime;
            }
        }

        return $gameTimes;
    }

    /**
     * @param $propertyName
     * @return int|string
     */
    public function __get($propertyName)
    {
        switch ($propertyName) {
            case "id":
            case "startTime":
            case "genderPreference":
                return $this->gameTimeOrm->{$propertyName};

            case "gameDate":
            case "field":
            case "game":
                return $this->{$propertyName};

            default:
                Precondition::isTrue(false, "Unrecognized get property: $propertyName");
        }
    }

    /**
     * @param $propertyName
     * @return bool
     */
    public function __isset($propertyName)
    {
        switch ($propertyName) {
            case "id":
            case "startTime":
                return isset($this->gameTimeOrm->{$propertyName});

            case "gameDate":
            case "field":
            case "game":
                return isset($this->{$propertyName});

            default:
                Precondition::isTrue(false, "Unrecognized get property: $propertyName");
        }
    }

    /**
     * @param $propertyName
     * @param $value
     */
    public function __set($propertyName, $value)
    {
        switch ($propertyName) {
            case "game":
                $this->gameTimeOrm->gameId  = isset($value) ? $value->id : $value;
                $this->gameTimeOrm->save();
                $this->game                 = $value;
                break;

            default:
                Precondition::isTrue(false, "Unrecognized set property: $propertyName");
        }
    }

    /**
     * @param $gameDurationMinutes
     * @return string - end time HH:MM:SS
     */
    public function getEndTime($gameDurationMinutes)
    {
        $dateTime = \DateTime::createFromFormat("H:i:s", $this->startTime);
        $interval = new \DateInterval("PT" . $gameDurationMinutes . "M");
        $dateTime->add($interval);
        return $dateTime->format("H:i:s");
    }

    /**
     *  Delete the gameTime
     */
    public function delete()
    {
        Assertion::isTrue(!isset($this->gameTimeOrm->gameId), "Cannot delete GameTime that has an assigned game.  Delete the game first");
        $this->gameTimeOrm->delete();
    }
}