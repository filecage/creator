<?php

// We want to test Union Type behaviour without breaking tests in PHP <= 8.0
// So we outsource this to a file that is only included in our tests when the PHP version is >= 8.0

use Creator\Tests\Mocks\ExtendedClass;
use Creator\Tests\Mocks\SimpleClass;

$this->creator->invoke(fn(SimpleClass|ExtendedClass $instance) => $this->fail('Should never be called'));