<?php

use \DAG\Domain\Schedule\Division;
use \DAG\Domain\Schedule\Schedule;
use \DAG\Framework\Orm\DuplicateEntryException;
use \DAG\Framework\Exception\Assertion;
use \DAG\Domain\Schedule\GameTime;
use \DAG\Domain\Schedule\DivisionField;
use \DAG\Domain\Schedule\Team;

class NotEnoughGameTimesException extends DAG_Exception
{
    public function __construct($numberOfTeams, $gamesPerTeam, $numberOfGameTimes)
    {
        $gamesNeeded = $numberOfTeams * $gamesPerTeam;
        parent::__construct("Need $gamesNeeded available game times.  Only $numberOfGameTimes available.");
    }
}

/**
 * Class Controller_Schedules_Schedule
 *
 * @brief Select a field to administer or create a new schedule
 */
class Controller_Schedules_Schedule extends Controller_Schedules_Base {

    public $m_name;
    public $m_divisionNames;
    public $m_scheduleId;
    public $m_gamesPerTeam;

    public function __construct() {
        parent::__construct();

        if (isset($_SERVER['REQUEST_METHOD']) and $_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->m_name = $this->getPostAttribute(
                View_Base::NAME,
                '* Name required'
            );

            if ($this->m_operation == View_Base::CREATE) {
                $this->m_divisionNames = $this->getPostAttributeArray(
                    View_Base::DIVISION_NAMES
                );
            }

            if ($this->m_operation == View_Base::CREATE or $this->m_operation == View_Base::UPDATE) {
                $this->m_gamesPerTeam = $this->getPostAttribute(
                    View_Base::GAMES_PER_TEAM,
                    '* gamesPerTeam required',
                    true,
                    true
                );
            }

            if ($this->m_operation == View_Base::UPDATE or $this->m_operation == View_Base::DELETE) {
                $this->m_scheduleId = $this->getPostAttribute(
                    View_Base::SCHEDULE_ID,
                    '* schedule required',
                    true,
                    true
                );
            }
        }
    }

    /**
     * @brief On GET, render the page to administer seasons
     *        On POST, complete the transaction and then render the page (with error message if any)
     */
    public function process() {
        if ($this->m_missingAttributes == 0) {
            switch ($this->m_operation) {
                case View_Base::CREATE:
                    $this->_createSchedule();
                    break;

                case View_Base::UPDATE:
                    $this->_updateSchedule();
                    break;

                case View_Base::DELETE:
                    $this->_deleteSchedule();
                    break;
            }
        }

        if ($this->m_isAuthenticated) {
            $view = new View_Schedules_Schedule($this);
        } else {
            $view = new View_Schedules_Home($this);
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

            foreach ($this->m_divisionNames as $divisionNameWithGender) {
                // DivisionName: <name> <gender>
                $divisionNameAttributes = explode(' ', $divisionNameWithGender);
                Assertion::isTrue(2 == count($divisionNameAttributes), "Invalid divisionName: $divisionNameWithGender");

                $divisionName   = $divisionNameAttributes[0];
                $gender         = $divisionNameAttributes[1];
                $division       = Division::lookupByNameAndGender($this->m_season, $divisionName, $gender);

                // Create Schedule
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
        $schedule = Schedule::create($division, $this->m_name, $this->m_gamesPerTeam);
        $schedule->populatePools();
        $schedule->populateGames();
        return $schedule;
    }

    /**
     * @brief Update Schedule
     */
    private function _updateSchedule() {
        $schedule = Schedule::lookupById($this->m_scheduleId);
        $schedule->name         = $this->m_name;
        $schedule->gamesPerTeam = $this->m_gamesPerTeam;

        $division               = $schedule->division;
        $this->m_messageString  = "Schedule '$division->name $division->gender: $schedule->name' updated.";
    }

    /**
     * @brief Delete Schedule
     */
    private function _deleteSchedule() {
        $schedule = Schedule::lookupById($this->m_scheduleId);
        $division = $schedule->division;
        $schedule->delete();

        $this->m_messageString = "Schedule '$division->name $division->gender: $schedule->name' deleted.";
    }
}