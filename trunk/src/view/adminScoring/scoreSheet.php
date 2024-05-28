<?php

use \DAG\Domain\Schedule\Coach;
use \DAG\Domain\Schedule\GameDate;
use \DAG\Domain\Schedule\Facility;
use \DAG\Domain\Schedule\Field;
use \DAG\Domain\Schedule\GameTime;

/**
 * @brief Show the Score Sheet Page
 */
class View_AdminScoring_ScoreSheet extends View_AdminScoring_Base
{
    /**
     * @brief Construct the View
     *
     * @param Controller_Base $controller - Controller that contains data used when rendering this view.
     */
    public function __construct($controller)
    {
        parent::__construct(self::SCORING_SCORE_SHEET_PAGE, $controller);
    }

    /**
     * @brief Render data for display on the page.
     */
    public function renderPage()
    {
        $sessionId          = $this->m_controller->getSessionId();
        $gameDateSelector   = $this->getGameDateSelector();

        $messageString = $this->m_controller->m_messageString;
        if (!empty($messageString)) {
            print "
                <p style='color: green' align='center'><strong>$messageString</strong></p><br>";
        }

        print "
            <table valign='top' align='center' width='400' border='1' cellpadding='5' cellspacing='0'>
                <tr>
                    <td valign='top' bgcolor='" . View_Base::VIEW_COLOR . "'>";

        $this->printSelectFacilityAndDay($sessionId, $gameDateSelector);

        print "
                    </td>
                </tr>
            </table>
            <br><br>";

        switch ($this->m_controller->m_scoringType) {
            case Controller_AdminScoring_ScoreSheet::GAME_DISPLAY_FOR_SCORING:
                if (isset($this->m_controller->m_facility)) {
                    $this->printGamesForFacilityForDay($this->m_controller->m_facility, $this->m_controller->m_gameDate, true);
                } else {
                    $this->printGamesForForDay($this->m_controller->m_gameDate);
                }
                break;
        }
    }

    /**
     * @brief Print the form to select the facility and date to display games for score keeping.
     *        - List of Facilities
     *        - Day to enter/update scores
     *
     * @param int   $sessionId          - Session Identifier
     * @param array $gameDateSelector   - List of gameDateId => day
     */
    private function printSelectFacilityAndDay($sessionId, $gameDateSelector)
    {
        $facilitySelector       = $this->getFacilitySelector();
        $selectedFacilityName   = isset($this->m_controller->m_facility) ? $this->m_controller->m_facility->name : '';

        print "
            <table valign='top' align='center' border='0' cellpadding='5' cellspacing='0'>
                <tr>
                    <th nowrap align='left' colspan='2'>View Games For Scoring</th>
                </tr>
            <form method='post' action='" . self::SCORING_SCORE_SHEET_PAGE . $this->m_urlParams . "'>";

        $this->displaySelector('Facility:', View_Base::FILTER_FACILITY_ID, '', $facilitySelector, $selectedFacilityName);
        $this->displaySelector('Game Date:', View_Base::GAME_DATE, '', $gameDateSelector, $this->m_controller->m_gameDate->day);

        // Print Update button and end form
        print "
                <tr>
                    <td align='left'>
                        <input style='background-color: yellow' name='" . View_Base::SUBMIT . "' type='submit' value='" . View_Base::ENTER . "'>
                        <input type='hidden' id='" . View_Base::SCORING_TYPE . "' name='" . View_Base::SCORING_TYPE . "' value='" . Controller_AdminScoring_ScoreSheet::GAME_DISPLAY_FOR_SCORING . "'>
                        <input type='hidden' id='sessionId' name='sessionId' value='$sessionId'>
                    </td>
                </tr>
            </form>
            </table>";
    }

    /**
     * @param GameDate  $gameDate
     */
    private function printGamesForForDay($gameDate)
    {
        $facilities = Facility::lookupBySeason($this->m_controller->m_season);
        foreach ($facilities as $facility) {
            $this->printGamesForFacilityForDay($facility, $gameDate, true);
        }
    }

    /**
     * @param Facility  $facility
     * @param GameDate  $gameDate
     * @param bool      $suppressNoGamesMessage
     */
    private function printGamesForFacilityForDay($facility, $gameDate, $suppressNoGamesMessage = false)
    {
        $fields     = Field::lookupByFacility($facility);
        $gameTimes  = GameTime::lookupByGameDateAndFields($gameDate, $fields);
        $gameData   = [];

        foreach ($gameTimes as $gameTime) {
            if (isset($gameTime->game)) {
                $field              = $gameTime->field;
                $game               = $gameTime->game;

                $homeTeamName       = isset($game->homeTeam) ? $game->homeTeam->nameIdWithSeed : "TBD";
                $visitingTeamName   = isset($game->visitingTeam) ? $game->visitingTeam->nameIdWithSeed : "TBD";
                $division           = $game->flight->schedule->division;
                $homeCoachName      = isset($game->homeTeam) ? Coach::lookupByTeam($game->homeTeam)->name : "TBD";
                $visitingCoachName  = isset($game->visitingTeam) ? Coach::lookupByTeam($game->visitingTeam)->name : "TBD";
                $teams['home']      = 'H: ' . $homeTeamName . " " . $homeCoachName;
                $teams['visiting']  = 'V: ' . $visitingTeamName . " " . $visitingCoachName;

                if ($division->isScoringTracked) {
                    $gameTag = isset($game->title) ? $game->id . "<br>" . $game->title : $game->id;
                    $gameData[$gameTime->actualStartTime][$division->nameWithGender][$field->name][$gameTag] = $teams;
                }
            }
        }
        ksort($gameData);

        if (count($gameData) == 0) {
            if (!$suppressNoGamesMessage) {
                print "
                    <p align='center' style='color: red; font-size: medium'>No games being played at $facility->name on $gameDate->day.</p>";
            }
            return;
        }

        $this->printGamesForFacilityForDayHeader($facility, $gameDate);

        $timeCount      = 0;
        $gameCount      = 0;
        $startNewTable  = false;
        foreach ($gameData as $actualStartTime => $divisionData) {
            $startNewTable      = $this->startNewTableIfNecessary($startNewTable, $facility, $gameDate);
            $timePrinted        = false;
            $backgroundColor    = $timeCount % 2 == 0 ? 'white' : 'lightskyblue';
            $fontColor          = $timeCount % 2 == 0 ? 'black' : 'black';
            $timeCount          += 1;
            $timeRowSpan        = 0;

            foreach ($divisionData as $divisionName => $fieldData) {
                $timeRowSpan += count($fieldData) * 2;
            }

            foreach ($divisionData as $divisionName => $fieldData) {
                $startNewTable      = $this->startNewTableIfNecessary($startNewTable, $facility, $gameDate);
                $divisionPrinted    = false;
                $divisionRowSpan    = count($fieldData) * 2;

                foreach ($fieldData as $fieldName => $gameData) {
                    foreach ($gameData as $gameTag => $teams) {
                        $startNewTable      = $this->startNewTableIfNecessary($startNewTable, $facility, $gameDate);
                        $homeTeamData       = $teams['home'];
                        $visitingTeamData   = $teams['visiting'];
                        $style              = "style='background-color: $backgroundColor; color: $fontColor; -webkit-print-color-adjust: exact; height: .5in'";

                        $gameBackgroundColor    = $gameCount % 2 == 0 ? 'lightyellow' : 'white';
                        $gameFontColor          = $gameCount % 2 == 0 ? 'black' : 'black';
                        $gameStyle              = "style='background-color: $gameBackgroundColor; color: $gameFontColor; -webkit-print-color-adjust: exact; height: .3in'";
                        $gameCount              += 1;

                        // Print home team
                        if (!$timePrinted) {
                            $timePrinted        = true;
                            $divisionPrinted    = true;

                            print "
                                <tr style='font-size: medium'>
                                    <td rowspan='$timeRowSpan' $style>$actualStartTime</td>
                                    <td rowspan='$divisionRowSpan' $style>$divisionName</td>";
                        } else if (!$divisionPrinted) {
                            $divisionPrinted = true;

                            print "
                                <tr style='font-size: medium'>
                                    <td rowspan='$divisionRowSpan' $style>$divisionName</td>";
                        } else {
                            print "
                                <tr style='font-size: medium'>";
                        }

                        print "
                                    <td nowrap rowspan='2' $gameStyle>$fieldName</td>
                                    <td rowspan='2' align='center' $gameStyle>$gameTag</td>
                                    <td nowrap $gameStyle>$homeTeamData</td>
                                    <td $gameStyle>&nbsp</td>
                                    <td $gameStyle>&nbsp</td>
                                    <td $gameStyle>&nbsp</td>
                                    <td $gameStyle width='400px'>&nbsp</td>
                                </tr>";

                        // Print visiting team
                        print "
                                <tr style='font-size: medium'>
                                    <td nowrap $gameStyle>$visitingTeamData</td>
                                    <td $gameStyle>&nbsp</td>
                                    <td $gameStyle>&nbsp</td>
                                    <td $gameStyle>&nbsp</td>
                                    <td $gameStyle width='400px'>&nbsp</td>
                                </tr>";

                        // Adjust rowspans for page break accuracy
                        $timeRowSpan        -= 2;
                        $divisionRowSpan    -= 2;

                        // Only print 12 games per page
                        if ($gameCount % 10 == 0) {
                            print "
                                </table>";

                            $startNewTable      = true;
                            $timePrinted        = false;
                            $divisionPrinted    = false;
                        }
                    }
                }
            }
        }

        if (!$startNewTable) {
            print "</table><br>";
        }
    }

    /**
     * @param bool      $startNewTable
     * @param Facility  $facility
     * @param GameDate  $gameDate
     *
     * @return bool
     */
    private function startNewTableIfNecessary($startNewTable, $facility, $gameDate)
    {
        if ($startNewTable) {
            $this->printGamesForFacilityForDayHeader($facility, $gameDate, false);
        }
        return false;
    }


    /**
     * @param Facility  $facility
     * @param GameDate  $gameDate
     * @param bool      $beginningLook
     */
    private function printGamesForFacilityForDayHeader($facility, $gameDate, $beginningLook = true)
    {
        $beginningStyle = $beginningLook ? "; height: .5in; font-size: 24px" : "; font-size: medium";

        print "
            <p style='page-break-before: always;'>&nbsp;</p>
            <table valign='top' align='center' border='1' cellpadding='5' cellspacing='0'>
                <thead>
                    <tr style='background-color: lightskyblue; color: black; -webkit-print-color-adjust: exact{$beginningStyle}'>
                        <th colspan='9'>$facility->name ($gameDate->day)</th>
                    <tr style='background-color: lightskyblue; color: black;  font-size: medium; -webkit-print-color-adjust: exact; height: .5in'>
                        <th>Start</th>
                        <th>Division</th>
                        <th>Field</th>
                        <th>GameId</th>
                        <th>Teams</th>
                        <th>Goals</th>
                        <th>Yellow</th>
                        <th>Red</th>
                        <th>Notes</th>
                    </tr>
                </thead>";
    }
}