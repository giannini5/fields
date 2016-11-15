<?php
require_once '../../autoload.php';

class ExceptionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException PreException
     */
    public function testPreException()
    {
        // Test No Exception
        precondition(TRUE, "Force a Failure");

        // Test Exception
        $this->setExpectedException('PreException');
        precondition(FALSE, "Force a Failure");
    }

    /**
     * @expectedException AssertException
     */
    public function testAssertException()
    {
        // Test No Exception
        assertion(TRUE, "Force a Failure");

        // Test Exception
        $this->setExpectedException('AssertException');
        assertion(FALSE, "Force a Failure");
    }
}

?>
