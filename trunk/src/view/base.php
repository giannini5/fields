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
    const ADMIN_LOGIN_PAGE    = '/admin_login';
    const ADMIN_HOME_PAGE     = '/admin_home';
    const ADMIN_SEASON_PAGE   = '/admin_season';
    const ADMIN_DIVISION_PAGE = '/admin_division';
    const ADMIN_LOCATION_PAGE = '/admin_location';
    const ADMIN_FACILITY_PAGE = '/admin_facility';
    const ADMIN_FIELD_PAGE    = '/admin_field';

    # Coach/Manager Pages
    const WELCOME_PAGE          = '/welcome';
    const LOGIN_PAGE            = '/login';
    const SHOW_RESERVATION_PAGE = '/showReservation';
    const SELECT_FACILITY_PAGE  = '/selectFacility';

    # Operations
    const SUBMIT           = 'submit';

    # Operation Values
    const CREATE_ACCOUNT   = 'Create Account';
    const SIGN_IN          = 'Sign In';
    const SIGN_OUT         = 'Sign Out';
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
    const START_TIME                = 'startTime';
    const END_TIME                  = 'endTime';
    const RESERVATION_ID            = 'reservationId';
    const FILTER_FACILITY_ID        = 'filterFacilityId';
    const FILTER_DIVISION_ID        = 'filterDivisionId';
    const FILTER_LOCATION_ID        = 'filterGeographicAreaId';

    # Request Attributes
    const NEW_SELECTION = 'newSelection';

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
     */
    protected function displayInput($request, $type, $name, $placeHolder, $requiredString) {
        $requiredString = empty($requiredString) ? '&nbsp' : $requiredString;
        print "
                <tr>
                    <td align='left'><font color='lightblue'><b>$request</b></font></td>
                    <td align='left'>
                        <input style='width: 135px' type='$type' name='$name' placeholder='$placeHolder'>
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
     */
    public function displaySelector($selectorTitle, $selectorName, $defaultSelection, $selectorData) {
        print "
            <tr>
                <td align='left'><font color='lightblue'><b>$selectorTitle</b></font></td>
                <td align='left'>";

        print "
                    <select style='width: 140px' name='$selectorName' required>
                        <option value=''>$defaultSelection</option>";

        foreach ($selectorData as $identifier=>$data) {
            print "
                        <option value='$identifier'>$data</option>";
        }

        print "
                    </select>";

        print "
                </td>
            </tr>";
    }

    /**
     * @brief Static function to help with debugging
     */
    static function htmlFormatArray($arr) {
        $retStr = '<ul>';
        if (is_array($arr)) {
            foreach ($arr as $key=>$val) {
                if (is_array($val)){
                    $retStr .= '<li>' . $key . ' => ' . htmlFormatArray($val) . '</li>';
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