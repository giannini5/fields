<?php

/**
 * @brief: Abstract base class for all views.
 *          - Page names needs to be added here
 *          - Operations are added here (example: submit)
 *          - Operation values are added here (example: Login)
 *          - Abstract methods must be implemented by child classes
 */
abstract class View_Base {
    # Pages
    const WELCOME_PAGE          = '/welcome';
    const LOGIN_PAGE            = '/login';
    const SHOW_RESERVATION_PAGE = '/showReservation';
    const SELECT_FACILITY_PAGE  = '/selectFacility';
    const SELECT_DAY_TIME_PAGE  = '/selectDayTime';

    # Operations
    const SUBMIT           = 'submit';

    # Operation Values
    const CREATE_ACCOUNT   = 'Create Account';
    const LOGIN            = 'Login';
    const SELECT           = 'Select';

    # Post Attribute Names
    const SESSION_ID    = 'sessionId';
    const FACILITY_ID   = 'facilityId';

    # Member variables
    protected $m_urlParams;
    protected $m_controller;

    private $m_pageName;
    private $m_styles;

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
     * @brief: Print out HTML to display this page.  Derived classes must implement
     *         the "render()" method to print out their page content.
     */
    public function displayPage()
    {
        print '
            <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';


        print '
            <html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">';

        $this->m_styles->render();

        print '
            <head>
                <title>Practice Fields</title>
                <script type="text/JavaScript" src="../js/scw.js"></script>
            </head>

            <body bgcolor="#FFFFFF">
                <div id="wrap">
                <div id="leftHeader">
                    <img src="images/aysoLogo.jpeg" alt="AYSO" width="75" height="75">
                </div>
                <div id="rightHeader">
                    <h1><font color="darkblue">AYSO Region 122: </font>Practice Field Reservation System</h1>
                </div>';

        $this->displayHeaderNavigation();
        $this->render();

        print '
                <br>';

// print htmlFormatArray($_REQUEST);
print $this->htmlFormatArray($_POST);
// print htmlFormatArray($this->m_tableData);
// print htmlFormatArray($this->m_tableSummaryData);

        print "
            </body>
        </html>";
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
                    <td align='right'><font color='lightblue'><b>$request</b></font></td>
                    <td align='right'>
                        <input style='text-align:left' type='$type' name='$name' placeholder='$placeHolder'>
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
                <td align='right'><font color='lightblue'><b>$selectorTitle</b></font></td>
                <td align='right'>";

        print "
                    <select name='$selectorName' required>
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
     * @brief Display Header Navigation (Welcome, Show Reservatio, etc.)
     */
    protected function displayHeaderNavigation() {
        /*
        print '
                <ul id="nav">'
            . ($this->m_pageName == self::WELCOME_PAGE || $this->m_pageName == self::LOGIN_PAGE ?
                '<li><div>HOME</div></li>' : '<li><a href="$this->m_pageName">HOME</a></li>')
            . ($this->m_pageName == self::SHOW_RESERVATION_PAGE ?
                '<li><div>RESERVATION</div></li>' : '<li><a href="' . self::SHOW_RESERVATION_PAGE . '">RESERVATION</a></li>')
            . '
               </ul>';
        */
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

    abstract function render();
}