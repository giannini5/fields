<?php

/**
 * @brief Show the Admin Home page and get the user to login.
 */
class View_AdminSchedules_Home extends View_AdminSchedules_Base {
    /**
     * @brief Construct he View
     *
     * @param Controller_Base $controller - Controller that contains data used when rendering this view.
     */
    public function __construct($controller) {
        parent::__construct(self::SCHEDULE_UPLOAD_PAGE, $controller);
    }

    /**
     * @brief Render data for display on the page.
     */
    public function render()
    {
        if ($this->m_controller->m_isAuthenticated) {
            $this->renderHome();
        } else {
            $this->renderLogin();
        }
    }

    /**
     * @brief Render instructions for how to administer practice fields
     */
    public function renderHome() {
        $messageString  = $this->m_controller->m_messageString;

        if (!empty($messageString)) {
            print "
                <p style='color: green' align='center'><strong>$messageString</strong></p><br>";
        }

        print "
            <p align='left' style='font-size: 15px; text-indent: 25px'>Welcome ... let's create schedules - follow the instructions below</p>
            <ol>
                <li>Click on <strong style='color: blue'>SEASON</strong> tab and create a season.</li>
                <li>Upload Coaches file to create divisions, teams, coaches, assistant coaches and families.</li>
                <li>OPTIONAL: Upload Players file to assign players to teams.</li>
                <li>Go to the <strong  style='color: blue'>GAME DATE</strong> tab and delete Game Dates that are not in the calendar</li>
                <li>Upload a Facility file using the Upload box below or go to the <strong style='color: blue'>FACILITY</strong> tab to manually create.</li>
                <li>Upload a Field file using the Upload box below or go to the <strong  style='color: blue'>FIELD</strong> tab to manually create.</li>
                <li>Go to the <strong  style='color: blue'>FIELD</strong> tab and delete Game Dates that are not in the calendar</li>
                <li>Go to the <strong  style='color: blue'>SCHEDULE</strong> tab and start creating schedules for each division</li>
                <li>Using the <strong  style='color: blue'>SCHEDULE</strong> tab apply schedule customizations (fix coach overlaps, apply special requests, etc.)</li>
                <li>Go to the <strong  style='color: blue'>PREVIEW</strong> tab, copy link and send to board members for schedule approval</li>
                <li>Once schedule approved, go to the <strong  style='color: blue'>SCHEDULE</strong> tab and publish each of the schedules</li>
                <li>Verify schedules correctly published</li>
            </ol>";

        print "<br>--------------- inLeague Imports ---------------<br>";
        $this->renderLoadInLeagueCoachesFromFile();
        print "<br>";
        $this->renderLoadInLeagueFieldsFromFile();

        print "<br>--------------- WebYouthSoccer Imports ---------------<br>";
        $this->renderLoadCoachesFromFile();
        print "<br>";
        $this->renderLoadPlayersFromFile();
        print "<br>";
        $this->renderLoadFacilitiesFromFile();
        print "<br>";
        $this->renderLoadFieldsFromFile();
    }

    /**
     * @brief Render HTML to load teams, coaches and assistant coaches from a file
     */
    public function renderLoadInLeagueCoachesFromFile()
    {
        $sessionId = $this->m_controller->getSessionId();

        print "
            <table bgcolor='lightyellow' valign='top' style='margin-left:25px' width='700' border='1' cellpadding='5' cellspacing='0'>
                <tr>
                    <td colspan='3' nowrap><strong style='color: blue; font-size: 18px'>Import Divisions, Teams, Coaches and Assistant Coaches</strong><br><strong style='font-size: 16px'>Sample CSV file format</strong>
                    <br><p style='font-size: 12px'>
                        Team,First Name,Last Name,Title,Vol Comment,Street,City,State,Zip,Email,Home Phone,Cell Phone,Risk Status,Risk Status Expiration,Safe Haven,Concussion,SafeSport,Certifications<br>
                        'B10-01','Paul','Singer','Head Coach','I want to be head coach. Last year I was Ryan Sparre's assistant coach.','5679 Stinson Way','Goleta','CA','93117',<br>
                        'pauldsinger@gmail.com','','510-757-3932','Green','12/6/24','Yes','Yes','No',<br>
                        'Concussion Awareness, Fingerprinting Certificate, Sudden Cardiac Arrest, AYSOs Safe Haven, Intermediate (14U) Coach and 12U Coach'
                        </p>
                    </td>
                </tr>
                <tr>
                    <form enctype='multipart/form-data' method='POST' action='" . self::SCHEDULE_UPLOAD_PAGE . $this->m_urlParams . "'>
                        <td nowrap>Select csv file to upload:</td>
                        <td>
                            <input type='file' name='fileToUpload' id='fileToUpload'>
                        </td>
                        <td>
                            <input style='background-color: yellow' type='" . View_Base::SUBMIT . "' value='" . View_Base::UPLOAD_INLEAGUE_FILE . "' name='" . View_Base::SUBMIT . "'>
                            <input type='hidden' id='sessionId' name='sessionId' value='$sessionId'>
                        </td>
                    </form>
                </tr>
            </table>
            ";
    }

    /**
     * @brief Render HTML to load fields
     */
    public function renderLoadInLeagueFieldsFromFile()
    {
        $sessionId = $this->m_controller->getSessionId();

        print "
            <table bgcolor='lightyellow' valign='top' style='margin-left:25px' width='700' border='1' cellpadding='5' cellspacing='0'>
                <tr>
                    <td colspan='3' nowrap ><strong style='color: blue; font-size: 18px'>Fields</strong><br><strong style='font-size: 16px'>Sample CSV file format</strong><br>
                    <p style='font-size: 12px'>
                        Field,Active,Favored Divisions,Competitions, Street, City, Zip<br>
                        'Girsh Park, Field 01, 7U (Girsh01_7U)','Yes','B7,G7','Fall League','Girsh Park 7050 Phelps Rd','Goleta','93117'<br>
                        'Girsh Park, Field 02, 6U (Girsh02_6U)','Yes','B6,G6','Fall League','Girsh Park 7050 Phelps Rd','Goleta','93117'
                    </p>
                    </td>
                </tr>
                <tr>
                    <form enctype='multipart/form-data' method='POST' action='" . self::SCHEDULE_UPLOAD_PAGE . $this->m_urlParams . "'>
                        <td nowrap>Select csv file to upload:</td>
                        <td>
                            <input type='file' name='fileToUpload' id='fileToUpload'>
                        </td>
                        <td>
                            <input style='background-color: yellow' type='" . View_Base::SUBMIT . "' value='" . View_Base::UPLOAD_INLEAGUE_FIELD_FILE . "' name='" . View_Base::SUBMIT . "'>
                            <input type='hidden' id='sessionId' name='sessionId' value='$sessionId'>
                        </td>
                    </form>
                </tr>
            </table>
            ";
    }

    /**
     * @brief Render HTML to load facility
     */
    public function renderLoadFacilitiesFromFile()
    {
        $sessionId = $this->m_controller->getSessionId();

        print "
            <table bgcolor='lightyellow' valign='top' style='margin-left:25px' width='700' border='1' cellpadding='5' cellspacing='0'>
                <tr>
                    <td colspan='3' nowrap ><strong style='color: blue; font-size: 25px'>Facilities</strong><br><strong style='font-size: 16px'>Sample CSV file format</strong><br>
                    <p style='font-size: 12px'>
                        FacilityName,Address1,Address2,City,State,ZipCode,ContactName,ContactEmail,ContactPhone,Enabled<br>
                        Girsh Park,7050 Phelps Rd,,Goleta,CA,93117,Ryan Harrington,rharrington@girshpark.org,(805) 968-2773 x3,1<br>
                        UCSB Rec Center,516 Ocean Road,,Santa Barbara,CA,93106,Celia Elliott,Celia.Elliott@recreation.ucsb.edu,,1
                    </p>
                    </td>
                </tr>
                <tr>
                    <form enctype='multipart/form-data' method='POST' action='" . self::SCHEDULE_UPLOAD_PAGE . $this->m_urlParams . "'>
                        <td nowrap>Select csv file to upload:</td>
                        <td>
                            <input type='file' name='fileToUpload' id='fileToUpload'>
                        </td>
                        <td>
                            <input style='background-color: yellow' type='" . View_Base::SUBMIT . "' value='" . View_Base::UPLOAD_FACILITY_FILE . "' name='" . View_Base::SUBMIT . "'>
                            <input type='hidden' id='sessionId' name='sessionId' value='$sessionId'>
                        </td>
                    </form>
                </tr>
            </table>
            ";
    }

    /**
     * @brief Render HTML to load fields
     */
    public function renderLoadFieldsFromFile()
    {
        $sessionId = $this->m_controller->getSessionId();

        print "
            <table bgcolor='lightyellow' valign='top' style='margin-left:25px' width='700' border='1' cellpadding='5' cellspacing='0'>
                <tr>
                    <td colspan='3' nowrap ><strong style='color: blue; font-size: 18px'>Fields</strong><br><strong style='font-size: 16px'>Sample CSV file format</strong><br>
                    <p style='font-size: 12px'>
                        FacilityName,FieldName,Enabled,DivisionsList<br>
                        Girsh Park,Field A,1,U5;U6<br>
                        UCSB Rec Center,Field 1,1,U14;U16/19
                    </p>
                    </td>
                </tr>
                <tr>
                    <form enctype='multipart/form-data' method='POST' action='" . self::SCHEDULE_UPLOAD_PAGE . $this->m_urlParams . "'>
                        <td nowrap>Select csv file to upload:</td>
                        <td>
                            <input type='file' name='fileToUpload' id='fileToUpload'>
                        </td>
                        <td>
                            <input style='background-color: yellow' type='" . View_Base::SUBMIT . "' value='" . View_Base::UPLOAD_FIELD_FILE . "' name='" . View_Base::SUBMIT . "'>
                            <input type='hidden' id='sessionId' name='sessionId' value='$sessionId'>
                        </td>
                    </form>
                </tr>
            </table>
            ";
    }

    /**
     * @brief Render HTML to load teams, coaches and assistant coaches from a file
     */
    public function renderLoadCoachesFromFile()
    {
        $sessionId = $this->m_controller->getSessionId();

        print "
            <table bgcolor='lightyellow' valign='top' style='margin-left:25px' width='700' border='1' cellpadding='5' cellspacing='0'>
                <tr>
                    <td colspan='3' nowrap><strong style='color: blue; font-size: 18px'>Import Divisions, Teams, Coaches and Assistant Coaches</strong><br><strong style='font-size: 16px'>Sample CSV file format</strong>
                    <br><p style='font-size: 12px'>
                        TeamName,TeamId,Region,City,Division,Gender,CoachType,CoachName,CoachPhone,CoachCell,CoachEmail<br>
                        apples,U12G-02,122,Santa Barbara,U12,G,Coach,Walid Afifi,805-679-1812,805-679-1810,w-afifi@comm.ucsb.edu<br>
                        oranges,U12B-05,122,Santa Barbara,U12,B,Coach,David Aguilar,805-284-2045,805-259-9680,davidoaguilar@gmail.com<br>
                        bananas,U6B-29,122,Santa Barbara,U6,B,Coach,Gerardo Aldana,805-637-0256,,soccercoachga@gmail.com
                        </p>
                    </td>
                </tr>
                <tr>
                    <form enctype='multipart/form-data' method='POST' action='" . self::SCHEDULE_UPLOAD_PAGE . $this->m_urlParams . "'>
                        <td nowrap>Select csv file to upload:</td>
                        <td>
                            <input type='file' name='fileToUpload' id='fileToUpload'>
                        </td>
                        <td>
                            <input style='background-color: yellow' type='" . View_Base::SUBMIT . "' value='" . View_Base::UPLOAD_FILE . "' name='" . View_Base::SUBMIT . "'>
                            <input type='hidden' id='sessionId' name='sessionId' value='$sessionId'>
                        </td>
                    </form>
                </tr>
            </table>
            ";
    }

    /**
     * @brief Render HTML to load players from a file
     */
    public function renderLoadPlayersFromFile()
    {
        $sessionId = $this->m_controller->getSessionId();

        print "
            <table bgcolor='lightyellow' valign='top' style='margin-left:25px' width='700' border='1' cellpadding='5' cellspacing='0'>
                <tr>
                    <td colspan='3' nowrap><strong style='color: blue; font-size: 18px'>Import Players</strong><br><strong style='font-size: 16px'>Sample CSV file format</strong>
                        <br><p style='font-size: 12px'>
                        Region,Div,Team,Status,ID,Name,Phone,RegDate,PreReg,Fee<br>
                        122,U8B,2,Registered,157472,Abbott; Cash,213-400-1566,2016-05-20,2016-05-20,155<br>
                        122,U9B,AVAIL,Pre-Reg,158885,Abbott; Owen,618-303-2208,,2016-08-16,<br>
                        122,U6B,AVAIL,Pre-Reg,158883,Abbott; Tadhg,618-303-2208,,2016-08-16,<br>
                        122,U14B,7,Registered,156874,Abdullah; Muhammad,805-845-7351,2016-05-04,2016-04-25,70<br>
                        </p>
                    </td>
                </tr>
                <tr>
                    <form enctype='multipart/form-data' method='POST' action='" . self::SCHEDULE_UPLOAD_PAGE . $this->m_urlParams . "'>
                        <td nowrap>Select player csv file to upload:</td>
                        <td>
                            <input type='file' name='fileToUpload' id='fileToUpload'>
                        </td>
                        <td>
                            <input style='background-color: yellow' type='" . View_Base::SUBMIT . "' value='" . View_Base::UPLOAD_PLAYER_FILE . "' name='" . View_Base::SUBMIT . "'>
                            <input type='hidden' id='sessionId' name='sessionId' value='$sessionId'>
                        </td>
                    </form>
                </tr>
            </table>
            ";
    }

    /**
     * @brief Render sign-in screen
     */
    public function renderLogin() {
        print "
            <table align='center' valign='top' border='1' cellpadding='5' cellspacing='0'>
            <tr><td>
            <table align='center' valign='top' border='0' cellpadding='5' cellspacing='0'>";

        // Login To an Existing Account Form
        print "
            <form method='post' action='" . self::SCHEDULE_UPLOAD_PAGE . $this->m_urlParams . "'>";

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
}