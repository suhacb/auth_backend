<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use App\Http\Requests\DynamicRequest;

class ApplicationRequest extends DynamicRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    protected function rulesForStore(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:applications,name'],
            'client_id' => ['required', 'string', 'max:255'],
            'client_secret' => ['required', 'string', 'max:255'],
            'grant_type' => ['required', 'string', 'max:255'],
            'url' => ['required', 'url', 'max:255'],
            'callback_url' => ['required', 'url', 'max:255'],
            'description' => ['sometimes', 'string', 'max:255'],
        ];
    }

    protected function messagesForStore(): array
    {
        return [
            'name.required' => 'The application name is required.',
            'name.string' => 'The application name must be a valid string.',
            'name.max' => 'The application name may not be greater than 255 characters.',
            'name.unique' => 'An application with this name already exists.',

            'client_id.required' => 'The client ID is required.',
            'client_id.string' => 'The client ID must be a valid string.',
            'client_id.max' => 'The client ID may not be greater than 255 characters.',

            'client_secret.required' => 'The client secret is required.',
            'client_secret.string' => 'The client secret must be a valid string.',
            'client_secret.max' => 'The client secret may not be greater than 255 characters.',

            'grant_type.required' => 'The grant type is required.',
            'grant_type.string' => 'The grant type must be a valid string.',
            'grant_type.max' => 'The grant type may not be greater than 255 characters.',

            'url.required' => 'The application URL is required.',
            'url.url' => 'The application URL must be a valid URL.',
            'url.max' => 'The application URL may not be greater than 255 characters.',

            'callback_url.required' => 'The callback URL is required.',
            'callback_url.url' => 'The callback URL must be a valid URL.',
            'callback_url.max' => 'The callback URL may not be greater than 255 characters.',

            'description.string' => 'The description must be a valid string.',
            'description.max' => 'The description may not be greater than 255 characters.',
        ];
    }

    protected function rulesForUpdate(): array
    {
        $applicationId = $this->route('application'); // get ID from route parameter

        return [
            'name' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('applications', 'name')->ignore($applicationId),
            ],
            'client_id' => ['sometimes', 'string', 'max:255'],
            'client_secret' => ['sometimes', 'string', 'max:255'],
            'grant_type' => ['sometimes', 'string', 'max:255'],
            'url' => ['sometimes', 'url', 'max:255'],
            'callback_url' => ['sometimes', 'url', 'max:255'],
            'description' => ['sometimes', 'string', 'max:255'],
        ];
    }

    protected function messagesForUpdate(): array
    {
        return [
            'name.string' => 'The application name must be a valid string.',
            'name.max' => 'The application name may not be greater than 255 characters.',
            'name.unique' => 'An application with this name already exists.',

            'client_id.string' => 'The client ID must be a valid string.',
            'client_id.max' => 'The client ID may not be greater than 255 characters.',

            'client_secret.string' => 'The client secret must be a valid string.',
            'client_secret.max' => 'The client secret may not be greater than 255 characters.',

            'grant_type.string' => 'The grant type must be a valid string.',
            'grant_type.max' => 'The grant type may not be greater than 255 characters.',

            'url.url' => 'The application URL must be a valid URL.',
            'url.max' => 'The application URL may not be greater than 255 characters.',

            'callback_url.url' => 'The callback URL must be a valid URL.',
            'callback_url.max' => 'The callback URL may not be greater than 255 characters.',

            'description.string' => 'The description must be a valid string.',
            'description.max' => 'The description may not be greater than 255 characters.',
        ];
    }
}
