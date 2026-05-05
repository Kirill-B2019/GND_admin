<?php

namespace Tests\Unit;

use App\Support\GndNodeError;
use PHPUnit\Framework\TestCase;

class GndNodeErrorTest extends TestCase
{
    public function test_parses_json_node_error(): void
    {
        $raw = '{"success":false,"error":"Неверный или отсутствующий X-Admin-Token","code":401}';
        $p = GndNodeError::parse($raw);
        $this->assertSame('Неверный или отсутствующий X-Admin-Token', $p['user_message']);
        $this->assertSame(401, $p['code']);
        $this->assertNotNull($p['hint']);
        $this->assertTrue($p['is_json']);
    }

    public function test_plain_string(): void
    {
        $p = GndNodeError::parse('timeout');
        $this->assertSame('timeout', $p['user_message']);
        $this->assertFalse($p['is_json']);
    }
}
