<?php

namespace DAG\Framework\Orm;

/**
 * Represents an error from trying to create an already-existing model object.
 * This is similar to MySQL's "duplicate key error"
 */
class DuplicateEntryException extends \DAGException
{
}
