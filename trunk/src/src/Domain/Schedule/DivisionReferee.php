<?php

namespace DAG\Domain\Schedule;

use DAG\Domain\Domain;
use DAG\Framework\Orm\DuplicateEntryException;
use DAG\Framework\Orm\NoResultsException;
use DAG\Orm\Schedule\DivisionRefereeOrm;
use DAG\Framework\Exception\Precondition;

/**
 * @property int        $id
 * @property Division   $division
 * @property Referee    $referee
 * @property bool       $isCenter
 * @property bool       $isAssistant
 * @property bool       $isMentor
 */
class DivisionReferee extends Domain
{
    /** @var DivisionRefereeOrm */
    private $divisionRefereeOrm;

    /** @var Division */
    private $division;

    /** @var Referee */
    private $referee;

    /**
     * @param DivisionRefereeOrm    $divisionRefereeOrm
     * @param Division              $division (defaults to null)
     * @param Referee               $referee (defaults to null)
     */
    protected function __construct(DivisionRefereeOrm $divisionRefereeOrm, $division = null, $referee = null)
    {
        $this->divisionRefereeOrm = $divisionRefereeOrm;
        $this->division           = isset($division) ? $division : Division::lookupById($divisionRefereeOrm->divisionId);
        $this->referee            = isset($referee) ? $referee : Referee::lookupById($divisionRefereeOrm->refereeId);
    }

    /**
     * @param Division  $division
     * @param Referee   $referee
     * @param bool      $isCenter
     * @param bool      $isAssistant
     * @param bool      $isMentor
     * @param bool      $ignoreIfAlreadyExists
     *
     * @return DivisionReferee
     * @throws DuplicateEntryException
     * @throws \Exception
     */
    public static function create(
        $division,
        $referee,
        $isCenter,
        $isAssistant,
        $isMentor,
        $ignoreIfAlreadyExists = false)
    {
        try {
            $divisionRefereeOrm = DivisionRefereeOrm::create($division->id, $referee->id,
                $isCenter ? 1 : 0,
                $isAssistant ? 1 : 0,
                $isMentor ? 1 : 0);
            return new static($divisionRefereeOrm, $division, $referee);
        } catch (DuplicateEntryException $e) {
            if ($ignoreIfAlreadyExists) {
                $divisionRefereeOrm = DivisionRefereeOrm::loadByDivisionIdAndReferee($division->id, $referee->id);
                return new static($divisionRefereeOrm, $division, $referee);
            } else {
                throw $e;
            }
        }
    }

    /**
     * @param int $divisionRefereeId
     *
     * @return DivisionReferee
     */
    public static function lookupById($divisionRefereeId)
    {
        $divisionRefereeOrm = DivisionRefereeOrm::loadById($divisionRefereeId);
        return new static($divisionRefereeOrm);
    }

    /**
     * @param Division  $division
     * @param Referee   $referee
     *
     * @return DivisionReferee
     */
    public static function lookupByDivisionAndReferee($division, $referee)
    {
        $divisionRefereeOrm = DivisionRefereeOrm::loadByDivisionIdAndReferee($division->id, $referee->id);
        return new static($divisionRefereeOrm, $division, $referee);
    }

    /**
     * @param Division          $division
     * @param Referee           $referee
     * @param DivisionReferee   $divisionReferee
     *
     * @return bool
     */
    public static function findByDivisionAndReferee($division, $referee, &$divisionReferee)
    {
        try {
            $divisionRefereeOrm = DivisionRefereeOrm::loadByDivisionIdAndReferee($division->id, $referee->id);
            $divisionReferee = new static($divisionRefereeOrm, $division, $referee);
            return true;
        } catch (NoResultsException $e) {
            return false;
        }
    }

    /**
     * @param Division $division
     *
     * @return DivisionReferee[]
     */
    public static function lookupByDivision($division)
    {
        $divisionReferees = [];

        $divisionRefereeOrms = DivisionRefereeOrm::loadByDivisionId($division->id);
        foreach ($divisionRefereeOrms as $divisionRefereeOrm){
            $divisionReferees[] = new static($divisionRefereeOrm, $division);
        }

        return $divisionReferees;
    }

    /**
     * @param Referee $referee
     *
     * @return DivisionReferee[]
     */
    public static function lookupByReferee($referee)
    {
        $divisionReferees = [];

        $divisionRefereeOrms = DivisionRefereeOrm::loadByRefereeId($referee->id);
        foreach ($divisionRefereeOrms as $divisionRefereeOrm){
            $divisionReferees[] = new static($divisionRefereeOrm, null, $referee);
        }

        return $divisionReferees;
    }

    /**
     * @param $propertyName
     * @return int|string|bool
     */
    public function __get($propertyName)
    {
        switch ($propertyName) {
            case "id":
                return $this->divisionRefereeOrm->id;

            case "isCenter":
            case "isAssistant":
            case "isMentor":
                return $this->divisionRefereeOrm->{$propertyName} == 1 ? true : false;

            case "division":
            case "referee":
                return $this->{$propertyName};

            default:
                Precondition::isTrue(false, "Unrecognized property: $propertyName");
                return false;
        }
    }

    /**
     * @param $propertyName
     * @param $value
     */
    public function __set($propertyName, $value)
    {
        switch ($propertyName) {
            case "isCenter":
            case "isAssistant":
            case "isMentor":
                $this->divisionRefereeOrm->{$propertyName} = $value ? 1 : 0;
                $this->divisionRefereeOrm->save();
                break;

            default:
                Precondition::isTrue(false, "Unrecognized property: $propertyName");
        }
    }

    /**
     *  Delete the divisionReferee
     */
    public function delete()
    {
        $this->divisionRefereeOrm->delete();
    }
}