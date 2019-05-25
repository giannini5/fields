<?php

namespace DAG\Orm\Schedule;

use DAG\Framework\Orm\NoResultsException;

require_once dirname(dirname(dirname(dirname(__DIR__)))) . '/lib/autoload.php';


abstract class ORM_TestHelper extends \PHPUnit_Framework_TestCase {
    const DEFAULT_LEAGUE_NAME = 'Default League';

    // Database column names:
    const NAME                        = 'name';
    const NAME_ID                     = 'nameId';
    const COLOR                       = 'color';
    const REGION                      = 'region';
    const PASSWORD                    = 'password';
    const START_DATE                  = 'startDate';
    const END_DATE                    = 'endDate';
    const START_TIME                  = 'startTime';
    const ACTUAL_START_TIME           = 'actualStartTime';
    const END_TIME                    = 'endTime';
    const DAYS_OF_WEEK                = 'daysOfWeek';
    const DISPLAY_NOTES               = 'displayNotes';
    const PUBLISHED                   = 'published';
    const ENABLED                     = 'enabled';
    const ADDRESS1                    = 'address1';
    const ADDRESS2                    = 'address2';
    const CITY                        = 'city';
    const STATE                       = 'state';
    const POSTAL_CODE                 = 'postalCode';
    const COUNTRY                     = 'country';
    const CONTACT_NAME                = 'contactName';
    const CONTACT_EMAIL               = 'contactEmail';
    const CONTACT_PHONE               = 'contactPhone';
    const IMAGE                       = 'image';
    const DAY                         = 'day';
    const PHONE                       = 'phone';
    const NUMBER                      = 'number';
    const GOALS                       = 'goals';
    const QUARTERS_SUB                = 'quartersSub';
    const QUARTERS_KEEP               = 'quartersKeep';
    const QUARTERS_INJURED            = 'quartersInjured';
    const QUARTERS_ABSENT             = 'quartersAbsent';
    const YELLOW_CARDS                = 'yellowCards';
    const RED_CARDS                   = 'redCards';
    const EMAIL                       = 'email';
    const PHONE1                      = 'phone1';
    const PHONE2                      = 'phone2';
    const GENDER                      = 'gender';
    const MAX_PLAYERS_PER_TEAM        = 'maxPlayersPerTeam';
    const DISPLAY_ORDER               = 'displayOrder';
    const GAME_DURATION_MINUTES       = 'gameDurationMinutes';
    const MINUTES_BETWEEN_GAMES       = 'minutesBetweenGames';
    const SCORING_TRACKED             = 'scoringTracked';
    const GAMES_PER_TEAM              = 'gamesPerTeam';
    const GENDER_PREFERENCE           = 'genderPreference';
    const TITLE                       = 'title';
    const PLAY_IN_HOME_GAME_ID        = 'playInHomeGameId';
    const PLAY_IN_VISITING_GAME_ID    = 'playInVisitingGameId';
    const PLAY_IN_BY_WIN              = 'playInByWin';
    const LOCKED                      = 'locked';
    const SCHEDULE_TYPE               = 'scheduleType';
    const INCLUDE_5TH_6TH_GAME        = 'include5th6thGame';
    const INCLUDE_3RD_4TH_GAME        = 'include3rd4thGame';
    const INCLUDE_SEMI_FINAL_GAMES    = 'includeSemiFinalGames';
    const INCLUDE_CHAMPIONSHIP_GAME   = 'includeChampionshipGame';
    const SCHEDULE_GAMES              = 'scheduleGames';
    const VOLUNTEER_POINTS            = 'volunteerPoints';
    const SEED                        = 'seed';
    const COMBINE_LEAGUE_SCHEDULES    = 'combineLeagueSchedules';
    const IS_CENTER                   = 'isCenter';
    const IS_ASSISTANT                = 'isAssistant';
    const IS_MENTOR                   = 'isMentor';
    const REFEREE_ROLE                = 'refereeRole';
    const BADGE_ID                    = 'badgeId';
    const MAX_GAMES_PER_DAY           = 'maxGamesPerDay';
    const SPECIAL_INSTRUCTIONS        = 'specialInstructions';
    const DIVISION_NAME               = 'divisionName';

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
            self::PHONE1  => '18052523944',
            self::PHONE2  => '',
        ];

    const DEFAULT_DIVISION_NAME = 'UTestG';
    protected static $defaultDivisionOrmAttributes =
        [
            self::NAME                      => self::DEFAULT_DIVISION_NAME,
            self::GENDER                    => 'Girls',
            self::MAX_PLAYERS_PER_TEAM      => 20,
            self::DISPLAY_ORDER             => 1,
            self::GAME_DURATION_MINUTES     => 60,
            self::MINUTES_BETWEEN_GAMES        => 180,
            self::SCORING_TRACKED           => 1,
            self::COMBINE_LEAGUE_SCHEDULES  => 0,
        ];

    protected static $defaultPoolOrmAttributes =
        [
            self::NAME   => 'Test Pool A',
        ];

    protected static $defaultFlightOrmAttributes =
        [
            self::NAME                          => 'Test Flight 1',
            self::INCLUDE_5TH_6TH_GAME          => 0,
            self::INCLUDE_3RD_4TH_GAME          => 0,
            self::INCLUDE_SEMI_FINAL_GAMES      => 0,
            self::INCLUDE_CHAMPIONSHIP_GAME     => 0,
            self::SCHEDULE_GAMES                => 1,
        ];

    protected static $defaultScheduleOrmAttributes =
        [
            self::NAME              => 'Test Default Schedule',
            self::SCHEDULE_TYPE     => ScheduleOrm::SCHEDULE_TYPE_LEAGUE,
            self::GAMES_PER_TEAM    => 10,
            self::START_DATE        => '2015-09-02',
            self::END_DATE          => '2015-10-20',
            self::START_TIME        => '08:00:00',
            self::END_TIME          => '19:00:00',
            self::DAYS_OF_WEEK      => '0001100',
            self::PUBLISHED         => 0,
            self::DISPLAY_NOTES     => '',
        ];

    protected static $defaultTeamOrmAttributes =
        [
            self::NAME              => 'Test Default Team 1',
            self::NAME_ID           => 'Test Id1',
            self::COLOR             => 'white',
            self::REGION            => '686',
            self::CITY              => 'Who Knows',
            self::VOLUNTEER_POINTS  => 0,
            self::SEED              => 0,
        ];

    protected static $defaultVisitingTeamOrmAttributes =
        [
            self::NAME              => 'Test Default Team 2',
            self::NAME_ID           => 'Test Id2',
            self::COLOR             => 'blue',
            self::REGION            => '688',
            self::CITY              => 'Uhhh',
            self::VOLUNTEER_POINTS  => 0,
            self::SEED              => 0,
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
            self::NAME             => 'default player name',
            self::EMAIL            => 'default player email',
            self::PHONE            => 'default player phone1',
            self::NUMBER           => null,
            self::GOALS            => 0,
            self::QUARTERS_SUB     => 0,
            self::QUARTERS_KEEP    => 0,
            self::QUARTERS_INJURED => 0,
            self::QUARTERS_ABSENT  => 0,
            self::YELLOW_CARDS     => 0,
            self::RED_CARDS        => 0,
        ];

    protected static $defaultGameTimeOrmAttributes =
        [
            self::START_TIME        => '16:30:00',
            self::GENDER_PREFERENCE => 'Girls',
            self::LOCKED            => 0,
        ];

    protected static $defaultGameOrmAttributes =
        [
            self::TITLE                     => 'Finals',
            self::PLAY_IN_HOME_GAME_ID      => 0,
            self::PLAY_IN_VISITING_GAME_ID  => 0,
            self::PLAY_IN_BY_WIN            => 0,
            self::LOCKED                    => 0,
        ];

    protected static $defaultRefereeOrmAttributes =
        [
            self::NAME                  => 'Ref Dave',
            self::EMAIL                 => 'dave@giannini.com',
            self::PHONE                 => '18052523944',
            self::BADGE_ID              => RefereeOrm::NATIONAL,
            self::MAX_GAMES_PER_DAY     => 1,
            self::SPECIAL_INSTRUCTIONS  => "Hello Turf",
        ];

    protected static $defaultCenterRefereeOrmAttributes =
        [
            self::NAME                  => 'Ref DaveCenter',
            self::EMAIL                 => 'daveCenter@giannini.com',
            self::PHONE                 => '18052523944',
            self::BADGE_ID              => RefereeOrm::NATIONAL,
            self::MAX_GAMES_PER_DAY     => 1,
            self::SPECIAL_INSTRUCTIONS  => "Hello Turf",
        ];

    protected static $defaultAssistantReferee1OrmAttributes =
        [
            self::NAME                  => 'Ref DaveAssistant1',
            self::EMAIL                 => 'daveAssistant1@giannini.com',
            self::PHONE                 => '18052523944',
            self::BADGE_ID              => RefereeOrm::NATIONAL,
            self::MAX_GAMES_PER_DAY     => 1,
            self::SPECIAL_INSTRUCTIONS  => "Hello Turf",
        ];

    protected static $defaultAssistantReferee2OrmAttributes =
        [
            self::NAME                  => 'Ref DaveAssistant2',
            self::EMAIL                 => 'daveAssistant2@giannini.com',
            self::PHONE                 => '18052523944',
            self::BADGE_ID              => RefereeOrm::NATIONAL,
            self::MAX_GAMES_PER_DAY     => 1,
            self::SPECIAL_INSTRUCTIONS  => "Hello Turf",
        ];

    protected static $defaultDivisionRefereeOrmAttributes =
        [
            self::IS_CENTER     => 0,
            self::IS_ASSISTANT  => 1,
            self::IS_MENTOR     => 1,
        ];

    protected static $defaultGameRefereeOrmAttributes =
        [
            self::REFEREE_ROLE  => GameRefereeOrm::CENTER_ROLE,
        ];

    protected static $defaultStandbyRefereeOrmAttributes =
        [
            self::REFEREE_ROLE  => GameRefereeOrm::CENTER_ROLE,
            self::DIVISION_NAME => self::DEFAULT_DIVISION_NAME,
            self::START_TIME    => '11:30:00',
        ];

    public $defaultLeagueOrm;
    public $defaultSeasonOrm;
    public $defaultFacilityOrm;
    public $defaultFieldOrm;
    public $defaultGameDateOrm;
    public $defaultFamilyOrm;
    public $defaultDivisionOrm;
    public $defaultDivisionFieldOrm;
    public $defaultFlightOrm;
    public $defaultPoolOrm;
    public $defaultScheduleOrm;
    public $defaultTeamOrm;
    public $defaultVisitingTeamOrm;
    public $defaultCoachOrm;
    public $defaultAssistantCoachOrm;
    public $defaultPlayerOrm;
    /** @var  GameTimeOrm */
    public $defaultGameTimeOrm;
    /** @var  GameOrm */
    public $defaultGameOrm;
    public $defaultFamilyGameOrm;
    public $defaultScheduleCoordinatorOrm;
    public $defaultRefereeOrm;
    public $defaultTeamRefereeOrm;
    public $defaultGameRefereeOrm;
    public $defaultDivisionRefereeOrm;
    public $defaultGameDateRefereeOrm;
    public $defaultCenterRefereeOrm;
    public $defaultAssistantReferee1Orm;
    public $defaultAssistantReferee2Orm;
    public $defaultRefereeCrewOrm;
    public $defaultStandbyRefereeOrm;

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

        $this->defaultDivisionOrm = DivisionOrm::create(
            $this->defaultSeasonOrm->id,
            self::$defaultDivisionOrmAttributes[self::NAME],
            self::$defaultDivisionOrmAttributes[self::GENDER],
            self::$defaultDivisionOrmAttributes[self::MAX_PLAYERS_PER_TEAM],
            self::$defaultDivisionOrmAttributes[self::GAME_DURATION_MINUTES],
            self::$defaultDivisionOrmAttributes[self::MINUTES_BETWEEN_GAMES],
            self::$defaultDivisionOrmAttributes[self::DISPLAY_ORDER],
            self::$defaultDivisionOrmAttributes[self::SCORING_TRACKED],
            self::$defaultDivisionOrmAttributes[self::COMBINE_LEAGUE_SCHEDULES]);

        $this->defaultFieldOrm = FieldOrm::create(
            $this->defaultFacilityOrm->id,
            self::$defaultFieldOrmAttributes[self::NAME],
            self::$defaultFieldOrmAttributes[self::ENABLED]);

        $this->defaultDivisionFieldOrm = DivisionFieldOrm::create(
            $this->defaultDivisionOrm->id,
            $this->defaultFieldOrm->id);

        $this->defaultGameDateOrm = GameDateOrm::create(
            $this->defaultSeasonOrm->id,
            self::$defaultGameDateOrmAttributes[self::DAY]);

        $this->defaultFamilyOrm = FamilyOrm::create(
            $this->defaultSeasonOrm->id,
            self::$defaultFamilyOrmAttributes[self::PHONE1]);

        $this->defaultScheduleOrm = ScheduleOrm::create(
            $this->defaultDivisionOrm->id,
            self::$defaultScheduleOrmAttributes[self::NAME],
            self::$defaultScheduleOrmAttributes[self::SCHEDULE_TYPE],
            self::$defaultScheduleOrmAttributes[self::GAMES_PER_TEAM],
            self::$defaultScheduleOrmAttributes[self::START_DATE],
            self::$defaultScheduleOrmAttributes[self::END_DATE],
            self::$defaultScheduleOrmAttributes[self::START_TIME],
            self::$defaultScheduleOrmAttributes[self::END_TIME],
            self::$defaultScheduleOrmAttributes[self::DAYS_OF_WEEK],
            self::$defaultScheduleOrmAttributes[self::PUBLISHED],
            self::$defaultScheduleOrmAttributes[self::DISPLAY_NOTES]);

        $this->defaultFlightOrm = FlightOrm::create(
            $this->defaultScheduleOrm->id,
            self::$defaultFlightOrmAttributes[self::NAME],
            self::$defaultFlightOrmAttributes[self::INCLUDE_5TH_6TH_GAME],
            self::$defaultFlightOrmAttributes[self::INCLUDE_3RD_4TH_GAME],
            self::$defaultFlightOrmAttributes[self::INCLUDE_SEMI_FINAL_GAMES],
            self::$defaultFlightOrmAttributes[self::INCLUDE_CHAMPIONSHIP_GAME]);

        $this->defaultPoolOrm = PoolOrm::create(
            $this->defaultFlightOrm->id,
            $this->defaultScheduleOrm->id,
            self::$defaultPoolOrmAttributes[self::NAME]);

        $this->defaultTeamOrm = TeamOrm::create(
            $this->defaultDivisionOrm->id,
            $this->defaultPoolOrm->id,
            self::$defaultTeamOrmAttributes[self::NAME],
            self::$defaultTeamOrmAttributes[self::NAME_ID],
            self::$defaultTeamOrmAttributes[self::REGION],
            self::$defaultTeamOrmAttributes[self::CITY],
            self::$defaultTeamOrmAttributes[self::VOLUNTEER_POINTS],
            self::$defaultTeamOrmAttributes[self::SEED],
            self::$defaultTeamOrmAttributes[self::COLOR]);

        $this->defaultVisitingTeamOrm = TeamOrm::create(
            $this->defaultDivisionOrm->id,
            $this->defaultPoolOrm->id,
            self::$defaultVisitingTeamOrmAttributes[self::NAME],
            self::$defaultVisitingTeamOrmAttributes[self::NAME_ID],
            self::$defaultVisitingTeamOrmAttributes[self::REGION],
            self::$defaultVisitingTeamOrmAttributes[self::CITY],
            self::$defaultVisitingTeamOrmAttributes[self::VOLUNTEER_POINTS],
            self::$defaultVisitingTeamOrmAttributes[self::SEED],
            self::$defaultVisitingTeamOrmAttributes[self::COLOR]);

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
            self::$defaultPlayerOrmAttributes[self::PHONE],
            self::$defaultPlayerOrmAttributes[self::NUMBER]);

        $this->defaultGameTimeOrm = GameTimeOrm::create(
            $this->defaultGameDateOrm->id,
            $this->defaultFieldOrm->id,
            self::$defaultGameTimeOrmAttributes[self::START_TIME],
            self::$defaultGameTimeOrmAttributes[self::GENDER_PREFERENCE]);

        $this->defaultGameOrm = GameOrm::create(
            $this->defaultFlightOrm->scheduleId,
            $this->defaultFlightOrm->id,
            $this->defaultPoolOrm->id,
            $this->defaultGameTimeOrm->gameDateId,
            $this->defaultGameTimeOrm->id,
            $this->defaultTeamOrm->id,
            $this->defaultVisitingTeamOrm->id,
            self::$defaultGameOrmAttributes[self::TITLE],
            self::$defaultGameOrmAttributes[self::LOCKED]);

        $this->defaultFamilyGameOrm = FamilyGameOrm::create(
            $this->defaultFamilyOrm->id,
            $this->defaultGameOrm->id);

        $this->defaultRefereeOrm = RefereeOrm::create(
            $this->defaultSeasonOrm->id,
            $this->defaultFamilyOrm->id,
            self::$defaultRefereeOrmAttributes[self::NAME],
            self::$defaultRefereeOrmAttributes[self::EMAIL],
            self::$defaultRefereeOrmAttributes[self::PHONE],
            self::$defaultRefereeOrmAttributes[self::BADGE_ID],
            self::$defaultRefereeOrmAttributes[self::MAX_GAMES_PER_DAY],
            self::$defaultRefereeOrmAttributes[self::SPECIAL_INSTRUCTIONS]);

        $this->defaultTeamRefereeOrm = TeamRefereeOrm::create(
            $this->defaultTeamOrm->id,
            $this->defaultRefereeOrm->id);

        $this->defaultGameRefereeOrm = GameRefereeOrm::create(
            $this->defaultGameOrm->id,
            $this->defaultRefereeOrm->id,
            self::$defaultGameRefereeOrmAttributes[self::REFEREE_ROLE]);

        $this->defaultDivisionRefereeOrm = DivisionRefereeOrm::create(
            $this->defaultDivisionOrm->id,
            $this->defaultRefereeOrm->id,
            self::$defaultDivisionRefereeOrmAttributes[self::IS_CENTER],
            self::$defaultDivisionRefereeOrmAttributes[self::IS_ASSISTANT],
            self::$defaultDivisionRefereeOrmAttributes[self::IS_MENTOR]);

        $this->defaultGameDateRefereeOrm = GameDateRefereeOrm::create(
            $this->defaultGameDateOrm->id,
            $this->defaultRefereeOrm->id);

        $this->defaultCenterRefereeOrm = RefereeOrm::create(
            $this->defaultSeasonOrm->id,
            $this->defaultFamilyOrm->id,
            self::$defaultCenterRefereeOrmAttributes[self::NAME],
            self::$defaultCenterRefereeOrmAttributes[self::EMAIL],
            self::$defaultCenterRefereeOrmAttributes[self::PHONE],
            self::$defaultCenterRefereeOrmAttributes[self::BADGE_ID],
            self::$defaultCenterRefereeOrmAttributes[self::MAX_GAMES_PER_DAY],
            self::$defaultCenterRefereeOrmAttributes[self::SPECIAL_INSTRUCTIONS]);

        $this->defaultAssistantReferee1Orm = RefereeOrm::create(
            $this->defaultSeasonOrm->id,
            $this->defaultFamilyOrm->id,
            self::$defaultAssistantReferee1OrmAttributes[self::NAME],
            self::$defaultAssistantReferee1OrmAttributes[self::EMAIL],
            self::$defaultAssistantReferee1OrmAttributes[self::PHONE],
            self::$defaultAssistantReferee1OrmAttributes[self::BADGE_ID],
            self::$defaultAssistantReferee1OrmAttributes[self::MAX_GAMES_PER_DAY],
            self::$defaultAssistantReferee1OrmAttributes[self::SPECIAL_INSTRUCTIONS]);

        $this->defaultAssistantReferee2Orm = RefereeOrm::create(
            $this->defaultSeasonOrm->id,
            $this->defaultFamilyOrm->id,
            self::$defaultAssistantReferee2OrmAttributes[self::NAME],
            self::$defaultAssistantReferee2OrmAttributes[self::EMAIL],
            self::$defaultAssistantReferee2OrmAttributes[self::PHONE],
            self::$defaultAssistantReferee2OrmAttributes[self::BADGE_ID],
            self::$defaultAssistantReferee2OrmAttributes[self::MAX_GAMES_PER_DAY],
            self::$defaultAssistantReferee2OrmAttributes[self::SPECIAL_INSTRUCTIONS]);

        $this->defaultRefereeCrewOrm = RefereeCrewOrm::create(
            $this->defaultCenterRefereeOrm->id,
            $this->defaultAssistantReferee1Orm->id,
            $this->defaultAssistantReferee2Orm->id,
            $this->defaultDivisionOrm->id,
            $this->defaultTeamOrm->id
        );

        $this->defaultStandbyRefereeOrm = StandbyRefereeOrm::create(
            $this->defaultFacilityOrm->id,
            $this->defaultGameDateOrm->id,
            self::$defaultStandbyRefereeOrmAttributes[self::DIVISION_NAME],
            self::$defaultStandbyRefereeOrmAttributes[self::START_TIME],
            $this->defaultRefereeOrm->id,
            self::$defaultStandbyRefereeOrmAttributes[self::REFEREE_ROLE]
        );
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
        $refereeOrms = RefereeOrm::loadBySeasonId($seasonOrm->id);
        foreach ($refereeOrms as $refereeOrm) {
            $this->clearReferee($refereeOrm);
        }

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
     * Cascading delete of everything below refereeOrm
     *
     * @param RefereeOrm $refereeOrm
     */
    protected function clearReferee($refereeOrm)
    {
        $teamReferees = TeamRefereeOrm::loadByRefereeId($refereeOrm->id);
        foreach ($teamReferees as $teamReferee) {
            $teamReferee->delete();
        }

        $gameReferees = GameRefereeOrm::loadByRefereeId($refereeOrm->id);
        foreach ($gameReferees as $gameReferee) {
            $gameReferee->delete();
        }

        $divisionReferees = DivisionRefereeOrm::loadByRefereeId($refereeOrm->id);
        foreach ($divisionReferees as $divisionReferee) {
            $divisionReferee->delete();
        }

        $gameDateReferees = GameDateRefereeOrm::loadByRefereeId($refereeOrm->id);
        foreach ($gameDateReferees as $gameDateReferee) {
            $gameDateReferee->delete();
        }

        $refereeOrm->delete();
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

        $standbyReferees = StandbyRefereeOrm::loadByFacilityId($facilityOrm->id);
        foreach ($standbyReferees as $standbyReferee) {
            $standbyReferee->delete();
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
        $familyGameOrms = FamilyGameOrm::loadByFamilyId($familyOrm->id);
        foreach ($familyGameOrms as $familyGameOrm) {
            $familyGameOrm->delete();
        }

        $familyOrm->delete();
    }

    /**
     * Cascading delete of everything below divisionOrm and then divisionOrm
     *
     * @param DivisionOrm $divisionOrm
     */
    protected function clearDivision($divisionOrm)
    {
        $refereeCrewOrms = RefereeCrewOrm::loadByDivisionId($divisionOrm->id);
        foreach ($refereeCrewOrms as $refereeCrewOrm) {
            $refereeCrewOrm->delete();
        }

        $teamOrms = TeamOrm::loadByDivisionId($divisionOrm->id);
        foreach ($teamOrms as $teamOrm) {
            $this->clearTeam($teamOrm);
        }

        $divisionFieldOrms = DivisionFieldOrm::loadByDivisionId($divisionOrm->id);
        foreach ($divisionFieldOrms as $divisionFieldOrm) {
            $divisionFieldOrm->delete();
        }

        $scheduleOrms = ScheduleOrm::loadByDivisionId($divisionOrm->id);
        foreach ($scheduleOrms as $scheduleOrm) {
            $this->clearSchedule($scheduleOrm);
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
        $gameOrms = GameOrm::loadByPoolId($poolOrm->id);
        foreach ($gameOrms as $gameOrm) {
            $this->clearGame($gameOrm);
        }

        $poolOrm->delete();
    }

    /**
     * Cascading delete of everything below flightOrm and then flightOrm
     *
     * @param FlightOrm $flightOrm
     */
    protected function clearFlight($flightOrm)
    {
        $poolOrms = PoolOrm::loadByFlightId($flightOrm->id);
        foreach ($poolOrms as $poolOrm) {
            $this->clearPool($poolOrm);
        }

        $flightOrm->delete();
    }

    /**
     * Cascading delete of everything below scheduleOrm and then scheduleOrm
     *
     * @param ScheduleOrm $scheduleOrm
     */
    protected function clearSchedule($scheduleOrm)
    {
        $flightOrms = FlightOrm::loadByScheduleId($scheduleOrm->id);
        foreach ($flightOrms as $flightOrm) {
            $this->clearFlight($flightOrm);
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
        foreach ($playerOrms as $playerOrm) {
            $this->clearPlayer($playerOrm);
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