<?php

/**
 * @brief: Abstract base class for all views.
 *          - Page names needs to be added here
 *          - Operations are added here (example: submit)
 *          - Operation values are added here (example: Login)
 *          - Abstract methods must be implemented by child classes
 */
abstract class View_Base {
    # Administrator Pages
    const ADMIN_LOGIN_PAGE        = '/admin_login';
    const ADMIN_HOME_PAGE         = '/admin_home';
    const ADMIN_SEASON_PAGE       = '/admin_season';
    const ADMIN_DIVISION_PAGE     = '/admin_division';
    const ADMIN_LOCATION_PAGE     = '/admin_location';
    const ADMIN_FACILITY_PAGE     = '/admin_facility';
    const ADMIN_FIELD_PAGE        = '/admin_field';
    const ADMIN_TRANSACTION_PAGE  = '/admin_transaction';
    const ADMIN_RESERVATIONS_PAGE = '/admin_reservations';

    # Coach/Manager Pages
    const WELCOME_PAGE               = '/welcome';
    const CREATE_ACCOUNT_PAGE        = '/createAccount';
    const LOGIN_PAGE                 = '/login';
    const SHOW_RESERVATION_PAGE      = '/showReservation';
    const SELECT_FIELD_PAGE          = '/selectField';
    const IMAGE_PAGE                 = '/image';
    const HELP_PAGE                  = '/help';
    const TEST_POST_PAGE             = '/testPost';

    # Operations
    const SUBMIT           = 'submit';

    # Operation Values
    const CREATE_ACCOUNT   = 'Create Account';
    const CREATE           = 'Create';
    const UPDATE           = 'Update';
    const SIGN_IN          = 'Sign In';
    const SIGN_OUT         = 'Sign Out';
    const NO_BUTTON        = 'NoButton';
    const SELECT           = 'Select';
    const DELETE           = 'Delete';
    const FILTER           = 'Filter';

    # Post Attribute Names
    const SESSION_ID                = 'sessionId';
    const FACILITY_ID               = 'facilityId';
    const FIELD_ID                  = 'fieldId';
    const MONDAY                    = 'Monday';
    const TUESDAY                   = 'Tuesday';
    const WEDNESDAY                 = 'Wednesday';
    const THURSDAY                  = 'Thursday';
    const FRIDAY                    = 'Friday';
    const SATURDAY                  = 'Saturday';
    const SUNDAY                    = 'Sunday';
    const BEGIN_RESERVATION_DATE    = 'beginReservationDate';
    const START_DATE                = 'startDate';
    const END_DATE                  = 'endDate';
    const START_TIME                = 'startTime';
    const END_TIME                  = 'endTime';
    const RESERVATION_ID            = 'reservationId';
    const FILTER_FACILITY_ID        = 'filterFacilityId';
    const FILTER_DIVISION_ID        = 'filterDivisionId';
    const FILTER_LOCATION_ID        = 'filterGeographicAreaId';
    const FILTER_TEAM_ID            = 'filterTeamId';

    const SEASON_ID                 = 'seasonId';
    const DIVISION_ID               = 'divisionId';
    const DIVISION_IDS              = 'divisionIds';
    const LOCATION_ID               = 'locationId';
    const LOCATION_IDS              = 'locationIds';

    const EMAIL_ADDRESS             = 'emailAddress';
    const SUBJECT                   = 'subject';
    const HELP_REQUEST              = 'helpRequest';

    # Request Attributes
    const NEW_SELECTION = 'newSelection';
    const IMAGE         = 'image';

    # Colors
    const AQUA = '#069';

    # Member variables
    protected $m_urlParams;
    protected $m_controller;

    protected $m_pageName;
    protected $m_styles;

    /**
     * @brief: Construct a new instance of this base class.
     *
     * @param: $page - Name of the page being constructed.  Must be defined as a const
     *                 in the above list.
     * @param $controller - Controller that contains data used when rendering this view.
     */
    public function __construct($page, $controller) {
        $this->m_pageName = $page;
        $this->m_styles = new View_Styles();
        $this->m_urlParams = '';
        $this->m_controller = $controller;
    }

    /**
     * @brief: Display an input box used inside of a form to get data.
     *
     * @param: $request - String that describes the data being requested.
     * @param: $type - Type of input being requested (string, int, etc)
     * @param: $placeHolder - Placeholder input to show in input box
     * @param: $requiredString - String to show just after input box
     * @param: $collapsible -  - Collapsible java script class - defaults to NULL
     */
    protected function displayInput($request, $type, $name, $placeHolder, $requiredString, $value = '', $collapsible = NULL, $colspan = 1) {
        $requiredString = empty($requiredString) ? '&nbsp' : $requiredString;
        $valueString = empty($value) ? '' : ", value='$value'";
        $collapsibleClass = isset($collapsible) ? "class='$collapsible'" : '';

        print "
                <tr $collapsibleClass>
                    <td align='left' nowrap><font color='" . View_Base::AQUA . "'><b>$request</b></font></td>
                    <td align='left' colspan='$colspan'>
                        <input style='width: 135px' type='$type' name='$name' placeholder='$placeHolder'$valueString>
                    </td>
                    <td>
                        <span class='error'>$requiredString</span>
                    </td>
                </tr>";
    }

    /**
     * @brief: Display a selector drop down.
     *
     * @param: $selectorTitle - Describes the data being selected
     * @param: $selectorName - Name for selector (used when processing POST)
     * @param: $defaultSelection - Default selection to show
     * @param: $selectorData - Array of data identifier=>string where the identifier is the value selected
     * @param: $currentSelection - Current selection (if any) defaults to empty string
     * @param: $collapsible -  - Collapsible java script class - defaults to NULL
     */
    public function displaySelector($selectorTitle, $selectorName, $defaultSelection, $selectorData, $currentSelection = '', $collapsible = NULL) {
        $collapsibleClass = isset($collapsible) ? "class='$collapsible'" : '';
        print "
            <tr $collapsibleClass>
                <td align='left'><font color='" . View_Base::AQUA . "'><b>$selectorTitle</b></font></td>
                <td align='left'>";

        print "
                    <select style='width: 140px' name='$selectorName' required>
                        <option value=''>$defaultSelection</option>";

        foreach ($selectorData as $identifier=>$data) {
            $selected = $currentSelection == $data ? ' selected ' : '';
            print "
                        <option value='$identifier'$selected>$data</option>";
        }

        print "
                    </select>";

        print "
                </td>
            </tr>";
    }

    /**
     * @brief: Display a multi-selector drop down.
     *
     * @param: $selectorTitle - Describes the data being selected
     * @param: $selectorName - Name for selector (used when processing POST)
     * @param: $defaultSelections - Array of default selections to pre-select
     * @param: $selectorData - Array of data identifier=>string where the identifier is the value selected
     * @param: $size - Size of the selector
     * @param: $collapsible -  - Collapsible java script class - defaults to NULL
     * @param: $colspan - Number of columns to span
     */
    public function displayMultiSelector($selectorTitle, $selectorName, $defaultSelections, $selectorData, $size, $collapsible = NULL, $colspan = 1) {
        $collapsibleClass = isset($collapsible) ? "class='$collapsible'" : '';

        $dropDownHTML = '';
        foreach ($selectorData as $identifier => $data) {
            $selected = in_array($identifier, $defaultSelections) ? ' selected ' : '';
            $dropDownHTML .= "<option value='$identifier'$selected>$data</option>";
        }

        print "
            <tr $collapsibleClass>
                <td align='left'><font color='" . View_Base::AQUA . "'><b>$selectorTitle</b></font></td>
                <td align='left' colspan='$colspan'>
                    <select size=$size name='" . $selectorName . "[]' multiple='multiple'>$dropDownHTML</select>
                </td>
            </tr>";
    }

    /**
     * @brief: Display a radio button selector
     *
     * @param: $selectorTitle - Describes the data being selected
     * @param: $selectorName - Name for selector (used when processing POST)
     * @param: $selectorData - Array of data identifier=>string where the identifier is the value selected
     * @param: $currentSelection - Current selection (if any) defaults to empty string
     * @param: $collapsible -  - Collapsible java script class - defaults to NULL
     * @param: $colspan - Number of columns to span
     */
    public function displayRadioSelector($selectorTitle, $selectorName, $selectorData, $currentSelection = '', $collapsible = NULL, $colspan = 1) {
        $collapsibleClass = isset($collapsible) ? "class='$collapsible'" : '';

        print "
            <tr $collapsibleClass>
                <td align='left'><font color='" . View_Base::AQUA . "'><b>$selectorTitle</b></font></td>
                <td align='left' colspan='$colspan'>";

        foreach ($selectorData as $identifier=>$data) {
            $checked = $currentSelection == $data ? ' checked ' : '';
            print "
                    <input type=radio name='$selectorName' value='$identifier' $checked>$data";
        }

        print "
                </td>
            </tr>";
    }

    /**
     * @brief Print start time and end time selectors
     *
     * @param $maxColumns - For colspan of field assignments table
     * @param $collapsible - Collapsible CSS
     * @param string $defaultStartTime - Default selection for startTime HH:MM:SS (defaults to 03:30:00)
     * @param string $defaultEndTime - Default selection for endTime HH:MM:SS (defaults to 07:00:00)
     * @param $colspan - Number of columns to span
     */
    public function printTimeSelectors($maxColumns, $defaultStartTime='03:30:00', $defaultEndTime='07:00:00', $collapsible = NULL, $colspan = 1)
    {
        $startTimeSectionHTML = '';
        $endTimeSectionHTML = '';
        $collapsibleClass = isset($collapsible) ? "class='$collapsible'" : '';

        $minute = 0;
        for ($hour = 3; $hour <= 8; ++$hour) {
            while ($minute <= 45) {
                $time = sprintf("%2d:%02d", $hour, $minute);

                // Normalize the time to check if it is a selected time
                $dateTime = DateTime::createFromFormat('Y-m-d H:i:s', "2015-06-01 $time" . ":00");
                $timeToCheck = $dateTime->format('H:i:s');

                $startTimeSelected = ($timeToCheck == $defaultStartTime) ? ' selected ' : '';
                $endTimeSelected = ($timeToCheck == $defaultEndTime) ? ' selected ' : '';

                // Populate the start and stop end drop downs
                $startTimeSectionHTML .= "<option value='$time'$startTimeSelected>$time</option>";
                $endTimeSectionHTML .= "<option value='$time'$endTimeSelected>$time</option>";

                $minute += 15;
            }
            $minute = 0;
        }

        print "
                <tr $collapsibleClass>
                    <td><font color='" . View_Base::AQUA . "'><b>Start Time:&nbsp</b></font></td>
                    <td colspan='$colspan'><select name=" . View_Base::START_TIME . ">$startTimeSectionHTML</select></td>
                </tr>
                <tr $collapsibleClass>
                    <td><font color='" . View_Base::AQUA . "'><b>End Time:&nbsp</b></font></td>
                    <td colspan='$colspan'><select name=" . View_Base::END_TIME . ">$endTimeSectionHTML</select></td>
                </tr>";
    }

    /**
     * @brief Print the days that can be selected
     *
     * @param $maxColumns  - For colspan if needed
     * @param $collapsible - Collapsible CSS
     * @param $daysOfWeek  - Days of week selected $daysOfWeek[0] is Monday
     */
    protected function printDaySelector($maxColumns, $collapsible, $daysOfWeek = '') {
        $monChecked = (isset($daysOfWeek[0]) and $daysOfWeek[0] == 1) ? 'checked' : '';
        $tueChecked = (isset($daysOfWeek[1]) and $daysOfWeek[1] == 1) ? 'checked' : '';
        $wedChecked = (isset($daysOfWeek[2]) and $daysOfWeek[2] == 1) ? 'checked' : '';
        $thuChecked = (isset($daysOfWeek[3]) and $daysOfWeek[3] == 1) ? 'checked' : '';
        $friChecked = (isset($daysOfWeek[4]) and $daysOfWeek[4] == 1) ? 'checked' : '';

        print "
                <tr class='$collapsible'>
                    <td><font color='" . View_Base::AQUA . "'><b>Days:&nbsp</b></font></td>
                    <td nowrap>
                        <nobr><input type=checkbox name='Monday'    id='Monday'    value='Monday'    $monChecked>Monday</nobr>
                        <nobr><input type=checkbox name='Tuesday'   id='Tuesday'   value='Tuesday'   $tueChecked>Tuesday</nobr>
                        <nobr><input type=checkbox name='Wednesday' id='Wednesday' value='Wednesday' $wedChecked>Wednesday</nobr>
                        <nobr><input type=checkbox name='Thursday'  id='Thursday'  value='Thursday'  $thuChecked>Thursday</nobr>
                        <nobr><input type=checkbox name='Friday'    id='Friday'    value='Friday'    $friChecked>Friday</nobr>
                    </td>
                </tr>";
    }


    /**
     * @brief: Display Calendar Date Selector
     *          - Start Date
     *          - End Date
     *
     * @param $maxColumns - For colspan of field assignments table
     * @param string $defaultStartDate - Default selection for startDate
     * @param string $defaultEndDate - Default selection for endDate
     * @param $collapsible - Collapsible CSS
     * @param $colspan - Number of columns to span
     */
    protected function displayCalendarSelector($maxColumns, $defaultStartDate, $defaultEndDate, $collapsible = NULL, $colspan = 1)
    {
        $this->displayCalendarDateSelector($maxColumns, View_Base::START_DATE, 'From', $defaultStartDate, $collapsible, $colspan);
        $this->displayCalendarDateSelector($maxColumns, View_Base::END_DATE, 'To', $defaultEndDate, $collapsible, $colspan);
    }

    /**
     * @brief: Display Calendar Date Selector
     *          - Start Date
     *          - End Date
     *
     * @param $maxColumns - For colspan of field assignments table
     * @param string $id - field identifier
     * @param string $tag - Input tag
     * @param string $defaultDate - Default selection for date
     * @param string $defaultEndDate - Default selection for endDate
     * @param $collapsible - Collapsible CSS
     * @param $colspan - Number of columns to span
     */
    protected function displayCalendarDateSelector($maxColumns, $id, $tag, $defaultDate, $collapsible = NULL, $colspan = 1)
    {
        $collapsibleClass = isset($collapsible) ? "class='$collapsible'" : '';

        print "
            <tr $collapsibleClass>
                <td><font color='" . View_Base::AQUA . "'><b>$tag:</b></font></td>
                <td colpan='$colspan'>
                    <input type='text' size=11 id='$id' name='$id' value='$defaultDate' style='font-size:11px'>
                </td>
            </tr>";
    }

    /**
     * @brief Print the drop down list of facilities for filtering by facility
     *
     * @param $facilities - List of facilities for filtering
     * @param $filterFacilityId - Default to selected facility or All if none selected
     */
    public function printFacilitySelector($facilities, $filterFacilityId) {
        $selectorHTML = '';
        $selectorHTML .= "<option value='0'";
        $selectorHTML .= " ";
        $selectorHTML .= ">All</option>";

        foreach ($facilities as $facility) {
            // Populate the facilities drop down
            $selected = ($facility->id == $filterFacilityId) ? ' selected ' : '';
            $selectorHTML .= "<option value='$facility->id' $selected>$facility->name</option>";
        }

        print "
                <tr>
                    <td><font color='#069'><b>Location:&nbsp</b></font></td>
                    <td><select name='" . View_Base::FILTER_FACILITY_ID . "'>" . $selectorHTML . "</select></td>
                </tr>";
    }

    /**
     * @brief Print the drop down list of divisions for filtering by facility
     *
     * @param $filterDivisionId - Show selected division or the coaches division if the filter is 0
     */
    public function printDivisionSelector($filterDivisionId) {
        $selectorHTML = '';
        $selectorHTML .= "<option value='0' ";
        $selectorHTML .= ">All</option>";

        foreach ($this->m_controller->m_divisions as $division) {
            $selected = ($division->id == $filterDivisionId) ? ' selected ' : '';
            $selectorHTML .= "<option value='$division->id' $selected>$division->name</option>";
        }

        print "
                <tr>
                    <td><font color='#069'><b>Division:&nbsp</b></font></td>
                    <td><select name='" . View_Base::FILTER_DIVISION_ID . "'>" . $selectorHTML . "</select></td>
                </tr>";
    }

    /**
     * @brief Print the drop down list of geographic selectors for filtering by facility\
     *
     * @param int $filterLocationId - Default selection
     */
    public function printGeographicSelector($filterLocationId) {
        $locations = $this->m_controller->getLocations();

        $selectorHTML = '';
        $selectorHTML .= "<option value='0' ";
        $selectorHTML .= ">All</option>";

        foreach ($locations as $location) {
            $selected = ($location->id == $filterLocationId) ? ' selected ' : '';
            $selectorHTML .= "<option value='$location->id' $selected>$location->name</option>";
        }

        print "
                <tr>
                    <td><font color='#069'><b>Geographic Area:&nbsp</b></font></td>
                    <td><select name='" . View_Base::FILTER_LOCATION_ID . "'>" . $selectorHTML . "</select></td>
                </tr>";
    }

    /**
     * @brief Print the drop down list of teams by division with coaches name for selection
     *
     * @param int $filterTeamId - Default selection
     */
    public function printTeamSelector($filterTeamId) {
        $teams = $this->m_controller->getTeams();

        $selectorHTML = '';
        $selectorHTML .= "<option value='0' ";
        $selectorHTML .= ">All</option>";

        foreach ($teams as $team) {
            $selected = ($team->id == $filterTeamId) ? ' selected ' : '';
            $teamName = $team->m_division->name . $team->gender . ': ' . $team->m_coach->name;
            $selectorHTML .= "<option value='$team->id' $selected>$teamName</option>";
        }

        print "
                <tr>
                    <td><font color='#069'><b>Team:&nbsp</b></font></td>
                    <td><select name='" . View_Base::FILTER_TEAM_ID . "'>" . $selectorHTML . "</select></td>
                </tr>";
    }

    /**
     * @brief Print the error seen with the last transaction (no op if no error)
     */
    protected function printError() {
        if (isset($this->m_controller->m_errorString) and !empty($this->m_controller->m_errorString)) {
            $errorString = $this->m_controller->m_errorString;

            print "
            <table valign='top' align='center' width='625' border='0' cellpadding='5' cellspacing='0'>
                <tr>
                    <td><h1 align='left'><font color='red' size='4'>$errorString</font></h1></td>
                </tr>
            </table>";
        }
    }

    /**
     * @brief Static function to help with debugging
     */
    static function htmlFormatArray($arr) {
        $retStr = '<ul>';
        if (is_array($arr)) {
            foreach ($arr as $key=>$val) {
                if (is_array($val)){
                    $retStr .= '<li>' . $key . ' => ' . View_Base::htmlFormatArray($val) . '</li>';
                }
                else {
                    $retStr .= '<li>' . $key . ' => ' . $val . '</li>';
                }
            }
        }
        $retStr .= '</ul>';
        return $retStr;
    }

    /**
     * @brief: Print out HTML to display this page.  Derived classes must implement
     *         the "render()" method to print out their page content.
     */
    abstract function displayPage();

    /**
     * @brief Display Header Navigation (Welcome, Show Reservatio, etc.)
     */
    abstract function displayHeaderNavigation();

    /**
     * @brief Render the contents of the page
     */
    abstract function render();
}
