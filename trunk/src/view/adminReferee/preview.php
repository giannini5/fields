<?php

use \DAG\Domain\Schedule\Division;
use \DAG\Domain\Schedule\DivisionReferee;
use \DAG\Domain\Schedule\GameDate;
use \DAG\Domain\Schedule\DivisionField;
use \DAG\Domain\Schedule\GameTime;
use \DAG\Domain\Schedule\GameReferee;
use \DAG\Domain\Schedule\RefereeCrew;
use \DAG\Domain\Schedule\StandbyReferee;

/**
 * @brief Schedule referees for a specified day
 */
class View_AdminReferee_Preview extends View_AdminReferee_Base {
    /** @var  Controller_AdminReferee_Preview */
    protected $m_controller;

    /**
     * @brief Construct the View
     *
     * @param Controller_AdminReferee_Preview $controller - Controller that contains data used when rendering this view.
     */
    public function __construct($controller) {
        $this->m_controller = $controller;
        parent::__construct(self::REFEREE_PREVIEW_PAGE, $controller);
    }

    /**
     * @brief Render data for display on the page.
     */
    public function render()
    {
        $filterDivisionName         = $this->m_controller->m_filterDivisionName;
        $filterGameDateId           = $this->m_controller->m_filterGameDateId;
        $sessionId                  = $this->m_controller->getSessionId();

        $messageString  = $this->m_controller->m_messageString;
        if (!empty($messageString)) {
            print "
                <p style='color: green' align='center'><strong>$messageString</strong></p><br>";
        }

        $this->printSelectors($filterDivisionName, $filterGameDateId, $sessionId);

        if ($filterDivisionName != '' and $filterGameDateId != 0) {
            $gameDates[] = GameDate::lookupById($filterGameDateId);
            $divisions   = Division::lookupByName($this->m_controller->m_season, $filterDivisionName);

            $divisionReferees   = [];
            $divisionNames      = [];
            foreach ($divisions as $division) {
                $divisionReferees               = array_merge($divisionReferees, DivisionReferee::lookupByDivision($division));
                $divisionNames[$division->name] = $division;
            }
            usort($divisionReferees, "compareDivisionDisplayOrder");

            print "
            <br><br>";

            foreach ($divisionNames as $divisionName => $count) {
                $divisions = Division::lookupByName($this->m_controller->m_season, $divisionName);

                $divisionFieldsById = [];
                $divisionReferees   = [];
                $divisionCrews     = [];
                foreach ($divisions as $division) {
                    $divisionFields     = DivisionField::lookupByDivision($division);
                    $divisionReferees   = array_merge($divisionReferees, DivisionReferee::lookupByDivision($division));
                    $divisionCrews     = array_merge($divisionCrews, RefereeCrew::lookupByDivision($division));
                    foreach ($divisionFields as $divisionField) {
                        $divisionFieldsById[$divisionField->id] = $divisionField;
                    }
                }

                $fields                 = [];
                $facilityByFieldsById   = [];
                foreach ($divisionFieldsById as $divisionFieldId => $divisionField) {
                    $fields[] = $divisionField->field;
                    $facilityByFieldsById[$divisionField->field->facility->id][$divisionField->field->id] = $divisionField->field;
                }

                foreach ($gameDates as $gameDate) {
                    $gameTimes = GameTime::lookupByGameDateAndFields($gameDate, $fields);

                    $gameTimesByDayByTimeByField = [];
                    foreach ($gameTimes as $gameTime) {
                        if (isset($gameTime->game)) {
                            $gameTimesByDayByTimeByField[$gameDate->day][$gameTime->startTime][$gameTime->field->id] = $gameTime;
                        }
                    }

                    foreach ($gameTimesByDayByTimeByField as $day => $gameTimesByTimeByField) {
                        $this->printRefereeForm($divisionName, $gameDate, $gameTimesByTimeByField, $facilityByFieldsById,
                            $divisionReferees);
                    }
                }
            }
        }
    }

    /**
     * @param int       $filterDivisionName
     * @param int       $filterGameDateId
     * @param int       $sessionId
     */
    private function printSelectors($filterDivisionName, $filterGameDateId, $sessionId)
    {
        $currentDay = $filterGameDateId == 0 ? '' : GameDate::lookupById($filterGameDateId)->day;

        print "
            <table valign='top' align='center' width='625' border='1' cellpadding='5' cellspacing='0'>
                    </td><td bgcolor='" . View_Base::VIEW_COLOR . "'>";

        $this->printViewSelector($currentDay, $filterDivisionName, $sessionId);

        print "
                    </td>
                </tr>
            </table>";
    }

    /**
     * @param string    $currentDay
     * @param int       $filterDivisionName
     * @param int       $sessionId
     */
    private function printViewSelector($currentDay, $filterDivisionName, $sessionId)
    {
        print "
                    <table valign='top' align='center' width='300' border='0' cellpadding='5' cellspacing='0'>
                        <form method='post' action='" . self::REFEREE_PREVIEW_PAGE . $this->m_urlParams . "'>";

        $this->printDivisionSelectorByName($filterDivisionName, false, true, false);
        $gameDateSelector = $this->getGameDateSelector();
        $this->displaySelector('GameDate', View_Base::GAME_DATE_ID,
            '', $gameDateSelector, $currentDay);

        // Print Filter button and end form
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
     * @param string            $divisionName
     * @param GameDate          $gameDate
     * @param array             $gameTimesByTimeByField
     * @param array             $facilityByFieldsById
     * @param DivisionReferee[] $divisionReferees
     */
    private function printRefereeForm($divisionName, $gameDate, $gameTimesByTimeByField, $facilityByFieldsById, $divisionReferees)
    {
        // Get unique list of referees ordered by name
        $refereesByName = [];
        foreach ($divisionReferees as $divisionReferee) {
            $refereesByName[$divisionReferee->referee->shortName] = $divisionReferee->referee;
        }
        ksort($refereesByName);

        print "
                <p align='center'>Boys and Girls $divisionName for $gameDate->day</p>";

        print "
				<table id='frame-table' align='center'><tr><td valign='top' style='border: none'>";

        $this->printRefereeScheduleTable($facilityByFieldsById, $gameTimesByTimeByField);

        print "
                </td></tr></table>";
    }

    /**
     * @param array     $facilityByFieldsById
     * @param array     $gameTimesByTimeByField
     */
    private function printRefereeScheduleTable($facilityByFieldsById, $gameTimesByTimeByField)
    {
        print "
                <table id='frame-table' border='1' valign='top' align='right' width='auto'>
                <tbody>
                    <tr bgcolor='white'>
                        <th colspan='2''>&nbsp</th>";

        foreach ($facilityByFieldsById as $facilityId => $fieldsById) {
            foreach ($fieldsById as $fieldId => $field) {
                $facilityName = $field->facility->name;
                $fieldName = $field->name;
                print "
                        <th nowrap align='center'>$facilityName<br>$fieldName</th>";
            }

            // One stand-by for each facility
            print "
                        <th nowrap align='center'>Stand-By</th>";
        }

        print "
                    </tr>";

        foreach ($gameTimesByTimeByField as $time => $gameTimesByField) {
            $time = substr($time, 0, 5);

            // Print Game Title Row
            $this->printRefereeRow($time, $facilityByFieldsById, $gameTimesByField, Controller_Api_RefAssign::TITLE_ROW);

            // Print Center Referee Row
            $this->printRefereeRow(NULL, $facilityByFieldsById, $gameTimesByField, Controller_Api_RefAssign::CENTER_ROW);

            // Print AR1 Row
            $this->printRefereeRow(NULL, $facilityByFieldsById, $gameTimesByField, Controller_Api_RefAssign::AR1_ROW);

            // Print AR2 Row
            $this->printRefereeRow(NULL, $facilityByFieldsById, $gameTimesByField, Controller_Api_RefAssign::AR2_ROW);

            // Print Mentor Row
            $this->printRefereeRow(NULL, $facilityByFieldsById, $gameTimesByField, Controller_Api_RefAssign::MENTOR_ROW);
        }

        print "
                </tbody>
                </table>";
    }

    /**
     * @param string    $time - NULL if cell should be skipped
     * @param array     $facilityByFieldsById
     * @param array     $gameTimesByField
     * @param string    $rowType - self const values
     */
    private function printRefereeRow($time, $facilityByFieldsById, $gameTimesByField, $rowType)
    {
        print "
                    <tr>";

        if (isset($time)) {
            print "
                        <td align='center' rowspan='5'><strong>$time</strong></td>";
        }

        $entry = substr($rowType, 0, 1);
        $entry = $rowType == Controller_Api_RefAssign::TITLE_ROW ? "Role" : $entry;
        print "
                        <td align='center'><strong>$entry</strong></td>";

        foreach ($facilityByFieldsById as $facilityId => $fieldsById) {
            $facility       = null;
            $gameDate       = null;
            $gameTime       = null;
            $divisionName   = null;
            foreach ($fieldsById as $fieldId => $field) {
                $entry              = "&nbsp";
                $bgcolor            = '';
                $title              = '';
                $facility           = isset($facility) ? $facility : $field->facility;

                if (isset($gameTimesByField[$fieldId])) {
                    $gameTime = $gameTimesByField[$fieldId];
                    $gameDate = isset($gameDate) ? $gameDate : $gameTime->gameDate;

                    if (isset($gameTime->game)) {
                        $game               = $gameTime->game;
                        $gender             = $game->flight->schedule->division->gender;
                        $bgcolor            = $gender == 'Boys' ? "bgcolor='lightblue'" : "bgcolor='lightyellow'";
                        $gameReferees       = GameReferee::lookupByGame($game);
                        $refereeName        = "&nbsp";
                        $title              = "GameId:$game->id";
                        $divisionName       = isset($divisionName) ? $divisionName : $game->schedule->division->name;

                        $refereesByRole = [];
                        foreach ($gameReferees as $gameReferee) {
                            $refereesByRole[$gameReferee->role][] = $gameReferee->referee;
                        }

                        switch ($rowType) {
                            case Controller_Api_RefAssign::TITLE_ROW:
                                if ((isset($game->homeTeam))) {
                                    $entry = $game->homeTeam->nameId . " v " . $game->visitingTeam->nameId;
                                } else if ($game->title != '') {
                                    $entry = $game->title;
                                }
                                break;

                            case Controller_Api_RefAssign::CENTER_ROW:
                            case Controller_Api_RefAssign::AR1_ROW:
                            case Controller_Api_RefAssign::AR2_ROW:
                            case Controller_Api_RefAssign::MENTOR_ROW:
                                $role = GameReferee::getRoleFromRowType($rowType);
                                if (isset($refereesByRole[$role])) {
                                    $refereeName = $refereesByRole[$role][0]->shortName;
                                }
                                $entry = "$refereeName";
                                break;

                            default:
                                $entry              = "&nbsp";
                                break;
                        }
                    }
                }

                print "
                        <td nowrap align='left' title='$title' $bgcolor>$entry</td>";
            }

            // Standby cells
            $bgcolor            = "bgcolor='#FEDDDF'";
            $standbyReferees    = [];
            $refereeName        = "&nbsp";
            $entry              = "&nbsp";

            if ($rowType != Controller_Api_RefAssign::TITLE_ROW and
                isset($facility) and isset($divisionName) and isset($gameTime)) {
                $standbyReferees = StandbyReferee::lookupByStartTime(
                    $facility,
                    $gameDate,
                    $divisionName,
                    $gameTime->startTime);
            }

            switch ($rowType) {
                case Controller_Api_RefAssign::TITLE_ROW:
                    $entry = 'Stand-By';
                    break;

                case Controller_Api_RefAssign::CENTER_ROW:
                case Controller_Api_RefAssign::AR1_ROW:
                case Controller_Api_RefAssign::AR2_ROW:
                case Controller_Api_RefAssign::MENTOR_ROW:
                    $role = GameReferee::getRoleFromRowType($rowType);
                    foreach ($standbyReferees as $standbyReferee) {
                        if ($standbyReferee->role == $role) {
                            $refereeName = $standbyReferee->referee->shortName;
                        }
                        $entry .= "$refereeName";
                    }
                    break;
                default:
                    $entry = "&nbsp";
                    break;
            }

            print "
                        <td nowrap align='left' $bgcolor>$entry</td>";
        }

        print "
                    </tr>";
    }
}