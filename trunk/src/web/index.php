<?php
ini_set('display_errors', '1');
error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);

require_once dirname(dirname(__FILE__)) . '/lib/autoload.php';


class ControllerException extends DAG_Exception
{
    /**
     * ControllerException constructor.
     *
     * @param string        $message
     * @param \Exception    $exception
     */
    public function __construct($message, $exception) {
        parent::__construct($message, 0, $exception);
    }
}


/**
 * Class Web_Index
 *
 * @brief Controller created to process the request based on the page being requested.
 */
class Web_Index
{
    private $m_controller;
    private $m_requestPage;

    public function __construct()
    {
        $this->m_requestPage = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : View_Base::WELCOME_PAGE;

        // Strip off the URI Params if any
        $position = strpos($this->m_requestPage, "?");
        if (!is_bool($position)) {
            $data = str_split($this->m_requestPage, $position);
            $this->m_requestPage = $data[0];
        }

        switch($this->m_requestPage) {
            case View_Base::TEST_POST_PAGE:
                $this->m_controller = new Controller_Fields_TestPost();
                break;

            case View_Base::WELCOME_PAGE:
                $this->m_controller = new Controller_Fields_Welcome();
                break;

            case View_Base::CREATE_ACCOUNT_PAGE:
                $this->m_controller = new Controller_Fields_CreateAccount();
                break;

            case View_Base::LOGIN_PAGE:
                $this->m_controller = new Controller_Fields_Login();
                break;

            case View_Base::SHOW_RESERVATION_PAGE:
                $this->m_controller = new Controller_Fields_ShowReservation();
                break;

            case View_Base::SELECT_FIELD_PAGE:
                $this->m_controller = new Controller_Fields_SelectFacility();
                break;

            case View_Base::IMAGE_PAGE:
                $this->m_controller = new Controller_Fields_Image();
                break;

            case View_Base::HELP_PAGE:
                $this->m_controller = new Controller_Fields_Help();
                break;

            case View_Base::ADMIN_HOME_PAGE:
                $this->m_controller = new Controller_AdminPractice_Home();
                break;

            case View_Base::ADMIN_SEASON_PAGE:
                $this->m_controller = new Controller_AdminPractice_Season();
                break;

            case View_Base::ADMIN_DIVISION_PAGE:
                $this->m_controller = new Controller_AdminPractice_Division();
                break;

            case View_Base::ADMIN_FACILITY_PAGE:
                $this->m_controller = new Controller_AdminPractice_Facility();
                break;

            case View_Base::ADMIN_FIELD_PAGE:
                $this->m_controller = new Controller_AdminPractice_Field();
                break;

            case View_Base::ADMIN_LOCATION_PAGE:
                $this->m_controller = new Controller_AdminPractice_Location();
                break;

            case View_Base::ADMIN_TRANSACTION_PAGE:
                $this->m_controller = new Controller_AdminPractice_Transaction();
                break;

            case View_Base::ADMIN_RESERVATIONS_PAGE:
                $this->m_controller = new Controller_AdminPractice_Reservations();
                break;

            case View_Base::ADMIN_SELECT_FIELD_PAGE:
                $this->m_controller = new Controller_AdminPractice_Select();
                break;

            case View_Base::SCHEDULE_UPLOAD_PAGE:
                $this->m_controller = new Controller_AdminSchedules_Upload();
                break;

            case View_Base::SCHEDULE_SEASON_PAGE:
                $this->m_controller = new Controller_AdminSchedules_Season();
                break;

            case View_Base::SCHEDULE_GAME_DATE_PAGE:
                $this->m_controller = new Controller_AdminSchedules_GameDate();
                break;

            case View_Base::SCHEDULE_FACILITIES_PAGE:
                $this->m_controller = new Controller_AdminSchedules_Facility();
                break;

            case View_Base::SCHEDULE_FIELDS_PAGE:
                $this->m_controller = new Controller_AdminSchedules_Field();
                break;

            case View_Base::SCHEDULE_TEAMS_PAGE:
                $this->m_controller = new Controller_AdminSchedules_Team();
                break;

            case View_Base::SCHEDULE_DIVISIONS_PAGE:
                $this->m_controller = new Controller_AdminSchedules_Division();
                break;

            case View_Base::SCHEDULE_FAMILY_PAGE:
                $this->m_controller = new Controller_AdminSchedules_Family();
                break;

            case View_Base::SCHEDULE_SCHEDULES_PAGE:
                $this->m_controller = new Controller_AdminSchedules_Schedule();
                break;

            case View_Base::SCHEDULE_PREVIEW_PAGE:
                $this->m_controller = new Controller_AdminSchedules_Preview();
                break;

            case View_Base::REFEREE_HOME_PAGE:
                $this->m_controller = new Controller_AdminReferee_Home();
                break;

            case View_Base::SCORING_ENTER_SCORES_PAGE:
                $this->m_controller = new Controller_AdminScoring_Home();
                break;

            case View_Base::SCORING_VOLUNTEER_POINTS_PAGE:
                $this->m_controller = new Controller_AdminScoring_VolunteerPoints();
                break;

            case View_Base::SCORING_GAME_CARDS_PAGE:
                $this->m_controller = new Controller_AdminScoring_GameCards();
                break;

            case View_Base::SCORING_SCORE_SHEET_PAGE:
                $this->m_controller = new Controller_AdminScoring_ScoreSheet();
                break;

            case View_Base::SCORING_PLAYER_STATS_PAGE:
                $this->m_controller = new Controller_AdminScoring_PlayerStats();
                break;

            case View_Base::GAMES_HOME_PAGE:
                $this->m_controller = new Controller_Games_Home();
                break;

            case View_Base::GAMES_FLIGHTS_PAGE:
                $this->m_controller = new Controller_Games_Flights();
                break;

            case View_Base::GAMES_SCHEDULE_PAGE:
                $this->m_controller = new Controller_Games_Schedule();
                break;

            case View_Base::GAMES_STANDINGS_PAGE:
                $this->m_controller = new Controller_Games_Standings();
                break;

            case View_Base::GAMES_TEAM_STATS_PAGE:
                $this->m_controller = new Controller_Games_TeamStats();
                break;

            case View_Base::GAMES_GAME_CARDS_PAGE:
                $this->m_controller = new Controller_Games_GameCards();
                break;

            case View_Base::API_SWAP:
                $this->m_controller = new Controller_Api_Swap();
                break;

            case View_Base::API_TOGGLE:
                $this->m_controller = new Controller_Api_Toggle();
                break;

            case View_Base::API_TOGGLE_GAME_TIME:
                $this->m_controller = new Controller_Api_ToggleGameTime();
                break;

            default:
                $this->m_controller = new Controller_Fields_Welcome();
                break;
        }
    }

    public function htmlFormatArray($arr) {
        $retStr = "";
        if (is_array($arr)) {
            foreach ($arr as $key=>$val) {
                if (is_array($val)){
                    $retStr .= $key . ' => ' . $this->htmlFormatArray($val) . "\n";
                }
                else {
                    $retStr .= $key . ' => ' . $val . "\n";
                }
            }
        }
        return $retStr;
    }

    public function process() {
        try {
            $this->m_controller->process();
        } catch (Exception $e) {
            $postString = $this->htmlFormatArray($_POST);
            $message = $e->getMessage() . " Post data: $postString\n";
            throw new ControllerException($message, $e);
        }
    }
}

$webIndex = new Web_Index();
$webIndex->process();
