<?php

namespace DAG\Orm\Schedule;

use DAG\Framework\Exception\Assertion;
use DAG\Framework\Orm\FieldValidator as FV;
use DAG\Framework\Orm\PersistenceConfig as PC;
use DAG\Framework\Orm\PersistenceModel;
use DAG\Framework\Orm\DuplicateEntryException;


/**
 * @property int    $id
 * @property int    $scheduleId
 * @property int    $flightId
 * @property int    $poolId
 * @property int    $gameDateId
 * @property int    $gameTimeId
 * @property int    $homeTeamId
 * @property int    $visitingTeamId
 * @property int    $homeTeamScore
 * @property int    $visitingTeamScore
 * @property int    $homeTeamYellowCards
 * @property int    $visitingTeamYellowCards
 * @property int    $homeTeamRedCards
 * @property int    $visitingTeamRedCards
 * @property int    $notes
 * @property string $title
 * @property int    $playInHomeGameId
 * @property int    $playInVisitingGameId
 * @property int    $playInByWin
 * @property int    $locked
 * @property int    $refereeCrewId
 */
class GameOrm extends PersistenceModel
{
    const TITLE_NONE                        = '';
    const TITLE_NORMAL                      = 'Normal';
    const TITLE_PLAYOFF                     = 'Playoff';
    const TITLE_QUARTER_FINAL               = 'Quarter-Final';
    const TITLE_5TH_6TH                     = '5th/6th';
    const TITLE_3RD_4TH                     = '3rd/4th';
    const TITLE_SEMI_FINAL                  = 'Semi-Final';
    const TITLE_CHAMPIONSHIP                = 'Championship';

    const FIELD_ID                          = 'id';
    const FIELD_SCHEDULE_ID                 = 'scheduleId';
    const FIELD_FLIGHT_ID                   = 'flightId';
    const FIELD_POOL_ID                     = 'poolId';
    const FIELD_GAME_DATE_ID                = 'gameDateId';
    const FIELD_GAME_TIME_ID                = 'gameTimeId';
    const FIELD_HOME_TEAM_ID                = 'homeTeamId';
    const FIELD_VISITING_TEAM_ID            = 'visitingTeamId';
    const FIELD_HOME_TEAM_SCORE             = 'homeTeamScore';
    const FIELD_VISITING_TEAM_SCORE         = 'visitingTeamScore';
    const FIELD_HOME_TEAM_YELLOW_CARDS      = 'homeTeamYellowCards';
    const FIELD_VISITING_TEAM_YELLOW_CARDS  = 'visitingTeamYellowCards';
    const FIELD_HOME_TEAM_RED_CARDS         = 'homeTeamRedCards';
    const FIELD_VISITING_TEAM_RED_CARDS     = 'visitingTeamRedCards';
    const FIELD_NOTES                       = 'notes';
    const FIELD_TITLE                       = 'title';
    const FIELD_PLAY_IN_HOME_GAME_ID        = 'playInHomeGameId';
    const FIELD_PLAY_IN_VISITING_GAME_ID    = 'playInVisitingGameId';
    const FIELD_PLAY_IN_BY_WIN              = 'playInByWin';
    const FIELD_LOCKED                      = 'locked';
    const FIELD_REFEREE_CREW_ID             = 'refereeCrewId';

    public static $titles = [
        self::TITLE_PLAYOFF,
        self::TITLE_QUARTER_FINAL,
        self::TITLE_5TH_6TH,
        self::TITLE_SEMI_FINAL,
        self::TITLE_3RD_4TH,
        self::TITLE_CHAMPIONSHIP,
    ];

    public static $abbreviatedTitles = [
        self::TITLE_PLAYOFF         => "PO",
        self::TITLE_QUARTER_FINAL   => "QF",
        self::TITLE_5TH_6TH         => "5/6",
        self::TITLE_SEMI_FINAL      => "SF",
        self::TITLE_3RD_4TH         => "3/4",
        self::TITLE_CHAMPIONSHIP    => "C",
    ];

    protected static $fields = [
        self::FIELD_ID                          => [FV::INT,    [FV::NO_CONSTRAINTS], null],
        self::FIELD_SCHEDULE_ID                 => [FV::INT,    [FV::NO_CONSTRAINTS]],
        self::FIELD_FLIGHT_ID                   => [FV::INT,    [FV::NO_CONSTRAINTS]],
        self::FIELD_POOL_ID                     => [FV::INT,    [FV::NO_CONSTRAINTS], null],
        self::FIELD_GAME_DATE_ID                => [FV::INT,    [FV::NO_CONSTRAINTS]],
        self::FIELD_GAME_TIME_ID                => [FV::INT,    [FV::NO_CONSTRAINTS]],
        self::FIELD_HOME_TEAM_ID                => [FV::INT,    [FV::NO_CONSTRAINTS], null],
        self::FIELD_VISITING_TEAM_ID            => [FV::INT,    [FV::NO_CONSTRAINTS], null],
        self::FIELD_HOME_TEAM_SCORE             => [FV::INT,    [FV::NO_CONSTRAINTS], null],
        self::FIELD_VISITING_TEAM_SCORE         => [FV::INT,    [FV::NO_CONSTRAINTS], null],
        self::FIELD_HOME_TEAM_YELLOW_CARDS      => [FV::INT,    [FV::NO_CONSTRAINTS], 0],
        self::FIELD_VISITING_TEAM_YELLOW_CARDS  => [FV::INT,    [FV::NO_CONSTRAINTS], 0],
        self::FIELD_HOME_TEAM_RED_CARDS         => [FV::INT,    [FV::NO_CONSTRAINTS], 0],
        self::FIELD_VISITING_TEAM_RED_CARDS     => [FV::INT,    [FV::NO_CONSTRAINTS], 0],
        self::FIELD_NOTES                       => [FV::STRING, [FV::NO_CONSTRAINTS], ''],
        self::FIELD_TITLE                       => [FV::STRING, [FV::NO_CONSTRAINTS]],
        self::FIELD_PLAY_IN_HOME_GAME_ID        => [FV::INT,    [FV::NO_CONSTRAINTS], 0],
        self::FIELD_PLAY_IN_VISITING_GAME_ID    => [FV::INT,    [FV::NO_CONSTRAINTS], 0],
        self::FIELD_PLAY_IN_BY_WIN              => [FV::INT,    [FV::NO_CONSTRAINTS], 0],
        self::FIELD_LOCKED                      => [FV::INT,    [FV::NO_CONSTRAINTS]],
        self::FIELD_REFEREE_CREW_ID             => [FV::INT,    [FV::NO_CONSTRAINTS], null],
    ];

    protected static $config = [
        PC::PERSISTENCE_DRIVER => PC::DRIVER_MYSQL,
        PC::SCHEMA             => 'schedule_rw',
        PC::TABLE              => 'game',
        PC::AUTO_INC_FIELD     => self::FIELD_ID,
        PC::PRIMARY_KEYS       => [self::FIELD_ID],
    ];

    /**
     * Create a GameOrm
     *
     * @param int       $poolId
     * @param int       $scheduleId
     * @param int       $flightId
     * @param int       $gameDateId
     * @param int       $gameTimeId
     * @param int       $homeTeamId
     * @param int       $visitingTeamId
     * @param string    $title
     * @param int       $locked
     * @param int       $playInHomeGameId
     * @param int       $playInVisitingGameId
     * @param int       $playInByWin
     *
     * @return GameOrm
     * @throws DuplicateEntryException
     */
    public static function create(
        $scheduleId,
        $flightId,
        $poolId,
        $gameDateId,
        $gameTimeId,
        $homeTeamId,
        $visitingTeamId,
        $title = '',
        $locked = 0,
        $playInHomeGameId = 0,
        $playInVisitingGameId = 0,
        $playInByWin = 0
    ) {
        // Verify GameTimeOrm exists and a game has not been assigned
        $gameTimeOrm = GameTimeOrm::loadById($gameTimeId);
        Assertion::isTrue(!isset($gameTimeOrm->gameId), "GameTime $gameTimeId already has a game assignment.  Cannot double book.");

        // Create the GameOrm
        $result = self::getPersistenceDriver()->create(
            array_filter(
                [
                    self::FIELD_SCHEDULE_ID                 => $scheduleId,
                    self::FIELD_FLIGHT_ID                   => $flightId,
                    self::FIELD_POOL_ID                     => $poolId,
                    self::FIELD_GAME_DATE_ID                => $gameDateId,
                    self::FIELD_GAME_TIME_ID                => $gameTimeId,
                    self::FIELD_HOME_TEAM_ID                => $homeTeamId,
                    self::FIELD_VISITING_TEAM_ID            => $visitingTeamId,
                    self::FIELD_TITLE                       => $title,
                    self::FIELD_PLAY_IN_HOME_GAME_ID        => $playInHomeGameId,
                    self::FIELD_PLAY_IN_VISITING_GAME_ID    => $playInVisitingGameId,
                    self::FIELD_PLAY_IN_BY_WIN              => $playInByWin,
                    self::FIELD_LOCKED                      => $locked,
                ],
                function ($item) {
                    return $item !== null;
                }
            )
        );

        $gameOrm = new static($result);

        // Update the GameTimeOrm to reference this game
        $gameTimeOrm->gameId = $gameOrm->id;
        $gameTimeOrm->save();

        return $gameOrm;
    }

    /**
     * Load a GameOrm by id
     *
     * @param int $id
     *
     * @return GameOrm
     */
    public static function loadById($id)
    {
        $result = self::getPersistenceDriver()->getOne([self::FIELD_ID => $id]);

        return new static($result);
    }

    /**
     * Load a GameOrm by gameTimeId
     *
     * @param int $gameTimeId
     *
     * @return GameOrm
     */
    public static function loadByGameTimeId($gameTimeId)
    {
        $result = self::getPersistenceDriver()->getOne([self::FIELD_GAME_TIME_ID => $gameTimeId]);

        return new static($result);
    }

    /**
     * Get GameOrms for a flightId
     *
     * @param $flightId
     *
     * @return GameOrm[]
     */
    public static function loadByFlightId($flightId)
    {
        $results = self::getPersistenceDriver()->getMany(
            [
                self::FIELD_FLIGHT_ID => $flightId
            ]);

        $gameOrms = [];
        foreach ($results as $result) {
            $gameOrms[] = new static($result);
        }

        return $gameOrms;
    }

    /**
     * Get GameOrms for a flightId
     *
     * @param int $scheduleId
     *
     * @return GameOrm[]
     */
    public static function loadByScheduleId($scheduleId)
    {
        $results = self::getPersistenceDriver()->getMany(
            [
                self::FIELD_SCHEDULE_ID => $scheduleId
            ]);

        $gameOrms = [];
        foreach ($results as $result) {
            $gameOrms[] = new static($result);
        }

        return $gameOrms;
    }

    /**
     * Get GameOrms for a flightId
     *
     * @param int $scheduleId
     *
     * @return GameOrm[]
     */
    public static function loadByScheduleIdGameDateId($scheduleId, $gameDateId)
    {
        $results = self::getPersistenceDriver()->getMany(
            [
                self::FIELD_SCHEDULE_ID     => $scheduleId,
                self::FIELD_GAME_DATE_ID    => $gameDateId,
            ]);

        $gameOrms = [];
        foreach ($results as $result) {
            $gameOrms[] = new static($result);
        }

        return $gameOrms;
    }

    /**
     * Get GameOrms for a flightId and title
     *
     * @param int       $flightId
     * @param string    $title
     *
     * @return GameOrm[]
     */
    public static function loadByFlightIdAndTitle($flightId, $title)
    {
        $results = self::getPersistenceDriver()->getMany(
            [
                self::FIELD_FLIGHT_ID   => $flightId,
                self::FIELD_TITLE       => $title,
            ]);

        $gameOrms = [];
        foreach ($results as $result) {
            $gameOrms[] = new static($result);
        }

        return $gameOrms;
    }

    /**
     * Get GameOrms for a poolId
     *
     * @param $poolId
     *
     * @return GameOrm[]
     */
    public static function loadByPoolId($poolId)
    {
        $results = self::getPersistenceDriver()->getMany(
            [
                self::FIELD_POOL_ID => $poolId
            ]);

        $gameOrms = [];
        foreach ($results as $result) {
            $gameOrms[] = new static($result);
        }

        return $gameOrms;
    }

    /**
     * Load a GameOrms by teamId
     *
     * @param int $teamId
     *
     * @return array []   GameOrms
     */
    public static function loadByTeamId($teamId)
    {
        $results = self::getPersistenceDriver()->getManyFromCustomMySqlQuery(
            [],
            "where " . self::FIELD_HOME_TEAM_ID . " = $teamId or " . self::FIELD_VISITING_TEAM_ID . " = $teamId");

        $gameOrms = [];
        foreach ($results as $result) {
            $gameOrms[] = new static($result);
        }

        return $gameOrms;
    }

    /**
     * Load a GameOrms by gameDateId and teamId
     *
     * @param int $gameDateId
     * @param int $teamId
     *
     * @return array []   GameOrms
     */
    public static function loadByGameDateIdTeamId($gameDateId, $teamId)
    {
        $results = self::getPersistenceDriver()->getManyFromCustomMySqlQuery(
            [],
            "where 
                (" . self::FIELD_GAME_DATE_ID . " = $gameDateId and " . self::FIELD_HOME_TEAM_ID . " = $teamId) 
                or (" . self::FIELD_GAME_DATE_ID . " = $gameDateId and " . self::FIELD_VISITING_TEAM_ID . " = $teamId)");

        $gameOrms = [];
        foreach ($results as $result) {
            $gameOrms[] = new static($result);
        }

        return $gameOrms;
    }

    /**
     * Load a GameOrms by teamId
     *
     * @param int $teamId
     *
     * @return array []   GameOrms
     */
    public static function loadByHomeTeamId($teamId)
    {
        $results = self::getPersistenceDriver()->getManyFromCustomMySqlQuery(
            [],
            "where " . self::FIELD_HOME_TEAM_ID . " = $teamId");

        $gameOrms = [];
        foreach ($results as $result) {
            $gameOrms[] = new static($result);
        }

        return $gameOrms;
    }

    /**
     * Load a GameOrm by playInGameId and playInByWin
     *
     * @param int       $gameId         - Playoff game id in bracket play used to find next game teams play
     * @param int       $playInByWin    - 1 if looking for next game based on winning prior game; 0 otherwise
     * @param GameOrm   $gameOrm        - Output parameter
     *
     * @return bool
     */
    public static function findByPlayInGameId($gameId, $playInByWin, &$gameOrm)
    {
        $results = self::getPersistenceDriver()->getMany(
            [
                self::FIELD_PLAY_IN_BY_WIN          => $playInByWin,
                self::FIELD_PLAY_IN_HOME_GAME_ID    => $gameId
            ]);

        if (count($results) == 0) {
            $results = self::getPersistenceDriver()->getMany(
                [
                    self::FIELD_PLAY_IN_BY_WIN              => $playInByWin,
                    self::FIELD_PLAY_IN_VISITING_GAME_ID    => $gameId
                ]);
        }

        if (count($results) == 0) {
            return false;
        }

        Assertion::isTrue(count($results) == 1, "Invalid count of games found in findByPlayInGameId for $gameId, $playInByWin");

        $gameOrm = new static($results[0]);
        return true;
    }
}