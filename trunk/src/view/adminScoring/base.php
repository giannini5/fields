<?php

use \DAG\Domain\Schedule\Coach;

/**
 * @brief: Abstract base class for all adminScoring views.
 */
abstract class View_AdminScoring_Base extends View_Base {
    /**
     * @brief: Construct a new instance of this base class.
     *
     * @param string            $page       - Name of the page being constructed.
     * @param Controller_Base   $controller - Controller that contains data used when rendering this view.
     */
    public function __construct($page, $controller)
    {
        $navigation         = new View_AdminScoring_Navigation($controller, $page);
        parent::__construct($navigation, $page, "Administer Scoring", $controller, 0);
    }

    /**
     * @brief Render data for display on the page.
     */
    public function render()
    {
        if ($this->m_controller->m_isAuthenticated) {
            $this->renderPage();
        } else {
            $this->renderLogin();
        }
    }

    /**
     * @brief Render sign-in screen
     */
    public function renderLogin() {
        print "
            <table align='center' valign='top' border='1' cellpadding='5' cellspacing='0'>
            <tr><td>
            <table align='center' valign='top' border='0' cellpadding='5' cellspacing='0'>";

        print "
            <form method='post' action='" . self::SCORING_ENTER_SCORES_PAGE . $this->m_urlParams . "'>";

        print "
                <tr>
                    <td colspan='2' style='font-size:24px'><font color='darkblue'><b>Sign In</b></font></td>
                </tr>";

        $this->displayInput('Email Address:', 'text', Model_Fields_PracticeFieldCoordinatorDB::DB_COLUMN_EMAIL, 'email address', $this->m_controller->m_email);
        $this->displayInput('Password:', 'password', Model_Fields_PracticeFieldCoordinatorDB::DB_COLUMN_PASSWORD, 'password', $this->m_controller->m_password);

        print "
                <tr>
                    <td colspan='2' align='right'>
                        <input style='background-color: yellow' name='" . View_Base::SUBMIT . "' type='submit' value='" . View_Base::SUBMIT . "'>
                    </td>
                    <td>&nbsp</td>
                </tr>
            </form>";

        print "
            </table>
            </td></tr></table>";
    }

    /**
     * @brief Print the drop down list of teams by division for selection
     *
     * @param int       $filterTeamId       - Default team selection
     * @param bool      $includeAllOption   - Default to true
     * @param bool      $disabledName       - Default to null
     * @param string    $displayName        - Field's display name
     * @param string    $idName             - Request identifier name
     */
    public function printTeamSelector($filterTeamId, $includeAllOption = true, $disabledName = null, $displayName = null, $idName = View_Base::FILTER_TEAM_ID) {
        $teams          = $this->m_controller->m_teams;
        $displayName    = isset($displayName) ? $displayName : "Team";

        $selectorHTML = '';

        if (isset($disabledName)) {
            $selected       = isset($filterTeamId) ? '' : ' selected ';
            $selectorHTML   .= "<option disabled value='' $selected>$disabledName</option>";
        }

        if ($includeAllOption) {
            $selectorHTML .= "<option value='0' ";
            $selectorHTML .= ">All</option>";
        }

        foreach ($teams as $team) {
            $coach      = Coach::lookupByTeam($team);
            $selected   = ($team->id == $filterTeamId) ? ' selected ' : '';
            $teamName   = $team->nameId . " - " . $coach->shortName;
            $selectorHTML .= "<option value='$team->id' $selected>$teamName</option>";
        }

        print "
                <tr>
                    <td nowrap><font color='#069'><b>$displayName:&nbsp</b></font></td>
                    <td><select name='$idName'>" . $selectorHTML . "</select></td>
                </tr>";
    }

    /**
     * @brief Print the drop down list of coaches by division for selection
     *
     * @param int $filterTeamId - Default selection
     */
    public function printCoachSelector($filterCoachId) {
        $coaches = $this->m_controller->m_coaches;

        $sortedCoaches = [];
        foreach ($coaches as $coach) {
            $sortedCoaches[$coach->id] = $coach->name . " (" . $coach->team->nameId . ")";
        }
        asort($sortedCoaches);

        $selectorHTML = '';
        $selectorHTML .= "<option value='0' ";
        $selectorHTML .= ">All</option>";

        foreach ($sortedCoaches as $id => $name) {
            $selected = ($id == $filterCoachId) ? ' selected ' : '';
            $coachName = $name;
            $selectorHTML .= "<option value='$id' $selected>$coachName</option>";
        }

        print "
                <tr>
                    <td><font color='#069'><b>Coach:&nbsp</b></font></td>
                    <td><select name='" . View_Base::FILTER_COACH_ID . "'>" . $selectorHTML . "</select></td>
                </tr>";
    }
}