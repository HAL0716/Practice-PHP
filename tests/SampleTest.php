<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversNothing;

#[CoversNothing]
final class SampleTest extends TestCase
{
    public function testTrue(): void
    {
        $this->assertTrue(true);
    }
}
