<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class validateOtpRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'mobile_number' => ['required', 'string', 'starts_with:09', 'min:11','max:11'],
            'type' => ['required', 'string'],
            'verification_code' => ['required', 'string', 'min:6', 'max:6']
        ];
    }
}
