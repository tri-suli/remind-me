<?php

namespace Tests\Unit\Enums;

use App\Enums\Gender;
use PHPUnit\Framework\TestCase;

class GenderTest extends TestCase
{
    /** @test */
    public function it_return_the_gender_description(): void
    {
        $male = Gender::MALE;
        $female = Gender::FEMALE;

        $this->assertEquals('Male', $male->title());
        $this->assertEquals('Female', $female->title());
        $this->assertEquals(1, $male->value);
        $this->assertEquals(0, $female->value);
    }

    /** @test */
    public function it_return_gender_values(): void
    {
        $this->assertEquals([1, 0], Gender::values());
    }
}
