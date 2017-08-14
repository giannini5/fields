<?php

use \DAG\Domain\Schedule\Field;
use \DAG\Domain\Schedule\Facility;
use \DAG\Domain\Schedule\Division;
use \DAG\Domain\Schedule\DivisionField;
use \DAG\Domain\Schedule\GameDate;
use \DAG\Domain\Schedule\GameTime;
use \DAG\Domain\Schedule\Coach;

/**
 * @brief Show the Field page and get the user to select a season to administer or create a new season.
 */
class View_AdminSchedules_Field extends View_AdminSchedules_Base {
    /**
     * @brief Construct the View
     *
     * @param Controller_Base $controller - Controller that contains data used when rendering this view.
     */
    public function __construct($controller) {
        parent::__construct(self::SCHEDULE_FIELDS_PAGE, $controller);
    }

    /**
     * @brief Render data for display on the page.
     */
    public function render()
    {
        $sessionId              = $this->m_controller->getSessionId();
        $messageString          = $this->m_controller->m_messageString;
        $facilitySelectorData   = [];
        $divisionsSelector      = $this->getDivisionsSelector(true);

        if (isset($this->m_controller->m_season)) {
            $facilities = Facility::lookupBySeason($this->m_controller->m_season);
            foreach ($facilities as $facility) {
                $facilitySelectorData[$facility->id] = $facility->name;
            }
        }

        if (!empty($messageString)) {
            print "
                <p style='color: green' align='center'><strong>$messageString</strong></p><br>";
        }

        print "
            <table valign='top' align='center' width='400' border='1' cellpadding='5' cellspacing='0'>
                <tr>
                    <td bgcolor='" . View_Base::CREATE_COLOR  . "'>";

        $this->_printCreateFieldForm($sessionId, $facilitySelectorData, $divisionsSelector);

        print "
                    </td>
                    <td valign='top' bgcolor='" . View_Base::VIEW_COLOR  . "'>";

        $facilitySelectorData[0] = 'All';
        ksort($facilitySelectorData);
        $this->_printUpdateFieldForm($sessionId, $facilitySelectorData);

        print "
                    </td>
                </tr>
            </table>
            <br><br>";

        // Print Update Field Form for the created field or the requested fields
        $facilityData = [];
        foreach ($this->m_controller->m_divisionNames as $name) {
            $divisions = Division::lookupByName($this->m_controller->m_season, $name);
            foreach ($divisions as $division) {
                $divisionFields = DivisionField::lookupByDivision($division, true);
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

        $dataPrinted = false;
        foreach ($facilityData as $name => $data) {
            $fields = [];
            foreach ($data as $id => $field) {
                $fields[] = $field;
            }
            $this->_printUpdateFieldsForm($sessionId, $fields[0]->facility, $fields, $divisionsSelector);
            $dataPrinted = true;
        }

        if (!$dataPrinted and $this->m_controller->m_operation == View_Base::VIEW) {
            print "
                <p align='center' style='color: red; font-size: medium'>No fields found for the selected facility.  Either create a field or go to the HOME tab to upload fields</p>";
        }
    }

    /**
     * @brief Print the form to create a field.  Form includes the following
     *        - Facility
     *        - Name
     *        - Enabled radio button
     *
     * @param int $sessionId
     * @param $facilitySelectorData
     * @param $divisionsSelector
     */
    private function _printCreateFieldForm($sessionId, $facilitySelectorData, $divisionsSelector) {
        // Print the start of the form to select a facility
        print "
            <table valign='top' align='center' border='0' cellpadding='5' cellspacing='0'>
                <tr>
                    <th nowrap colspan='2' align='left'>Create New Field</th>
                </tr>
            <form method='post' action='" . self::SCHEDULE_FIELDS_PAGE . $this->m_urlParams . "'>";

        $this->displaySelector('Facility:', View_Base::FACILITY_ID, '', $facilitySelectorData, $this->m_controller->m_facilityId);
        $this->displayInput('Field Name:', 'text', View_Base::NAME, 'Field Name', '');
        $this->displayRadioSelector('Enabled:', View_Base::ENABLED, array(0=>'No', 1=>'Yes'), 'Yes');
        $this->displayMultiSelector('Divisions', View_Base::DIVISION_NAMES, '', $divisionsSelector, count($divisionsSelector));

        // Print Create button and end form
        print "
                <tr>
                    <td align='left'>
                        <input style='background-color: yellow' name='" . View_Base::SUBMIT . "' type='submit' value='" . View_Base::CREATE . "'>
                        <input type='hidden' id='sessionId' name='sessionId' value='$sessionId'>
                    </td>
                </tr>
            </form>
            </table>";
    }

    /**
     * @brief Print the form to update a field.  Form includes the following
     *        - Facility (or all)
     *        - Division (or all)
     *
     * @param int $sessionId
     * @param $facilitySelectorData
     */
    private function _printUpdateFieldForm($sessionId, $facilitySelectorData) {
        $divisionsSelector  = $this->getDivisionsSelector(true, true, false);
        $facilityName       = $this->m_controller->m_facilityId == 0 ? '' : Facility::lookupById($this->m_controller->m_facilityId)->name;

        // Print the start of the form to select which field to view
        print "
            <table valign='top' align='center' border='0' cellpadding='5' cellspacing='0'>
                <tr>
                    <th nowrap colspan='2' align='left'>View Existing Field(s)</th>
                </tr>
            <form method='post' action='" . self::SCHEDULE_FIELDS_PAGE . $this->m_urlParams . "'>";

        $this->displaySelector('Facility:', View_Base::FACILITY_ID, $this->m_controller->m_facilityId, $facilitySelectorData, $facilityName);
        $this->displaySelector('Division:', View_Base::DIVISION_NAME, $this->m_controller->m_divisionName, $divisionsSelector, $this->m_controller->m_divisionNameSelection);

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
     * @brief Print the form to update a season.  Form includes the following
     *        - Field Name
     *        - Enabled radio button
     *
     * @param int $sessionId
     * @param Facility $facility
     * @param Field[] $fields
     * @param $divisionsSelector
     */
    private function _printUpdateFieldsForm($sessionId, $facility, $fields, $divisionsSelector) {
        $gameDates = [];
        $startTime = '';
        $endTime   = '';
        if (isset($this->m_controller->m_season)) {
            $gameDates  = GameDate::lookupBySeason($this->m_controller->m_season);
            $startTime  = $this->m_controller->m_season->startTime;
            $endTime    = $this->m_controller->m_season->endTime;
        }

        print "
            <table valign='top' align='center' width='400' border='1' cellpadding='5' cellspacing='0'>
                <tr bgcolor='lightskyblue'>
                    <th align='center'>$facility->name</th>
                </tr>";

        foreach ($fields as $field) {
            print "
                <tr>
                    <td>";

            $this->printUpdateFieldFormAndGames($sessionId, $facility, $field, $gameDates, $startTime, $endTime, $divisionsSelector);

            print "
                    </td>
                </tr>";

            print "
            </table>
                    </td>
                </tr>";
        }

        print "
            </table><br><br>";
    }

    /**
     * @param int           $sessionId
     * @param Facility      $facility
     * @param Field         $field
     * @param GameDate[]    $gameDates
     * @param string        $startTime
     * @param string        $endTime
     * @param Division[]    $divisionsSelector
     */
    private function printUpdateFieldFormAndGames($sessionId, $facility, $field, $gameDates, $startTime, $endTime, $divisionsSelector)
    {
        $interval           = $field->getGameDurationInMinutesInterval();
        $defaultGameTimes   = GameTime::getDefaultGameTimes($startTime, $endTime, $interval);

        print "
            <table valign='top' align='center' border='0' cellpadding='5' cellspacing='0'>
                <tr>
                    <td>";

        $this->printUpdateFieldForm($sessionId, $facility, $field, $divisionsSelector);
        print "
                    </td>
                    <td>";

        $this->printMoveGameForm($sessionId, $defaultGameTimes, $facility, $field);

        print "
                    </td>
                </tr>";

        print "
                </tr>
                <tr>
                    <td colspan='8'>
                    <table valign='top' align='center' border='1' cellpadding='5' cellspacing='0' width='900'>
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
                <form method='post' action='" . self::SCHEDULE_FIELDS_PAGE . $this->m_urlParams . "'>";

            print "
                <tr>
                    <td nowrap>$gameDate->day</td>";

            $gameTimesFound = count($gameTimes) > 0;
            $gamesFound     = false;
            foreach ($defaultGameTimes as $defaultGameTime) {
                $bgHTML     = "bgcolor='red'";
                $cellHTML   = '&nbsp';
                $title      = '';
                foreach ($gameTimes as $gameTime) {
                    if ($gameTime->startTime == $defaultGameTime) {
                        if (isset($gameTime->game)) {
                            $gamesFound         = true;
                            $game               = $gameTime->game;
                            $gender             = $gameTime->game->flight->schedule->division->gender;
                            $bgHTML             = $gender == 'Boys' ? "bgcolor='lightblue'" : "bgcolor='lightyellow'";
                            $cellHTML           = "Game Id: " . $game->id . "<br>";
                            $titleGameDivisionFlight = $game->flight->schedule->division->name . $game->flight->schedule->division->gender;

                            if (isset($game->homeTeam)) {
                                $homeTeamCoach      = Coach::lookupByTeam($game->homeTeam);
                                $visitingTeamCoach  = Coach::lookupByTeam($game->visitingTeam);
                                $homeTeam           = $game->homeTeam;
                                $visitingTeam       = $game->visitingTeam;
                                $cellHTML           .= $homeTeam->nameId . "<br>" . $visitingTeam->nameId;
                                $title              = "title='" . $homeTeamCoach->name . " vs " . $visitingTeamCoach->name . "'";
                            } else {
                                $cellHTML           .= $game->title;
                                $title              = "title='" . $titleGameDivisionFlight . " " . $game->flight->name . "'";
                            }

                            if ($game->id == $this->m_controller->m_moveGameId) {
                                $bgHTML = "bgcolor='salmon'";
                            }
                        } else {
                            $bgHTML = $gameTime->genderPreference == 'Boys' ? "bgcolor='lightblue'" : "bgcolor='lightyellow'";
                        }
                    }
                }
                print "
                        <td nowrap $bgHTML $title>$cellHTML</td>";
            }

            // Print Update button and end form
            if (!$gamesFound) {
                if ($gameTimesFound) {
                    print "
                        <td align='left'>
                            <input style='background-color: salmon' name='" . View_Base::SUBMIT . "' type='submit' value='" . View_Base::REMOVE . "'>
                            <input type='hidden' id='fieldId' name='fieldId' value='$field->id'>
                            <input type='hidden' id='facilityId' name='facilityId' value='$facility->id'>
                            <input type='hidden' id='gameDateId' name='gameDateId' value='$gameDate->id'>
                            <input type='hidden' id='sessionId' name='sessionId' value='$sessionId'>
                        </td>";
                } else {
                    print "
                        <td align='left'>
                            <input style='background-color: lightgreen' name='" . View_Base::SUBMIT . "' type='submit' value='" . View_Base::ADD . "'>
                            <input type='hidden' id='fieldId' name='fieldId' value='$field->id'>
                            <input type='hidden' id='facilityId' name='facilityId' value='$facility->id'>
                            <input type='hidden' id='gameDateId' name='gameDateId' value='$gameDate->id'>
                            <input type='hidden' id='sessionId' name='sessionId' value='$sessionId'>
                        </td>";
                }
            }

            print "
                </tr>
                </form>
                ";
        }

        print "
            </table>";
    }

    /**
     * @param int           $sessionId
     * @param Facility      $facility
     * @param Field         $field
     * @param Division[]    $divisionsSelector
     */
    private function printUpdateFieldForm($sessionId, $facility, $field, $divisionsSelector)
    {
        print "
            <table valign='top' align='center' border='1' cellpadding='5' cellspacing='0'>
                <tr>
                    <td>
                        <table valign='top' align='center' border='0' cellpadding='5' cellspacing='0'>";

        print "
                <form method='post' action='" . self::SCHEDULE_FIELDS_PAGE . $this->m_urlParams . "'>
                <tr>";

        $name = View_Base::FIELD_UPDATE_DATA . "[$field->id][" . View_Base::NAME . "]";
        $this->displayInput('Field Name:', 'text', $name, '', '', $field->name, NULL, 1, false);

        print "
                </tr>
                <tr>";

        $name = View_Base::FIELD_UPDATE_DATA . "[$field->id][" . View_Base::ENABLED . "]";
        $this->displayRadioSelector('Enabled:', $name, array(0=>'No', 1=>'Yes'), $field->enabled ? 'Yes' : 'No', NULL, 1, false);

        print "
                </tr>
                <tr>";

        $divisionFields = DivisionField::lookupByField($field);
        $selectedDivisions = [];
        foreach ($divisionFields as $divisionField) {
            $selectedDivisions[] = $divisionField->division->name;
        }
        $name = View_Base::FIELD_UPDATE_DATA . "[$field->id][" . View_Base::DIVISION_NAMES . "]";
        $this->displayMultiSelector('Divisions', $name, $selectedDivisions, $divisionsSelector, count($divisionsSelector), NULL, 1, false);

        print "
                </tr>";

        // Print Update button and end form
        print "
                <tr>
                    <td align='left'>
                        <input style='background-color: lightgreen' name='" . View_Base::SUBMIT . "' type='submit' value='" . View_Base::UPDATE . "'>
                        <input type='hidden' id='fieldId' name='fieldId' value='$field->id'>
                        <input type='hidden' id='facilityId' name='facilityId' value='$facility->id'>
                        <input type='hidden' id='sessionId' name='sessionId' value='$sessionId'>
                    </td>
                    <td align='left'>
                        <input style='background-color: salmon' name='" . View_Base::SUBMIT . "' type='submit' value='" . View_Base::DELETE . "'>
                        <input type='hidden' id='fieldId' name='fieldId' value='$field->id'>
                        <input type='hidden' id='facilityId' name='facilityId' value='$facility->id'>
                        <input type='hidden' id='sessionId' name='sessionId' value='$sessionId'>
                    </td>
                </tr>
            </form>";

        print "
                        </table>
                    </td>
                </tr>
            </table>";
    }

    /**
     * @param int       $sessionId
     * @param string[]  $defaultGameTimes
     * @param Facility  $facility
     * @param Field     $field
     */
    private function printMoveGameForm($sessionId, $defaultGameTimes, $facility, $field)
    {
        $gameIds        = [];
        $gameTimesStr   = [];
        $gameTimes      = GameTime::lookupByField($field);

        foreach ($gameTimes as $gameTime) {
            if (isset($gameTime->game)) {
                $gameIds[$gameTime->game->id] = $gameTime->game->id;
            }
        }

        foreach ($defaultGameTimes as $gameTime) {
            $readableGameTime           = ltrim(substr($gameTime, 0, 5), "0");
            $gameTimesStr[$gameTime]    = $readableGameTime;

        }

        // Print Move controls and button
        print "
            <table valign='top' align='left' border='1' cellpadding='5' cellspacing='0'>
                <tr>
                    <td>";

        print "
                        <table valign='top' align='left' border='0' cellpadding='5' cellspacing='0'>
                            <form method='post' action='" . self::SCHEDULE_FIELDS_PAGE . $this->m_urlParams . "'>";

        asort($gameIds);
        $this->displaySelector('Game Id To Move:', View_Base::GAME_ID, '', $gameIds, '', NULL, true, 150, 'left', 'Select Game Id to Move');
        $this->displaySelector('New Game Time:', View_Base::GAME_TIME, '', $gameTimesStr, '', NULL, true, 150, 'left', 'Select New Game Time');

        print "
                            <tr>
                                <td align='right' colspan='2' title='Move game to an open time slot'>
                                    <input style='background-color: lightgreen' name='" . View_Base::SUBMIT . "' type='submit' value='" . View_Base::MOVE . "'>
                                    <input type='hidden' id='fieldId' name='fieldId' value='$field->id'>
                                    <input type='hidden' id='facilityId' name='facilityId' value='$facility->id'>
                                    <input type='hidden' id='sessionId' name='sessionId' value='$sessionId'>
                                </td>
                            </tr>
                            </form>
                        </table>
                    </td>
                </tr>
            </table>";
    }
}