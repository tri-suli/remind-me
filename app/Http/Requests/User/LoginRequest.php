<?php

namespace App\Http\Requests\User;

use App\Http\Resources\Errors\UnprocessableResource;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
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
            'email'    => ['required'],
            'password' => ['required'],
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
