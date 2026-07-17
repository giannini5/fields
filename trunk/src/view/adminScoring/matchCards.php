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
class View_AdminScoring_MatchCards extends View_AdminScoring_Base
{
    const HOME      = 'HOME';
    const VISITOR   = 'VISITOR';
    const MEDAL     = 'MEDAL';

    /**
     * @brief Construct the View
     *
     * @param Controller_AdminScoring_MatchCards $controller - Controller that contains data used when rendering this view.
     */
    public function __construct($controller)
    {
        parent::__construct(self::ADMIN_REF_MATCH_CARDS_PAGE, $controller);
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
                <tr>";

        print "
                    <td valign='top' bgcolor='" . View_Base::VIEW_COLOR . "'>";

        $this->printMatchCardsByFacilityAndDay($sessionId, $gameDateSelector);

        print "
                    </td>";

        print "
                </tr>
            </table>
            <br><br>";

        switch ($this->m_controller->displayType) {
            case Controller_AdminScoring_MatchCards::FACILITY_BY_DAY:
                $this->printMatchCardsByFacility($this->m_controller->facilityId, $this->m_controller->gameDate);
                break;
        }
    }

    /**
     * @brief Print the form to select the facility and date to display referee match cards
     *        - List of Facilities
     *        - Day to print match cards
     *
     * @param int   $sessionId          - Session Identifier
     * @param array $gameDateSelector   - List of gameDateId => day
     */
    private function printMatchCardsByFacilityAndDay($sessionId, $gameDateSelector)
    {
        $facilitySelector       = $this->getFacilitySelector(false);
        $facility               = isset($this->m_controller->facilityId) ? Facility::lookupById($this->m_controller->facilityId) : null;
        $selectedFacilityName   = isset($facility) ? $facility->name : '';
        $gameDay                = isset($this->m_controller->gameDate) ? $this->m_controller->gameDate->day : '';

        print "
            <table valign='top' align='center' border='0' cellpadding='5' cellspacing='0'>
                <tr>
                    <th nowrap align='left' colspan='2'>View Game Cards for Facility</th>
                </tr>
            <form method='post' action='" . self::ADMIN_REF_MATCH_CARDS_PAGE . $this->m_urlParams . "'>";

        $this->displaySelector('Facility:', View_Base::FACILITY_ID, '', $facilitySelector, $selectedFacilityName);
        $this->displaySelector('Game Date:', View_Base::GAME_DATE_ID, '', $gameDateSelector, $gameDay);

        // Print Update button and end form
        print "
                <tr>
                    <td align='left'>
                        <input style='background-color: yellow' name='" . View_Base::SUBMIT . "' type='submit' value='" . View_Base::ENTER . "'>
                        <input type='hidden' id='" . View_Base::SCORING_TYPE . "' name='" . View_Base::GAME_CARD_TYPE . "' value='" . Controller_AdminScoring_MatchCards::FACILITY_BY_DAY . "'>
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
    private function printMatchCardsByFacility($facilityId, $gameDate)
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
                    $this->printMatchCardsByDivision($division, $gameDate, $facility);
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
    private function printMatchCardsByDivision($division, $gameDate, $facilityFilter = null, $genderFilter = 'All')
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
            $this->printMatchCard($game, $game->homeTeam, $game->visitingTeam, View_AdminScoring_MatchCards::HOME);
        }
    }

    /**
     * @param Game      $game
     * @param Team      $team
     * @param Team      $opposingTeam
     * @param string    $homeOrVisitor - 'HOME', 'VISITOR'
     */
    private function printMatchCard($game, $team, $opposingTeam, $homeOrVisitor)
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
        $gameId                 = $game->id;
        $color                  = $team->color;
        $color                  = $color == "" ? "<u>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</u>" : $color;
        $teamName               = $teamName == $teamId ? "<u>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</u>" : $teamName;

        print '
<table border="0" style="page-break-before: always; table-layout: fixed; width: 5.5in; font-size: 10px">
    <tr>
        <td align="left" nowrap><strong>HOME</strong></td>
        <td align="center" nowrap><strong>⏴☐ KICKOFF ☐⏵</strong></td>
        <td align="right" nowrap><strong>AWAY</strong></td>
    </tr>
</table>
<table border="0" style="table-layout: fixed; width: 5.5in; border-collapse: collapse; font-size: 10px">
    <tr style="height: 15px; border-bottom: 1px solid black; border-top: 1px solid black">
        <td nowrap align="left" style="border-right: 1px solid black"><strong>COLOR</strong></td>
        <td style="border-right: 1px solid black">&nbsp;</td>
        <td nowrap align="center" style="border-right: 1px solid black"><strong>CAPTAIN(S)</strong></td>
        <td style="border-right: 1px solid black">&nbsp;</td>
        <td nowrap align="right"><strong>COLOR</strong></td>
    </tr>
</table>
<table border="0" style="table-layout: fixed; width: 5.5in; border-collapse: collapse; font-size: 10px">
    <tr style="height: 20px; border-bottom: 1px solid black">
        <td nowrap align="left" style="border-right: 1px solid black"><strong>DATE</strong></td>
        <td rowspan="3" style="border-right: 1px solid black; padding: 4px; vertical-align: middle; text-align: center; width: 30px">
            <img src="/images/aysoLogoBlackAndWhite.png" alt="" style="width: 100%; height: auto; display: block; max-height: 1.5in">
        </td>
        <td nowrap align="left"><strong>DURATION (2X): 25&nbsp;&nbsp;&nbsp;30&nbsp;&nbsp;&nbsp;35&nbsp;&nbsp;&nbsp;40&nbsp;&nbsp;&nbsp;45 - O.T.</strong></td>
    </tr>
    <tr style="height: 20px; border-bottom: 1px solid black">
        <td nowrap align="left" style="border-right: 1px solid black"><strong>FIELD</strong></td>
        <td nowrap align="left" style="font-size: 8px">Sched:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Start Time:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;End First Half:</td>
    </tr>
    <tr style="height: 20px; border-bottom: 1px solid black">
        <td nowrap align="left" style="border-right: 1px solid black"><strong>DIVISION</strong></td>
        <td nowrap align="left" style="font-size: 8px">Start Second Half:</td>
    </tr>
</table>
<div style="height: 0.5em"></div>
<table border="0" style="width: 5.5in; border-collapse: collapse" cellpadding="0" cellspacing="0">
    <tr>
        <td style="vertical-align: top; padding-right: 0; padding-left: 0">
<table border="0" style="table-layout: fixed; width: 2.75in; border-collapse: collapse">
    <tr style="height: 15px; border-bottom: 1px solid black">
        <td nowrap align="left" style="border: 2px solid black; border-right: 1px solid black; border-bottom: 1px solid black; font-size: 6px">GOALS</td>
        <td colspan="5" nowrap align="center" style="border-top: 2px solid black; border-right: 2px solid black; border-bottom: 1px solid black; border-left: 1px solid black; font-size: 10px"><strong>First Half</strong></td>
        <td colspan="5" nowrap align="center" style="border-top: 2px solid black; border-right: 1px solid black; border-bottom: 1px solid black; border-left: 2px solid black; font-size: 10px"><strong>Second Half</strong></td>
        <td nowrap align="center" style="border: 2px solid black; border-left: 1px solid black; border-bottom: 1px solid black; font-size: 8px"><strong>Final</strong></td>
    </tr>
    <tr style="height: 15px; border-bottom: 1px solid black">
        <td nowrap align="left" style="border-left: 2px solid black; border-right: 1px solid black; font-size: 10px">#</td>
        <td style="border-right: 1px solid black">&nbsp;</td>
        <td style="border-right: 1px solid black">&nbsp;</td>
        <td style="border-right: 1px solid black">&nbsp;</td>
        <td style="border-right: 1px solid black">&nbsp;</td>
        <td style="border-right: 2px solid black">&nbsp;</td>
        <td style="border-right: 1px solid black; border-left: 2px solid black">&nbsp;</td>
        <td style="border-right: 1px solid black">&nbsp;</td>
        <td style="border-right: 1px solid black">&nbsp;</td>
        <td style="border-right: 1px solid black">&nbsp;</td>
        <td style="border-right: 1px solid black">&nbsp;</td>
        <td style="border-right: 2px solid black">&nbsp;</td>
    </tr>
    <tr style="height: 15px; border-bottom: 1px solid black">
        <td nowrap align="left" style="border-left: 2px solid black; border-right: 1px solid black; border-bottom: 2px solid black; font-size: 10px">MIN</td>
        <td style="border-right: 1px solid black; border-bottom: 2px solid black">&nbsp;</td>
        <td style="border-right: 1px solid black; border-bottom: 2px solid black">&nbsp;</td>
        <td style="border-right: 1px solid black; border-bottom: 2px solid black">&nbsp;</td>
        <td style="border-right: 1px solid black; border-bottom: 2px solid black">&nbsp;</td>
        <td style="border-right: 2px solid black; border-bottom: 2px solid black">&nbsp;</td>
        <td style="border-right: 1px solid black; border-left: 2px solid black; border-bottom: 2px solid black">&nbsp;</td>
        <td style="border-right: 1px solid black; border-bottom: 2px solid black">&nbsp;</td>
        <td style="border-right: 1px solid black; border-bottom: 2px solid black">&nbsp;</td>
        <td style="border-right: 1px solid black; border-bottom: 2px solid black">&nbsp;</td>
        <td style="border-right: 1px solid black; border-bottom: 2px solid black">&nbsp;</td>
        <td style="border-right: 2px solid black; border-bottom: 2px solid black">&nbsp;</td>
    </tr>
</table>
        </td>
        <td style="vertical-align: top">
<table border="0" style="table-layout: fixed; width: 2.75in; border-collapse: collapse">
    <tr style="height: 15px; border-bottom: 1px solid black">
        <td nowrap align="left" style="border: 2px solid black; border-right: 1px solid black; border-bottom: 1px solid black; font-size: 6px">GOALS</td>
        <td colspan="5" nowrap align="center" style="border-top: 2px solid black; border-right: 2px solid black; border-bottom: 1px solid black; border-left: 1px solid black; font-size: 10px"><strong>First Half</strong></td>
        <td colspan="5" nowrap align="center" style="border-top: 2px solid black; border-right: 1px solid black; border-bottom: 1px solid black; border-left: 2px solid black; font-size: 10px"><strong>Second Half</strong></td>
        <td nowrap align="center" style="border: 2px solid black; border-left: 1px solid black; border-bottom: 1px solid black; font-size: 8px"><strong>Final</strong></td>
    </tr>
    <tr style="height: 15px; border-bottom: 1px solid black">
        <td nowrap align="left" style="border-left: 2px solid black; border-right: 1px solid black; font-size: 10px">#</td>
        <td style="border-right: 1px solid black">&nbsp;</td>
        <td style="border-right: 1px solid black">&nbsp;</td>
        <td style="border-right: 1px solid black">&nbsp;</td>
        <td style="border-right: 1px solid black">&nbsp;</td>
        <td style="border-right: 2px solid black">&nbsp;</td>
        <td style="border-right: 1px solid black; border-left: 2px solid black">&nbsp;</td>
        <td style="border-right: 1px solid black">&nbsp;</td>
        <td style="border-right: 1px solid black">&nbsp;</td>
        <td style="border-right: 1px solid black">&nbsp;</td>
        <td style="border-right: 1px solid black">&nbsp;</td>
        <td style="border-right: 2px solid black">&nbsp;</td>
    </tr>
    <tr style="height: 15px; border-bottom: 1px solid black">
        <td nowrap align="left" style="border-left: 2px solid black; border-right: 1px solid black; border-bottom: 2px solid black; font-size: 10px">MIN</td>
        <td style="border-right: 1px solid black; border-bottom: 2px solid black">&nbsp;</td>
        <td style="border-right: 1px solid black; border-bottom: 2px solid black">&nbsp;</td>
        <td style="border-right: 1px solid black; border-bottom: 2px solid black">&nbsp;</td>
        <td style="border-right: 1px solid black; border-bottom: 2px solid black">&nbsp;</td>
        <td style="border-right: 2px solid black; border-bottom: 2px solid black">&nbsp;</td>
        <td style="border-right: 1px solid black; border-left: 2px solid black; border-bottom: 2px solid black">&nbsp;</td>
        <td style="border-right: 1px solid black; border-bottom: 2px solid black">&nbsp;</td>
        <td style="border-right: 1px solid black; border-bottom: 2px solid black">&nbsp;</td>
        <td style="border-right: 1px solid black; border-bottom: 2px solid black">&nbsp;</td>
        <td style="border-right: 1px solid black; border-bottom: 2px solid black">&nbsp;</td>
        <td style="border-right: 2px solid black; border-bottom: 2px solid black">&nbsp;</td>
    </tr>
</table>
        </td>
    </tr>
</table>
<table border="0" style="table-layout: fixed; width: 5.5in">
    <tr>
        <td align="center" style="font-size: 10px; padding-bottom: 2px"><strong>M I S C O N D U C T</strong></td>
    </tr>
</table>
<table border="0" style="table-layout: fixed; width: 5.5in; border-collapse: collapse; font-size: 9px">
    <tr style="height: 15px">
        <td nowrap align="left" style="border: 1px solid black; border-bottom: 1px solid black; border-right: 1px solid black; font-size: 10px; width: 30%"><strong>REASON / PLAYER NAME</strong></td>
        <td nowrap align="center" style="border-top: 1px solid black; border-right: 1px solid black; border-bottom: 1px solid black; border-left: 1px solid black; font-size: 10px; width: 5%"><strong>#</strong></td>
        <td nowrap align="center" style="border-top: 1px solid black; border-right: 1px solid black; border-bottom: 1px solid black; border-left: 1px solid black; font-size: 10px; width: 5%"><strong>MIN</strong></td>
        <td nowrap align="center" style="border-top: 1px solid black; border-right: 1px solid black; border-bottom: 1px solid black; border-left: 1px solid black; font-size: 10px; width: 5%"><strong>C</strong></td>
        <td nowrap align="center" style="border-top: 1px solid black; border-right: 3px solid black; border-bottom: 1px solid black; border-left: 1px solid black; font-size: 10px; width: 5%"><strong>E</strong></td>
        <td nowrap align="left" style="border-top: 1px solid black; border-right: 1px solid black; border-bottom: 1px solid black; border-left: 1px solid black; font-size: 10px; width: 30%"><strong>REASON / PLAYER NAME</strong></td>
        <td nowrap align="center" style="border-top: 1px solid black; border-right: 1px solid black; border-bottom: 1px solid black; border-left: 1px solid black; font-size: 10px; width: 5%"><strong>#</strong></td>
        <td nowrap align="center" style="border-top: 1px solid black; border-right: 1px solid black; border-bottom: 1px solid black; border-left: 1px solid black; font-size: 10px; width: 5%"><strong>MIN</strong></td>
        <td nowrap align="center" style="border-top: 1px solid black; border-right: 1px solid black; border-bottom: 1px solid black; border-left: 1px solid black; font-size: 10px; width: 5%"><strong>C</strong></td>
        <td nowrap align="center" style="border: 1px solid black; border-left: 1px solid black; border-bottom: 1px solid black; font-size: 10px; width: 5%"><strong>E</strong></td>
    </tr>
    <tr style="height: 15px">
        <td style="border-left: 1px solid black; border-bottom: 1px solid black; border-right: 1px solid black">&nbsp;</td>
        <td style="border-right: 1px solid black; border-bottom: 1px solid black">&nbsp;</td>
        <td style="border-right: 1px solid black; border-bottom: 1px solid black">&nbsp;</td>
        <td style="border-right: 1px solid black; border-bottom: 1px solid black">&nbsp;</td>
        <td style="border-right: 3px solid black; border-bottom: 1px solid black">&nbsp;</td>
        <td style="border-right: 1px solid black; border-bottom: 1px solid black">&nbsp;</td>
        <td style="border-right: 1px solid black; border-bottom: 1px solid black">&nbsp;</td>
        <td style="border-right: 1px solid black; border-bottom: 1px solid black">&nbsp;</td>
        <td style="border-right: 1px solid black; border-bottom: 1px solid black">&nbsp;</td>
        <td style="border-right: 1px solid black; border-bottom: 1px solid black">&nbsp;</td>
    </tr>
    <tr style="height: 15px">
        <td style="border-left: 1px solid black; border-bottom: 1px solid black; border-right: 1px solid black">&nbsp;</td>
        <td style="border-right: 1px solid black; border-bottom: 1px solid black">&nbsp;</td>
        <td style="border-right: 1px solid black; border-bottom: 1px solid black">&nbsp;</td>
        <td style="border-right: 1px solid black; border-bottom: 1px solid black">&nbsp;</td>
        <td style="border-right: 3px solid black; border-bottom: 1px solid black">&nbsp;</td>
        <td style="border-right: 1px solid black; border-bottom: 1px solid black">&nbsp;</td>
        <td style="border-right: 1px solid black; border-bottom: 1px solid black">&nbsp;</td>
        <td style="border-right: 1px solid black; border-bottom: 1px solid black">&nbsp;</td>
        <td style="border-right: 1px solid black; border-bottom: 1px solid black">&nbsp;</td>
        <td style="border-right: 1px solid black; border-bottom: 1px solid black">&nbsp;</td>
    </tr>
    <tr style="height: 15px">
        <td style="border-left: 1px solid black; border-bottom: 1px solid black; border-right: 1px solid black">&nbsp;</td>
        <td style="border-right: 1px solid black; border-bottom: 1px solid black">&nbsp;</td>
        <td style="border-right: 1px solid black; border-bottom: 1px solid black">&nbsp;</td>
        <td style="border-right: 1px solid black; border-bottom: 1px solid black">&nbsp;</td>
        <td style="border-right: 3px solid black; border-bottom: 1px solid black">&nbsp;</td>
        <td style="border-right: 1px solid black; border-bottom: 1px solid black">&nbsp;</td>
        <td style="border-right: 1px solid black; border-bottom: 1px solid black">&nbsp;</td>
        <td style="border-right: 1px solid black; border-bottom: 1px solid black">&nbsp;</td>
        <td style="border-right: 1px solid black; border-bottom: 1px solid black">&nbsp;</td>
        <td style="border-right: 1px solid black; border-bottom: 1px solid black">&nbsp;</td>
    </tr>
    <tr style="height: 15px">
        <td style="border-left: 1px solid black; border-bottom: 1px solid black; border-right: 1px solid black">&nbsp;</td>
        <td style="border-right: 1px solid black; border-bottom: 1px solid black">&nbsp;</td>
        <td style="border-right: 1px solid black; border-bottom: 1px solid black">&nbsp;</td>
        <td style="border-right: 1px solid black; border-bottom: 1px solid black">&nbsp;</td>
        <td style="border-right: 3px solid black; border-bottom: 1px solid black">&nbsp;</td>
        <td style="border-right: 1px solid black; border-bottom: 1px solid black">&nbsp;</td>
        <td style="border-right: 1px solid black; border-bottom: 1px solid black">&nbsp;</td>
        <td style="border-right: 1px solid black; border-bottom: 1px solid black">&nbsp;</td>
        <td style="border-right: 1px solid black; border-bottom: 1px solid black">&nbsp;</td>
        <td style="border-right: 1px solid black; border-bottom: 1px solid black">&nbsp;</td>
    </tr>
    <tr style="height: 15px">
        <td style="border-left: 1px solid black; border-bottom: 1px solid black; border-right: 1px solid black">&nbsp;</td>
        <td style="border-right: 1px solid black; border-bottom: 1px solid black">&nbsp;</td>
        <td style="border-right: 1px solid black; border-bottom: 1px solid black">&nbsp;</td>
        <td style="border-right: 1px solid black; border-bottom: 1px solid black">&nbsp;</td>
        <td style="border-right: 3px solid black; border-bottom: 1px solid black">&nbsp;</td>
        <td style="border-right: 1px solid black; border-bottom: 1px solid black">&nbsp;</td>
        <td style="border-right: 1px solid black; border-bottom: 1px solid black">&nbsp;</td>
        <td style="border-right: 1px solid black; border-bottom: 1px solid black">&nbsp;</td>
        <td style="border-right: 1px solid black; border-bottom: 1px solid black">&nbsp;</td>
        <td style="border-right: 1px solid black; border-bottom: 1px solid black">&nbsp;</td>
    </tr>
    <tr style="height: 15px">
        <td style="border-left: 1px solid black; border-bottom: 1px solid black; border-right: 1px solid black">&nbsp;</td>
        <td style="border-right: 1px solid black; border-bottom: 1px solid black">&nbsp;</td>
        <td style="border-right: 1px solid black; border-bottom: 1px solid black">&nbsp;</td>
        <td style="border-right: 1px solid black; border-bottom: 1px solid black">&nbsp;</td>
        <td style="border-right: 3px solid black; border-bottom: 1px solid black">&nbsp;</td>
        <td style="border-right: 1px solid black; border-bottom: 1px solid black">&nbsp;</td>
        <td style="border-right: 1px solid black; border-bottom: 1px solid black">&nbsp;</td>
        <td style="border-right: 1px solid black; border-bottom: 1px solid black">&nbsp;</td>
        <td style="border-right: 1px solid black; border-bottom: 1px solid black">&nbsp;</td>
        <td style="border-right: 1px solid black; border-bottom: 1px solid black">&nbsp;</td>
    </tr>
</table>
<table border="0" style="table-layout: fixed; width: 5.5in" cellpadding="0" cellspacing="0"; font-size: 9px>
    <tr>
        <td colspan="2" style="font-size: 10px; padding-top: 4px; border-right: 1px solid black""><strong>CAUTION FOR:</strong></td>
        <td colspan="2" style="font-size: 10px; padding-top: 4px"><strong>SEND OFF FOR:</strong></td>
    </tr>
    <tr>
        <td style="font-size: 7px; ">(A) DISSENT</td>
        <td style="font-size: 7px; border-right: 1px solid black">(E) DELAYING RESTART OF GAME</td>
        <td style="font-size: 7px">&nbsp;(H) HAND BALL</td>
        <td style="font-size: 7px">(L) SERIOUS FOUL PLAY</td>
    </tr>
    <tr>
        <td style="font-size: 7px">(B) UNSPORTING BEHAVIOR</td>
        <td style="font-size: 7px; border-right: 1px solid black">(F) FAIL TO RESPECT DISTANCE</td>
        <td style="font-size: 7px">&nbsp;(I) GIAL DENIED</td>
        <td style="font-size: 7px">(M) VIOLENT CONDUCT</td>
    </tr>
    <tr>
        <td style="font-size: 7px">(C) PERSISTENT INFRINGEMENT</td>
        <td style="font-size: 7px; border-right: 1px solid black">(G) LEAVING THE FIELD OF PLAY</td>
        <td style="font-size: 7px">&nbsp;(J) LANGUAGE VIOLATION</td>
        <td style="font-size: 7px">(N) SPITS AT PERSON</td>
    </tr>
    <tr>
        <td style="font-size: 7px">(D) ILLEGAL ENTRY</td>
        <td style="font-size: 7px; border-right: 1px solid black">&nbsp;</td>
        <td style="font-size: 7px">&nbsp;(K) SECOND CAUTION SAME MATCH</td>
        <td style="font-size: 7px">&nbsp;</td>
    </tr>
</table>';

        // $headerElementHeight    = "20px";
        // print "
        //             <table border='0' style='page-break-before: always; table-layout: fixed; width: 5.5in'>
        //                 <tr>
        //                     <td align='left' nowrap><strong style='font-size: larger'>HOME</strong></td>
        //                     <td align='right'><strong style='font-size: larger'>AWAY</strong></td>
        //                 </tr>
        //             </table>
        //             <table border='0' style='table-layout: fixed; width: 4.5in'>
        //                 <tr style='height: $headerElementHeight'>
        //                     <td nowrap align='left' style='font-size: larger'>$day $time</td>
        //                     <td>&nbsp</td>
        //                     <td nowrap align='left' style='font-size: larger'>$fieldName</td>
        //                     <td>&nbsp</td>
        //                     <td nowrap align='right'>GID: <strong style='font-size: larger'>$gameId</strong></td>
        //                 </tr>
        //             </table>
        //             <table border='0' style='width: 4.5in; table-layout: auto'>
        //                 <tr style='height: $headerElementHeight'>
        //                     <td nowrap align='left' style='overflow: hidden; font-size: larger'><strong>TEAM: </strong>$teamId</td>
        //                     <td nowrap align='left' style='overflow: hidden; font-size: larger'><strong>NAME: </strong>$teamName</td>
        //                     <td nowrap align='right' style='overflow: hidden; font-size: larger'><strong>COLOR: </strong>$color</td>
        //                     <!-- <td nowrap align='left' style='overflow: hidden; font-size: larger'><strong>TEAM: </strong>$fullTeamName</td> -->
        //                     <!-- <td nowrap align='right' style='overflow: hidden; font-size: larger'><strong>VS: </strong>$fullOpposingTeamName</td> -->
        //                 </tr>
        //             </table>
        //             <table border='0' style='table-layout: auto; width: 4.5in'>
        //                 <tr style='height: $headerElementHeight'>
        //                     <td nowrap align='left' style='overflow: hidden; font-size: larger'><strong>COACH: </strong>$coachName</td>
        //                     <td nowrap align='right' style='overflow: hidden; font-size: larger'><strong>ASST. COACH: </strong>$assistantCoachName</td>
        //                 </tr>
        //             </table>
        //             <table border='0' style='table-layout: fixed; width: 4.5in'>
        //                 <tr style='height: $headerElementHeight'>
        //                     <td nowrap align='right' style='font-size: 9px'><strong>Sub: X, Keeper: G, Injured: I, Absent: A</strong></td>
        //                 </tr>
        //             </table>
        //             <table border='2' style='table-layout: fixed; width: 4.5in' cellpadding='5' cellspacing='0'>
        //                     <tr>
        //                         <td rowspan='1' width='5px' align='center' style='border: 1px solid'><strong>#</strong></td>
        //                         <td rowspan='1' width='65px' align='center' style='border: 1px solid'><strong>Player's Name</strong></td>
        //                         <td rowspan='1' width='30px' colspan='2' align='center' style='border: 1px solid; border-right: double'><strong>Goals</strong></td>
        //                         <td width='15px' align='center' style='border: 1px solid; border-left: double; font-size: 10px; border-left: double'><strong>1</strong></td>
        //                         <td width='15px' align='center' style='border: 1px solid; font-size: 10px'><strong>2</strong></td>
        //                         <td width='15px' align='center' style='border: 1px solid; font-size: 10px'><strong>3</strong></td>
        //                         <td width='15px' align='center' style='border: 1px solid; font-size: 10px'><strong>4</strong></td>
        //                     </tr>";

        // $playerCount = 0;
        // Assertion::isTrue(count($players) < 25, "Count of players on a team cannot exceed 18. Team has " . count($players) . " players");
        // foreach ($players as $player) {
        //     $this->printPlayerRow($player->name, $player->number);
        //     $playerCount += 1;
        // }

        // // if no players then print 17 rows
        // if ($playerCount == 0) {
        //     while ($playerCount < 17) {
        //         $this->printPlayerRow();
        //         $playerCount += 1;
        //     }
        // }

        // // print one more row to add a missing/new player
        // $this->printPlayerRow();
        // $playerCount += 1;

        // // print game notes box using up remaining rows
        // $remainingRows  = 22 - $playerCount;
        // $refereeNote    = $this->m_controller->refereeNote;
        // $title          = isset($game->title) ? $game->title . " VS:" : "VS:";
        // print "
        //                 <tr style='height: 25px'>
        //                     <td colspan='5' rowspan='$remainingRows' valign='top' style='border: none;'><strong>$game->notes $title</strong> $fullOpposingTeamName</td> 
        //                     <td colspan='3' rowspan='$remainingRows' valign='top' align='right' style='border: none;'>$refereeNote</td>
        //                 </tr>";

        // while ($remainingRows > 1) {
        //     print "
        //                 <tr style='height: 25px'></tr>";
        //     $remainingRows -= 1;
        // }

        // print "
        //             </table>";
    }
}