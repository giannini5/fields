<?php

/**
 * @brief Show the Field page and get the user to select a season to administer or create a new season.
 */
class View_Schedules_Field extends View_Schedules_Base {
    /**
     * @brief Construct the View
     *
     * @param $controller - Controller that contains data used when rendering this view.
     */
    public function __construct($controller) {
        parent::__construct(self::SCHEDULE_FIELDS_PAGE, $controller);
    }

    /**
     * @brief Render data for display on the page.
     */
    public function render()
    {
        $maxColumns = 4;

        print "
            <table bgcolor='lightyellow' valign='top' align='center' width='400' border='1' cellpadding='5' cellspacing='0'>
                <tr>
                    <td>TODO
                    </td>
                </tr>
            </table>
            <br><br>";
    }

    /**
     * @brief Print the form to create a field.  Form includes the following
     *        - Field Name
     *        - Enabled radio button
     *
     * @param $maxColumns - Number of columns the form is covering
     */
    private function _printCreateFieldForm($maxColumns) {
        // TODO
    }

    /**
     * @brief Print the form to update a season.  Form includes the following
     *        - Field Name
     *        - Enabled radio button
     *
     * @param $maxColumns - Number of columns the form is covering
     * @param $season - Field to be edited
     */
    private function _printUpdateFieldForm($maxColumns, $season) {
        // TODO
    }
}