<?php

/**
 * @brief Select the Facility for the reservation.
 *
 * @param $controller - Controller that contains data used when rendering this view.
 */
class View_SelectFacility extends View_Base {
    /**
     * @brief Construct the Select Facility View
     *
     * @param $controller - Controller that contains data used when rendering this view.
     */
    public function __construct($controller) {
        parent::__construct(self::SELECT_FACILITY_PAGE, $controller);
    }

    /**
     * @brief Render data for display on the page.
     */
    public function render() {
        $facilities = $this->m_controller->getFacilities();
        $sessionId = $this->m_controller->getSessionId();

        if (count($facilities) == 0) {
            print "<p>Uh, sorry, no facilities found - better call Dave!";
        }

        print "
            <table valign='top' border='0' cellpadding='5' cellspacing='0'>";

        foreach ($facilities as $facility) {
            print "
                <form method='post' action='" . self::SELECT_FACILITY_PAGE . $this->m_urlParams . "'>";

            print "
                <tr>
                    <td colspan='2'>
                        <b>$facility->name</b><br>
                        $facility->address1 $facility->address2<br>
                        $facility->city, $facility->state, $facility->postalCode<br><br>
                    </td>
                </tr>";

            $fields = $this->m_controller->getFields($facility);
            foreach ($fields as $field) {
                print "
                <tr>
                    <td align='right'>
                        <input style='background-color: yellow' name='" . View_Base::SUBMIT . "' type='submit' value='" . View_Base::SELECT . "'>
                        <input type='hidden' id='facilityId' name='facilityId' value='$facility->id'>
                        <input type='hidden' id='fieldId' name='fieldId' value='$field->id'>
                        <input type='hidden' id='sessionId' name='sessionId' value='$sessionId'>
                    </td>
                    <td>
                        <b>$field->name</b>
                    </td>
                </tr>";
            }

            print "
                </tr>";

            print "
                </form>";
        }

        print "
            </table>";
    }


    /**
     * @brief Render data for display on the page.
     */
    public function renderOld() {
        print "Okay, pick a faclity!!! </br>";

        print "
            <table>
            <form method='post' action='" . self::WELCOME_PAGE . $this->m_urlParams . "'>
                <tr><td><a href='selectDayTime'>Select Day and Time</a></td></tr>
                <tr><td><a href='welcome'>Home</a></br></td></tr>
            </form>
            </table>";

    }
}