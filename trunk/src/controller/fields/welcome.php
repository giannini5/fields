<?php

/**
 * Class Controller_Welcome
 *
 * @brief Get user to create an account or login to an existing account.
 */
#[AllowDynamicProperties]
class Controller_Fields_Welcome extends Controller_Fields_Base {
    const DIVISION_NAME = 'divisionName';

    public $m_divisionName = '';

    public function __construct() {
        parent::__construct();

        if (isset($_SERVER['REQUEST_METHOD']) and $_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->m_divisionName = $this->getPostAttribute(
                Controller_Fields_Welcome::DIVISION_NAME,
                '', FALSE
            );
            $this->m_gender = $this->getPostAttribute(
                Model_Fields_TeamDB::DB_COLUMN_GENDER,
                '', FALSE
            );
            $this->m_email = $this->getPostAttribute(
                Model_Fields_CoachDB::DB_COLUMN_EMAIL,
                '', FALSE
            );
            $this->m_name = $this->getPostAttribute(
                Model_Fields_CoachDB::DB_COLUMN_NAME,
                '', FALSE
            );
            $this->m_phone = $this->getPostAttribute(
                Model_Fields_CoachDB::DB_COLUMN_PHONE,
                '', FALSE
            );
        }
    }

    /**
     * @brief On GET, render the page to ask user to Create account or Login.
     *        On POST, complete login or create account
     */
    public function process() {
        switch ($this->m_operation) {
            case View_Base::CREATE_ACCOUNT:
                $view = new View_Fields_CreateAccount($this, View_Base::WELCOME_PAGE);
                $view->displayPage();
                break;

            case View_Base::SIGN_IN:
                $view = new View_Fields_Login($this, View_Base::WELCOME_PAGE);
                $view->displayPage();
                break;

            case View_Base::SIGN_OUT:
                $this->signOut();
                $view = new View_Fields_Login($this);
                $view->displayPage();
                break;

            default:
                $this->attemptLogin();
                $view = new View_Fields_Welcome($this);
                $view->displayPage();
                break;
        }
    }

    /**
     * Attempt to authenticate user based on passed in post attributes (sign-out current user if any)
     */
    public function attemptLogin() {
        if (!empty($this->m_divisionName)) {
            if ($this->m_isAuthenticated) {
                $this->signOut();
            }

            $this->m_division = Model_Fields_Division::LookupByName($this->m_league, $this->m_divisionName, FALSE);
            if (isset($this->m_division)) {
                if (!empty($this->m_gender) and !empty($this->m_email) and !empty($this->m_name) and !empty($this->m_phone)) {
                    $this->m_coach = $this->getOrCreateCoach();
                    $this->m_team = $this->getOrCreateTeam();

                    $this->createSession($this->m_coach->id, Model_Fields_Session::COACH_USER_TYPE, $this->m_team->id);
                }
            }
        }
    }

    /**
     * @return Model_Fields_Coach
     */
    public function getOrCreateCoach() {
        $coach = Model_Fields_Coach::LookupByEmail($this->m_season, $this->m_division, $this->m_email, FALSE);
        if (!isset($coach)) {
            $coach = Model_Fields_Coach::Create($this->m_season, $this->m_division, $this->m_name, $this->m_email, $this->m_phone, '1234');
        }

        return $coach;
    }

    /**
     * @return Model_Fields_Team
     */
    public function getOrCreateTeam() {
        $team = Model_Fields_Team::LookupByCoach($this->m_coach, $this->m_gender);
        if (!isset($team)) {
            $team = Model_Fields_Team::Create($this->m_division, $this->m_coach, $this->m_gender, '');
        }

        return $team;
    }
}