<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        //Bármely felhasználó csinálhatja
        return true;
    }

    // app/Http/Requests/LoginUserRequest.php
    public function rules()
    {
        return [
            'email' => 'required|email',
            'password' => 'required|string',
            // Remove positionId from validation
        ];
    }
}
