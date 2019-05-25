<?php

/**
 * @brief: Abstract base class for all referee views.
 */
abstract class View_Referees_Base extends View_Base {

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
        $navigation     = isset($navigation) ? $navigation : new View_Referees_Navigation($controller, $page);

        parent::__construct($navigation, $page, "Referees", $controller);
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

    abstract public function renderPage();
}