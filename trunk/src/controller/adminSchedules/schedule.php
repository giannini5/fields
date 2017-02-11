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

class NotEnoughGameTimesException extends DAG_Exception
{
    public function __construct($numberOfTeams, $gamesPerTeam, $numberOfGameTimes)
    {
        $gamesNeeded = $numberOfTeams * $gamesPerTeam;
        parent::__construct("Need $gamesNeeded available game times.  Only $numberOfGameTimes available.");
    }
}

/**
 * Class Controller_AdminSchedules_Schedule
 *
 * @brief Select a field to administer or create a new schedule
 */
class Controller_AdminSchedules_Schedule extends Controller_AdminSchedules_Base {

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

    public function __construct() {
        parent::__construct();

        if (isset($_SERVER['REQUEST_METHOD']) and $_SERVER['REQUEST_METHOD'] == 'POST') {
            if ($this->m_operation == View_Base::CREATE or $this->m_operation == View_Base::UPDATE or $this->m_operation == View_Base::DELETE) {
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

            if ($this->m_operation == View_Base::CREATE or $this->m_operation == View_Base::VIEW) {
                $this->m_divisionNames = $this->getPostAttributeArray(
                    View_Base::DIVISION_NAMES
                );
                if (count($this->m_divisionNames) == 0) {
                    $this->setErrorString("Error: Division Name(s) is a Required Field");
                }
            }

            if ($this->m_operation == View_Base::CREATE or $this->m_operation == View_Base::UPDATE) {
                $this->m_gamesPerTeam = $this->getPostAttribute(
                    View_Base::GAMES_PER_TEAM,
                    0,
                    true,
                    true
                );
                if ($this->m_gamesPerTeam == 0) {
                    $this->setErrorString("Error: Games Per Team is a required Field");
                }

                $this->m_startDate  = $this->getPostAttribute(View_Base::START_DATE, null, true, false, "Error: Start Date is a required field");
                $this->m_endDate    = $this->getPostAttribute(View_Base::END_DATE, null, true, false, "Error: End Date is a required field");
                $this->m_startTime  = $this->getPostAttribute(View_Base::START_TIME, null);
                $this->m_endTime    = $this->getPostAttribute(View_Base::END_TIME, null);

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

                // Verify startDate < endDate
                if ($this->m_startDate > $this->m_endDate) {
                    $this->m_missingAttributes += 1;
                    $this->m_errorString = "Error: Start Date ($this->m_startDate) must be less than End Date ($this->m_endDate)";
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
                or $this->m_operation == View_Base::CLEAR
                or $this->m_operation == View_Base::DELETE) {
                $this->m_scheduleId = $this->getPostAttribute(
                    View_Base::SCHEDULE_ID,
                    '* schedule required',
                    true,
                    true
                );
            }

            if ($this->m_operation == View_Base::FAMILY_VIEW or $this->m_operation == View_Base::FAMILY_FIX) {
                $this->m_familyId = $this->getPostAttribute(
                    View_Base::FAMILY_ID,
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

                $this->m_moveFieldId = $this->getPostAttribute(
                    View_Base::FIELD_ID,
                    null,
                    true,
                    true);

                $this->m_moveGameTime = $this->getPostAttribute(
                    View_Base::GAME_TIME,
                    null,
                    true);
            }
        }
    }

    /**
     * @brief On GET, render the page to administer seasons
     *        On POST, complete the transaction and then render the page (with error message if any)
     */
    public function process() {
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

                case View_Base::DELETE:
                    $this->_deleteSchedule();
                    break;

                case View_Base::VIEW:
                    break;

                case View_Base::FAMILY_FIX:
                    $this->_fixSchedule();
                    break;

                case View_Base::MOVE:
                    $this->_moveGame();
                    break;
            }
        }

        if ($this->m_isAuthenticated) {
            $this->m_divisions = $this->_getDivisionsFromNames();
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

            $this->m_divisions = $this->_getDivisionsFromNames();
            foreach ($this->m_divisions as $division) {
                // Create Schedule
                $divisionNameWithGender = $division->name . " " . $division->gender;
                $schedule               = $this->createScheduleForDivision($division);
                $this->m_messageString .= " '$divisionNameWithGender $schedule->name'";
            }
            $this->m_messageString .= " created.";
        } catch (DuplicateEntryException $e) {
            $this->m_messageString  = '';
            $this->m_errorString    = "Schedule already exists for $divisionNameWithGender.  You need to Delete it first before creating a new one.  Or, scroll down and edit the exiting schedule: " . $e->getMessage();
        } catch (NotEnoughGameTimesException $e) {
            $this->m_messageString  = '';
            $this->m_errorString    = "Schedule creation failed for $divisionNameWithGender.  Reason: " . $e->getMessage();
        } catch (DAGException $e) {
            $this->m_messageString  = '';
            $this->m_errorString    = "Schedule creation failed for $divisionNameWithGender.  Reason: " . $e->asString();
        } catch (DAG_Exception $e) {
            $this->m_messageString  = '';
            $this->m_errorString    = "Schedule creation failed for $divisionNameWithGender.  Reason: " . $e->asString();
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
     * @brief get Divisions from division names
     *
     * @return Division[]
     */
    private function _getDivisionsFromNames()
    {
        $divisions = [];
        foreach ($this->m_divisionNames as $divisionNameWithGender) {
            // DivisionName: <name> <gender>
            $divisionNameAttributes = explode(' ', $divisionNameWithGender);
            Assertion::isTrue(2 == count($divisionNameAttributes), "Invalid divisionName: $divisionNameWithGender");

            $divisionName   = $divisionNameAttributes[0];
            $gender         = $divisionNameAttributes[1];
            $divisions[]    = Division::lookupByNameAndGender($this->m_season, $divisionName, $gender);
        }

        return $divisions;
    }

    /**
     * Create schedule and pools for specified division.
     *
     * @param Division $division
     * @return Schedule
     * @throws NotEnoughGameTimesException
     */
    private function createScheduleForDivision($division)
    {
        // Verify there are enough open time slots for number of games
        $divisionFields = DivisionField::lookupByDivision($division);
        $teams          = Team::lookupByDivision($division);
        $gameTimes      = [];

        foreach ($divisionFields as $divisionField) {
            $gameTimes = array_merge($gameTimes, GameTime::lookupByField($divisionField->field, true));
        }

        if ((count($teams) * $this->m_gamesPerTeam) > count($gameTimes)) {
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

        // If only one pool (younger groups) then populate games; otherwise let the administrator muck
        // with the pools before populating the games
        $pools = Pool::lookupBySchedule($schedule);
        if (count($pools) == 1) {
            $schedule->populateGames();
        }

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

        foreach ($this->m_flightUpdates as $flightId => $name) {
            $flight = Flight::lookupById($flightId);
            $flight->name = $name;
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
        $pools                      = Pool::lookupBySchedule($schedule);
        foreach ($pools as $pool) {
            $games = Game::lookupByPool($pool);
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
     * Attempt to move a game to new field and time
     */
    private function _moveGame()
    {
        $game                       = Game::lookupById($this->m_moveGameId);
        $field                      = Field::lookupById($this->m_moveFieldId);
        $gameTimes                  = GameTime::lookupByGameDateAndField($game->gameTime->gameDate, $field);
        $schedule                   = $game->pool->schedule;
        $divisionNameWithGender     = $schedule->division->name . " " . $schedule->division->gender;
        $this->m_divisionNames[]    = $divisionNameWithGender;

        $this->m_errorString = "Error: Game Time: $this->m_moveGameTime not found for field";
        foreach ($gameTimes as $gameTime) {
            if ($gameTime->startTime == $this->m_moveGameTime) {
                // Verify the schedule is not already published
                if ($game->pool->schedule->published == 1) {
                    $this->m_errorString = "Error: Cannot move game because schedule has been published.";
                    break;
                }

                // Verify no game already scheduled at this time
                if (isset($gameTime->game)) {
                    $this->m_errorString = "Error: Cannot move game to $gameTime->startTime because a game is already scheduled at this time.";
                    break;
                }

                // Move game
                $game->move($gameTime);
                $this->m_messageString  = "Game $this->m_moveGameId successfully moved to new field/time";
                $this->m_errorString    = "";
                break;
            }
        }
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