<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFavouriteRequest extends FormRequest
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
    public function rules()
    {
        return [
            'filmId' => 'required|exists:films,id',
            'evaluation' => [
                'required',
                'numeric',
                'min:0.5',
                'max:5',
                function ($attribute, $value, $fail) {
                    if (fmod($value * 2, 1) !== 0.0) {
                        $fail('The evaluation must be in 0.5 increments (0.5, 1.0, 1.5, etc.)');
                    }
                }
            ],
            'content' => 'required|string',
        ];
    }
}
