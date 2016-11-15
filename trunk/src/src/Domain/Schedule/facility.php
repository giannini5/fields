<?php

namespace DAG\Domain\Schedule;

use DAG\Domain\Domain;
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
     *
     * @return Facility
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
        $enabled)
    {
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
     *
     * @return array Facilities
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
                return $this->facilityOrm->id;
                break;

            case "name":
                return $this->facilityOrm->name;
                break;

            case "address1":
                return $this->facilityOrm->address1;
                break;

            case "address2":
                return $this->facilityOrm->address2;
                break;

            case "city":
                return $this->facilityOrm->city;
                break;

            case "state":
                return $this->facilityOrm->state;
                break;

            case "postalCode":
                return $this->facilityOrm->postalCode;
                break;

            case "country":
                return $this->facilityOrm->country;
                break;

            case "contactName":
                return $this->facilityOrm->contactName;
                break;

            case "contactEmail":
                return $this->facilityOrm->contactEmail;
                break;

            case "contactPhone":
                return $this->facilityOrm->contactPhone;
                break;

            case "image":
                return $this->facilityOrm->image;
                break;

            case "enabled":
                return $this->facilityOrm->enabled;
                break;

            case "season":
                return $this->season;
                break;

            default:
                Precondition::isTrue(false, "Unrecognized property: $propertyName");
                break;
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