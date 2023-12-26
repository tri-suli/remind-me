<?php

namespace Tests\Unit\Http\FormRequests;

use App\Enums\Gender;
use App\Http\Requests\User\UpdateUserRequest;
use Illuminate\Validation\Rule;
use PHPUnit\Framework\TestCase;

class UpdateUserRequestTest extends TestCase
{
    /** @test */
    public function it_will_always_authorize(): void
    {
        $request = new UpdateUserRequest();

        $this->assertTrue($request->authorize());
    }

    /** @test */
    public function it_should_contains_the_given_field_rules(): void
    {
        $request = new UpdateUserRequest();
        $request->id = 1;

        $rules = $request->rules();

        $this->assertEquals([
            'userName'  => ['nullable', Rule::unique('users', 'name')->ignore(1)],
            'email'     => ['nullable', Rule::unique('users', 'email')->ignore(1)],
            'password'  => ['nullable', 'min:11'],
            'firstName' => ['nullable', 'max:100'],
            'lastName'  => ['nullable', 'max:100'],
            'gender'    => ['nullable', Rule::in(Gender::values())],
            'dob'       => ['nullable', 'date_format:Y-m-d'],
            'location'  => ['nullable', 'timezone:all'],
        ], $rules);
    }

    /** @test */
    public function it_should_contains_specified_custom_attributes(): void
    {
        $request = new UpdateUserRequest();

        $rules = $request->attributes();

        $this->assertEquals([
            'userName'  => 'user name',
            'firstName' => 'first name',
            'lastName'  => 'last name',
            'dob'       => 'date of birth',
        ], $rules);
    }
}
