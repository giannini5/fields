<?php

use \DAG\Domain\Schedule\Division;
use \DAG\Domain\Schedule\Family;
use \DAG\Domain\Schedule\Coach;

/**
 * @brief: Abstract base class for all views.
 *          - Page names needs to be added here
 *          - Operations are added here (example: submit)
 *          - Operation values are added here (example: Login)
 *          - Abstract methods must be implemented by child classes
 */
abstract class View_Base {
    # Practice field Administration Pages
    const ADMIN_LOGIN_PAGE        = '/admin_practice_login';
    const ADMIN_HOME_PAGE         = '/admin_practice_home';
    const ADMIN_SEASON_PAGE       = '/admin_practice_season';
    const ADMIN_DIVISION_PAGE     = '/admin_practice_division';
    const ADMIN_LOCATION_PAGE     = '/admin_practice_location';
    const ADMIN_FACILITY_PAGE     = '/admin_practice_facility';
    const ADMIN_FIELD_PAGE        = '/admin_practice_field';
    const ADMIN_TRANSACTION_PAGE  = '/admin_practice_transaction';
    const ADMIN_RESERVATIONS_PAGE = '/admin_practice_reservations';

    # Coach/Manager Practice Field Selection Pages
    const WELCOME_PAGE               = '/welcome';
    const CREATE_ACCOUNT_PAGE        = '/createAccount';
    const LOGIN_PAGE                 = '/login';
    const SHOW_RESERVATION_PAGE      = '/showReservation';
    const SELECT_FIELD_PAGE          = '/selectField';
    const IMAGE_PAGE                 = '/image';
    const HELP_PAGE                  = '/help';
    const TEST_POST_PAGE             = '/testPost';

    # Schedule Creation Pages
    const SCHEDULE_UPLOAD_PAGE      = '/admin_schedule_home';
    const SCHEDULE_SEASON_PAGE      = '/admin_schedule_season';
    const SCHEDULE_GAME_DATE_PAGE   = '/admin_schedule_gameDate';
    const SCHEDULE_FACILITIES_PAGE  = '/admin_schedule_facilities';
    const SCHEDULE_FIELDS_PAGE      = '/admin_schedule_fields';
    const SCHEDULE_TEAMS_PAGE       = '/admin_schedule_teams';
    const SCHEDULE_FAMILY_PAGE      = '/admin_schedule_families';
    const SCHEDULE_DIVISIONS_PAGE   = '/admin_schedule_divisions';
    const SCHEDULE_SCHEDULES_PAGE   = '/admin_schedule_schedules';
    const SCHEDULE_PREVIEW_PAGE     = '/admin_schedule_preview';

    # Schedule Viewing Pages
    const SCHEDULE_HOME_PAGE        = '/schedule';
    const SCHEDULE_TEAM_PAGE        = '/schedule/team';
    const SCHEDULE_DIVISION_PAGE    = '/schedule/division';

    # Operations
    const SUBMIT           = 'submit';

    # Operation Values
    const CREATE_ACCOUNT        = 'Create Account';
    const CREATE                = 'Create';
    const VIEW                  = 'View';
    const UPDATE                = 'Update';
    const SIGN_IN               = 'Sign In';
    const SIGN_OUT              = 'Sign Out';
    const NO_BUTTON             = 'NoButton';
    const SELECT                = 'Select';
    const DELETE                = 'Delete';
    const MOVE                  = 'Move';
    const REMOVE                = 'Remove';
    const ADD                   = 'Add';
    const CLEAR                 = 'Clear';
    const POPULATE              = 'Populate';
    const PUBLISH               = 'Publish';
    const FILTER                = 'Filter';
    const UPLOAD_FILE           = 'Upload File';
    const UPLOAD_PLAYER_FILE    = 'Upload Player File';
    const UPLOAD_FACILITY_FILE  = 'Upload Facility File';
    const UPLOAD_FIELD_FILE     = 'Upload Field File';
    const FIELD_VIEW            = 'Field View';
    const DIVISION_VIEW         = 'Division View';
    const TEAM_VIEW             = 'Team View';
    const FAMILY_VIEW           = 'Family View';
    const FAMILY_FIX            = 'Fix';

    # Checkbox Names
    const SHOW_PLAYERS          = 'showPlayers';

    # Post Attribute Names
    const SESSION_ID                = 'sessionId';
    const FACILITY_ID               = 'facilityId';
    const FIELD_ID                  = 'fieldId';
    const GAME_DATE_ID              = 'gameDateId';
    const MONDAY                    = 'Monday';
    const TUESDAY                   = 'Tuesday';
    const WEDNESDAY                 = 'Wednesday';
    const THURSDAY                  = 'Thursday';
    const FRIDAY                    = 'Friday';
    const SATURDAY                  = 'Saturday';
    const SUNDAY                    = 'Sunday';
    const BEGIN_RESERVATION_DATE    = 'beginReservationDate';
    const DAY                       = 'day';
    const START_DATE                = 'startDate';
    const END_DATE                  = 'endDate';
    const START_TIME                = 'startTime';
    const END_TIME                  = 'endTime';
    const RESERVATION_ID            = 'reservationId';
    const FILTER_FACILITY_ID        = 'filterFacilityId';
    const FILTER_DIVISION_ID        = 'filterDivisionId';
    const FILTER_LOCATION_ID        = 'filterGeographicAreaId';
    const FILTER_TEAM_ID            = 'filterTeamId';
    const FILTER_COACH_ID           = 'filterCoachId';
    const NAME                      = 'name';
    const ENABLED                   = 'enabled';
    const ADDRESS1                  = 'address1';
    const ADDRESS2                  = 'address2';
    const CITY                      = 'city';
    const STATE                     = 'state';
    const POSTAL_CODE               = 'postalCode';
    const CONTACT_NAME              = 'contactName';
    const CONTACT_EMAIL             = 'contactEmail';
    const CONTACT_PHONE             = 'contactPhone';
    const FIELD_UPDATE_DATA         = 'fieldUpdateData';
    const TEAM_POOL_UPDATE_DATA     = 'teamPoolUpdateData';
    const CROSS_POOL_UPDATE_DATA    = 'crossPoolUpdateData';
    const GAMES_PER_TEAM            = 'gamesPerTeam';
    const GAME_ID                   = 'gameId';
    const GAME_TIME                 = 'gameTime';
    const FLIGHT_UPDATE_DATA        = 'flightUpdateData';
    const SCHEDULE_TYPE             = 'scheduleType';

    const SEASON_ID                 = 'seasonId';
    const DIVISION_ID               = 'divisionId';
    const DIVISION_IDS              = 'divisionIds';
    const DIVISION_NAMES            = 'divisionNames';
    const DIVISION_NAME             = 'divisionName';
    const LOCATION_ID               = 'locationId';
    const LOCATION_IDS              = 'locationIds';
    const SCHEDULE_ID               = 'scheduleId';
    const POOL_ID                   = 'poolIds';
    const FAMILY_ID                 = 'familyId';

    const EMAIL_ADDRESS             = 'emailAddress';
    const SUBJECT                   = 'subject';
    const HELP_REQUEST              = 'helpRequest';

    # Request Attributes
    const NEW_SELECTION = 'newSelection';
    const IMAGE         = 'image';

    # Colors
    const AQUA          = '#069';
    const CREATE_COLOR  = 'lightskyblue';
    const VIEW_COLOR    = 'aquamarine';

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
     * @param: $request         - String that describes the data being requested.
     * @param: $type            - Type of input being requested (string, int, password, etc)
     * @param: $placeHolder     - Placeholder input to show in input box
     * @param: $requiredString  - String to show just after input box
     * @param: $collapsible     - Collapsible java script class - defaults to NULL
     * @param: $newRow          - Defaults to true
     * @param: $width           - Defaults to 135px
     * @param: $showError       - Defaults to true
     */
    protected function displayInput($request, $type, $name, $placeHolder, $requiredString, $value = '', $collapsible = NULL, $colspan = 1, $newRow = true, $width = 135, $showError = true) {
        $requiredString     = empty($requiredString) ? '&nbsp' : $requiredString;
        $valueString        = empty($value) ? '' : ", value='$value'";
        $collapsibleClass   = isset($collapsible) ? "class='$collapsible'" : '';
        $width              = $width . 'px';

        if ($newRow) {
            print "
                <tr $collapsibleClass>";
        }

        if (!empty($request)) {
            print "
                    <td align='left' nowrap><font color='" . View_Base::AQUA . "'><b>$request</b></font></td>";
        }

        print "
                    <td align='left' colspan='$colspan'>
                        <input style='width: $width' type='$type' name='$name' placeholder='$placeHolder'$valueString>
                    </td>";

        if ($showError) {
            print "
                    <td>
                        <span class='error'>$requiredString</span>
                    </td>";
        }

        if ($newRow) {
            print "
                </tr>";
        }
    }

    /**
     * @brief: Display a selector drop down.
     *
     * @param: $selectorTitle       - Describes the data being selected
     * @param: $selectorName        - Name for selector (used when processing POST)
     * @param: $defaultSelection    - Default selection to show
     * @param: $selectorData        - Array of data identifier=>string where the identifier is the value selected
     * @param: $currentSelection    - Current selection (if any) defaults to empty string
     * @param: $collapsible         - Collapsible java script class - defaults to NULL
     * @param: $newRow              - defaults to true
     * @param: $width               - defaults to 140 (px)
     * @param: $align               - defaults to left
     * @param: $disabledTag         - defaults to ''; used to prompt for a selection
     * @param: $selectBackgroundColor   - defaults to ''
     */
    public function displaySelector($selectorTitle, $selectorName, $defaultSelection, $selectorData, $currentSelection = '', $collapsible = NULL, $newRow = true, $width = 140, $align='left', $disabledTag='', $selectBackgroundColor='')
    {
        $collapsibleClass = isset($collapsible) ? "class='$collapsible'" : '';
        $selectBgColorHTML = empty($selectBackgroundColor) ? '' : "background-color: $selectBackgroundColor";

        if ($newRow) {
            print "
            <tr $collapsibleClass>";
        }

        $width = $width . "px";
        if (!empty($selectorTitle)) {
            print "
                <td align='$align'><font color='" . View_Base::AQUA . "'><b>$selectorTitle</b></font></td>";
        }

        print "
                <td align='left'>
                    <select style='width: $width; $selectBgColorHTML' name='$selectorName' required>";

        if (!empty($defaultSelection)) {
            print "
                        <option value=''>$defaultSelection</option>";
        }

        if (!empty($disabledTag)) {
            $selected = empty($currentSelection) ? ' selected ' : '';
            print "
                        <option disabled value=''$selected>$disabledTag</option>";
        }

        foreach ($selectorData as $identifier=>$data) {
            $selected = $currentSelection == $data ? ' selected ' : '';
            print "
                        <option value='$identifier'$selected>$data</option>";
        }

        print "
                    </select>
                </td>";

        if ($newRow) {
            print "
            </tr>";
        }
    }

    /**
     * @brief: Display a multi-selector drop down.
     *
     * @param: $selectorTitle       - Describes the data being selected
     * @param: $selectorName        - Name for selector (used when processing POST)
     * @param: $defaultSelections   - Array of default selections to pre-select
     * @param: $selectorData        - Array of data identifier=>string where the identifier is the value selected
     * @param: $size                - Size of the selector
     * @param: $collapsible         - Collapsible java script class - defaults to NULL
     * @param: int $colspan         - Number of columns to span
     * @param: bool $newRow         - defaults to true
     * @param: int $rowSpan         - defaults to 1
     */
    public function displayMultiSelector($selectorTitle, $selectorName, $defaultSelections, $selectorData, $size, $collapsible = NULL, $colspan = 1, $newRow = true, $rowSpan = 1) {
        $collapsibleClass = isset($collapsible) ? "class='$collapsible'" : '';

        $dropDownHTML = '';
        foreach ($selectorData as $identifier => $data) {
            $selected = in_array($identifier, $defaultSelections) ? ' selected ' : '';
            $dropDownHTML .= "<option style='font-size: 8px' value='$identifier'$selected>$data</option>";
        }

        if ($newRow) {
            print "
            <tr $collapsibleClass>";
        }

        print "
                <td align='left' rowspan='$rowSpan'><font color='" . View_Base::AQUA . "'><b>$selectorTitle</b></font></td>
                <td align='left' colspan='$colspan' rowspan='$rowSpan'>
                    <select size=$size name='" . $selectorName . "[]' multiple='multiple'>$dropDownHTML</select>
                </td>";

        if ($newRow) {
            print "
            </tr>";
        }
    }

    /**
     * @brief: Display a radio button selector
     *
     * @param: $selectorTitle       - Describes the data being selected
     * @param: $selectorName        - Name for selector (used when processing POST)
     * @param: $selectorData        - Array of data identifier=>string where the identifier is the value selected
     * @param: $currentSelection    - Current selection (if any) defaults to empty string
     * @param: $collapsible         - Collapsible java script class - defaults to NULL
     * @param: $colspan             - Number of columns to span
     * @param: $newRow              - defaults to true
     */
    public function displayRadioSelector($selectorTitle, $selectorName, $selectorData, $currentSelection = '', $collapsible = NULL, $colspan = 1, $newRow = true) {
        $collapsibleClass = isset($collapsible) ? "class='$collapsible'" : '';

        if ($newRow) {
            print "
            <tr $collapsibleClass>";

        }

        print "
                <td nowrap align='left'><font color='" . View_Base::AQUA . "'><b>$selectorTitle</b></font></td>
                <td nowrap align='left' colspan='$colspan'>";

        foreach ($selectorData as $identifier=>$data) {
            $checked = $currentSelection == $data ? ' checked ' : '';
            print "
                    <input type=radio name='$selectorName' value='$identifier' $checked>$data";
        }

        print "
                </td>";

        if ($newRow) {
            print "
            </tr>";
        }
    }

    /**
     * @brief Print start time and end time selectors
     *
     * @param $maxColumns - For colspan of field assignments table
     * @param string $defaultStartTime - Default selection for startTime HH:MM:SS (defaults to 03:30:00)
     * @param string $defaultEndTime - Default selection for endTime HH:MM:SS (defaults to 07:00:00)
     * @param $collapsible - Collapsible CSS
     * @param int $colspan - Number of columns to span
     * @param string $startTimeLabel
     * @param string $endTimeLabel
     */
    public function printTimeSelectors(
        $maxColumns,
        $defaultStartTime='03:30:00',
        $defaultEndTime='07:00:00',
        $collapsible = NULL,
        $colspan = 1,
        $startTimeLabel = 'Start Time',
        $endTimeLabel = 'End Time')
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
                    <td><font color='" . View_Base::AQUA . "'><b>$startTimeLabel:&nbsp</b></font></td>
                    <td colspan='$colspan'><select name=" . View_Base::START_TIME . ">$startTimeSectionHTML</select></td>
                </tr>
                <tr $collapsibleClass>
                    <td><font color='" . View_Base::AQUA . "'><b>$endTimeLabel:&nbsp</b></font></td>
                    <td colspan='$colspan'><select name=" . View_Base::END_TIME . ">$endTimeSectionHTML</select></td>
                </tr>";
    }

    /**
     * @brief Print start time and end time selectors
     *
     * @param $maxColumns - For colspan of field assignments table
     * @param string $defaultStartTime - Default selection for startTime HH:MM:SS (defaults to 03:30:00)
     * @param string $defaultEndTime - Default selection for endTime HH:MM:SS (defaults to 07:00:00)
     * @param $collapsible - Collapsible CSS
     * @param int $colspan - Number of columns to span
     * @param string $startTimeLabel
     * @param string $endTimeLabel
     */
    public function printGameTimeSelectors(
        $maxColumns,
        $defaultStartTime='08:00:00',
        $defaultEndTime='17:00:00',
        $collapsible = NULL,
        $colspan = 1,
        $startTimeLabel,
        $endTimeLabel)
    {
        $startTimeSectionHTML = '';
        $endTimeSectionHTML = '';
        $collapsibleClass = isset($collapsible) ? "class='$collapsible'" : '';

        $minute = 0;
        for ($hour = 8; $hour <= 17; ++$hour) {
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
                    <td nowrap><font color='" . View_Base::AQUA . "'><b>$startTimeLabel:&nbsp</b></font></td>
                    <td colspan='$colspan'><select name=" . View_Base::START_TIME . ">$startTimeSectionHTML</select></td>
                </tr>
                <tr $collapsibleClass>
                    <td><font color='" . View_Base::AQUA . "'><b>$endTimeLabel:&nbsp</b></font></td>
                    <td colspan='$colspan'><select name=" . View_Base::END_TIME . ">$endTimeSectionHTML</select></td>
                </tr>";
    }

    /**
     * @brief Print time selector
     *
     * @param string    $tag            item identifier
     * @param string    $label          Input label
     * @param string    $defaultTime    Default selected time
     * @param int       $colspan
     */
    public function printTimeSelector(
        $tag,
        $label,
        $defaultTime='08:00:00',
        $colspan = 1)
    {
        $timeSectionHTML = '';

        $minute = 0;
        for ($hour = 8; $hour <= 17; ++$hour) {
            while ($minute <= 45) {
                $time = sprintf("%2d:%02d", $hour, $minute);

                // Normalize the time to check if it is a selected time
                $dateTime = DateTime::createFromFormat('Y-m-d H:i:s', "2015-06-01 $time" . ":00");
                $timeToCheck = $dateTime->format('H:i:s');

                $timeSelected = ($timeToCheck == $defaultTime) ? ' selected ' : '';

                // Populate the drop downs
                $timeSectionHTML .= "<option value='$time'$timeSelected>$time</option>";

                $minute += 15;
            }
            $minute = 0;
        }

        $labelHTML  = empty($label) ? '' : "<td nowrap><font color='" . View_Base::AQUA . "'><b>$label:&nbsp</b></font></td>";
        $newRow     = !empty($label);

        if ($newRow) {
            print "
                <tr>";
        }

        print "
                    $labelHTML
                    <td colspan='$colspan'><select name=$tag>$timeSectionHTML</select></td>";

        if ($newRow) {
            print "
                </tr>";
        }

    }

    /**
     * @brief Print the days that can be selected
     *
     * @param $maxColumns       - For colspan if needed
     * @param $collapsible      - Collapsible CSS
     * @param $daysOfWeek       - Days of week selected $daysOfWeek[0] is Monday
     * @param $label            - Label for input
     * @param $includeWeekend   - Defaults to true
     * @param $newRow           - Defaults to true
     * @param $daysPerRow       - Defaults to 7
     * @param $daysColspan      - Defaults to 1
     */
    protected function printDaySelector($maxColumns, $collapsible, $daysOfWeek = '', $label = 'Days', $includeWeekend = true, $newRow = true, $daysPerRow = 7, $emptyCells = 1, $daysColspan = 1) {
        /*
        $monChecked = (isset($daysOfWeek[0]) and $daysOfWeek[0] == 1) ? 'checked' : '';
        $tueChecked = (isset($daysOfWeek[1]) and $daysOfWeek[1] == 1) ? 'checked' : '';
        $wedChecked = (isset($daysOfWeek[2]) and $daysOfWeek[2] == 1) ? 'checked' : '';
        $thuChecked = (isset($daysOfWeek[3]) and $daysOfWeek[3] == 1) ? 'checked' : '';
        $friChecked = (isset($daysOfWeek[4]) and $daysOfWeek[4] == 1) ? 'checked' : '';
        $satChecked = (isset($daysOfWeek[5]) and $daysOfWeek[5] == 1) ? 'checked' : '';
        $sunChecked = (isset($daysOfWeek[6]) and $daysOfWeek[5] == 1) ? 'checked' : '';
        */

        $weekdayData = array(
            'Monday'    => (isset($daysOfWeek[0]) and $daysOfWeek[0] == 1) ? 'checked' : '',
            'Tuesday'   => (isset($daysOfWeek[1]) and $daysOfWeek[1] == 1) ? 'checked' : '',
            'Wednesday' => (isset($daysOfWeek[2]) and $daysOfWeek[2] == 1) ? 'checked' : '',
            'Thursday'  => (isset($daysOfWeek[3]) and $daysOfWeek[3] == 1) ? 'checked' : '',
            'Friday'    => (isset($daysOfWeek[4]) and $daysOfWeek[4] == 1) ? 'checked' : '',
        );

        $weekendData = array(
            'Saturday'  => (isset($daysOfWeek[5]) and $daysOfWeek[5] == 1) ? 'checked' : '',
            'Sunday'    => (isset($daysOfWeek[6]) and $daysOfWeek[6] == 1) ? 'checked' : '',
        );

        if ($newRow) {
            print "
                <tr class='$collapsible'>";
        }

        if (!empty($label)) {
            print "
                    <td nowrap><font color='" . View_Base::AQUA . "'><b>$label:&nbsp</b></font></td>";
        }

        // Print weekday data
        $currentDaysPrinted = 0;
        print "
                    <td nowrap colspan='$daysColspan'>";

        foreach ($weekdayData as $day => $checked) {
            print "
                        <nobr><input type=checkbox name='$day'    id='$day'    value='$day'    $checked>$day</nobr>";

            $currentDaysPrinted += 1;
            if ($currentDaysPrinted == $daysPerRow) {
                print "
                    </td>
                </tr>
                <tr>";

                for ($i = 0; $i < $emptyCells; $i++) {
                    print "
                    <td>&nbsp</td>";
                }

                print "
                    <td>";
                $currentDaysPrinted = 0;
            }
        }

        if ($includeWeekend)  {
            foreach ($weekendData as $day => $checked) {
                if ($currentDaysPrinted == $daysPerRow) {
                    print "
                    </td>
                </tr>
                <tr>";

                    for ($i = 0; $i < $emptyCells; $i++) {
                        print "
                    <td>&nbsp</td>";
                    }

                    print "
                    <td>";
                    $currentDaysPrinted = 0;
                }

                print "
                        <nobr><input type=checkbox name='$day'  id='$day'  value='$day'  $checked>$day</nobr>";
                $currentDaysPrinted += 1;
            }
        }


        /*
        print "
                        <nobr><input type=checkbox name='Tuesday'   id='Tuesday'   value='Tuesday'   $tueChecked>Tuesday</nobr>";

        $currentDaysPrinted += 1;
        if ($currentDaysPrinted == $daysPerRow) {
            print "
                    </td>
                <tr>
                    <td colspan='$colspanPerRow' align='right'>";
            $currentDaysPrinted = 0;
        }

        print "
                        <nobr><input type=checkbox name='Wednesday' id='Wednesday' value='Wednesday' $wedChecked>Wednesday</nobr>";

        $currentDaysPrinted += 1;
        if ($currentDaysPrinted == $daysPerRow) {
            print "
                    </td>
                <tr>
                    <td colspan='$colspanPerRow' align='right'>";
            $currentDaysPrinted = 0;
        }

        print "
                        <nobr><input type=checkbox name='Thursday'  id='Thursday'  value='Thursday'  $thuChecked>Thursday</nobr>";

        $currentDaysPrinted += 1;
        if ($currentDaysPrinted == $daysPerRow) {
            print "
                    </td>
                <tr>
                    <td colspan='$colspanPerRow' align='right'>";
            $currentDaysPrinted = 0;
        }

        print "
                        <nobr><input type=checkbox name='Friday'    id='Friday'    value='Friday'    $friChecked>Friday</nobr>";

*/
        print "
                    </td>";

        if ($newRow) {
            print"
                </tr>";
        }
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
     * @param $width - defaults to 135 px
     * @param $newRow - defaults to true
     */
    protected function displayCalendarDateSelector($maxColumns, $id, $tag, $defaultDate, $collapsible = NULL, $colspan = 1, $width = 135, $newRow = true)
    {
        $collapsibleClass = isset($collapsible) ? "class='$collapsible'" : '';

        if ($newRow) {
            print "
            <tr $collapsibleClass>";
        }

        if (!empty($tag)) {
            print "
                <td nowrap><font color='" . View_Base::AQUA . "'><b>$tag:</b></font></td>";
        }

        $width = $width . "px";
        print "
                <td colpan='$colspan'>
                    <input type='text' style='width: $width' id='$id' name='$id' value='$defaultDate'>
                </td>";

        if ($newRow) {
            print "
            </tr>";
        }
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
     * @brief Print checkbox
     *
     * @param $checkboxName - Name of the checkbox
     * @param $description  - Description of the checkbox
     * @param $isChecked    - True if box should be checked
     */
    public function printCheckboxSelector($checkboxName, $description, $isChecked)
    {
        $checked = $isChecked ? 'checked' : '';

        print "
        <tr>
            <td>
                <input type='checkbox' name='$checkboxName' value='checked' $checked> $description
            </td>
        </tr>";
    }

    /**
     * @brief Print the error seen with the last transaction (no op if no error)
     */
    protected function printError() {
        if (isset($this->m_controller->m_errorString) and !empty($this->m_controller->m_errorString)) {
            $errorStringElements = explode("\n", $this->m_controller->m_errorString);
            $element = array_shift($errorStringElements);
            $errorString = "<font color='red' size='3'>$element</font>";
            foreach ($errorStringElements as $element) {
                $errorString .= "<br><font color='red' size='1'>$element</font>";
            }

            print "
            <table valign='top' align='center' width='625' border='0' cellpadding='5' cellspacing='0'>
                <tr>
                    <td><h1 align='left'><font color='red' size='3'>$errorString</font></h1></td>
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
     * @brief: Return the unique set of teams based on gener
     *
     * @param array $reservations - list of reservations
     * @param char $gender - Gender 'B' or 'G'
     * @return int - Count of teams
     */
    public function getTeamCount($reservations, $gender) {
        $teams = array();
        foreach ($reservations as $reservation) {
            if ($reservation->m_team->gender == $gender) {
                if (!isset($teams[$reservation->m_team->id])) {
                    $teams[$reservation->m_team->id] = 0;
                }
                $teams[$reservation->m_team->id] += 1;

            }
        }

        return count($teams);
    }

    /**
     * Get selector array for Divisions
     *
     * @param bool $byName          - if true then get by name instead of id.  Defaults to false, by id.
     * @param bool $allOption       - if true then All is an option otherwise All is not allowed
     * @param bool $includeGender   - if true then Gender is included in the display name
     *
     * @return array - id => name
     */
    public function getDivisionsSelector($byName = false, $allOption = false, $includeGender = false)
    {
        $divisions = [];
        if (isset($this->m_controller->m_season))
        {
            $divisions = Division::lookupBySeason($this->m_controller->m_season);
        }

        $divisionsSelector = [];
        if ($allOption) {
            if ($byName) {
                $divisionsSelector['All'] = 'All';
            } else {
                $divisionsSelector[0] = 'All';
            }
        }

        foreach ($divisions as $division) {
            $identifier = $division->id;
            if ($byName) {
                $identifier = $division->name;
                if ($includeGender) {
                    $identifier .= ' ' . $division->gender;
                }
            }
            $divisionsSelector[$identifier] = $includeGender ? $division->name . ' ' . $division->gender : $division->name;
        }

        return $divisionsSelector;
    }

    /**
     * Get selector array for Families
     *
     * @param bool $allOption       - if true then All is an option otherwise All is not allowed
     *
     * @return array - id => Family Last Name
     */
    public function getFamilySelector($allOption = false)
    {
        $familySelector = [];
        $families       = [];

        if (isset($this->m_controller->m_season))
        {
            $families = Family::lookupBySeason($this->m_controller->m_season);
        }

        foreach ($families as $family) {
            $coaches = Coach::lookupByFamily($family);
            if (count($coaches) == 0) {
                continue;
            }

            $identifier = $family->id;
            $familySelector[$identifier] = $coaches[0]->shortName;
        }

        asort($familySelector);

        if ($allOption) {
            $familySelectorWithAll[0] = 'All';
            foreach ($familySelector as $familyId => $lastName) {
                $familySelectorWithAll[$familyId] = $lastName;
            }

            $familySelector = $familySelectorWithAll;
        }

        return $familySelector;
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
