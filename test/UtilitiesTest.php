<?php

namespace Jogger;

use PHPUnit\Framework\TestCase;

class UtilitiesTest extends TestCase
{

    public function testValidateLoglevel() {
        $invalidLevels = ["you", "shall", "not", "emmit", "any", "fatal", "case"];
        foreach ($invalidLevels as $invalidLevel) {
            $this->expectException("InvalidArgumentException");
            $this->expectExceptionMessage("Invalid log level was provided.");
            Utilities::validateLoglevel($invalidLevel);
        }
    }
}
