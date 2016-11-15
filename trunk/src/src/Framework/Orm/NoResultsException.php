<?php

namespace DAG\Framework\Orm;

/**
 * Indicates that the ORM cannot load an object from DB because it does not exist.
 */
class NoResultsException extends \DAGException
{
}
