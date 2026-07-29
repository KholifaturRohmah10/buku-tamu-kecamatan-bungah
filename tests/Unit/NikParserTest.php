<?php

namespace Tests\Unit;

use App\Support\NikParser;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class NikParserTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::parse('2026-06-02 08:00:00', 'Asia/Jakarta'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_it_can_parse_male_nik(): void
    {
        $result = app(NikParser::class)->parse('3175091504900001');

        $this->assertNotNull($result);
        $this->assertSame('1990-04-15', $result['tanggal_lahir']);
        $this->assertSame(36, $result['umur']);
    }

    public function test_it_can_parse_female_nik(): void
    {
        $result = app(NikParser::class)->parse('3175095504900001');

        $this->assertNotNull($result);
        $this->assertSame('1990-04-15', $result['tanggal_lahir']);
        $this->assertSame(36, $result['umur']);
    }

    public function test_it_rejects_invalid_nik(): void
    {
        $result = app(NikParser::class)->parse('3175099913900001');

        $this->assertNull($result);
    }
}
