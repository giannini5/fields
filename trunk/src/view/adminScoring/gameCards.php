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
use \DAG\Framework\Exception\Assertion;

/**
 * @brief Show the Schedule page and get the user to select a schedule to administer or create a new schedule.
 */
class View_AdminScoring_GameCards extends View_AdminScoring_Base
{
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
        $divisionsSelector  = $this->getDivisionsSelector(false, true, true, true);
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

        $this->_printGameCardsByDivisionAndDay($sessionId, $divisionsSelector, $gameDateSelector);

        print "
                    </td>
                    <td valign='top' bgcolor='" . View_Base::VIEW_COLOR . "'>";

        $this->printGameCardsByFacilityAndDay($sessionId, $gameDateSelector);

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
                $this->printGameCardsByDivisionId($this->m_controller->divisionId, $this->m_controller->gameDate);
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
    private function _printGameCardsByDivisionAndDay($sessionId, $divisionsSelector, $gameDateSelector)
    {
        $division               = isset($this->m_controller->divisionId) ? Division::lookupById($this->m_controller->divisionId) : null;
        $selectedDivisionName   = isset($division) ? $division->nameWithGender : '';
        $gameDay                = isset($this->m_controller->gameDate) ? $this->m_controller->gameDate->day : '';

        print "
            <table valign='top' align='center' border='0' cellpadding='5' cellspacing='0'>
                <tr>
                    <th nowrap align='left' colspan='2'>View Game Cards For Division</th>
                </tr>
            <form method='post' action='" . self::SCORING_GAME_CARDS_PAGE . $this->m_urlParams . "'>";

        $this->displaySelector('Division:', View_Base::DIVISION_ID, '', $divisionsSelector, $selectedDivisionName);
        $this->displaySelector('Game Date:', View_Base::GAME_DATE_ID, '', $gameDateSelector, $gameDay);

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
     * @param int       $divisionId
     * @param GameDate  $gameDate
     */
    private function  printGameCardsByDivisionId($divisionId, $gameDate)
    {
        $divisions = [];
        if ($divisionId == 0) {
            $divisions = Division::lookupBySeason($this->m_controller->m_season);
        } else {
            $divisions[] = Division::lookupById($divisionId);
        }

        foreach ($divisions as $division) {
            $this->printGameCardsByDivision($division, $gameDate);
        }
    }

    /**
     * @param Division          $division
     * @param GameDate          $gameDate
     * @param Facility | null   $facilityFilter
     */
    private function printGameCardsByDivision($division, $gameDate, $facilityFilter = null)
    {
        $games          = Game::lookupByDivisionDay($division, $gameDate->day, true);

        foreach ($games as $game) {
            // Skip games that are not played at the specified facility
            if (isset($facilityFilter)) {
                if ($game->gameTime->field->facility->id != $facilityFilter->id) {
                    continue;
                }
            }

            print "
            <p style='page-break-before: always;'>&nbsp</p>
            <div style ='margin: auto; width: 1000px;'>";

            $this->printGameCard($game, 'left', true);
            $this->printGameCard($game, 'right', false);

            print "
            </div>";
        }
    }

    /**
     * @param Game      $game
     * @param string    $position
     * @param bool      $isHomeTeam
     */
    private function printGameCard($game, $position, $isHomeTeam)
    {
        $homeOrVisitor          = $isHomeTeam ? "HOME" : "VISITOR";
        $team                   = $isHomeTeam ? $game->homeTeam : $game->visitingTeam;
        $teamId                 = isset($team) ? $team->nameId : "";
        $teamName               = isset($team) ? $team->name : "";
        $opposingTeam           = $isHomeTeam ? $game->visitingTeam : $game->homeTeam;
        $opposingTeamId         = isset($opposingTeam) ? $opposingTeam->nameId : "";
        $opposingTeamName       = isset($opposingTeam) ? $opposingTeam->name : "";
        $coach                  = isset($team) ? Coach::lookupByTeam($team) : null;
        $coachName              = isset($coach) ? $coach->name : "";
        $assistantCoaches       = isset($team) ? AssistantCoach::lookupByTeam($team) : [];
        $assistantCoachName     = count($assistantCoaches) > 0 ? $assistantCoaches[0]->name : "";
        $day                    = $game->gameTime->gameDate->day;
        $time                   = substr($game->gameTime->actualStartTime, 0, 5);
        $fieldName              = $game->gameTime->field->fullName;
        $fullTeamName           = $teamName == $teamId ? $teamId : "$teamId: $teamName";
        $fullOpposingTeamName   = $opposingTeamName == $opposingTeamId ? $opposingTeamId : "$opposingTeamId: $opposingTeamName";
        $players                = $this->getPlayersOrderedByNumber($team);

        $headerElementHeight    = "20px";
        print "
                <div style='float: $position; width=500px; height=700px; margin-left: 5px; margin-right 5px; border: none'>
                    <br><br><br>
                    <table border='0' style='table-layout: fixed; width: 4.5in'>
                        <tr>
                            <td align='left'><img src='/images/aysoLogoBlackAndWhite.png' height='30px' width='30px'></td>
                            <td align='center' nowrap><strong style='font-size: larger'>REGION 122 GAME CARD</strong></td>
                            <td align='right'><strong style='font-size: larger'>$homeOrVisitor</strong></td>
                        </tr>
                    </table>
                    <table border='0' style='table-layout: fixed; width: 4.5in'>
                        <tr style='height: $headerElementHeight'>
                            <td nowrap align='left' style='font-size: larger'>$day $time</td>
                            <td>&nbsp</td>
                            <td nowrap align='left' style='font-size: larger'>$fieldName</td>
                            <td>&nbsp</td>
                            <td nowrap align='right'>GID: <strong style='font-size: larger'>$game->id</strong></td>
                        </tr>
                    </table>
                    <table border='0' style='table-layout: fixed; width: 4.5in'>
                        <tr style='height: $headerElementHeight'>
                            <td nowrap align='left' style='overflow: hidden; font-size: larger'><strong>TEAM: </strong>$fullTeamName</td>
                            <td nowrap align='right' style='overflow: hidden; font-size: larger'><strong>OPPOSING TEAM: </strong>$fullOpposingTeamName</td>
                        </tr>
                    </table>
                    <table border='0' style='table-layout: fixed; width: 4.5in'>
                        <tr style='height: $headerElementHeight'>
                            <td nowrap align='left' style='overflow: hidden; font-size: larger'><strong>COACH: </strong>$coachName</td>
                            <td nowrap align='right' style='overflow: hidden; font-size: larger'><strong>ASST. COACH: </strong>$assistantCoachName</td>
                        </tr>
                    </table>
                    <br>
                    <table border='2' style='table-layout: fixed; width: 4.5in' cellpadding='5' cellspacing='0'>
                            <tr>
                                <td rowspan='2' width='5px' align='center' style='border: 1px solid'><strong>#</strong></td>
                                <td rowspan='2' width='65px' align='center' style='border: 1px solid'><strong>Player's Name</strong></td>
                                <td rowspan='2' width='30px' colspan='2' align='center' style='border: 1px solid; border-right: double'><strong>Goals Scored</strong></td>
                                <td width='60px' colspan='4' align='center' style='border: 1px solid; border-left: double'><strong>Sub: X, Keeper: G</strong></td>
                            </tr>
                            <tr>
                                <td align='center' style='border: 1px solid; font-size: 10px; border-left: double'><strong>1</strong></td>
                                <td align='center' style='border: 1px solid; font-size: 10px'><strong>2</strong></td>
                                <td align='center' style='border: 1px solid; font-size: 10px'><strong>3</strong></td>
                                <td align='center' style='border: 1px solid; font-size: 10px'><strong>4</strong></td>
                            </tr>";

        $playerCount = 0;
        Assertion::isTrue(count($players) < 18, "Count of players on a team cannot exceed 18. Team has " . count($players) . " players");
        foreach ($players as $player) {
            $this->printPlayerRow($player->name, $player->number);
            $playerCount += 1;
        }

        while ($playerCount < 18) {
            $this->printPlayerRow();
            $playerCount += 1;
        }

        print "
                    </table>
                    
                    <table border='0' style='table-layout: fixed; width: 4.5in'>
                        <tr style='font-size: 12px; height: 25px'>
                            <td align='left' nowrap>Halftime score:</td>
                            <td style='text-decoration: underline; border-bottom: 1px solid'></td>
                            <td align='right' nowrap>In Favor Of:</td>
                            <td style='text-decoration: underline; border-bottom: 1px solid'></td>
                        </tr>
                        <tr style='font-size: 12px; height: 25px'>
                            <td align='left' nowrap>Final Score:</td>
                            <td style='text-decoration: underline; border-bottom: 1px solid'></td>
                            <td align='right' nowrap>Winning Team Id:</td>
                            <td style='text-decoration: underline; border-bottom: 1px solid'></td>
                        </tr>
                        <tr style='font-size: 12px; height: 25px'>
                            <td align='left' nowrap>Center Referee:</td>
                            <td colspan=3 style='text-decoration: underline; border-bottom: 1px solid'></td>
                        </tr>
                        <tr style='font-size: 12px; height: 25px'>
                            <td align='left' nowrap>Assistant Referee:</td>
                            <td colspan=3 style='text-decoration: underline; border-bottom: 1px solid'></td>
                        </tr>
                        <tr style='font-size: 12px; height: 25px'>
                            <td align='left' nowrap>Assistant Referee:</td>
                            <td colspan=3 style='text-decoration: underline; border-bottom: 1px solid'></td>
                        </tr>
                    </table>                 
                    <p align='center'><strong>Write game comments (if any) on reverse side.  Thansks for reffing!</strong></p>
                    <br><br>

                </div>";
    }

    /**
     * @param string    $playerName
     * @param mixed     $number
     */
    private function printPlayerRow($playerName = '', $number = '') {
        print "
                        <tr style='overflow: hidden; height: 30px'>
                            <td width='5px' align='center' style='font-size: larger'>$number</td> 
                            <td width='75px' style='overflow: hidden; white-space: nowrap; font-size: larger'>$playerName</td>
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