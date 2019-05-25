<?php

namespace DAG\Domain\Schedule;

use DAG\Domain\Domain;
use DAG\Framework\Orm\DuplicateEntryException;
use DAG\Framework\Orm\NoResultsException;
use DAG\Orm\Schedule\RefereeOrm;
use DAG\Framework\Exception\Precondition;

/**
 * @property int    $id
 * @property Season $season
 * @property Family $family
 * @property string $name
 * @property string $shortName
 * @property string $email
 * @property string $phone
 * @property string $badgeId
 * @property string $badge
 * @property int    $maxGamesPerDay
 * @property string $specialInstructions
 */
class Referee extends Domain
{
    /** @var RefereeOrm */
    private $refereeOrm;

    /** @var Season */
    private $season;

    /** @var Family */
    private $family;

    // Referee Types
    const ALL_REFEREES      = 'allReferees';
    const TEAM_REFEREE      = 'teamReferee';
    const NON_TEAM_REFEREE  = 'floatingReferee';

    static $refereeTypes = [
        self::ALL_REFEREES,
        self::TEAM_REFEREE,
        self::NON_TEAM_REFEREE,
    ];

    /**
     * @param RefereeOrm $refereeOrm
     * @param Season     $season (defaults to null)
     * @param Family     $family (defaults to null)
     */
    protected function __construct(RefereeOrm $refereeOrm, $season = null, $family = null)
    {
        $this->refereeOrm = $refereeOrm;
        $this->season     = isset($season) ? $season : Season::lookupById($refereeOrm->seasonId);
        $this->family     = (!isset($family) and isset($refereeOrm->familyId)) ? Family::lookupById($refereeOrm->familyId) : $family;
    }

    /**
     * @param Season $season
     * @param Family $family
     * @param string $name
     * @param string $email
     * @param string $phone
     * @param bool   $ignoreDuplicateEntry
     * @param string $badgeId - See RefereeOrm for accepted badge identifiers
     * @param int    $maxGamesPerDay
     * @param string $specialInstrutions
     *
     * @return Referee
     *
     * @throws DuplicateEntryException
     * @throws \Exception
     */
    public static function create(
        $season,
        $family,
        $name,
        $email,
        $phone,
        $badgeId,
        $maxGamesPerDay,
        $specialInstructions,
        $ignoreDuplicateEntry = false)
    {
        try {
            $familyId = isset($family) ? $family->id : null;
            $refereeOrm = RefereeOrm::create(
                $season->id,
                $familyId,
                $name,
                $email,
                $phone,
                $badgeId,
                $maxGamesPerDay,
                $specialInstructions);
            return new static($refereeOrm, $season, $family);
        } catch (DuplicateEntryException $e) {
            if ($ignoreDuplicateEntry) {
                return Referee::lookupByEmailAndName($season, $email, $name);
            } else {
                throw $e;
            }
        }
    }

    /**
     * @param int $refereeId
     *
     * @return Referee
     */
    public static function lookupById($refereeId)
    {
        $refereeOrm = RefereeOrm::loadById($refereeId);
        return new static($refereeOrm);
    }

    /**
     * @param Season $season
     * @param string $email
     * @param string $name
     *
     * @return Referee
     */
    public static function lookupByEmailAndName($season, $email, $name)
    {
        Precondition::isNonEmpty($email, 'email should not be empty');
        Precondition::isNonEmpty($name, 'name should not be empty');
        $refereeOrm = RefereeOrm::loadBySeasonIdEmailAndName($season->id, $email, $name);
        return new static($refereeOrm, $season);
    }

    /**
     * @param Season $season
     * @param string $email
     *
     * @return Referee[]
     */
    public static function lookupByEmail($season, $email)
    {
        Precondition::isNonEmpty($email, 'email should not be empty');
        $refereeOrms = RefereeOrm::loadBySeasonIdAndEmail($season->id, $email);

        $referees = [];
        foreach ($refereeOrms as $refereeOrm) {
            $referees[] = new static($refereeOrm, $season);
        }
        return $referees;
    }

    /**
     * @param Season $season
     * @param string $name
     *
     * @return Referee[]
     */
    public static function lookupByName($season, $name)
    {
        Precondition::isNonEmpty($name, 'name should not be empty');
        $refereeOrms = RefereeOrm::loadBySeasonIdAndName($season->id, $name);

        $referees = [];
        foreach ($refereeOrms as $refereeOrm) {
            $referees[] = new static($refereeOrm, $season);
        }
        return $referees;
    }

    /**
     * @param Season $season
     * @param string $email
     * @param string $name
     * @param Referee $referee - output parameter
     *
     * @return bool - true if referee found, false otherwise
     */
    public static function findByEmailAndName($season, $email, $name, &$referee)
    {
        Precondition::isNonEmpty($email, 'email should not be empty');
        Precondition::isNonEmpty($name, 'name should not be empty');

        try {
            $refereeOrm = RefereeOrm::loadBySeasonIdEmailAndName($season->id, $email, $name);
            $referee = new static($refereeOrm, $season);
            return true;
        } catch (NoResultsException $e) {
            return false;
        }
    }

    /**
     * @param Season $season
     *
     * @return Referee[]
     */
    public static function lookupBySeason($season)
    {
        $referees = [];

        $refereeOrms = RefereeOrm::loadBySeasonId($season->id);
        foreach ($refereeOrms as $refereeOrm) {
            $referees[] = new static($refereeOrm, $season);
        }
        return $referees;
    }

    /**
     * @param Family $family
     *
     * @return Referee[]
     */
    public static function lookupByFamily($family)
    {
        $referees = [];

        $refereeOrms = RefereeOrm::loadByFamilyId($family->id);
        foreach ($refereeOrms as $refereeOrm) {
            $referees[] = new static($refereeOrm, null, $family);
        }

        return $referees;
    }

    /**
     * @param Division  $division
     * @param string    $refereeType
     *
     * @return Referee[]
     */
    public static function lookupByDivisionAndType($division, $refereeType)
    {
        Precondition::arrayValueExists(self::$refereeTypes, $refereeType);

        $referees           = [];
        $divisionReferees   = DivisionReferee::lookupByDivision($division);

        foreach ($divisionReferees as $divisionReferee) {
            switch ($refereeType) {
                case self::ALL_REFEREES:
                    $referees[] = $divisionReferee->referee;
                    break;

                case self::TEAM_REFEREE:
                    $teamReferees = TeamReferee::lookupByReferee($divisionReferee->referee);
                    if (count($teamReferees) > 0) {
                        $referees[] = $divisionReferee->referee;
                    }
                    break;

                case self::NON_TEAM_REFEREE:
                    $teamReferees = TeamReferee::lookupByReferee($divisionReferee->referee);
                    if (count($teamReferees) == 0) {
                        $referees[] = $divisionReferee->referee;
                    }
                    break;

                default:
                    Precondition::isTrue(false, "Unrecognized refereeType: $refereeType");
            }
        }

        return $referees;
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
            case "email":
            case "phone":
            case "badgeId":
            case "maxGamesPerDay":
            case "specialInstructions":
                return $this->refereeOrm->{$propertyName};

            case "badge":
                return $this->refereeOrm->getBadge();

            case "season":
            case "family":
                return $this->{$propertyName};

            case "shortName":
                $nameParts = explode(" ", $this->refereeOrm->name);
                switch (count($nameParts)) {
                    case 0:
                    case 1:
                        return $this->refereeOrm->name;
                    default:
                        $firstName  = array_shift($nameParts);
                        $lastName   = implode(" ", $nameParts);
                        $lastName   .= ", " . $firstName[0];
                        return $lastName;
                }
                break;

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
            case "name":
            case "email":
            case "phone":
            case "badgeId":
            case "maxGamesPerDay":
            case "specialInstructions":
                $this->refereeOrm->{$propertyName} = $value;
                $this->refereeOrm->save();
                break;

            case "family":
                $this->refereeOrm->familyId = isset($value) ? $value->id : null;
                $this->refereeOrm->save();
                $this->family = $value;
                break;

            default:
                Precondition::isTrue(false, "Set not allowed for property: $propertyName");
        }
    }

    /**
     * @param $propertyName
     * @return int|string
     */
    public function __isset($propertyName)
    {
        switch ($propertyName) {
            case "season":
            case "family":
                return isset($this->{$propertyName});

            case "name":
            case "email":
            case "phone":
            case "badgeId":
            case "maxGamesPerDay":
            case "specialInstructions":
                return isset($this->refereeOrm->{$propertyName});

            default:
                Precondition::isTrue(false, "Unrecognized property: $propertyName");
                return false;
        }
    }

    /**
     * @return string[] - List of division names (nameWithGender) map with value 1
     */
    public function getDivisionsChecked()
    {
        $divisionsChecked = [];

        $divisionReferees = DivisionReferee::lookupByReferee($this);
        foreach ($divisionReferees as $divisionReferee) {
            $divisionsChecked[$divisionReferee->division->id][\View_Base::CENTER]    = $divisionReferee->isCenter;
            $divisionsChecked[$divisionReferee->division->id][\View_Base::ASSISTANT] = $divisionReferee->isAssistant;
            $divisionsChecked[$divisionReferee->division->id][\View_Base::MENTOR]    = $divisionReferee->isMentor;
        }

        return $divisionsChecked;
    }

    /**
     * @return string[] - List of game days map with value 1
     */
    public function getGameDatesChecked()
    {
        $gameDaysChecked = [];

        $gameDateReferees = GameDateReferee::lookupByReferee($this);
        foreach ($gameDateReferees as $gameDateReferee) {
            $gameDaysChecked[$gameDateReferee->gameDate->id] = 1;
        }

        return $gameDaysChecked;
    }

    /**
     * Set maxGamesPerDay to 2 if not already set to a number greater than 0
     * Set gameDateReferee to all game dates
     * Set divisionReferee based on badgeId
     */
    public function setDefaultPreferences()
    {
        // Set max games per day if not already set
        if ($this->maxGamesPerDay == 0) {
            $this->maxGamesPerDay = 2;
        }

        // Set gameDateReferee if not already set
        $gameDateReferees = GameDateReferee::lookupByReferee($this);
        if (count($gameDateReferees) == 0) {
            $gameDates = GameDate::lookupBySeason($this->season);
            foreach ($gameDates as $gameDate) {
                GameDateReferee::create($gameDate, $this, true);
            }
        }

        // Set divisions for center and AR based on badge level
        $divisions = Division::lookupBySeason($this->season);
        foreach ($divisions as $division) {
            $canCenter = $division->canCenter($this->badgeId);
            $canAR = $division->canAR($this->badgeId);
            if ($canCenter or $canAR) {
                DivisionReferee::create($division, $this, $canCenter, $canAR, false, true);
            }
        }
    }

    /**
     *  Delete the referee
     *  TODO: Change to cascading delete
     */
    public function delete()
    {
        $this->refereeOrm->delete();
    }
}