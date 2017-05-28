<?php

use \DAG\Domain\Schedule\Facility;
use \DAG\Domain\Schedule\Division;
use \DAG\Domain\Schedule\DivisionField;
use \DAG\Domain\Schedule\GameDate;
use \DAG\Domain\Schedule\GameTime;

/**
 * @brief Show the Referee page
 */
class View_AdminSchedules_Referee extends View_AdminSchedules_Base
{
    /**
     * @brief Construct the View
     *
     * @param $controller - Controller that contains data used when rendering this view.
     */
    public function __construct($controller)
    {
        parent::__construct(self::SCHEDULE_REFEREE_PAGE, $controller);
    }

    /**
     * @brief Render data for display on the page.
     */
    public function render()
    {
        $sessionId              = $this->m_controller->getSessionId();
        $messageString          = $this->m_controller->m_messageString;
        $facilitySelectorData   = [];

        if (isset($this->m_controller->m_season)) {
            $facilities = Facility::lookupBySeason($this->m_controller->m_season);
            foreach ($facilities as $facility) {
                $facilitySelectorData[$facility->id] = $facility->name;
            }
        } else {
            return;
        }

        if (!empty($messageString)) {
            print "
                <p style='color: green' align='center'><strong>$messageString</strong></p><br>";
        }

        /*
        print "
            <table valign='top' align='center' width='400' border='1' cellpadding='5' cellspacing='0'>
                <tr>
                    <td valign='top' bgcolor='" . View_Base::VIEW_COLOR . "'>";

        ksort($facilitySelectorData);
        $this->_printSelectFacilityForm($sessionId, $facilitySelectorData);

        print "
                    </td>
                </tr>
            </table>
            <br><br>";
        */

        // If a facility was selected then get all games sorted by day and time for display
        $divisions  = Division::lookupBySeason($this->m_controller->m_season);
        $gameDates  = GameDate::lookupBySeason($this->m_controller->m_season);

        $divisionNames = [];
        foreach ($divisions as $division) {
            $divisionNames[$division->name] = 1;
        }

        foreach ($divisionNames as $divisionName => $count) {
            $divisions = Division::lookupByName($this->m_controller->m_season, $divisionName);

            $divisionFieldsById = [];
            foreach ($divisions as $division) {
                $divisionFields = DivisionField::lookupByDivision($division);
                foreach ($divisionFields as $divisionField) {
                    $divisionFieldsById[$divisionField->id] = $divisionField;
                }
            }

            $fields     = [];
            $fieldsById = [];
            foreach ($divisionFieldsById as $divisionFieldId => $divisionField) {
                $fields[] = $divisionField->field;
                $fieldsById[$divisionField->field->id] = $divisionField->field;
            }

            foreach ($gameDates as $gameDate) {
                $gameTimes = GameTime::lookupByGameDateAndFields($gameDate, $fields);

                $gameTimesByDayByTimeByField = [];
                foreach ($gameTimes as $gameTime) {
                    $gameTimesByDayByTimeByField[$gameDate->day][$gameTime->startTime][$gameTime->field->id] = $gameTime;
                }

                foreach ($gameTimesByDayByTimeByField as $day => $gameTimesByTimeByField) {
                    $this->printRefereeForm($divisionName, $day, $gameTimesByTimeByField, $fieldsById);
                }
            }
        }
    }

    /**
     * @brief Print the form to select a facility for display.  Form includes the following
     *        - Facility (or all)
     *
     * @param int $sessionId
     * @param $facilitySelectorData
     */
    private function _printSelectFacilityForm($sessionId, $facilitySelectorData)
    {
        $facilityName = $this->m_controller->m_facilityId == 0 ? '' : Facility::lookupById($this->m_controller->m_facilityId)->name;

        // Print the start of the form to select which facility to view
        print "
            <table valign='top' align='center' border='0' cellpadding='5' cellspacing='0'>
                <tr>
                    <th nowrap colspan='2' align='left'>View Existing Field(s)</th>
                </tr>
            <form method='post' action='" . self::SCHEDULE_REFEREE_PAGE . $this->m_urlParams . "'>";

        $this->displaySelector('Facility:', View_Base::FACILITY_ID, $this->m_controller->m_facilityId, $facilitySelectorData, $facilityName);

        // Print View button and end form
        print "
                <tr>
                    <td align='left'>
                        <input style='background-color: yellow' name='" . View_Base::SUBMIT . "' type='submit' value='" . View_Base::VIEW . "'>
                        <input type='hidden' id='sessionId' name='sessionId' value='$sessionId'>
                    </td>
                </tr>
            </form>
            </table>";
    }

    /**
     * @brief Print the form to display games by field and time
     *
     * @param string $divisionName
     * @param string $day
     * @param array $gameTimesByTimeByField
     * @param array $fieldsById
     */
    private function printRefereeForm($divisionName, $day, $gameTimesByTimeByField, $fieldsById)
    {
        print "
            <p align='center'>Boys and Girls $divisionName for $day</p>
            <table valign='top' align='center' width='400' border='1' cellpadding='5' cellspacing='0'>
                <tr bgcolor='lightskyblue'>
                    <th align='center'>Time/Field</th>";

        foreach ($fieldsById as $fieldId => $field) {
            $facilityName = $field->facility->name;
            $fieldName = $field->name;
            print "
                    <th nowrap align='center'>$facilityName<br>$fieldName</th>";
        }

        print "
                </tr>";

        foreach ($gameTimesByTimeByField as $time => $gameTimesByField) {
            $time = substr($time, 0, 5);
            print "
                <tr>
                    <td align='center' rowspan='3'>$time</td>";

            foreach ($fieldsById as $fieldId => $field) {
                $entry = "&nbsp";
                $bgcolor = '';
                if (isset($gameTimesByField[$fieldId])) {
                    $gameTime = $gameTimesByField[$fieldId];
                    if (isset($gameTime->game)) {
                        $game = $gameTime->game;
                        $gender = $game->flight->schedule->division->gender;
                        $bgcolor = $gender == 'Boys' ? "bgcolor='lightblue'" : "bgcolor='lightyellow'";
                        if ((isset($game->homeTeam))) {
                            $entry = $game->homeTeam->nameId . " v " . $game->visitingTeam->nameId;
                        } else if ($game->title != '') {
                            $entry = $game->title;
                        }
                    }
                }

                print "
                    <td nowrap align='center' $bgcolor>$entry</td>";
            }

            print "
                    </tr>";

            print "
                <tr>";

            foreach ($fieldsById as $fieldId => $field) {
                print "
                    <td nowrap align='center'>&nbsp</td>";
            }

            print "
                </tr>";
            print "
                <tr>";

            foreach ($fieldsById as $fieldId => $field) {
                print "
                    <td nowrap align='center'>&nbsp</td>";
            }

            print "
                </tr>";
        }

        print "
            </table><br><br>";
    }
}