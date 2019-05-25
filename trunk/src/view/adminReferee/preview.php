<?php

use \DAG\Domain\Schedule\Division;
use \DAG\Domain\Schedule\DivisionReferee;
use \DAG\Domain\Schedule\GameDate;
use \DAG\Domain\Schedule\DivisionField;
use \DAG\Domain\Schedule\GameTime;

/**
 * @brief Preview referee schedule for a specified day
 */
class View_AdminReferee_Preview extends View_AdminReferee_Base {
    const TITLE_ROW     = 'title';
    const CENTER_ROW    = 'C';
    const AR_ROW        = 'AR';
    const MENTOR_ROW    = 'Mentor';

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
        $filterDivisionName = $this->m_controller->m_filterDivisionName;
        $filterGameDateId   = $this->m_controller->m_filterGameDateId;
        $sessionId          = $this->m_controller->getSessionId();

        $messageString  = $this->m_controller->m_messageString;
        if (!empty($messageString)) {
            print "
                <p style='color: green' align='center'><strong>$messageString</strong></p><br>";
        }

        $this->printSelectors($filterDivisionName, $filterGameDateId, $sessionId);

        if ($filterDivisionName != '' and $filterGameDateId != 0) {
            $gameDates[] = GameDate::lookupById($filterGameDateId);

            if ($filterDivisionName == 'All') {
                $divisions   = Division::lookupBySeason($this->m_controller->m_season);
            } else {
                $divisions   = Division::lookupByName($this->m_controller->m_season, $filterDivisionName);
            }

            $divisionReferees   = [];
            $divisionNames      = [];
            foreach ($divisions as $division) {
                if (!$division->isScoringTracked) {
                    continue;
                }
                $divisionReferees               = array_merge($divisionReferees, DivisionReferee::lookupByDivision($division));
                $divisionNames[$division->name] = $division;
            }
            usort($divisionReferees, "compareDivisionDisplayOrder");

            print "
            <br><br>";

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
                        $this->printRefereeSchedule($divisionName, $day, $gameTimesByTimeByField, $fieldsById);
                    }
                }
            }
        }
    }

    /**
     * @param int   $filterDivisionName
     * @param int   $filterGameDateId
     * @param int   $sessionId
     */
    private function printSelectors($filterDivisionName, $filterGameDateId, $sessionId)
    {
        $currentDay = $filterGameDateId == 0 ? '' : GameDate::lookupById($filterGameDateId)->day;

        print "
            <table valign='top' align='center' width='625' border='1' cellpadding='5' cellspacing='0'>
                <tr><td bgcolor='" . View_Base::VIEW_COLOR . "'>";

        $this->printViewSelector($currentDay, $filterDivisionName, $sessionId);

        print "
                    </td>
                </tr>
            </table>";
    }

    /**
     * @param string    $currentDay
     * @param int       $filterDivisionName
     * @param int       $filterGameDateId
     * @param int       $sessionId
     */
    private function printViewSelector($currentDay, $filterDivisionName, $sessionId)
    {
        print "
                    <table valign='top' align='center' width='300' border='0' cellpadding='5' cellspacing='0'>
                        <form method='post' action='" . self::REFEREE_PREVIEW_PAGE . $this->m_urlParams . "'>";

        $this->printDivisionSelectorByName($filterDivisionName, false, true, true);
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
     * @param string $divisionName
     * @param string $day
     * @param array $gameTimesByTimeByField
     * @param array $fieldsById
     */
    private function printRefereeSchedule($divisionName, $day, $gameTimesByTimeByField, $fieldsById)
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
                        <th nowrap align='center'>Stand-By</th>";

        print "
                    </tr>";

        foreach ($gameTimesByTimeByField as $time => $gameTimesByField) {
            $time = substr($time, 0, 5);

            // Print Game Title Row
            $this->printRefereeRow($time, $fieldsById, $gameTimesByField, self::TITLE_ROW);

            // Print Center Referee Row
            $this->printRefereeRow(NULL, $fieldsById, $gameTimesByField, self::CENTER_ROW);

            // Print AR1 Row
            $this->printRefereeRow(NULL, $fieldsById, $gameTimesByField, self::AR_ROW);

            // Print AR2 Row
            $this->printRefereeRow(NULL, $fieldsById, $gameTimesByField, self::AR_ROW);

            // Print Mentor Row
            $this->printRefereeRow(NULL, $fieldsById, $gameTimesByField, self::MENTOR_ROW);
        }

        print "
                </table><br><br>";
    }

    /**
     * @param string    $time - NULL if cell should be skipped
     * @param array     $fieldsById
     * @param array     $gameTimesByField
     * @param string    $rowType - self const values
     */
    private function printRefereeRow($time, $fieldsById, $gameTimesByField, $rowType)
    {
        print "
                    <tr>";

        if (isset($time)) {
            print "
                        <td align='center' rowspan='5'>$time</td>";
        }

        foreach ($fieldsById as $fieldId => $field) {
            $entry = "&nbsp";
            $bgcolor = '';
            if (isset($gameTimesByField[$fieldId])) {
                $gameTime = $gameTimesByField[$fieldId];
                if (isset($gameTime->game)) {
                    $game = $gameTime->game;
                    $gender = $game->flight->schedule->division->gender;
                    $bgcolor = $gender == 'Boys' ? "bgcolor='lightblue'" : "bgcolor='lightyellow'";

                    switch ($rowType) {
                        case self::TITLE_ROW:
                            if ((isset($game->homeTeam))) {
                                $entry = $game->homeTeam->nameId . " v " . $game->visitingTeam->nameId;
                            } else if ($game->title != '') {
                                $entry = $game->title;
                            }
                            break;

                        case self::CENTER_ROW:
                        case self::AR_ROW:
                            $entry = "$rowType:&nbsp";
                            break;

                        case self::MENTOR_ROW:
                        default:
                            $entry = "&nbsp";
                            break;
                    }
                }
            }

            print "
                        <td nowrap align='left' $bgcolor>$entry</td>";
        }

        // Standby cells
        $bgcolor = "bgcolor='#FEDDDF'";
        switch ($rowType) {
            case self::TITLE_ROW:
                $entry = 'Stand-By';
                break;

            case self::CENTER_ROW:
            case self::AR_ROW:
                $entry = "$rowType:&nbsp";
                break;

            case self::MENTOR_ROW:
            default:
                $entry = "&nbsp";
                break;
        }
        print "
                        <td nowrap align='left' $bgcolor>$entry</td>";

        print "
                    </tr>";
    }
}