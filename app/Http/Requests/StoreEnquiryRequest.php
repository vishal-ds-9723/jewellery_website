<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEnquiryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('email'))) {
            $this->merge(['email' => mb_strtolower(trim($this->input('email')))]);
        }
    }

    public function rules(): array
    {
        return [
            'submission_id' => ['required', 'uuid'],
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email:rfc', 'max:255'],
            'phone' => ['bail', 'required', 'string', 'max:30', 'regex:/^\+?[0-9\s().-]{7,30}$/', function ($attribute, $value, $fail) {
                $digits = preg_replace('/\D/', '', $value);
                if (strlen($digits) < 7 || strlen($digits) > 15) {
                    $fail('Enter a phone number containing 7 to 15 digits.');
                }
            }],
            'shopping_interest' => ['nullable', Rule::in(['men', 'women', 'gifting', 'silver'])],
            'appointment_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:'.now('Asia/Kolkata')->toDateString()],
            'appointment_slot' => ['required', Rule::in(['morning', 'evening'])],
            'message' => ['required', 'string', 'max:5000'],
            'website' => ['nullable', 'string', 'max:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'appointment_date.after_or_equal' => 'Please select today or a future appointment date.',
            'phone.regex' => 'Please enter a valid contact number.',
            'website.max' => 'We could not accept this submission. Please try again.',
        ];
    }
}
