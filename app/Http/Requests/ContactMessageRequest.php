<?php

namespace App\Http\Requests;

use App\Enums\BudgetRange;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ContactMessageRequest extends FormRequest
{
    /**
     * The contact form is public, so there is nothing to authorize here.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:80'],
            'email' => ['required', 'string', 'email:rfc', 'max:120'],
            'phone' => ['nullable', 'string', 'max:30', 'regex:/^\+?[0-9][0-9\s().-]{4,29}$/'],
            'subject' => ['required', 'string', 'min:3', 'max:140'],
            'budget' => ['nullable', Rule::enum(BudgetRange::class)],
            'message' => ['required', 'string', 'min:20', 'max:2000'],

            // Honeypot: hidden from people, tempting to bots. Must stay empty.
            'website' => ['prohibited'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'phone.regex' => 'Please enter a phone number using digits, spaces, or + ( ) - . characters.',
            'message.min' => 'Please tell me a little more — at least :min characters.',
            'website.prohibited' => 'This submission could not be processed.',
            'budget.enum' => 'Please choose one of the listed budget ranges.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'name',
            'email' => 'email address',
            'phone' => 'phone number',
            'subject' => 'subject',
            'budget' => 'budget',
            'message' => 'message',
        ];
    }

    /**
     * Normalise input before it is validated.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => is_string($this->input('name')) ? trim($this->input('name')) : $this->input('name'),
            'email' => is_string($this->input('email')) ? mb_strtolower(trim($this->input('email'))) : $this->input('email'),
            'phone' => is_string($this->input('phone')) && trim($this->input('phone')) !== ''
                ? trim($this->input('phone'))
                : null,
            'subject' => is_string($this->input('subject')) ? trim($this->input('subject')) : $this->input('subject'),
            'budget' => filled($this->input('budget')) ? $this->input('budget') : null,
        ]);
    }
}
