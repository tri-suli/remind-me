<?php

namespace Tests\Unit\Http\FormRequests;

use App\Enums\Gender;
use App\Http\Requests\User\StoreUserRequest;
use Illuminate\Validation\Rule;
use PHPUnit\Framework\TestCase;

class StoreUserRequestTest extends TestCase
{
    /** @test */
    public function it_will_always_authorize(): void
    {
        $request = new StoreUserRequest();

        $this->assertTrue($request->authorize());
    }

    /** @test */
    public function it_should_contains_the_given_field_rules(): void
    {
        $request = new StoreUserRequest();

        $rules = $request->rules();

        $this->assertEquals([
            'userName'  => ['nullable', 'unique:users,name'],
            'email'     => ['required', 'unique:users,email'],
            'password'  => ['required', 'min:11'],
            'firstName' => ['required', 'max:100'],
            'lastName'  => ['required', 'max:100'],
            'gender'    => ['nullable', Rule::in(Gender::values())],
            'dob'       => ['required', 'date_format:Y-m-d'],
            'location'  => ['nullable', 'timezone:all'],
        ], $rules);
    }

    /** @test */
    public function it_should_contains_specified_custom_attributes(): void
    {
        $request = new StoreUserRequest();

        $rules = $request->attributes();

        $this->assertEquals([
            'userName'  => 'user name',
            'firstName' => 'first name',
            'lastName'  => 'last name',
            'dob'       => 'date of birth',
        ], $rules);
    }
}
