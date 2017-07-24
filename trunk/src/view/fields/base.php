<?php

/**
 * @brief: Abstract base class for all field request views.
 */
abstract class View_Fields_Base extends View_Base {

    const REQUIRE_PASSWORD = FALSE;

    /**
     * @brief: Construct a new instance of this base class.
     *
     * @param string                $page       - Name of the page being constructed.  Must be defined as a const
     *                                              in the above list.
     * @param Controller_Base       $controller - Controller that contains data used when rendering this view.
     * @param null|View_Navigation  $navigation - Override navigation
     */
    public function __construct($page, $controller, $navigation = null)
    {
        $navigation     = isset($navigation) ? $navigation : new View_Fields_Navigation($controller, $page);
        $facilityCount  = count($controller->getFacilities());

        parent::__construct($navigation, $page, "Practice Fields", $controller, $facilityCount);
    }

    /**
     * @brief: Render the page body
     */
    public function render()
    {
        if (isset($this->m_controller->m_season)) {
            $this->renderPage();
        } else {
            print "
            <table align='center' valign='top' border='1' cellpadding='5' cellspacing='0'>
                <tr>
                    <td>Sorry, we are in the off season right now.  Come back later.</td>
                </tr>
            </table>";
        }
    }

    /**
     * @brief Render data for display how to authenticate instruction on the page.
     */
    public function renderAuthenticateView() {
        print "
            <table align='center' valign='top' border='1' cellpadding='5' cellspacing='0' style='max-width:900px;'>
            <tr><td>
            <table align='center' valign='top' border='0' cellpadding='5' cellspacing='0'>";

        print "
                <tr>";

        $this->renderAuthenticateInfo();

        print "
                </tr>";

        print "
            </table>
            </td></tr>
            </table>";
    }

    public function renderAuthenticateInfo() {
        print "
                <h1 align='center'>Ah, first time User - Welcome!</h1>

                <p style='text-align: left;'>Here's how to access this site so that I know your team information:</p>
                <ol>
                    <li>Go to <a href='https://r122.webyouthsoccer.com/login.php'>Region 122 WebYouthSoccer</a></li>
                    <li>Login</li>
                    <li>Click on <strong>Coach</strong><font color='red'>*</font></li>
                    <li>Select your team<font color='red'>**</font></li>
                    <li>Click on Practice Field link to get back to this site to select a practice space</li>
                </ol>
                <p style='text-align: left;'>Go to the HELP tab if you have questions.</p>
                <p style='text-align: left;'><font color='red'>*</font> You must be a coach to select a practice field<br><font color='red'>**</font> Team selection only required if you coach multiple teams</p>";
    }

    abstract public function renderPage();
}