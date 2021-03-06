<?php

namespace DAG\Domain\Schedule;

use DAG\Domain\Domain;
use DAG\Framework\Exception\Assertion;
use DAG\Orm\Schedule\AssistantCoachOrm;
use DAG\Orm\Schedule\CoachOrm;
use DAG\Orm\Schedule\FamilyOrm;
use DAG\Framework\Exception\Precondition;


/**
 * @property int    $id
 * @property Season $season
 * @property string $phone1
 * @property string $phone2
 * @property string $name
 */
class Family extends Domain
{
    /** @var FamilyOrm */
    private $familyOrm;

    /** @var Season */
    private $season;

    /** @var string */
    private $name;

    /**
     * @param FamilyOrm $familyOrm
     * @param Season    $season (defaults to null)
     */
    protected function __construct(FamilyOrm $familyOrm, $season = null)
    {
        $this->familyOrm    = $familyOrm;
        $this->season       = isset($season) ? $season : Season::lookupById($familyOrm->seasonId);
    }

    /**
     * @param Season $season
     * @param string $phone1
     * @param string $phone2
     *
     * @return Family
     */
    public static function create(
        $season,
        $phone1,
        $phone2 = '')
    {
        $familyOrm = FamilyOrm::create($season->id, $phone1, $phone2);
        return new static($familyOrm, $season);
    }

    /**
     * Create families from coaches.  Idempotent.
     *
     * @param $season
     *
     * @return Family[] $families
     */
    public static function createFromCoaches($season)
    {
        // Find coaches that are coaching multiple teams and create families
        $coachOrms = CoachOrm::loadBySeasonId($season->id);
        self::createFamilies($season, $coachOrms, $coachOrms, true);

        // Find coaches that are also an assistant coach and create families
        $assistantCoachOrms = AssistantCoachOrm::loadBySeasonId($season->id);
        self::createFamilies($season, $coachOrms, $assistantCoachOrms, false);

        // Find assistant coaches that are coaching multiple teams and create families
        self::createFamilies($season, $assistantCoachOrms, $assistantCoachOrms, true);

        return self::lookupBySeason($season);
    }

    /**
     * Create Families from coaches and players - idempotent
     *
     * @param Season                            $season
     * @param CoachOrm[] | AssistantCoachOrm[]  $list1
     * @param CoachOrm[] | AssistantCoachOrm[]  $list2
     * @param bool                              $optimize
     */
    private static function createFamilies($season, $list1, $list2, $optimize = false)
    {
        foreach ($list1 as $item1) {

            $startingPointFound = false;
            foreach ($list2 as $item2) {
                // Skip items that have already been processed if optimizing
                if ($optimize) {
                    if (!$startingPointFound) {
                        if ($item1 === $item2) {
                            $startingPointFound = true;
                        }
                        continue;
                    }
                }

                // Skip if coaching the same team
                if ($item1->teamId == $item2->teamId) {
                    continue;
                }

                // If match then create a family
                if ((!empty($item1->phone1) and !empty($item2->phone1) and $item1->phone1 == $item2->phone1)
                    or (!empty($item1->phone1) and !empty($item2->phone2) and $item1->phone1 == $item2->phone2)
                    or (!empty($item1->phone2) and !empty($item2->phone1) and $item1->phone2 == $item2->phone1)
                    or (!empty($item1->phone2) and !empty($item2->phone2) and $item1->phone2 == $item2->phone2)
                ) {

                    // Load or Create family
                    $familyOrm = FamilyOrm::loadOrCreate($season->id, $item1->phone1, $item1->phone2);

                    // Update $item1 if family not already set, or set to a different family
                    if (!isset($item1->familyId) or $item1->familyId != $familyOrm->id) {
                        $item1->familyId = $familyOrm->id;
                        $item1->save();
                    }

                    // Update $item2 if family not already set, or set to a different family
                    if (!isset($item2->familyId) or $item2->familyId != $familyOrm->id) {
                        $item2->familyId = $familyOrm->id;
                        $item2->save();
                    }
                }
            }
        }
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
     * @param string $phone
     * @param Family $family - output parameter
     *
     * @return bool - True if family found; false otherwise
     */
    public static function findByPhone($season, $phone, &$family)
    {
        if (empty($phone)) {
            return false;
        }

        $familyOrm = null;
        $result = FamilyOrm::findBySeasonIdAndPhone($season->id, $phone, $familyOrm);
        if ($result) {
            $family = new static($familyOrm, $season);
            return true;
        }

        return false;
    }

    /**
     * @param Season $season
     *
     * @return Family[]
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
            case "phone1":
            case "phone2":
                return $this->familyOrm->{$propertyName};

            case "name":
                if (!isset($this->name)) {
                    $this->setName();
                }
                return $this->name;

            case "season":
                return $this->{$propertyName};

            default:
                Precondition::isTrue(false, "Unrecognized property: $propertyName");
                return 0;
        }
    }

    /**
     * Derive the family name from the last name of the first coach or assistant coach for the family
     */
    private function setName()
    {
        Precondition::isTrue(!isset($this->name), "Family name is already set, cannot set a second time");

        $coaches = Coach::lookupByFamily($this);
        if (count($coaches) > 0) {
            $this->name = $coaches[0]->shortName;
        } else {
            $assistantCoaches = AssistantCoach::lookupByFamily($this);
            if (count($assistantCoaches) > 0) {
                $this->name = $assistantCoaches[0]->shortName;
            } else {
                $this->name = "Unknown: $this->familyOrm->phone1";
            }
        }

        Assertion::isTrue(isset($this->name), "Quick, call Dave, the family name did not get set and should have");
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