<?php

namespace DAG\Framework\Orm;

/**
 * Indicates that the ORM loaded multiple objects from DB but only one was expected.
 */
class MultipleResultsException extends \DAGException
{
}
