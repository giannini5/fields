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
class View_Schedules_Field extends View_Schedules_Base {
    /**
     * @brief Construct the View
     *
     * @param $controller - Controller that contains data used when rendering this view.
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
        $facilities             = [];
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
                <tr bgcolor='lightyellow'>
                    <td>";

        $this->_printCreateFieldForm($sessionId, $facilitySelectorData, $divisionsSelector);

        print "
                    </td>
                </tr>
            </table>
            <br><br>";

        print "
            <table valign='top' align='center' width='400' border='1' cellpadding='5' cellspacing='0'>
                <tr bgcolor='lightblue'>
                    <td>";

        array_unshift($facilitySelectorData, 'All');
        $this->_printViewFieldForm($sessionId, $facilitySelectorData, $divisionsSelector);

        print "
                    </td>
                </tr>
            </table>
            <br><br>";

        // Print Update Field Form for the created field or the requested fields
        if ($this->m_controller->m_operation != View_Base::DELETE) {
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
                $this->_printUpdateFieldsForm($sessionId, $fields[0]->facility, $fields, $divisionsSelector);
            }
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
                    <th align='center'>Create New Field</th>
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
     * @brief Print the form to view a field.  Form includes the following
     *        - Facility (or all)
     *        - Division (or all)
     *
     * @param int $sessionId
     * @param $facilitySelectorData
     */
    private function _printViewFieldForm($sessionId, $facilitySelectorData) {
        $divisionsSelector = $this->getDivisionsSelector(true, true, false);

        // Print the start of the form to select which field to view
        print "
            <table valign='top' align='center' border='0' cellpadding='5' cellspacing='0'>
                <tr>
                    <th align='center'>View Existing Field(s)</th>
                </tr>
            <form method='post' action='" . self::SCHEDULE_FIELDS_PAGE . $this->m_urlParams . "'>";

        $this->displaySelector('Facility:', View_Base::FACILITY_ID, '', $facilitySelectorData, $this->m_controller->m_facilityId);
        $this->displaySelector('Division:', View_Base::DIVISION_NAME, '', $divisionsSelector, $this->m_controller->m_facilityId);

        // Print Create button and end form
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
                    <td>
            <table valign='top' align='center' border='0' cellpadding='5' cellspacing='0'>
                <tr>
                    <td>";

            print "
            <form method='post' action='" . self::SCHEDULE_FIELDS_PAGE . $this->m_urlParams . "'>";

            $interval = $field->getGameDurationInMinutesInterval();

            print "
                <tr>";

            $name = View_Base::FIELD_UPDATE_DATA . "[$field->id][" . View_Base::NAME . "]";
            $this->displayInput('Field Name:', 'text', $name, '', '', $field->name, NULL, 1, false);

            $name = View_Base::FIELD_UPDATE_DATA . "[$field->id][" . View_Base::ENABLED . "]";
            $this->displayRadioSelector('Enabled:', $name, array(0=>'No', 1=>'Yes'), $field->enabled ? 'Yes' : 'No', NULL, 1, false);

            $divisionFields = DivisionField::lookupByField($field);
            $selectedDivisions = [];
            foreach ($divisionFields as $divisionField) {
                $selectedDivisions[] = $divisionField->division->name;
            }
            $name = View_Base::FIELD_UPDATE_DATA . "[$field->id][" . View_Base::DIVISION_NAMES . "]";
            $this->displayMultiSelector('Divisions', $name, $selectedDivisions, $divisionsSelector, count($divisionsSelector), NULL, 1, false);

            $defaultGameTimes = GameTime::getDefaultGameTimes($startTime, $endTime, $interval);
            print "
                </tr>
                <tr>
                    <td colspan='8'>
                    <table valign='top' align='center' border='1' cellpadding='5' cellspacing='0'>
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
                        if ($gameTime->startTime == $defaultGameTime) {
                            if (isset($gameTime->game)) {
                                $game               = $gameTime->game;
                                $homeTeamCoach      = Coach::lookupByTeam($game->homeTeam);
                                $visitingTeamCoach  = Coach::lookupByTeam($game->visitingTeam);
                                $gender             = $gameTime->game->homeTeam->division->gender;
                                $bgHTML             = $gender == 'Boys' ? "bgcolor='lightblue'" : "bgcolor='lightyellow'";
                                $homeTeam           = $game->homeTeam;
                                $visitingTeam       = $game->visitingTeam;
                                $cellHTML           = $homeTeam->name . "<br>" . $visitingTeam->name;
                                $title              = "title='" . $homeTeamCoach->name . " vs " . $visitingTeamCoach->name . "'";
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
                    <td align='right' colspan='6'>
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
                </tr>";
        }

        print "
            </table>";
    }
}