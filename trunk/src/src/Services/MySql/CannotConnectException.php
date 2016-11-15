<?php

namespace DAG\Services\MySql;

/**
 * This represents a failure to connect to the database.
 * This can be due to bad credentials, database server down, etc.
 */
class CannotConnectException extends \DAGException
{
}
