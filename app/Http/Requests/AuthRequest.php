<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use App\Http\Requests\DynamicRequest;

class AuthRequest extends DynamicRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
    
    protected function rulesForLogin(): array
    {
        return [
            'username' => 'required|string',
            'password' => 'required|string',
        ];
    }
    
    protected function messagesForLogin(): array
    {
        return [
            'username.required' => 'Username is required.',
            'username.string' => 'Username must be a string.',
            'password.required' => 'Password is required.',
            'password.string' => 'Password must be a string.',
        ];
    }

    protected function rulesForLoginToken(): array
    {
        return [
            'app' => [
                'required',
                'string',
                Rule::in(['nutrients'])
            ]
        ];
    }

    protected function messagesForLoginToken(): array
    {
        return [
            'app.required' => 'The app field is required.',
            'app.string' => 'The app field must be a string.',
            'app.in' => 'The selected app is invalid.',
        ];
    }
}
