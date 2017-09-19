<?php

use \DAG\Domain\Schedule\Division;
use \DAG\Domain\Schedule\Schedule;
use \DAG\Framework\Orm\DuplicateEntryException;
use \DAG\Framework\Exception\Assertion;
use \DAG\Domain\Schedule\GameTime;
use \DAG\Domain\Schedule\DivisionField;
use \DAG\Domain\Schedule\Team;
use \DAG\Domain\Schedule\Pool;
use \DAG\Domain\Schedule\Game;
use \DAG\Domain\Schedule\Family;
use \DAG\Domain\Schedule\FamilyGame;
use \DAG\Domain\Schedule\Field;
use \DAG\Domain\Schedule\Flight;
use \DAG\Domain\Schedule\Coach;
use \DAG\Domain\Schedule\GameDate;

class NotEnoughGameTimesException extends DAG_Exception
{
    /**
     * NotEnoughGameTimesException constructor
     *
     * @param int $numberOfTeams
     * @param int $gamesPerTeam
     * @param int $numberOfGameTimes
     */
    public function __construct($numberOfTeams, $gamesPerTeam, $numberOfGameTimes)
    {
        $gamesNeeded = $numberOfTeams * $gamesPerTeam;
        parent::__construct("Need $gamesNeeded available game times.  Only $numberOfGameTimes available.");
    }
}

class UnpublishedScheduleException extends DAG_Exception
{
    public function __construct($message)
    {
        parent::__construct($message);
    }
}

class ScheduleOverlapException extends DAG_Exception
{
    /**
     * ScheduleOverlapException constructor
     *
     * @param string    $startDate
     * @param string    $endDate
     * @param Schedule  $existingSchedule
     */
    public function __construct($startDate, $endDate, $existingSchedule)
    {
        parent::__construct("Cannot create schedule due to dates overlapping with another schedule new($startDate, $endDate) existing($existingSchedule->startDate, $existingSchedule->endDate)");
    }
}

/**
 * Class Controller_AdminSchedules_Schedule
 *
 * @brief Select a field to administer or create a new schedule
 */
class Controller_AdminSchedules_Schedule extends Controller_AdminSchedules_Base {

    /** @var string */
    public $m_name;
    public $m_divisionNames;
    public $m_scheduleId;
    public $m_scheduleType;
    public $m_gamesPerTeam;
    public $m_startDate;
    public $m_endDate;
    public $m_startTime;
    public $m_endTime;
    public $m_daysSelected          = [];
    public $m_daysSelectedString    = '';
    public $m_crossPoolUpdates      = [];
    public $m_teamPoolUpdates       = [];
    public $m_flightUpdates         = [];
    public $m_familyId;
    public $m_moveGameId;
    public $m_moveFieldId;
    public $m_moveGameTime;
    public $m_primaryGameId;
    public $m_secondaryGameId;
    public $m_lockToggleGameId;
    public $m_deleteGameId;
    public $m_teamId;
    public $m_fieldId;
    public $m_showPublishedSchedules;
    public $m_flightId;
    public $m_poolId;
    public $m_flightName;
    public $m_poolName;
    public $m_homeTeamId;
    public $m_visitingTeamId;
    public $m_gameTimeId;
    public $m_actualStartTime;

    public function __construct() {
        parent::__construct();

        if (isset($_SERVER['REQUEST_METHOD']) and $_SERVER['REQUEST_METHOD'] == 'POST') {
            if ($this->m_operation == View_Base::CREATE
                or $this->m_operation == View_Base::UPDATE
                or $this->m_operation == View_Base::DELETE) {

                $this->m_name = $this->getPostAttribute(
                    View_Base::NAME,
                    '', true, false, 'Error: Schedule Name is a Required Field'
                );
            }

            if ($this->m_operation == View_Base::CREATE) {
                $this->m_scheduleType = $this->getPostAttribute(
                    View_Base::SCHEDULE_TYPE,
                    '', true, false, 'Error: Schedule Type is a Required Field'
                );
            }

            if ($this->m_operation == View_Base::CREATE
                or $this->m_operation == View_Base::VIEW) {

                $this->m_divisionNames = $this->getPostAttributeArray(
                    View_Base::DIVISION_NAMES
                );
                if (count($this->m_divisionNames) == 0) {
                    $this->setErrorString("Error: Division Name(s) is a Required Field");
                }
            }

            if ($this->m_operation == View_Base::CREATE
                or $this->m_operation == View_Base::UPDATE
                or $this->m_operation == View_Base::ALTER) {

                $startDateId        = $this->getPostAttribute(View_Base::START_DATE, null, true, true, "Error: Start Date is a required field");
                $endDateId          = $this->getPostAttribute(View_Base::END_DATE, null, true, true, "Error: End Date is a required field");
                $this->m_startTime  = $this->getPostAttribute(View_Base::START_TIME, null);
                $this->m_endTime    = $this->getPostAttribute(View_Base::END_TIME, null);

                $startDate          = GameDate::lookupById((int)$startDateId);
                $endDate            = GameDate::lookupById((int)$endDateId);
                $this->m_startDate  = $startDate->day;
                $this->m_endDate    = $endDate->day;

                // Verify startDate < endDate
                if ($this->m_startDate > $this->m_endDate) {
                    $this->m_missingAttributes += 1;
                    $this->m_errorString = "Error: Start Date ($this->m_startDate) must be less than End Date ($this->m_endDate)";
                }
            }

            if ($this->m_operation == View_Base::CREATE
                or $this->m_operation == View_Base::UPDATE) {

                $this->m_gamesPerTeam = $this->getPostAttribute(
                    View_Base::GAMES_PER_TEAM,
                    0,
                    true,
                    true
                );
                if ($this->m_gamesPerTeam == 0) {
                    $this->setErrorString("Error: Games Per Team is a required Field");
                }

                $this->m_daysSelected[View_Base::MONDAY]    = $this->_isDaySelected(View_Base::MONDAY);
                $this->m_daysSelected[View_Base::TUESDAY]   = $this->_isDaySelected(View_Base::TUESDAY);
                $this->m_daysSelected[View_Base::WEDNESDAY] = $this->_isDaySelected(View_Base::WEDNESDAY);
                $this->m_daysSelected[View_Base::THURSDAY]  = $this->_isDaySelected(View_Base::THURSDAY);
                $this->m_daysSelected[View_Base::FRIDAY]    = $this->_isDaySelected(View_Base::FRIDAY);
                $this->m_daysSelected[View_Base::SATURDAY]  = $this->_isDaySelected(View_Base::SATURDAY);
                $this->m_daysSelected[View_Base::SUNDAY]    = $this->_isDaySelected(View_Base::SUNDAY);

                // Verify that at least one days was selected
                if (!$this->m_daysSelected[View_Base::MONDAY]
                    and !$this->m_daysSelected[View_Base::TUESDAY]
                    and !$this->m_daysSelected[View_Base::WEDNESDAY]
                    and !$this->m_daysSelected[View_Base::THURSDAY]
                    and !$this->m_daysSelected[View_Base::FRIDAY]
                    and !$this->m_daysSelected[View_Base::SATURDAY]
                    and !$this->m_daysSelected[View_Base::SUNDAY]) {

                    $this->setErrorString('Error: At least one day must be selected');
                }

                foreach ($this->m_daysSelected as $day=>$selected) {
                    $this->m_daysSelectedString .= $selected ? '1' : '0';
                }
            }

            if ($this->m_operation == View_Base::UPDATE) {
                $this->m_crossPoolUpdates   = $this->getPostAttributeArray(View_Base::CROSS_POOL_UPDATE_DATA);
                $this->m_teamPoolUpdates    = $this->getPostAttributeArray(View_Base::TEAM_POOL_UPDATE_DATA);
                $this->m_flightUpdates      = $this->getPostAttributeArray(View_Base::FLIGHT_UPDATE_DATA);
            }

            if ($this->m_operation == View_Base::UPDATE
                or $this->m_operation == View_Base::POPULATE
                or $this->m_operation == View_Base::PUBLISH
                or $this->m_operation == View_Base::UN_PUBLISH
                or $this->m_operation == View_Base::CLEAR
                or $this->m_operation == View_Base::DELETE
                or $this->m_operation == View_Base::MOVE
                or $this->m_operation == View_Base::SWAP
                or $this->m_operation == View_Base::TOGGLE
                or $this->m_operation == View_Base::ALTER
                or $this->m_operation == View_Base::CREATE_FLIGHT
                or $this->m_operation == View_Base::CREATE_POOL
                or $this->m_operation == View_Base::DELETE_FLIGHT
                or $this->m_operation == View_Base::DELETE_POOL
                or $this->m_operation == View_Base::DELETE_GAME
            ) {
                $this->m_scheduleId = $this->getPostAttribute(
                    View_Base::SCHEDULE_ID,
                    '* schedule required',
                    true,
                    true
                );
            }

            if ($this->m_operation == View_Base::FAMILY_VIEW
                or $this->m_operation == View_Base::FAMILY_FIX) {

                $this->m_familyId = $this->getPostAttribute(
                    View_Base::FAMILY_ID,
                    null,
                    true,
                    true);
            }

            if ($this->m_operation == View_Base::MOVE
                or $this->m_operation == View_Base::ALTER) {

                $this->m_moveFieldId = $this->getPostAttribute(
                    View_Base::FIELD_ID,
                    null,
                    true,
                    true);
            }

            if ($this->m_operation == View_Base::MOVE) {
                $this->m_moveGameId = $this->getPostAttribute(
                    View_Base::GAME_ID,
                    null,
                    true,
                    true);

                $this->m_moveGameTime = $this->getPostAttribute(
                    View_Base::GAME_TIME,
                    null,
                    true);
            }

            if ($this->m_operation == View_Base::SWAP) {
                $this->m_primaryGameId = $this->getPostAttribute(
                    View_Base::GAME_ID1,
                    null,
                    true,
                    true);

                $this->m_secondaryGameId = $this->getPostAttribute(
                    View_Base::GAME_ID2,
                    null,
                    true,
                    true);
            }

            if ($this->m_operation == View_Base::TOGGLE) {
                $this->m_lockToggleGameId = $this->getPostAttribute(
                    View_Base::GAME_ID,
                    null,
                    true,
                    true);
            }

            if ($this->m_operation == View_Base::DELETE_GAME) {
                $this->m_deleteGameId = $this->getPostAttribute(
                    View_Base::GAME_ID,
                    null,
                    true,
                    true);
            }

            if ($this->m_operation == View_Base::ALTER) {
                $this->m_teamId = $this->getPostAttribute(
                    View_Base::TEAM_ID,
                    null,
                    true,
                    true);

                $this->m_fieldId = $this->getPostAttribute(
                    View_Base::FIELD_ID,
                    null,
                    true,
                    true);
            }

            if ($this->m_operation == View_Base::CREATE_FLIGHT) {
                $this->m_flightName = $this->getPostAttribute(
                    View_Base::FLIGHT_NAME,
                    '', true, false, 'Error: Flight Name is a Required Field'
                );
            }

            if ($this->m_operation == View_Base::DELETE_FLIGHT
                or $this->m_operation == View_Base::CREATE_POOL) {
                $this->m_flightId = $this->getPostAttribute(
                    View_Base::FLIGHT_ID,
                    '', true, true, 'Error: Flight Id is a Required Field'
                );
            }

            if ($this->m_operation == View_Base::CREATE_POOL) {
                $this->m_poolName = $this->getPostAttribute(
                    View_Base::POOL_NAME,
                    '', true, false, 'Error: Pool Name is a Required Field'
                );
            }

            if ($this->m_operation == View_Base::DELETE_POOL) {
                $this->m_poolId = $this->getPostAttribute(
                    View_Base::POOL_ID,
                    '', true, true, 'Error: Pool Id is a Required Field'
                );
            }

            if ($this->m_operation == View_Base::ADD) {
                $this->m_homeTeamId = $this->getPostAttribute(
                    View_Base::HOME_TEAM_ID,
                    null,
                    true,
                    true);

                $this->m_visitingTeamId = $this->getPostAttribute(
                    View_Base::VISITING_TEAM_ID,
                    null,
                    true,
                    true);

                $this->m_gameTimeId = $this->getPostAttribute(
                    View_Base::GAME_TIME,
                    null,
                    true,
                    true);

                $this->m_actualStartTime = $this->getPostAttribute(
                    View_Base::ACTUAL_START_TIME,
                    '',
                    false,
                    false);

                $this->m_scheduleId = $this->getPostAttribute(
                    View_Base::SCHEDULE_ID,
                    null,
                    true,
                    true);
            }


            $this->m_showPublishedSchedules = $this->getPostCheckboxAttribute(View_Base::SHOW_PUBLISHED, false);
        }
    }

    /**
     * @brief On GET, render the page to administer seasons
     *        On POST, complete the transaction and then render the page (with error message if any)
     */
    public function process() {
        try {
            $this->m_divisions = [];

            if ($this->m_missingAttributes == 0) {
                switch ($this->m_operation) {
                    case View_Base::CREATE:
                        $this->_createSchedule();
                        break;

                    case View_Base::POPULATE:
                        $this->_populateSchedule();
                        break;

                    case View_Base::UPDATE:
                        $this->_updateSchedule();
                        break;

                    case View_Base::CLEAR:
                        $this->_clearSchedule();
                        break;

                    case View_Base::PUBLISH:
                        $this->_publishSchedule();
                        break;

                    case View_Base::UN_PUBLISH:
                        $this->_unPublishSchedule();
                        break;

                    case View_Base::DELETE:
                        $this->_deleteSchedule();
                        break;

                    case View_Base::VIEW:
                        break;

                    case View_Base::FAMILY_FIX:
                        $this->_fixSchedule();
                        break;

                    case View_Base::CREATE_FLIGHT:
                        $this->_createFlight();
                        break;

                    case View_Base::DELETE_FLIGHT:
                        $this->_deleteFlight();
                        break;

                    case View_Base::CREATE_POOL:
                        $this->_createPool();
                        break;

                    case View_Base::DELETE_POOL:
                        $this->_deletePool();
                        break;

                    case View_Base::MOVE:
                        $this->_moveGame();
                        break;

                    case View_Base::SWAP:
                        $this->_swapGame();
                        break;

                    case View_Base::TOGGLE:
                        $this->_lockToggleGame();
                        break;

                    case View_Base::ALTER:
                        $this->_alterTeamGames();
                        break;

                    case View_Base::ADD:
                        $this->_addGame();
                        break;

                    case View_Base::DELETE_GAME:
                        $this->_deleteGame();
                        break;
                }
            }
        } catch (\Exception $e) {
            $this->m_errorString = "Operation failed: " . $e->getMessage();
        }

        if ($this->m_isAuthenticated) {
            $this->m_divisions = $this->_getDivisionsFromNames($this->m_divisionNames);
            $view              = new View_AdminSchedules_Schedule($this);
        } else {
            $view = new View_AdminSchedules_Home($this);
        }

        $view->displayPage();
    }

    /**
     * @brief Create Schedule
     */
    private function _createSchedule()
    {
        $divisionNameWithGender = 'Unknown';
        try {
            $this->m_messageString = "Schedule(s)";

            $this->m_divisions = $this->_getDivisionsFromNames($this->m_divisionNames);
            foreach ($this->m_divisions as $division) {
                // Create Schedule
                $divisionNameWithGender = $division->name . " " . $division->gender;
                $schedule               = $this->createScheduleForDivision($division);
                $this->m_messageString .= " '$divisionNameWithGender $schedule->name'";
            }
            $this->m_messageString .= " created.";
        } catch (DuplicateEntryException $e) {
            $this->m_messageString = '';
            $this->m_errorString = "ERROR: Schedule already exists for $divisionNameWithGender with name $this->m_name.  You need to Delete it first before creating a new one.  Or, scroll down and edit the exiting schedule: " . $e->getMessage();
        } catch (UnpublishedScheduleException $e) {
            $this->m_messageString = '';
            $this->m_errorString = "ERROR: Schedule creation failed for $divisionNameWithGender.  Reason: " . $e->getMessage();
        } catch (ScheduleOverlapException $e) {
            $this->m_messageString  = '';
            $this->m_errorString    = "ERROR: Schedule creation failed for $divisionNameWithGender.  Reason: " . $e->getMessage();
        } catch (NotEnoughGameTimesException $e) {
            $this->m_messageString  = '';
            $this->m_errorString    = "ERROR: Schedule creation failed for $divisionNameWithGender.  Reason: " . $e->getMessage();
        } catch (DAGException $e) {
            $this->m_messageString  = '';
            $this->m_errorString    = "ERROR: Schedule creation failed for $divisionNameWithGender.  Reason: " . $e->asString();
        } catch (DAG_Exception $e) {
            $this->m_messageString  = '';
            $this->m_errorString    = "ERROR: Schedule creation failed for $divisionNameWithGender.  Reason: " . $e->asString();
        }
    }

    /**
     * Populate schedule with games
     *
     * @throws NotEnoughGameTimesException
     */
    private function _populateSchedule()
    {
        $divisionNameWithGender = 'Unknown';
        try {
            $schedule                   = Schedule::lookupById($this->m_scheduleId);
            $divisionNameWithGender     = $schedule->division->name . " " . $schedule->division->gender;
            $this->m_divisionNames[]    = $divisionNameWithGender;

            // Verify games have not already been populated
            if ($this->gamesExistForSchedule($schedule)) {
                $this->m_messageString  = '';
                $this->m_errorString    = "Schedule population failed for $divisionNameWithGender.  Reason: games already exist.  You must clear out existing games before populating games.";
                return;
            }

            // Verify there are enough open time slots for number of games
            $divisionFields = DivisionField::lookupByDivision($schedule->division);
            $teams          = Team::lookupByDivision($schedule->division);
            $gameTimes      = [];

            foreach ($divisionFields as $divisionField) {
                $gameTimes = array_merge($gameTimes, GameTime::lookupByField($divisionField->field, true));
            }

            if ((count($teams) * $this->m_gamesPerTeam) > count($gameTimes)) {
                throw new NotEnoughGameTimesException(count($teams), $this->m_gamesPerTeam, count($gameTimes));
            }

            $schedule->populateGames();
            $this->m_messageString = "Games populated for '$divisionNameWithGender $schedule->name'";
        } catch (NotEnoughGameTimesException $e) {
            $this->m_messageString  = '';
            $this->m_errorString    = "Schedule population failed for $divisionNameWithGender.  Reason: " . $e->getMessage();
        } catch (DAGException $e) {
            $this->m_messageString  = '';
            $this->m_errorString    = "Schedule population failed for $divisionNameWithGender.  Reason: " . $e->asString();
        } catch (DAG_Exception $e) {
            $this->m_messageString  = '';
            $this->m_errorString    = "Schedule population failed for $divisionNameWithGender.  Reason: " . $e->asString();
        }
    }

    /**
     * @param $schedule
     * @return bool     True if games already exist; false otherwise
     */
    private function gamesExistForSchedule($schedule)
    {
        $pools = Pool::lookupBySchedule($schedule);
        foreach ($pools as $pool) {
            $games = Game::lookupByPool($pool);
            if (count($games) > 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Create schedule and pools for specified division.
     *
     * @param Division $division
     *
     * @return Schedule
     *
     * @throws NotEnoughGameTimesException
     * @throws UnpublishedScheduleException
     */
    private function createScheduleForDivision($division)
    {
        // Verify no overlapping schedule already exists
        $schedules = Schedule::lookupByDivision($division);
        foreach ($schedules as $schedule) {
            if (($this->m_startDate <= $schedule->startDate and $this->m_endDate > $schedule->startDate)
                or ($this->m_startDate > $schedule->startDate and $this->m_startDate <= $schedule->endDate)) {
                throw new ScheduleOverlapException($this->m_startDate, $this->m_endDate, $schedule);
            }
        }

        // Verify there are enough open time slots for number of games
        $divisionFields = DivisionField::lookupByDivision($division);
        $teams          = Team::lookupByDivision($division);
        $gameTimes      = [];

        foreach ($divisionFields as $divisionField) {
            $gameTimes = array_merge($gameTimes, GameTime::lookupByField($divisionField->field, true));
        }

        if ((round(count($teams) / 2) * $this->m_gamesPerTeam) > count($gameTimes)) {
            throw new NotEnoughGameTimesException(count($teams), $this->m_gamesPerTeam, count($gameTimes));
        }

        // Create schedule
        $schedule = Schedule::create(
            $division,
            $this->m_name,
            $this->m_scheduleType,
            $this->m_gamesPerTeam,
            $this->m_startDate,
            $this->m_endDate,
            $this->m_startTime,
            $this->m_endTime,
            $this->m_daysSelectedString);

        // Lookup schedule to get normalized time stamps for game population
        $schedule = Schedule::lookupById($schedule->id);

        // Populate pools
        $schedule->populatePools();

        // Populating games
        $schedule->populateGames();

        return $schedule;
    }

    /**
     * @brief Update Schedule
     */
    private function _updateSchedule() {
        // Get Data
        $schedule                   = Schedule::lookupById($this->m_scheduleId);
        $divisionNameWithGender     = $schedule->division->name . " " . $schedule->division->gender;
        $this->m_divisionNames[]    = $divisionNameWithGender;

        // Update only allowed if games have not yet been populated
        if ($this->gamesExistForSchedule($schedule)) {
            $this->m_messageString  = '';
            $this->m_errorString    = "Schedule update failed for $divisionNameWithGender.  Reason: games already exist.  You must clear out existing games before updating schedule data.";
            return;
        }


        // Perform updates
        $schedule->name         = $this->m_name;
        $schedule->gamesPerTeam = $this->m_gamesPerTeam;
        $schedule->startDate    = $this->m_startDate;
        $schedule->endDate      = $this->m_endDate;
        $schedule->startTime    = $this->m_startTime;
        $schedule->endTime      = $this->m_endTime;
        $schedule->daysOfWeek   = $this->m_daysSelectedString;

        foreach ($this->m_crossPoolUpdates as $poolId => $crossPoolId) {
            $pool       = Pool::lookupById($poolId);
            $crossPool  = Pool::lookupById($crossPoolId);

            if ($pool->id == $crossPool->id) {
                $pool->gamesAgainstPool = null;
            } else {
                $pool->gamesAgainstPool = $crossPool;
            }
        }

        foreach ($this->m_teamPoolUpdates as $teamId => $data) {
            $team = Team::lookupById($teamId);
            $pool = Pool::lookupById($data[View_Base::POOL_ID]);
            $team->pool = $pool;
        }

        foreach ($this->m_flightUpdates as $flightId => $data) {
            $flight = Flight::lookupById($flightId);
            $flight->name                       = $data[View_Base::NAME];
            $flight->include5th6thGame          = isset($data[View_Base::INCLUDE_5TH_6TH_GAME]) ? 1 : 0;
            $flight->include3rd4thGame          = isset($data[View_Base::INCLUDE_3RD_4TH_GAME]) ? 1 : 0;
            $flight->includeSemiFinalGames      = isset($data[View_Base::INCLUDE_SEMI_FINAL_GAMES]) ? 1 : 0;
            $flight->includeChampionshipGame    = isset($data[View_Base::INCLUDE_CHAMPIONSHIP_GAME]) ? 1 : 0;
            $flight->scheduleGames              = isset($data[View_Base::FLIGHT_SCHEDULE_GAMES]) ? 1 : 0;
        }

        $this->m_messageString  = "Schedule '$divisionNameWithGender: $schedule->name' updated.";
    }

    /**
     * @brief Clear Schedule
     */
    private function _clearSchedule() {
        $schedule                   = Schedule::lookupById($this->m_scheduleId);
        $schedule->published        = 0;
        $divisionNameWithGender     = $schedule->division->name . " " . $schedule->division->gender;
        $this->m_divisionNames[]    = $divisionNameWithGender;
        $flights                    = Flight::lookupBySchedule($schedule);
        foreach ($flights as $flight) {
            $games = Game::lookupByFlight($flight);
            foreach ($games as $game) {
                $game->delete();
            }
        }

        $this->m_messageString = "Schedule '$divisionNameWithGender: $schedule->name' cleared.";
    }

    /**
     * @brief Publish Schedule
     */
    private function _publishSchedule() {
        $schedule                   = Schedule::lookupById($this->m_scheduleId);
        $divisionNameWithGender     = $schedule->division->name . " " . $schedule->division->gender;
        $this->m_divisionNames[]    = $divisionNameWithGender;

        // Publish only allowed if games have been populated
        if (!$this->gamesExistForSchedule($schedule)) {
            $this->m_messageString  = '';
            $this->m_errorString    = "Schedule publish failed for $divisionNameWithGender.  Reason: No games exist.  You must populate games before you can publish.";
            return;
        }

        $schedule->published        = 1;

        $this->m_messageString = "Schedule '$divisionNameWithGender: $schedule->name' published for all to see.";
    }

    /**
     * @brief UnPublish Schedule
     */
    private function _unPublishSchedule() {
        $schedule                   = Schedule::lookupById($this->m_scheduleId);
        $divisionNameWithGender     = $schedule->division->name . " " . $schedule->division->gender;
        $this->m_divisionNames[]    = $divisionNameWithGender;

        // UnPublish schedule
        $schedule->published = 0;

        $this->m_messageString = "Schedule '$divisionNameWithGender: $schedule->name' un-published so you can make changes.";
    }

    /**
     * Attempt to reschedule overlapping games for selected family(s)
     */
    private function _fixSchedule()
    {
        if ($this->m_familyId == 0) {
            $families = Family::lookupBySeason($this->m_season);
        } else {
            $families[] = Family::lookupById($this->m_familyId);
        }

        foreach ($families as $family) {
            // Attempt two passes to fix games
            FamilyGame::fixOverlaps($family);
            FamilyGame::fixOverlaps($family);
        }

        $this->m_messageString = "Best attempt made to fix Family Schedule.  Some overlaps may remain.";
    }

    /**
     * Create a new flight
     */
    private function _createFlight()
    {
        $schedule                   = Schedule::lookupById((int)$this->m_scheduleId);
        $divisionNameWithGender     = $schedule->division->name . " " . $schedule->division->gender;
        $this->m_divisionNames[]    = $divisionNameWithGender;
        $flight                     = Flight::create($schedule, $this->m_flightName, 0, 0, 0, 0);

        $this->m_messageString  = "Flight $flight->name successfully created";
    }

    /**
     * Create a new pool
     */
    private function _createPool()
    {
        $schedule                   = Schedule::lookupById((int)$this->m_scheduleId);
        $divisionNameWithGender     = $schedule->division->name . " " . $schedule->division->gender;
        $this->m_divisionNames[]    = $divisionNameWithGender;
        $flight                     = Flight::lookupById((int)$this->m_flightId);
        $pool                       = Pool::create($flight, $schedule, $this->m_poolName);

        $this->m_messageString  = "Pool $pool->fullName successfully created";
    }

    /**
     * Delete a flight
     */
    private function _deleteFlight()
    {
        $schedule                   = Schedule::lookupById((int)$this->m_scheduleId);
        $divisionNameWithGender     = $schedule->division->name . " " . $schedule->division->gender;
        $this->m_divisionNames[]    = $divisionNameWithGender;
        $flight                     = Flight::lookupById((int)$this->m_flightId);

        // Error if flight has games
        $games = Game::lookupByFlight($flight);
        if (count($games) > 0) {
            $this->m_errorString = "Cannot delete flight because it has assigned games.  Try 'Clear' command first.";
            return;
        }

        $flight->delete();

        $this->m_messageString  = "Flight $this->m_flightName successfully deleted";
    }

    /**
     * Delete a pool
     */
    private function _deletePool()
    {
        $schedule                   = Schedule::lookupById((int)$this->m_scheduleId);
        $divisionNameWithGender     = $schedule->division->name . " " . $schedule->division->gender;
        $this->m_divisionNames[]    = $divisionNameWithGender;
        $pool                       = Pool::lookupById((int)$this->m_poolId);

        // Error if pool has teams
        $teams = Team::lookupByPool($pool);
        if (count($teams) > 0) {
            $this->m_errorString = "Cannot delete pool because it has assigned teams.  Move teams to other pools first.";
            return;
        }

        $pool->delete();

        $this->m_messageString  = "Pool $pool->fullName successfully deleted";
    }

    /**
     * Attempt to move a game to new field and time
     */
    private function _moveGame()
    {
        $game                       = Game::lookupById($this->m_moveGameId);
        $field                      = Field::lookupById($this->m_moveFieldId);
        $gameTimes                  = GameTime::lookupByGameDateAndField($game->gameTime->gameDate, $field);
        $schedule                   = $game->flight->schedule;
        $divisionNameWithGender     = $schedule->division->name . " " . $schedule->division->gender;
        $this->m_divisionNames[]    = $divisionNameWithGender;

        $this->moveGame($game, $gameTimes, $this->m_moveGameTime);
    }

    /**
     * Attempt to move a game to new field and time
     *
     * @param Game          $game
     * @param GameTime[]    $gameTimes
     * @param string        $newGameTime - Find a gameTime in $gamesTimes that matches if this is set
     *
     * @return bool         true if game moved, false otherwise
     */
    private function moveGame($game, $gameTimes, $newGameTime = null)
    {
        $this->m_errorString = "Error: Game Time: $this->m_moveGameTime not found for field";
        foreach ($gameTimes as $gameTime) {
            if (isset($newGameTime) and $gameTime->startTime != $newGameTime) {
                continue;
            }

            // Verify the game is not locked
            if ($game->isLocked()) {
                $this->m_errorString = "Error: Cannot move game because it is locked.  Please unlock the game first.";
                return false;
            }

            // Verify the schedule is not already published
            if ($game->pool->schedule->published == 1) {
                $this->m_errorString = "Error: Cannot move game because schedule has been published.";
                return false;
            }

            // Verify no game already scheduled at this time
            if (isset($gameTime->game)) {
                $this->m_errorString = "Error: Cannot move game to $gameTime->startTime because a game is already scheduled at this time.";
                return false;
            }

            // Move game
            $game->move($gameTime);
            $this->m_messageString  = "Game $this->m_moveGameId successfully moved to new field/time";
            $this->m_errorString    = "";
            return true;
        }

        return false;
    }

    /**
     * Check to see if a division is supported by the field
     *
     * @param Division  $division
     * @param Field     $field
     * @return bool     true if division supported by field; false otherwise
     */
    private function isDivisionSupportedByField($division, $field)
    {
        $divisionFields = DivisionField::lookupByField($field);

        foreach ($divisionFields as $divisionField) {
            if ($divisionField->division->id == $division->id) {
                return true;
            }
        }

        return false;
    }

    /**
     * Attempt to swap two games
     */
    private function _swapGame()
    {
        $primaryGame                = Game::lookupById($this->m_primaryGameId);
        $secondaryGame              = Game::lookupById($this->m_secondaryGameId);
        $schedule                   = $primaryGame->flight->schedule;
        $divisionNameWithGender     = $schedule->division->name . " " . $schedule->division->gender;
        $this->m_divisionNames[]    = $divisionNameWithGender;

        if ($this->swapGame($primaryGame, $secondaryGame)) {
            $this->m_messageString  = "Primary gameId: $this->m_primaryGameId swapped with secondary gameId: $this->m_secondaryGameId";
        }
    }

    /**
     * Attempt to swap two games
     *
     * @param Game  $primaryGame
     * @param Game  $secondaryGame
     *
     * @return bool true if games swapped; false end error string set otherwise
     */
    private function swapGame($primaryGame, $secondaryGame)
    {
        // Verify not the same gameId
        if ($primaryGame->id == $secondaryGame->id) {
            $this->m_errorString = "Error: Trying to move gameId: $secondaryGame->id to gameId: $primaryGame->id, gameIds are identical.";
            return false;
        }

        // Verify the primary game has not been published
        if ($primaryGame->pool->schedule->published == 1) {
            $this->m_errorString = "Error: Not allowed to move a published gameId: $primaryGame->id";
            return false;
        }

        // Verify the secondary game has not been published
        if ($secondaryGame->pool->schedule->published == 1) {
            $this->m_errorString = "Error: Not allowed to move a published gameId: $secondaryGame->id";
            return false;
        }

        // Verify the primary game has not been locked
        if ($primaryGame->isLocked()) {
            $this->m_errorString = "Error: Not allowed to move a locked gameId: $primaryGame->id";
            return false;
        }

        // Verify the secondary game has not been locked
        if ($secondaryGame->isLocked()) {
            $this->m_errorString = "Error: Not allowed to move a locked gameId: $secondaryGame->id";
            return false;
        }

        // Verify secondary game can be played on primary game's field
        $secondaryDivision = $secondaryGame->flight->schedule->division;
        if (!$this->isDivisionSupportedByField($secondaryDivision, $primaryGame->gameTime->field)) {
            $this->m_errorString = "Error: Trying to move secondaryGameId: $secondaryGame->id, to a field that does not support $secondaryDivision->name";
            return false;
        }

        // Verify primary game can be played on secondary game's field
        $primaryDivision = $primaryGame->flight->schedule->division;
        if (!$this->isDivisionSupportedByField($primaryDivision, $secondaryGame->gameTime->field)) {
            $this->m_errorString = "Error: Trying to move primaryGameId: $primaryGame->id, to a field that does not support $primaryDivision->name";
            return false;
        }

        // Swap the games
        $primaryGameTime            = $primaryGame->gameTime;
        $secondaryGameTime          = $secondaryGame->gameTime;
        $secondaryGame->gameTime    = $primaryGameTime;
        $primaryGame->gameTime      = $secondaryGameTime;

        $primaryGameTime->game      = null;
        $secondaryGameTime->game    = null;
        $primaryGameTime->game      = $secondaryGame;
        $secondaryGameTime->game    = $primaryGame;

        return true;
    }

    /**
     * Toggle the lock setting for specified game
     */
    private function _lockToggleGame()
    {
        $game                       = Game::lookupById($this->m_lockToggleGameId);
        $schedule                   = $game->pool->schedule;
        $divisionNameWithGender     = $schedule->division->name . " " . $schedule->division->gender;
        $this->m_divisionNames[]    = $divisionNameWithGender;

        // Toggle the game's lock setting
        $operation = 'locked';
        if ($game->isLocked()) {
            $game->locked = 0;
            $operation = 'unlocked';
        } else {
            $game->locked = 1;
        }

        $this->m_messageString  = "GameId: $this->m_lockToggleGameId $operation";
    }

    /**
     * Alter team games for specified date range and time range.
     * Lock the games for this team during the date range
     */
    private function _alterTeamGames()
    {
        $team                       = Team::lookupById($this->m_teamId);
        $schedule                   = $team->pool->schedule;
        $division                   = $schedule->division;
        $divisionNameWithGender     = $schedule->division->name . " " . $schedule->division->gender;
        $this->m_divisionNames[]    = $divisionNameWithGender;
        $this->m_startTime          = $this->getNormalizedTime($this->m_startTime);
        $this->m_endTime            = $this->getNormalizedTime($this->m_endTime);
        $gamesMoved                 = 0;
        $gamesNotMoved              = 0;

        // Verify schedule has not been published
        if ($schedule->published == 1) {
            $this->m_errorString = "ERROR: Schedule for team has been published. You must un-publish before you can change the schedule";
            return;
        }

        // Get Game Dates that need to be modified
        $allGameDates = GameDate::lookupBySeason($this->m_season, GameDate::ALL_DAYS, $schedule);
        $gameDates = [];
        foreach ($allGameDates as $gameDate) {
            if ($gameDate->day >= $this->m_startDate
                and $gameDate->day <= $this->m_endDate) {
                $gameDates[] = $gameDate;
            }
        }

        // Get team's games that need to be moved.  A game needs to be moved if any of the following are true:
        //      - Game falls in day range and
        //          - Game falls outside of desired time range
        //          - or Game is not being played on desired field
        $teamGamesByDay = [];
        $games = Game::lookupByTeam($team);
        foreach ($games as $game) {
            if ($game->gameTime->gameDate->day >= $this->m_startDate
                and $game->gameTime->gameDate->day <= $this->m_endDate) {

                if ($game->gameTime->startTime < $this->m_startTime
                    or $game->gameTime->startTime > $this->m_endTime
                    or ($this->m_fieldId != 0 and $game->gameTime->field->id != $this->m_fieldId))
                $teamGamesByDay[$game->gameTime->gameDate->day][] = $game;
            }
        }

        // Get the list of supported fields for the team's division
        $divisionFields = DivisionField::lookupByDivision($division);
        $fieldsById     = [];
        foreach ($divisionFields as $divisionField) {
            $fieldsById[$divisionField->field->id] = $divisionField->field;
        }

        // For each game date, update team games to use a time-slot/field that is desired
        // and then lock the game.
        foreach ($gameDates as $gameDate) {
            // Get acceptable gameTimes
            $allGameTimes   = GameTime::lookupByGameDate($gameDate);
            $gameTimes      = [];
            foreach ($allGameTimes as $gameTime) {
                if ($gameTime->startTime >= $this->m_startTime
                    and $gameTime->startTime <= $this->m_endTime) {

                    if (($this->m_fieldId == 0 and isset($fieldsById[$gameTime->field->id]))
                        or ($this->m_fieldId == $gameTime->field->id)) {
                        $gameTimes[] = $gameTime;
                    }
                }
            }

            $games = isset($teamGamesByDay[$gameDate->day]) ? $teamGamesByDay[$gameDate->day] : [];
            foreach ($games as $gameToMove) {

                // Find an available gameTime and "move" the game
                $gameMoved = false;
                foreach ($gameTimes as $gameTime) {
                    if (!isset($gameTime->game)) {
                        $gameToMove->locked = 0;
                        $gameMoved = $this->moveGame($gameToMove, [$gameTime]);
                        Assertion::isTrue($gameMoved, 'Uh-oh, see Dave to help figure this one out');
                    }
                }

                // Find a game that is not locked and schedule is not published and swap the game
                if (!$gameMoved) {
                    foreach ($gameTimes as $gameTime) {
                        if (isset($gameTime->game)
                            and $gameTime->game->locked == 0
                            and $gameTime->game->flight->schedule->published == 0) {

                            $gameToMove->locked = 0;
                            $result = $this->swapGame($gameToMove, $gameTime->game);
                            Assertion::isTrue($result, "Dave needs to look into this");
                            $gameMoved = true;
                            break;
                        }
                    }
                }

                // Update counters
                if ($gameMoved) {
                    $gamesMoved += 1;
                    $gameToMove->locked = 1;
                } else {
                    $gamesNotMoved += 1;
                }
            }
        }

        $coach                  = Coach::lookupByTeam($team);
        $teamName               = $team->name . " - " . $coach->shortName;
        $totalGames             = $gamesMoved + $gamesNotMoved;
        $this->m_errorString    = '';
        $this->m_messageString  = "Team ${teamName}: $gamesMoved of $totalGames have been altered.";
    }

    /**
     * Add a game
     */
    private function _addGame()
    {
        $homeTeam                   = Team::lookupById((int)$this->m_homeTeamId);
        $visitingTeam               = Team::lookupById((int)$this->m_visitingTeamId);
        $schedule                   = $homeTeam->pool->schedule;
        $gameTime                   = GameTime::lookupById((int)$this->m_gameTimeId);
        $pool                       = $homeTeam->pool;
        $flight                     = $pool->flight;
        $divisionNameWithGender     = $schedule->division->name . " " . $schedule->division->gender;
        $this->m_divisionNames[]    = $divisionNameWithGender;

        // Verify the schedule is not already published
        if ($schedule->published == 1) {
            $this->m_errorString = "Error: Cannot add game because schedule has been published.";
            return;
        }

        // Verify no game already scheduled at this time
        if (isset($gameTime->game)) {
            $this->m_errorString = "Error: Cannot add game to $gameTime->startTime because a game is already scheduled at this time.";
            return;
        }

        // Verify both teams are in the same flight and pool
        if ($homeTeam->pool->id != $visitingTeam->pool->id) {
            $this->m_errorString = "Error: Support not implemented yet to allow cross-pool games.";
            return;
        }

        // Create game
        $game = Game::create(
            $flight,
            $pool,
            $gameTime,
            $homeTeam,
            $visitingTeam);
        
        // Update actual game time if requested
        if (!empty($this->m_actualStartTime)) {
            $gameTime->actualStartTime = $this->m_actualStartTime;
        }

        $coach                  = Coach::lookupByTeam($homeTeam);
        $homeTeamName           = $homeTeam->name . " - " . $coach->shortName;
        $coach                  = Coach::lookupByTeam($visitingTeam);
        $visitingTeamName       = $visitingTeam->name . " - " . $coach->shortName;
        $this->m_errorString    = '';
        $this->m_messageString  = "Game $game->id added between ${homeTeamName} and ${visitingTeamName}.";
    }

    /**
     * Delete specified game
     */
    private function _deleteGame()
    {
        $game                       = Game::lookupById((int)$this->m_deleteGameId);
        $schedule                   = Schedule::lookupById((int)$this->m_scheduleId);
        $divisionNameWithGender     = $schedule->division->name . " " . $schedule->division->gender;
        $this->m_divisionNames[]    = $divisionNameWithGender;

        // Do not allow if schedule has been published
        if ($schedule->id != $game->pool->schedule->id) {
            $this->m_errorString = "ERROR: Cannot delete game $this->m_deleteGameId because it is not in the $divisionNameWithGender division.";
            return;
        }

        // Do not allow if schedule id's do not match
        if ($schedule->published == 1) {
            $this->m_errorString = "ERROR: Cannot delete game $this->m_deleteGameId because schedule is published.";
            return;
        }

        // Do not allow if game is locked
        if ($game->isLocked()) {
            $this->m_errorString = "ERROR: Cannot delete game $this->m_deleteGameId because the game is locked.";
            return;
        }

        // Delete the game
        $game->delete();

        $this->m_messageString  = "GameId: $this->m_deleteGameId deleted for $divisionNameWithGender";
    }

    /**
     * Return a normalized time string of the form: HH:MM:SS
     *
     * @param string $time
     *
     * @return string
     */
    private function getNormalizedTime($time)
    {
        $time = trim($time);
        if (strlen($time) == 4) {
            $time = "0" . $time;
        }

        if (strlen($time) == 5) {
            $time = $time . ":00";
        }

        Assertion::isTrue(strlen($time) == 8, "Invalid time: $time");

        return $time;
    }

    /**
     * @brief Delete Schedule
     */
    private function _deleteSchedule() {
        $schedule                   = Schedule::lookupById($this->m_scheduleId);
        $divisionNameWithGender     = $schedule->division->name . " " . $schedule->division->gender;
        $schedule->delete();

        $this->m_messageString = "Schedule '$divisionNameWithGender: $schedule->name' deleted.";
    }
}