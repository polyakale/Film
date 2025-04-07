<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
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
    // Example StoreUserRequest
    public function rules()
    {
        return [
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|max:16',
            'positionId' => 'required|in:1,2',
            'name' => 'required|string'
        ];
    }
}