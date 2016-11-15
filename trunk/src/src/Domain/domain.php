<?php

namespace DAG\Domain;
use DAG\Framework\Exception\Precondition;

/**
 * Domain model base class
 */
class Domain
{
    public function __set($name, $value)
    {
        Precondition::isTrue(false, "Set not allowed for variable $name");
    }
}
