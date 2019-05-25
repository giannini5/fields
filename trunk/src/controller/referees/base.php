<?php

use \DAG\Domain\Schedule\Coordinator;
use \DAG\Domain\Schedule\Referee;

/**
 * Class Controller_Referees_Base
 *
 * @brief Encapsulates everything that is common for the various controllers.
 *        Derived classes must implement all abstract method.
 */
abstract class Controller_Referees_Base extends Controller_Base
{
    /** @var Referee */
    public $m_referee;

    /** @var  string */
    public $m_loginErrorMessage;

    public function __construct()
    {
        $this->_reset();
        parent::__construct(self::REFEREE_COOKIE, Coordinator::REFEREE_USER_TYPE);

        if (isset($_SERVER['REQUEST_METHOD']) and $_SERVER['REQUEST_METHOD'] == 'POST') {
            if ($this->m_operation == View_Base::SIGN_IN) {
                // TODO: get email, password, lookup referee, verify password if required
            }
        }
    }
}