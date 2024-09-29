<?php

/**
 * @brief: Abstract navigation class for all views.
 *          - Abstract methods must be implemented by child classes
 */
abstract class View_Navigation
{
    /** @var Controller_Base */
    protected $controller;

    /** @var string */
    protected $pageName;

    /** @var string */
    protected $redipsPath;

    /** @var string */
    protected $redipsScripPath;

    /**
     * @param Controller_Base   $controller
     * @param string            $pageName
     * @param bool              $useRefereeRedips - defaults to false
     */
    public function __construct($controller, $pageName, $useRefereeRedips = false)
    {
        $this->controller       = $controller;
        $this->pageName         = $pageName;
        $this->redipsPath       = $useRefereeRedips ? '../js/schedule_redips/style_ref.css' : '../js/schedule_redips/style.css';
        $this->redipsScripPath  = $useRefereeRedips ? '../js/schedule_redips/script_ref.js' : '../js/schedule_redips/script.js';
    }

    /**
     * @brief Display HTML start and header
     *
     * @param View_Styles   $styles             - CSS
     * @param string        $title              - page title
     * @param int           $collapsibleCounter - defaults to 0
     */
    public function displayHeader($styles, $title, $collapsibleCounter = 0)
    {
        print "
            <head>
                <title>$title</title>
                <script type='text/JavaScript' src='../js/scw.js'></script>

                <link rel='stylesheet' href='$this->redipsPath' type='text/css' media='screen'/>
		        <script type='text/javascript'>
			        var redipsURL = '/javascript/drag-and-drop-example-3/';
		        </script>
		        <script type='text/javascript' src='../js/REDIPS_drag/redips-drag-min.js'></script>
		        <script type='text/javascript' src='$this->redipsScripPath'></script>
                <script type='text/javascript' src='../js/no_scroll_number.js'></script>";

        $styles->render($collapsibleCounter);

        print "
            </head>";
    }

    /**
     * @brief Display Body Header (section above navigation)
     *
     * @param string            $urlParams
     */
    abstract public function displayBodyHeader($urlParams);

    /**
     * @brief Display Header Navigation
     */
    abstract public function displayNavigation();
}
