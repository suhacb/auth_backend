<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AuthRequest extends FormRequest
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
    public function rules(): array
    {
        // Get the controller method from the current route
        $action = $this->route()->getActionMethod();

        // Call a method dynamically based on action
        $method = 'rulesFor' . ucfirst($action);

        if (method_exists($this, $method)) {
            return $this->$method();
        }

        // default fallback
        return [];
    }

    public function messages(): array
    {
        // Get the controller method from the current route
        $action = $this->route()->getActionMethod();

        // Call a method dynamically based on action
        $method = 'messagesFor' . ucfirst($action);

        if (method_exists($this, $method)) {
            return $this->$method();
        }
        
        // default fallback
        return [];
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
