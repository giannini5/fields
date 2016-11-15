<?php

namespace DAG\Domain\Schedule;

use DAG\Domain\Domain;
use DAG\Orm\Schedule\FamilyOrm;
use DAG\Framework\Exception\Precondition;

/**
 * @property int    $id
 * @property Season $season
 * @property string $phone
 */
class Family extends Domain
{
    /** @var FamilyOrm */
    private $familyOrm;

    /** @var Season */
    private $season;

    /**
     * @param FamilyOrm $familyOrm
     * @param Season    $season (defaults to null)
     */
    protected function __construct(FamilyOrm $familyOrm, $season = null)
    {
        $this->familyOrm = $familyOrm;
        $this->season = isset($season) ? $season : Season::lookupById($familyOrm->seasonId);
    }

    /**
     * @param Season $season
     * @param string $phone
     *
     * @return Family
     */
    public static function create(
        $season,
        $phone)
    {
        $familyOrm = FamilyOrm::create($season->id, $phone);
        return new static($familyOrm, $season);
    }

    /**
     * @param int $familyId
     *
     * @return Family
     */
    public static function lookupById($familyId)
    {
        $familyOrm = FamilyOrm::loadById($familyId);
        return new static($familyOrm);
    }

    /**
     * @param Season $season
     * @param string $phone
     *
     * @return Family
     */
    public static function lookupByPhone($season, $phone)
    {
        Precondition::isNonEmpty($phone, 'phone should not be empty');
        $familyOrm = FamilyOrm::loadBySeasonIdAndPhone($season->id, $phone);
        return new static($familyOrm, $season);
    }

    /**
     * @param Season $season
     *
     * @return array Families
     */
    public static function lookupBySeason($season)
    {
        $families = [];

        $familyOrms = FamilyOrm::loadBySeasonId($season->id);
        foreach ($familyOrms as $familyOrm) {
            $families[] = new static($familyOrm, $season);
        }

        return $families;
    }

    /**
     * @param $propertyName
     * @return int|string
     */
    public function __get($propertyName)
    {
        switch ($propertyName) {
            case "id":
                return $this->familyOrm->id;
                break;

            case "phone":
                return $this->familyOrm->phone;
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
     *  Delete the family
     *  TODO: Change to cascading delete
     */
    public function delete()
    {
        $this->familyOrm->delete();
    }
}