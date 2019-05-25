<?php

use \DAG\Domain\Schedule\Coordinator;

/**
 * Class Controller_AdminPractice_Base
 *
 * @brief Encapsulates everything that is common for the Admin controllers.
 *        Derived classes must implement all abstract method.
 */
abstract class Controller_AdminPractice_Base extends Controller_Base
{
    public function __construct()
    {
        parent::__construct(self::SESSION_ADMIN_COOKIE, Coordinator::PRACTICE_FIELD_COORDINATOR_USER_TYPE);
    }

    /**
     * @return string
     */
    public function getHeaderButtonToShow()
    {
        return View_Base::SIGN_OUT;
    }

    /**
     * @return string
     */
    public function getCoachName()
    {
        return isset($this->m_coordinator) ? $this->m_coordinator->name : '';
    }

    /**
     * @return string
     */
    public function getDivisionName()
    {
        return '';
    }

    /**
     * @return string
     */
    public function getGender()
    {
        return '';
    }
}