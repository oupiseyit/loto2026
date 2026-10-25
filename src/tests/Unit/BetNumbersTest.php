<?php

namespace Tests\Unit;

use App\Support\BetNumbers as N;
use PHPUnit\Framework\TestCase;

class BetNumbersTest extends TestCase
{
    public function test_normal(): void
    {
        $this->assertSame(['25'], N::expand(N::NORMAL, '25'));
        $this->assertSame([], N::expand(N::NORMAL, ''));
        $this->assertSame([], N::expand(N::NORMAL, '1234'));
    }

    public function test_multiply_dedupes_repeated_digits(): void
    {
        $this->assertSame(['123', '132', '213', '231', '312', '321'], N::expand(N::MULTIPLY, '123'));
        $this->assertSame(['112', '121', '211'], N::expand(N::MULTIPLY, '112'));
        $this->assertSame(['12', '21'], N::expand(N::MULTIPLY, '12'));
    }

    public function test_range_sweeps_the_single_differing_digit(): void
    {
        $this->assertSame(['10', '11', '12', '13'], N::expand(N::RANGE, '10', '13'));      // tail
        $this->assertCount(9, N::expand(N::RANGE, '15', '95'));                          // head, step 10
        $this->assertSame(['105', '115', '125'], N::expand(N::RANGE, '105', '125'));    // middle
        $this->assertCount(100, N::expand(N::RANGE, '00', '99'));                       // 2 digits differ → every number
        $this->assertSame([], N::expand(N::RANGE, '50', '10'));
        $this->assertSame([], N::expand(N::RANGE, '50', '100'));
    }

    public function test_pair(): void
    {
        $this->assertSame(['00', '11', '22', '33', '44', '55', '66', '77', '88', '99'], N::expand(N::PAIR, ''));
        $this->assertSame('999', N::expand(N::PAIR, '000')[9]);
    }

    public function test_roll(): void
    {
        $nums = N::expand(N::ROLL, '5');
        $this->assertCount(19, $nums);
        $this->assertContains('05', $nums);
        $this->assertContains('59', $nums);
        $this->assertSame([], N::expand(N::ROLL, '55'));
    }
}
