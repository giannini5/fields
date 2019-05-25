<?php

namespace DAG\Domain\Schedule;

use DAG\Domain\Domain;
use DAG\Framework\Orm\DuplicateEntryException;
use DAG\Framework\Orm\NoResultsException;
use DAG\Orm\Schedule\FacilityOrm;
use DAG\Framework\Exception\Precondition;


/**
 * @property int    $id
 * @property Season $season
 * @property string $name
 * @property string $address1
 * @property string $address2
 * @property string $city
 * @property string $state
 * @property string $postalCode
 * @property string $country
 * @property string $contactName
 * @property string $contactEmail
 * @property string $contactPhone
 * @property string $image
 * @property int    $enabled
 */
class Facility extends Domain
{
    /** @var FacilityOrm */
    private $facilityOrm;

    /** @var Season */
    private $season;
    
    /**
     * @param FacilityOrm $facilityOrm
     * @param Season        $season (defaults to null)
     */
    protected function __construct(FacilityOrm $facilityOrm, $season = null)
    {
        $this->facilityOrm = $facilityOrm;
        $this->season = isset($season) ? $season : Season::lookupById($facilityOrm->seasonId);
    }

    /**
     * @param Season $season
     * @param string $name
     * @param string $address1
     * @param string $address2
     * @param string $city
     * @param string $state
     * @param string $postalCode
     * @param string $country
     * @param string $contactName
     * @param string $contactEmail
     * @param string $contactPhone
     * @param string $image
     * @param int    $enabled
     * @param bool   $ignoreDuplicateEntry
     *
     * @return Facility
     *
     * @throws DuplicateEntryException
     * @throws \Exception
     */
    public static function create(
        $season,
        $name,
        $address1,
        $address2,
        $city,
        $state,
        $postalCode,
        $country,
        $contactName,
        $contactEmail,
        $contactPhone,
        $image,
        $enabled,
        $ignoreDuplicateEntry = false)
    {
        try {
            $facilityOrm = FacilityOrm::create(
                $season->id,
                $name,
                $address1,
                $address2,
                $city,
                $state,
                $postalCode,
                $country,
                $contactName,
                $contactEmail,
                $contactPhone,
                $image,
                $enabled);
            return new static($facilityOrm, $season);
        } catch (DuplicateEntryException $e) {
            if ($ignoreDuplicateEntry) {
                return Facility::lookupByName($season, $name);
            } else {
                throw $e;
            }
        }
    }

    /**
     * @param int $facilityId
     *
     * @return Facility
     */
    public static function lookupById($facilityId)
    {
        $facilityOrm = FacilityOrm::loadById($facilityId);
        return new static($facilityOrm);
    }

    /**
     * @param Season $season
     * @param string $name
     *
     * @return Facility
     */
    public static function lookupByName($season, $name)
    {
        Precondition::isNonEmpty($name, 'name should not be empty');
        $facilityOrm = FacilityOrm::loadBySeasonIdAndName($season->id, $name);
        return new static($facilityOrm, $season);
    }

    /**
     * @param Season $season
     * @param string $name
     * @param Facility $facility - output parameter
     *
     * @return bool - true if facility found, false otherwise
     */
    public static function findByName($season, $name, &$facility)
    {
        Precondition::isNonEmpty($name, 'name should not be empty');

        try {
            $facilityOrm = FacilityOrm::loadBySeasonIdAndName($season->id, $name);
            $facility = new static($facilityOrm, $season);
            return true;
        } catch (NoResultsException $e) {
            return false;
        }
    }

    /**
     * @param Season $season
     *
     * @return Facility[]
     */
    public static function lookupBySeason($season)
    {
        $facilities = [];

        $facilityOrms = FacilityOrm::loadBySeasonId($season->id);
        foreach ($facilityOrms as $facilityOrm) {
            $facilities[] = new static($facilityOrm, $season);
        }
        return $facilities;
    }

    /**
     * @param $propertyName
     * @return int|string
     */
    public function __get($propertyName)
    {
        switch ($propertyName) {
            case "id":
            case "name":
            case "address1":
            case "address2":
            case "city":
            case "state":
            case "postalCode":
            case "country":
            case "contactName":
            case "contactEmail":
            case "contactPhone":
            case "image":
            case "enabled":
                return $this->facilityOrm->{$propertyName};

            case "season":
                return $this->{$propertyName};

            default:
                Precondition::isTrue(false, "Unrecognized property: $propertyName");
                return 0;
        }
    }

    /**
     * @param $propertyName
     * @param $value
     */
    public function __set($propertyName, $value)
    {
        switch ($propertyName) {
            case "name":
            case "address1":
            case "address2":
            case "city":
            case "state":
            case "postalCode":
            case "country":
            case "contactName":
            case "contactEmail":
            case "contactPhone":
            case "image":
            case "enabled":
                $this->facilityOrm->{$propertyName} = $value;
                $this->facilityOrm->save();
                break;

            default:
                Precondition::isTrue(false, "Set not allowed for property: $propertyName");
        }
    }

    /**
     *  Delete the facility
     *  TODO: Change to cascading delete
     */
    public function delete()
    {
        $this->facilityOrm->delete();
    }
}