<?php

namespace App\Http\Requests;

use App\Enums\ProjectStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validation for creating and updating a project.
 *
 * Shared by store() and update(); the unique-slug rule ignores the project
 * currently being edited.
 */
class ProjectRequest extends FormRequest
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
        $project = $this->route('project');

        return [
            'title' => ['required', 'string', 'max:150'],
            'slug' => [
                'nullable', 'string', 'max:180', 'alpha_dash',
                Rule::unique('projects', 'slug')->ignore($project?->id),
            ],
            'summary' => ['nullable', 'string', 'max:300'],
            'description' => ['nullable', 'string', 'max:20000'],
            'problem' => ['nullable', 'string', 'max:2000'],
            'purpose' => ['nullable', 'string', 'max:2000'],
            'features' => ['nullable', 'string', 'max:4000'],
            'highlights' => ['nullable', 'string', 'max:4000'],
            'contribution' => ['nullable', 'string', 'max:4000'],
            'role' => ['nullable', 'string', 'max:120'],
            'year' => ['nullable', 'string', 'max:20'],
            'technologies' => ['nullable', 'string', 'max:1000'],
            'category' => ['nullable', 'string', 'max:80'],
            'live_demo_url' => ['nullable', 'url', 'max:2048'],
            'repository_url' => ['nullable', 'url', 'max:2048'],
            'is_featured' => ['nullable', 'boolean'],
            'is_demo' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:99999'],
            'status' => ['required', Rule::enum(ProjectStatus::class)],

            'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'remove_cover' => ['nullable', 'boolean'],

            'gallery' => ['nullable', 'array', 'max:12'],
            'gallery.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'remove_gallery' => ['nullable', 'array'],
            'remove_gallery.*' => ['string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'cover_image.image' => 'The cover must be an image file.',
            'cover_image.mimes' => 'The cover must be a JPG, PNG or WebP image.',
            'cover_image.max' => 'The cover image must not be larger than 4 MB.',
            'gallery.*.image' => 'Every gallery file must be an image.',
            'gallery.*.mimes' => 'Gallery images must be JPG, PNG or WebP files.',
            'gallery.*.max' => 'Each gallery image must not be larger than 4 MB.',
            'live_demo_url.url' => 'The live demo URL must be a valid URL (for example https://example.com).',
            'repository_url.url' => 'The repository URL must be a valid URL (for example https://github.com/you/repo).',
        ];
    }
}
