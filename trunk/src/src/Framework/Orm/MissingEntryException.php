<?php

namespace DAG\Framework\Orm;

/**
 * Represents an error from trying to update or delete a model object that does not exist.
 * Example: in MySQL an UPDATE will be a no-op if the object does not exist; that scenario is captured here.
 */
class MissingEntryException extends \DAGException
{
}
