<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @property-read \App\Models\Account $account
 */
class StoreCalendarRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<\Illuminate\Contracts\Validation\ValidationRule|string>|string>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                Rule::unique('calendars')->where('account_id', $this->account->id),
                'string',
                'max:192',
            ],
            'url' => [
                'required',
                Rule::unique('calendars'),
                'url',
                'max:192',
            ],
        ];
    }
}
