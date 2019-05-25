<?php

use \DAG\Domain\Schedule\Referee;
use \DAG\Domain\Schedule\Division;
use \DAG\Domain\Schedule\DivisionReferee;
use \DAG\Domain\Schedule\GameDate;

/**
 * @brief Show the Referee page and get the user to select a referee to administer or create a new referee.
 */
class View_AdminReferee_Preferences extends View_AdminReferee_Base {
    /** @var  View_AdminReferee_Preferences */
    public $m_controller;
    /**
     * @brief Construct the View
     *
     * @param Controller_AdminReferee_Preferences $controller - Controller that contains data used when rendering this view.
     */
    public function __construct($controller) {
        $this->m_controller = $controller;
        parent::__construct(self::REFEREE_PREFERENCES_PAGE, $controller);
    }

    /**
     * @brief Render data for display on the page.
     */
    public function render()
    {
        $filterDivisionId   = $this->m_controller->m_filterDivisionId;
        $filterRefereeId    = $this->m_controller->m_filterRefereeId;
        $sessionId          = $this->m_controller->getSessionId();

        $messageString  = $this->m_controller->m_messageString;
        if (!empty($messageString)) {
            print "
                <p style='color: green' align='center'><strong>$messageString</strong></p><br>";
        }

        $this->printSelectors($filterDivisionId, $filterRefereeId);

        if ($filterRefereeId != 0) {
            $referees[] = Referee::lookupById($filterRefereeId);
        }
        else if ($filterDivisionId != 0) {
            $division   = Division::lookupById($filterDivisionId);
            $referees   = DivisionReferee::lookupByDivision($division);
            usort($referees, "compareDivisionDisplayOrder");
        } else {
            return;
            // $referees = Referee::lookupBySeason($this->m_controller->m_season);
            // usort($referees, "compareName");
        }

        $gameDates = GameDate::lookupBySeason($this->m_controller->m_season);
        $divisions = Division::lookupBySeason($this->m_controller->m_season);

        print "
            <br><br>";

        $this->printReferees($referees, $gameDates, $divisions, $filterRefereeId, $filterDivisionId, $sessionId);
    }

    /**
     * @param GameDate[]    $gameDates
     * @param string[]      $gameDatesChecked
     */
    private function printGameDateSelection($gameDates, $gameDatesChecked)
    {
        $center = false;
        $newRow = true;

        print "
            <table valign='top' align='center' border='0' cellpadding='5' cellspacing='0'>
                <tr>";

        foreach ($gameDates as $gameDate) {
            $description    = $gameDate->day;
            $isChecked      = isset($gameDatesChecked[$gameDate->id]);
            $checkboxName   = View_Base::GAME_DATES_CHECKED . "[" . $gameDate->id . "]";
            $this->printCheckboxSelector($checkboxName, $description, $isChecked, 1, $newRow, $center);
        }

        print "
                </tr>
            </table>";
    }

    /**
     * @param Division[]    $divisions
     * @param string        $gender
     * @param string[]      $divisionsChecked
     */
    private function printDivisionSelection($divisions, $gender, $divisionsChecked)
    {
        $divisionCount = 1;

        foreach ($divisions as $division) {
            if ($division->gender == $gender and $division->isScoringTracked) {
                $divisionCount += 1;
            }
        }

        print "
        <table valign='top' align='center' border='0' cellpadding='5' cellspacing='0'>
            <thead>
                <tr bgcolor='lightskyblue'>
                    <th colspan='$divisionCount'>$gender</th>
                </tr>
                <tr bgcolor='lightskyblue'>
                    <th>&nbsp;</th>";

        foreach ($divisions as $division) {
            if ($division->gender != $gender or !$division->isScoringTracked) {
                continue;
            }

            print "
                        <th>$division->nameWithGender</th>";
        }

        print "
                    </tr>
                </thead>";

        $this->printPreferenceLine($divisions, View_Base::CENTER, $gender, $divisionsChecked);
        $this->printPreferenceLine($divisions, View_Base::ASSISTANT, $gender, $divisionsChecked);
        $this->printPreferenceLine($divisions, View_Base::MENTOR, $gender, $divisionsChecked);

        print "
            </table>";
    }

    /**
     * @param Division[]    $divisions
     * @param string        $label
     * @param string        $gender
     * @param string[]      $divisionsChecked
     */
    private function printPreferenceLine($divisions, $label, $gender, $divisionsChecked)
    {
        print "
                    <tr>
                        <td>$label</td>";

        $center = true;
        $newRow = false;
        foreach ($divisions as $division) {
            if ($division->gender != $gender or !$division->isScoringTracked) {
                continue;
            }

            $description    = "";
            $checkboxName   = View_Base::DIVISIONS_CHECKED . "[" . $division->id . "][" . $label . "]";
            $isChecked      = isset($divisionsChecked[$division->id][$label]) ? $divisionsChecked[$division->id][$label] : false;
            $colspan        = 1;
            $this->printCheckboxSelector($checkboxName, $description, $isChecked, $colspan, $newRow, $center);
        }

        print "
                    </tr>";
    }

    /**
     * @param int   $filterDivisionId
     * @param int   $filterRefereeId
     */
    private function printSelectors($filterDivisionId, $filterRefereeId)
    {
        $sessionId = $this->m_controller->getSessionId();

        print "
            <table valign='top' align='center' width='625' border='1' cellpadding='5' cellspacing='0'>
                <tr><td bgcolor='" . View_Base::CREATE_COLOR . "'>
                    <table valign='top' align='center' width='300' border='0' cellpadding='5' cellspacing='0'>
                        <tr>
                            <td valign='top'>";

        $this->printSelectReferee($sessionId, $filterDivisionId, $filterRefereeId);

        print "
                            </td>
                        </tr>
                    </table>
                    </td>
                </tr>
            </table>";
    }

    /**
     * @param int   $sessionId
     * @param int   $filterDivisionId
     * @param int   $filterRefereeId
     */
    private function printSelectReferee($sessionId, $filterDivisionId, $filterRefereeId)
    {
        print "
            <form method='post' action='" . self::REFEREE_PREFERENCES_PAGE . $this->m_urlParams . "'>
                <tr>
                    <td colspan='2'><strong> View Referees</strong></td>
                </tr>";

        $this->printDivisionSelectorByName($filterDivisionId, true, true);
        $this->printRefereeSelector($filterRefereeId);

        // Print Filter button and end form
        print "
                <tr>
                    <td align='left'>
                        <input style='background-color: yellow' name='" . View_Base::SUBMIT . "' type='submit' value='" . View_Base::FILTER . "'>
                        <input type='hidden' id='sessionId' name='sessionId' value='$sessionId'>
                    </td>
                </tr>
            </form>";
    }

    /**
     * @param Referee[]     $referees
     * @param GameDate[]    $gameDates
     * @param Division[]    $divisions
     * @param int           $filterRefereeId
     * @param int           $filterDivisionId
     * @param int           $sessionId
     */
    private function printReferees($referees, $gameDates, $divisions, $filterRefereeId, $filterDivisionId, $sessionId)
    {
        foreach ($referees as $referee) {
            if ($filterRefereeId != 0 and $referee->id != $filterRefereeId) {
                continue;
            }

            $divisionsChecked = $referee->getDivisionsChecked();
            $gameDatesChecked = $referee->getGameDatesChecked();

            print "
            <table valign='top' align='center' border='1' cellpadding='5' cellspacing='0'>
                <thead>
                    <tr bgcolor='lightskyblue'>
                        <th colspan='2'>$referee->name ($referee->email, $referee->phone)</th>
                    </tr>
                    <tr bgcolor='lightskyblue'>
                        <th>Availability</th>
                        <th>Division Preferences</th>
                    </tr>
                </thead>";

            $rowspan = 1;

            print "
                <form method='post' action='" . self::REFEREE_PREFERENCES_PAGE . $this->m_urlParams . "'>
                <tr>";

            print "
                    <td valign='top'>";

            $this->printGameDateSelection($gameDates, $gameDatesChecked);

            print "
                    </td>
                    <td valign='center'>";

            $this->printDivisionSelection($divisions, Division::$GIRLS, $divisionsChecked);
            print "<br><br>";
            $this->printDivisionSelection($divisions, Division::$BOYS, $divisionsChecked);

            print "
                    </td>";

            $this->printMaxGamesPerDay($referee);
            $this->displayInput("", "text", View_Base::SPECIAL_INSTRUCTIONS, 'Special Instructions (Optional)', '', $referee->specialInstructions, null, 2, true, 500, false);

            print "
                <tr>
                    <td rowspan='$rowspan' align='center' colspan='2'>
                        <input style='background-color: lightgreen' name='" . View_Base::SUBMIT . "' type='submit' value='" . View_Base::UPDATE . "'>
                        <input type='hidden' id='" . self::REFEREE_ID . "' name='" . self::REFEREE_ID . "' value='$referee->id'>
                        <input type='hidden' id='" . self::FILTER_REFEREE_ID . "' name='" . self::FILTER_REFEREE_ID . "' value='$filterRefereeId'>
                        <input type='hidden' id='" . self::FILTER_DIVISION_ID . "' name='" . self::FILTER_DIVISION_ID . "' value='$filterDivisionId'>
                        <input type='hidden' id='sessionId' name='sessionId' value='$sessionId'>
                    </td>
                </tr>";

            // Print remaining coach info
            print "
                </form>
            </table><br><br>";
        }
    }

    /**
     * @param Referee $referee
     */
    private function printMaxGamesPerDay($referee)
    {
        print "
                <tr>
                     <td colspan='2' align='center'>Max Games Per Day: ";

        $maxGamesName = View_Base::MAX_GAMES_PER_DAY;
        print "
                        <select name='$maxGamesName' required>";

        for($i = 1; $i < 10; ++$i) {
            $selected = ($referee->maxGamesPerDay == $i) ? ' selected ' : '';
            print "
                            <option value='$i' $selected>$i</option>";
        }

        print "
                        </select>";

        print "
                    </td>
                </tr>";
    }
}