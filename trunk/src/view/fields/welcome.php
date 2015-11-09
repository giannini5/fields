<?php

/**
 * @brief Show the Welcome page and get the user to either create an account or login.
 */
class View_Fields_Welcome extends View_Fields_Base {
    /**
     * @brief Construct he View
     *
     * @param $controller - Controller that contains data used when rendering this view.
     */
    public function __construct($controller) {
        parent::__construct(self::WELCOME_PAGE, $controller);
    }

    /**
     * @brief Render data for display on the page.
     */
    public function render() {
        print "
            <table align='center' valign='top' border='1' cellpadding='5' cellspacing='0' style='max-width:900px;'>
            <tr><td>
            <table align='center' valign='top' border='0' cellpadding='5' cellspacing='0'>";

        print "
                <tr>";

        $this->renderInfo();

        print "
                </tr>";

        print "
            </table>
            </td></tr>
            </table>";
    }

    public function renderInfo() {
        print '
                <h1 align="center">Practice Field Info &amp; Etiquette</h1>

                <p style="text-align: left;">Real-estate, being a precious resource in our beautiful part of the world, leaves us with limited practice field availability. Because of the limited availability, AYSO prioritizes premium field usage for our older divisions (U12, U14, U16-19).</p>
                <br><p style="text-align: left;"><strong>Insurance:</strong></p>
                <p style="text-align: left;">For Insurance purposes, AYSO is required to know the days and times where all teams are practicing.</p>
                <br><p style="text-align: left;"><strong>Permits:</strong></p>
                <p style="text-align: left;">All fields require a permit for field use.  Permits are on file with AYSO.  You will receive a confirmation email when you successfully reserve a field that will let you know the first day you can begin practice and the last day you can hold practice.</p>
                <br><p style="text-align: left;"><strong>Practice Guidelines:</strong></p>
                <table style="text-align: center;" border="0" cellspacing="0" cellpadding="10" width="60%" align="center">
                    <tbody>
                        <tr bgcolor="#CCCCCC">
                            <td style="text-align: center;">Age</td>
                            <td style="text-align: center;">Number of Practices <br />Per Week</td>
                            <td style="text-align: center;">Maximum Time <br />Allowed Per Practice</td>
                        </tr>
                        <tr>
                            <td style="text-align: center;">U6</td>
                            <td style="text-align: center;">1</td>
                            <td style="text-align: center;">1 hour</td>
                        </tr>
                        <tr>
                            <td style="text-align: center;">U7</td>
                            <td style="text-align: center;">1</td>
                            <td>1 hour</td>
                        </tr>
                        <tr>
                            <td>U8</td>
                            <td>1</td>
                            <td>1 hour</td>
                        </tr>
                        <tr>
                            <td>U9</td>
                            <td>2</td>
                            <td>1.5 hours</td>
                        </tr>
                        <tr>
                            <td>U10</td>
                            <td>2</td>
                            <td>1.5 hours</td>
                        </tr>
                        <tr>
                        </tr>
                        <tr>
                            <td>U11</td>
                            <td>2</td>
                            <td>1.5 hours</td>
                        </tr>
                        <tr>
                            <td>U12</td>
                            <td>2</td>
                            <td>1.5 hours</td>
                        </tr>
                        <tr>
                            <td>U14</td>
                            <td>2</td>
                            <td>1.5 hours</td>
                        </tr>
                        <tr>
                            <td>U16/U19</td>
                            <td>2</td>
                            <td>2 hours</td>
                        </tr>
                    </tbody>
                </table>

                <p style="text-align: center;"> </p>
                <p style="text-align: center;"><strong>AYSO National has requested that 1 adult of each gender be in attendance at each practice.</strong></p>
                <p style="text-align: left;"><span style="line-height: 1.3em;">In some cases, more than one team is assigned the same field as the same time. For example, </span><span style="line-height: 1.3em;">full soccer fields are divided in half for practice; school fields are divided into sections to </span><span style="line-height: 1.3em;">accommodate more than one team.</span></p>
                <p style="text-align: left;">Please be courteous to other coaches and teams.</p>
                <br><p style="text-align: left;"><strong>Field Reservations:</strong></p>
                <p style="text-align: left;">Field reservations are handled in the order received.  Click on the RESERVATION tab to start the process (you may need to login or create an account if this is your first time.)</p>
                <p style="text-align: left;"><strong><em>Please do not make direct contact with any of the practice facilities until you’ve received a confirmation email from AYSO. You confirmation email will give you follow-on instructions for facilities that require additional processing.  In many cases AYSO approval is all you need.</em></strong></p>
                <p style="text-align: left;"><strong>Schools that require additional processing:</strong></p>
                <p style="padding-left: 90px; text-align: left;">Adams<br />Peabody<br />Hope<br />Monte Vista<br />Vieja Valley<br />Cold Springs<br />Montecito Union</p>
                <p style="text-align: left;">All of the above requires teams to pay a usage fee for the fields.  This is handled by individual coaches.</p>
                <p style="text-align: left;">Peabody, Hope School District, and Montecito Union only allow teams containing students who attend those schools to practice on their field.</p>
            ';
    }
}