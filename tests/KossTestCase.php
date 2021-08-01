<?php

use Aberdeener\Koss\Koss;
use PHPUnit\Framework\TestCase;

class KossTestCase extends TestCase
{
    protected Koss $koss;

    public function setUp(): void
    {
        parent::setUp();

        $this->koss = new Koss('localhost', 3306, 'koss', 'root', '');
    }
}
