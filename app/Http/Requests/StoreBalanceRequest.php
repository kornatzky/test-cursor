<?php

namespace App\Http\Requests;

use App\Models\Balance;
use App\Support\Currencies;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreBalanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Balance::class) ?? false;
    }

    protected function prepareForValidation(): void
    {
        $currency = $this->input('currency');
        $amount = $this->input('amount');

        $this->merge([
            'currency' => is_string($currency) ? Str::upper(trim($currency)) : $currency,
            'amount' => is_string($amount) ? str_replace([',', ' '], '', trim($amount)) : $amount,
        ]);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'currency' => ['bail', 'required', 'string', Rule::in(array_keys(Currencies::CODES))],
            'amount' => ['bail', 'required', 'numeric', 'decimal:0,2', 'between:-999999999999.99,999999999999.99'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'currency.required' => 'Choose a currency.',
            'currency.in' => 'Choose a valid currency.',
            'amount.required' => 'Enter an amount.',
            'amount.numeric' => 'Enter an amount.',
            'amount.decimal' => 'Enter an amount with up to 2 decimal places.',
            'amount.between' => 'Enter an amount with up to 12 digits.',
        ];
    }

    /**
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $message = Currencies::rejectionMessage((string) $this->input('currency'));

                if ($message === null) {
                    return;
                }

                $validator->errors()->forget('currency');
                $validator->errors()->add('currency', $message);
            },
        ];
    }
}
