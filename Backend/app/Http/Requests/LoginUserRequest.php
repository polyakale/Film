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

    public function rules(): array
    {
        return [
            'positionId' => 'required|int',
            'email' => 'required|email',
            'password' => 'required',
        ];
    }
}
