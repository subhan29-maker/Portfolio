<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validation for the Profile & settings screen.
 *
 * One form saves everything: the optional new picture (when one is attached)
 * and the portfolio details shown on the public pages. The upload rules run
 * against the real file content, not just its name, so a non-image can never
 * reach the storage disk.
 */
class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->isAdmin();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            // Profile picture (optional: absent means "keep the current one").
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'photo_alt' => ['nullable', 'string', 'max:160'],

            // Portfolio identity.
            'name' => ['required', 'string', 'max:80'],
            'role' => ['required', 'string', 'max:80'],
            'hero_label' => ['nullable', 'string', 'max:80'],
            'hero_headline' => ['nullable', 'string', 'max:80'],
            'hero_lead' => ['nullable', 'string', 'max:400'],
            'availability' => ['nullable', 'string', 'max:120'],

            // Contact details: hidden from the interface while empty.
            'email' => ['nullable', 'email', 'max:190'],
            'phone' => ['nullable', 'string', 'max:40'],
            'location' => ['nullable', 'string', 'max:120'],

            // Social links: hidden from the interface while empty.
            'github' => ['nullable', 'url', 'max:2048'],
            'linkedin' => ['nullable', 'url', 'max:2048'],
            'x' => ['nullable', 'url', 'max:2048'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'photo.image' => 'The picture must be an image file.',
            'photo.mimes' => 'The picture must be a JPG, PNG or WebP image.',
            'photo.max' => 'The picture must not be larger than 5 MB.',
            'github.url' => 'The GitHub link must be a valid URL (for example https://github.com/you).',
            'linkedin.url' => 'The LinkedIn link must be a valid URL (for example https://www.linkedin.com/in/you).',
            'x.url' => 'The X link must be a valid URL (for example https://x.com/you).',
        ];
    }
}
