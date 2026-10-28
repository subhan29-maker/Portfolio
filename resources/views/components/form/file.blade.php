@props([
    'name',
    'label',
    'hint' => null,
    'required' => false,
    'accept' => 'image/*',
    'preview' => null,
])

@php
    $hasError = $errors->has($name);
    $describedBy = collect([
        filled($hint) ? $name.'-hint' : null,
        $hasError ? $name.'-error' : null,
    ])->filter()->implode(' ');
@endphp

<div class="field {{ $hasError ? 'field--invalid' : '' }}">
    <label class="field__label" for="{{ $name }}">
        {{ $label }}
        @unless ($required)
            <span class="field__optional">optional</span>
        @endunless
    </label>

    @if (filled($hint))
        <p class="field__hint" id="{{ $name }}-hint">{{ $hint }}</p>
    @endif

    <input
        class="admin-file-input"
        id="{{ $name }}"
        name="{{ $name }}"
        type="file"
        accept="{{ $accept }}"
        @if ($required) required @endif
        @if (filled($preview)) data-preview-target="{{ $preview }}" @endif
        @if ($hasError) aria-invalid="true" @endif
        @if (filled($describedBy)) aria-describedby="{{ $describedBy }}" @endif
        {{ $attributes }}
    >

    @if ($hasError)
        <p class="field__error" id="{{ $name }}-error">{{ $errors->first($name) }}</p>
    @endif
</div>
