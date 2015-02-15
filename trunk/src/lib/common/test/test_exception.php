<?php
require_once '../../autoLoader.php';

class ExceptionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException PreconditionException
     */
    public function testPreconditionException()
    {
        // Test No Exception
        precondition(TRUE, "Force a Failure");

        // Test Exception
        $this->setExpectedException('PreconditionException');
        precondition(FALSE, "Force a Failure");
    }

    /**
     * @expectedException AssertionException
     */
    public function testAssertionException()
    {
        // Test No Exception
        assertion(TRUE, "Force a Failure");

        // Test Exception
        $this->setExpectedException('AssertionException');
        assertion(FALSE, "Force a Failure");
    }
}

?>
