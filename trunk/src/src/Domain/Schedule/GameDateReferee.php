<?php

namespace DAG\Domain\Schedule;

use DAG\Domain\Domain;
use DAG\Framework\Orm\DuplicateEntryException;
use DAG\Framework\Orm\NoResultsException;
use DAG\Orm\Schedule\GameDateRefereeOrm;
use DAG\Framework\Exception\Precondition;

/**
 * @property int        $id
 * @property GameDate   $gameDate
 * @property Referee    $referee
 */
class GameDateReferee extends Domain
{
    /** @var GameDateRefereeOrm */
    private $gameDateRefereeOrm;

    /** @var GameDate */
    private $gameDate;

    /** @var Referee */
    private $referee;

    /**
     * @param GameDateRefereeOrm    $gameDateRefereeOrm
     * @param GameDate              $gameDate (defaults to null)
     * @param Referee               $referee (defaults to null)
     */
    protected function __construct(GameDateRefereeOrm $gameDateRefereeOrm, $gameDate = null, $referee = null)
    {
        $this->gameDateRefereeOrm = $gameDateRefereeOrm;
        $this->gameDate           = isset($gameDate) ? $gameDate : GameDate::lookupById($gameDateRefereeOrm->gameDateId);
        $this->referee            = isset($referee) ? $referee : Referee::lookupById($gameDateRefereeOrm->refereeId);
    }

    /**
     * @param GameDate  $gameDate
     * @param Referee   $referee
     * @param bool      $ignoreIfAlreadyExists
     *
     * @return GameDateReferee
     * @throws DuplicateEntryException
     * @throws \Exception
     */
    public static function create(
        $gameDate,
        $referee,
        $ignoreIfAlreadyExists = false)
    {
        try {
            $gameDateRefereeOrm = GameDateRefereeOrm::create($gameDate->id, $referee->id);
            return new static($gameDateRefereeOrm, $gameDate, $referee);
        } catch (DuplicateEntryException $e) {
            if ($ignoreIfAlreadyExists) {
                $gameDateRefereeOrm = GameDateRefereeOrm::loadByGameDateIdAndReferee($gameDate->id, $referee->id);
                return new static($gameDateRefereeOrm, $gameDate, $referee);
            } else {
                throw $e;
            }
        }
    }

    /**
     * @param int $gameDateRefereeId
     *
     * @return GameDateReferee
     */
    public static function lookupById($gameDateRefereeId)
    {
        $gameDateRefereeOrm = GameDateRefereeOrm::loadById($gameDateRefereeId);
        return new static($gameDateRefereeOrm);
    }

    /**
     * @param GameDate  $gameDate
     * @param Referee   $referee
     *
     * @return GameDateReferee
     */
    public static function lookupByGameDateAndReferee($gameDate, $referee)
    {
        $gameDateRefereeOrm = GameDateRefereeOrm::loadByGameDateIdAndReferee($gameDate->id, $referee->id);
        return new static($gameDateRefereeOrm, $gameDate, $referee);
    }

    /**
     * @param GameDate          $gameDate
     * @param Referee           $referee
     * @param GameDateReferee   $gameDateReferee
     *
     * @return bool
     */
    public static function findByGameDateAndReferee($gameDate, $referee, &$gameDateReferee)
    {
        try {
            $gameDateRefereeOrm = GameDateRefereeOrm::loadByGameDateIdAndReferee($gameDate->id, $referee->id);
            $gameDateReferee    = new static($gameDateRefereeOrm, $gameDate, $referee);
            return true;
        } catch (NoResultsException $e) {
            return false;
        }
    }

    /**
     * @param GameDate $gameDate
     *
     * @return GameDateReferee[]
     */
    public static function lookupByGameDate($gameDate)
    {
        $gameDateReferees = [];

        $gameDateRefereeOrms = GameDateRefereeOrm::loadByGameDateId($gameDate->id);
        foreach ($gameDateRefereeOrms as $gameDateRefereeOrm){
            $gameDateReferees[] = new static($gameDateRefereeOrm, $gameDate);
        }

        return $gameDateReferees;
    }

    /**
     * @param Referee $referee
     *
     * @return GameDateReferee[]
     */
    public static function lookupByReferee($referee)
    {
        $gameDateReferees = [];

        $gameDateRefereeOrms = GameDateRefereeOrm::loadByRefereeId($referee->id);
        foreach ($gameDateRefereeOrms as $gameDateRefereeOrm){
            $gameDateReferees[] = new static($gameDateRefereeOrm, null, $referee);
        }

        return $gameDateReferees;
    }

    /**
     * @param $propertyName
     * @return int|string|bool
     */
    public function __get($propertyName)
    {
        switch ($propertyName) {
            case "id":
                return $this->gameDateRefereeOrm->id;

            case "gameDate":
            case "referee":
                return $this->{$propertyName};

            default:
                Precondition::isTrue(false, "Unrecognized property: $propertyName");
                return false;
        }
    }

    /**
     *  Delete the gameDateReferee
     */
    public function delete()
    {
        $this->gameDateRefereeOrm->delete();
    }
}