<?php

use \DAG\Domain\Schedule\Facility;
use \DAG\Domain\Schedule\Division;
use \DAG\Domain\Schedule\DivisionField;
use \DAG\Domain\Schedule\Field;
use \DAG\Domain\Schedule\GameTime;
use \DAG\Domain\Schedule\Coach;
use \DAG\Domain\Schedule\GameDate;
use \DAG\Domain\Schedule\Schedule;
use \DAG\Domain\Schedule\Game;
use \DAG\Domain\Schedule\Team;
use \DAG\Domain\Schedule\Family;
use \DAG\Domain\Schedule\FamilyGame;
use \DAG\Framework\Exception\Precondition;
use \DAG\Domain\Schedule\Flight;

/**
 * @brief Show the Preview page and get the user to select a preview to administer or create a new preview.
 */
class View_AdminSchedules_Preview extends View_AdminSchedules_Base {
    /**
     * @brief Construct the View
     *
     * @param Controller_Base $controller - Controller that contains data used when rendering this view.
     */
    public function __construct($controller) {
        parent::__construct(self::SCHEDULE_PREVIEW_PAGE, $controller);
    }

    /**
     * @brief Render data for display on the page.
     */
    public function render()
    {
        $messageString          = $this->m_controller->m_messageString;
        if (!empty($messageString)) {
            print "
                <p style='color: green' align='center'><strong>$messageString</strong></p><br>";
        }

        print "
            <table valign='top' align='center' width='400' border='1' cellpadding='5' cellspacing='0'>
                <tr>
                    <td valign='top' bgcolor='" . View_Base::VIEW_COLOR  . "'>";

        $this->_printViewSchedulesByField();

        print "
                    </td>
                    <td valign='top' bgcolor='" . View_Base::VIEW_COLOR  . "'>";

        $this->_printViewSchedulesByDivision();

        print "
                    </td>
                    <td valign='top' bgcolor='" . View_Base::VIEW_COLOR  . "'>";

        $this->_printViewSchedulesByTeam();

        print "
                    </td>
                    <td valign='top' bgcolor='" . View_Base::VIEW_COLOR  . "'>";

        self::_printViewSchedulesByFamily($this, self::SCHEDULE_PREVIEW_PAGE);
        print "
                    </td>
                </tr>
            </table>
            <br><br>";

        switch ($this->m_controller->m_operation) {
            case View_Base::FIELD_VIEW:
                $this->printSchedulesByFields();
                break;

            case View_Base::DIVISION_VIEW:
                $this->printSchedulesByDivisions();
                break;

            case View_Base::TEAM_VIEW:
                $this->printSchedulesByTeam();
                break;

            case View_Base::FAMILY_VIEW:
                self::printSchedulesByFamily($this);
                break;
        }
    }

    /**
     * @brief Print schedules by field(s)
     *        - Facility (or all)
     *        - Division (or all)
     */
    private function _printViewSchedulesByField() {
        $facilitySelectorData   = [];
        if (isset($this->m_controller->m_season)) {
            $facilities = Facility::lookupBySeason($this->m_controller->m_season);
            $facilitySelectorData[0] = 'All';
            foreach ($facilities as $facility) {
                $facilitySelectorData[$facility->id] = $facility->name;
            }
        }

        $divisionsSelector = $this->getDivisionsSelector(true, true, false);

        // Print the form
        print "
            <table valign='top' align='center' border='0' cellpadding='5' cellspacing='0'>
                <tr>
                    <th nowrap colspan='2' align='left'>View Schedules by Field(s)</th>
                </tr>
            <form method='post' action='" . self::SCHEDULE_PREVIEW_PAGE . $this->m_urlParams . "'>";

        $facilityName = '';
        if (isset($this->m_controller->m_facilityId) and $this->m_controller->m_facilityId != 0) {
            $facilityName = Facility::lookupById($this->m_controller->m_facilityId)->name;
        }

        $this->displaySelector('Facility:', View_Base::FACILITY_ID, '', $facilitySelectorData, $facilityName);
        $this->displaySelector('Division:', View_Base::DIVISION_NAME, '', $divisionsSelector, $this->m_controller->m_divisionName);

        print "
                <tr>
                    <td align='left'>
                        <input style='background-color: yellow' name='" . View_Base::SUBMIT . "' type='submit' value='" . View_Base::FIELD_VIEW . "'>
                    </td>
                </tr>
            </form>
            </table>";
    }

    /**
     * @brief Print schedules by divisions(s)
     *        - Division (or all)
     */
    private function _printViewSchedulesByDivision() {
        $divisionsSelector = $this->getDivisionsSelector(true, true, true);

        // Print the form
        print "
            <table valign='top' align='center' border='0' cellpadding='5' cellspacing='0'>
                <tr>
                    <th nowrap colspan='2' align='left'>View Schedules by Division(s)</th>
                </tr>
            <form method='post' action='" . self::SCHEDULE_PREVIEW_PAGE . $this->m_urlParams . "'>";

        $this->displaySelector('Division:', View_Base::DIVISION_NAME, '', $divisionsSelector, $this->m_controller->m_divisionName);

        // Print View button and end form
        print "
                <tr>
                    <td align='left'>
                        <input style='background-color: yellow' name='" . View_Base::SUBMIT . "' type='submit' value='" . View_Base::DIVISION_VIEW . "'>
                    </td>
                </tr>
            </form>
            </table>";
    }

    /**
     * @brief Print schedules by team(s)
     *        - Division
     *        - Team (or all)
     */
    private function _printViewSchedulesByTeam() {
        $divisionsSelector = $this->getDivisionsSelector(true, false, true);

        // Print the form
        print "
            <table valign='top' align='center' border='0' cellpadding='5' cellspacing='0'>
                <tr>
                    <th nowrap colspan='2' align='left'>View Schedules by Team(s)</th>
                </tr>
            <form method='post' action='" . self::SCHEDULE_PREVIEW_PAGE . $this->m_urlParams . "'>";

        $this->displaySelector('Division:', View_Base::DIVISION_NAME, '', $divisionsSelector, $this->m_controller->m_divisionName);

        // Print View button and end form
        print "
                <tr>
                    <td align='left'>
                        <input style='background-color: yellow' name='" . View_Base::SUBMIT . "' type='submit' value='" . View_Base::TEAM_VIEW . "'>
                    </td>
                </tr>
            </form>
            </table>";
    }

    /**
     * @brief Print schedules by family(s)
     *        - Family (or all)
     *
     * @param View_AdminSchedules_Base  $view
     * @param string                    $page
     * @param int|null                  $sessionId - Identifier of authenticated session (if any)
     * @param bool                      $includeFixButton
     */
    public static function _printViewSchedulesByFamily($view, $page, $sessionId = null, $includeFixButton = false) {
        $familySelector = $view->getFamilySelector(true);
        $currentSelection = 'All';
        if (isset($view->m_controller->m_familyId) and $view->m_controller->m_familyId != 0) {
            $family = Family::lookupById($view->m_controller->m_familyId);
            $coaches = Coach::lookupByFamily($family);
            if (count($coaches) > 0) {
                $currentSelection = $coaches[0]->shortName;
            }
        }

        // Print the form
        print "
            <table valign='top' align='center' border='0' cellpadding='5' cellspacing='0'>
                <tr>
                    <th nowrap colspan='2' align='left'>View Schedules by Family(s)</th>
                </tr>
            <form method='post' action='" . $page . $view->m_urlParams . "'>";

        $view->displaySelector('Family:', View_Base::FAMILY_ID, '', $familySelector, $currentSelection);

        // Print View button and end form
        $sessionHTML = '';
        if (isset($sessionId)) {
            $sessionHTML = "<input type='hidden' id='sessionId' name='sessionId' value='$sessionId'>";
        }

        print "
                <tr>
                    <td align='left'>
                        <input style='background-color: yellow' name='" . View_Base::SUBMIT . "' type='submit' value='" . View_Base::FAMILY_VIEW . "'>
                        $sessionHTML
                    </td>
                </tr>";

        if ($includeFixButton) {
            print "
                <tr>
                    <td align='left' colspan='2' title='Best attempt to reschedule games that overlap'>
                        <input style='background-color: yellow' name='" . View_Base::SUBMIT . "' type='submit' value='" . View_Base::FAMILY_FIX . "'>
                        $sessionHTML
                    </td>
                </tr>";
        }

        print "
            </form>
            </table>";
    }

    private function printSchedulesByFields()
    {
        $facilityData = [];
        foreach ($this->m_controller->m_divisionNames as $name) {
            $divisions = Division::lookupByName($this->m_controller->m_season, $name);
            foreach ($divisions as $division) {
                $divisionFields = DivisionField::lookupByDivision($division);
                foreach ($divisionFields as $divisionField) {
                    if ($this->m_controller->m_facilityId != 0) {
                        if ($this->m_controller->m_facilityId == $divisionField->field->facility->id) {
                            if (!isset($facilityData[$divisionField->field->facility->name][$divisionField->field->id])) {
                                $facilityData[$divisionField->field->facility->name][$divisionField->field->id] = $divisionField->field;
                            }
                        }
                    } else {
                        if (!isset($facilityData[$divisionField->field->facility->name][$divisionField->field->id])) {
                            $facilityData[$divisionField->field->facility->name][$divisionField->field->id] = $divisionField->field;
                        }
                    }
                }
            }
        }

        ksort($facilityData);

        foreach ($facilityData as $name => $data) {
            $fields = [];
            foreach ($data as $id => $field) {
                $fields[] = $field;
            }
            $this->_printSchedulesByFacilityAndFields($fields[0]->facility, $fields);
        }
    }

    /**
     * @brief Print Schedules for the specified facility and fields
     *
     * @param Facility $facility
     * @param Field[] $fields
     */
    private function _printSchedulesByFacilityAndFields($facility, $fields) {
        $gameDates  = GameDate::lookupBySeason($this->m_controller->m_season);
        $startTime  = $this->m_controller->m_season->startTime;
        $endTime    = $this->m_controller->m_season->endTime;

        print "
            <table valign='top' align='center' width='400' border='1' cellpadding='5' cellspacing='0'>
                <tr bgcolor='lightskyblue'>
                    <th align='center'>$facility->name</th>
                </tr>";

        foreach ($fields as $field) {
            print "
                <tr>
                    <td>
            <table valign='top' align='center' border='0' cellpadding='5' cellspacing='0' width='700'>";

            $interval = $field->getGameDurationInMinutesInterval();
            $divisionFields = DivisionField::lookupByField($field);
            $selectedDivisions = '';
            foreach ($divisionFields as $divisionField) {
                $gender = $divisionField->division->gender == 'Girls' ? "G" : "B";
                $selectedDivisions .= $divisionField->division->name . $gender . " ";
            }

            print "
                <tr>
                    <td width='75' nowrap><strong>Field Name:</strong></td>
                    <td nowrap>$field->name</td>
                    <td>&nbsp</td>
                </tr>
                <tr>
                    <td width='75' nowrap><strong>Divisions:</strong></td>
                    <td nowrap>$selectedDivisions</td>
                    <td>&nbsp</td>
                </tr>";

            $defaultGameTimes = GameTime::getDefaultGameTimes($startTime, $endTime, $interval, [$field]);
            print "
                <tr>
                    <td colspan='3'>
                    <table valign='top' align='center' border='1' cellpadding='5' cellspacing='0' width='700'>
                        <thead>
                        <tr bgcolor='lightskyblue'>
                            <th>&nbsp</th>";

            foreach ($defaultGameTimes as $defaultGameTime) {
                $defaultGameTime = ltrim(substr($defaultGameTime, 0, 5), "0");
                print "
                            <th>$defaultGameTime</th>
                ";
            }

            print "
                        </tr>
                        </thead>";

            foreach ($gameDates as $gameDate) {
                $gameTimes = GameTime::lookupByGameDateAndField($gameDate, $field);
                print "
                <tr>
                    <td nowrap>$gameDate->day</td>";

                foreach ($defaultGameTimes as $defaultGameTime) {
                    $bgHTML     = "bgcolor='red'";
                    $cellHTML   = '&nbsp';
                    $title      = '';
                    foreach ($gameTimes as $gameTime) {
                        if ($gameTime->actualStartTime == $defaultGameTime) {
                            if (isset($gameTime->game)) {
                                $game               = $gameTime->game;
                                $gender             = $gameTime->game->flight->schedule->division->gender;
                                $bgHTML             = $gender == 'Boys' ? "bgcolor='lightblue'" : "bgcolor='lightyellow'";

                                if (isset($game->homeTeam)) {
                                    $homeTeamCoach = Coach::lookupByTeam($game->homeTeam);
                                    $homeTeam      = $game->homeTeam;

                                    if (isset($game->visitingTeam)) {
                                        $visitingTeamCoach  = Coach::lookupByTeam($game->visitingTeam);
                                        $visitingTeam       = $game->visitingTeam;
                                        $cellHTML           = "H: " . $homeTeam->nameIdWithSeed . "<br>" . "V: " . $visitingTeam->nameIdWithSeed;
                                        $title              = "title='" . $homeTeamCoach->name . " vs " . $visitingTeamCoach->name . "'";
                                    } else {
                                        $cellHTML = "H: " . $homeTeam->nameIdWithSeed . "<br>" . "V: TBD";
                                        $title    = "title='" . $homeTeamCoach->name . " vs TBD'";
                                    }

                                    if (isset($game->title)) {
                                        $cellHTML .= "<br>" . $game->title;
                                    }
                                } else {
                                    $cellHTML = $game->title;
                                    $title    = "title='" . $game->title . "'";
                                }
                            } else {
                                $bgHTML = $gameTime->genderPreference == 'Boys' ? "bgcolor='lightblue'" : "bgcolor='lightyellow'";
                            }
                        }
                    }
                    print "<td nowrap $bgHTML $title>$cellHTML</td>";
                }

                print "
                </tr>
                ";
            }

            print "
                    </table>";

            print "
                            </td>
                        </tr>
            </table>
                    </td>
                </tr>";
        }

        print "
            </table><br><br>";
    }

    /**
     * @brief Print schedules for the selected divisions
     */
    private function printSchedulesByDivisions() {
        $schedules = [];
        foreach ($this->m_controller->m_divisions as $division) {
            $schedules = array_merge($schedules, Schedule::lookupByDivision($division));
        }

        print "
            <table valign='top' align='center' width='400' border='1' cellpadding='5' cellspacing='0'>
                <tr>
                    <td valign='top'>";

        foreach ($schedules as $schedule) {
            if ($schedule->published == 0) {
                View_Games_Schedule::printSchedule($schedule);
            }
            // View_Games_Division::printSchedule($this->m_controller->m_season, $schedule);
        }

        print "
                    </td>
                </tr>
            </table>";
    }

    private function printSchedulesByTeam()
    {
        Precondition::isTrue(count($this->m_controller->m_divisions) == 1, "Just pick one Division, no support yet to display schedules by team across divisions");
        $division = $this->m_controller->m_divisions[0];

        // Get schedules for division
        $schedules = Schedule::lookupByDivision($division);

        // Get games across all schedules for division
        $games = [];
        foreach ($schedules as $schedule) {
            $flights = Flight::lookupBySchedule($schedule);
            foreach ($flights as $flight) {
                $games = array_merge($games, Game::lookupByFlight($flight));
            }
        }

        // Track games by day
        $gamesByDay = [];
        foreach ($games as $game) {
            $gamesByDay[$game->gameTime->gameDate->day][] = $game;
        }
        ksort($gamesByDay);

        // For each team, display games ordered by date and time
        $teams = Team::lookupByDivision($division);
        foreach ($teams as $team) {
            View_Games_Team::printScheduleForTeam($team, null, $gamesByDay, $schedules, true, false);
        }
    }

    /**
     * @param View_AdminSchedules_Base  $view
     */
    public static function printSchedulesByFamily($view)
    {
        $families = [];
        if ($view->m_controller->m_familyId == 0) {
            $families = Family::lookupBySeason($view->m_controller->m_season);
        } else {
            $families[] = Family::lookupById($view->m_controller->m_familyId);
        }

        foreach ($families as $family) {
            $familyGames    = FamilyGame::lookupByFamily($family);
            $familyName     = $family->name;
            $gamesByTime    = [];
            $gamesByDay     = [];

            // Sort by time
            foreach ($familyGames as $familyGame) {
                $gamesByTime[$familyGame->game->gameTime->startTime][] = $familyGame->game;
            }
            ksort($gamesByTime);

            // Sort by day
            foreach ($gamesByTime as $games) {
                foreach ($games as $game) {
                    $gamesByDay[$game->gameTime->gameDate->day][] = $game;
                }
            }
            ksort($gamesByDay);

            // Print table header
            print "
            <table valign='top' align='center' border='1' cellpadding='5' cellspacing='0' width='700'>
                <thead>
                    <tr bgcolor='lightskyblue'>
                        <th align='center' colspan='6'>$familyName</th>
                    </tr>
                    <tr bgcolor='lightskyblue'>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Field</th>
                        <th>Game Id</th>
                        <th>Home Team</th>
                        <th>Visiting Team</th>
                    </tr>
                </thead>";

            $dayCount = 0;
            foreach ($gamesByDay as $day => $games) {
                $gameCount  = count($games);
                $dayPrinted = false;
                $bgcolor    = ($dayCount % 2 == 0) ? "" : "bgcolor='lightgray'";
                foreach ($games as $game) {
                    $field              = $game->gameTime->field;
                    $gameId             = $game->id;
                    $facility           = $field->facility;
                    $fieldName          = $facility->name . ": " . $field->name;
                    $homeCoach          = isset($game->homeTeam) ? Coach::lookupByTeam($game->homeTeam) : null;
                    $visitingCoach      = isset($game->visitingTeam) ? Coach::lookupByTeam($game->visitingTeam) : null;

                    if (isset($game->homeTeam)) {
                        $homeTeamName   = $game->homeTeam->name . ": " . $homeCoach->lastName;
                    } else {
                        $homeTeamName   = 'TBD';
                    }
                    if (isset($game->visitingTeam)) {
                        $visitingTeamName   = $game->visitingTeam->name . ": " . $visitingCoach->lastName;
                    } else {
                        $visitingTeamName   = 'TBD';
                    }

                    $startTime          = $game->gameTime->actualStartTime;
                    $overlapBgColor     = $game->anyOverlap($games) ? "bgcolor='red'" : "";

                    $homeTeamStyle      = '';
                    $visitingTeamStyle  = '';
                    if (isset($homeCoach) and $homeCoach->family->id == $family->id) {
                        $homeTeamStyle = "style='color: red'";
                    }

                    if (isset($visitingCoach) and $visitingCoach->family->id == $family->id) {
                        $visitingTeamStyle = "style='color: red'";
                    }

                    $dayCell    = $dayPrinted ? "" : "<td rowspan='$gameCount'>$day</td>";
                    $dayPrinted = true;

                    print "
                    <tr $bgcolor>
                        $dayCell
                        <td $overlapBgColor>$startTime</td>
                        <td>$fieldName</td>
                        <td align='center'>$gameId</td>
                        <td $homeTeamStyle>$homeTeamName</td>
                        <td $visitingTeamStyle>$visitingTeamName</td>
                    </tr>";
                }

                $dayCount += 1;
            }

            print "
            </table><br>";
        }
    }
}