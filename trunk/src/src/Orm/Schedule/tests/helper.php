<?php

namespace DAG\Orm\Schedule;

use DAG\Framework\Orm\NoResultsException;

require_once dirname(dirname(dirname(dirname(__DIR__)))) . '/lib/autoload.php';


abstract class ORM_TestHelper extends \PHPUnit_Framework_TestCase {
    const DEFAULT_LEAGUE_NAME = 'Default League';

    // Database column names:
    const NAME              = 'name';
    const PASSWORD          = 'password';
    const START_DATE        = 'startDate';
    const END_DATE          = 'endDate';
    const START_TIME        = 'startTime';
    const END_TIME          = 'endTime';
    const DAYS_OF_WEEK      = 'daysOfWeek';
    const ENABLED           = 'enabled';
    const ADDRESS1          = 'address1';
    const ADDRESS2          = 'address2';
    const CITY              = 'city';
    const STATE             = 'state';
    const POSTAL_CODE       = 'postalCode';
    const COUNTRY           = 'country';
    const CONTACT_NAME      = 'contactName';
    const CONTACT_EMAIL     = 'contactEmail';
    const CONTACT_PHONE     = 'contactPhone';
    const IMAGE             = 'image';
    const DAY               = 'day';
    const PHONE             = 'phone';
    const EMAIL             = 'email';
    const PHONE1            = 'phone1';
    const PHONE2            = 'phone2';

    protected static $defaultSeasonOrmAttributes =
        [
            self::NAME          => 'Default Season',
            self::START_DATE    => '2015-09-01',
            self::END_DATE      => '2015-11-20',
            self::START_TIME    => '07:00:00',
            self::END_TIME      => '20:00:00',
            self::DAYS_OF_WEEK  => '0000011',
            self::ENABLED       =>  1,
        ];

    protected static $defaultScheduleCoordinatorOrmAttributes =
        [
            self::EMAIL         => 'Default Email',
            self::NAME          => 'Default Coord',
            self::PASSWORD      => 'pwd',
        ];

    protected static $defaultFacilityOrmAttributes =
        [
            self::NAME          => 'Default Facility',
            self::ADDRESS1      => 'default addr1',
            self::ADDRESS2      => 'default addr2',
            self::CITY          => 'default city',
            self::STATE         => 'default state',
            self::POSTAL_CODE   => 'default postal',
            self::COUNTRY       => 'default country',
            self::CONTACT_NAME  => 'default contact name',
            self::CONTACT_EMAIL => 'default contact email',
            self::CONTACT_PHONE => 'default contact phone',
            self::IMAGE         => 'default image',
            self::ENABLED       => 1,
        ];

    protected static $defaultFieldOrmAttributes =
        [
            self::NAME          => 'Default Field',
            self::ENABLED       => 1,
        ];

    protected static $defaultGameDateOrmAttributes =
        [
            self::DAY   => '2016-10-22',
        ];

    protected static $defaultFamilyOrmAttributes =
        [
            self::PHONE   => '18052523944',
        ];

    protected static $defaultDivisionOrmAttributes =
        [
            self::NAME   => 'UTestG',
        ];

    protected static $defaultPoolOrmAttributes =
        [
            self::NAME   => 'Test Pool A',
        ];

    protected static $defaultScheduleOrmAttributes =
        [
            self::NAME   => 'Test Default Schedule',
        ];

    protected static $defaultTeamOrmAttributes =
        [
            self::NAME   => 'Test Default Team 1',
        ];

    protected static $defaultVisitingTeamOrmAttributes =
        [
            self::NAME   => 'Test Default Team 2',
        ];

    protected static $defaultCoachOrmAttributes =
        [
            self::NAME      => 'default name',
            self::EMAIL     => 'default email',
            self::PHONE1    => 'default phone1',
            self::PHONE2    => 'default phone2',
        ];

    protected static $defaultAssistantCoachOrmAttributes =
        [
            self::NAME      => 'default asst name',
            self::EMAIL     => 'default asst email',
            self::PHONE1    => 'default asst phone1',
            self::PHONE2    => 'default asst phone2',
        ];

    protected static $defaultPlayerOrmAttributes =
        [
            self::NAME      => 'default player name',
            self::EMAIL     => 'default player email',
            self::PHONE     => 'default player phone1',
        ];

    protected static $defaultGameTimeOrmAttributes =
        [
            self::START_TIME    => '16:30:00',
        ];

    public $defaultLeagueOrm;
    public $defaultSeasonOrm;
    public $defaultFacilityOrm;
    public $defaultFieldOrm;
    public $defaultGameDateOrm;
    public $defaultFamilyOrm;
    public $defaultDivisionOrm;
    public $defaultPoolOrm;
    public $defaultScheduleOrm;
    public $defaultTeamOrm;
    public $defaultVisitingTeamOrm;
    public $defaultCoachOrm;
    public $defaultAssistantCoachOrm;
    public $defaultPlayerOrm;
    public $defaultGameTimeOrm;
    public $defaultGameOrm;
    public $defaultScheduleCoordinatorOrm;

    /**
     * Prime the database for unit testing
     */
    protected function primeDatabase()
    {
        $this->clearDatabase();

        $this->defaultLeagueOrm = LeagueOrm::create(self::DEFAULT_LEAGUE_NAME);

        $this->defaultSeasonOrm = SeasonOrm::create(
            $this->defaultLeagueOrm->id,
            self::$defaultSeasonOrmAttributes[self::NAME],
            self::$defaultSeasonOrmAttributes[self::START_DATE],
            self::$defaultSeasonOrmAttributes[self::END_DATE],
            self::$defaultSeasonOrmAttributes[self::START_TIME],
            self::$defaultSeasonOrmAttributes[self::END_TIME],
            self::$defaultSeasonOrmAttributes[self::DAYS_OF_WEEK],
            self::$defaultSeasonOrmAttributes[self::ENABLED]);

        $this->defaultScheduleCoordinatorOrm = ScheduleCoordinatorOrm::create(
            $this->defaultLeagueOrm->id,
            self::$defaultScheduleCoordinatorOrmAttributes[self::EMAIL],
            self::$defaultScheduleCoordinatorOrmAttributes[self::NAME],
            self::$defaultScheduleCoordinatorOrmAttributes[self::PASSWORD]);

        $this->defaultFacilityOrm = FacilityOrm::create(
            $this->defaultSeasonOrm->id,
            self::$defaultFacilityOrmAttributes[self::NAME],
            self::$defaultFacilityOrmAttributes[self::ADDRESS1],
            self::$defaultFacilityOrmAttributes[self::ADDRESS2],
            self::$defaultFacilityOrmAttributes[self::CITY],
            self::$defaultFacilityOrmAttributes[self::STATE],
            self::$defaultFacilityOrmAttributes[self::POSTAL_CODE],
            self::$defaultFacilityOrmAttributes[self::COUNTRY],
            self::$defaultFacilityOrmAttributes[self::CONTACT_NAME],
            self::$defaultFacilityOrmAttributes[self::CONTACT_EMAIL],
            self::$defaultFacilityOrmAttributes[self::CONTACT_PHONE],
            self::$defaultFacilityOrmAttributes[self::IMAGE],
            self::$defaultFacilityOrmAttributes[self::ENABLED]);

        $this->defaultFieldOrm = FieldOrm::create(
            $this->defaultFacilityOrm->id,
            self::$defaultFieldOrmAttributes[self::NAME],
            self::$defaultFieldOrmAttributes[self::ENABLED]);

        $this->defaultGameDateOrm = GameDateOrm::create(
            $this->defaultSeasonOrm->id,
            self::$defaultGameDateOrmAttributes[self::DAY]);

        $this->defaultFamilyOrm = FamilyOrm::create(
            $this->defaultSeasonOrm->id,
            self::$defaultFamilyOrmAttributes[self::PHONE]);

        $this->defaultDivisionOrm = DivisionOrm::create(
            $this->defaultSeasonOrm->id,
            self::$defaultDivisionOrmAttributes[self::NAME]);

        $this->defaultPoolOrm = PoolOrm::create(
            $this->defaultDivisionOrm->id,
            self::$defaultPoolOrmAttributes[self::NAME]);

        $this->defaultScheduleOrm = ScheduleOrm::create(
            $this->defaultPoolOrm->id,
            self::$defaultScheduleOrmAttributes[self::NAME]);

        $this->defaultTeamOrm = TeamOrm::create(
            $this->defaultDivisionOrm->id,
            $this->defaultPoolOrm->id,
            self::$defaultTeamOrmAttributes[self::NAME]);

        $this->defaultVisitingTeamOrm = TeamOrm::create(
            $this->defaultDivisionOrm->id,
            $this->defaultPoolOrm->id,
            self::$defaultVisitingTeamOrmAttributes[self::NAME]);

        $this->defaultCoachOrm = CoachOrm::create(
            $this->defaultTeamOrm->id,
            $this->defaultFamilyOrm->id,
            self::$defaultCoachOrmAttributes[self::NAME],
            self::$defaultCoachOrmAttributes[self::EMAIL],
            self::$defaultCoachOrmAttributes[self::PHONE1],
            self::$defaultCoachOrmAttributes[self::PHONE2]);

        $this->defaultAssistantCoachOrm = AssistantCoachOrm::create(
            $this->defaultTeamOrm->id,
            $this->defaultFamilyOrm->id,
            self::$defaultAssistantCoachOrmAttributes[self::NAME],
            self::$defaultAssistantCoachOrmAttributes[self::EMAIL],
            self::$defaultAssistantCoachOrmAttributes[self::PHONE1],
            self::$defaultAssistantCoachOrmAttributes[self::PHONE2]);

        $this->defaultPlayerOrm = PlayerOrm::create(
            $this->defaultTeamOrm->id,
            $this->defaultFamilyOrm->id,
            self::$defaultPlayerOrmAttributes[self::NAME],
            self::$defaultPlayerOrmAttributes[self::EMAIL],
            self::$defaultPlayerOrmAttributes[self::PHONE]);

        $this->defaultGameTimeOrm = GameTimeOrm::create(
            $this->defaultGameDateOrm->id,
            $this->defaultDivisionOrm->id,
            $this->defaultFieldOrm->id,
            self::$defaultGameTimeOrmAttributes[self::START_TIME]);

        $this->defaultGameOrm = GameOrm::create(
            $this->defaultScheduleOrm->id,
            $this->defaultGameTimeOrm->id,
            $this->defaultTeamOrm->id,
            $this->defaultVisitingTeamOrm->id);
    }

    /**
     * Clear out all entities for the test league
     */
    protected function clearDatabase()
    {
        $this->clearLeague();
    }

    /**
     * Clear the database.  Cascading delete starting with default league
     */
    protected function clearLeague()
    {
        if (!isset($this->defaultLeagueOrm)) {
            try {
                $this->defaultLeagueOrm = LeagueOrm::loadByName(self::DEFAULT_LEAGUE_NAME);
            } catch (NoResultsException $e) {
                // no op
            }

        }

        if (isset($this->defaultLeagueOrm)) {
            $seasonOrms = SeasonOrm::loadByLeagueId($this->defaultLeagueOrm->id);
            foreach ($seasonOrms as $seasonOrm) {
                $this->clearSeason($seasonOrm);
            }

            $scheduleCoordinatorOrms = ScheduleCoordinatorOrm::loadByLeagueId($this->defaultLeagueOrm->id);
            foreach ($scheduleCoordinatorOrms as $scheduleCoordinatorOrm) {
                $scheduleCoordinatorOrm->delete();
            }

            $this->defaultLeagueOrm->delete();
            $this->defaultLeagueOrm = null;
        }
    }

    /**
     * Cascading delete of everything below seasonOrm and then seasonOrm
     *
     * @param SeasonOrm $seasonOrm
     */
    protected function clearSeason($seasonOrm)
    {
        $facilityOrms = FacilityOrm::loadBySeasonId($seasonOrm->id);
        foreach ($facilityOrms as $facilityOrm) {
            $this->clearFacility($facilityOrm);
        }

        $gameDateOrms = GameDateOrm::loadBySeasonId($seasonOrm->id);
        foreach ($gameDateOrms as $gameDateOrm) {
            $this->clearGameDate($gameDateOrm);
        }

        $familyOrms = FamilyOrm::loadBySeasonId($seasonOrm->id);
        foreach ($familyOrms as $familyOrm) {
            $this->clearFamily($familyOrm);
        }

        $divisionOrms = DivisionOrm::loadBySeasonId($seasonOrm->id);
        foreach ($divisionOrms as $divisionOrm) {
            $this->clearDivision($divisionOrm);
        }

        $seasonOrm->delete();
    }

    /**
     * Cascading delete of everything below facilityOrm and then facilityOrm
     *
     * @param FacilityOrm $facilityOrm
     */
    protected function clearFacility($facilityOrm)
    {
        $fieldOrms = FieldOrm::loadByFacilityId($facilityOrm->id);
        foreach ($fieldOrms as $fieldOrm) {
            $this->clearField($fieldOrm);
        }

        $facilityOrm->delete();
    }

    /**
     * Cascading delete of everything below fieldOrm and then fieldOrm
     *
     * @param FieldOrm $fieldOrm
     */
    protected function clearField($fieldOrm)
    {
        $fieldOrm->delete();
    }

    /**
     * Cascading delete of everything below gameDateOrm and then gameDateOrm
     *
     * @param GameDateOrm $gameDateOrm
     */
    protected function clearGameDate($gameDateOrm)
    {
        $gameTimeOrms = GameTimeOrm::loadByGameDateId($gameDateOrm->id);
        foreach ($gameTimeOrms as $gameTimeOrm) {
            $this->clearGameTime($gameTimeOrm);
        }

        $gameDateOrm->delete();
    }

    /**
     * Cascading delete of everything below familyOrm and then familyOrm
     *
     * @param FamilyOrm $familyOrm
     */
    protected function clearFamily($familyOrm)
    {
        $familyOrm->delete();
    }

    /**
     * Cascading delete of everything below divisionOrm and then divisionOrm
     *
     * @param DivisionOrm $divisionOrm
     */
    protected function clearDivision($divisionOrm)
    {
        $poolOrms = PoolOrm::loadByDivisionId($divisionOrm->id);
        foreach ($poolOrms as $poolOrm) {
            $this->clearPool($poolOrm);
        }

        $teamOrms = TeamOrm::loadByDivisionId($divisionOrm->id);
        foreach ($teamOrms as $teamOrm) {
            $this->clearTeam($teamOrm);
        }

        $divisionOrm->delete();
    }

    /**
     * Cascading delete of everything below poolOrm and then poolOrm
     *
     * @param PoolOrm $poolOrm
     */
    protected function clearPool($poolOrm)
    {
        $scheduleOrms = ScheduleOrm::loadByPoolId($poolOrm->id);
        foreach ($scheduleOrms as $scheduleOrm) {
            $this->clearSchedule($scheduleOrm);
        }

        $poolOrm->delete();
    }

    /**
     * Cascading delete of everything below scheduleOrm and then scheduleOrm
     *
     * @param ScheduleOrm $scheduleOrm
     */
    protected function clearSchedule($scheduleOrm)
    {
        $gameOrms = GameOrm::loadByScheduleId($scheduleOrm->id);
        foreach ($gameOrms as $gameOrm) {
            $this->clearGame($gameOrm);
        }

        $scheduleOrm->delete();
    }

    /**
     * Cascading delete of everything below teamOrm and then teamOrm
     *
     * @param TeamOrm $teamOrm
     */
    protected function clearTeam($teamOrm)
    {
        try {
            $coachOrm = CoachOrm::loadByTeamId($teamOrm->id);
            $this->clearCoach($coachOrm);
        } catch (NoResultsException $e) {
            // No coaches to cleanup
        }

        $assistantCoachOrms = AssistantCoachOrm::loadByTeamId($teamOrm->id);
        foreach ($assistantCoachOrms as $assistantCoachOrm) {
            $this->clearAssistantCoach($assistantCoachOrm);
        }

        $playerOrms = PlayerOrm::loadByTeamId($teamOrm->id);
        foreach ($playerOrms as $playerOrms) {
            $this->clearPlayer($playerOrms);
        }

        $teamOrm->delete();
    }

    /**
     * Cascading delete of everything below coachOrm and then coachOrm
     *
     * @param CoachOrm $coachOrm
     */
    protected function clearCoach($coachOrm)
    {
        $coachOrm->delete();
    }

    /**
     * Cascading delete of everything below assistantCoachOrm and then assistantCoachOrm
     *
     * @param AssistantCoachOrm $assistantCoachOrm
     */
    protected function clearAssistantCoach($assistantCoachOrm)
    {
        $assistantCoachOrm->delete();
    }

    /**
     * Cascading delete of everything below playerOrm and then playerOrm
     *
     * @param PlayerOrm $playerOrm
     */
    protected function clearPlayer($playerOrm)
    {
        $playerOrm->delete();
    }

    /**
     * Cascading delete of everything below gameTimeOrm and then gameTimeOrm
     *
     * @param GameTimeOrm $gameTimeOrm
     */
    protected function clearGameTime($gameTimeOrm)
    {
        $gameTimeOrm->delete();
    }

    /**
     * Cascading delete of everything below gameOrm and then gameOrm
     *
     * @param GameOrm $gameOrm
     */
    protected function clearGame($gameOrm)
    {
        $gameOrm->delete();
    }
}