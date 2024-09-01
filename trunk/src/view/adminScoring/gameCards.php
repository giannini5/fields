<?php

use \DAG\Domain\Schedule\Game;
use \DAG\Domain\Schedule\Coach;
use \DAG\Domain\Schedule\Division;
use \DAG\Domain\Schedule\GameDate;
use \DAG\Domain\Schedule\Facility;
use \DAG\Domain\Schedule\Field;
use \DAG\Domain\Schedule\DivisionField;
use \DAG\Domain\Schedule\AssistantCoach;
use \DAG\Domain\Schedule\Player;
use \DAG\Domain\Schedule\Team;
use \DAG\Orm\Schedule\PlayerOrm;
use \DAG\Orm\Schedule\GameOrm;
use \DAG\Framework\Exception\Assertion;

/**
 * @brief Show the Schedule page and get the user to select a schedule to administer or create a new schedule.
 */
class View_AdminScoring_GameCards extends View_AdminScoring_Base
{
    const HOME      = 'HOME';
    const VISITOR   = 'VISITOR';
    const MEDAL     = 'MEDAL';

    /**
     * @brief Construct the View
     *
     * @param Controller_AdminScoring_GameCards $controller - Controller that contains data used when rendering this view.
     */
    public function __construct($controller)
    {
        parent::__construct(self::SCORING_GAME_CARDS_PAGE, $controller);
    }

    /**
     * @brief Render data for display on the page.
     */
    public function renderPage()
    {
        $sessionId          = $this->m_controller->getSessionId();
        $divisionsSelector  = $this->getDivisionsSelector(true, true, false, true);
        $genderSelector     = $this->getGenderSelector(true);
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

        $this->_printGameCardsByDivisionGenderAndDay($sessionId, $divisionsSelector, $genderSelector, $gameDateSelector);

        print "
                    </td>
                    <td valign='top' bgcolor='" . View_Base::VIEW_COLOR . "'>";

        $this->printGameCardsByFacilityAndDay($sessionId, $gameDateSelector);

        print "
                    </td>
                    <td valign='top' bgcolor='" . View_Base::VIEW_COLOR . "'>";

        $this->_printMedalRoundGameCardsByGenderAndDay($sessionId, $genderSelector, $gameDateSelector);

        print "
                    </td>
                </tr>
            </table>
            <br><br>";

        switch ($this->m_controller->displayType) {
            case Controller_AdminScoring_GameCards::FACILITY_BY_DAY:
                $this->printGameCardsByFacility($this->m_controller->facilityId, $this->m_controller->gameDate);
                break;

            case Controller_AdminScoring_GameCards::DIVISION_BY_DAY:
                $this->printGameCardsByDivisionNameAndGender($this->m_controller->divisionName, $this->m_controller->gender, $this->m_controller->gameDate);
                break;

            case Controller_AdminScoring_GameCards::MEDAL_BY_DAY:
                $this->printMedalGameCardsByGender($this->m_controller->gender, $this->m_controller->gameDate);
                break;
        }
    }

    /**
     * @brief Print the form to print game cards for day.  Form includes the following
     *        - List of Divisions
     *        - Day to enter/update scores
     *
     * @param int   $sessionId          - Session Identifier
     * @param array $divisionsSelector  - List of divisionId => name
     * @param array $gameDateSelector   - List of gameDateId => day
     */
    private function _printGameCardsByDivisionGenderAndDay($sessionId, $divisionsSelector, $genderSelector, $gameDateSelector)
    {
        $selectedDivisionName   = isset($this->m_controller->divisionName) ? $this->m_controller->divisionName : '';
        $gameDay                = isset($this->m_controller->gameDate) ? $this->m_controller->gameDate->day : '';
        $selectedGender         = isset($this->m_controller->gender) ? $this->m_controller->gender : '';
        $refereeNote            = isset($this->m_controller->refereeNote) ? $this->m_controller->refereeNote : '';

        print "
            <table valign='top' align='center' border='0' cellpadding='5' cellspacing='0'>
                <tr>
                    <th nowrap align='left' colspan='2'>View Game Cards For Division</th>
                </tr>
            <form method='post' action='" . self::SCORING_GAME_CARDS_PAGE . $this->m_urlParams . "'>";

        $this->displaySelector('Division:', View_Base::DIVISION_NAME, '', $divisionsSelector, $selectedDivisionName);
        $this->displaySelector('Gender:', View_Base::GENDER, '', $genderSelector, $selectedGender);
        $this->displaySelector('Game Date:', View_Base::GAME_DATE_ID, '', $gameDateSelector, $gameDay);
        $this->displayInput('Special Note:', 'text', View_Base::REFEREE_NOTE, 'Referee Note', '', $refereeNote, null, 1, true, 150, false);

        // Print Update button and end form
        print "
                <tr>
                    <td align='left'>
                        <input style='background-color: yellow' name='" . View_Base::SUBMIT . "' type='submit' value='" . View_Base::ENTER . "'>
                        <input type='hidden' id='" . View_Base::GAME_CARD_TYPE . "' name='" . View_Base::GAME_CARD_TYPE . "' value='" . Controller_AdminScoring_GameCards::DIVISION_BY_DAY . "'>
                        <input type='hidden' id='sessionId' name='sessionId' value='$sessionId'>
                    </td>
                </tr>
            </form>
            </table>";
    }

    /**
     * @brief Print the form to select the facility and date to display games for score keeping.
     *        - List of Facilities
     *        - Day to enter/update scores
     *
     * @param int   $sessionId          - Session Identifier
     * @param array $gameDateSelector   - List of gameDateId => day
     */
    private function printGameCardsByFacilityAndDay($sessionId, $gameDateSelector)
    {
        $facilitySelector       = $this->getFacilitySelector();
        $facility               = isset($this->m_controller->facilityId) ? Facility::lookupById($this->m_controller->facilityId) : null;
        $selectedFacilityName   = isset($facility) ? $facility->name : '';
        $gameDay                = isset($this->m_controller->gameDate) ? $this->m_controller->gameDate->day : '';

        print "
            <table valign='top' align='center' border='0' cellpadding='5' cellspacing='0'>
                <tr>
                    <th nowrap align='left' colspan='2'>View Game Cards for Facility</th>
                </tr>
            <form method='post' action='" . self::SCORING_GAME_CARDS_PAGE . $this->m_urlParams . "'>";

        $this->displaySelector('Facility:', View_Base::FACILITY_ID, '', $facilitySelector, $selectedFacilityName);
        $this->displaySelector('Game Date:', View_Base::GAME_DATE_ID, '', $gameDateSelector, $gameDay);

        // Print Update button and end form
        print "
                <tr>
                    <td align='left'>
                        <input style='background-color: yellow' name='" . View_Base::SUBMIT . "' type='submit' value='" . View_Base::ENTER . "'>
                        <input type='hidden' id='" . View_Base::SCORING_TYPE . "' name='" . View_Base::GAME_CARD_TYPE . "' value='" . Controller_AdminScoring_GameCards::FACILITY_BY_DAY . "'>
                        <input type='hidden' id='sessionId' name='sessionId' value='$sessionId'>
                    </td>
                </tr>
            </form>
            </table>";
    }

    /**
     * @brief Print the form to print medal round game cards for day.  Form includes the following
     *        - Gender
     *        - Day to enter/update scores
     *
     * @param int   $sessionId          - Session Identifier
     * @param array $genderSelector     - List of genders
     * @param array $gameDateSelector   - List of gameDateId => day
     */
    private function _printMedalRoundGameCardsByGenderAndDay($sessionId, $genderSelector, $gameDateSelector)
    {
        $gameDay                = isset($this->m_controller->gameDate) ? $this->m_controller->gameDate->day : '';
        $selectedGender         = isset($this->m_controller->gender) ? $this->m_controller->gender : '';
        $refereeNote            = isset($this->m_controller->refereeNote) ? $this->m_controller->refereeNote : '';

        print "
            <table valign='top' align='center' border='0' cellpadding='5' cellspacing='0'>
                <tr>
                    <th nowrap align='left' colspan='2'>View Medal Round Game Cards</th>
                </tr>
            <form method='post' action='" . self::SCORING_GAME_CARDS_PAGE . $this->m_urlParams . "'>";

        $this->displaySelector('Gender:', View_Base::GENDER, '', $genderSelector, $selectedGender);
        $this->displaySelector('Game Date:', View_Base::GAME_DATE_ID, '', $gameDateSelector, $gameDay);
        $this->displayInput('Special Note:', 'text', View_Base::REFEREE_NOTE, 'Referee Note', '', $refereeNote, null, 1, true, 150, false);

        // Print Update button and end form
        print "
                <tr>
                    <td align='left'>
                        <input style='background-color: yellow' name='" . View_Base::SUBMIT . "' type='submit' value='" . View_Base::ENTER . "'>
                        <input type='hidden' id='" . View_Base::GAME_CARD_TYPE . "' name='" . View_Base::GAME_CARD_TYPE . "' value='" . Controller_AdminScoring_GameCards::MEDAL_BY_DAY . "'>
                        <input type='hidden' id='sessionId' name='sessionId' value='$sessionId'>
                    </td>
                </tr>
            </form>
            </table>";
    }

    /**
     * @param int       $facilityId - 0 if all facilities
     * @param GameDate  $gameDate
     */
    private function printGameCardsByFacility($facilityId, $gameDate)
    {
        $facilities = [];
        if ($facilityId != 0) {
            $facilities[] = Facility::lookupById($facilityId);
        } else {
            $facilities = Facility::lookupBySeason($this->m_controller->m_season);
        }

        foreach ($facilities as $facility) {
            // Get list of divisions that play at the facility
            $divisionsById  = [];
            $fields         = Field::lookupByFacility($facility);
            foreach ($fields as $field) {
                $divisionFields = DivisionField::lookupByField($field);
                foreach ($divisionFields as $divisionField) {
                    $divisionsById[$divisionField->division->id] = $divisionField->division;
                }
            }

            // For each division print the games at the facility
            foreach ($divisionsById as $id => $division) {
                if ($division->isScoringTracked) {
                    $this->printGameCardsByDivision($division, $gameDate, $facility);
                }
            }
        }
    }

    /**
     * @param string    $divisionName
     * @param string    $gender     - All, Boys, Girls
     * @param GameDate  $gameDate
     */
    private function  printGameCardsByDivisionNameAndGender($divisionName, $gender, $gameDate)
    {
        if ($divisionName == 'All') {
            $this->printGameCardsByGameDate($gameDate, $gender);
        } else {
            $divisions = Division::lookupByName($this->m_controller->m_season, $divisionName);
            foreach ($divisions as $division) {
                if ($division->isScoringTracked) {
                    $this->printGameCardsByDivision($division, $gameDate, null, $gender);
                }
            }
        }
    }

    /**
     * @param string    $genderFilter - All, Boys, Girls
     * @param GameDate  $gameDate
     */
    private function  printMedalGameCardsByGender($genderFilter, $gameDate)
    {
        $divisions                          = Division::lookupBySeason($this->m_controller->m_season);
        $gameTypesByDivisionByFlight        = [];
        $teamsByDivisionByFlight            = [];

        foreach ($divisions as $division) {
            if ($genderFilter != 'All' and $division->gender != $genderFilter) {
                continue;
            }

            if ($division->isScoringTracked) {
                $games = Game::lookupByDivisionDay($division, $gameDate->day);
                foreach ($games as $game) {
                    $gameTypesByDivisionByFlight[$division->id][$game->flight->id][$game->title] = 1;
                }

                $teams = Team::lookupByDivision($division);
                foreach ($teams as $team) {
                    $teamsByDivisionByFlight[$division->id][$team->pool->flight->id][] = $team;
                }
            }
        }

        ksort($gameTypesByDivisionByFlight);
        foreach ($gameTypesByDivisionByFlight as $divisionId => $flightData) {
            ksort($flightData);
            foreach ($flightData as $flightId => $gameTitleData) {
                $cardCount = 0;
                foreach ($gameTitleData as $gameTitle => $data) {
                    switch ($gameTitle) {
                        case "":
                        case GameOrm::TITLE_PLAYOFF:
                            // Skip, not a medal round game
                            break;
                        case GameOrm::TITLE_5TH_6TH:
                            // Count covered by semi-final game
                            break;
                        case GameOrm::TITLE_QUARTER_FINAL:
                            $cardCount += 1;
                            break;
                        case GameOrm::TITLE_SEMI_FINAL:
                            $cardCount += 1;
                            break;
                        case GameOrm::TITLE_3RD_4TH:
                            // Count covered by championship game
                            break;
                        case GameOrm::TITLE_CHAMPIONSHIP:
                            $cardCount += 1;
                            break;
                        default:
                            Assertion::isTrue(false, "Unrecognized game title: $gameTitle");
                            break;
                    }
                }

                foreach ($teamsByDivisionByFlight as $teamDivisionId => $teamFlightData) {
                    if ($teamDivisionId != $divisionId) {
                        continue;
                    }

                    foreach ($teamFlightData as $teamFlightId => $teams) {
                        if ($teamFlightId != $flightId) {
                            continue;
                        }

                        foreach ($teams as $team) {
                            // Print front/back based on cardCount
                            for ($i = 0; $i < $cardCount; $i++) {
                                $this->printMedalRoundGameCard($team, $gameDate);
                                $this->printBackOfGameCard();
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * @param Division          $division
     * @param GameDate          $gameDate
     * @param Facility | null   $facilityFilter
     * @param string            $genderFilter - defaults to ALL
     */
    private function printGameCardsByDivision($division, $gameDate, $facilityFilter = null, $genderFilter = 'All')
    {
        $games = Game::lookupByDivisionDay($division, $gameDate->day, true);

        foreach ($games as $game) {
            // Skip medal round games
            /*
            if (!empty($game->title) and $game->title != GameOrm::TITLE_PLAYOFF) {
                continue;
            }
            */

            // Skip games that are not played at the specified facility
            if (isset($facilityFilter)) {
                if ($game->gameTime->field->facility->id != $facilityFilter->id) {
                    continue;
                }
            }

            // Skip games that are not associated with the specified gender
            if ($genderFilter != 'All') {
                if ($game->flight->schedule->division->gender != $genderFilter) {
                    continue;
                }
            }

            // Home Team Game Card (front and back, two pages
            $this->printGameCard($game, $game->homeTeam, $game->visitingTeam, View_AdminScoring_GameCards::HOME);
            $this->printBackOfGameCard();

            // Visiting Team Game Card (front and back, two pages
            $this->printGameCard($game, $game->visitingTeam, $game->homeTeam, View_AdminScoring_GameCards::VISITOR);
            $this->printBackOfGameCard();
        }
    }

    /**
     * @param GameDate          $gameDate
     * @param string            $genderFilter -
     */
    private function printGameCardsByGameDate($gameDate, $genderFilter = 'All')
    {
        $divisions                          = Division::lookupBySeason($this->m_controller->m_season);
        $gamesByFacilityByDivisionByTime    = [];

        foreach ($divisions as $division) {
            if ($division->isScoringTracked) {
                $games = Game::lookupByDivisionDay($division, $gameDate->day);
                foreach ($games as $game) {
                    $gamesByFacilityByDivisionByTime[$game->gameTime->field->facility->id][$division->id][$game->gameTime->actualStartTime][] = $game;
                }
            }
        }

        ksort($gamesByFacilityByDivisionByTime);

        foreach ($gamesByFacilityByDivisionByTime as $facilityId => $gamesByDivision) {
            ksort($gamesByDivision);

            foreach ($gamesByDivision as $divisionId => $gamesByStartTime) {
                ksort($gamesByStartTime);

                foreach ($gamesByStartTime as $startTime => $games) {
                    foreach ($games as $game) {
                        /*
                        // Skip medal round games
                        if (!empty($game->title) and $game->title != GameOrm::TITLE_PLAYOFF) {
                            continue;
                        }
                        */

                        // Skip games that are not associated with the specified gender
                        if ($genderFilter != 'All') {
                            if ($game->flight->schedule->division->gender != $genderFilter) {
                                continue;
                            }
                        }

                        // Home Team Game Card (front and back, two pages
                        $this->printGameCard($game, $game->homeTeam, $game->visitingTeam, View_AdminScoring_GameCards::HOME);
                        $this->printBackOfGameCard();

                        // Visiting Team Game Card (front and back, two pages
                        $this->printGameCard($game, $game->visitingTeam, $game->homeTeam, View_AdminScoring_GameCards::VISITOR);
                        $this->printBackOfGameCard();
                    }
                }
            }
        }
    }

    /**
     * @param Game      $game
     * @param Team      $team
     * @param Team      $opposingTeam
     * @param string    $homeOrVisitor - 'HOME', 'VISITOR'
     */
    private function printGameCard($game, $team, $opposingTeam, $homeOrVisitor)
    {
        $teamId                 = isset($team) ? $team->nameId : "";
        $teamName               = isset($team) ? $team->name : "";
        $opposingTeamId         = isset($opposingTeam) ? $opposingTeam->nameId : "";
        $opposingTeamName       = isset($opposingTeam) ? $opposingTeam->name : "";
        $coach                  = isset($team) ? Coach::lookupByTeam($team) : null;
        $coachName              = isset($coach) ? $coach->name : "";
        $assistantCoaches       = isset($team) ? AssistantCoach::lookupByTeam($team) : [];
        $assistantCoachName     = count($assistantCoaches) > 0 ? $assistantCoaches[0]->name : "";
        $day                    = $game->gameTime->gameDate->day;
        $time                   = substr($game->gameTime->actualStartTime, 0, 5);
        $fieldName              = $game->gameTime->field->fullName;
        $fullTeamName           = $teamName == $teamId ? $teamId : "$teamId ($teamName)";
        $fullOpposingTeamName   = $opposingTeamName == $opposingTeamId ? $opposingTeamId : "$opposingTeamId ($opposingTeamName), $opposingTeam->color";
        $players                = $this->getPlayersOrderedByNumber($team);
        $gameId                 = $game->id;
        $color                  = $team->color;
        $color                  = $color == "" ? "<u>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</u>" : $color;
        $teamName               = $teamName == $teamId ? "<u>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</u>" : $teamName;

        $headerElementHeight    = "20px";
        print "
                    <table border='0' style='page-break-before: always; table-layout: fixed; width: 4.5in'>
                        <tr>
                            <td align='left'><img src='/images/aysoLogoBlackAndWhite.png' height='30px' width='30px'></td>
                            <td align='center' nowrap><strong style='font-size: larger'>GAME CARD</strong></td>
                            <td align='right'><strong style='font-size: larger'>$homeOrVisitor</strong></td>
                        </tr>
                    </table>
                    <table border='0' style='table-layout: fixed; width: 4.5in'>
                        <tr style='height: $headerElementHeight'>
                            <td nowrap align='left' style='font-size: larger'>$day $time</td>
                            <td>&nbsp</td>
                            <td nowrap align='left' style='font-size: larger'>$fieldName</td>
                            <td>&nbsp</td>
                            <td nowrap align='right'>GID: <strong style='font-size: larger'>$gameId</strong></td>
                        </tr>
                    </table>
                    <table border='0' style='width: 4.5in; table-layout: auto'>
                        <tr style='height: $headerElementHeight'>
                            <td nowrap align='left' style='overflow: hidden; font-size: larger'><strong>TEAM: </strong>$teamId</td>
                            <td nowrap align='left' style='overflow: hidden; font-size: larger'><strong>NAME: </strong>$teamName</td>
                            <td nowrap align='right' style='overflow: hidden; font-size: larger'><strong>COLOR: </strong>$color</td>
                            <!-- <td nowrap align='left' style='overflow: hidden; font-size: larger'><strong>TEAM: </strong>$fullTeamName</td> -->
                            <!-- <td nowrap align='right' style='overflow: hidden; font-size: larger'><strong>VS: </strong>$fullOpposingTeamName</td> -->
                        </tr>
                    </table>
                    <table border='0' style='table-layout: auto; width: 4.5in'>
                        <tr style='height: $headerElementHeight'>
                            <td nowrap align='left' style='overflow: hidden; font-size: larger'><strong>COACH: </strong>$coachName</td>
                            <td nowrap align='right' style='overflow: hidden; font-size: larger'><strong>ASST. COACH: </strong>$assistantCoachName</td>
                        </tr>
                    </table>
                    <table border='0' style='table-layout: fixed; width: 4.5in'>
                        <tr style='height: $headerElementHeight'>
                            <td nowrap align='right' style='font-size: 9px'><strong>Sub: X, Keeper: G, Injured: I, Absent: A</strong></td>
                        </tr>
                    </table>
                    <table border='2' style='table-layout: fixed; width: 4.5in' cellpadding='5' cellspacing='0'>
                            <tr>
                                <td rowspan='1' width='5px' align='center' style='border: 1px solid'><strong>#</strong></td>
                                <td rowspan='1' width='65px' align='center' style='border: 1px solid'><strong>Player's Name</strong></td>
                                <td rowspan='1' width='30px' colspan='2' align='center' style='border: 1px solid; border-right: double'><strong>Goals</strong></td>
                                <td width='15px' align='center' style='border: 1px solid; border-left: double; font-size: 10px; border-left: double'><strong>1</strong></td>
                                <td width='15px' align='center' style='border: 1px solid; font-size: 10px'><strong>2</strong></td>
                                <td width='15px' align='center' style='border: 1px solid; font-size: 10px'><strong>3</strong></td>
                                <td width='15px' align='center' style='border: 1px solid; font-size: 10px'><strong>4</strong></td>
                            </tr>";

        $playerCount = 0;
        Assertion::isTrue(count($players) < 18, "Count of players on a team cannot exceed 18. Team has " . count($players) . " players");
        foreach ($players as $player) {
            $this->printPlayerRow($player->name, $player->number);
            $playerCount += 1;
        }

        // if no players then print 17 rows
        if ($playerCount == 0) {
            while ($playerCount < 17) {
                $this->printPlayerRow();
                $playerCount += 1;
            }
        }

        // print one more row to add a missing/new player
        $this->printPlayerRow();
        $playerCount += 1;

        // print game notes box using up remaining rows
        $remainingRows  = 22 - $playerCount;
        $refereeNote    = $this->m_controller->refereeNote;
        $title          = isset($game->title) ? $game->title . " VS:" : "VS:";
        print "
                        <tr style='height: 25px'>
                            <td colspan='5' rowspan='$remainingRows' valign='top' style='border: none;'><strong>$game->notes $title</strong> $fullOpposingTeamName</td> 
                            <td colspan='3' rowspan='$remainingRows' valign='top' align='right' style='border: none;'>$refereeNote</td>
                        </tr>";

        while ($remainingRows > 1) {
            print "
                        <tr style='height: 25px'></tr>";
            $remainingRows -= 1;
        }

        print "
                    </table>";
    }

    /**
     * @param Team      $team
     * @param GameDate  $gameDate
     */
    private function printMedalRoundGameCard($team, $gameDate)
    {
        $teamId                 = isset($team) ? $team->nameId : "";
        $teamName               = isset($team) ? $team->name : "";
        $coach                  = isset($team) ? Coach::lookupByTeam($team) : null;
        $coachName              = isset($coach) ? $coach->name : "";
        $assistantCoaches       = isset($team) ? AssistantCoach::lookupByTeam($team) : [];
        $assistantCoachName     = count($assistantCoaches) > 0 ? $assistantCoaches[0]->name : "";
        $day                    = $gameDate->day;
        $time                   = "Time: <u>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</u>";
        $fieldName              = "Field <u>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</u>";
        $fullOpposingTeamName   = "";
        $players                = $this->getPlayersOrderedByNumber($team);
        $color                  = $team->color;
        $color                  = $color == "" ? "<u>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</u>" : $color;
        $teamName               = $teamName == $teamId ? "<u>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</u>" : $teamName;

        $headerElementHeight    = "20px";
        print "
                    <table border='0' style='page-break-before: always; table-layout: fixed; width: 4.5in'>
                        <tr>
                            <td align='left'><img src='/images/aysoLogoBlackAndWhite.png' height='30px' width='30px'></td>
                            <td align='center' nowrap><strong style='font-size: larger'>GAME CARD</strong></td>
                            <td align='right'><strong style='font-size: larger'>TITLE: <u>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</u></strong></td>
                        </tr>
                    </table>
                    <table border='0' style='table-layout: auto; width: 4.5in'>
                        <tr style='height: $headerElementHeight'>
                            <td nowrap align='left' style='font-size: larger'>$day $time</td>
                            <td>&nbsp</td>
                            <td nowrap align='left' style='font-size: larger'>$fieldName</td>
                            <td>&nbsp</td>
                            <td nowrap align='right'>GID: <strong style='font-size: larger'><u>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</u></strong></td>
                        </tr>
                    </table>
                    <table border='0' style='width: 4.5in; table-layout: auto'>
                        <tr style='height: $headerElementHeight'>
                            <td nowrap align='left' style='overflow: hidden; font-size: larger'><strong>TEAM: </strong>$teamId</td>
                            <td nowrap align='left' style='overflow: hidden; font-size: larger'><strong>NAME: </strong>$teamName</td>
                            <td nowrap align='right' style='overflow: hidden; font-size: larger'><strong>COLOR: </strong>$color</td>
                        </tr>
                    </table>
                    <table border='0' style='table-layout: auto; width: 4.5in'>
                        <tr style='height: $headerElementHeight'>
                            <td nowrap align='left' style='overflow: hidden; font-size: larger'><strong>COACH: </strong>$coachName</td>
                            <td nowrap align='right' style='overflow: hidden; font-size: larger'><strong>ASST. COACH: </strong>$assistantCoachName</td>
                        </tr>
                    </table>
                    <table border='0' style='table-layout: fixed; width: 4.5in'>
                        <tr style='height: $headerElementHeight'>
                            <td nowrap align='right' style='font-size: 9px'><strong>Sub: X, Keeper: G, Injured: I, Absent: A</strong></td>
                        </tr>
                    </table>
                    <table border='2' style='table-layout: fixed; width: 4.5in' cellpadding='5' cellspacing='0'>
                            <tr>
                                <td rowspan='1' width='5px' align='center' style='border: 1px solid'><strong>#</strong></td>
                                <td rowspan='1' width='65px' align='center' style='border: 1px solid'><strong>Player's Name</strong></td>
                                <td rowspan='1' width='30px' colspan='2' align='center' style='border: 1px solid; border-right: double'><strong>Goals</strong></td>
                                <td width='15px' align='center' style='border: 1px solid; border-left: double; font-size: 10px; border-left: double'><strong>1</strong></td>
                                <td width='15px' align='center' style='border: 1px solid; font-size: 10px'><strong>2</strong></td>
                                <td width='15px' align='center' style='border: 1px solid; font-size: 10px'><strong>3</strong></td>
                                <td width='15px' align='center' style='border: 1px solid; font-size: 10px'><strong>4</strong></td>
                            </tr>";

        $playerCount = 0;
        Assertion::isTrue(count($players) < 18, "Count of players on a team cannot exceed 18. Team has " . count($players) . " players");
        foreach ($players as $player) {
            $this->printPlayerRow($player->name, $player->number);
            $playerCount += 1;
        }

        while ($playerCount < 12) {
            $this->printPlayerRow();
            $playerCount += 1;
        }

        $remainingRows  = 22 - $playerCount;
        $refereeNote    = $this->m_controller->refereeNote;
        print "
                        <tr style='height: 25px'>
                            <td colspan='3' rowspan='$remainingRows' valign='top' style='border: none;'><strong>VS:</strong> $fullOpposingTeamName</td> 
                            <td colspan='5' rowspan='$remainingRows' valign='top' align='right' style='border: none;'>$refereeNote</td>
                        </tr>";

        while ($remainingRows > 1) {
            print "
                        <tr style='height: 25px'></tr>";
            $remainingRows -= 1;
        }

        print "
                    </table>";
    }

    /**
     */
    private function printBackOfGameCard()
    {
        print "
                    <table border='0' style='page-break-before: always; table-layout: fixed; width: 4.5in'>
                        <tr>
                            <th style='font-size: 20px' colspan='4' align='center'>Referee Game Report</th>
                        </tr>
                        <tr><td>&nbsp</td></tr>
                        <tr style='font-size: 12px; height: 25px'>
                            <td align='left' nowrap>Halftime Score:</td>
                            <td style='text-decoration: underline; border-bottom: 1px solid'></td>
                            <td align='right' nowrap>In Favor Of:</td>
                            <td style='text-decoration: underline; border-bottom: 1px solid'></td>
                        </tr>
                        <tr><td>&nbsp</td></tr>
                        <tr>
                            <td align='left' nowrap>Final Score:</td>
                            <td style='text-decoration: underline; border-bottom: 1px solid'></td>
                            <td align='right' nowrap>In Favor Of:</td>
                            <td style='text-decoration: underline; border-bottom: 1px solid'></td>
                        </tr>
                        <tr><td>&nbsp</td></tr>
                        <tr><td>&nbsp</td></tr>
                        <tr>
                            <th style='font-size: 20px' colspan='4' align='center'>Preliminary Incident Report</th>
                        </tr>
                        <tr>
                            <th style='font-size: 12px' colspan='4' align='center'>(A more detailed report may be required.  Check at the referee tent.)</th>
                        </tr>
                        <tr>
                            <th style='font-size: 8px' colspan='4' align='center'>Disciplinary Action / Significant Injuries / Additional Comments: Please include names and player numbers.</th>
                        </tr>
                        <tr><td>&nbsp</td></tr>
                        <tr style='font-size: 12px; height: 25px'>
                            <td colspan=4 style='text-decoration: underline; border-bottom: 1px solid'></td>
                        </tr>
                        <tr><td>&nbsp</td></tr>
                        <tr style='font-size: 12px; height: 25px'>
                            <td colspan=4 style='text-decoration: underline; border-bottom: 1px solid'></td>
                        </tr>
                        <tr><td>&nbsp</td></tr>
                        <tr style='font-size: 12px; height: 25px'>
                            <td colspan=4 style='text-decoration: underline; border-bottom: 1px solid'></td>
                        </tr>
                        <tr><td>&nbsp</td></tr>
                        <tr style='font-size: 12px; height: 25px'>
                            <td colspan=4 style='text-decoration: underline; border-bottom: 1px solid'></td>
                        </tr>
                        <tr><td>&nbsp</td></tr>
                        <tr style='font-size: 12px; height: 25px'>
                            <td colspan=4 style='text-decoration: underline; border-bottom: 1px solid'></td>
                        </tr>
                        <tr><td>&nbsp</td></tr>
                        <tr style='font-size: 12px; height: 25px'>
                            <td colspan=4 style='text-decoration: underline; border-bottom: 1px solid'></td>
                        </tr>
                        <tr><td>&nbsp</td></tr>
                        <tr style='font-size: 12px; height: 25px'>
                            <td colspan=4 style='text-decoration: underline; border-bottom: 1px solid'></td>
                        </tr>
                        <tr><td>&nbsp</td></tr>
                        <tr style='font-size: 12px; height: 25px'>
                            <td colspan=4 style='text-decoration: underline; border-bottom: 1px solid'></td>
                        </tr>
                        <tr><td>&nbsp</td></tr>
                        <tr>
                            <th style='font-size: 20px' colspan='4' align='center'>Referee Printed Name</th>
                        </tr>
                        <tr><td>&nbsp</td></tr>
                        <tr style='font-size: 12px; height: 25px'>
                            <td align='left' nowrap>Center Referee:</td>
                            <td colspan=3 style='text-decoration: underline; border-bottom: 1px solid'></td>
                        </tr>
                        <tr><td>&nbsp</td></tr>
                        <tr style='font-size: 12px; height: 25px'>
                            <td align='left' nowrap>Assistant Referee:</td>
                            <td colspan=3 style='text-decoration: underline; border-bottom: 1px solid'></td>
                        </tr>
                        <tr><td>&nbsp</td></tr>
                        <tr style='font-size: 12px; height: 25px'>
                            <td align='left' nowrap>Assistant Referee:</td>
                            <td colspan=3 style='text-decoration: underline; border-bottom: 1px solid'></td>
                        </tr>
                    </table>";
    }

    /**
     * @param string    $playerName
     * @param mixed     $number
     */
    private function printPlayerRow($playerName = '', $number = '') {
        print "
                        <tr style='overflow: hidden; height: 25px'>
                            <td width='5px' align='center' style='font-size: 12px'>$number</td> 
                            <td width='75px' style='overflow: hidden; white-space: nowrap; font-size: 12px'>$playerName</td>
                            <td width='15px'>&nbsp</td>
                            <td width='15px' style='border-right: double'>&nbsp</td>
                            <td width='15px' style='border-left: double'>&nbsp</td>
                            <td width='15px'>&nbsp</td>
                            <td width='15px'>&nbsp</td>
                            <td width='15px'>&nbsp</td>
                        </tr>";
    }

    /**
     * @param Team  $team
     * @return array|Player[]
     */
    private function getPlayersOrderedByNumber($team)
    {
        $players = isset($team) ? Player::lookupByTeam($team, PlayerOrm::ORDER_BY_NUMBER) : [];
        return $players;
    }
}