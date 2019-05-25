<?php

namespace DAG\Domain\Schedule;

use DAG\Domain\Domain;
use DAG\Framework\Orm\DuplicateEntryException;
use DAG\Framework\Orm\NoResultsException;
use DAG\Orm\Schedule\StandbyRefereeOrm;
use DAG\Framework\Exception\Precondition;


/**
 * @property int         $id
 * @property Facility    $facility
 * @property GameDate    $gameDate
 * @property string      $divisionName
 * @property string      $startTime
 * @property Referee     $referee
 * @property string      $role
 * @property RefereeCrew $refereeCrew
 */
class StandbyReferee extends Domain
{
    /** @var StandbyRefereeOrm */
    private $standbyRefereeOrm;

    /** @var Facility */
    private $facility;

    /** @var GameDate */
    private $gameDate;

    /** @var Referee */
    private $referee;

    /** @var RefereeCrew */
    private $refereeCrew;

    /**
     * @param StandbyRefereeOrm $standbyRefereeOrm
     * @param Facility          $facility (defaults to null)
     * @param GameDate          $gameDate (defaults to null)
     * @param Referee           $referee (defaults to null)
     * @param RefereeCrew       $refereeCrew (defaults to null)
     */
    protected function __construct(
        StandbyRefereeOrm $standbyRefereeOrm,
        $facility = null,
        $gameDate = null,
        $referee = null,
        $refereeCrew = null)
    {
        $this->standbyRefereeOrm    = $standbyRefereeOrm;
        $this->facility             = isset($facility) ? $facility : Facility::lookupById($standbyRefereeOrm->facilityId);
        $this->gameDate             = isset($gameDate) ? $gameDate : GameDate::lookupById($standbyRefereeOrm->gameDateId);
        $this->referee              = isset($referee) ? $referee : Referee::lookupById($standbyRefereeOrm->refereeId);

        if (isset($refereeCrew)) {
            $this->refereeCrew = $refereeCrew;
        } else {
            $this->refereeCrew          = isset($standbyRefereeOrm->refereeCrewId) ? RefereeCrew::lookupById($standbyRefereeOrm->refereeCrewId) : null;
        }
    }

    /**
     * @param Facility      $facility
     * @param GameDate      $gameDate
     * @param string        $divisionName
     * @param string        $startTime
     * @param Referee       $referee
     * @param string        $role - See StandbyRefereeORM for roles
     * @param RefereeCrew   $refereeCrew - defaults to null
     * @param bool          $ignoreIfAlreadyExists - defaults to false
     *
     * @return StandbyReferee
     * @throws DuplicateEntryException
     * @throws \Exception
     */
    public static function create(
        $facility,
        $gameDate,
        $divisionName,
        $startTime,
        $referee,
        $role,
        $refereeCrew = null,
        $ignoreIfAlreadyExists = false)
    {
        try {
            $standbyRefereeOrm = StandbyRefereeOrm::create(
                $facility->id,
                $gameDate->id,
                $divisionName,
                $startTime,
                $referee->id,
                $role,
                isset($refereeCrew) ? $refereeCrew->id : null);
            return new static($standbyRefereeOrm, $facility, $gameDate, $referee, $refereeCrew);
        } catch (DuplicateEntryException $e) {
            if ($ignoreIfAlreadyExists) {
                $standbyRefereeOrm = StandbyRefereeOrm::loadByFacilityIdGameDateIdDivisionNameStartTimeRefereeId(
                    $facility->id,
                    $gameDate->id,
                    $divisionName,
                    $startTime,
                    $referee->id);
                return new static($standbyRefereeOrm, $facility, $gameDate, $referee, $refereeCrew);
            } else {
                throw $e;
            }
        }
    }

    /**
     * @param int $standbyRefereeId
     *
     * @return StandbyReferee
     */
    public static function lookupById($standbyRefereeId)
    {
        $standbyRefereeOrm = StandbyRefereeOrm::loadById($standbyRefereeId);
        return new static($standbyRefereeOrm);
    }

    /**
     * @param Facility      $facility
     * @param GameDate      $gameDate
     * @param string        $divisionName
     * @param string        $startTime
     *
     * @return StandbyReferee[]
     */
    public static function lookupByStartTime($facility, $gameDate, $divisionName, $startTime)
    {
        $standbyReferee = [];

        $standbyRefereeOrms = StandbyRefereeOrm::loadByFacilityIdGameDateIdDivisionNameStartTime(
            $facility->id,
            $gameDate->id,
            $divisionName,
            $startTime);

        foreach ($standbyRefereeOrms as $standbyRefereeOrm) {
            $standbyReferee[] = new static($standbyRefereeOrm, $facility, $gameDate);
        }

        return $standbyReferee;
    }

    /**
     * @param Facility          $facility
     * @param GameDate          $gameDate
     * @param string            $divisionName
     * @param string            $startTime
     * @param Referee           $referee
     * @param StandbyReferee    &$standbyReferee
     *
     * @return bool
     */
    public static function findByStartTimeReferee($facility, $gameDate, $divisionName, $startTime, $referee, &$standbyReferee)
    {
        try {
            $standbyRefereeOrm = StandbyRefereeOrm::loadByFacilityIdGameDateIdDivisionNameStartTimeRefereeId(
                $facility->id,
                $gameDate->id,
                $divisionName,
                $startTime,
                $referee->id);
            $standbyReferee = new static($standbyRefereeOrm, $facility, $gameDate, $referee);
            return true;
        } catch (NoResultsException $e) {
            return false;
        }
    }

    /**
     * @param Facility          $facility
     * @param GameDate          $gameDate
     * @param Referee           $referee
     *
     * @return StandbyReferee[]
     */
    public static function lookupByReferee($facility, $gameDate, $referee)
    {
        $standbyReferees = [];

        $standbyRefereeOrms = StandbyRefereeOrm::loadByGameDateIdRefereeId($facility->id, $gameDate->id, $referee->id);
        foreach ($standbyRefereeOrms as $standbyRefereeOrm){
            $standbyReferees[] = new static($standbyRefereeOrm, $facility, $gameDate, $referee);
        }

        return $standbyReferees;
    }

    /**
     * @param $propertyName
     * @return int|string
     */
    public function __get($propertyName)
    {
        switch ($propertyName) {
            case "id":
            case "divisionName":
            case "startTime":
            case "role":
                return $this->standbyRefereeOrm->{$propertyName};

            case "facility":
            case "gameDate":
            case "referee":
            case "refereeCrew":
                return $this->{$propertyName};

            default:
                Precondition::isTrue(false, "Unrecognized property: $propertyName");
                return false;
        }
    }

    /**
     * @param string    $propertyName
     * @param mixed     $value
     */
    public function __set($propertyName, $value)
    {
        switch ($propertyName) {
            case "role":
                $this->standbyRefereeOrm->{$propertyName} = $value;
                $this->standbyRefereeOrm->save();
                break;

            case "refereeCrew":
                $this->standbyRefereeOrm->refereeCrewId = isset($value) ? $value->id : null;
                $this->standbyRefereeOrm->save();
                $this->refereeCrew = $value;
                break;

            default:
                Precondition::isTrue(false, "Unrecognized property: $propertyName");
        }
    }

    /**
     *  Delete the standbyReferee
     */
    public function delete()
    {
        $this->standbyRefereeOrm->delete();
    }
}