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
class View_AdminReferee_Schedule extends View_AdminReferee_Base {
    /** @var  Controller_AdminReferee_Schedule */
    protected $m_controller;

    /**
     * @brief Construct the View
     *
     * @param Controller_AdminReferee_Schedule $controller - Controller that contains data used when rendering this view.
     */
    public function __construct($controller) {
        $this->m_controller = $controller;
        parent::__construct(self::REFEREE_SCHEDULE_PAGE, $controller);
    }

    /**
     * @brief Render data for display on the page.
     */
    public function render()
    {
        $filterDivisionName         = $this->m_controller->m_filterDivisionName;
        $filterGameDateId           = $this->m_controller->m_filterGameDateId;
        $filterRefereeDisplayType   = $this->m_controller->m_filterRefereeDisplayType;
        $sessionId                  = $this->m_controller->getSessionId();

        $messageString  = $this->m_controller->m_messageString;
        if (!empty($messageString)) {
            print "
                <p style='color: green' align='center'><strong>$messageString</strong></p><br>";
        }

        $this->printSelectors($filterDivisionName, $filterGameDateId, $filterRefereeDisplayType, $sessionId);

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
                            $gameTimesByDayByTimeByField[$gameDate->day][$gameTime->actualStartTime][$gameTime->field->id] = $gameTime;
                        }
                    }

                    foreach ($gameTimesByDayByTimeByField as $day => $gameTimesByTimeByField) {
                        if ($filterRefereeDisplayType == View_Base::REFEREE_BY_CREW) {
                            $this->printRefereeCrewForm($sessionId, $divisionName, $gameDate, $gameTimesByTimeByField, $facilityByFieldsById,
                                $divisionCrews);
                        } else {
                            $this->printRefereeForm($sessionId, $divisionName, $gameDate, $gameTimesByTimeByField, $facilityByFieldsById,
                                $divisionReferees);
                        }
                    }
                }
            }
        }
    }

    /**
     * @param int       $filterDivisionName
     * @param int       $filterGameDateId
     * @param string    $filterRefereeDisplayType
     * @param int       $sessionId
     */
    private function printSelectors($filterDivisionName, $filterGameDateId, $filterRefereeDisplayType, $sessionId)
    {
        $currentDay = $filterGameDateId == 0 ? '' : GameDate::lookupById($filterGameDateId)->day;

        print "
            <table valign='top' align='center' width='625' border='1' cellpadding='5' cellspacing='0'>
                    </td><td bgcolor='" . View_Base::VIEW_COLOR . "'>";

        $this->printViewSelector($currentDay, $filterDivisionName, $filterRefereeDisplayType, $sessionId);

        print "
                    </td>
                </tr>
            </table>";
    }

    /**
     * @param string    $currentDay
     * @param int       $filterDivisionName
     * @param string    $filterRefereeDisplayType
     * @param int       $sessionId
     */
    private function printViewSelector($currentDay, $filterDivisionName, $filterRefereeDisplayType, $sessionId)
    {
        print "
                    <table valign='top' align='center' width='300' border='0' cellpadding='5' cellspacing='0'>
                        <form method='post' action='" . self::REFEREE_SCHEDULE_PAGE . $this->m_urlParams . "'>";

        $this->printDivisionSelectorByName($filterDivisionName, false, true, false);
        $gameDateSelector = $this->getGameDateSelector();
        $this->displaySelector('GameDate', View_Base::GAME_DATE_ID,
            '', $gameDateSelector, $currentDay);

        $refereeDisplayTypes = [];
        $refereeDisplayTypes[View_Base::REFEREE_BY_NAME] = View_Base::REFEREE_BY_NAME;
        $refereeDisplayTypes[View_Base::REFEREE_BY_CREW] = View_Base::REFEREE_BY_CREW;
        $this->displayRadioSelector("RefereeType", View_Base::REFEREE_DISPLAY_TYPE, $refereeDisplayTypes, $filterRefereeDisplayType);

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
     * @param int               $sessionId
     * @param string            $divisionName
     * @param GameDate          $gameDate
     * @param array             $gameTimesByTimeByField
     * @param array             $facilityByFieldsById
     * @param DivisionReferee[] $divisionReferees
     */
    private function printRefereeForm($sessionId, $divisionName, $gameDate, $gameTimesByTimeByField, $facilityByFieldsById, $divisionReferees)
    {
        // Get unique list of referees ordered by name
        $refereesByName = [];
        foreach ($divisionReferees as $divisionReferee) {
            $refereesByName[$divisionReferee->referee->shortName] = $divisionReferee->referee;
        }
        ksort($refereesByName);

        // Main Container Div
        //   Redips-drag Div
        //     Left Container
        //       Table with clone-able content (table1)
        //       Trash cell to delete entry
        //     Right Container
        //       Table with content cloned from table1 or pre-populated (table2)

        print "
                <div id='message' style='background-color: darkgray'>Drag referee to open slot or from game slot into trash</div>
                <div id='s' data-sessionid='$sessionId'/>
                <p align='center'>Boys and Girls $divisionName for $gameDate->day</p>";

        $this->printButtons($sessionId, $divisionName, $gameDate->id);

        print "
			<!-- tables inside this DIV could have drag-able content -->
			<div id='redips-drag'>
	
				<table id='frame-table' align='center'><tr><td valign='top' style='border: none'>";

        $this->printRefereeTable($refereesByName);

        print "
            </td><td valign='top' style='border: none'>";

        $this->printRefereeScheduleTable($facilityByFieldsById, $gameTimesByTimeByField);

        print "
                </td></tr></table>
			</div><!-- drag container -->";
    }

    /**
     * @brief Print the form to display games by field and time
     *
     * @param int               $sessionId
     * @param string            $divisionName
     * @param GameDate          $gameDate
     * @param array             $gameTimesByTimeByField
     * @param array             $facilityByFieldsById
     * @param RefereeCrew[]     $refereeCrews
     */
    private function printRefereeCrewForm($sessionId, $divisionName, $gameDate, $gameTimesByTimeByField, $facilityByFieldsById, $refereeCrews)
    {
        // Get unique list of referee crews ordered by name
        $crewByName = [];
        foreach ($refereeCrews as $refereeCrew) {
            $crewByName[$refereeCrew->name] = $refereeCrew;
        }
        ksort($crewByName);

        print "
                <div id='message' style='background-color: darkgray'>Drag referee team to open slot or from game slot into trash</div>
                <div id='s' data-sessionid='$sessionId'/>
                <p align='center'>Boys and Girls $divisionName for $gameDate->day</p>";

        // $this->printButtons($sessionId, $divisionName, $gameDate->id);

        print "
			<!-- tables inside this DIV could have drag-able content -->
			<div id='redips-drag'>
	
				<table id='frame-table' align='center'><tr><td valign='top' style='border: none'>";

        $this->printRefereeCrewTable($crewByName);

        print "
            </td><td valign='top' style='border: none'>";

        $this->printRefereeCrewScheduleTable($facilityByFieldsById, $gameTimesByTimeByField);

        print "
                </td></tr></table>
			</div><!-- drag container -->";
    }

    /**
     * @param int       $sessionId
     * @param string    $divisionName
     * @param int       $gameDateId
     */
    private function printButtons($sessionId, $divisionName, $gameDateId)
    {
        print "
                    <table valign='top' align='center' width='300' border='0' cellpadding='5' cellspacing='0'>
                    <form method='post' action='" . self::REFEREE_SCHEDULE_PAGE . $this->m_urlParams . "'>
                        <tr>";

        $this->printPopulate($sessionId, $divisionName, $gameDateId);
        $this->printPublish($sessionId, $divisionName, $gameDateId);
        $this->printClear($sessionId, $divisionName, $gameDateId);

        print "
                        </tr>
                    </form>
                    </table>";
    }

    /**
     * @param int       $sessionId
     * @param string    $divisionName
     * @param int       $gameDateId
     */
    private function printPopulate($sessionId, $divisionName, $gameDateId)
    {
        print "
                            <td align='center'>
                                <input title='Auto-populate games with qualified referees' style='background-color: yellow' name='" . View_Base::SUBMIT . "' type='submit' value='" . View_Base::POPULATE . "'>
                                <input type='hidden' id='" . View_Base::FILTER_DIVISION_NAME . "' name='" . View_Base::FILTER_DIVISION_NAME . "' value='$divisionName'>
                                <input type='hidden' id='" . View_Base::GAME_DATE_ID . "' name='" . View_Base::GAME_DATE_ID . "' value='$gameDateId'>
                                <input type='hidden' id='sessionId' name='sessionId' value='$sessionId'>
                            </td>";
    }

    /**
     * @param int       $sessionId
     * @param string    $divisionName
     * @param int       $gameDateId
     */
    private function printPublish($sessionId, $divisionName, $gameDateId)
    {
        print "
                            <td align='center'>
                                <input title='Publish schedule and email referees to accept game assignments'
                                    style='background-color: yellow' name='" . View_Base::SUBMIT . "' type='submit' value='" . View_Base::PUBLISH . "'>
                                <input type='hidden' id='" . View_Base::FILTER_DIVISION_NAME . "' name='" . View_Base::FILTER_DIVISION_NAME . "' value='$divisionName'>
                                <input type='hidden' id='" . View_Base::GAME_DATE_ID . "' name='" . View_Base::GAME_DATE_ID . "' value='$gameDateId'>
                                <input type='hidden' id='sessionId' name='sessionId' value='$sessionId'>
                            </td>";
    }

    /**
     * @param int       $sessionId
     * @param string    $divisionName
     * @param int       $gameDateId
     */
    private function printClear($sessionId, $divisionName, $gameDateId)
    {
        print "
                            <td align='center'>
                                <input title='Clear the referee schedule and start over' style='background-color: salmon' name='" . View_Base::SUBMIT . "' type='submit' value='" . View_Base::CLEAR . "'>
                                <input type='hidden' id='" . View_Base::FILTER_DIVISION_NAME . "' name='" . View_Base::FILTER_DIVISION_NAME . "' value='$divisionName'>
                                <input type='hidden' id='" . View_Base::GAME_DATE_ID . "' name='" . View_Base::GAME_DATE_ID . "' value='$gameDateId'>
                                <input type='hidden' id='sessionId' name='sessionId' value='$sessionId'>
                            </td>";
    }

    /**
     * @param array $refereesByName
     */
    private function printRefereeTable($refereesByName)
    {
        $maxRefereesPerColumn   = 30;
        $maxRefereesPerRow      = ceil(count($refereesByName) / $maxRefereesPerColumn);
        print "
                <table id='frame-table' valign='top' align='left' width='auto'>
                <tbody>
                    <tr bgcolor='white'>
                        <th colspan='$maxRefereesPerRow' align='center' class='redips-mark dark'>&nbsp<br>Referees</th>
                    </tr>
                    <tr>";

        $columnCount = 0;
        foreach ($refereesByName as $name => $referee) {
            // $redipsDragStart    = "<div id='${rowType}_${gameId}_0'>";
            if ($columnCount >= $maxRefereesPerRow) {
                print "
                    </tr>
                    <tr>";
                $columnCount = 0;
            }

            $fullName = $referee->name;
            print "
                    <td id='xxx_0_$referee->id' title='$fullName' class='redips-dark'><div id='divxxx_0_$referee->id' class='redips-drag redips-clone'>$name</div></td>";

            $columnCount += 1;
        }

        print "
                    </tr>
                    <tr>
                        <td colspan='$maxRefereesPerRow' align='center' class='redips-trash'>Trash</td>
                    </tr>
                </tbody>
                </table>";
    }

    /**
     * @param array $crewsByName
     */
    private function printRefereeCrewTable($crewsByName)
    {
        $maxCrewsPerColumn   = 10;
        $maxCrewsPerRow      = ceil(count($crewsByName) / $maxCrewsPerColumn);
        print "
                <table id='frame-table' valign='top' align='left' width='auto'>
                <tbody>
                    <tr bgcolor='white'>
                        <th colspan='$maxCrewsPerRow' align='center' class='redips-mark dark'>&nbsp<br>Referee Crews</th>
                    </tr>
                    <tr>";

        $columnCount = 0;
        foreach ($crewsByName as $name => $refereeCrew) {
            if ($columnCount >= $maxCrewsPerRow) {
                print "
                    </tr>
                    <tr>";
                $columnCount = 0;
            }

            $fullName = $refereeCrew->centerReferee->name . " (" . $refereeCrew->centerReferee->badgeId . ")" . "\n"
                . $refereeCrew->assistantReferee1->name . " (" . $refereeCrew->assistantReferee1->badgeId . ")" . "\n"
                . $refereeCrew->assistantReferee2->name . " (" . $refereeCrew->assistantReferee2->badgeId . ")";
            print "
                    <td id='xxx_0_$refereeCrew->id" . "_crew' title='$fullName' class='redips-dark'><div id='divxxx_0_$refereeCrew->id" . "_crew' class='redips-drag redips-clone'>$name</div></td>";

            $columnCount += 1;
        }

        print "
                    </tr>
                    <tr>
                        <td colspan='$maxCrewsPerRow' align='center' class='redips-trash'>Trash</td>
                    </tr>
                </tbody>
                </table>";
    }

    /**
     * @param array     $facilityByFieldsById
     * @param array     $gameTimesByTimeByField
     */
    private function printRefereeScheduleTable($facilityByFieldsById, $gameTimesByTimeByField)
    {
        print "
                <table id='frame-table' valign='top' align='right' width='auto'>
                <tbody>
                    <tr bgcolor='white'>
                        <th colspan='2' class='redips-mark dark'>&nbsp</th>";

        foreach ($facilityByFieldsById as $facilityId => $fieldsById) {
            foreach ($fieldsById as $fieldId => $field) {
                $facilityName = $field->facility->name;
                $fieldName = $field->name;
                print "
                        <th nowrap align='center' class='redips-mark dark'>$facilityName<br>$fieldName</th>";
            }

            // One stand-by for each facility
            print "
                        <th nowrap align='center' class='redips-mark dark'>Stand-By</th>";
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
     * @param array     $facilityByFieldsById
     * @param array     $gameTimesByTimeByField
     */
    private function printRefereeCrewScheduleTable($facilityByFieldsById, $gameTimesByTimeByField)
    {
        print "
                <table id='frame-table' valign='top' align='right' width='auto'>
                <tbody>
                    <tr bgcolor='white'>
                        <th colspan='1' class='redips-mark dark'>&nbsp</th>";
        foreach ($facilityByFieldsById as $facilityId => $fieldsById) {
            foreach ($fieldsById as $fieldId => $field) {
                $facilityName = $field->facility->name;
                $fieldName = $field->name;
                print "
                        <th nowrap align='center' class='redips-mark dark'>$facilityName<br>$fieldName</th>";
            }

            print "
                        <th nowrap align='center' class='redips-mark dark'>Stand-By</th>";
        }

        print "
                    </tr>";

        foreach ($gameTimesByTimeByField as $time => $gameTimesByField) {
            $time = substr($time, 0, 5);

            // Print Game Title Row
            $this->printRefereeCrewRow($time, $facilityByFieldsById, $gameTimesByField, Controller_Api_RefAssign::TITLE_ROW);

            // Print Center Referee Row
            $this->printRefereeCrewRow(NULL, $facilityByFieldsById, $gameTimesByField, Controller_Api_RefAssign::CREW_ROW);
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
                        <td align='center' rowspan='5' class='redips-mark dark'><strong>$time</strong></td>";
        }

        $entry = substr($rowType, 0, 1);
        $entry = $rowType == Controller_Api_RefAssign::TITLE_ROW ? "Role" : $entry;
        print "
                        <td align='center' class='redips-mark dark'><strong>$entry</strong></td>";

        foreach ($facilityByFieldsById as $facilityId => $fieldsById) {
            $facility       = null;
            $gameDate       = null;
            $gameTime       = null;
            $divisionName   = null;
            foreach ($fieldsById as $fieldId => $field) {
                $entry              = "&nbsp";
                $bgcolor            = '';
                $redipsClass        = "class='redips-mark dark'";
                $redipsDragStart    = "";
                $redipsDragEnd      = "";
                $cellId             = '';
                $data               = '';
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
                        $gameId             = $game->id;
                        $cellId             = "id='${rowType}_${gameId}_0'";
                        $redipsDragStart    = "<div id='div${rowType}_${gameId}_0'>";
                        $redipsDragEnd      = "</div>";
                        $refereeName        = '';
                        $title              = "GameId:$game->id";
                        $divisionName       = isset($divisionName) ? $divisionName : $game->schedule->division->name;

                        $refereesByRole = [];
                        foreach ($gameReferees as $gameReferee) {
                            $refereesByRole[$gameReferee->role][] = $gameReferee->referee;
                        }

                        switch ($rowType) {
                            case Controller_Api_RefAssign::TITLE_ROW:
                                $redipsDragStart    = "";
                                $redipsDragEnd      = "";
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
                                $redipsClass    = '';
                                $role           = GameReferee::getRoleFromRowType($rowType);
                                if (isset($refereesByRole[$role])) {
                                    $refereeName        = $refereesByRole[$role][0]->shortName;
                                    $refereeId          = $refereesByRole[$role][0]->id;
                                    $cellId             = "id='${rowType}_${gameId}_${refereeId}'";
                                    $redipsDragStart    = "<div id='div${rowType}_${gameId}_${refereeId}' class='redips-drag'>";
                                }
                                $entry = "$refereeName";
                                break;

                            default:
                                $redipsDragStart    = "";
                                $redipsDragEnd      = "";
                                $entry              = "&nbsp";
                                break;
                        }
                    }
                }

                print "
                        <td $cellId nowrap align='left' title='$title' $bgcolor $redipsClass>${$data}${redipsDragStart}${entry}${redipsDragEnd}</td>";
            }

            // Standby cells
            $bgcolor            = "bgcolor='#FEDDDF'";
            $redipsClass        = "class='redips-mark dark'";
            $redipsDragStart    = '';
            $redipsDragEnd      = '';
            $standbyReferees    = [];
            $gameDateId         = '';
            $startTime          = '';
            $cellId             = '';
            $refereeName        = '';
            $entry              = '';

            if ($rowType != Controller_Api_RefAssign::TITLE_ROW and
                isset($facility) and isset($divisionName) and isset($gameTime)) {
                $startTime  = $gameTime->startTime;
                $gameDateId = $gameTime->gameDate->id;
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
                    $redipsClass    = '';
                    $cellId         = "id='standby_${rowType}_${facilityId}_{$gameDateId}_${divisionName}_${startTime}_0'";
                    $role           = GameReferee::getRoleFromRowType($rowType);
                    foreach ($standbyReferees as $standbyReferee) {
                        if ($standbyReferee->role == $role) {
                            $refereeName        = $standbyReferee->referee->shortName;
                            $refereeId          = $standbyReferee->referee->id;
                            $redipsDragStart    = "<div id='div_standby_${rowType}_${facilityId}_{$gameDateId}_${divisionName}_${startTime}_${refereeId}' class='redips-drag'>";
                            $redipsDragEnd      = "</div>";
                        }
                        $entry .= "${redipsDragStart}${refereeName}${redipsDragEnd}";
                    }
                    break;
                default:
                    $entry          = "&nbsp";
                    break;
            }

            print "
                        <td $cellId nowrap align='left' $bgcolor $redipsClass>$entry</td>";
        }

        print "
                    </tr>";
    }

    /**
     * @param string    $time - NULL if cell should be skipped
     * @param array     $facilityByFieldsById
     * @param array     $gameTimesByField
     * @param string    $rowType - self const values
     */
    private function printRefereeCrewRow($time, $facilityByFieldsById, $gameTimesByField, $rowType)
    {
        print "
                    <tr>";

        if (isset($time)) {
            print "
                        <td align='center' rowspan='2' class='redips-mark dark'><strong>$time</strong></td>";
        }

        foreach ($facilityByFieldsById as $facilityId => $fieldsById) {
            foreach ($fieldsById as $fieldId => $field) {
                $entry              = "&nbsp";
                $bgcolor            = '';
                $redipsClass        = "class='redips-mark dark'";
                $redipsDragStart    = "";
                $redipsDragEnd      = "";
                $cellId             = '';
                $data               = '';
                $title              = '';
                if (isset($gameTimesByField[$fieldId])) {
                    $gameTime = $gameTimesByField[$fieldId];
                    if (isset($gameTime->game)) {
                        $game               = $gameTime->game;
                        $gender             = $game->flight->schedule->division->gender;
                        $bgcolor            = $gender == 'Boys' ? "bgcolor='lightblue'" : "bgcolor='lightyellow'";
                        // $refereeCrew       = RefereeCrew::lookupByGame($game);
                        /** @var RefereeCrew $refereeCrew */
                        $refereeCrew        = isset($game->refereeCrew) ? $game->refereeCrew : null;
                        $gameReferees       = isset($game->refereeCrew) ? null : GameReferee::lookupByGame($game);
                        $gameId             = $game->id;
                        $cellId             = "id='${rowType}_${gameId}_0_crew'";
                        $redipsDragStart    = "<div id='div${rowType}_${gameId}_0_crew'>";
                        $redipsDragEnd      = "</div>";
                        $refereeCrewName    = '';
                        $title              = "GameId:$game->id";

                        switch ($rowType) {
                            case Controller_Api_RefAssign::TITLE_ROW:
                                $redipsDragStart    = "";
                                $redipsDragEnd      = "";
                                if ((isset($game->homeTeam))) {
                                    $entry = $game->homeTeam->nameId . " v " . $game->visitingTeam->nameId;
                                } else if ($game->title != '') {
                                    $entry = $game->title;
                                }
                                break;

                            case Controller_Api_RefAssign::CREW_ROW:
                                $redipsClass    = '';
                                if (isset($refereeCrew)) {
                                    $refereeCrewName   = $refereeCrew->name;
                                    $refereeCrewId     = $refereeCrew->id;
                                    $cellId             = "id='${rowType}_${gameId}_${refereeCrewId}_crew'";
                                    $redipsDragStart    = "<div id='div${rowType}_${gameId}_${refereeCrewId}_crew' class='redips-drag'>";
                                } else if (count($gameReferees) > 0) {
                                    $redipsDragStart    = "";
                                    $redipsDragEnd      = "";
                                    $redipsClass        = "class='redips-mark dark'";
                                    $bgcolor            = "bgcolor='salmon'";
                                    foreach ($gameReferees as $gameReferee) {
                                        $refereeCrewName .= $refereeCrewName != '' ? "<br>" : "";
                                        $refereeCrewName .= $gameReferee->role . ":" . $gameReferee->referee->shortName;
                                    }
                                }
                                $entry = "$refereeCrewName";
                                break;

                            default:
                                $redipsDragStart    = "";
                                $redipsDragEnd      = "";
                                $entry              = "&nbsp";
                                break;
                        }
                    }
                }

                print "
                        <td $cellId nowrap align='left' title='$title' $bgcolor $redipsClass>${$data}${redipsDragStart}${entry}${redipsDragEnd}</td>";
            }

            // Standby cells
            $bgcolor            = "bgcolor='#FEDDDF'";
            $redipsClass        = "class='redips-mark dark'";
            $redipsDragStart    = '';
            $redipsDragEnd      = '';
            switch ($rowType) {
                case Controller_Api_RefAssign::TITLE_ROW:
                    $entry = 'Stand-By';
                    break;

                case Controller_Api_RefAssign::CREW_ROW:
                    $redipsClass    = '';
                    $entry          = "&nbsp";
                    break;

                default:
                    $redipsClass    = '';
                    $entry          = "&nbsp";
                    break;
            }
            print "
                        <td nowrap align='left' $bgcolor $redipsClass>${redipsDragStart}${entry}${redipsDragEnd}</td>";
        }

        print "
                    </tr>";
    }
}