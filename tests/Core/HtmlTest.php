<?php

declare(strict_types=1);

namespace Tests\Core;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use App\Support\Html;

#[CoversClass(Html::class)]
final class HtmlTest extends TestCase
{
    public function testEscapeBasic(): void
    {
        $this->assertSame('&lt;script&gt;', Html::escape('<script>'));
    }

    public function testEscapeQuotes(): void
    {
        $this->assertSame('&quot;test&quot;', Html::escape('"test"'));
    }

    public function testEscapeNull(): void
    {
        $this->assertSame('', Html::escape(null));
    }

    public function testEscapeNumber(): void
    {
        $this->assertSame('123', Html::escape(123));
    }

    public function testEscapeArrayThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Html::escape([]);
    }

    public function testDoubleEncodeFalse(): void
    {
        $this->assertSame('&lt;', Html::escape('&lt;', double_encode: false));
    }

    public function testCustomFlags(): void
    {
        $this->assertSame('"test"', Html::escape('"test"', flags: ENT_NOQUOTES));
    }
}
