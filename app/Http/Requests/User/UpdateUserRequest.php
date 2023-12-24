<?php

namespace App\Http\Requests\User;

use App\Enums\Gender;
use App\Http\Resources\Errors\UnprocessableResource;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'userName'  => ['nullable', Rule::unique('users', 'name')->ignore($this->id)],
            'email'     => ['nullable', Rule::unique('users', 'email')->ignore($this->id)],
            'password'  => ['nullable', 'min:11'],
            'firstName' => ['nullable', 'max:100'],
            'lastName'  => ['nullable', 'max:100'],
            'gender'    => ['nullable', Rule::in(Gender::values())],
            'dob'       => ['nullable', 'date_format:Y-m-d'],
            'location'  => ['nullable', 'timezone:all'],
        ];
    }

    /**
     * {@inheritDoc}
     *
     * @return array
     */
    public function attributes(): array
    {
        return [
            'userName'  => 'user name',
            'firstName' => 'first name',
            'lastName'  => 'last name',
            'dob'       => 'date of birth',
        ];
    }

    /**
     * {@inheritDoc}
     *
     * @throws ValidationException
     */
    protected function failedValidation(Validator $validator): void
    {
        if ($this->expectsJson()) {
            $resource = new UnprocessableResource(new ValidationException($validator));

            throw new HttpResponseException($resource->toResponse($this));
        }

        parent::failedValidation($validator);
    }
}
